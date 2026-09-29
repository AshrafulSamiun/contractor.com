<template>
  <AccountLayout bare>
    <main class="po-page sq-page" :aria-busy="busy">
      <header class="po-header">
        <div class="po-heading"><FileText/><div><h1>Estimation / Quotes</h1><p>Accounting / Sales</p></div></div>
        <div v-if="view === 'list'" class="po-header-search"><Search/><input v-model="filters.search" aria-label="Search estimates" placeholder="Search estimate no., project or customer"></div>
        <div class="po-actions"><button v-if="view === 'list'" class="po-btn primary" @click="startNew"><Plus/> New Estimate</button><button v-else class="po-btn" @click="backToList"><ArrowLeft/> Back to list</button></div>
      </header>
      <div v-if="error" class="po-alert error" role="alert">{{ error }}</div>
      <div v-if="notice" class="po-alert success" role="status">{{ notice }}</div>

      <template v-if="view === 'list'">
        <section class="po-metrics"><article v-for="metric in metrics" :key="metric.label" :class="metric.tone"><component :is="metric.icon"/><div><h2>{{ metric.label }}</h2><strong>{{ metric.value }}</strong></div></article></section>
        <form class="po-filters" @submit.prevent="page=1">
          <label>Status<select v-model="filters.status"><option value="">All Status</option><option v-for="option in statuses" :key="option.value" :value="option.value">{{ option.label }}</option></select></label>
          <label>From Date<input v-model="filters.from" type="date"></label>
          <label>To Date<input v-model="filters.to" type="date"></label>
          <button class="po-btn primary" type="submit"><Search/> Search</button><button class="po-btn" type="button" @click="resetFilters"><RotateCcw/> Reset</button>
        </form>
        <section class="po-list"><header class="po-section-head"><h2>Estimation / Quotes List</h2><button class="po-btn primary" @click="startNew"><Plus/> New Estimate</button></header>
          <div class="po-table-scroll"><table class="po-list-table"><thead><tr><th>#</th><th>Estimate No.</th><th>Estimate Date</th><th>Project / Job</th><th>Customer Name</th><th>Amount</th><th>Status</th><th>Expiry Date</th><th>Actions</th></tr></thead><tbody>
            <tr v-for="(row,index) in pagedRows" :key="row.id"><td>{{ pageStart+index+1 }}</td><td><button class="po-link" @click="openEstimate(row)">{{ row.estimation_no }}</button></td><td>{{ dateLabel(row.issue_date) }}</td><td>{{ row.job_site?.name || row.job_description || '-' }}</td><td>{{ customerName(row) }}</td><td class="po-number">{{ money(row.total) }} {{ currencyCode(row) }}</td><td><span class="po-pill" :class="statusClass(row)">{{ statusName(row) }}</span></td><td>{{ dateLabel(row.expire_date) }}</td><td><div class="po-actions"><button class="po-icon" title="View estimate" @click="openEstimate(row)"><Eye/></button><button class="po-icon" title="Edit estimate" @click="editEstimate(row)"><Pencil/></button><button class="po-icon" title="Duplicate estimate" @click="duplicateEstimate(row)"><Copy/></button><button class="po-icon danger" title="Delete estimate" @click="deleteEstimate(row)"><Trash2/></button></div></td></tr>
            <tr v-if="!pagedRows.length"><td colspan="9" class="po-empty">{{ busy ? 'Loading estimates...' : 'No sales estimates found.' }}</td></tr>
          </tbody></table></div>
          <footer class="po-pagination"><span>Showing {{ filteredRows.length ? pageStart+1 : 0 }} to {{ Math.min(pageStart+perPage,filteredRows.length) }} of {{ filteredRows.length }} entries</span><div class="po-actions"><select v-model.number="perPage" aria-label="Estimates per page" @change="page=1"><option :value="15">15</option><option :value="30">30</option><option :value="50">50</option></select><button class="po-btn compact" :disabled="page===1" @click="page--">Previous</button><b>{{ page }} / {{ pageCount }}</b><button class="po-btn compact" :disabled="page===pageCount" @click="page++">Next</button></div></footer>
          <div class="po-action-bar"><b>Action Bar:</b><button class="po-btn primary" @click="printPage"><Printer/> Print</button><button class="po-btn" @click="emailEstimate"><Mail/> Email</button></div>
        </section>
      </template>

      <div v-else class="po-workspace">
        <section class="po-document"><header class="po-document-head"><div class="po-heading"><FileText/><h2>Sales Quote / Estimation</h2></div><div class="po-actions"><b>{{ form.estimation_no || 'New estimate' }}</b><span class="po-pill" :class="statusClass(form)">{{ statusName(form) }}</span><button class="po-icon" title="Print estimate" @click="printPage"><Printer/></button><button class="po-icon" title="Email estimate" @click="emailEstimate"><Mail/></button></div></header>
          <div class="po-document-body">
            <div class="po-details-grid">
              <section><h3>Customer Information</h3><div v-if="editing" class="po-field"><label>Customer</label><i>:</i><select v-model.number="form.customer_id" @change="applyCustomer"><option :value="null">Select customer</option><option v-for="item in customers" :key="item.id" :value="item.id">{{ item.company_name || item.account_name }}</option></select></div><div class="po-field"><label>Customer Name</label><i>:</i><b>{{ customerName(form) }}</b></div><div class="po-field"><label>Phone</label><i>:</i><b>{{ form.customer?.contact || '-' }}</b></div><div class="po-field"><label>Email</label><i>:</i><b>{{ form.customer?.email || '-' }}</b></div><div class="po-field"><label>Address</label><i>:</i><b>{{ form.customer?.address || '-' }}</b></div></section>
              <section><h3>Project Information</h3><div class="po-field"><label>Project / Job</label><i>:</i><select v-if="editing" v-model.number="form.job_site_id" @change="applySite"><option :value="null">Select project</option><option v-for="site in sites" :key="site.id" :value="site.id">{{ site.job_site_name }}</option></select><b v-else>{{ form.job_site?.name || '-' }}</b></div><div class="po-field"><label>Site Address</label><i>:</i><b>{{ form.job_site?.address || '-' }}</b></div><div class="po-field"><label>Description</label><i>:</i><input v-if="editing" v-model="form.job_description"><b v-else>{{ form.job_description || '-' }}</b></div><div class="po-field"><label>Scope of Work</label><i>:</i><textarea v-if="editing" v-model="form.scope_of_work" rows="2"/><b v-else>{{ form.scope_of_work || '-' }}</b></div></section>
              <section><h3>Quote Details</h3><div class="po-field"><label>Estimate No.</label><i>:</i><b>{{ form.estimation_no || 'Assigned on save' }}</b></div><div class="po-field"><label>Quote Date</label><i>:</i><input v-if="editing" v-model="form.issue_date" type="date"><b v-else>{{ dateLabel(form.issue_date) }}</b></div><div class="po-field"><label>Valid Until</label><i>:</i><input v-if="editing" v-model="form.expire_date" type="date"><b v-else>{{ dateLabel(form.expire_date) }}</b></div><div class="po-field"><label>Currency</label><i>:</i><select v-if="editing" v-model.number="form.currency"><option v-for="(label,id) in currencies" :key="id" :value="Number(id)">{{ label }}</option></select><b v-else>{{ currencyCode(form) }}</b></div><div class="po-field"><label>Status</label><i>:</i><select v-if="editing" v-model.number="form.status"><option v-for="item in statuses" :key="item.value" :value="item.value">{{ item.label }}</option></select><b v-else>{{ statusName(form) }}</b></div><div class="po-field"><label>Payment Terms</label><i>:</i><input v-if="editing" v-model="form.payment_term"><b v-else>{{ form.payment_term || '-' }}</b></div></section>
            </div>
            <section class="po-items"><div class="po-section-head"><h3>Items / Services</h3><button v-if="editing" class="po-btn compact primary" @click="addItem"><Plus/> Add item</button></div><div class="po-table-scroll"><table><thead><tr><th>Item / Service</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Sub Total</th><th>Tax %</th><th>Total</th><th v-if="editing"></th></tr></thead><tbody><tr v-for="(item,index) in form.details" :key="index"><td><input v-if="editing" v-model="item.item_name" aria-label="Item name"><span v-else>{{ item.item_name }}</span></td><td><input v-if="editing" v-model="item.item_description" aria-label="Item description"><span v-else>{{ item.item_description || '-' }}</span></td><td><input v-if="editing" v-model.number="item.quantity" type="number" min="0.01" step="0.01" aria-label="Quantity"><span v-else>{{ item.quantity }}</span></td><td><input v-if="editing" v-model.number="item.unit_price" type="number" min="0" step="0.01" aria-label="Unit price"><span v-else>{{ money(item.unit_price) }}</span></td><td>{{ money(lineSubtotal(item)) }}</td><td><input v-if="editing" v-model.number="item.sale_tax_percentage" type="number" min="0" max="100" step="0.01" aria-label="Tax percentage"><span v-else>{{ item.sale_tax_percentage || 0 }}</span></td><td>{{ money(lineTotal(item)) }}</td><td v-if="editing"><button class="po-icon danger" title="Remove item" :disabled="form.details.length===1" @click="form.details.splice(index,1)"><Trash2/></button></td></tr></tbody><tfoot><tr><th colspan="4">Total</th><th>{{ money(subtotal) }}</th><th>{{ money(taxTotal) }} tax</th><th>{{ money(grandTotal) }}</th><th v-if="editing"></th></tr></tfoot></table></div></section>
            <div class="po-bottom-grid"><section><h3>Terms &amp; Conditions</h3><textarea v-if="editing" v-model="form.scope_of_work" rows="4" aria-label="Terms and conditions"/><p v-else class="po-terms">{{ form.scope_of_work || '-' }}</p></section><section><h3>Notes to Customer</h3><textarea v-if="editing" v-model="form.notes_to_customer" rows="4" aria-label="Notes to customer"/><p v-else class="po-terms">{{ form.notes_to_customer || '-' }}</p></section><section class="po-totals"><div><span>Sub Total</span><strong>{{ money(subtotal) }}</strong></div><div><span>Sales Tax</span><strong>{{ money(taxTotal) }}</strong></div><div><span>Grand Total ({{ currencyCode(form) }})</span><strong>{{ money(grandTotal) }}</strong></div></section></div>
          </div>
          <footer class="po-document-footer"><div class="po-actions"><button class="po-btn" @click="startNew"><Plus/> New</button><button class="po-btn" :disabled="editing || !form.id" @click="editing=true"><Pencil/> Edit</button><button class="po-btn danger" :disabled="!form.id || busy" @click="deleteEstimate(form)"><Trash2/> Delete</button><button class="po-btn primary" :disabled="!editing || busy" @click="saveEstimate"><Save/> {{ busy ? 'Saving...' : 'Save' }}</button><button v-if="editing" class="po-btn" @click="cancelEdit"><X/> Cancel</button><button class="po-btn" @click="printPage"><Printer/> Print</button><button class="po-btn" @click="savePdf"><FileDown/> Save PDF</button><button class="po-btn" @click="emailEstimate"><Mail/> Email</button><button class="po-btn" :disabled="!form.id" @click="duplicateEstimate(form)"><Copy/> Duplicate</button></div></footer>
        </section>
        <aside class="po-sidebar"><section><h3>Sales Tracker</h3><div class="po-table-scroll"><table class="po-tracker"><thead><tr><th>Doc Type</th><th>Doc No.</th><th>Subtotal</th><th>Sales Tax</th><th>Total</th></tr></thead><tbody><tr><td>Sales Estimate</td><td>{{ form.estimation_no || '-' }}</td><td>{{ money(subtotal) }}</td><td>{{ money(taxTotal) }}</td><td>{{ money(grandTotal) }}</td></tr></tbody></table></div></section><section><h3>Quick Action</h3><div class="sq-side-actions"><button class="po-btn primary" :disabled="!form.id || editing" @click="convertToSalesOrder"><FileCheck/> Convert to Sales Order</button></div></section><section><h3>Quote Information</h3><div class="po-field"><label>Status</label><i>:</i><b>{{ statusName(form) }}</b></div><div class="po-field"><label>Project</label><i>:</i><b>{{ form.job_site?.name || '-' }}</b></div><div class="po-field"><label>Prepared By</label><i>:</i><b>{{ form.creator?.name || '-' }}</b></div><div class="po-field"><label>Last Updated</label><i>:</i><b>{{ dateLabel(form.updated_at) }}</b></div></section></aside>
      </div>

      <section v-if="view === 'detail'" class="sq-print">
        <header><strong v-if="Number(form.status)===2" class="sq-stamp">APPROVED</strong><h1>{{ company.name }}</h1><h2>Sales Quote / Estimation</h2><p>{{ company.address }}</p><p>{{ company.phone }} &nbsp; {{ company.email }} &nbsp; {{ company.website }}</p></header>
        <div class="sq-print-grid"><div><h3>1- Sales Quote / Estimation</h3><div class="sq-print-top"><table><tbody><tr><th colspan="2">Customer:</th></tr><tr><th>Acc. No</th><td>{{ form.customer_id || '-' }}</td></tr><tr><th>Name</th><td>{{ customerName(form) }}</td></tr><tr><th>Phone</th><td>{{ form.customer?.contact || '-' }}</td></tr><tr><th>Email</th><td>{{ form.customer?.email || '-' }}</td></tr><tr><th>Address</th><td>{{ form.customer?.address || '-' }}</td></tr></tbody></table><table><tbody><tr><th>Quote No</th><td>{{ form.estimation_no || '-' }}</td></tr><tr><th>Quote Date</th><td>{{ dateLabel(form.issue_date) }}</td></tr><tr><th>Valid Until</th><td>{{ dateLabel(form.expire_date) }}</td></tr><tr><th>Payment Terms</th><td>{{ form.payment_term || '-' }}</td></tr><tr><th>Currency</th><td>{{ currencyCode(form) }}</td></tr><tr><th>Status</th><td>{{ statusName(form) }}</td></tr></tbody></table></div><h3>Details</h3><table><thead><tr><th>No</th><th>Item / Service</th><th>Qty</th><th>Unit Price</th><th>Tax %</th><th>Total</th></tr></thead><tbody><tr v-for="(item,index) in form.details" :key="index"><td>{{ index+1 }}</td><td>{{ item.item_name }}</td><td>{{ item.quantity }}</td><td>{{ money(item.unit_price) }}</td><td>{{ item.sale_tax_percentage || 0 }}</td><td>{{ money(lineTotal(item)) }}</td></tr></tbody><tfoot><tr><th colspan="5">Total</th><th>{{ money(grandTotal) }}</th></tr></tfoot></table><div class="sq-print-top"><section><h3>Terms &amp; Conditions</h3><p>{{ form.scope_of_work || '-' }}</p></section><section><h3>Notes</h3><p>{{ form.notes_to_customer || '-' }}</p></section></div></div><aside><h3>SALES TRACKER</h3><table><thead><tr><th>Doc Type</th><th>Doc No</th><th>Subtotal</th><th>Tax</th><th>Total</th></tr></thead><tbody><tr><td>Sales Estimation</td><td>{{ form.estimation_no }}</td><td>{{ money(subtotal) }}</td><td>{{ money(taxTotal) }}</td><td>{{ money(grandTotal) }}</td></tr></tbody></table><h3>Info</h3><p>Quote Status: {{ statusName(form) }}</p><p>Currency: {{ currencyCode(form) }}</p><p>Project Name: {{ form.job_site?.name || '-' }}</p><p>Project Site Address: {{ form.job_site?.address || '-' }}</p><h3>Last Update</h3><p>{{ dateLabel(form.updated_at) }}</p></aside></div>
      </section>
    </main>
  </AccountLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { FileText, Search, Plus, ArrowLeft, RotateCcw, Eye, Pencil, Copy, Trash2, Printer, Mail, Save, FileDown, X, FileCheck, Clock, CheckCircle2, CircleX, Hourglass } from 'lucide-vue-next'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { today, money, dateLabel, roundMoney } from '../utils/purchaseOrders'

