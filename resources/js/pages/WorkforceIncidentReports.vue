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
          <input v-model="searchQuery" class="form-control" placeholder="Search incidents..." />
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
              <h2>Incident Reports</h2>
              <div class="pm-page-subtitle">Capture security incidents with severity, people involved, and response details.</div>
            </div>
          </div>

          <div class="pm-card pm-ops-card p-4">
            <div class="pm-card-header">
              <div>
                <h3>{{ form.id ? 'Edit Incident Report' : 'Create Incident Report' }}</h3>
                <p>Log incident details, involved people, and response actions.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="resetForm">Clear</button>
                <button class="btn btn-outline-danger btn-sm" type="button" :disabled="!form.id" @click="removeIncident">Delete</button>
              </div>
            </div>
            <div v-if="form.id" class="pm-approval-bar">
              <input v-model="approvalNote" class="form-control" type="text" placeholder="Approval note (optional)" />
              <button v-if="!isApprover && canSubmit" class="btn btn-primary btn-sm" type="button" @click="submitIncident">Submit</button>
              <button v-if="isApprover && form.status === 'submitted'" class="btn btn-success btn-sm" type="button" @click="approveIncident">Approve</button>
              <button v-if="isApprover && form.status === 'submitted'" class="btn btn-outline-danger btn-sm" type="button" @click="rejectIncident">Reject</button>
              <button v-if="isAdmin && form.status === 'approved'" class="btn btn-outline-primary btn-sm" type="button" @click="finalizeIncident">Finalize</button>
            </div>

            <form class="pm-form-grid" @submit.prevent="saveIncident">
              <div class="pm-form-field">
                <label class="pm-field-label">Incident No</label>
                <input v-model="form.incident_no" class="form-control" type="text" placeholder="Auto" disabled />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Incident Date *</label>
                <input v-model="form.occurred_date" class="form-control" type="date" required />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Incident Time</label>
                <input v-model="form.occurred_time" class="form-control" type="time" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Jobsite</label>
                <input v-model="form.jobsite" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Shift</label>
                <input v-model="form.shift_name" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Incident Location</label>
                <input v-model="form.incident_location" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Severity</label>
                <select v-model="form.severity" class="form-control">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="critical">Critical</option>
                </select>
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
                <label class="pm-field-label">Notes</label>
                <textarea v-model="form.incident_notes" class="form-control" rows="3"></textarea>
              </div>

              <div class="pm-section-title">Guard Info</div>
              <div class="pm-form-field">
                <label class="pm-field-label">Employee Name</label>
                <input v-model="form.employee_name" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">License No</label>
                <input v-model="form.license_no" class="form-control" type="text" />
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

              <div class="pm-section-title">Incident Type</div>
              <div class="pm-form-field">
                <label class="pm-field-label">Type</label>
                <input v-model="form.incident_type" class="form-control" type="text" placeholder="Security breach" />
              </div>
              <div class="pm-form-field full">
                <label class="pm-field-label">Categories</label>
                <div class="pm-chip-grid">
                  <label v-for="item in incidentCategoryOptions" :key="item" class="pm-chip">
                    <input type="checkbox" :value="item" v-model="form.incident_categories" />
                    <span>{{ item }}</span>
                  </label>
                </div>
              </div>

              <div class="pm-section-title">Incident Details</div>
              <div class="pm-form-field full">
                <label class="pm-field-label">Description</label>
                <textarea v-model="form.incident_description" class="form-control" rows="3"></textarea>
              </div>
              <div class="pm-form-field full">
                <label class="pm-field-label">Damages</label>
                <textarea v-model="form.incident_damages" class="form-control" rows="2"></textarea>
              </div>
              <div class="pm-form-field full">
                <label class="pm-field-label">Injuries</label>
                <textarea v-model="form.incident_injuries" class="form-control" rows="2"></textarea>
              </div>
              <div class="pm-form-field full">
                <label class="pm-field-label">Immediate Actions Taken</label>
                <textarea v-model="form.action_taken" class="form-control" rows="2"></textarea>
              </div>

              <div class="pm-section-title">Involved People</div>
              <div class="pm-form-field full">
                <div class="pm-repeat-list">
                  <div v-for="(person, index) in form.involved_people" :key="`involved-${index}`" class="pm-repeat-row">
                    <input v-model="person.name" class="form-control" type="text" placeholder="Name" />
                    <input v-model="person.role" class="form-control" type="text" placeholder="Role" />
                    <input v-model="person.email" class="form-control" type="email" placeholder="Email" />
                    <button class="pm-icon-btn" type="button" @click="removePerson('involved_people', index)">x</button>
                  </div>
                  <button class="btn btn-outline-primary btn-sm" type="button" @click="addPerson('involved_people')">Add Person</button>
                </div>
              </div>

              <div class="pm-section-title">Witnesses</div>
              <div class="pm-form-field full">
                <div class="pm-repeat-list">
                  <div v-for="(person, index) in form.witness_people" :key="`witness-${index}`" class="pm-repeat-row">
                    <input v-model="person.name" class="form-control" type="text" placeholder="Name" />
                    <input v-model="person.role" class="form-control" type="text" placeholder="Role" />
                    <input v-model="person.email" class="form-control" type="email" placeholder="Email" />
                    <button class="pm-icon-btn" type="button" @click="removePerson('witness_people', index)">x</button>
                  </div>
                  <button class="btn btn-outline-primary btn-sm" type="button" @click="addPerson('witness_people')">Add Witness</button>
                </div>
              </div>

              <div class="pm-section-title">Emergency Response</div>
              <div class="pm-form-field">
                <label class="pm-field-label">Police Called</label>
                <select v-model="form.police_called" class="form-control">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Police File No</label>
                <input v-model="form.police_file_no" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Officer Name</label>
                <input v-model="form.police_officer_name" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Badge No</label>
                <input v-model="form.badge_no" class="form-control" type="text" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Fire Dept Called</label>
                <select v-model="form.fire_dept_called" class="form-control">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Ambulance Called</label>
                <select v-model="form.ambulance_called" class="form-control">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div class="pm-form-field full">
                <label class="pm-field-label">Emergency Details</label>
                <textarea v-model="form.emergency_details" class="form-control" rows="3"></textarea>
              </div>

              <div class="pm-form-actions">
                <button class="btn btn-primary" type="submit">
                  {{ saving ? 'Saving...' : form.id ? 'Update Incident' : 'Save Incident' }}
                </button>
              </div>
            </form>
          </div>

          <div class="pm-card">
            <div class="pm-card-header">
              <div>
                <h3>Recent Incidents</h3>
                <p>Track severity and response status.</p>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Incident No</th>
                    <th>Date</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredIncidents" :key="item.id">
                    <td>{{ item.incident_no || '-' }}</td>
                    <td>{{ formatDate(item.occurred_at) }}</td>
                    <td><span class="pm-status-pill" :class="severityClass(item.severity)">{{ item.severity }}</span></td>
                    <td><span class="pm-status-pill" :class="statusClass(item.status)">{{ item.status }}</span></td>
                    <td>{{ item.incident_location || item.location || '-' }}</td>
                    <td class="pm-table-actions">
                      <button class="pm-icon-btn" type="button" @click="editIncident(item)">Edit</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="filteredIncidents.length === 0" class="pm-empty-state">No incidents yet.</div>
            </div>
          </div>
          <div class="pm-card" v-if="approvalLogs.length">
            <div class="pm-card-header">
              <div>
                <h3>Approval History</h3>
                <p>Audit trail for this incident.</p>
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

