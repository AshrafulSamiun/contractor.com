<!-- Legacy bank page retained temporarily for reference.
<template>
  <AccountLayout bare>
  <div class="container py-4 pm-bank-page">
    <section class="card border-0 shadow-sm mb-4"><div class="card-body d-flex justify-content-between align-items-center"><div><small class="text-uppercase text-muted fw-bold">Profiles Module</small><h2 class="mb-1">🏦 Banks</h2><span class="text-muted">Dashboard &gt; Profiles &gt; Account Holders &gt; Banks</span></div><div><button class="btn btn-outline-primary me-2" @click="editing=false">☷ List</button><button class="btn btn-primary" @click="startNew">＋ New Bank</button></div></div></section>
    <section v-if="!editing" class="card border-primary shadow-sm mb-4"><div class="card-body"><h5>Summary</h5><div class="row g-3"><div v-for="item in summary" :key="item.label" class="col-md-3"><div class="border rounded p-3"><small class="text-muted">{{ item.label }}</small><div class="fs-4 fw-bold text-primary">{{ item.value }}</div></div></div></div></div></section>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>
    <section class="card shadow-sm border-0 mb-4"><div class="card-body"><div class="row g-3"><div class="col-md-4"><input v-model="search" class="form-control" placeholder="Search bank name or account number" /></div><div class="col-md-3"><select v-model="statusFilter" class="form-select"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div></div></div></section>
    <section v-if="editing" class="mb-4"><h2>🏦 {{ form.id ? 'Edit Bank' : 'New Bank' }}</h2><form class="row g-3" @submit.prevent="save"><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>BANK INFO.</h5><label>Bank No.</label><input v-model="form.bank_no" class="form-control mb-2" required /><label>Bank Name</label><input v-model="form.bank_name" class="form-control mb-2" required /><label>Status</label><select v-model="form.status_active" class="form-select mb-2"><option :value="true">Active</option><option :value="false">Inactive</option></select><label>Currency</label><select v-model="form.currency_id" class="form-select"><option :value="null">Select currency</option><option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.currency_code }}</option></select></div></div></div><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>ACCOUNT INFO.</h5><label>Linked GL Account</label><input v-model="form.linked_gl_account_id" type="number" class="form-control mb-2" /><label>Account Name</label><input v-model="form.account_name" class="form-control mb-2" required /><label>Account Number</label><input v-model="form.account_number" class="form-control mb-2" required /><label>Opening Balance</label><input v-model="form.opening_balance" type="number" step=".01" class="form-control mb-2" /><label>Opening Balance Date</label><input v-model="form.opening_balance_date" type="date" class="form-control" /></div></div></div><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>CONTACT INFORMATION</h5><label>Contact Person</label><input v-model="form.contact_person" class="form-control mb-2" /><label>Phone Number</label><input v-model="form.phone_number" class="form-control mb-2" /><label>Email Address</label><input v-model="form.email" type="email" class="form-control mb-2" /><label>Website</label><input v-model="form.website" type="url" class="form-control" /></div></div></div><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>SYSTEM INFORMATION</h5><p class="text-muted">Created and modified details are recorded automatically when this bank is saved.</p></div></div></div><div class="col-12"><button class="btn btn-primary me-2" :disabled="saving">{{ saving ? 'Saving...' : 'Save Bank' }}</button><button class="btn btn-outline-secondary" type="button" @click="editing=false">Cancel</button></div></form></section>
    <section v-if="!editing" class="card shadow-sm border-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th @click="sortKey='bank_no';sortAsc=!sortAsc">No. ↕</th><th @click="sortKey='bank_name';sortAsc=!sortAsc">Bank Name ↕</th><th @click="sortKey='status_active';sortAsc=!sortAsc">Status ↕</th><th @click="sortKey='account_number';sortAsc=!sortAsc">Account No. ↕</th><th class="text-end" @click="sortKey='opening_balance';sortAsc=!sortAsc">Opening Balance ↕</th><th>Action</th></tr></thead><tbody><tr v-for="bank in filteredBanks" :key="bank.id"><td>{{ bank.bank_no }}</td><td>{{ bank.bank_name }}</td><td><span class="badge" :class="bank.status_active ? 'text-bg-success' : 'text-bg-secondary'">{{ bank.status_active ? 'Active' : 'Inactive' }}</span></td><td>{{ bank.account_number }}</td><td class="text-end">{{ bank.opening_balance }}</td><td class="text-end"><button class="btn btn-sm btn-outline-primary me-2" @click="edit(bank)">Edit</button><button class="btn btn-sm btn-outline-danger" @click="remove(bank)">Delete</button></td></tr><tr v-if="!filteredBanks.length"><td colspan="6" class="text-center text-muted py-4">No banks found.</td></tr></tbody></table></div></section>
  </div>
  </AccountLayout>
