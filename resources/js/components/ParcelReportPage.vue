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
          <input v-model="filters.q" class="form-control" placeholder="Search by recipient, tracking, facility..." />
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
        <div class="container pm-ops-page">
          <div class="pm-page-head">
            <div>
              <h2>{{ title }}</h2>
              <div class="pm-page-subtitle">{{ subtitle }}</div>
            </div>
            <button class="btn btn-outline-primary" type="button" :disabled="!canExportReports" @click="exportCsv">Export CSV</button>
          </div>

          <div class="pm-card p-4 mb-4 pm-ops-card">
            <div class="pm-approval-export">
              <div class="pm-form-field">
                <label class="pm-field-label">Updated From</label>
                <input v-model="filters.from" class="form-control" type="date" />
              </div>
              <div class="pm-form-field">
                <label class="pm-field-label">Updated To</label>
                <input v-model="filters.to" class="form-control" type="date" />
              </div>
              <div class="pm-form-actions">
                <button class="btn btn-outline-secondary" type="button" @click="loadParcels">Apply Filters</button>
                <button class="btn btn-outline-secondary" type="button" @click="resetFilters">Reset</button>
              </div>
            </div>
          </div>

          <div class="pm-card pm-ops-card">
            <div class="pm-card-header">
              <div>
                <h3>{{ listTitle }}</h3>
                <p>{{ listSubtitle }}</p>
              </div>
            </div>
            <div class="pm-table-wrap">
              <table class="table pm-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Recipient</th>
                    <th>Status</th>
                    <th>Facility</th>
                    <th>Location</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="parcel in parcels" :key="parcel.id">
                    <td>PK-{{ parcel.id }}</td>
                    <td>{{ parcel.recipient_name }}</td>
                    <td><span :class="['pm-status-pill', statusClass(parcel.status)]">{{ parcel.status }}</span></td>
                    <td>{{ parcel.facility_name || '-' }}</td>
                    <td>{{ parcel.location || '-' }}</td>
                    <td>{{ formatDate(parcel.updated_at) }}</td>
                  </tr>
                  <tr v-if="!parcels.length">
                    <td colspan="6" class="text-center pm-muted">No data found.</td>
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
import { hasUserPermission } from '../config/permissions'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, required: true },
  listTitle: { type: String, required: true },
  listSubtitle: { type: String, required: true },
  status: { type: String, default: '' },
  statusIn: { type: Array, default: () => [] },
})

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const parcels = ref([])

const filters = ref({
  q: '',
  from: '',
  to: '',
})

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})
const canExportReports = computed(() => hasUserPermission(authState.user, 'reports', 'export'))

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

const loadParcels = async () => {
  const params = {
    q: filters.value.q || undefined,
    from: filters.value.from || undefined,
    to: filters.value.to || undefined,
  }
  if (props.statusIn.length) {
    params.status_in = props.statusIn.join(',')
  } else if (props.status) {
    params.status = props.status
  }

  const { data } = await client.get('/parcels', { params })
  parcels.value = data?.data?.data || []
}

const exportCsv = () => {
  if (!canExportReports.value) return

  const params = new URLSearchParams()
  if (props.statusIn.length) params.append('status_in', props.statusIn.join(','))
  if (props.status) params.append('status', props.status)
  if (filters.value.q) params.append('q', filters.value.q)
  if (filters.value.from) params.append('from', filters.value.from)
  if (filters.value.to) params.append('to', filters.value.to)
  window.location.href = `/api/v1/reports/parcels?${params.toString()}`
}

const resetFilters = () => {
  filters.value = { q: '', from: '', to: '' }
  loadParcels()
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

const statusClass = (status) => {
  if (status === 'delivered' || status === 'picked_up') return 'success'
  if (['held', 'returned', 'rejected', 'expired'].includes(status)) return 'warning'
  if (['lost', 'damaged'].includes(status)) return 'danger'
  return 'pending'
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadParcels()
})
</script>