const incidents = ref([])
const saving = ref(false)
const approvalLogs = ref([])
const approvalNote = ref('')

const incidentCategoryOptions = [
  'Alarm Triggered',
  'Trespassing',
  'Door Forced Open',
  'Suspicious Person',
  'Lockdown / Evacuation',
  'Unauthorized Entry',
]

const emptyPerson = () => ({ name: '', role: '', email: '' })

const form = reactive({
  id: null,
  incident_no: '',
  occurred_date: '',
  occurred_time: '',
  jobsite: '',
  shift_name: '',
  incident_location: '',
  severity: 'medium',
  status: 'draft',
  incident_notes: '',
  employee_name: '',
  license_no: '',
  expire_date: '',
  is_valid: true,
  incident_type: '',
  incident_categories: [],
  incident_description: '',
  incident_damages: '',
  incident_injuries: '',
  action_taken: '',
  involved_people: [emptyPerson()],
  witness_people: [emptyPerson()],
  police_called: false,
  police_file_no: '',
  police_officer_name: '',
  badge_no: '',
  fire_dept_called: false,
  ambulance_called: false,
  emergency_details: '',
})

const isAdmin = computed(() => authState.user?.role === 'admin')
const isApprover = computed(() => ['admin', 'manager'].includes(authState.user?.role))
const canSubmit = computed(() => ['draft', 'rejected'].includes(form.status))

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})