const router = useRouter()
const rows = ref([])
const customers = ref([])
const sites = ref([])
const view = ref('list')
const editing = ref(false)
const busy = ref(false)
const error = ref('')
const notice = ref('')
const page = ref(1)
const perPage = ref(15)
const filters = ref({ search: '', status: '', from: '', to: '' })
const statuses = [{value:1,label:'Pending'},{value:2,label:'Customer Approved'},{value:3,label:'Canceled'},{value:4,label:'Expired'}]
const currencies = {1:'USD',2:'EUR',3:'GBP',4:'BDT'}
const company = computed(() => ({
  name: authState.user?.company_name || authState.user?.business_name || 'Company Name',
  address: authState.user?.company_address || '',
  phone: authState.user?.contact_primary_phone || '',
  email: authState.user?.contact_business_email || authState.user?.email || '',
  website: authState.user?.website || '',
}))
const blankItem = () => ({ item_name:'', item_description:'', quantity:1, unit_price:0, sale_tax_percentage:0 })
const blank = () => ({id:null,estimation_no:'',issue_date:today(),expire_date:'',currency:1,status:1,job_type:6,customer_id:null,customer:null,job_site_id:null,job_site:null,job_description:'',scope_of_work:'',notes_to_customer:'',payment_term:'',details:[blankItem()]})
const form = ref(blank())
const filteredRows = computed(() => rows.value.filter(row => {
  const search = [row.estimation_no,row.job_site?.name,row.job_description,customerName(row)].join(' ').toLowerCase()
  return (!filters.value.search || search.includes(filters.value.search.toLowerCase())) && (!filters.value.status || Number(row.status)===Number(filters.value.status)) && (!filters.value.from || row.issue_date>=filters.value.from) && (!filters.value.to || row.issue_date<=filters.value.to)
}))
const pageCount = computed(() => Math.max(1,Math.ceil(filteredRows.value.length/perPage.value)))
const pageStart = computed(() => (page.value-1)*perPage.value)
const pagedRows = computed(() => filteredRows.value.slice(pageStart.value,pageStart.value+perPage.value))
const metrics = computed(() => [{label:'Total Estimates',value:rows.value.length,icon:FileText,tone:''},{label:'Pending',value:rows.value.filter(x=>Number(x.status)===1).length,icon:Clock,tone:'amber'},{label:'Customer Approved',value:rows.value.filter(x=>Number(x.status)===2).length,icon:CheckCircle2,tone:'green'},{label:'Canceled',value:rows.value.filter(x=>Number(x.status)===3).length,icon:CircleX,tone:'red'},{label:'Expired',value:rows.value.filter(x=>Number(x.status)===4).length,icon:Hourglass,tone:''}])
const subtotal = computed(() => roundMoney(form.value.details.reduce((sum,item)=>sum+lineSubtotal(item),0)))
const taxTotal = computed(() => roundMoney(form.value.details.reduce((sum,item)=>sum+lineSubtotal(item)*Number(item.sale_tax_percentage||0)/100,0)))
const grandTotal = computed(() => roundMoney(subtotal.value+taxTotal.value-Number(form.value.discount||0)))
watch(filteredRows,()=>{if(page.value>pageCount.value)page.value=pageCount.value})
function lineSubtotal(item){return roundMoney(Number(item.quantity||0)*Number(item.unit_price||0))}
function lineTotal(item){return roundMoney(lineSubtotal(item)*(1+Number(item.sale_tax_percentage||0)/100))}
function customerName(row){return row.customer?.company_name||row.customer?.name||'-'}
function currencyCode(row){return currencies[Number(row.currency)]||row.currency_label||'USD'}
function statusName(row){return statuses.find(x=>x.value===Number(row.status))?.label||'Pending'}
function statusClass(row){return {1:'pending',2:'approved',3:'cancelled',4:'expired'}[Number(row.status)]||'pending'}
function normalize(row){return {...blank(),...row,expire_date:String(row.expire_date||'').slice(0,10),details:(row.details||[]).map(x=>({...x}))}}
function apiError(e){return Object.values(e.response?.data?.errors||{})[0]?.[0]||e.response?.data?.message||e.message||'Request failed.'}
async function load(){
  busy.value=true
  try {
    const {data}=await client.get('/estimations',{params:{per_page:100}})
    rows.value=data.data?.data||[]
  } catch(e){error.value=apiError(e)} finally{busy.value=false}
}
async function loadOptions(){
  const results=await Promise.allSettled([client.get('/estimations/customers'),client.get('/estimations/job-sites')])
  if(results[0].status==='fulfilled')customers.value=results[0].value.data.data||[]
  if(results[1].status==='fulfilled')sites.value=results[1].value.data.data||[]
}
function applyCustomer(){const item=customers.value.find(x=>x.id===Number(form.value.customer_id));form.value.customer=item?{name:item.account_name,company_name:item.company_name,email:item.email,contact:item.cell_phone,address:[item.house_number,item.street_number,item.city,item.state,item.country,item.zip_code].filter(Boolean).join(', ')}:null}
function applySite(){const item=sites.value.find(x=>x.id===Number(form.value.job_site_id));form.value.job_site=item?{name:item.job_site_name,address:item.address}:null}
function startNew(){form.value=blank();view.value='detail';editing.value=true;error.value='';notice.value=''}
function backToList(){view.value='list';editing.value=false}
async function openEstimate(row){busy.value=true;error.value='';try{const {data}=await client.get('/estimations/'+row.id);form.value=normalize(data.data);view.value='detail';editing.value=false}catch(e){error.value=apiError(e)}finally{busy.value=false}}
async function editEstimate(row){await openEstimate(row);if(view.value==='detail')editing.value=true}
function duplicateEstimate(row){form.value=normalize({...row,id:null,estimation_no:'',issue_date:today()});view.value='detail';editing.value=true}
async function cancelEdit(){if(form.value.id)await openEstimate(form.value);else backToList()}
function addItem(){form.value.details.push(blankItem())}
function resetFilters(){filters.value={search:'',status:'',from:'',to:''};page.value=1}
async function saveEstimate(){
  error.value=''
  if(!form.value.expire_date||form.value.expire_date<=form.value.issue_date){error.value='Valid Until must be after Quote Date.';return}
  if(form.value.details.some(x=>!x.item_name.trim()||Number(x.quantity)<=0)){error.value='Each line needs an item name and positive quantity.';return}
  const start=form.value.issue_date+' 09:00:00'
  const end=form.value.expire_date+' 17:00:00'
  const payload={...form.value,expire_date:form.value.expire_date+' 23:59:00',schedule_start_date:start,schedule_end_date:end,sub_total:subtotal.value,tax:taxTotal.value,total:grandTotal.value,job_type:Number(form.value.job_type||6),currency:Number(form.value.currency||1),status:Number(form.value.status||1),details:form.value.details.map(x=>({item_name:x.item_name,item_description:x.item_description||'',quantity:Number(x.quantity),unit_price:Number(x.unit_price||0),sale_tax_percentage:Number(x.sale_tax_percentage||0)}))}
  busy.value=true
  try{const {data}=form.value.id?await client.put('/estimations/'+form.value.id,payload):await client.post('/estimations',payload);form.value=normalize(data.data);editing.value=false;notice.value='Sales estimate saved.';await load()}catch(e){error.value=apiError(e)}finally{busy.value=false}
}
async function deleteEstimate(row){if(!row?.id||!confirm('Delete '+row.estimation_no+'?'))return;busy.value=true;try{await client.delete('/estimations/'+row.id);backToList();notice.value='Sales estimate deleted.';await load()}catch(e){error.value=apiError(e)}finally{busy.value=false}}
function convertToSalesOrder(){router.push({path:'/accounting/sales-orders',query:{estimation_id:form.value.id}})}
function printPage(){window.print()}
function savePdf(){printPage()}
function emailEstimate(){window.location.href='mailto:?subject='+encodeURIComponent('Sales Quote '+(form.value.estimation_no||''))}
onMounted(()=>{load();loadOptions()})
</script>

