<template>
  <div class="pm-dashboard-layout" :class="{ 'pm-sidebar-hidden': sidebarHidden }">
    <AppSidebar :isOpen="sidebarOpen" @close="closeSidebar" />
    <div class="pm-dashboard-main">
      <div class="pm-dashboard-topbar">
        <button class="pm-icon-btn pm-menu-btn" type="button" aria-label="Open menu" @click="toggleSidebar">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16" />
          </svg>
        </button>
        <button class="pm-icon-btn pm-hide-btn" type="button" aria-label="Toggle sidebar" @click="toggleSidebarHidden">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 6 3 12l6 6M21 12H4" />
          </svg>
        </button>
        <div class="pm-topbar-search">
          <input v-model="searchQuery" class="form-control" placeholder="Search timesheets..." />
          <button v-if="searchQuery" class="pm-clear-btn" type="button" @click="searchQuery = ''">&times;</button>
        </div>
        <div class="pm-topbar-actions">
          <button class="pm-icon-btn" type="button" aria-label="Notifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
          <div class="pm-topbar-avatar">{{ userInitials }}</div>
          <span class="pm-topbar-name">{{ userName }}</span>
          <span class="pm-topbar-pill">Admin</span>
          <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="m7 10 5 5 5-5H7Z" />
            </svg>
          </button>
          <div v-if="userMenuOpen" class="pm-user-menu">
            <button class="pm-user-item" type="button">Profile</button>
            <button class="pm-user-item" type="button">Account</button>
            <button class="pm-user-item danger" type="button">Log out</button>
          </div>
        </div>
      </div>

      <section class="pm-dashboard-content">
        <div class="container pm-ops-page">
          <div class="pm-page-head">
            <div>
              <h2>Timesheets</h2>
              <div class="pm-page-subtitle">Weekly hours, approvals, and time summaries.</div>
            </div>
          </div>

          <div class="pm-card pm-ops-card p-4">
            <div class="pm-card-header">
              <div>
                <h3>{{ form.id ? 'Edit Timesheet' : 'Create Timesheet' }}</h3>
                <p>Log weekly hours with daily breakdown and approvals.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="resetForm">Clear</button>
                <button class="btn btn-outline-danger btn-sm" type="button" :disabled="!form.id" @click="removeTimesheet">Delete</button>
              </div>
            </div>
            <div v-if="form.id" class="pm-approval-bar">
              <input v-model="approvalNote" class="form-control" type="text" placeholder="Approval note (optional)" />
              <button v-if="!isApprover && canSubmit" class="btn btn-primary btn-sm" type="button" @click="submitTimesheet">Submit</button>
              <button v-if="isApprover && form.status === 'submitted'" class="btn btn-success btn-sm" type="button" @click="approveTimesheet">Approve</button>
              <button v-if="isApprover && form.status === 'submitted'" class="btn btn-outline-danger btn-sm" type="button" @click="rejectTimesheet">Reject</button>
              <button v-if="isAdmin && form.status === 'approved'" class="btn btn-outline-primary btn-sm" type="button" @click="finalizeTimesheet">Finalize</button>
            </div>

            <form class="pm-form-grid" @submit.prevent="saveTimesheet">
              <div class="pm-form-field">
                <label class="pm-field-label">Timesheet No</label>
                <input v-model="form.timesheet_no" class="form-control" type="text" disabled />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Employee Name *</label>
                <input v-model="form.employee_name" class="form-control" type="text" required />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Employee Code</label>
                <input v-model="form.employee_code" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Role/Title</label>
                <input v-model="form.role_title" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Week Start *</label>
                <input v-model="form.week_start" class="form-control" type="date" required @change="syncWeekEnd" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Week End</label>
                <input v-model="form.week_end" class="form-control" type="date" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Status</label>
                <select v-model="form.status" class="form-control">
                  <option value="draft">Draft</option>
                  <option value="submitted">Submitted</option>
                  <option value="approved">Approved</option>
                  <option value="final">Final</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Approval Notes</label>
                <input v-model="form.notes" class="form-control" type="text" />
              </div>

              <div class="pm-section-title">Weekly Entries</div>
              <div class="pm-form-field full">
                <div class="pm-table-wrap">
                  <table class="table pm-table">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Hours</th>
                        <th>Overtime</th>
                        <th>Notes</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(entry, index) in form.entries" :key="`entry-${index}`">
                        <td><input v-model="entry.date" type="date" class="form-control" /></td>
                        <td><input v-model.number="entry.hours" type="number" min="0" step="0.25" class="form-control" /></td>
                        <td><input v-model.number="entry.overtime" type="number" min="0" step="0.25" class="form-control" /></td>
                        <td><input v-model="entry.notes" type="text" class="form-control" /></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="pm-section-title">Weekly Totals</div>
              <div class="pm-form-field">
                <label class="pm-field-label">Regular Hours</label>
                <input v-model.number="totals.regular_hours" class="form-control" type="number" step="0.25" min="0" disabled />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Overtime Hours</label>
                <input v-model.number="totals.overtime_hours" class="form-control" type="number" step="0.25" min="0" disabled />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Total Hours</label>
                <input v-model.number="totals.total_hours" class="form-control" type="number" step="0.25" min="0" disabled />
              </div>

              <div class="pm-form-actions">
                <button class="btn btn-primary" type="submit">
                  {{ saving ? 'Saving...' : form.id ? 'Update Timesheet' : 'Save Timesheet' }}
                </button>
              </div>
            </form>
          </div>

          <div class="pm-card">
            <div class="pm-card-header">
              <div>
                <h3>Recent Timesheets</h3>
                <p>Monitor weekly hours and approvals.</p>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Timesheet No</th>
                    <th>Employee</th>
                    <th>Week</th>
                    <th>Status</th>
                    <th>Total Hours</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredTimesheets" :key="item.id">
                    <td>{{ item.timesheet_no || '-' }}</td>
                    <td>{{ item.employee_name }}</td>
                    <td>{{ item.week_start }} - {{ item.week_end || '-' }}</td>
                    <td><span class="pm-status-pill" :class="statusClass(item.status)">{{ item.status }}</span></td>
                    <td>{{ item.total_hours }}</td>
                    <td class="pm-table-actions">
                      <button class="pm-icon-btn" type="button" @click="editTimesheet(item)">Edit</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="filteredTimesheets.length === 0" class="pm-empty-state">No timesheets yet.</div>
            </div>
          </div>
          <div class="pm-card" v-if="approvalLogs.length">
            <div class="pm-card-header">
              <div>
                <h3>Approval History</h3>
                <p>Audit trail for this timesheet.</p>
              </div>
            </div>
            <div class="pm-approval-list">
              <div v-for="log in approvalLogs" :key="log.id" class="pm-approval-item">
                <div class="pm-approval-status" :class="statusClass(log.status)">{{ log.status }}</div>
                <div class="pm-approval-meta">
                  <span>{{ formatDateTime(log.approved_at || log.created_at) }}</span>
                  <span v-if="log.step"> - {{ log.step }}</span>
                  <span v-if="log.note"> - {{ log.note }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const timesheets = ref([])
const saving = ref(false)
const approvalLogs = ref([])
const approvalNote = ref('')

const form = reactive({
  id: null,
  timesheet_no: '',
  employee_name: '',
  employee_code: '',
  role_title: '',
  week_start: '',
  week_end: '',
  status: 'draft',
  notes: '',
  entries: Array.from({ length: 7 }, () => ({ date: '', hours: 0, overtime: 0, notes: '' })),
})

const isAdmin = computed(() => authState.user?.role === 'admin')
const isApprover = computed(() => ['admin', 'manager'].includes(authState.user?.role))
const canSubmit = computed(() => ['draft', 'rejected'].includes(form.status))

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})

