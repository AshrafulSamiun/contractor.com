<template>
  <div class="pm-dashboard-layout" :class="{ 'pm-sidebar-hidden': sidebarHidden }">
    <AppSidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />
    <main class="pm-dashboard-main">
      <header class="pm-dashboard-topbar">
        <button class="pm-icon-btn pm-menu-btn" type="button" @click="sidebarOpen = !sidebarOpen" aria-label="Open menu">☰</button>
        <div class="pm-topbar-search"><input v-model="filters.search" class="form-control" placeholder="Search staff attendance..." @keyup.enter="load" /></div>
        <div class="pm-topbar-actions"><button class="pm-icon-btn" type="button" aria-label="Notifications">♧</button><button class="pm-icon-btn" type="button" aria-label="Help">?</button></div>
      </header>

      <section class="pm-dashboard-content">
        <div class="container pm-ops-page attendance-page">
          <div class="attendance-title"><h2>Staff Attendance</h2><span>- Daily Attendance Report</span></div>

          <section class="pm-card attendance-filter">
            <div class="attendance-section-title">⌕&nbsp; Filter / Report Info</div>
            <div class="attendance-filter-grid">
              <label>Report Date<input v-model="filters.date" class="form-control" type="date" /></label>
              <label>Location / Site<input v-model="filters.location" class="form-control" placeholder="All locations" /></label>
              <label>Customer / Job Site<input v-model="filters.customer" class="form-control" placeholder="All customers" /></label>
              <label>Department<input v-model="filters.department" class="form-control" placeholder="All departments" /></label>
              <label>Shift<input v-model="filters.shift" class="form-control" placeholder="All shifts" /></label>
              <div class="attendance-filter-actions"><button class="btn btn-primary" type="button" @click="load">Search</button><button class="btn btn-outline-secondary" type="button" @click="clearFilters">Clear</button><button class="btn btn-outline-secondary" type="button" @click="exportCsv">Export</button></div>
            </div>
          </section>

          <section class="pm-card attendance-list">
            <div class="attendance-list-head"><div class="attendance-section-title">▣&nbsp; Daily Attendance Records <span v-if="filters.date">— {{ displayDate(filters.date) }}</span> ({{ records.length }})</div><button class="btn btn-primary" type="button" @click="openCreate">+ Add Attendance</button></div>
            <div class="table-responsive">
              <table class="table attendance-table">
                <thead><tr><th>No.</th><th>Staff Name</th><th>Position / Title</th><th>Phone</th><th>Location / Site</th><th>Customer / Job Site</th><th>Check In Time</th><th>Check Out Time</th><th>Total Hours (Net)</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                  <tr v-for="(item, index) in records" :key="item.id"><td>{{ index + 1 }}</td><td><strong>{{ item.staff_name }}</strong><small v-if="item.employee_id">{{ item.employee_id }}</small></td><td>{{ item.position_title || '—' }}</td><td>{{ item.phone || '—' }}</td><td>{{ item.location_site || '—' }}</td><td>{{ item.customer_job_site || '—' }}</td><td>{{ formatTime(item.check_in_time) }}</td><td>{{ formatTime(item.check_out_time) }}</td><td>{{ item.total_hours }}</td><td><span class="attendance-status" :class="`is-${item.status}`">{{ capitalize(item.status) }}</span></td><td><button class="attendance-action" type="button" @click="edit(item)">View</button></td></tr>
                  <tr v-if="!loading && !records.length"><td colspan="11" class="attendance-empty">No attendance records found.</td></tr>
                </tbody>
                <tfoot v-if="records.length"><tr><td colspan="6">TOTAL</td><td colspan="2">{{ records.length }} Employees</td><td>{{ totalHours }}</td><td colspan="2"></td></tr></tfoot>
              </table>
            </div>
          </section>

          <section v-if="showForm" class="pm-card attendance-form-card">
            <div class="attendance-list-head"><div class="attendance-section-title">{{ form.id ? 'Edit Attendance' : 'New Attendance' }}</div><button class="btn btn-outline-secondary" type="button" @click="closeForm">List</button></div>
            <form class="attendance-form" @submit.prevent="save">
              <label>Attendance Date*<input v-model="form.attendance_date" class="form-control" type="date" required /></label><label>Staff Name*<input v-model="form.staff_name" class="form-control" required /></label><label>Employee ID<input v-model="form.employee_id" class="form-control" /></label><label>Position / Title<input v-model="form.position_title" class="form-control" /></label><label>Phone<input v-model="form.phone" class="form-control" /></label><label>Email<input v-model="form.email" class="form-control" type="email" /></label><label>Location / Site<input v-model="form.location_site" class="form-control" /></label><label>Customer / Job Site<input v-model="form.customer_job_site" class="form-control" /></label><label>Department<input v-model="form.department" class="form-control" /></label><label>Shift<input v-model="form.shift_name" class="form-control" /></label><label>Check In Time<input v-model="form.check_in_time" class="form-control" type="time" /></label><label>Check Out Time<input v-model="form.check_out_time" class="form-control" type="time" /></label><label>Attendance Status<select v-model="form.status" class="form-control"><option value="present">Present</option><option value="absent">Absent</option><option value="late">Late</option><option value="on_leave">On Leave</option></select></label><div class="attendance-toggles"><label><input v-model="form.overtime" type="checkbox" /> Overtime</label><label><input v-model="form.late_arrival" type="checkbox" /> Late Arrival</label><label><input v-model="form.early_departure" type="checkbox" /> Early Departure</label></div><label class="attendance-wide">Job Order / Work Performed<textarea v-model="form.work_performed" class="form-control" rows="2"></textarea></label><label class="attendance-wide">Notes<textarea v-model="form.notes" class="form-control" rows="2"></textarea></label><label class="attendance-wide">Remarks<textarea v-model="form.remarks" class="form-control" rows="2"></textarea></label>
              <div class="attendance-form-actions"><button class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving…' : 'Save Attendance' }}</button><button v-if="form.id" class="btn btn-outline-danger" type="button" @click="remove">Delete</button></div>
            </form>
          </section>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'