<style src="../assets/purchase-orders.css"></style>
<style>
.sq-page .po-filters{grid-template-columns:repeat(3,minmax(120px,1fr)) auto auto}
.sq-page .po-items td:first-child{min-width:170px}.sq-page .po-items td:nth-child(2){min-width:140px}
.sq-page .po-bottom-grid{grid-template-columns:1fr 1fr .9fr}
.sq-page .po-bottom-grid textarea{margin:10px;width:calc(100% - 20px)}
.sq-page .po-totals>div{display:flex;justify-content:space-between;gap:10px;padding:10px}
.sq-page .po-totals strong{font-size:14px}
.sq-side-actions{padding:12px}
.sq-print{display:none}
@media(max-width:1000px){.sq-page .po-bottom-grid{grid-template-columns:1fr}.sq-page .po-filters{grid-template-columns:repeat(2,minmax(0,1fr)) auto auto}}
@media(max-width:650px){.sq-page .po-filters{grid-template-columns:1fr 1fr}.sq-page .po-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media print{
  body:has(.sq-page) .pm-app-sidebar,body:has(.sq-page) .pm-dashboard-topbar,.sq-page>.po-header,.sq-page>.po-alert,.sq-page>.po-workspace,.sq-page>.po-list,.sq-page>.po-filters,.sq-page>.po-metrics{display:none!important}
  .sq-page{padding:0!important;background:#fff!important}.sq-print{display:block!important;color:#111;font:11px Arial,sans-serif}.sq-print>header{position:relative;text-align:center;background:#eaf3ff;border:1px solid #98abc3;padding:8px}.sq-print h1{font-size:27px;margin:0}.sq-print h2{font-size:20px;color:#0b2386;margin:3px}.sq-print p{margin:6px}.sq-stamp{position:absolute;left:24px;top:28px;color:green;border:4px solid green;padding:8px;transform:rotate(-8deg);font-size:25px}.sq-print-grid{display:grid;grid-template-columns:2fr .95fr;gap:14px;margin-top:10px}.sq-print-grid>aside{background:#f0f6ff;padding:6px}.sq-print-top{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:8px 0 20px}.sq-print table{width:100%;border-collapse:collapse}.sq-print th,.sq-print td{border:1px solid #b7bfcc;padding:6px;text-align:left}.sq-print thead th,.sq-print h3{background:#0c248b;color:#fff}.sq-print h3{font-size:12px;padding:5px;margin:12px 0 3px}.sq-print-grid>div>h3:first-child{background:none;color:#111;font-size:15px}.sq-print section p{white-space:pre-wrap;min-height:65px;border:1px solid #bcc5d4;padding:8px}.sq-print tr{break-inside:avoid}
}
</style>