const filteredIncidents = computed(() => {
  if (!searchQuery.value) return incidents.value
  const term = searchQuery.value.toLowerCase()
  return incidents.value.filter((item) => {
    return [
      item.incident_no,
      item.incident_location,
      item.jobsite,
      item.status,
      item.severity,
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

const severityClass = (severity) => {
  if (severity === 'critical') return 'danger'
  if (severity === 'high') return 'warning'
  if (severity === 'medium') return 'info'
  return 'neutral'
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  return date.toLocaleDateString()
}

const loadIncidents = async () => {
  const { data } = await client.get('/workforce/incident-reports')
  incidents.value = data?.data || []
}

const loadApprovals = async (id) => {
  if (!id) {
    approvalLogs.value = []
    return
  }
  const { data } = await client.get('/workforce/approvals', {
    params: { entity_type: 'incident_report', entity_id: id },
  })
  approvalLogs.value = data?.data || []
}

const resetForm = () => {
  Object.assign(form, {
    id: null,
    incident_no: '',
    occurred_date: '',
    occurred_time: '',
    jobsite: '',
    shift_name: '',
    incident_location: '',
    severity: 'medium',
    status: 'draft',
    incident_notes: '',
    employee_name: '',
    license_no: '',
    expire_date: '',
    is_valid: true,
    incident_type: '',
    incident_categories: [],
    incident_description: '',
    incident_damages: '',
    incident_injuries: '',
    action_taken: '',
    involved_people: [emptyPerson()],
    witness_people: [emptyPerson()],
    police_called: false,
    police_file_no: '',
    police_officer_name: '',
    badge_no: '',
    fire_dept_called: false,
    ambulance_called: false,
    emergency_details: '',
  })
  approvalNote.value = ''
  approvalLogs.value = []
}

const addPerson = (key) => {
  form[key].push(emptyPerson())
}

const removePerson = (key, index) => {
  if (form[key].length <= 1) return
  form[key].splice(index, 1)
}

const editIncident = (item) => {
  const occurred = item.occurred_at ? new Date(item.occurred_at) : null
  form.id = item.id
  form.incident_no = item.incident_no || ''
  form.occurred_date = occurred ? occurred.toISOString().split('T')[0] : ''
  form.occurred_time = occurred ? occurred.toTimeString().slice(0, 5) : ''
  form.jobsite = item.jobsite || ''
  form.shift_name = item.shift_name || ''
  form.incident_location = item.incident_location || item.location || ''
  form.severity = item.severity || 'medium'
  form.status = item.status || 'draft'
  form.incident_notes = item.incident_notes || ''
  form.employee_name = item.employee_name || ''
  form.license_no = item.license_no || ''
  form.expire_date = item.expire_date || ''
  form.is_valid = item.is_valid ?? true
  form.incident_type = item.incident_type || ''
  form.incident_categories = item.incident_categories || []
  form.incident_description = item.incident_description || ''
  form.incident_damages = item.incident_damages || ''
  form.incident_injuries = item.incident_injuries || ''
  form.action_taken = item.action_taken || ''
  form.involved_people = item.involved_people?.length ? item.involved_people : [emptyPerson()]
  form.witness_people = item.witness_people?.length ? item.witness_people : [emptyPerson()]
  form.police_called = item.police_called ?? false
  form.police_file_no = item.police_file_no || ''
  form.police_officer_name = item.police_officer_name || ''
  form.badge_no = item.badge_no || ''
  form.fire_dept_called = item.fire_dept_called ?? false
  form.ambulance_called = item.ambulance_called ?? false
  form.emergency_details = item.emergency_details || ''
  approvalNote.value = ''
  loadApprovals(item.id)
}

const saveIncident = async () => {
  saving.value = true
  try {
    const occurredAt = form.occurred_date
      ? `${form.occurred_date}T${form.occurred_time || '00:00'}:00`
      : null

    const payload = {
      occurred_at: occurredAt,
      jobsite: form.jobsite,
      shift_name: form.shift_name,
      incident_location: form.incident_location,
      location: form.incident_location,
      severity: form.severity,
      status: form.status,
      incident_notes: form.incident_notes,
      employee_name: form.employee_name,
      license_no: form.license_no,
      expire_date: form.expire_date || null,
      is_valid: form.is_valid,
      incident_type: form.incident_type,
      incident_categories: form.incident_categories,
      incident_description: form.incident_description,
      incident_damages: form.incident_damages,
      incident_injuries: form.incident_injuries,
      action_taken: form.action_taken,
      involved_people: form.involved_people,
      witness_people: form.witness_people,
      police_called: form.police_called,
      police_file_no: form.police_file_no,
      police_officer_name: form.police_officer_name,
      badge_no: form.badge_no,
      fire_dept_called: form.fire_dept_called,
      ambulance_called: form.ambulance_called,
      emergency_details: form.emergency_details,
    }

    if (form.id) {
      await client.put(`/workforce/incident-reports/${form.id}`, payload)
    } else {
      await client.post('/workforce/incident-reports', payload)
    }
    await loadIncidents()
    resetForm()
  } finally {
    saving.value = false
  }
}

const removeIncident = async () => {
  if (!form.id) return
  await client.delete(`/workforce/incident-reports/${form.id}`)
  await loadIncidents()
  resetForm()
}

const submitIncident = async () => {
  if (!form.id) return
  await client.post(`/workforce/incident-reports/${form.id}/submit`, { note: approvalNote.value })
  await loadIncidents()
  await loadApprovals(form.id)
}

const approveIncident = async () => {
  if (!form.id) return
  await client.post(`/workforce/incident-reports/${form.id}/approve`, { note: approvalNote.value })
  await loadIncidents()
  await loadApprovals(form.id)
}

const finalizeIncident = async () => {
  if (!form.id) return
  await client.post(`/workforce/incident-reports/${form.id}/finalize`, { note: approvalNote.value })
  await loadIncidents()
  await loadApprovals(form.id)
}

const rejectIncident = async () => {
  if (!form.id) return
  await client.post(`/workforce/incident-reports/${form.id}/reject`, { note: approvalNote.value })
  await loadIncidents()
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
  loadIncidents()
})
</script>