const totals = computed(() => {
  const regular = form.entries.reduce((sum, entry) => sum + (Number(entry.hours) || 0), 0)
  const overtime = form.entries.reduce((sum, entry) => sum + (Number(entry.overtime) || 0), 0)
  return {
    regular_hours: Number(regular.toFixed(2)),
    overtime_hours: Number(overtime.toFixed(2)),
    total_hours: Number((regular + overtime).toFixed(2)),
  }
})

const filteredTimesheets = computed(() => {
  if (!searchQuery.value) return timesheets.value
  const term = searchQuery.value.toLowerCase()
  return timesheets.value.filter((item) => {
    return [item.timesheet_no, item.employee_name, item.status]
      .filter(Boolean)
      .some((val) => String(val).toLowerCase().includes(term))
  })
})

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleSidebarHidden = () => {
  sidebarHidden.value = !sidebarHidden.value
}

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const statusClass = (status) => {
  if (status === 'approved' || status === 'final') return 'active'
  if (status === 'rejected') return 'danger'
  if (status === 'submitted') return 'info'
  return 'neutral'
}

const loadTimesheets = async () => {
  const { data } = await client.get('/workforce/timesheets')
  timesheets.value = data?.data || []
}

const loadApprovals = async (id) => {
  if (!id) {
    approvalLogs.value = []
    return
  }
  const { data } = await client.get('/workforce/approvals', {
    params: { entity_type: 'timesheet', entity_id: id },
  })
  approvalLogs.value = data?.data || []
}