</template>
<script setup>
import { computed, onMounted, ref } from 'vue'
import client from '../api/client'
import AccountLayout from '../components/AccountLayout.vue'
import './Banks.css'
import './ProfileTableLayout.css'
const banks=ref([]), currencies=ref([]), search=ref(''), statusFilter=ref(''), editing=ref(false), saving=ref(false), error=ref(''), sortKey=ref('bank_name'), sortAsc=ref(true)
const blank=()=>({status_active:true,currency_id:null,opening_balance:0})
const form=ref(blank())
const filteredBanks=computed(()=>banks.value.filter(b=>`${b.bank_name} ${b.bank_no} ${b.account_number}`.toLowerCase().includes(search.value.toLowerCase())&&(!statusFilter.value||(statusFilter.value==='active'?b.status_active:!b.status_active))).sort((a,b)=>String(a[sortKey.value]||'').localeCompare(String(b[sortKey.value]||''))* (sortAsc.value?1:-1)))
const summary=computed(()=>[{label:'Total Banks',value:banks.value.length},{label:'Active Banks',value:banks.value.filter(b=>b.status_active).length},{label:'Inactive Banks',value:banks.value.filter(b=>!b.status_active).length},{label:'Current Balance',value:banks.value.reduce((n,b)=>n+Number(b.opening_balance||0),0).toLocaleString()}])
const load=async()=>{const [{data:list},{data:options}]=await Promise.all([client.get('/banks'),client.get('/banks/options')]);banks.value=list.data;currencies.value=options.data.currencies}
const startNew=()=>{form.value=blank();editing.value=true}
const edit=(bank)=>{form.value={...bank,opening_balance_date:bank.opening_balance_date?.slice(0,10)||null};editing.value=true}
const save=async()=>{saving.value=true;error.value='';try{const request=form.value.id?client.put(`/banks/${form.value.id}`,form.value):client.post('/banks',form.value);await request;editing.value=false;await load()}catch(e){error.value=e.response?.data?.message||'Unable to save bank.'}finally{saving.value=false}}
const remove=async(bank)=>{if(!confirm(`Delete ${bank.bank_name}?`))return;await client.delete(`/banks/${bank.id}`);await load()}
onMounted(()=>load().catch(()=>error.value='Unable to load banks.'))
</script>
-->