const sidebarOpen = ref(false); const sidebarHidden = ref(false); const records = ref([]); const loading = ref(false); const saving = ref(false); const showForm = ref(false)
const today = new Date().toISOString().slice(0, 10)
const filters = reactive({ date: today, location: '', customer: '', department: '', shift: '', search: '' })
const blank = () => ({ id: null, report_no: '', attendance_date: today, prepared_by: '', staff_name: '', employee_id: '', position_title: '', phone: '', email: '', location_site: '', customer_job_site: '', department: '', shift_name: '', check_in_time: '', check_out_time: '', status: 'present', overtime: false, late_arrival: false, early_departure: false, work_performed: '', notes: '', remarks: '' })
const form = reactive(blank())
const totalHours = computed(() => { const total = records.value.reduce((sum, item) => sum + Number(item.total_minutes || 0), 0); return `${Math.floor(total / 60)}h ${String(total % 60).padStart(2, '0')}m` })
const load = async () => { loading.value = true; try { const { data } = await client.get('/workforce/staff-attendances', { params: filters }); records.value = data?.data || [] } finally { loading.value = false } }
const clearFilters = () => { Object.assign(filters, { date: '', location: '', customer: '', department: '', shift: '', search: '' }); load() }
const openCreate = () => { Object.assign(form, blank()); showForm.value = true }
const closeForm = () => { showForm.value = false }
const edit = async (item) => { const { data } = await client.get(`/workforce/staff-attendances/${item.id}`); Object.assign(form, data.data); showForm.value = true }
const save = async () => { saving.value = true; try { const request = form.id ? client.put(`/workforce/staff-attendances/${form.id}`, form) : client.post('/workforce/staff-attendances', form); await request; await load(); closeForm() } finally { saving.value = false } }
const remove = async () => { if (!confirm('Delete this attendance record?')) return; await client.delete(`/workforce/staff-attendances/${form.id}`); await load(); closeForm() }
const formatTime = (value) => value ? new Date(`1970-01-01T${value}`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '—'
const displayDate = (value) => value ? new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) : 'All Dates'
const capitalize = (value) => String(value || '').replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase())
const exportCsv = () => { const rows = [['Staff Name', 'Position', 'Phone', 'Location', 'Customer / Job Site', 'Check In', 'Check Out', 'Total Hours', 'Status'], ...records.value.map((item) => [item.staff_name, item.position_title, item.phone, item.location_site, item.customer_job_site, item.check_in_time, item.check_out_time, item.total_hours, item.status])]; const blob = new Blob([rows.map((row) => row.map((cell) => `"${String(cell || '').replaceAll('"', '""')}"`).join(',')).join('\n')], { type: 'text/csv' }); const url = URL.createObjectURL(blob); const link = document.createElement('a'); link.href = url; link.download = 'staff-attendance.csv'; link.click(); URL.revokeObjectURL(url) }
onMounted(load)
</script>

