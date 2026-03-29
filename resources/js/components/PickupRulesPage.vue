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
          <input v-model="searchQuery" class="form-control" placeholder="Search pickup rules..." />
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
        <div class="container">
          <div class="pm-page-head">
            <div>
              <h2>{{ title }}</h2>
              <div class="pm-page-subtitle">{{ subtitle }}</div>
            </div>
          </div>

          <div class="pm-card p-4 mb-4">
            <div class="pm-card-header">
              <div>
                <h3>SLA Summary</h3>
                <p>Active rules and SLA breaches.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" :disabled="!canReadPickup" @click="loadSummary">Refresh</button>
                <button class="btn btn-outline-primary btn-sm" type="button" :disabled="!canExportPickup" @click="exportSla">Export CSV</button>
              </div>
            </div>
            <div class="pm-approval-export">
              <div class="pm-form-field">
                <label class="pm-field-label">Status</label>
                <select v-model="summaryFilters.status" class="form-control">
                  <option value="">All</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Updated From</label>
                <input v-model="summaryFilters.from" class="form-control" type="date" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Updated To</label>
                <input v-model="summaryFilters.to" class="form-control" type="date" />
              </div>
              <div class="pm-form-actions">
                <button class="btn btn-outline-primary" type="button" :disabled="!canReadPickup" @click="loadSummary">Apply Filters</button>
              </div>
            </div>
            <div class="pm-metrics-grid">
              <div class="pm-metric-card">
                <p>Total Rules</p>
                <h3>{{ summaryForType.total }}</h3>
                <span class="pm-muted">Type: {{ props.ruleType }}</span>
              </div>
              <div class="pm-metric-card">
                <p>SLA Breaches</p>
                <h3>{{ summaryForType.breaches }}</h3>
                <span class="pm-muted">Window &gt; SLA</span>
              </div>
            </div>
            <div v-if="summaryBreaches.length" class="pm-table-wrap mt-3">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Rule</th>
                    <th>Window (h)</th>
                    <th>SLA (h)</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in summaryBreaches" :key="item.id">
                    <td>{{ item.name }}</td>
                    <td>{{ item.window_hours }}</td>
                    <td>{{ item.sla_hours }}</td>
                    <td>{{ item.updated_at }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="pm-card p-4 mb-4">
            <div class="pm-card-header">
              <div>
                <h3>{{ formTitle }}</h3>
                <p>Define pickup rules, SLA, and notifications.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="resetForm">Clear</button>
                <button class="btn btn-primary btn-sm" type="button" :disabled="!canEditCurrent" @click="saveRule">{{ editingId ? 'Update' : 'Save' }}</button>
              </div>
            </div>

            <div class="pm-approval-config" :class="{ 'pm-disabled': !canEditCurrent }">
              <div class="pm-form-field">
                <label class="pm-field-label">Rule Name *</label>
                <input v-model="form.name" class="form-control" placeholder="Rule name" :disabled="!canEditCurrent" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Verification Method</label>
                <input v-model="form.verification_method" class="form-control" placeholder="ID check / PIN / Signature" :disabled="!canEditCurrent" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Pickup Window (hours)</label>
                <input v-model.number="form.window_hours" class="form-control" type="number" min="1" max="168" :disabled="!canEditCurrent" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">SLA Hours</label>
                <input v-model.number="form.sla_hours" class="form-control" type="number" min="1" max="168" :disabled="!canEditCurrent" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Reminder Enabled</label>
                <select v-model="form.reminder_enabled" class="form-control" :disabled="!canEditCurrent">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Reminder Frequency (hours)</label>
                <input v-model.number="form.reminder_frequency_hours" class="form-control" type="number" min="1" max="168" :disabled="!canEditCurrent" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Reminder Channels</label>
                <div class="pm-chip-grid">
                  <label v-for="channel in channelList" :key="channel" class="pm-chip">
                    <input type="checkbox" :value="channel" v-model="form.reminder_channels" :disabled="!canEditCurrent" />
                    <span>{{ channel }}</span>
                  </label>
                </div>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Allowed Roles</label>
                <div class="pm-chip-grid">
                  <label v-for="role in roleList" :key="role" class="pm-chip">
                    <input type="checkbox" :value="role" v-model="form.allowed_roles" :disabled="!canEditCurrent" />
                    <span>{{ role }}</span>
                  </label>
                </div>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Notify Recipient</label>
                <select v-model="form.notify_recipient" class="form-control" :disabled="!canEditCurrent">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Notify Staff</label>
                <select v-model="form.notify_staff" class="form-control" :disabled="!canEditCurrent">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Active</label>
                <select v-model="form.active" class="form-control" :disabled="!canEditCurrent">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Notes</label>
                <input v-model="form.notes" class="form-control" placeholder="Optional notes" :disabled="!canEditCurrent" />
              </div>
            </div>
          </div>

          <div class="pm-card">
            <div class="pm-card-header">
              <div>
                <h3>Rules List</h3>
                <p>All configured pickup rules for this method.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" :disabled="!canReadPickup" @click="loadRules">Refresh</button>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Verification</th>
                    <th>Window (h)</th>
                    <th>SLA (h)</th>
                    <th>Reminder</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="rule in filteredRules" :key="rule.id">
                    <td>{{ rule.name }}</td>
                    <td>{{ rule.verification_method || '--' }}</td>
                    <td>{{ rule.window_hours }}</td>
                    <td>{{ rule.sla_hours }}</td>
                    <td>
                      <span class="pm-status-pill" :class="rule.reminder_enabled ? 'success' : 'muted'">
                        {{ rule.reminder_enabled ? 'On' : 'Off' }}
                      </span>
                    </td>
                    <td>
                      <div class="d-flex flex-column gap-1">
                        <span class="pm-status-pill" :class="rule.active ? 'success' : 'muted'">
                          {{ rule.active ? 'Active' : 'Inactive' }}
                        </span>
                        <span v-if="isBreach(rule)" class="pm-status-pill danger">SLA Breach</span>
                      </div>
                    </td>
                    <td class="text-end">
                      <button class="btn btn-outline-primary btn-sm me-2" type="button" :disabled="!canEditRule(rule)" @click="editRule(rule)">Edit</button>
                      <button class="btn btn-outline-danger btn-sm" type="button" :disabled="!canDeletePickup || !canEditRule(rule)" @click="deleteRule(rule.id)">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="filteredRules.length === 0" class="pm-empty-state">No pickup rules yet.</div>
            </div>
          </div>

          <div v-if="editingId" class="pm-card mt-4">
            <div class="pm-card-header">
              <div>
                <h3>Audit Log</h3>
                <p>Recent changes for this rule.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="loadAudit">Refresh</button>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Action</th>
                    <th>User</th>
                    <th>Time</th>
                    <th>Changes</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in auditLogs" :key="log.id">
                    <td>{{ log.action }}</td>
                    <td>
                      <div>{{ log.user_name || 'System' }}</div>
                      <div class="pm-muted small">{{ log.user_role || 'system' }}</div>
                    </td>
                    <td>{{ formatDate(log.created_at) }}</td>
                    <td>
                      <button class="btn btn-sm btn-outline-secondary mb-2" type="button" @click="toggleDiff(log.id)">
                        {{ expandedDiffs.has(log.id) ? 'Hide' : 'Show' }} JSON
                      </button>
                      <div v-if="diffFor(log).length" class="pm-diff-list">
                        <div v-for="item in diffFor(log)" :key="item.key">
                          <strong>{{ item.key }}:</strong>
                          <span class="pm-muted">{{ item.before }}</span>
                          <span class="pm-muted"> -> </span>
                          <span>{{ item.after }}</span>
                        </div>
                      </div>
                      <pre v-if="expandedDiffs.has(log.id)" class="pm-json-block">{{ formatJson(log) }}</pre>
                      <span v-else class="pm-muted">--</span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="auditLogs.length === 0" class="pm-empty-state">No audit entries yet.</div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import AppSidebar from './AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { hasUserPermission } from '../config/permissions'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, required: true },
  ruleType: { type: String, required: true },
})

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const rules = ref([])
const editingId = ref(null)
const channelList = ['email', 'in_app']
const roleList = ['admin', 'manager', 'staff']
const slaSummary = ref(null)
const summaryFilters = ref({
  status: '',
  from: '',
  to: '',
})

