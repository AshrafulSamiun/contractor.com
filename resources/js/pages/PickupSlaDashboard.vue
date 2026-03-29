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
          <input v-model="searchQuery" class="form-control" placeholder="Search pickup SLA..." />
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
              <h2>Pickup SLA Dashboard</h2>
              <div class="pm-page-subtitle">Track pickup rule SLA breaches.</div>
            </div>
          </div>

          <div class="pm-card p-4 mb-4">
            <div class="pm-card-header">
              <div>
                <h3>Filters</h3>
                <p>Filter breaches by type, status, and date.</p>
              </div>
              <div class="pm-card-actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="loadSummary">Refresh</button>
                <button class="btn btn-outline-primary btn-sm" type="button" :disabled="!canExportPickupSla" @click="exportCsv">Export CSV</button>
              </div>
            </div>
            <div class="pm-approval-export">
              <div class="pm-form-field">
                <label class="pm-field-label">Type</label>
                <select v-model="filters.type" class="form-control">
                  <option value="">All</option>
                  <option v-for="type in types" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Status</label>
                <select v-model="filters.status" class="form-control">
                  <option value="">All</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
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
              <div class="pm-form-actions">
                <button class="btn btn-outline-primary" type="button" @click="loadSummary">Apply Filters</button>
              </div>
            </div>
          </div>

          <div class="pm-metrics-grid">
            <div v-for="type in types" :key="type.value" class="pm-metric-card">
              <p>{{ type.label }}</p>
              <h3>{{ summary[type.value]?.breaches || 0 }}</h3>
              <span class="pm-muted">{{ summary[type.value]?.total || 0 }} total</span>
            </div>
          </div>
          <div class="pm-card mt-4">
            <div class="pm-card-header">
              <div>
                <h3>Breaches by Type</h3>
                <p>Visual distribution of SLA breaches.</p>
              </div>
            </div>
            <div class="pm-chart">
              <div v-for="item in chartItems" :key="item.type" class="pm-chart-row">
                <div class="pm-chart-label">{{ item.label }}</div>
                <div class="pm-chart-track">
                  <div class="pm-chart-bar" :style="{ width: item.percent + '%'}"></div>
                </div>
                <div class="pm-chart-value">{{ item.value }}</div>
              </div>
            </div>
          </div>

          <div class="pm-card">
            <div class="pm-card-header">
              <div>
                <h3>Breached Rules</h3>
                <p>Pickup rules with window &gt; SLA.</p>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>Type</th>
                    <th>Rule</th>
                    <th>Window (h)</th>
                    <th>SLA (h)</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredBreaches" :key="item.id">
                    <td>{{ typeLabel(item.type) }}</td>
                    <td>{{ item.name }}</td>
                    <td>{{ item.window_hours }}</td>
                    <td>{{ item.sla_hours }}</td>
                    <td>{{ item.updated_at }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-if="filteredBreaches.length === 0" class="pm-empty-state">No SLA breaches.</div>
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
const summary = ref({})
const breaches = ref([])

const filters = ref({
  type: '',
  status: '',
  from: '',
  to: '',
})

const types = [
  { value: 'front_desk', label: 'Front Desk' },
  { value: 'facility_locker', label: 'Facility Locker' },
  { value: 'external_locker', label: 'External Locker' },
  { value: 'counter_staff', label: 'Counter Staff' },
]

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})
const canExportPickupSla = computed(() => hasUserPermission(authState.user, 'pickup', 'export'))

const filteredBreaches = computed(() => {
  if (!searchQuery.value) return breaches.value
  const term = searchQuery.value.toLowerCase()
  return breaches.value.filter((item) =>
    [item.name, item.type].filter(Boolean).some((val) => String(val).toLowerCase().includes(term))
  )
})

const typeLabel = (type) => types.find((t) => t.value === type)?.label || type
const chartItems = computed(() => {
  const max = Math.max(1, ...types.map((t) => summary.value[t.value]?.breaches || 0))
  return types.map((t) => {
    const value = summary.value[t.value]?.breaches || 0
    return {
      type: t.value,
      label: t.label,
      value,
      percent: Math.round((value / max) * 100),
    }
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

const loadSummary = async () => {
  const params = {
    type: filters.value.type || undefined,
    status: filters.value.status || undefined,
    from: filters.value.from || undefined,
    to: filters.value.to || undefined,
  }
  const { data } = await client.get('/pickup-rules/sla/summary', { params })
  summary.value = data?.data?.summary || {}
  breaches.value = data?.data?.breaches || []
}

const exportCsv = () => {
  if (!canExportPickupSla.value) return

  const params = new URLSearchParams()
  if (filters.value.type) params.append('type', filters.value.type)
  if (filters.value.status) params.append('status', filters.value.status)
  if (filters.value.from) params.append('from', filters.value.from)
  if (filters.value.to) params.append('to', filters.value.to)
  window.location.href = `/api/v1/pickup-rules/sla/export?${params.toString()}`
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadSummary()
})
</script>
