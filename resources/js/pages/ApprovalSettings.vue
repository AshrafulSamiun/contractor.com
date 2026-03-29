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
          <input v-model="searchQuery" class="form-control" placeholder="Search approval steps..." />
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
              <h2>Approval Flow Settings</h2>
              <div class="pm-page-subtitle">Define multi-step approval routing and roles.</div>
            </div>
          </div>

          <div class="pm-settings-shell">
            <SystemSettingsMenu />
            <div class="pm-settings-shell-content">
          <div class="pm-card pm-ops-card p-4">
            <div class="pm-card-header">
              <div>
                <h3>Approval Steps</h3>
                <p>Configure default and module-specific approval flows.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="loadSettings">Reload</button>
                <button class="btn btn-primary btn-sm" type="button" :disabled="saving || !isAdmin" @click="saveSettings">Save</button>
              </div>
            </div>

            <div class="pm-approval-config">
              <div class="pm-form-field">
                <label class="pm-field-label">Scope</label>
                <select v-model="activeScope" class="form-control">
                  <option value="default">Default (all modules)</option>
                  <option v-for="module in moduleList" :key="module" :value="module">{{ moduleLabels[module] }}</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">SLA Hours</label>
                <input v-model.number="activeConfig.sla_hours" class="form-control" type="number" min="1" max="168" :disabled="!isAdmin" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Reminder Frequency (hours)</label>
                <input v-model.number="activeConfig.reminder_frequency_hours" class="form-control" type="number" min="1" max="168" :disabled="!isAdmin" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Reminder Channels</label>
                <div class="pm-chip-grid">
                  <label v-for="channel in channelList" :key="channel" class="pm-chip">
                    <input type="checkbox" :value="channel" v-model="activeConfig.reminder_channels" :disabled="!isAdmin" />
                    <span>{{ channel }}</span>
                  </label>
                </div>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Reminders Enabled</label>
                <select v-model="activeConfig.reminder_enabled" class="form-control" :disabled="!isAdmin">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
            </div>

            <div class="pm-approval-steps">
              <div
                v-for="(step, index) in filteredSteps"
                :key="step.key"
                class="pm-approval-step"
                :class="{ dragging: dragIndex === index, locked: isFixedStep(step) }"
                :draggable="isAdmin && !isFixedStep(step)"
                @dragstart="onDragStart(index)"
                @dragover.prevent
                @drop="onDrop(index)"
              >
                <div class="pm-step-main">
                  <div class="pm-step-handle">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <circle cx="8" cy="7" r="1.5" />
                      <circle cx="16" cy="7" r="1.5" />
                      <circle cx="8" cy="12" r="1.5" />
                      <circle cx="16" cy="12" r="1.5" />
                      <circle cx="8" cy="17" r="1.5" />
                      <circle cx="16" cy="17" r="1.5" />
                    </svg>
                    <span>#{{ index + 1 }}</span>
                    <span v-if="isFixedStep(step)" class="pm-step-lock">Locked</span>
                  </div>
                  <div class="pm-step-fields">
                    <label class="pm-field-label">Label</label>
                    <input v-model="step.label" class="form-control" :disabled="!isAdmin" />
                  </div>
                  <div class="pm-step-fields">
                    <label class="pm-field-label">Key</label>
                    <input v-model="step.key" class="form-control" :disabled="!isAdmin || isFixedStep(step)" />
                  </div>
                  <div class="pm-step-toggle">
                    <label class="pm-field-label">Enabled</label>
                    <select v-model="step.enabled" class="form-control" :disabled="!isAdmin">
                      <option :value="true">Yes</option>
                      <option :value="false">No</option>
                    </select>
                  </div>
                </div>
                <div class="pm-step-roles">
                  <label class="pm-field-label">Allowed Roles</label>
                  <div class="pm-chip-grid">
                    <label v-for="role in roles" :key="role" class="pm-chip">
                      <input type="checkbox" :value="role" v-model="step.roles" :disabled="!isAdmin" />
                      <span>{{ role }}</span>
                    </label>
                  </div>
                </div>
                <div class="pm-step-actions" v-if="isAdmin">
                  <button class="btn btn-outline-danger btn-sm" type="button" :disabled="isFixedStep(step)" @click="removeStep(index)">Remove</button>
                </div>
              </div>
            </div>

            <div class="pm-step-add" v-if="isAdmin">
              <button class="btn btn-outline-primary btn-sm" type="button" @click="addStep">Add Step</button>
            </div>
          </div>

          <div class="pm-card pm-ops-card p-4">
            <div class="pm-card-header">
              <div>
                <h3>Approval Audit Export</h3>
                <p>Download approval history for auditing.</p>
              </div>
            </div>
            <div class="pm-approval-export">
              <div class="pm-form-field">
                <label class="pm-field-label">Module</label>
                <select v-model="exportFilters.entity_type" class="form-control">
                  <option value="">All</option>
                  <option v-for="module in moduleList" :key="module" :value="module">{{ moduleLabels[module] }}</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Status</label>
                <select v-model="exportFilters.status" class="form-control">
                  <option value="">All</option>
                  <option value="submitted">Submitted</option>
                  <option value="approved">Approved</option>
                  <option value="final">Final</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">From</label>
                <input v-model="exportFilters.from" class="form-control" type="date" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">To</label>
                <input v-model="exportFilters.to" class="form-control" type="date" />
              </div>
              <div class="pm-form-actions">
                <button class="btn btn-outline-primary" type="button" @click="exportAudit">Export CSV</button>
              </div>
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
import SystemSettingsMenu from '../components/SystemSettingsMenu.vue'
import client from '../api/client'
import { authState } from '../store/auth'

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const saving = ref(false)
const dragIndex = ref(null)
const fixedStepKeys = ['submit', 'approve', 'final']

