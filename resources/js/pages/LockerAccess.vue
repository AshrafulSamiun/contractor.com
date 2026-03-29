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
          <input v-model="searchQuery" class="form-control" placeholder="Search access, residents, locker codes..." />
          <button v-if="searchQuery" class="pm-clear-btn" type="button" aria-label="Clear search" @click="searchQuery = ''">
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

      <div class="container pm-ops-page">
        <div class="pm-page-head">
          <div>
            <h2>Locker Access</h2>
            <div class="pm-page-subtitle">Dashboard &gt; Profiles &gt; Lockers &gt; Access</div>
          </div>
          <div class="pm-page-actions">
            <button class="btn btn-primary" type="button" @click="toggleForm">
              {{ openForm ? 'Close' : 'Grant Access' }}
            </button>
            <button class="btn btn-outline-primary" type="button" @click="loadAccess">Refresh</button>
          </div>
        </div>

        <section v-if="openForm" class="pm-card pm-ops-card p-4">
          <h5 class="pm-form-title">Assign Access</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="pm-field-label">Resident / User *</label>
              <select v-model="form.recipient_id" class="form-control">
                <option value="">Select resident</option>
                <option v-for="recipient in recipients" :key="recipient.id" :value="recipient.id">
                  {{ recipient.recipient_name }}
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="pm-field-label">Locker Code *</label>
              <select v-model="form.locker_id" class="form-control">
                <option value="">Select locker</option>
                <option v-for="locker in lockers" :key="locker.id" :value="locker.id">
                  {{ locker.code }} - {{ locker.facility_name || 'Facility' }}
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="pm-field-label">Access Window *</label>
              <div class="d-flex gap-2">
                <input v-model="form.starts_at" type="datetime-local" class="form-control" />
                <input v-model="form.ends_at" type="datetime-local" class="form-control" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="pm-field-label">Access Type</label>
              <select v-model="form.access_type" class="form-control">
                <option value="temporary">Temporary</option>
                <option value="permanent">Permanent</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="pm-field-label">Status</label>
              <select v-model="form.status" class="form-control">
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="expired">Expired</option>
                <option value="revoked">Revoked</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="pm-field-label">Notes</label>
              <input v-model="form.notes" class="form-control" placeholder="Optional notes" />
            </div>
          </div>
          <div class="pm-form-actions pm-form-actions-right">
            <button class="btn btn-outline-primary" type="button" @click="resetForm">Clear</button>
            <button class="btn btn-primary" type="button" @click="saveAccess">
              {{ editingId ? 'Update Access' : 'Save Access' }}
            </button>
          </div>
          <div v-if="error" class="text-danger mt-2">{{ error }}</div>
        </section>

        <section class="pm-card pm-ops-card p-4 mt-3">
          <div class="pm-table-title">Recent Access Activity</div>
          <div class="table-responsive">
            <table class="table pm-dash-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Locker</th>
                  <th>Access Type</th>
                  <th>Status</th>
                  <th>Last Used</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="access in accessList" :key="access.id">
                  <td>{{ access.recipient?.recipient_name || '-' }}</td>
                  <td>{{ access.locker?.code || '-' }}</td>
                  <td class="text-capitalize">{{ access.access_type }}</td>
                  <td><span :class="['pm-status-pill', statusClass(access.status)]">{{ access.status }}</span></td>
                  <td>{{ formatDate(access.last_used_at || access.updated_at) }}</td>
                  <td class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" type="button" @click="markUsed(access)">Mark Used</button>
                    <button class="btn btn-outline-primary btn-sm" type="button" @click="editAccess(access)">Edit</button>
                    <button class="btn btn-outline-danger btn-sm" type="button" @click="revokeAccess(access)">Revoke</button>
                  </td>
                </tr>
                <tr v-if="!accessList.length">
                  <td colspan="6" class="text-center pm-muted">No access records found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { authState } from '../store/auth'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { setFlash } from '../store/flash'
import { logout as apiLogout, clearToken } from '../api/auth'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const openForm = ref(false)
const editingId = ref(null)
const error = ref('')
const accessList = ref([])
const lockers = ref([])
const recipients = ref([])
const form = ref({
  recipient_id: '',
  locker_id: '',
  access_type: 'temporary',
  status: 'active',
  starts_at: '',
  ends_at: '',
  notes: '',
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
    await apiLogout()
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

const toggleForm = () => {
  openForm.value = !openForm.value
  if (!openForm.value) resetForm()
}

const loadAccess = async () => {
  try {
    const params = {
      q: searchQuery.value || undefined,
    }
    const { data } = await client.get('/locker-accesses', { params })
    if (data?.success) {
      accessList.value = data.data || []
    }
  } catch {
    accessList.value = []
  }
}

const loadSupportData = async () => {
  try {
    const [lockerRes, recipientRes] = await Promise.all([
      client.get('/lockers'),
      client.get('/recipients'),
    ])
    lockers.value = lockerRes.data?.data || []
    recipients.value = recipientRes.data?.data || []
  } catch {
    lockers.value = []
    recipients.value = []
  }
}

const saveAccess = async () => {
  error.value = ''
  if (!form.value.recipient_id || !form.value.locker_id) {
    error.value = 'Resident and locker are required.'
    return
  }
  try {
    if (editingId.value) {
      await client.put(`/locker-accesses/${editingId.value}`, form.value)
      setFlash('Access updated.', 'success', 2000)
    } else {
      await client.post('/locker-accesses', form.value)
      setFlash('Access granted.', 'success', 2000)
    }
    resetForm()
    openForm.value = false
    loadAccess()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to save access.'
  }
}

const editAccess = (access) => {
  editingId.value = access.id
  form.value = {
    recipient_id: access.recipient_id,
    locker_id: access.locker_id,
    access_type: access.access_type || 'temporary',
    status: access.status || 'active',
    starts_at: access.starts_at ? access.starts_at.slice(0, 16) : '',
    ends_at: access.ends_at ? access.ends_at.slice(0, 16) : '',
    notes: access.notes || '',
  }
  openForm.value = true
}

const revokeAccess = async (access) => {
  try {
    await client.put(`/locker-accesses/${access.id}`, { status: 'revoked' })
    setFlash('Access revoked.', 'success', 2000)
    loadAccess()
  } catch {
    setFlash('Failed to revoke.', 'danger', 2000)
  }
}

const markUsed = async (access) => {
  try {
    await client.put(`/locker-accesses/${access.id}`, { last_used_at: new Date().toISOString() })
    loadAccess()
  } catch {
    // ignore
  }
}

const resetForm = () => {
  editingId.value = null
  form.value = {
    recipient_id: '',
    locker_id: '',
    access_type: 'temporary',
    status: 'active',
    starts_at: '',
    ends_at: '',
    notes: '',
  }
}

const statusClass = (value) => {
  if (value === 'active') return 'success'
  if (value === 'pending') return 'warning'
  if (value === 'expired') return 'pending'
  return 'danger'
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
  loadSupportData()
  loadAccess()
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>