<template>
  <AccountLayout bare>
    <main class="bank-page">
      <header class="bank-head">
        <div class="bank-heading">
          <span class="bank-mark"><Landmark :size="30" /></span>
          <div><h1>{{ editing ? (form.id ? 'Edit Bank' : 'New Bank') : 'Banks' }}</h1><p>{{ editing ? 'Create and maintain bank account details' : 'Manage all bank accounts' }}</p></div>
        </div>
        <div class="bank-actions">
          <button v-if="!editing" class="bank-btn secondary" type="button" @click="importInput?.click()"><Upload :size="17" /> Import</button>
          <input ref="importInput" hidden type="file" accept=".csv,text/csv" @change="showImportNotice" />
          <button v-if="!editing" class="bank-btn primary" type="button" @click="startNew"><CirclePlus :size="17" /> New Bank</button>
          <button v-else class="bank-btn secondary" type="button" @click="editing = false"><List :size="17" /> Bank List</button>
        </div>
      </header>

      <div v-if="error" class="bank-alert"><CircleAlert :size="18" /><span>{{ error }}</span><button type="button" @click="error = ''"><X :size="16" /></button></div>

      <template v-if="!editing">
        <section class="bank-summary">
          <h2>Summary</h2>
          <div class="summary-grid">
            <article v-for="item in summary" :key="item.label">
              <span class="summary-icon" :class="item.tone"><component :is="item.icon" :size="24" /></span>
              <div><small>{{ item.label }}</small><strong>{{ item.value }}</strong><p>{{ item.note }}</p></div>
            </article>
          </div>
        </section>

        <section class="bank-list-panel">
          <div class="bank-filters">
            <label class="control search"><Search :size="18" /><input v-model="search" type="search" placeholder="Search banks by name, account no..." /></label>
            <label class="control"><ListFilter :size="17" /><select v-model="statusFilter"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
            <label class="control"><Landmark :size="17" /><select v-model="currencyFilter"><option value="">All Currencies</option><option v-for="currency in currencies" :key="currency.id" :value="String(currency.id)">{{ currency.currency_code }}</option></select></label>
            <button class="bank-btn secondary" type="button" @click="exportCsv"><Download :size="17" /> Export</button>
          </div>

          <div class="bank-table-wrap">
            <table class="bank-table">
              <thead><tr><th class="number">No.</th><th><button @click="sortBy('bank_name')">Bank Name <ArrowUpDown :size="13" /></button></th><th><button @click="sortBy('status_active')">Status <ArrowUpDown :size="13" /></button></th><th><button @click="sortBy('account_number')">Account No. <ArrowUpDown :size="13" /></button></th><th><button @click="sortBy('opening_balance')">Balance <ArrowUpDown :size="13" /></button></th><th class="center">View Profile</th><th class="center">Action</th></tr></thead>
              <tbody>
                <tr v-for="(bank, index) in paginatedBanks" :key="bank.id">
                  <td class="number">{{ pageStart + index + 1 }}</td>
                  <td><div class="bank-name"><span><Landmark :size="17" /></span><div><b>{{ bank.bank_name }}</b><small>{{ bank.bank_no }}</small></div></div></td>
                  <td><em class="status" :class="bank.status_active ? 'active' : 'inactive'">{{ bank.status_active ? 'Active' : 'Inactive' }}</em></td>
                  <td class="account-no">{{ bank.account_number }}</td>
                  <td class="balance">{{ money(bank.opening_balance, bank.currency_id) }}</td>
                  <td class="center"><button class="icon-btn view" title="View bank" @click="edit(bank)"><Eye :size="19" /></button></td>
                  <td class="center"><div class="menu-wrap"><button class="icon-btn" title="Actions" @click.stop="openMenu = openMenu === bank.id ? null : bank.id"><EllipsisVertical :size="19" /></button><div v-if="openMenu === bank.id" class="row-menu"><button @click="edit(bank)"><Pencil :size="15" /> Edit</button><button class="danger" @click="remove(bank)"><Trash2 :size="15" /> Delete</button></div></div></td>
                </tr>
                <tr v-if="!paginatedBanks.length"><td colspan="7" class="empty"><Landmark :size="32" /><b>No banks found</b><span>Change the filters or add a new bank.</span></td></tr>
              </tbody>
            </table>
          </div>

          <footer class="bank-table-footer">
            <span>Showing {{ filteredBanks.length ? pageStart + 1 : 0 }} to {{ Math.min(pageStart + pageSize, filteredBanks.length) }} of {{ filteredBanks.length }} entries</span>
            <nav v-if="totalPages > 1"><button :disabled="page === 1" @click="page--"><ChevronLeft :size="17" /></button><button v-for="number in visiblePages" :key="number" :class="{ active: page === number }" @click="page = number">{{ number }}</button><button :disabled="page === totalPages" @click="page++"><ChevronRight :size="17" /></button></nav>
            <select v-model.number="pageSize"><option :value="10">10</option><option :value="25">25</option><option :value="50">50</option></select>
          </footer>
        </section>
      </template>

      <form v-else class="bank-form" @submit.prevent="save">
        <section class="form-block"><h2>Bank Info.</h2><div class="form-grid">
          <label><span>Bank No.</span><input :value="form.bank_no || 'Auto generated'" disabled /></label>
          <label><span>Bank Name <b>*</b></span><input v-model.trim="form.bank_name" required placeholder="Enter bank name" /></label>
          <label><span>Status <b>*</b></span><select v-model="form.status_active"><option :value="true">Active</option><option :value="false">Inactive</option></select></label>
          <label><span>Currency</span><select v-model="form.currency_id"><option :value="null">Select currency</option><option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.currency_code }} - {{ currency.currency_name }}</option></select></label>
          <label><span>Linked GL Account</span><input v-model="form.linked_gl_account_id" type="number" min="1" placeholder="Enter GL account ID" /></label>
        </div></section>

        <section class="form-block"><h2>Account Info.</h2><div class="form-grid">
          <label><span>Account Name <b>*</b></span><input v-model.trim="form.account_name" required placeholder="Enter account name" /></label>
          <label><span>Account Number <b>*</b></span><input v-model.trim="form.account_number" required placeholder="Enter account number" /></label>
          <label><span>Opening Balance</span><input v-model="form.opening_balance" type="number" step="0.01" placeholder="Enter opening balance (e.g. 0.00)" /></label>
          <label><span>Opening Balance Date</span><input v-model="form.opening_balance_date" type="date" /></label>
        </div></section>

        <section class="form-block"><h2>Contact Information</h2><div class="form-grid">
          <label><span>Contact Person</span><input v-model.trim="form.contact_person" placeholder="Enter contact person" /></label>
          <label><span>Phone Number</span><input v-model.trim="form.phone_number" type="tel" placeholder="Enter phone number" /></label>
          <label><span>Email Address</span><input v-model.trim="form.email" type="email" placeholder="Enter email address" /></label>
          <label><span>Website</span><input v-model.trim="form.website" type="url" placeholder="Enter website (optional)" /></label>
        </div></section>

        <section class="form-block system"><h2>System Information</h2><div class="form-grid">
          <label><span>Created By</span><input :value="form.created_by ? `User #${form.created_by}` : 'Auto'" disabled /></label>
          <label><span>Created Date</span><input :value="dateTime(form.created_at)" disabled /></label>
          <label><span>Modified By</span><input :value="form.updated_by ? `User #${form.updated_by}` : 'Auto'" disabled /></label>
          <label><span>Modified Date</span><input :value="dateTime(form.updated_at)" disabled /></label>
        </div></section>

        <footer class="form-actions"><button class="bank-btn secondary" type="button" @click="startNew"><CirclePlus :size="17" /> New</button><button class="bank-btn primary" :disabled="saving"><Save :size="17" /> {{ saving ? 'Saving...' : 'Save' }}</button><button v-if="form.id" class="bank-btn delete" type="button" @click="remove(form)"><Trash2 :size="17" /> Delete</button><button class="bank-btn secondary" type="button" @click="printPage"><Printer :size="17" /> Print</button><button class="bank-btn secondary cancel" type="button" @click="editing = false"><X :size="17" /> Cancel</button></footer>
      </form>
    </main>
  </AccountLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { ArrowUpDown, ChevronLeft, ChevronRight, CircleAlert, CircleDollarSign, CirclePlus, Download, EllipsisVertical, Eye, Landmark, List, ListFilter, Pencil, Printer, Save, Search, Trash2, Upload, WalletCards, X } from 'lucide-vue-next'