const form = ref({
  name: '',
  verification_method: '',
  window_hours: 24,
  sla_hours: 24,
  reminder_enabled: true,
  reminder_frequency_hours: 24,
  reminder_channels: ['email', 'in_app'],
  allowed_roles: ['admin', 'manager', 'staff'],
  notify_recipient: true,
  notify_staff: false,
  active: true,
  notes: '',
})

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})

const formTitle = computed(() => (editingId.value ? 'Edit Rule' : 'New Rule'))
const auditLogs = ref([])
const role = computed(() => authState.user?.role || 'staff')
const canReadPickup = computed(() => hasUserPermission(authState.user, 'pickup', 'read'))
const canCreatePickup = computed(() => hasUserPermission(authState.user, 'pickup', 'create'))
const canEditPickup = computed(() => hasUserPermission(authState.user, 'pickup', 'edit'))
const canDeletePickup = computed(() => hasUserPermission(authState.user, 'pickup', 'delete'))
const canExportPickup = computed(() => hasUserPermission(authState.user, 'pickup', 'export'))
const canEditRule = (rule) => {
  if (!canEditPickup.value) return false
  if (!rule) return canEditPickup.value
  const allowed = rule.allowed_roles || []
  if (!allowed.length) return canEditPickup.value
  return allowed.includes(role.value)
}
const currentRule = computed(() => rules.value.find((item) => item.id === editingId.value) || null)
const canEditCurrent = computed(() => (editingId.value ? canEditRule(currentRule.value) : canCreatePickup.value))

