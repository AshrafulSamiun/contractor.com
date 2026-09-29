<template>
  <div class="pm-dashboard-layout">
    <AppSidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />
    <main class="pm-dashboard-main">
      <header class="pm-dashboard-topbar">
        <button class="pm-icon-btn pm-menu-btn" type="button" @click="sidebarOpen = !sidebarOpen">☰</button>
        <div class="pm-topbar-search"><input class="form-control" placeholder="Search..." @keyup.enter="load" /></div>
      </header>
      <section class="pm-dashboard-content">
        <div class="container pm-ops-page timesheet-page">
          <div class="title-row">
            <h1>Timesheet <small v-if="mode === 'list'">- List</small><small v-else>- {{ editingId ? 'Update Entry' : 'New Entry' }}</small></h1>
            <button v-if="mode !== 'list'" class="btn btn-outline-secondary" type="button" @click="showList">☰ List</button>
          </div>

          <template v-if="mode === 'list'">
            <section class="pm-card filter-card">
              <h2>FILTERS</h2>
              <div class="filter-grid">
                <label>From Date<input v-model="filters.from" class="form-control" type="date" /></label>
                <label>To Date<input v-model="filters.to" class="form-control" type="date" /></label>
                <label>Department<input v-model="filters.department" class="form-control" placeholder="All" /></label>
                <label>Employee<input v-model="filters.employee" class="form-control" placeholder="All" /></label>
                <label>Job Order No.<input v-model="filters.job_order" class="form-control" placeholder="Select or Enter" /></label>
                <div class="filter-actions"><button class="btn btn-primary" type="button" @click="search">⌕&nbsp; Search</button><button class="btn btn-outline-secondary" type="button" @click="clearFilters">↻ Reset</button></div>
              </div>
            </section>

            <section class="pm-card summary-card">
              <h2>TIMESHEET SUMMARY <span v-if="filters.from || filters.to">({{ filters.from || '...' }} - {{ filters.to || '...' }})</span></h2>
              <div class="summary-grid">
                <div><small>Total Employees</small><strong>{{ summary.employees }}</strong></div><div><small>Total Regular Hours</small><strong>{{ summary.regular.toFixed(2) }}</strong></div><div><small>Total OT Hours</small><strong>{{ summary.overtime.toFixed(2) }}</strong></div><div><small>Total Hours Worked</small><strong>{{ summary.total.toFixed(2) }}</strong></div><div><small>Total Stat Holiday Hours</small><strong>{{ summary.stat.toFixed(2) }}</strong></div><div><small>Total Days Worked</small><strong>{{ summary.days }}</strong></div><div><small>Total Days</small><strong>{{ summary.days }}</strong></div>
              </div>
            </section>

            <section class="pm-card list-card">
              <div class="list-heading"><h2>Timesheets</h2><button class="btn btn-primary" type="button" @click="newTimesheet">＋ New Timesheet</button></div>
              <div class="table-wrap"><table><thead><tr><th>No.</th><th><button @click="setSort('employee_name')">Employee Name {{ arrow('employee_name') }}</button></th><th>Employee ID</th><th>Department</th><th>Regular Hours<br>(8:00 AM - 5:00 PM)</th><th>OT Hours<br>(After 5:00 PM)</th><th>Stat Holiday<br>Hours</th><th><button @click="setSort('total_hours')">Total Hours {{ arrow('total_hours') }}</button></th><th>Days Worked</th><th>Job Order No.</th><th>View</th></tr></thead>
                <tbody><tr v-for="(row,index) in rows" :key="row.id"><td>{{ meta.from + index }}</td><td>{{ row.employee_name }}</td><td>{{ row.employee_code || '-' }}</td><td>{{ row.role_title || '-' }}</td><td>{{ number(row.regular_hours) }}</td><td>{{ number(row.overtime_hours) }}</td><td>{{ statHours(row) }}</td><td>{{ number(row.total_hours) }}</td><td>{{ workedDays(row) }}</td><td>{{ jobOrders(row) || '-' }}</td><td><button class="view-btn" type="button" @click="edit(row)">◉</button></td></tr><tr v-if="!rows.length"><td colspan="11" class="empty">No timesheets found.</td></tr></tbody>
                <tfoot v-if="rows.length"><tr><td colspan="4">TOTAL</td><td>{{ summary.regular.toFixed(2) }}</td><td>{{ summary.overtime.toFixed(2) }}</td><td>{{ summary.stat.toFixed(2) }}</td><td>{{ summary.total.toFixed(2) }}</td><td>{{ summary.days }}</td><td colspan="2"></td></tr></tfoot></table></div>
              <div class="pagination-bar"><span>Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} records</span><div><button :disabled="meta.current_page <= 1" @click="go(meta.current_page-1)">‹</button><button v-for="page in pages" :key="page" :class="{active: page === meta.current_page}" @click="go(page)">{{ page }}</button><button :disabled="meta.current_page >= meta.last_page" @click="go(meta.current_page+1)">›</button></div></div>
            </section>
          </template>

          <template v-else>
            <section class="pm-card form-card"><h2><b>1.</b> TIMESHEET INFO</h2><div class="form-grid five"><label>Timesheet No.<input class="form-control readonly" :value="form.timesheet_no || 'Generated on save'" readonly /></label><label>Week Starting<input v-model="form.week_start" class="form-control" type="date" /></label><label>Week Ending<input v-model="form.week_end" class="form-control" type="date" /></label><label>Department<input v-model="form.role_title" class="form-control" placeholder="Maintenance" /></label><label>Status<select v-model="form.status" class="form-control"><option>draft</option><option>submitted</option><option>approved</option><option>rejected</option></select></label></div></section>
            <section class="pm-card form-card"><h2><b>2.</b> EMPLOYEE INFO</h2><div class="form-grid four"><label>Employee / Staff<input v-model="form.employee_name" class="form-control" placeholder="Employee name" /></label><label>Employee ID<input v-model="form.employee_code" class="form-control" placeholder="EMP-1005" /></label><label>Position<input v-model="form.role_title" class="form-control" placeholder="Maintenance Technician" /></label><label>Phone<input v-model="form.phone" class="form-control" placeholder="604-555-5678" /></label></div></section>
            <section class="pm-card form-card"><h2><b>3.</b> TIMESHEET DETAILS</h2><div class="table-wrap edit-table"><table><thead><tr><th>Date</th><th>Day</th><th>Day Type</th><th>From</th><th>To</th><th>Regular Hours</th><th>OT Hours</th><th>Total Hours</th><th>Service Location</th><th>Job Order No.</th></tr></thead><tbody><tr v-for="(entry,index) in form.entries_json" :key="index"><td><input v-model="entry.date" type="date" /></td><td>{{ dayName(entry.date) }}</td><td><select v-model="entry.day_type"><option>Regular Day</option><option>Overtime</option><option>Stat Holiday</option></select></td><td><input v-model="entry.from_time" type="time" /></td><td><input v-model="entry.to_time" type="time" /></td><td><input v-model.number="entry.hours" type="number" min="0" step="0.25" /></td><td><input v-model.number="entry.overtime" type="number" min="0" step="0.25" /></td><td>{{ number(Number(entry.hours || 0) + Number(entry.overtime || 0)) }}</td><td><input v-model="entry.service_location" placeholder="Service location" /></td><td><input v-model="entry.job_order" placeholder="JO-2026-0145" /></td></tr></tbody><tfoot><tr><td colspan="5">TOTAL</td><td>{{ formTotals.regular.toFixed(2) }}</td><td>{{ formTotals.overtime.toFixed(2) }}</td><td>{{ formTotals.total.toFixed(2) }}</td><td colspan="2"></td></tr></tfoot></table></div></section>
            <section class="pm-card form-card"><h2>TIMESHEET SUMMARY</h2><div class="summary-grid form-summary"><div><small>Total Regular Hours</small><strong>{{ formTotals.regular.toFixed(2) }}</strong></div><div><small>Total OT Hours</small><strong>{{ formTotals.overtime.toFixed(2) }}</strong></div><div><small>Total Hours Worked</small><strong>{{ formTotals.total.toFixed(2) }}</strong></div><div><small>Total Stat Holiday Hours</small><strong>{{ formTotals.stat.toFixed(2) }}</strong></div><div><small>Total Days Worked</small><strong>{{ formTotals.days }}</strong></div><div><small>Total Days</small><strong>{{ form.entries_json.length }}</strong></div></div></section>
            <section class="pm-card form-card"><h2><b>4.</b> APPROVAL</h2><div class="form-grid five"><label>Submitted By<input class="form-control readonly" :value="form.employee_name" readonly /></label><label>Date Submitted<input v-model="form.submitted_date" class="form-control" type="date" /></label><label>Approved By<input class="form-control readonly" :value="form.approved_by || 'Select Approver'" readonly /></label><label>Date Approved<input class="form-control readonly" :value="form.approved_at || ''" readonly /></label><label>Notes<textarea v-model="form.notes" class="form-control" placeholder="Enter notes (if any)..."></textarea></label></div></section>
            <div class="form-actions"><button class="btn btn-outline-secondary" type="button" @click="showList">Cancel</button><button class="btn btn-outline-secondary" type="button" @click="resetForm">Reset</button><button class="btn btn-primary" type="button" :disabled="saving" @click="save(false)">Save</button><button class="btn btn-primary" type="button" :disabled="saving" @click="save(true)">Save & New</button><button class="btn btn-primary" type="button" :disabled="saving" @click="saveAndList">Save & Out</button><button v-if="editingId" class="btn btn-outline-danger" type="button" @click="destroyRecord">Delete</button></div>
          </template>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'

