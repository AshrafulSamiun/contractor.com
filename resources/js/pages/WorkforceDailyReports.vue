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
          <input v-model="searchQuery" class="form-control" placeholder="Search reports..." />
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
              <h2>Daily Reports</h2>
              <div class="pm-page-subtitle">Operational daily logs with guard info, metrics, and approvals.</div>
            </div>
          </div>

          <div class="pm-metrics-grid">
            <div class="pm-metric-card">
              <p>Reports</p>
              <h3>{{ metrics.totalReports }}</h3>
            </div>
            <div class="pm-metric-card">
              <p>Parcels</p>
              <h3>{{ metrics.totalParcels }}</h3>
            </div>
            <div class="pm-metric-card">
              <p>Pickups</p>
              <h3>{{ metrics.totalPickups }}</h3>
            </div>
            <div class="pm-metric-card">
              <p>Incidents</p>
              <h3>{{ metrics.totalIncidents }}</h3>
            </div>
          </div>

          <div class="pm-card pm-ops-card p-4">
            <div class="pm-card-header">
              <div>
                <h3>{{ form.id ? 'Edit Daily Report' : 'Create Daily Report' }}</h3>
                <p>Capture daily activity details, guard info, and metrics.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="resetForm">Clear</button>
                <button class="btn btn-outline-danger btn-sm" type="button" :disabled="!form.id" @click="removeReport">Delete</button>
              </div>
            </div>
            <div v-if="form.id" class="pm-approval-bar">
              <input v-model="approvalNote" class="form-control" type="text" placeholder="Approval note (optional)" />
              <button v-if="!isApprover && canSubmit" class="btn btn-primary btn-sm" type="button" @click="submitReport">Submit</button>
              <button v-if="isApprover && form.status === 'submitted'" class="btn btn-success btn-sm" type="button" @click="approveReport">Approve</button>
              <button v-if="isApprover && form.status === 'submitted'" class="btn btn-outline-danger btn-sm" type="button" @click="rejectReport">Reject</button>
              <button v-if="isAdmin && form.status === 'approved'" class="btn btn-outline-primary btn-sm" type="button" @click="finalizeReport">Finalize</button>
            </div>
            <form class="pm-form-grid" @submit.prevent="saveReport">
              <div class="pm-form-field">
                <label class="pm-field-label">Report No</label>
                <input v-model="form.report_no" class="form-control" type="text" placeholder="Auto" disabled />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Report Date *</label>
                <input v-model="form.report_date" class="form-control" type="date" required />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Shift Name</label>
                <input v-model="form.shift_name" class="form-control" type="text" placeholder="Morning" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Shift Time</label>
                <input v-model="form.shift_time" class="form-control" type="time" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Report Location</label>
                <input v-model="form.report_location" class="form-control" type="text" placeholder="Front Desk" />
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
              <div class="pm-form-field full">
                <label class="pm-field-label">Summary</label>
                <textarea v-model="form.summary" class="form-control" rows="3"></textarea>
              </div>
              <div class="pm-form-field full">
                <label class="pm-field-label">Notes</label>
                <textarea v-model="form.report_notes" class="form-control" rows="3"></textarea>
              </div>

              <div class="pm-section-title">Guard Info</div>
              <div class="pm-form-field">
                <label class="pm-field-label">Employee Name</label>
                <input v-model="form.employee_name" class="form-control" type="text" placeholder="John Anderson" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Licence No</label>
                <input v-model="form.licence_no" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Expiry Date</label>
                <input v-model="form.expire_date" class="form-control" type="date" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Valid</label>
                <select v-model="form.is_valid" class="form-control">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>

              <div class="pm-section-title">Daily Metrics</div>
              <div class="pm-form-field">
                <label class="pm-field-label">Parcels Processed</label>
                <input v-model.number="form.parcels" class="form-control" type="number" min="0" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Pickups Completed</label>
                <input v-model.number="form.pickups" class="form-control" type="number" min="0" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Incidents Logged</label>
                <input v-model.number="form.incidents" class="form-control" type="number" min="0" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Deliveries Completed</label>
                <input v-model.number="form.deliveries" class="form-control" type="number" min="0" />
              </div>

              <div class="pm-form-actions">
                <button class="btn btn-primary" type="submit">
                  {{ saving ? 'Saving...' : form.id ? 'Update Report' : 'Save Report' }}
                </button>
              </div>
            </form>
          </div>

          <div class="pm-card">
            <div class="pm-card-header">
              <div>
                <h3>Recent Reports</h3>
                <p>Review submitted daily logs.</p>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Report No</th>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Parcels</th>
                    <th>Pickups</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredReports" :key="item.id">
                    <td>{{ item.report_no || '-' }}</td>
                    <td>{{ item.report_date }}</td>
                    <td>{{ item.shift_name || item.shift || '-' }}</td>
                    <td>{{ item.report_location || '-' }}</td>
                    <td>
                      <span class="pm-status-pill" :class="statusClass(item.status)">
                        {{ item.status || 'draft' }}
                      </span>
                    </td>
                    <td>{{ item.metrics_json?.parcels || 0 }}</td>
                    <td>{{ item.metrics_json?.pickups || 0 }}</td>
                    <td class="pm-table-actions">
                      <button class="pm-icon-btn" type="button" @click="editReport(item)">Edit</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="filteredReports.length === 0" class="pm-empty-state">No reports yet.</div>
            </div>
          </div>

          <div class="pm-card" v-if="approvalLogs.length">
            <div class="pm-card-header">
              <div>
                <h3>Approval History</h3>
                <p>Audit trail for this report.</p>
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

