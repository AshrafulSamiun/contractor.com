<template>
  <AccountLayout bare>
    <main class="po-page" :aria-busy="busy">
      <header class="po-header">
        <div class="po-heading"><ShoppingCart :size="30"/><div><h1>Purchase Order</h1><p>Issued to Sellers</p></div></div>
        <div v-if="view === 'list'" class="po-header-search"><Search/><input v-model="filters.search" aria-label="Search purchase orders" placeholder="Search PO No. / Seller / Ref. No..."></div>
        <div class="po-actions">
          <select v-if="view === 'detail' && rows.length" :value="form.id || ''" aria-label="Open purchase order" @change="openOrder(rows.find(x => x.id === Number($event.target.value)))">
            <option value="" disabled>New purchase order</option><option v-for="row in rows" :key="row.id" :value="row.id">{{ row.po_no }}</option>
          </select>
          <button v-if="view === 'detail'" class="po-btn" @click="backToList"><ArrowLeft/> Back to list</button>
          <span v-if="view === 'detail'" class="po-pill" :class="slug(orderStatus(form))">{{ orderStatus(form) }}</span>
        </div>
      </header>
      <div v-if="error" class="po-alert error" role="alert">{{ error }}</div>
      <div v-if="notice" class="po-alert success" role="status">{{ notice }}</div>
      <template v-if="view === 'list'">
        <section class="po-metrics">
          <article v-for="card in metrics" :key="card.label" :class="card.tone">
            <component :is="card.icon" :size="30"/><div><h2>{{ card.label }}</h2><strong>{{ card.orders.length }} <small>{{ card.orders.length === 1 ? 'Order' : 'Orders' }}</small></strong><b v-for="total in currencyTotals(card.orders)" :key="total.code">{{ money(total.amount) }} {{ total.code }}</b></div>
          </article>
        </section>
        <form class="po-filters" @submit.prevent="page = 1">
          <label>Date Range<span class="po-date-range"><input v-model="filters.from" type="date"><input v-model="filters.to" type="date"></span></label>
          <label>Seller<select v-model="filters.seller"><option value="">All Sellers</option><option v-for="name in sellerNames" :key="name">{{ name }}</option></select></label>
          <label>PO Status<select v-model="filters.status"><option value="">All Status</option><option v-for="status in statuses" :key="status">{{ status }}</option></select></label>
          <label>Payment Status<select v-model="filters.payment"><option value="">All Status</option><option v-for="status in paymentStatuses" :key="status">{{ status }}</option></select></label>
          <label>Department<select v-model="filters.department"><option value="">All Departments</option><option v-for="name in departments" :key="name">{{ name }}</option></select></label>
          <label>Project<select v-model="filters.project"><option value="">All Projects</option><option v-for="name in projects" :key="name">{{ name }}</option></select></label>
          <button class="po-btn primary" type="submit"><Search/> Search</button><button class="po-btn" type="button" @click="resetFilters"><RotateCcw/> Reset</button>
        </form>
        <section class="po-list">
          <header class="po-section-head"><h2>Purchase Order List <small>(Issued to Sellers)</small></h2><div class="po-actions"><button class="po-btn primary" :disabled="busy" @click="startNew"><Plus/> New Purchase Order</button><button class="po-btn green" @click="exportExcel"><Download/> Export</button></div></header>
          <div class="po-table-scroll">
            <table class="po-list-table"><thead><tr>
              <th v-for="col in listColumns" :key="col.key"><button @click="sortBy(col.key)">{{ col.label }} <ArrowUpDown :size="12"/></button></th><th>View</th>
            </tr></thead><tbody>
              <tr v-for="row in paginatedRows" :key="row.id">
                <td><button class="po-link" @click="openOrder(row)">{{ row.po_no }}</button></td><td>{{ dateLabel(row.po_date) }}</td><td class="po-number">{{ money(row.total) }} {{ row.currency_code }}</td>
                <td>{{ dateTimeLabel(row.expiry_at) }}</td><td>{{ row.creator?.name || row.requested_by || '-' }}</td><td>{{ row.seller_no || '-' }}</td><td>{{ row.seller_name }}</td>
                <td>{{ invoiceFor(row)?.number || '-' }}</td><td>{{ dateLabel(invoiceFor(row)?.date) }}</td><td class="po-number">{{ invoiceFor(row) ? money(invoiceFor(row).total) : '-' }}</td>
                <td :class="{ 'po-negative': variance(row) < 0 }">{{ invoiceFor(row) ? money(variance(row)) : '-' }}</td><td class="po-positive">{{ money(sellerBalance(row)) }} {{ row.currency_code }}</td>
                <td><span class="po-pill" :class="slug(orderStatus(row))">{{ orderStatus(row) }}</span><small class="po-payment-status">{{ paymentStatus(row) }}</small></td>
                <td><button class="po-btn compact" :disabled="busy" @click="openOrder(row)"><Eye/> View</button></td>
              </tr>
              <tr v-if="!paginatedRows.length"><td colspan="14" class="po-empty">{{ busy ? 'Loading purchase orders...' : 'No purchase orders found.' }}</td></tr>
            </tbody></table>
          </div>
          <footer class="po-pagination"><span>Showing {{ filteredRows.length ? (page - 1) * perPage + 1 : 0 }} to {{ Math.min(page * perPage, filteredRows.length) }} of {{ filteredRows.length }} entries</span>
            <div class="po-actions"><select v-model.number="perPage" aria-label="Orders per page"><option :value="15">15</option><option :value="30">30</option><option :value="50">50</option></select>
              <button class="po-icon" title="First page" :disabled="page === 1" @click="page = 1"><ChevronsLeft/></button><button class="po-icon" title="Previous page" :disabled="page === 1" @click="page--"><ChevronLeft/></button>
              <b>{{ page }} / {{ pageCount }}</b><button class="po-icon" title="Next page" :disabled="page === pageCount" @click="page++"><ChevronRight/></button><button class="po-icon" title="Last page" :disabled="page === pageCount" @click="page = pageCount"><ChevronsRight/></button>
            </div>
          </footer>
          <div class="po-action-bar"><b>Action Bar:</b><button class="po-btn primary" @click="printPage"><Printer/> Print</button><button class="po-btn green" @click="savePdf"><FileDown/> Save PDF</button><button class="po-btn" @click="emailOrder"><Mail/> Email</button></div>
        </section>
      </template>
      <div v-else class="po-workspace">
        <section class="po-document">
          <header class="po-document-head"><div class="po-heading"><FileText/><h2>Purchase Order</h2></div><div class="po-actions"><b>{{ form.po_no || 'New order' }}</b><span class="po-pill" :class="slug(form.approval_status)">{{ form.approval_status }}</span><button class="po-icon" title="Print purchase order" @click="printPage"><Printer/></button><button class="po-icon" title="Email purchase order" @click="emailOrder"><Mail/></button><button class="po-icon" title="More actions" type="button"><MoreVertical/></button></div></header>
          <div class="po-document-body">
            <div class="po-details-grid">
              <section><h3>Sellers</h3>
                <div v-if="editing" class="po-field"><label>Seller</label><i aria-hidden="true">:</i><SearchableSelect v-model="form.seller_id" :options="sellers" option-label="seller_name" option-value="id" :description-keys="['contact_person', 'email', 'phone']" placeholder="Search seller..." aria-label="Seller" @select="applySeller"/></div>
                <Field label="Vendor ID" :model-value="form.seller_no"/>
                <Field label="Company Name" v-model="form.seller_name" :editable="editing && !form.seller_id"/>
                <Field v-for="field in sellerFields" :key="field.key" :label="field.label" v-model="form.seller_details[field.key]" :editable="editing && !form.seller_id" :multiline="field.key === 'address'"/>
              </section>
              <section><h3>Requested By</h3>
                <Field label="Department" v-model="form.department" :editable="editing"/><Field label="Contact Person" v-model="form.requested_by" :editable="editing"/>
                <Field v-for="field in requesterFields" :key="field.key" :label="field.label" v-model="form.requester_details[field.key]" :editable="editing" :multiline="field.key === 'address'"/>
                <Field label="Project" v-model="form.project" :editable="editing"/>
              </section>
              <section><h3>Order Details</h3>
                <Field label="PO No." v-model="form.po_no" :editable="editing"/>
                <Field label="PO Date" v-model="form.po_date" :editable="editing" type="date" :display-value="dateLabel(form.po_date)"/>
                <Field label="Expiry Date - Time" v-model="form.expiry_at" :editable="editing" type="datetime-local" :display-value="dateTimeLabel(form.expiry_at)"/>
                <Field label="Expected Delivery" v-model="form.expected_delivery_at" :editable="editing" type="datetime-local" :display-value="dateTimeLabel(form.expected_delivery_at)"/>
                <Field label="Delivery Location" v-model="form.delivery_location" :editable="editing"/>
                <Field label="Currency" v-model="form.currency_code" :editable="editing" :options="currencies"/>
                <Field label="Status" v-model="form.status" :editable="editing" :options="editableStatuses"/>
                <Field label="Payment Term" v-model="form.payment_term" :editable="editing" :options="paymentTerms"/>
                <Field label="Payment Method" v-model="form.payment_method" :editable="editing" :options="paymentMethods"/>
              </section>
            </div>
            <section class="po-items">
              <div v-if="editing" class="po-section-head"><h3>Items / Services</h3><button class="po-btn compact primary" @click="addItem"><Plus/> Add item</button></div>
              <div class="po-table-scroll"><table><thead><tr><th>Item / Service</th><th>Item Code / SKU</th><th>UOM</th><th>Qty</th><th>Cost Rate</th><th>Sub Total</th><th>Sales Tax (amount)</th><th>Total</th><th v-if="editing"></th></tr></thead>
                <tbody><tr v-for="(item, index) in form.items" :key="index">
                  <td><SearchableSelect v-if="editing" v-model="item.name" :options="catalog" option-label="name" option-value="name" :description-keys="['code', 'uom']" placeholder="Search service item..." aria-label="Item or service" @select="applyCatalog(item, $event)"/><span v-else>{{ item.name }}</span></td>
                  <td><input v-if="editing" v-model="item.code" aria-label="Item code"><span v-else>{{ item.code || '-' }}</span></td>
                  <td><input v-if="editing" v-model="item.uom" aria-label="Unit of measure"><span v-else>{{ item.uom }}</span></td>
                  <td><input v-if="editing" v-model.number="item.quantity" type="number" min="0.01" step="0.01" aria-label="Quantity"><span v-else>{{ money(item.quantity) }}</span></td>
                  <td><input v-if="editing" v-model.number="item.cost_rate" type="number" min="0" step="0.01" aria-label="Cost rate"><span v-else>{{ money(item.cost_rate) }}</span></td>
                  <td>{{ money(lineSubtotal(item)) }}</td><td><input v-if="editing" v-model.number="item.sales_tax" type="number" min="0" step="0.01" aria-label="Sales tax amount"><span v-else>{{ money(item.sales_tax) }}</span></td><td>{{ money(lineTotal(item)) }}</td>
                  <td v-if="editing"><button class="po-icon danger" title="Remove item" @click="form.items.splice(index, 1)"><Trash2/></button></td>
                </tr><tr v-if="!form.items.length"><td :colspan="editing ? 9 : 8" class="po-empty">No items added.</td></tr></tbody>
                <tfoot><tr><th colspan="5">Total</th><th>{{ money(subtotal) }}</th><th>{{ money(taxTotal) }}</th><th>{{ money(grandTotal) }}</th><th v-if="editing"></th></tr></tfoot>
              </table></div>
            </section>
            <div class="po-bottom-grid">
              <section><h3>Terms &amp; Conditions</h3><textarea v-if="editing" v-model="form.terms" rows="4" aria-label="Terms and conditions"/><p v-else class="po-terms">{{ form.terms || '-' }}</p>
                <div class="po-metadata"><Field label="Last modification" :model-value="dateTimeLabel(form.updated_at)"/><Field label="Created By" :model-value="form.creator?.name"/><Field label="Created Date" :model-value="dateTimeLabel(form.created_at)"/></div>
              </section>
              <section><h3>Delivery Information</h3><Field label="Expected Delivery" :model-value="dateTimeLabel(form.expected_delivery_at)"/><Field label="Delivery Location" :model-value="form.delivery_location"/><Field label="Delivery Contact" v-model="form.delivery_details.contact_person" :editable="editing"/><Field label="Delivery Phone" v-model="form.delivery_details.phone" :editable="editing"/><Field label="Delivery Instructions" v-model="form.notes" :editable="editing" multiline/></section>
              <section class="po-totals"><Field label="Sub Total" :model-value="money(subtotal)"/><Field label="Sales Tax (Total)" :model-value="money(taxTotal)"/><div><span>Grand Total ({{ form.currency_code }})</span><strong>{{ money(grandTotal) }}</strong></div></section>
            </div>
            <section v-if="form.attachments.length" class="po-attachments"><h3>Attachments</h3><button v-for="file in form.attachments" :key="file.id" class="po-btn compact" @click="downloadAttachment(file)"><Paperclip/> {{ file.name }} <small>{{ Math.ceil(file.size / 1024) }} KB</small></button></section>
          </div>
          <footer class="po-document-footer">
            <div class="po-actions"><button class="po-btn" :disabled="busy" @click="startNew"><Plus/> New</button><button class="po-btn" :disabled="busy || editing || !!invoiceFor(form)" @click="beginEdit"><Pencil/> Edit</button><button class="po-btn danger" :disabled="busy || !form.id || !!invoiceFor(form)" @click="deleteOrder"><Trash2/> Delete</button><button class="po-btn primary" :disabled="busy || !editing" @click="saveOrder"><Save/> {{ busy ? 'Saving...' : 'Save' }}</button><button v-if="editing" class="po-btn" :disabled="busy" @click="cancelEdit"><X/> Cancel</button>
              <button class="po-btn" @click="printPage"><Printer/> Print</button><button class="po-btn" @click="savePdf"><FileDown/> Save PDF</button><button class="po-btn" @click="emailOrder"><Mail/> Email</button>
              <button class="po-btn green" :disabled="!canDecide" @click="workflow('approve')"><Check/> Approve</button><button class="po-btn danger" :disabled="!canDecide" @click="workflow('reject')"><X/> Reject</button><button class="po-btn" :disabled="busy || !form.id || editing" @click="duplicateOrder"><Copy/> Duplicate PO</button>
            </div>
            <div class="po-actions"><button class="po-btn primary" :disabled="!canConvert" @click="workflow('convert')"><FileCheck/> Convert to Invoice</button><button class="po-btn" :disabled="busy || !form.id || editing" @click="fileInput.click()"><Paperclip/> Attach File</button><input ref="fileInput" class="po-hidden" type="file" accept=".pdf,.png,.jpg,.jpeg,.txt,.csv,.doc,.docx,.xls,.xlsx" @change="uploadAttachment">
              <button class="po-btn" :disabled="!form.id" @click="modal = 'audit'"><History/> View Audit Log</button><button class="po-btn" :disabled="!canSubmit" @click="workflow('submit')"><Send/> Send for Approval</button><button class="po-btn green" @click="exportExcel"><FileSpreadsheet/> Export Excel</button>
            </div>
            <p>Last modification date - time: <b>{{ dateTimeLabel(form.updated_at) }}</b></p>
          </footer>
        </section>
        <aside class="po-sidebar">
          <section><h3>Transaction Tracker</h3><div class="po-table-scroll"><table class="po-tracker"><thead><tr><th>Doc. Type</th><th>No.</th><th>Date</th><th>Subtotal</th><th>Sales Tax</th><th>Total</th></tr></thead><tbody>
            <tr><td>Purchase Order</td><td>{{ form.po_no || '-' }}</td><td>{{ dateLabel(form.po_date) }}</td><td>{{ money(subtotal) }}</td><td>{{ money(taxTotal) }}</td><td>{{ money(grandTotal) }}</td></tr>
            <template v-for="type in documentTypes" :key="type"><tr v-for="doc in documentsOfType(type)" :key="doc.id || type"><td>{{ type }}</td><td><button v-if="doc.id" class="po-link" @click="showDocument(doc)">{{ doc.number }}</button><span v-else>-</span></td><td>{{ dateLabel(doc.date) }}</td><td>{{ doc.id ? money(doc.subtotal) : '-' }}</td><td>{{ doc.id ? money(doc.sales_tax) : '-' }}</td><td>{{ doc.id ? money(doc.total) : '-' }}</td></tr></template>
          </tbody><tfoot><tr><th>Balance</th><th colspan="2">{{ form.currency_code }}</th><th>{{ money(balanceFor(form, 'subtotal')) }}</th><th>{{ money(balanceFor(form, 'sales_tax')) }}</th><th>{{ money(balanceFor(form)) }}</th></tr></tfoot></table></div>
            <button v-if="invoiceFor(form)" class="po-btn compact po-record" :disabled="busy" @click="startDocument"><Plus/> Record payment / adjustment</button>
          </section>
          <section><h3>Quick Reports</h3><label class="po-report-seller">Select Seller<select v-model="reportSeller"><option value="">All Sellers</option><option v-for="name in sellerNames" :key="name">{{ name }}</option></select></label>
            <button v-for="report in quickReports" :key="report.key" class="po-report-link" @click="openReport(report)"><span>{{ report.label }}</span><b>{{ report.count }}</b></button>
          </section>
          <section><h3>Current PO Details</h3><Field label="PO No." :model-value="form.po_no"/><Field label="PO Date" :model-value="dateLabel(form.po_date)"/><Field label="Expiry Date - Time" :model-value="dateTimeLabel(form.expiry_at)"/><Field label="Expected Delivery" :model-value="dateTimeLabel(form.expected_delivery_at)"/><Field label="Delivery Location" :model-value="form.delivery_location"/><Field label="Currency" :model-value="form.currency_code"/><Field label="Status" :model-value="orderStatus(form)"/><Field label="Payment Term" :model-value="form.payment_term"/><Field label="Payment Method" :model-value="form.payment_method"/><Field label="Payment Status" :model-value="paymentStatus(form)"/></section>
        </aside>
      </div>
      <div v-if="modal" class="po-modal-backdrop" @click.self="closeModal" @keydown.esc="closeModal">
        <section ref="modalPanel" class="po-modal" role="dialog" aria-modal="true" aria-labelledby="po-modal-title" tabindex="-1">
          <header><h2 id="po-modal-title">{{ modalTitle }}</h2><button class="po-icon" title="Close dialog" @click="closeModal"><X/></button></header>
          <div v-if="modalError" class="po-alert error" role="alert">{{ modalError }}</div>
          <template v-if="modal === 'audit'"><div class="po-table-scroll"><table><thead><tr><th>Date / Time</th><th>User</th><th>Action</th><th>Details</th></tr></thead><tbody><tr v-for="entry in form.activities" :key="entry.id"><td>{{ dateTimeLabel(entry.created_at) }}</td><td>{{ entry.actor }}</td><td>{{ entry.action }}</td><td>{{ activityDetails(entry.changes) }}</td></tr></tbody></table></div></template>
          <form v-else-if="modal === 'record'" @submit.prevent="recordDocument">
            <Field label="Document Type" v-model="documentForm.type" editable :options="documentTypes.slice(1)"/><Field label="Document No." v-model="documentForm.number" editable/><Field label="Date" v-model="documentForm.date" editable type="date"/>
            <Field label="Subtotal" v-model="documentForm.subtotal" editable type="number"/><Field label="Sales Tax" v-model="documentForm.sales_tax" editable type="number"/><Field label="Notes" v-model="documentForm.notes" editable multiline/>
            <Field label="Total" :model-value="money(Number(documentForm.subtotal) + Number(documentForm.sales_tax))"/><button class="po-btn primary" :disabled="busy"><Save/> Record Document</button>
          </form>
          <template v-else-if="modal === 'document'"><Field label="Document No." :model-value="selectedDocument.number"/><Field label="Date" :model-value="dateLabel(selectedDocument.date)"/><Field label="Due Date" :model-value="dateLabel(selectedDocument.due_date)"/>
            <div v-if="selectedDocument.items?.length" class="po-table-scroll"><table><thead><tr><th>Item</th><th>Qty</th><th>Cost Rate</th><th>Tax</th><th>Total</th></tr></thead><tbody><tr v-for="(item, index) in selectedDocument.items" :key="index"><td>{{ item.name }}</td><td>{{ item.quantity }}</td><td>{{ money(item.cost_rate) }}</td><td>{{ money(item.sales_tax) }}</td><td>{{ money(lineTotal(item)) }}</td></tr></tbody></table></div>
            <Field label="Subtotal" :model-value="money(selectedDocument.subtotal)"/><Field label="Sales Tax" :model-value="money(selectedDocument.sales_tax)"/><Field label="Total" :model-value="money(selectedDocument.total)"/><Field label="Notes" :model-value="selectedDocument.notes"/>
          </template>
          <template v-else-if="modal === 'report'"><div class="po-table-scroll"><table><thead><tr><th v-for="heading in reportData.headers" :key="heading">{{ heading }}</th></tr></thead><tbody><tr v-for="(row, index) in reportData.rows" :key="index"><td v-for="(cell, i) in row" :key="i">{{ cell }}</td></tr><tr v-if="!reportData.rows.length"><td :colspan="reportData.headers.length" class="po-empty">No records found.</td></tr></tbody></table></div><button class="po-btn green" @click="exportReport"><FileSpreadsheet/> Export Excel</button></template>
        </section>
      </div>
    </main>
  </AccountLayout>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { ShoppingCart, Search, Plus, RotateCcw, Download, Printer, Mail, FileText, FileDown, FileSpreadsheet, ArrowLeft, ArrowUpDown, Eye, Pencil, Save, Trash2, X, Check, Copy, Paperclip, History, Send, FileCheck, Clock, BadgeCheck, ChevronsLeft, ChevronLeft, ChevronRight, ChevronsRight, MoreVertical } from 'lucide-vue-next'
