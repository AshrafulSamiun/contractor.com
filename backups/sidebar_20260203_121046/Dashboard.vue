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
          <button class="pm-icon-btn" type="button" aria-label="Notifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
            <span class="pm-topbar-badge"></span>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
          <div class="pm-topbar-avatar">JA</div>
          <span class="pm-topbar-name">{{ userName }}</span>
          <span class="pm-topbar-pill">Admin</span>
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

      <div class="container">
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
        <div class="pm-dash-card pm-kpi">
          <div class="pm-kpi-label">Total Parcels</div>
          <div class="pm-kpi-value">{{ kpis.total }}</div>
          <div class="pm-kpi-meta">All time</div>
        </div>
        <div class="pm-dash-card pm-kpi">
          <div class="pm-kpi-label">Pending Pickup</div>
          <div class="pm-kpi-value">{{ kpis.pending }}</div>
          <div class="pm-kpi-meta">Awaiting collection</div>
        </div>
        <div class="pm-dash-card pm-kpi">
          <div class="pm-kpi-label">Delivered Today</div>
          <div class="pm-kpi-value">{{ kpis.delivered_today }}</div>
          <div class="pm-kpi-meta">Today</div>
        </div>
        <div class="pm-dash-card pm-kpi">
          <div class="pm-kpi-label">Alerts</div>
          <div class="pm-kpi-value">{{ kpis.alerts }}</div>
          <div class="pm-kpi-meta">Needs review</div>
        </div>
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
            <div class="pm-chart-bars">
              <span v-for="bar in chartBars" :key="bar" :style="{ height: bar + '%' }"></span>
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
                <tr v-for="parcel in recentParcels" :key="parcel.id">
                  <td>{{ parcel.id }}</td>
                  <td>{{ parcel.resident }}</td>
                  <td><span :class="['pm-status-pill', parcel.statusClass]">{{ parcel.status }}</span></td>
                  <td>{{ parcel.location }}</td>
                  <td>{{ parcel.updated }}</td>
                </tr>
                <tr v-if="!recentParcels.length">
                  <td colspan="5" class="text-center pm-muted">No recent parcels yet.</td>
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
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import client from '../api/client'
import { authState } from '../store/auth'
import AppSidebar from '../components/AppSidebar.vue'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const kpis = ref({ total: 0, pending: 0, delivered_today: 0, alerts: 0 })
const notifications = ref({ email_sent: 0, sms_sent: 0, failed: 0, system_alerts: 0 })
const chartBars = ref([20, 30, 40, 25, 55, 45, 60])
const recentParcels = ref([])
const locations = ref([])
const planInfo = ref({ name: 'standard', next_billing: '--', active_users: 0 })
const isAdmin = computed(() => authState.user?.role === 'admin')
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

const mapStatus = (status) => {
  if (status === 'delivered') return { label: 'Delivered', cls: 'success' }
  if (status === 'held') return { label: 'Held', cls: 'warning' }
  if (status === 'returned') return { label: 'Returned', cls: 'warning' }
  return { label: 'Pending', cls: 'pending' }
}

const loadDashboard = async () => {
  try {
    if (authState.user?.name) userName.value = authState.user.name
    const { data } = await client.get('/dashboard')
    if (!data?.success) return
    kpis.value = data.data.kpis
    notifications.value = data.data.notifications
    chartBars.value = data.data.weekly.map((item) => Math.min(100, item.count * 5))
    recentParcels.value = data.data.recent.map((parcel) => {
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
    locations.value = data.data.locations
    planInfo.value = data.data.plan || planInfo.value
  } catch {
    // keep fallback data
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
  router.push('/account')
}

const logout = () => {
  userMenuOpen.value = false
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