const roles = ref([])
const moduleList = ref(['daily_report', 'incident_report', 'timesheet'])
const channelList = ref(['email', 'in_app'])

const moduleLabels = {
  daily_report: 'Daily Reports',
  incident_report: 'Incident Reports',
  timesheet: 'Timesheets',
}

const approvalConfig = reactive({
  default: { steps: [], sla_hours: 24, reminder_enabled: true, reminder_frequency_hours: 6, reminder_channels: ['email', 'in_app'] },
  modules: {},
})

const activeScope = ref('default')

const exportFilters = reactive({
  entity_type: '',
  status: '',
  from: '',
  to: '',
})

const isAdmin = computed(() => authState.user?.role === 'admin')

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})

const activeConfig = computed(() => {
  if (activeScope.value === 'default') return approvalConfig.default
  if (!approvalConfig.modules[activeScope.value]) {
    approvalConfig.modules[activeScope.value] = {
      steps: JSON.parse(JSON.stringify(approvalConfig.default.steps)),
      sla_hours: approvalConfig.default.sla_hours,
      reminder_enabled: approvalConfig.default.reminder_enabled,
      reminder_frequency_hours: approvalConfig.default.reminder_frequency_hours,
      reminder_channels: approvalConfig.default.reminder_channels,
    }
  }
  return approvalConfig.modules[activeScope.value]
})

const filteredSteps = computed(() => {
  if (!searchQuery.value) return activeConfig.value.steps
  const term = searchQuery.value.toLowerCase()
  return activeConfig.value.steps.filter((step) =>
    [step.key, step.label].filter(Boolean).some((val) => val.toLowerCase().includes(term))
  )
})

const isFixedStep = (step) => fixedStepKeys.includes(step.key)

const enforceStepOrder = (steps) => {
  const required = {
    submit: { key: 'submit', label: 'Submit', roles: ['staff', 'manager', 'admin'], enabled: true },
    approve: { key: 'approve', label: 'Approve', roles: ['manager', 'admin'], enabled: true },
    final: { key: 'final', label: 'Finalize', roles: ['admin'], enabled: true },
  }
  const extras = []
  steps.forEach((step) => {
    if (!step || !step.key) return
    if (required[step.key]) {
      required[step.key] = { ...required[step.key], ...step }
    } else {
      extras.push(step)
    }
  })
  return [required.submit, required.approve, required.final, ...extras]
}

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

const loadSettings = async () => {
  const { data } = await client.get('/settings/approvals')
  approvalConfig.default = data?.data?.default || { steps: [], sla_hours: 24, reminder_enabled: true, reminder_frequency_hours: 6, reminder_channels: ['email', 'in_app'] }
  approvalConfig.modules = data?.data?.modules || {}
  roles.value = data?.data?.roles || ['admin', 'manager', 'staff']
  moduleList.value = data?.data?.module_list || moduleList.value
  channelList.value = data?.data?.channel_list || channelList.value
  approvalConfig.default.steps = enforceStepOrder(approvalConfig.default.steps || [])
  Object.keys(approvalConfig.modules).forEach((key) => {
    const moduleConfig = approvalConfig.modules[key]
    if (moduleConfig?.steps) {
      moduleConfig.steps = enforceStepOrder(moduleConfig.steps)
    }
  })
}

const addStep = () => {
  activeConfig.value.steps.push({
    key: `step_${activeConfig.value.steps.length + 1}`,
    label: 'New Step',
    roles: ['admin'],
    enabled: true,
  })
  activeConfig.value.steps = enforceStepOrder(activeConfig.value.steps)
}

const removeStep = (index) => {
  const step = activeConfig.value.steps[index]
  if (step && isFixedStep(step)) return
  activeConfig.value.steps.splice(index, 1)
  activeConfig.value.steps = enforceStepOrder(activeConfig.value.steps)
}

const onDragStart = (index) => {
  if (!isAdmin.value) return
  if (isFixedStep(activeConfig.value.steps[index] || {})) return
  dragIndex.value = index
}

const onDrop = (index) => {
  if (!isAdmin.value || dragIndex.value === null) return
  const steps = activeConfig.value.steps
  if (isFixedStep(steps[index] || {})) {
    dragIndex.value = null
    return
  }
  const [moved] = steps.splice(dragIndex.value, 1)
  steps.splice(index, 0, moved)
  activeConfig.value.steps = enforceStepOrder(steps)
  dragIndex.value = null
}

const saveSettings = async () => {
  if (!isAdmin.value) return
  saving.value = true
  try {
    approvalConfig.default.steps = enforceStepOrder(approvalConfig.default.steps || [])
    Object.keys(approvalConfig.modules).forEach((key) => {
      const moduleConfig = approvalConfig.modules[key]
      if (moduleConfig?.steps) {
        moduleConfig.steps = enforceStepOrder(moduleConfig.steps)
      }
    })
    await client.put('/settings/approvals', {
      default: approvalConfig.default,
      modules: approvalConfig.modules,
    })
  } finally {
    saving.value = false
  }
}

const exportAudit = () => {
  const params = new URLSearchParams()
  if (exportFilters.entity_type) params.append('entity_type', exportFilters.entity_type)
  if (exportFilters.status) params.append('status', exportFilters.status)
  if (exportFilters.from) params.append('from', exportFilters.from)
  if (exportFilters.to) params.append('to', exportFilters.to)
  window.location.href = `/api/v1/workforce/approvals/export?${params.toString()}`
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadSettings()
})
</script>