const syncWeekEnd = () => {
  if (!form.week_start) return
  const start = new Date(form.week_start)
  const end = new Date(start)
  end.setDate(start.getDate() + 6)
  form.week_end = end.toISOString().split('T')[0]

  form.entries = form.entries.map((entry, index) => {
    const day = new Date(start)
    day.setDate(start.getDate() + index)
    return {
      ...entry,
      date: entry.date || day.toISOString().split('T')[0],
    }
  })
}

const resetForm = () => {
  Object.assign(form, {
    id: null,
    timesheet_no: '',
    employee_name: '',
    employee_code: '',
    role_title: '',
    week_start: '',
    week_end: '',
    status: 'draft',
    notes: '',
    entries: Array.from({ length: 7 }, () => ({ date: '', hours: 0, overtime: 0, notes: '' })),
  })
  approvalNote.value = ''
  approvalLogs.value = []
}

const editTimesheet = (item) => {
  form.id = item.id
  form.timesheet_no = item.timesheet_no || ''
  form.employee_name = item.employee_name || ''
  form.employee_code = item.employee_code || ''
  form.role_title = item.role_title || ''
  form.week_start = item.week_start || ''
  form.week_end = item.week_end || ''
  form.status = item.status || 'draft'
  form.notes = item.notes || ''
  form.entries = item.entries_json?.length
    ? item.entries_json
    : Array.from({ length: 7 }, () => ({ date: '', hours: 0, overtime: 0, notes: '' }))
  approvalNote.value = ''
  loadApprovals(item.id)
}

const saveTimesheet = async () => {
  saving.value = true
  try {
    const payload = {
      week_start: form.week_start,
      week_end: form.week_end || null,
      employee_name: form.employee_name,
      employee_code: form.employee_code,
      role_title: form.role_title,
      status: form.status,
      notes: form.notes,
      entries_json: form.entries,
      regular_hours: totals.value.regular_hours,
      overtime_hours: totals.value.overtime_hours,
      total_hours: totals.value.total_hours,
    }

    if (form.id) {
      await client.put(`/workforce/timesheets/${form.id}`, payload)
    } else {
      await client.post('/workforce/timesheets', payload)
    }
    await loadTimesheets()
    resetForm()
  } finally {
    saving.value = false
  }
}

const removeTimesheet = async () => {
  if (!form.id) return
  await client.delete(`/workforce/timesheets/${form.id}`)
  await loadTimesheets()
  resetForm()
}

const submitTimesheet = async () => {
  if (!form.id) return
  await client.post(`/workforce/timesheets/${form.id}/submit`, { note: approvalNote.value })
  await loadTimesheets()
  await loadApprovals(form.id)
}

const approveTimesheet = async () => {
  if (!form.id) return
  await client.post(`/workforce/timesheets/${form.id}/approve`, { note: approvalNote.value })
  await loadTimesheets()
  await loadApprovals(form.id)
}

const finalizeTimesheet = async () => {
  if (!form.id) return
  await client.post(`/workforce/timesheets/${form.id}/finalize`, { note: approvalNote.value })
  await loadTimesheets()
  await loadApprovals(form.id)
}

const rejectTimesheet = async () => {
  if (!form.id) return
  await client.post(`/workforce/timesheets/${form.id}/reject`, { note: approvalNote.value })
  await loadTimesheets()
  await loadApprovals(form.id)
}

const formatDateTime = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadTimesheets()
})
</script>