const filteredRules = computed(() => {
  if (!searchQuery.value) return rules.value
  const term = searchQuery.value.toLowerCase()
  return rules.value.filter((rule) =>
    [rule.name, rule.verification_method, rule.notes].filter(Boolean).some((val) => String(val).toLowerCase().includes(term))
  )
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

const resetForm = () => {
  editingId.value = null
  form.value = {
    name: '',
    verification_method: '',
    window_hours: 24,
    sla_hours: 24,
    reminder_enabled: true,
    reminder_frequency_hours: 24,
    reminder_channels: ['email', 'in_app'],
    allowed_roles: ['admin', 'manager', 'staff'],
    notify_recipient: true,
    notify_staff: false,
    active: true,
    notes: '',
  }
  auditLogs.value = []
}

const loadRules = async () => {
  if (!canReadPickup.value) {
    rules.value = []
    return
  }

  const { data } = await client.get('/pickup-rules', { params: { type: props.ruleType } })
  rules.value = data?.data || []
}

const loadSummary = async () => {
  if (!canReadPickup.value) {
    slaSummary.value = null
    return
  }

  const params = {
    type: props.ruleType,
    status: summaryFilters.value.status || undefined,
    from: summaryFilters.value.from || undefined,
    to: summaryFilters.value.to || undefined,
  }
  const { data } = await client.get('/pickup-rules/sla/summary', { params })
  slaSummary.value = data?.data || null
}

const exportSla = () => {
  if (!canExportPickup.value) return

  const params = new URLSearchParams()
  if (props.ruleType) params.append('type', props.ruleType)
  if (summaryFilters.value.status) params.append('status', summaryFilters.value.status)
  if (summaryFilters.value.from) params.append('from', summaryFilters.value.from)
  if (summaryFilters.value.to) params.append('to', summaryFilters.value.to)
  window.location.href = `/api/v1/pickup-rules/sla/export?${params.toString()}`
}

const saveRule = async () => {
  if (!canEditCurrent.value) return

  const payload = {
    ...form.value,
    reminder_channels: form.value.reminder_channels || [],
    allowed_roles: form.value.allowed_roles || [],
    type: props.ruleType,
  }
  if (editingId.value) {
    const { data } = await client.put(`/pickup-rules/${editingId.value}`, payload)
    const updated = data?.data
    rules.value = rules.value.map((rule) => (rule.id === updated.id ? updated : rule))
    await loadAudit()
  } else {
    const { data } = await client.post('/pickup-rules', payload)
    if (data?.data) {
      rules.value = [data.data, ...rules.value]
    }
  }
  loadSummary()
  resetForm()
}

const editRule = (rule) => {
  editingId.value = rule.id
  form.value = {
    name: rule.name || '',
    verification_method: rule.verification_method || '',
    window_hours: rule.window_hours || 24,
    sla_hours: rule.sla_hours || 24,
    reminder_enabled: rule.reminder_enabled ?? true,
    reminder_frequency_hours: rule.reminder_frequency_hours || 24,
    reminder_channels: rule.reminder_channels || [],
    allowed_roles: rule.allowed_roles || ['admin', 'manager', 'staff'],
    notify_recipient: rule.notify_recipient ?? true,
    notify_staff: rule.notify_staff ?? false,
    active: rule.active ?? true,
    notes: rule.notes || '',
  }
  loadAudit()
}

const deleteRule = async (id) => {
  if (!canDeletePickup.value) return

  await client.delete(`/pickup-rules/${id}`)
  rules.value = rules.value.filter((rule) => rule.id !== id)
  loadSummary()
}

const loadAudit = async () => {
  if (!editingId.value || !canReadPickup.value) return
  const { data } = await client.get(`/pickup-rules/${editingId.value}/audits`)
  auditLogs.value = data?.data || []
}

const isBreach = (rule) => {
  if (!rule) return false
  return (rule.window_hours || 0) > (rule.sla_hours || 0)
}

const summaryForType = computed(() => {
  const summary = slaSummary.value?.summary || {}
  return summary[props.ruleType] || { total: 0, breaches: 0 }
})

const summaryBreaches = computed(() => {
  const items = slaSummary.value?.breaches || []
  return items.filter((item) => item.type === props.ruleType)
})

const expandedDiffs = ref(new Set())
const toggleDiff = (id) => {
  if (expandedDiffs.value.has(id)) {
    expandedDiffs.value.delete(id)
  } else {
    expandedDiffs.value.add(id)
  }
}

const diffFor = (log) => {
  if (!log?.before_json || !log?.after_json) return []
  const before = log.before_json
  const after = log.after_json
  const keys = new Set([...Object.keys(before), ...Object.keys(after)])
  const ignore = new Set(['updated_at', 'created_at'])
  const diffs = []
  keys.forEach((key) => {
    if (ignore.has(key)) return
    const a = before[key]
    const b = after[key]
    if (JSON.stringify(a) !== JSON.stringify(b)) {
      diffs.push({
        key,
        before: a === null || a === undefined ? '--' : JSON.stringify(a),
        after: b === null || b === undefined ? '--' : JSON.stringify(b),
      })
    }
  })
  return diffs
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString()
}

const formatJson = (log) => {
  return JSON.stringify({ before: log.before_json, after: log.after_json }, null, 2)
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadRules()
  loadSummary()
})
</script>