const sidebarOpen = ref(false), mode = ref('list'), editingId = ref(null), saving = ref(false), rows = ref([])
const meta = reactive({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const filters = reactive({ from: '', to: '', department: '', employee: '', job_order: '', sort_by: 'week_start', sort_direction: 'desc' })
const blankEntry = () => ({ date: '', day_type: 'Regular Day', from_time: '08:00', to_time: '17:00', hours: 0, overtime: 0, service_location: '', job_order: '' })
const blankForm = () => ({ timesheet_no: '', week_start: '', week_end: '', employee_name: '', employee_code: '', role_title: '', phone: '', status: 'draft', notes: '', submitted_date: '', approved_by: '', approved_at: '', entries_json: Array.from({ length: 7 }, blankEntry) })
const form = reactive(blankForm())
const number = value => Number(value || 0).toFixed(2)
const entries = row => Array.isArray(row.entries_json) ? row.entries_json : []
const jobOrders = row => [...new Set(entries(row).map(x => x.job_order).filter(Boolean))].join(', ')
const workedDays = row => entries(row).filter(x => Number(x.hours || 0) + Number(x.overtime || 0) > 0).length
const statHours = row => entries(row).filter(x => x.day_type === 'Stat Holiday').reduce((sum,x) => sum + Number(x.hours || 0) + Number(x.overtime || 0), 0).toFixed(2)
const dayName = date => date ? new Date(`${date}T12:00:00`).toLocaleDateString(undefined,{weekday:'short'}) : '-'
const formTotals = computed(() => { const active = form.entries_json.filter(x => Number(x.hours || 0) + Number(x.overtime || 0) > 0); const regular = form.entries_json.reduce((s,x)=>s + Number(x.hours || 0),0); const overtime = form.entries_json.reduce((s,x)=>s + Number(x.overtime || 0),0); return { regular, overtime, total: regular + overtime, stat: form.entries_json.filter(x => x.day_type === 'Stat Holiday').reduce((s,x)=>s + Number(x.hours || 0) + Number(x.overtime || 0),0), days: active.length } })
const summary = computed(() => ({ employees: new Set(rows.value.map(x => x.employee_name).filter(Boolean)).size, regular: rows.value.reduce((s,x)=>s+Number(x.regular_hours||0),0), overtime: rows.value.reduce((s,x)=>s+Number(x.overtime_hours||0),0), total: rows.value.reduce((s,x)=>s+Number(x.total_hours||0),0), stat: rows.value.reduce((s,x)=>s+Number(statHours(x)),0), days: rows.value.reduce((s,x)=>s+workedDays(x),0) }))
const pages = computed(() => Array.from({ length: meta.last_page }, (_, i) => i + 1))
async function load(page = meta.current_page) { try { const { data } = await client.get('/workforce/timesheets', { params: { ...filters, page, per_page: 10 } }); rows.value = data.data || []; Object.assign(meta, data.meta || {}) } catch (error) { rows.value = []; Object.assign(meta, { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 }) } }
function search(){ load(1) } function go(page){ if(page >= 1 && page <= meta.last_page) load(page) }
function clearFilters(){ Object.assign(filters, { from:'', to:'', department:'', employee:'', job_order:'', sort_by:'week_start', sort_direction:'desc' }); load(1) }
function setSort(column){ filters.sort_direction = filters.sort_by === column && filters.sort_direction === 'asc' ? 'desc' : 'asc'; filters.sort_by = column; load(1) }
function arrow(column){ return filters.sort_by === column ? (filters.sort_direction === 'asc' ? '↑' : '↓') : '↕' }
function resetForm(){ Object.assign(form, blankForm()); editingId.value = null }
function newTimesheet(){ resetForm(); mode.value = 'form' }
function edit(row){ Object.assign(form, blankForm(), row, { entries_json: entries(row).length ? entries(row).map(x => ({ ...blankEntry(), ...x })) : Array.from({ length: 7 }, blankEntry) }); editingId.value = row.id; mode.value = 'form' }
function showList(){ mode.value = 'list'; load(meta.current_page) }
async function save(andNew){ saving.value = true; try { const payload = { ...form, regular_hours: formTotals.value.regular, overtime_hours: formTotals.value.overtime, total_hours: formTotals.value.total }; const response = editingId.value ? await client.put(`/workforce/timesheets/${editingId.value}`, payload) : await client.post('/workforce/timesheets', payload); if (andNew) { resetForm() } else { Object.assign(form, response.data.data); editingId.value = response.data.data.id } } finally { saving.value = false } }
async function saveAndList(){ await save(false); showList() }
async function destroyRecord(){ if (!confirm('Delete this timesheet?')) return; await client.delete(`/workforce/timesheets/${editingId.value}`); showList() }
onMounted(() => load(1))
</script>

<style scoped>
.timesheet-page{max-width:1600px;padding-bottom:2rem;color:#071b58}.title-row{display:flex;align-items:center;justify-content:space-between;margin:0 0 1rem}.title-row h1{margin:0;color:#080808;font-size:2.1rem;font-weight:800}.title-row h1 small{font-size:.72em;font-weight:600}.pm-card{border:1px solid #dce3ef;border-radius:6px;background:#fff;margin-bottom:.8rem;padding:1rem 1.25rem;box-shadow:none}.pm-card h2{margin:0 0 .85rem;font-size:1rem;font-weight:800;color:#071b58}.pm-card h2 b{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:4px;background:#061d5b;color:#fff;margin-right:.5rem}.filter-grid,.form-grid{display:grid;gap:1.25rem}.filter-grid{grid-template-columns:repeat(5,minmax(140px,1fr));align-items:end}.form-grid.five{grid-template-columns:repeat(5,minmax(150px,1fr))}.form-grid.four{grid-template-columns:repeat(4,minmax(150px,1fr))}label{display:flex;flex-direction:column;gap:.35rem;font-size:.78rem;font-weight:700;color:#08123a}.form-control{min-height:39px;border:1px solid #cfd8e7;border-radius:4px;font-size:.85rem}.readonly{background:#f4f4f4}.filter-actions{display:flex;gap:.7rem;align-items:end}.btn{min-height:39px;padding:.45rem 1.25rem;border-radius:4px;font-size:.85rem;font-weight:600}.btn-primary{background:#061d5b;border-color:#061d5b}.summary-card{padding-bottom:1.1rem}.summary-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:0}.summary-grid>div{border:1px solid #dce3ef;min-height:85px;padding:.8rem;text-align:center;display:flex;flex-direction:column;justify-content:center}.summary-grid small{font-weight:700;font-size:.72rem;color:#071b58}.summary-grid strong{font-size:1.55rem;line-height:1.3;color:#082083}.list-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem}.list-heading h2{margin:0}.table-wrap{overflow-x:auto;border:1px solid #dce3ef}table{width:100%;border-collapse:collapse;font-size:.82rem}th,td{border:1px solid #dce3ef;padding:.62rem .5rem;text-align:center;vertical-align:middle}th{color:#071b58;font-weight:800;background:#fff;white-space:nowrap}th button{border:0;background:transparent;color:#071b58;font-weight:800;font-size:.82rem}tbody td{color:#08123a}tfoot td{font-weight:800;background:#fafcff}.empty{padding:1.5rem!important;color:#667085}.view-btn{min-width:35px;border:1px solid #cbd6e7;border-radius:4px;background:#fff;color:#061d5b;padding:.25rem}.pagination-bar{display:flex;align-items:center;justify-content:space-between;padding-top:.8rem;color:#111b3e;font-size:.82rem;background:transparent}.pagination-bar button{min-width:34px;height:34px;margin-left:.3rem;border:1px solid #d8e0eb;border-radius:4px;background:#fff;color:#071b58}.pagination-bar button.active{border-color:#071b58;font-weight:800}.pagination-bar button:disabled{opacity:.45}.edit-table input,.edit-table select{width:100%;min-height:31px;border:1px solid #d4dce9;border-radius:3px;padding:.2rem;font-size:.74rem}.form-summary{grid-template-columns:repeat(6,1fr)}textarea.form-control{min-height:39px}.form-actions{display:flex;justify-content:flex-end;gap:1rem;padding:.1rem 0}.btn-outline-danger{color:#ba1821;border-color:#ba1821}@media(max-width:1100px){.filter-grid,.form-grid.five{grid-template-columns:repeat(3,1fr)}.summary-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:700px){.title-row h1{font-size:1.5rem}.filter-grid,.form-grid.five,.form-grid.four,.summary-grid,.form-summary{grid-template-columns:1fr}.filter-actions,.form-actions{flex-wrap:wrap}.pagination-bar{align-items:flex-start;gap:.5rem;flex-direction:column}}
</style>