import client from '../api/client'
import AccountLayout from '../components/AccountLayout.vue'

const banks = ref([]), currencies = ref([]), search = ref(''), statusFilter = ref(''), currencyFilter = ref('')
const editing = ref(false), saving = ref(false), error = ref(''), sortKey = ref('bank_name'), sortAsc = ref(true)
const page = ref(1), pageSize = ref(10), openMenu = ref(null), importInput = ref(null)
const blank = () => ({ status_active: true, currency_id: null, linked_gl_account_id: null, opening_balance: 0, opening_balance_date: new Date().toISOString().slice(0, 10) })
const form = ref(blank())

const filteredBanks = computed(() => banks.value.filter((bank) => {
  const term = search.value.trim().toLowerCase()
  return (!term || `${bank.bank_name} ${bank.bank_no} ${bank.account_number} ${bank.account_name}`.toLowerCase().includes(term))
    && (!statusFilter.value || (statusFilter.value === 'active' ? bank.status_active : !bank.status_active))
    && (!currencyFilter.value || String(bank.currency_id) === currencyFilter.value)
}).sort((a, b) => {
  const left = sortKey.value === 'opening_balance' ? Number(a[sortKey.value] || 0) : String(a[sortKey.value] ?? '')
  const right = sortKey.value === 'opening_balance' ? Number(b[sortKey.value] || 0) : String(b[sortKey.value] ?? '')
  return (typeof left === 'number' ? left - right : left.localeCompare(right)) * (sortAsc.value ? 1 : -1)
}))
const totalBalance = computed(() => banks.value.reduce((sum, bank) => sum + Number(bank.opening_balance || 0), 0))
const activeCount = computed(() => banks.value.filter((bank) => bank.status_active).length)
const summary = computed(() => [
  { label: 'Total Balance', value: money(totalBalance.value), note: 'Across all accounts', icon: WalletCards, tone: 'blue' },
  { label: 'Average Balance', value: money(banks.value.length ? totalBalance.value / banks.value.length : 0), note: 'Per bank account', icon: CircleDollarSign, tone: 'green' },
  { label: 'Active Banks', value: activeCount.value, note: `${banks.value.length} total bank accounts`, icon: Landmark, tone: 'purple' },
  { label: 'Inactive Banks', value: banks.value.length - activeCount.value, note: 'Require review', icon: CircleAlert, tone: 'orange' },
])
const totalPages = computed(() => Math.max(1, Math.ceil(filteredBanks.value.length / pageSize.value)))
const pageStart = computed(() => (page.value - 1) * pageSize.value)
const paginatedBanks = computed(() => filteredBanks.value.slice(pageStart.value, pageStart.value + pageSize.value))
const visiblePages = computed(() => { const start = Math.max(1, Math.min(page.value - 1, totalPages.value - 2)); return Array.from({ length: Math.min(3, totalPages.value) }, (_, i) => start + i) })
watch([search, statusFilter, currencyFilter, pageSize], () => { page.value = 1 })

