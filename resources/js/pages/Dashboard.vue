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
          <input
            v-model="searchQuery"
            class="form-control"
            placeholder="Search parcels, residents, tracking..."
            @keydown.enter.prevent="runGlobalSearch"
          />
          <button
            v-if="searchQuery"
            class="pm-clear-btn"
            type="button"
            aria-label="Clear search"
            @click="searchQuery = ''"
          >
            &times;
          </button>
        </div>
        <div class="pm-topbar-actions">
          <button class="pm-icon-btn" type="button" aria-label="Notifications" @click="goNotifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
            <span v-if="showNotificationBadge" class="pm-topbar-badge">
              {{ notificationAttentionCount > 9 ? '9+' : notificationAttentionCount }}
            </span>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings" @click="goSettings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
          <div class="pm-topbar-avatar">{{ userInitials }}</div>
          <span class="pm-topbar-name">{{ userName }}</span>
          <span class="pm-topbar-pill">{{ userRoleLabel }}</span>
          <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="m7 10 5 5 5-5H7Z" />
            </svg>
          </button>
          <div v-if="userMenuOpen" class="pm-user-menu">
            <button class="pm-user-item" type="button" @click="goProfile">Profile</button>
            <button class="pm-user-item" type="button" @click="goAccount">Account</button>
            <button class="pm-user-item danger" type="button" @click="logout">Log out</button>
          </div>
        </div>
      </div>

      <div class="container pm-dashboard-wrap" :class="{ 'pm-dashboard-loading': isLoading }">
      <section class="pm-dash-hero">
        <div class="pm-dashboard-header">
          <div>
            <h2>Welcome back, {{ userName }}</h2>
            <p class="pm-muted">Here is your parcel overview and activity for today.</p>
          </div>
          <div class="pm-dashboard-actions">
            <button class="btn btn-outline-primary" type="button" @click="downloadReport">Download Report</button>
            <button class="btn btn-primary" type="button" @click="goParcels">Add Parcel</button>
            <button class="btn btn-outline-primary" type="button" @click="goPlanHistory">Plan History</button>
          </div>
        </div>

      <div class="pm-dash-grid">
        <div class="pm-dash-card pm-kpi pm-kpi-card">
          <div class="pm-kpi-label">Total Parcels</div>
          <div class="pm-kpi-value" :class="{ 'pm-skeleton-text': isLoading }">{{ isLoading ? '--' : kpis.total }}</div>
          <div class="pm-kpi-meta">All time</div>
        </div>
        <div class="pm-dash-card pm-kpi pm-kpi-card">
          <div class="pm-kpi-label">Pending Pickup</div>
          <div class="pm-kpi-value" :class="{ 'pm-skeleton-text': isLoading }">{{ isLoading ? '--' : kpis.pending }}</div>
          <div class="pm-kpi-meta">Awaiting collection</div>
        </div>
        <div class="pm-dash-card pm-kpi pm-kpi-card">
          <div class="pm-kpi-label">Delivered Today</div>
          <div class="pm-kpi-value" :class="{ 'pm-skeleton-text': isLoading }">{{ isLoading ? '--' : kpis.delivered_today }}</div>
          <div class="pm-kpi-meta">Today</div>
        </div>
        <div class="pm-dash-card pm-kpi pm-kpi-card">
          <div class="pm-kpi-label">Alerts</div>
          <div class="pm-kpi-value" :class="{ 'pm-skeleton-text': isLoading }">{{ isLoading ? '--' : kpis.alerts }}</div>
          <div class="pm-kpi-meta">Needs review</div>
        </div>
      </div>
      </section>

      <div v-if="loadError" class="pm-dashboard-alert">
        <span>{{ loadError }}</span>
        <button class="btn btn-outline-primary btn-sm" type="button" @click="loadDashboard">Retry</button>
      </div>

      <div class="pm-dash-panels">
        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Weekly Activity</h4>
              <p class="pm-muted">Parcel intake, pickups, and delivery confirmations.</p>
            </div>
            <span class="pm-chip">Last 7 days</span>
          </div>
          <div class="pm-chart-placeholder">
            <div v-if="isLoading" class="pm-chart-loading">Loading trend...</div>
            <div v-else class="pm-chart-bars">
              <span v-for="(bar, index) in chartBars" :key="`bar-${index}`" :style="{ height: `${bar}%` }"></span>
            </div>
          </div>
        </section>

        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Notification Health</h4>
              <p class="pm-muted">Email and SMS delivery status.</p>
            </div>
            <span class="pm-chip pm-chip-success">Stable</span>
          </div>
          <ul class="pm-status-list">
            <li>
              <span>Email sent</span>
              <strong>{{ notifications.email_sent }}</strong>
            </li>
            <li>
              <span>SMS sent</span>
              <strong>{{ notifications.sms_sent }}</strong>
            </li>
            <li>
              <span>Failed notifications</span>
              <strong class="text-danger">{{ notifications.failed }}</strong>
            </li>
            <li>
              <span>System alerts</span>
              <strong class="text-warning">{{ notifications.system_alerts }}</strong>
            </li>
          </ul>
          <button class="btn btn-outline-primary w-100" type="button" @click="goNotifications">View All Alerts</button>
        </section>
      </div>

      <div class="pm-dash-panels">
        <section class="pm-dash-card pm-panel pm-panel-wide">
          <div class="pm-panel-head">
            <div>
              <h4>Recent Parcels</h4>
              <p class="pm-muted">Latest parcels processed by the front desk.</p>
            </div>
            <button class="btn btn-outline-primary btn-sm" type="button" @click="goParcels">View Queue</button>
          </div>
          <div class="table-responsive">
            <table class="table pm-dash-table">
              <thead>
                <tr>
                  <th>Parcel ID</th>
                  <th>Resident</th>
                  <th>Status</th>
                  <th>Location</th>
                  <th>Updated</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="isLoading">
                  <td colspan="5" class="text-center pm-muted">Loading parcels...</td>
                </tr>
                <template v-else-if="filteredRecentParcels.length">
                  <tr v-for="parcel in filteredRecentParcels" :key="parcel.id">
                    <td>{{ parcel.id }}</td>
                    <td>{{ parcel.resident }}</td>
                    <td><span :class="['pm-status-pill', parcel.statusClass]">{{ parcel.status }}</span></td>
                    <td>{{ parcel.location }}</td>
                    <td>{{ parcel.updated }}</td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="5" class="text-center pm-muted">
                    {{ searchQuery.trim() ? 'No parcels found for this search.' : 'No recent parcels yet.' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Plan & Billing</h4>
              <p class="pm-muted">Your current subscription status.</p>
            </div>
            <span class="pm-chip">{{ planLabel }}</span>
          </div>
          <div class="pm-plan-summary">
            <div>
              <div class="pm-plan-label">Next Billing</div>
              <strong>{{ planInfo.next_billing || '--' }}</strong>
            </div>
            <div>
              <div class="pm-plan-label">Active Users</div>
              <strong>{{ planInfo.active_users ?? 0 }}</strong>
            </div>
            <div>
              <div class="pm-plan-label">Monthly Cost</div>
              <strong>{{ planMonthlyCost }}</strong>
            </div>
          </div>
          <button class="btn btn-primary w-100" type="button" @click="goPlans">Upgrade Plan</button>
        </section>
      </div>

      <div class="pm-dash-panels">
        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Quick Actions</h4>
              <p class="pm-muted">Common actions for your team.</p>
            </div>
          </div>
          <div class="pm-action-grid">
            <button class="pm-action-card" type="button" @click="goScan">Scan QR</button>
            <button v-if="isAdmin" class="pm-action-card" type="button" @click="goUsers">Create User</button>
            <button class="pm-action-card" type="button" @click="downloadReport">Export CSV</button>
            <button class="pm-action-card" type="button" @click="goParcels">Schedule Pickup</button>
          </div>
        </section>

        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Facility Overview</h4>
              <p class="pm-muted">Parcels by location and peak times.</p>
            </div>
          </div>
          <ul class="pm-status-list">
            <li v-for="loc in locations" :key="loc.facility_name">
              <span>{{ loc.facility_name }}</span>
              <strong>{{ loc.total }}</strong>
            </li>
          </ul>
          <div class="pm-peak-note">Peak hours: 11:00 AM - 2:00 PM</div>
        </section>
      </div>

      <div class="pm-dash-panels pm-dash-panels-compact">
        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Operations Pulse</h4>
              <p class="pm-muted">Fast view of delivery throughput and system pressure.</p>
            </div>
          </div>
          <div class="pm-pulse-grid">
            <div class="pm-pulse-card">
              <span>Delivered Rate</span>
              <strong>{{ deliveredRate }}%</strong>
            </div>
            <div class="pm-pulse-card">
              <span>Unresolved Items</span>
              <strong>{{ unresolvedCount }}</strong>
            </div>
            <div class="pm-pulse-card">
              <span>Message Health</span>
              <strong>{{ messageHealth }}</strong>
            </div>
          </div>
        </section>

        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Attention Queue</h4>
              <p class="pm-muted">Priority parcels that need follow-up.</p>
            </div>
          </div>
          <ul class="pm-attention-list" v-if="attentionQueue.length">
            <li v-for="item in attentionQueue" :key="item.id">
              <div>
                <strong>{{ item.id }}</strong>
                <span>{{ item.resident }}</span>
              </div>
              <span :class="['pm-status-pill', item.statusClass]">{{ item.status }}</span>
            </li>
          </ul>
          <div v-else class="pm-muted">No urgent parcels right now.</div>
        </section>
      </div>

      <div class="pm-dash-panels pm-dash-panels-compact">
        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>SLA Snapshot</h4>
              <p class="pm-muted">Live pressure indicators for operations and service quality.</p>
            </div>
          </div>
          <div class="pm-sla-stack">
            <div class="pm-sla-item">
              <div class="pm-sla-meta">
                <span>Pickup Queue Pressure</span>
                <strong>{{ pickupPressure }}%</strong>
              </div>
              <div class="pm-sla-track"><span :style="{ width: `${pickupPressure}%` }"></span></div>
            </div>
            <div class="pm-sla-item">
              <div class="pm-sla-meta">
                <span>Alert Pressure</span>
                <strong>{{ alertPressure }}%</strong>
              </div>
              <div class="pm-sla-track"><span :style="{ width: `${alertPressure}%` }"></span></div>
            </div>
            <div class="pm-sla-item">
              <div class="pm-sla-meta">
                <span>Ops Stability</span>
                <strong>{{ opsStability }}%</strong>
              </div>
              <div class="pm-sla-track"><span :style="{ width: `${opsStability}%` }"></span></div>
            </div>
          </div>
        </section>

        <section class="pm-dash-card pm-panel">
          <div class="pm-panel-head">
            <div>
              <h4>Team Checklist</h4>
              <p class="pm-muted">Quick operational checks for today.</p>
            </div>
          </div>
          <ul class="pm-checklist">
            <li v-for="item in checklistItems" :key="item.label" :class="{ done: item.done }">
              <span class="pm-check-indicator">{{ item.done ? 'Done' : 'Pending' }}</span>
              <div>
                <strong>{{ item.label }}</strong>
                <small>{{ item.note }}</small>
              </div>
            </li>
          </ul>
        </section>
      </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import client from '../api/client'
import { clearToken } from '../api/auth'
import { authState } from '../store/auth'
import AppSidebar from '../components/AppSidebar.vue'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const isLoading = ref(true)
const loadError = ref('')
const kpis = ref({ total: 0, pending: 0, delivered_today: 0, alerts: 0 })
const notifications = ref({ email_sent: 0, sms_sent: 0, failed: 0, system_alerts: 0 })
const weeklyCounts = ref([])
const recentParcels = ref([])
const locations = ref([])
const planInfo = ref({ name: 'standard', next_billing: '--', active_users: 0 })
const isAdmin = computed(() => authState.user?.role === 'admin')
const userRoleLabel = computed(() => {
  const role = String(authState.user?.role || 'user').trim()
  if (!role) return 'User'
  return role.charAt(0).toUpperCase() + role.slice(1)
})
const userInitials = computed(() => {
  const name = String(userName.value || authState.user?.name || 'User').trim()
  if (!name) return 'U'
  const parts = name.split(/\s+/).filter(Boolean)
  const first = parts[0]?.[0] || ''
  const second = parts[1]?.[0] || ''
  return (first + second || first).toUpperCase()
})
const normalizedSearch = computed(() => searchQuery.value.trim().toLowerCase())
const filteredRecentParcels = computed(() => {
  const query = normalizedSearch.value
  if (!query) return recentParcels.value
  return recentParcels.value.filter((parcel) => (
    String(parcel.id || '').toLowerCase().includes(query) ||
    String(parcel.resident || '').toLowerCase().includes(query) ||
    String(parcel.status || '').toLowerCase().includes(query) ||
    String(parcel.location || '').toLowerCase().includes(query) ||
    String(parcel.updated || '').toLowerCase().includes(query)
  ))
})
const chartBars = computed(() => {
  if (!weeklyCounts.value.length) return [18, 18, 18, 18, 18, 18, 18]
  const max = Math.max(...weeklyCounts.value, 0)
  if (max <= 0) return [18, 18, 18, 18, 18, 18, 18]
  return weeklyCounts.value.map((count) => {
    const normalized = Math.round((Number(count || 0) / max) * 100)
    if (normalized <= 0) return 8
    return Math.max(normalized, 12)
  })
})
const notificationAttentionCount = computed(
  () => Number(notifications.value.failed || 0) + Number(notifications.value.system_alerts || 0),
)
const showNotificationBadge = computed(() => notificationAttentionCount.value > 0)
const planLabel = computed(() => {
  const name = (planInfo.value.name || 'standard').toLowerCase()
  if (name === 'basic') return 'Basic'
  if (name === 'enterprise') return 'Enterprise'
  return 'Standard'
})
const planPrice = computed(() => {
  const name = (planInfo.value.name || 'standard').toLowerCase()
  if (name === 'basic') return 15
  if (name === 'enterprise') return 25
  return 20
})
const planMonthlyCost = computed(() => {
  const users = Number(planInfo.value.active_users || 0)
  if (!users) return '--'
  return `$${users * planPrice.value}`
})
const deliveredRate = computed(() => {
  const total = Number(kpis.value.total || 0)
  if (!total) return 0
  return Math.min(100, Math.round((Number(kpis.value.delivered_today || 0) / total) * 100))
})
const unresolvedCount = computed(
  () => Number(kpis.value.alerts || 0) + Number(notifications.value.failed || 0) + Number(notifications.value.system_alerts || 0)
)
const messageHealth = computed(() => {
  const sent = Number(notifications.value.email_sent || 0) + Number(notifications.value.sms_sent || 0)
  const failed = Number(notifications.value.failed || 0)
  if (!sent) return 'N/A'
  const score = Math.max(0, Math.round(((sent - failed) / sent) * 100))
  return `${score}%`
})
const attentionQueue = computed(() => recentParcels.value.filter((item) => item.statusClass !== 'success').slice(0, 4))
const pickupPressure = computed(() => {
  const pending = Number(kpis.value.pending || 0)
  const delivered = Number(kpis.value.delivered_today || 0)
  const pool = pending + delivered
  if (!pool) return 0
  return Math.min(100, Math.round((pending / pool) * 100))
})
const alertPressure = computed(() => {
  const alerts = Number(kpis.value.alerts || 0)
  return Math.min(100, alerts * 12)
})
const opsStability = computed(() => {
  const unresolved = unresolvedCount.value
  return Math.max(0, 100 - Math.min(100, unresolved * 10))
})
const checklistItems = computed(() => [
  {
    label: 'Pickup queue within target',
    note: 'Pending pickup should stay below active delivery volume.',
    done: pickupPressure.value <= 45,
  },
  {
    label: 'Critical alerts reviewed',
    note: 'No unresolved high-priority system notifications.',
    done: Number(kpis.value.alerts || 0) === 0 && Number(notifications.value.system_alerts || 0) === 0,
  },
  {
    label: 'Notification reliability healthy',
    note: 'Failed notifications should remain minimal.',
    done: Number(notifications.value.failed || 0) <= 1,
  },
])

const mapStatus = (status) => {
  if (status === 'delivered') return { label: 'Delivered', cls: 'success' }
  if (status === 'held') return { label: 'Held', cls: 'warning' }
  if (status === 'returned') return { label: 'Returned', cls: 'warning' }
  return { label: 'Pending', cls: 'pending' }
}

const loadDashboard = async () => {
  isLoading.value = true
  loadError.value = ''
  try {
    if (authState.user?.name) userName.value = authState.user.name
    const { data } = await client.get('/dashboard')
    const payload = data?.data
    if (!data?.success || !payload) {
      throw new Error('Dashboard response is invalid.')
    }
    kpis.value = {
      total: Number(payload.kpis?.total || 0),
      pending: Number(payload.kpis?.pending || 0),
      delivered_today: Number(payload.kpis?.delivered_today || 0),
      alerts: Number(payload.kpis?.alerts || 0),
    }
    notifications.value = {
      email_sent: Number(payload.notifications?.email_sent || 0),
      sms_sent: Number(payload.notifications?.sms_sent || 0),
      failed: Number(payload.notifications?.failed || 0),
      system_alerts: Number(payload.notifications?.system_alerts || 0),
    }
    weeklyCounts.value = Array.isArray(payload.weekly)
      ? payload.weekly.map((item) => Number(item?.count || 0))
      : []
    recentParcels.value = (Array.isArray(payload.recent) ? payload.recent : []).map((parcel) => {
      const meta = mapStatus(parcel.status)
      return {
        id: `PK-${parcel.id}`,
        resident: parcel.recipient_name,
        status: meta.label,
        statusClass: meta.cls,
        location: parcel.location || parcel.facility_name || '--',
        updated: parcel.updated_at ? new Date(parcel.updated_at).toLocaleString() : '--',
      }
    })
    locations.value = Array.isArray(payload.locations) ? payload.locations : []
    planInfo.value = {
      ...planInfo.value,
      ...(payload.plan || {}),
    }
  } catch (error) {
    loadError.value = error?.response?.data?.message || error?.message || 'Unable to load dashboard data right now.'
    weeklyCounts.value = []
  } finally {
    isLoading.value = false
  }
}

const downloadReport = async () => {
  try {
    const response = await client.get('/reports/parcels', { responseType: 'blob' })
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'parcels.csv'
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  } catch {
    // ignore
  }
}

const goParcels = () => {
  router.push('/parcels')
}

const runGlobalSearch = () => {
  const query = searchQuery.value.trim()
  router.push({
    path: '/parcels',
    query: query ? { q: query } : {},
  })
}

const goScan = () => {
  router.push({ path: '/parcels', query: { scan: '1' } })
}

const goUsers = () => {
  router.push('/admin/users')
}

const goPlans = () => {
  router.push('/plans')
}

const goNotifications = () => {
  router.push('/notifications')
}

const goSettings = () => {
  router.push('/settings/theme')
}

const goPlanHistory = () => {
  router.push('/plans/history')
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

const goProfile = () => {
  userMenuOpen.value = false
  router.push('/account/profile')
}

const goAccount = () => {
  userMenuOpen.value = false
  router.push('/account/status')
}

const logout = async () => {
  userMenuOpen.value = false
  try {
    await client.post('/logout')
  } catch {
    // ignore
  }
  clearToken()
  router.push('/login')
}

const handleOutsideClick = (event) => {
  if (!userMenuRef.value) return
  if (!userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

const handleEsc = (event) => {
  if (event.key === 'Escape') {
    userMenuOpen.value = false
  }
}


watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(loadDashboard)

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>
