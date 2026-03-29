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
          <input v-model="filters.q" class="form-control" placeholder="Search notifications..." />
          <button v-if="filters.q" class="pm-clear-btn" type="button" @click="filters.q = ''">&times;</button>
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
            <button class="btn btn-outline-primary" type="button" @click="reload">Refresh</button>
          </div>

          <div class="pm-dash-card pm-panel mb-4">
            <div class="pm-panel-head">
              <div>
                <h4>Delivery Summary</h4>
                <p class="pm-muted">Quick view of latest delivery outcomes.</p>
              </div>
            </div>
            <div class="pm-stats-grid">
              <div class="pm-stat-card">
                <div class="pm-stat-label">Sent</div>
                <div class="pm-stat-value">{{ summary.sent }}</div>
              </div>
              <div class="pm-stat-card">
                <div class="pm-stat-label">Failed</div>
                <div class="pm-stat-value text-warning">{{ summary.failed }}</div>
              </div>
              <div class="pm-stat-card">
                <div class="pm-stat-label">Queued</div>
                <div class="pm-stat-value">{{ summary.queued }}</div>
              </div>
              <div class="pm-stat-card">
                <div class="pm-stat-label">Email</div>
                <div class="pm-stat-value">{{ summary.email }}</div>
              </div>
              <div class="pm-stat-card">
                <div class="pm-stat-label">SMS</div>
                <div class="pm-stat-value">{{ summary.sms }}</div>
              </div>
              <div class="pm-stat-card">
                <div class="pm-stat-label">In-App</div>
                <div class="pm-stat-value">{{ summary.in_app }}</div>
              </div>
            </div>
          </div>

          <div class="pm-dash-card pm-panel mb-4">
            <div class="pm-panel-head">
              <div>
                <h4>Filters</h4>
                <p class="pm-muted">Filter by date range, channel, or status.</p>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-md-3">
                <label class="pm-field-label">From</label>
                <input v-model="filters.from" type="date" class="form-control" />
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">To</label>
                <input v-model="filters.to" type="date" class="form-control" />
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">Channel</label>
                <select v-model="filters.channel" class="form-control">
                  <option value="">All</option>
                  <option value="email">Email</option>
                  <option value="sms">SMS</option>
                  <option value="in_app">In-App</option>
                  <option value="todo">To-Do</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">Status</label>
                <select v-model="filters.status" class="form-control">
                  <option value="">All</option>
                  <option value="sent">Sent</option>
                  <option value="failed">Failed</option>
                  <option value="queued">Queued</option>
                  <option value="skipped_disabled">Skipped</option>
                  <option value="quiet_hours">Quiet Hours</option>
                </select>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-primary" type="button" @click="reload">Apply Filters</button>
              <button class="btn btn-outline-primary" type="button" @click="resetFilters">Reset</button>
            </div>
          </div>

          <div class="pm-dash-card pm-panel">
            <div class="pm-panel-head">
              <div>
                <h4>Recent Notifications</h4>
                <p class="pm-muted">Latest 100 delivery attempts.</p>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table pm-dash-table">
                <thead>
                  <tr>
                    <th>Channel</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Context</th>
                    <th>Created</th>
                    <th>Error</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in logs" :key="log.id">
                    <td class="text-capitalize">{{ formatChannel(log.channel) }}</td>
                    <td>{{ log.to || '-' }}</td>
                    <td>
                      <span :class="['pm-status-pill', statusClass(log.status)]">
                        {{ formatStatus(log.status) }}
                      </span>
                    </td>
                    <td>{{ formatContext(log) }}</td>
                    <td>{{ formatDate(log.created_at) }}</td>
                    <td class="pm-muted">{{ log.error || '-' }}</td>
                  </tr>
                  <tr v-if="!logs.length">
                    <td colspan="6" class="text-center pm-muted">No notification logs yet.</td>
                  </tr>
                </tbody>
              </table>
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

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, required: true },
  event: { type: String, default: '' },
})

const logs = ref([])
const filters = ref({
  q: '',
  from: '',
  to: '',
  channel: '',
  status: '',
})
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
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

const summary = computed(() => {
  const base = { sent: 0, failed: 0, queued: 0, email: 0, sms: 0, in_app: 0 }
  logs.value.forEach((log) => {
    if (log.status === 'sent') base.sent += 1
    if (log.status === 'failed') base.failed += 1
    if (log.status === 'queued') base.queued += 1
    if (log.channel === 'email') base.email += 1
    if (log.channel === 'sms') base.sms += 1
    if (log.channel === 'in_app') base.in_app += 1
  })
  return base
})

const statusClass = (status) => {
  if (status === 'sent') return 'success'
  if (status === 'failed') return 'warning'
  if (status === 'queued') return 'pending'
  return 'pending'
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

const formatStatus = (value) => {
  if (!value) return 'unknown'
  return value.replace(/_/g, ' ')
}

const formatChannel = (value) => {
  if (!value) return '-'
  return value.replace(/_/g, ' ')
}

const formatContext = (log) => {
  if (log.context && log.context.length > 2) return log.context
  if (!log.context_json) return '-'
  try {
    const data = JSON.parse(log.context_json)
    if (data?.event) return data.event.replace(/_/g, ' ')
    if (data?.status) return data.status.replace(/_/g, ' ')
  } catch {
    return log.context || '-'
  }
  return '-'
}

const loadLogs = async () => {
  try {
    const params = {}
    if (filters.value.q) params.q = filters.value.q
    if (filters.value.from) params.from = filters.value.from
    if (filters.value.to) params.to = filters.value.to
    if (filters.value.channel) params.channel = filters.value.channel
    if (filters.value.status) params.status = filters.value.status
    if (props.event) params.event = props.event
    const { data } = await client.get('/notifications', { params })
    if (data?.success) {
      logs.value = data.data || []
    }
  } catch {
    logs.value = []
  }
}

const reload = () => {
  loadLogs()
}

const resetFilters = () => {
  filters.value = { q: '', from: '', to: '', channel: '', status: '' }
  loadLogs()
}

onMounted(loadLogs)

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
})
</script>