const reports = ref([])
const saving = ref(false)
const approvalLogs = ref([])
const approvalNote = ref('')

const form = reactive({
  id: null,
  report_no: '',
  report_date: '',
  shift: '',
  shift_name: '',
  shift_time: '',
  report_location: '',
  status: 'draft',
  summary: '',
  report_notes: '',
  employee_name: '',
  licence_no: '',
  expire_date: '',
  is_valid: true,
  parcels: 0,
  pickups: 0,
  incidents: 0,
  deliveries: 0,
})

const isAdmin = computed(() => authState.user?.role === 'admin')
const isApprover = computed(() => ['admin', 'manager'].includes(authState.user?.role))
const canSubmit = computed(() => ['draft', 'rejected'].includes(form.status))

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})

const metrics = computed(() => {
  return {
    totalReports: reports.value.length,
    totalParcels: reports.value.reduce((sum, item) => sum + (item.metrics_json?.parcels || 0), 0),
    totalPickups: reports.value.reduce((sum, item) => sum + (item.metrics_json?.pickups || 0), 0),
    totalIncidents: reports.value.reduce((sum, item) => sum + (item.metrics_json?.incidents || 0), 0),
  }
})

const filteredReports = computed(() => {
  if (!searchQuery.value) return reports.value
  const term = searchQuery.value.toLowerCase()
  return reports.value.filter((item) => {
    return [
      item.report_no,
      item.report_location,
      item.shift_name,
      item.employee_name,
      item.status,
    ]
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

const loadReports = async () => {
  const { data } = await client.get('/workforce/daily-reports')
  reports.value = data?.data || []
}

const loadApprovals = async (id) => {
  if (!id) {
    approvalLogs.value = []
    return
  }
  const { data } = await client.get('/workforce/approvals', {
    params: { entity_type: 'daily_report', entity_id: id },
  })
  approvalLogs.value = data?.data || []
}

const resetForm = () => {
  Object.assign(form, {
    id: null,
    report_no: '',
    report_date: '',
    shift: '',
    shift_name: '',
    shift_time: '',
    report_location: '',
    status: 'draft',
    summary: '',
    report_notes: '',
    employee_name: '',
    licence_no: '',
    expire_date: '',
    is_valid: true,
    parcels: 0,
    pickups: 0,
    incidents: 0,
    deliveries: 0,
  })
  approvalNote.value = ''
  approvalLogs.value = []
}

const editReport = (item) => {
  form.id = item.id
  form.report_no = item.report_no || ''
  form.report_date = item.report_date
  form.shift = item.shift || ''
  form.shift_name = item.shift_name || ''
  form.shift_time = item.shift_time || ''
  form.report_location = item.report_location || ''
  form.status = item.status || 'draft'
  form.summary = item.summary || ''
  form.report_notes = item.report_notes || ''
  form.employee_name = item.employee_name || ''
  form.licence_no = item.licence_no || ''
  form.expire_date = item.expire_date || ''
  form.is_valid = item.is_valid ?? true
  form.parcels = item.metrics_json?.parcels || 0
  form.pickups = item.metrics_json?.pickups || 0
  form.incidents = item.metrics_json?.incidents || 0
  form.deliveries = item.metrics_json?.deliveries || 0
  approvalNote.value = ''
  loadApprovals(item.id)
}

const saveReport = async () => {
  saving.value = true
  try {
    const payload = {
      report_date: form.report_date,
      shift: form.shift,
      shift_name: form.shift_name,
      shift_time: form.shift_time || null,
      report_location: form.report_location,
      status: form.status,
      summary: form.summary,
      report_notes: form.report_notes,
      employee_name: form.employee_name,
      licence_no: form.licence_no,
      expire_date: form.expire_date || null,
      is_valid: form.is_valid,
      metrics_json: {
        parcels: form.parcels,
        pickups: form.pickups,
        incidents: form.incidents,
        deliveries: form.deliveries,
      },
    }
    if (form.id) {
      await client.put(`/workforce/daily-reports/${form.id}`, payload)
    } else {
      await client.post('/workforce/daily-reports', payload)
    }
    await loadReports()
    resetForm()
  } finally {
    saving.value = false
  }
}

const removeReport = async () => {
  if (!form.id) return
  await client.delete(`/workforce/daily-reports/${form.id}`)
  await loadReports()
  resetForm()
}

const submitReport = async () => {
  if (!form.id) return
  await client.post(`/workforce/daily-reports/${form.id}/submit`, { note: approvalNote.value })
  await loadReports()
  await loadApprovals(form.id)
}

const approveReport = async () => {
  if (!form.id) return
  await client.post(`/workforce/daily-reports/${form.id}/approve`, { note: approvalNote.value })
  await loadReports()
  await loadApprovals(form.id)
}

const finalizeReport = async () => {
  if (!form.id) return
  await client.post(`/workforce/daily-reports/${form.id}/finalize`, { note: approvalNote.value })
  await loadReports()
  await loadApprovals(form.id)
}

const rejectReport = async () => {
  if (!form.id) return
  await client.post(`/workforce/daily-reports/${form.id}/reject`, { note: approvalNote.value })
  await loadReports()
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
  loadReports()
})
</script>