import AccountLayout from '../components/AccountLayout.vue'
import Field from '../components/PurchaseOrderField.vue'
import SearchableSelect from '../components/SearchableSelect.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { roundMoney, lineSubtotal, lineTotal, invoiceFor, balanceFor, paymentStatus, orderStatus, today, localDateTime, money, dateLabel, dateTimeLabel } from '../utils/purchaseOrders'

const view = ref('list'), rows = ref([]), sellers = ref([]), catalog = ref([]), currencies = ref(['USD', 'CAD']), paymentTerms = ref([]), paymentMethods = ref([])
const busy = ref(false), editing = ref(false), error = ref(''), notice = ref(''), page = ref(1), perPage = ref(15), sort = ref({ key: 'po_date', direction: -1 })
const fileInput = ref(null), modal = ref(''), modalPanel = ref(null), modalError = ref(''), reportSeller = ref(''), selectedDocument = ref({}), reportData = ref({ title: '', headers: [], rows: [] })
const initialFilters = () => ({ search: '', from: '', to: '', seller: '', status: '', payment: '', department: '', project: '' })
const filters = ref(initialFilters())
const statuses = ['Pending', 'Confirmed', 'Accepted', 'Cancelled', 'Expired'], editableStatuses = statuses.filter(x => x !== 'Accepted'), paymentStatuses = ['Unpaid', 'Partially Paid', 'Paid', 'Overdue']
const documentTypes = ['Purchase Invoice', 'Purchase Invoice Return', 'Debit Note', 'Credit Note', 'Bill Payment']
const sellerFields = [{ key: 'contact_person', label: 'Contact Person' }, { key: 'phone', label: 'Phone' }, { key: 'email', label: 'Email' }, { key: 'address', label: 'Address' }, { key: 'website', label: 'Website' }, { key: 'tax_number', label: 'Tax Number' }, { key: 'vendor_category', label: 'Vendor Category' }]
const requesterFields = [{ key: 'phone', label: 'Phone' }, { key: 'email', label: 'Email' }, { key: 'company_name', label: 'Company Name' }, { key: 'address', label: 'Address' }]
const listColumns = [{ key: 'po_no', label: 'PO No.' }, { key: 'po_date', label: 'Date' }, { key: 'total', label: 'Total' }, { key: 'expiry_at', label: 'Expiry Date - Time' }, { key: 'creator', label: 'Ordered By' }, { key: 'seller_no', label: 'Seller No.' }, { key: 'seller_name', label: 'Name' }, { key: 'invoice_no', label: 'Purchase Inv. No.' }, { key: 'invoice_date', label: 'Invoice Date' }, { key: 'invoice_total', label: 'Invoice Total' }, { key: 'variance', label: 'Over (Short) Amount' }, { key: 'balance', label: 'Seller Balance' }, { key: 'status', label: 'Status' }]
const blank = () => ({ id: null, po_no: '', seller_id: null, seller_no: '', seller_name: '', seller_details: Object.fromEntries(sellerFields.map(x => [x.key, ''])), requester_details: { phone: '', email: authState.user?.email || '', company_name: '', address: '' }, requested_by: authState.user?.name || '', department: '', project: '', po_date: today(), expiry_at: '', expected_delivery_at: '', delivery_location: '', delivery_details: { contact_person: '', phone: '', email: '' }, currency_code: currencies.value.includes('USD') ? 'USD' : currencies.value[0], status: 'Pending', approval_status: 'Draft', payment_status: 'Unpaid', payment_term: paymentTerms.value.includes('Net 30') ? 'Net 30' : '', payment_method: '', items: [], terms: '', notes: '', documents: [], activities: [], attachments: [] })
const form = ref(blank()), documentForm = ref({})
const unique = values => [...new Set(values.filter(Boolean))].sort()
const sellerNames = computed(() => unique([...sellers.value.map(x => x.seller_name), ...rows.value.map(x => x.seller_name)]))
const departments = computed(() => unique(rows.value.map(x => x.department))), projects = computed(() => unique(rows.value.map(x => x.project)))
const variance = row => roundMoney(Number(invoiceFor(row)?.total || 0) - Number(row.total || 0))
const sameSeller = (a, b) => a.seller_id && b.seller_id ? Number(a.seller_id) === Number(b.seller_id) : a.seller_name === b.seller_name
const sellerBalance = row => roundMoney(rows.value.filter(x => sameSeller(x, row) && x.currency_code === row.currency_code).reduce((total, x) => total + balanceFor(x), 0))
function sortValue(row, key) {
  if (key === 'creator') return row.creator?.name || row.requested_by || ''
  if (key === 'invoice_no') return invoiceFor(row)?.number || ''
  if (key === 'invoice_date') return invoiceFor(row)?.date || ''
  if (key === 'invoice_total') return Number(invoiceFor(row)?.total || 0)
  if (key === 'variance') return variance(row)
  if (key === 'balance') return sellerBalance(row)
  if (key === 'total') return Number(row.total)
  if (key === 'status') return orderStatus(row)
  return row[key] || ''
}
const filteredRows = computed(() => rows.value.filter(row => {
  const f = filters.value, query = f.search.toLowerCase(), date = String(row.po_date).slice(0, 10)
  return (!query || [row.po_no, row.seller_name, row.seller_no, invoiceFor(row)?.number].some(value => String(value || '').toLowerCase().includes(query)))
    && (!f.seller || row.seller_name === f.seller) && (!f.status || orderStatus(row) === f.status) && (!f.payment || paymentStatus(row) === f.payment)
    && (!f.department || row.department === f.department) && (!f.project || row.project === f.project) && (!f.from || date >= f.from) && (!f.to || date <= f.to)
}).sort((a, b) => {
  const x = sortValue(a, sort.value.key), y = sortValue(b, sort.value.key)
  return (typeof x === 'number' ? x - y : String(x).localeCompare(String(y), undefined, { numeric: true })) * sort.value.direction
}))
const pageCount = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / perPage.value))), paginatedRows = computed(() => filteredRows.value.slice((page.value - 1) * perPage.value, page.value * perPage.value))
const metrics = computed(() => [
  { label: 'Total Orders', tone: 'blue', icon: ShoppingCart, orders: filteredRows.value },
  ...[{ status: 'Confirmed', tone: 'green', icon: FileCheck }, { status: 'Cancelled', tone: 'red', icon: X }, { status: 'Expired', tone: 'amber', icon: Clock }, { status: 'Accepted', tone: 'green', icon: BadgeCheck }].map(x => ({ ...x, label: 'Total ' + x.status, orders: filteredRows.value.filter(row => orderStatus(row) === x.status) })),
])
function currencyTotals(orders) {
  const totals = {}
  orders.forEach(row => { totals[row.currency_code] = (totals[row.currency_code] || 0) + Number(row.total) })
  return Object.entries(totals).map(([code, amount]) => ({ code, amount }))
}
const subtotal = computed(() => roundMoney(form.value.items.reduce((total, item) => total + lineSubtotal(item), 0)))
const taxTotal = computed(() => roundMoney(form.value.items.reduce((total, item) => total + roundMoney(item.sales_tax), 0)))
const grandTotal = computed(() => roundMoney(subtotal.value + taxTotal.value))
const actionable = computed(() => !!form.value.id && !editing.value && !busy.value && !invoiceFor(form.value) && !['Cancelled', 'Expired'].includes(orderStatus(form.value)) && !(form.value.expiry_at && new Date(form.value.expiry_at) < new Date()))
const canDecide = computed(() => actionable.value && ['Draft', 'Submitted'].includes(form.value.approval_status))
const canSubmit = computed(() => actionable.value && form.value.approval_status === 'Draft')
const canConvert = computed(() => actionable.value && form.value.approval_status === 'Approved')
const reportOrders = computed(() => rows.value.filter(row => !reportSeller.value || row.seller_name === reportSeller.value))
const reportDocuments = computed(() => reportOrders.value.flatMap(order => (order.documents || []).map(doc => ({ ...doc, order }))))
const quickReports = computed(() => [
  { key: 'documents', label: 'Purchases Documents', count: reportOrders.value.length + reportDocuments.value.filter(x => x.type !== 'Bill Payment').length },
  { key: 'payments', label: 'Payment List', count: reportDocuments.value.filter(x => x.type === 'Bill Payment').length },
  { key: 'unpaid', label: 'Unpaid Invoices', count: reportOrders.value.filter(x => invoiceFor(x) && balanceFor(x) > 0).length },
  { key: 'aging', label: 'AP Aging Report', count: reportOrders.value.filter(x => paymentStatus(x) === 'Overdue').length },
  { key: 'statement', label: 'Account Statement', count: reportDocuments.value.length },
  { key: 'taxes', label: 'Sellers Paid Sales Taxes', count: reportDocuments.value.filter(x => x.type === 'Bill Payment' && Number(x.sales_tax) > 0).length },
])
const modalTitle = computed(() => ({ audit: 'Purchase Order Audit Log', record: 'Record Financial Document', document: selectedDocument.value.type, report: reportData.value.title })[modal.value])
const slug = value => String(value || '').toLowerCase().replace(/\s+/g, '-')
const apiError = e => Object.values(e.response?.data?.errors || {})[0]?.[0] || e.response?.data?.message || e.message || 'Unable to complete this request.'
function normalize(order) {
  const base = blank()
  return { ...base, ...order, seller_id: order.seller_id ? Number(order.seller_id) : null, po_date: String(order.po_date || today()).slice(0, 10), expiry_at: localDateTime(order.expiry_at), expected_delivery_at: localDateTime(order.expected_delivery_at), seller_details: { ...base.seller_details, ...order.seller_details }, requester_details: { ...base.requester_details, ...order.requester_details }, delivery_details: { ...base.delivery_details, ...order.delivery_details }, items: (order.items || []).map(x => ({ ...x })), documents: order.documents || [], activities: order.activities || [], attachments: order.attachments || [] }
}
async function fetchAll(url, params = {}) {
  let current = 1, last = 1, result = []
  do {
    const { data } = await client.get(url, { params: { ...params, per_page: 100, page: current } })
    const body = data.data
    result.push(...(Array.isArray(body) ? body : body?.data || []))
    last = data.meta?.last_page || body?.last_page || 1
    current++
  } while (current <= last)
  return result
}
async function load() { rows.value = await fetchAll('/purchase-orders') }
async function loadOptions() {
  const options = await Promise.allSettled([fetchAll('/sellers', { is_active: 1 }), fetchAll('/service-items', { status: 1 }), fetchAll('/currencies'), fetchAll('/invoice-terms'), fetchAll('/payment-methods')])
  const value = index => options[index].status === 'fulfilled' ? options[index].value : []
  sellers.value = value(0)
  catalog.value = value(1).map(x => ({ key: x.id, name: x.item_name, code: x.item_no, uom: x.unit_of_measure || 'PCS', price: Number(x.price || 0) }))
  if (value(2).length) currencies.value = unique(value(2).map(x => x.currency_code))
  paymentTerms.value = unique(value(3).filter(x => Number(x.status) === 1).map(x => x.term_name))
  paymentMethods.value = unique(value(4).filter(x => x.status_active).map(x => x.name))
  if (options.some(x => x.status === 'rejected')) error.value = 'Some profile options could not be loaded. Refresh the page to retry.'
}
function confirmDiscard() { return !editing.value || confirm('Discard unsaved changes?') }
function startNew() { if (!confirmDiscard()) return; form.value = blank(); editing.value = true; view.value = 'detail'; error.value = ''; notice.value = '' }
async function openOrder(order, discard = true) {
  if (!order || (discard && !confirmDiscard())) return
  busy.value = true; error.value = ''
  try {
    const { data } = await client.get('/purchase-orders/' + order.id)
    form.value = normalize(data.data); reportSeller.value = form.value.seller_name; editing.value = false; view.value = 'detail'
  } catch (e) { error.value = apiError(e) } finally { busy.value = false }
}
function beginEdit() { editing.value = true; if (form.value.status === 'Accepted') form.value.status = 'Pending' }
function backToList() { if (!confirmDiscard()) return; view.value = 'list'; editing.value = false; error.value = '' }
async function cancelEdit() { if (!confirmDiscard()) return; if (form.value.id) await openOrder(form.value, false); else { editing.value = false; view.value = 'list' } }
function applySeller(seller) {
  form.value.seller_id = seller?.id || null
  form.value.seller_name = seller?.seller_name || ''; form.value.seller_no = seller ? 'SLR-' + String(seller.id).padStart(3, '0') : ''
  form.value.seller_details = Object.fromEntries(sellerFields.map(x => [x.key, seller?.[x.key] || '']))
  reportSeller.value = form.value.seller_name
}
function addItem() { form.value.items.push({ name: '', code: '', uom: 'PCS', quantity: 1, cost_rate: 0, sales_tax: 0 }) }
function applyCatalog(item, selected) { const found = selected || catalog.value.find(x => x.name === item.name); if (found) Object.assign(item, { name: found.name, code: found.code, uom: found.uom, cost_rate: found.price }) }
async function saveOrder() {
  if (!form.value.seller_name.trim()) { error.value = 'Select a seller.'; return }
  if (!form.value.items.length) { error.value = 'Add at least one item or service.'; return }
  busy.value = true; error.value = ''
  try {
    const payload = { ...form.value, expiry_at: form.value.expiry_at ? new Date(form.value.expiry_at).toISOString() : null, expected_delivery_at: form.value.expected_delivery_at ? new Date(form.value.expected_delivery_at).toISOString() : null }
    const { data } = form.value.id ? await client.put('/purchase-orders/' + form.value.id, payload) : await client.post('/purchase-orders', payload)
    form.value = normalize(data.data); editing.value = false; await load(); notice.value = 'Purchase order saved.'
  } catch (e) { error.value = apiError(e) } finally { busy.value = false }
}
async function workflow(action) {
  if (action === 'reject' && !confirm('Reject this purchase order?')) return
  busy.value = true; error.value = ''
  try { const { data } = await client.post('/purchase-orders/' + form.value.id + '/workflow', { action }); form.value = normalize(data.data); await load(); notice.value = { submit: 'Purchase order submitted for approval.', approve: 'Purchase order approved.', reject: 'Purchase order rejected.', convert: 'Purchase invoice created.' }[action] }
  catch (e) { error.value = apiError(e) } finally { busy.value = false }
}
async function deleteOrder() {
  if (!confirm('Delete purchase order ' + form.value.po_no + '?')) return
  busy.value = true; error.value = ''
  try { await client.delete('/purchase-orders/' + form.value.id); await load(); view.value = 'list'; editing.value = false; notice.value = 'Purchase order deleted.' } catch (e) { error.value = apiError(e) } finally { busy.value = false }
}
function duplicateOrder() {
  const copy = normalize(form.value)
  form.value = { ...copy, id: null, po_no: '', po_date: today(), expiry_at: '', expected_delivery_at: '', status: 'Pending', approval_status: 'Draft', payment_status: 'Unpaid', documents: [], activities: [], attachments: [], created_at: null, updated_at: null, creator: null }
  editing.value = true; notice.value = ''
}
function resetFilters() { filters.value = initialFilters(); page.value = 1 }
function sortBy(key) { sort.value = { key, direction: sort.value.key === key ? -sort.value.direction : 1 } }
function documentsOfType(type) { const docs = form.value.documents.filter(x => x.type === type); return docs.length ? docs : [{}] }
function showDocument(doc) { selectedDocument.value = doc; modal.value = 'document' }
function startDocument() { documentForm.value = { type: 'Bill Payment', number: '', date: today(), subtotal: Math.max(0, balanceFor(form.value, 'subtotal')), sales_tax: Math.max(0, balanceFor(form.value, 'sales_tax')), notes: '' }; modal.value = 'record' }
async function recordDocument() {
  busy.value = true; modalError.value = ''
  try { const { data } = await client.post('/purchase-orders/' + form.value.id + '/documents', documentForm.value); form.value = normalize(data.data); await load(); modal.value = ''; notice.value = 'Financial document recorded.' } catch (e) { modalError.value = apiError(e) } finally { busy.value = false }
}
async function uploadAttachment(event) {
  const file = event.target.files[0]; if (!file) return
  const body = new FormData(); body.append('file', file); busy.value = true; error.value = ''
  try { const { data } = await client.post('/purchase-orders/' + form.value.id + '/attachments', body, { headers: { 'Content-Type': 'multipart/form-data' } }); form.value = normalize(data.data); notice.value = 'File attached.' }
  catch (e) { error.value = apiError(e) } finally { busy.value = false; event.target.value = '' }
}
function downloadBlob(blob, name) { const url = URL.createObjectURL(blob), anchor = document.createElement('a'); anchor.href = url; anchor.download = name; anchor.click(); setTimeout(() => URL.revokeObjectURL(url), 1000) }
async function downloadAttachment(file) { try { const { data } = await client.get('/purchase-orders/' + form.value.id + '/attachments/' + file.id, { responseType: 'blob' }); downloadBlob(data, file.name) } catch (e) { error.value = apiError(e) } }
function activityDetails(value) { try { const details = typeof value === 'string' ? JSON.parse(value) : value; return Array.isArray(details) ? details.join(', ') : Object.entries(details || {}).map(([key, val]) => key + ': ' + val).join(', ') } catch { return '' } }
function closeModal() { if (!busy.value) modal.value = '' }
function openReport(report) {
  let headers = ['PO No.', 'Seller', 'Document', 'No.', 'Date', 'Currency', 'Total'], data = []
  const docs = reportDocuments.value
  if (report.key === 'documents') {
    data = reportOrders.value.map(x => [x.po_no, x.seller_name, 'Purchase Order', x.po_no, dateLabel(x.po_date), x.currency_code, money(x.total)])
    data.push(...docs.filter(x => x.type !== 'Bill Payment').map(x => [x.order.po_no, x.order.seller_name, x.type, x.number, dateLabel(x.date), x.order.currency_code, money(x.total)]))
  } else if (['payments', 'taxes', 'statement'].includes(report.key)) {
    headers = ['PO No.', 'Seller', 'Document', 'No.', 'Date', 'Currency', report.key === 'taxes' ? 'Sales Tax Paid' : 'Amount']
    data = docs.filter(x => report.key === 'statement' || (x.type === 'Bill Payment' && (report.key !== 'taxes' || Number(x.sales_tax) > 0))).map(x => [x.order.po_no, x.order.seller_name, x.type, x.number, dateLabel(x.date), x.order.currency_code, money(report.key === 'taxes' ? x.sales_tax : Number(x.total) * (report.key === 'statement' && x.type !== 'Purchase Invoice' ? -1 : 1))])
    if (report.key === 'statement') {
      const balances = {}
      reportOrders.value.forEach(x => { balances[x.currency_code] = (balances[x.currency_code] || 0) + balanceFor(x) })
      data.push(...Object.entries(balances).map(([code, total]) => ['', '', 'Balance', '', '', code, money(total)]))
    }
  } else {
    headers = ['PO No.', 'Seller', 'Invoice', 'Due Date', 'Currency', 'Outstanding', 'Aging']
    data = reportOrders.value.filter(x => invoiceFor(x) && balanceFor(x) > 0).map(x => {
      const inv = invoiceFor(x), days = Math.max(0, Math.floor((new Date(today() + 'T00:00:00') - new Date(inv.due_date + 'T00:00:00')) / 86400000))
      return [x.po_no, x.seller_name, inv.number, dateLabel(inv.due_date), x.currency_code, money(balanceFor(x)), days === 0 ? 'Current' : days <= 30 ? '1-30 days' : days <= 60 ? '31-60 days' : days <= 90 ? '61-90 days' : '90+ days']
    })
  }
  reportData.value = { title: report.label, headers, rows: data }; modal.value = 'report'
}
const listExport = () => ({ headers: listColumns.map(x => x.label), data: filteredRows.value.map(x => [x.po_no, dateLabel(x.po_date), money(x.total) + ' ' + x.currency_code, dateTimeLabel(x.expiry_at), x.creator?.name || x.requested_by, x.seller_no, x.seller_name, invoiceFor(x)?.number || '', dateLabel(invoiceFor(x)?.date), invoiceFor(x) ? money(invoiceFor(x).total) : '', invoiceFor(x) ? money(variance(x)) : '', money(sellerBalance(x)) + ' ' + x.currency_code, orderStatus(x)]) })
async function writeExcel(title, headers, data) {
  try {
    const { default: ExcelJS } = await import('exceljs')
    const workbook = new ExcelJS.Workbook(), sheet = workbook.addWorksheet(title.replace(/[\\/*?:[\]]/g, '-').slice(0, 31))
    sheet.addRow(headers); data.forEach(row => sheet.addRow(row))
    sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } }; sheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF102A83' } }
    sheet.columns.forEach(column => { column.width = 23 }); sheet.views = [{ state: 'frozen', ySplit: 1 }]
    downloadBlob(new Blob([await workbook.xlsx.writeBuffer()], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }), title + '.xlsx')
  } catch (e) { error.value = apiError(e) }
}
function exportExcel() {
  if (view.value === 'list') { const data = listExport(); return writeExcel('Purchase Orders', data.headers, data.data) }
  return writeExcel(form.value.po_no || 'Purchase Order', ['Item / Service', 'Code / SKU', 'UOM', 'Qty', 'Cost Rate', 'Subtotal', 'Sales Tax', 'Total'], [...form.value.items.map(x => [x.name, x.code, x.uom, Number(x.quantity), Number(x.cost_rate), lineSubtotal(x), Number(x.sales_tax), lineTotal(x)]), ['Total (' + form.value.currency_code + ')', '', '', '', '', subtotal.value, taxTotal.value, grandTotal.value]])
}
function exportReport() { return writeExcel(reportData.value.title, reportData.value.headers, reportData.value.rows) }
function printPage() { window.print() }
async function savePdf() {
  const { default: jsPDF } = await import('jspdf')
  const pdf = new jsPDF(), width = 180; let y = 18
  function text(value, size = 10) { pdf.setFontSize(size); const lines = pdf.splitTextToSize(String(value || '-'), width); lines.forEach(line => { if (y > 278) { pdf.addPage(); y = 18 } pdf.text(line, 15, y); y += size * .5 + 2 }); y += 2 }
  text(view.value === 'list' ? 'Purchase Orders - Issued to Sellers' : 'Purchase Order ' + (form.value.po_no || ''), 18)
  if (view.value === 'list') filteredRows.value.forEach(row => text([row.po_no, dateLabel(row.po_date), row.seller_name, row.currency_code + ' ' + money(row.total), orderStatus(row)].join(' | ')))
  else {
    const x = form.value
    text('Date: ' + dateLabel(x.po_date) + ' | Status: ' + x.status + ' | Approval: ' + x.approval_status)
    text('Seller: ' + x.seller_name + ' (' + x.seller_no + ')', 12)
    sellerFields.forEach(field => text(field.label + ': ' + (x.seller_details[field.key] || '-')))
    text('Requested by: ' + x.requested_by + ' | Department: ' + x.department + ' | Project: ' + x.project)
    requesterFields.forEach(field => text(field.label + ': ' + (x.requester_details[field.key] || '-')))
    text('Expiry: ' + dateTimeLabel(x.expiry_at) + ' | Expected delivery: ' + dateTimeLabel(x.expected_delivery_at))
    text('Payment term: ' + x.payment_term + ' | Payment method: ' + x.payment_method)
    x.items.forEach(item => text([item.name, item.code || '-', item.quantity + ' ' + item.uom + ' x ' + money(item.cost_rate), 'Tax ' + money(item.sales_tax), 'Total ' + money(lineTotal(item))].join(' | ')))
    text('Subtotal: ' + money(subtotal.value) + ' | Sales Tax: ' + money(taxTotal.value))
    text('Grand Total (' + x.currency_code + '): ' + money(grandTotal.value), 14)
    text('Terms & Conditions: ' + (x.terms || '-'))
    text('Delivery: ' + x.delivery_location + ' | ' + x.delivery_details.contact_person + ' | ' + x.delivery_details.phone)
    text('Delivery Instructions: ' + (x.notes || '-'))
  }
  pdf.save((view.value === 'list' ? 'purchase-orders' : form.value.po_no || 'purchase-order') + '.pdf')
}
function emailOrder() {
  const list = view.value === 'list', subject = list ? 'Purchase Orders' : 'Purchase Order ' + (form.value.po_no || '')
  const body = list ? filteredRows.value.map(x => x.po_no + ' - ' + x.seller_name + ': ' + x.currency_code + ' ' + money(x.total)).join('\n') : subject + '\nSeller: ' + form.value.seller_name + '\nDate: ' + dateLabel(form.value.po_date) + '\n\n' + form.value.items.map(x => x.name + ': ' + x.quantity + ' ' + x.uom + ', ' + money(lineTotal(x))).join('\n') + '\n\nGrand Total: ' + form.value.currency_code + ' ' + money(grandTotal.value) + '\nPayment Term: ' + form.value.payment_term + '\nPayment Method: ' + form.value.payment_method
  window.location.href = 'mailto:' + (list ? '' : encodeURIComponent(form.value.seller_details.email || '')) + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body)
}
watch([filters, perPage], () => { page.value = 1 }, { deep: true })
watch(pageCount, value => { page.value = Math.min(page.value, value) })
watch(modal, async value => { modalError.value = ''; if (value) { await nextTick(); modalPanel.value?.focus() } })
onMounted(async () => { busy.value = true; try { await Promise.all([load(), loadOptions()]) } catch (e) { error.value = apiError(e) } finally { busy.value = false } })
</script>

<style src="../assets/purchase-orders.css"></style>