function money(value, currencyId = null) { const code = currencies.value.find((c) => Number(c.id) === Number(currencyId))?.currency_code || 'USD'; try { return new Intl.NumberFormat('en-US', { style: 'currency', currency: code }).format(Number(value || 0)) } catch { return Number(value || 0).toFixed(2) } }
function dateTime(value) { return value ? new Date(value).toLocaleString() : 'Auto' }
function sortBy(key) { if (sortKey.value === key) sortAsc.value = !sortAsc.value; else { sortKey.value = key; sortAsc.value = true } }
async function load() { const [{ data: list }, { data: options }] = await Promise.all([client.get('/banks'), client.get('/banks/options')]); banks.value = list.data; currencies.value = options.data.currencies }
function startNew() { form.value = blank(); editing.value = true; openMenu.value = null; window.scrollTo({ top: 0, behavior: 'smooth' }) }
function edit(bank) { form.value = { ...bank, opening_balance_date: bank.opening_balance_date?.slice(0, 10) || null }; editing.value = true; openMenu.value = null; window.scrollTo({ top: 0, behavior: 'smooth' }) }
async function save() { saving.value = true; error.value = ''; try { const payload = { ...form.value, linked_gl_account_id: form.value.linked_gl_account_id || null }; await (form.value.id ? client.put(`/banks/${form.value.id}`, payload) : client.post('/banks', payload)); editing.value = false; await load() } catch (e) { error.value = e.response?.data?.message || 'Unable to save bank.' } finally { saving.value = false } }
async function remove(bank) { openMenu.value = null; if (!confirm(`Delete ${bank.bank_name}?`)) return; try { await client.delete(`/banks/${bank.id}`); editing.value = false; await load() } catch (e) { error.value = e.response?.data?.message || 'Unable to delete bank.' } }
function exportCsv() { const rows = [['Bank No.', 'Bank Name', 'Status', 'Account Name', 'Account Number', 'Opening Balance'], ...filteredBanks.value.map((b) => [b.bank_no, b.bank_name, b.status_active ? 'Active' : 'Inactive', b.account_name, b.account_number, b.opening_balance])]; const csv = rows.map((row) => row.map((cell) => `"${String(cell ?? '').replaceAll('"', '""')}"`).join(',')).join('\r\n'); const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' })); link.download = 'banks.csv'; link.click(); URL.revokeObjectURL(link.href) }
function showImportNotice(event) { if (event.target.files?.length) error.value = 'CSV import requires column mapping and is not enabled yet.'; event.target.value = '' }
function printPage() { window.print() }
onMounted(() => load().catch(() => { error.value = 'Unable to load banks.' }))
</script>