<style scoped>
.attendance-page{max-width:1500px}.attendance-title{display:flex;align-items:baseline;gap:.6rem;margin-bottom:1rem}.attendance-title h2{margin:0;font-size:2rem;font-weight:800;color:#0a1536}.attendance-title span{font-size:1.15rem;font-weight:650}.attendance-filter,.attendance-list,.attendance-form-card{padding:1rem 1.1rem;margin-bottom:.8rem}.attendance-section-title{font-weight:800;color:var(--pm-primary,#08286e);font-size:1rem}.attendance-filter-grid{display:grid;grid-template-columns:repeat(5,minmax(150px,1fr));gap:.85rem 1.25rem;margin-top:.7rem}.attendance-filter-grid label,.attendance-form label{display:grid;gap:.3rem;font-size:.82rem;font-weight:700;color:#151515}.attendance-filter-actions{display:flex;align-items:end;gap:.55rem;grid-column:4 / span 2}.attendance-list-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.8rem}.attendance-table{font-size:.83rem;margin:0}.attendance-table thead th{background:#061e59;color:white;text-align:center;white-space:nowrap;border-color:#486087}.attendance-table td{vertical-align:middle}.attendance-table td small{display:block;color:#667085;margin-top:.15rem}.attendance-table tfoot{font-weight:800;color:#071a53;background:#f3f6fc}.attendance-status{display:inline-block;border:1px solid #a7e3bc;border-radius:3px;padding:.15rem .45rem;font-size:.72rem;font-weight:750;color:#14813b;background:#effcf3}.attendance-status.is-absent{color:#b42318;border-color:#f2b8b5;background:#fff1f0}.attendance-status.is-late{color:#9a6700;border-color:#f6d98e;background:#fff9df}.attendance-status.is-on_leave{color:#175cd3;border-color:#b2ccff;background:#eff6ff}.attendance-action{border:1px solid #bfd0e6;background:white;color:#062a80;border-radius:4px;padding:.3rem .7rem;font-weight:700}.attendance-empty{text-align:center;color:#667085;padding:2rem!important}.attendance-form{display:grid;grid-template-columns:repeat(4,minmax(170px,1fr));gap:.85rem 1rem}.attendance-wide{grid-column:span 2}.attendance-toggles{display:flex;gap:.7rem;align-items:end;flex-wrap:wrap;font-size:.8rem;font-weight:700}.attendance-toggles label{display:flex;gap:.3rem;align-items:center}.attendance-form-actions{grid-column:1/-1;display:flex;gap:.6rem;margin-top:.2rem}@media(max-width:900px){.attendance-filter-grid{grid-template-columns:repeat(2,1fr)}.attendance-filter-actions{grid-column:auto}.attendance-form{grid-template-columns:repeat(2,1fr)}}@media(max-width:560px){.attendance-title{display:block}.attendance-filter-grid,.attendance-form{grid-template-columns:1fr}.attendance-wide{grid-column:auto}.attendance-list-head{gap:.75rem;align-items:flex-start;flex-direction:column}}
</style>
