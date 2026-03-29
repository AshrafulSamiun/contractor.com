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
          <input v-model="searchQuery" class="form-control" placeholder="Search SLA breaches..." />
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
              <h2>Approval SLA Dashboard</h2>
              <div class="pm-page-subtitle">Track approvals that exceed SLA thresholds.</div>
            </div>
          </div>

          <div class="pm-metrics-grid">
            <div v-for="module in moduleList" :key="module" class="pm-metric-card">
              <p>{{ moduleLabels[module] }}</p>
              <h3>{{ summary[module]?.overdue || 0 }}</h3>
              <span class="pm-muted">SLA {{ summary[module]?.sla_hours || 0 }}h</span>
            </div>
          </div>

          <div class="pm-card">
            <div class="pm-card-header">
              <div>
                <h3>Overdue Approvals</h3>
                <p>Items awaiting action beyond SLA.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="loadDashboard">Refresh</button>
                <button class="btn btn-outline-primary btn-sm" type="button" :disabled="!canExportApprovalSla" @click="exportSla">Export CSV</button>
              </div>
            </div>
            <div class="pm-approval-export">
              <div class="pm-form-field">
                <label class="pm-field-label">Module</label>
                <select v-model="filters.module" class="form-control">
                  <option value="">All</option>
                  <option v-for="module in moduleList" :key="module" :value="module">{{ moduleLabels[module] }}</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Status</label>
                <select v-model="filters.status" class="form-control">
                  <option value="">All</option>
                  <option value="submitted">Submitted</option>
                  <option value="approved">Approved</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Updated From</label>
                <input v-model="filters.from" class="form-control" type="date" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Updated To</label>
                <input v-model="filters.to" class="form-control" type="date" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Min Age (hours)</label>
                <input v-model.number="filters.min_age_hours" class="form-control" type="number" min="1" max="720" />
              </div>
              <div class="pm-form-actions">
                <button class="btn btn-outline-primary" type="button" @click="loadDashboard">Apply Filters</button>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Module</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th>Age (hours)</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredItems" :key="`${item.module}-${item.id}`">
                    <td>{{ moduleLabels[item.module] || item.module }}</td>
                    <td>{{ item.reference || item.id }}</td>
                    <td><span class="pm-status-pill warning">{{ item.status }}</span></td>
                    <td>{{ item.age_hours }}</td>
                    <td>{{ item.updated_at }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-if="filteredItems.length === 0" class="pm-empty-state">No SLA breaches right now.</div>
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
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { hasUserPermission } from '../config/permissions'

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const moduleList = ['daily_report', 'incident_report', 'timesheet']
const moduleLabels = {
  daily_report: 'Daily Reports',
  incident_report: 'Incident Reports',
  timesheet: 'Timesheets',
}

const summary = ref({})
const items = ref([])
const filters = ref({
  module: '',
  status: '',
  from: '',
  to: '',
  min_age_hours: '',
})

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})
const canExportApprovalSla = computed(() => hasUserPermission(authState.user, 'workforce', 'export'))

const filteredItems = computed(() => {
  if (!searchQuery.value) return items.value
  const term = searchQuery.value.toLowerCase()
  return items.value.filter((item) =>
    [item.module, item.reference, item.status]
      .filter(Boolean)
      .some((val) => String(val).toLowerCase().includes(term))
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

const loadDashboard = async () => {
  const params = {}
  if (filters.value.module) params.module = filters.value.module
  if (filters.value.status) params.status = filters.value.status
  if (filters.value.from) params.from = filters.value.from
  if (filters.value.to) params.to = filters.value.to
  if (filters.value.min_age_hours) params.min_age_hours = filters.value.min_age_hours
  const { data } = await client.get('/workforce/approvals/sla', { params })
  summary.value = data?.data?.summary || {}
  items.value = data?.data?.items || []
}

const exportSla = () => {
  if (!canExportApprovalSla.value) return

  const params = new URLSearchParams()
  if (filters.value.module) params.append('module', filters.value.module)
  if (filters.value.status) params.append('status', filters.value.status)
  if (filters.value.from) params.append('from', filters.value.from)
  if (filters.value.to) params.append('to', filters.value.to)
  if (filters.value.min_age_hours) params.append('min_age_hours', filters.value.min_age_hours)
  const query = params.toString()
  window.location.href = `/api/v1/workforce/approvals/sla/export${query ? `?${query}` : ''}`
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadDashboard()
})
</script>
