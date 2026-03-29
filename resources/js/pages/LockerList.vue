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
          <input v-model="searchQuery" class="form-control" placeholder="Search lockers, locations, assignments..." />
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
            <h2>Lockers</h2>
            <div class="pm-page-subtitle">Dashboard &gt; Profiles &gt; Lockers &gt; List</div>
          </div>
          <div class="pm-page-actions">
            <button class="btn btn-primary" type="button" @click="toggleForm">
              {{ openForm ? 'Close' : 'New Locker' }}
            </button>
            <button class="btn btn-outline-primary" type="button" @click="triggerImport">Import</button>
            <input ref="importInput" type="file" accept=".csv" class="d-none" @change="handleImport" />
          </div>
        </div>

        <section v-if="openForm" class="pm-card pm-ops-card p-4 mb-3">
          <div class="pm-panel-head">
            <div>
              <h4>{{ editingId ? 'Edit Locker' : 'Create Locker' }}</h4>
              <p class="pm-muted">Define locker code, location, and capacity.</p>
            </div>
          </div>
          <div class="pm-form-section">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="pm-field-label">Locker Code *</label>
                <input v-model="form.code" class="form-control" placeholder="LK-2024-001" />
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Facility</label>
                <input v-model="form.facility_name" class="form-control" placeholder="Main Tower" />
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Location</label>
                <input v-model="form.location" class="form-control" placeholder="Floor 2" />
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">Status</label>
                <select v-model="form.status" class="form-control">
                  <option value="active">Active</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">Capacity</label>
                <input v-model.number="form.capacity" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Notes</label>
                <input v-model="form.notes" class="form-control" placeholder="Optional notes" />
              </div>
            </div>
            <div class="pm-form-actions pm-form-actions-right">
              <button class="btn btn-outline-primary" type="button" @click="resetForm">Clear</button>
              <button class="btn btn-primary" type="button" @click="saveLocker">
                {{ editingId ? 'Update Locker' : 'Save Locker' }}
              </button>
            </div>
            <div v-if="error" class="text-danger mt-2">{{ error }}</div>
          </div>
        </section>

        <section class="pm-card pm-ops-card p-4">
          <div class="pm-form-section">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="pm-field-label">Locker Code</label>
                <input v-model="filters.code" class="form-control" placeholder="Search locker code" />
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Facility</label>
                <input v-model="filters.facility_name" class="form-control" placeholder="Filter by facility" />
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Status</label>
                <select v-model="filters.status" class="form-control">
                  <option value="">All</option>
                  <option value="active">Active</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div class="pm-form-actions pm-form-actions-right">
              <button class="btn btn-outline-primary" type="button" @click="loadLockers">Apply</button>
              <button class="btn btn-light" type="button" @click="resetFilters">Reset</button>
            </div>
          </div>
        </section>

        <section class="pm-card pm-ops-card p-4 mt-3">
          <div class="pm-table-title">Locker List</div>
          <div class="table-responsive">
            <table class="table pm-dash-table">
              <thead>
                <tr>
                  <th>Locker ID</th>
                  <th>Facility</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Capacity</th>
                  <th>Last Updated</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="locker in lockers" :key="locker.id">
                  <td>{{ locker.code }}</td>
                  <td>{{ locker.facility_name || '-' }}</td>
                  <td>{{ locker.location || '-' }}</td>
                  <td><span :class="['pm-status-pill', statusClass(locker.status)]">{{ formatStatus(locker.status) }}</span></td>
                  <td>{{ locker.capacity ?? 0 }} slots</td>
                  <td>{{ formatDate(locker.updated_at) }}</td>
                  <td class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" type="button" @click="editLocker(locker)">Edit</button>
                    <button class="btn btn-outline-danger btn-sm" type="button" @click="deleteLocker(locker)">Delete</button>
                  </td>
                </tr>
                <tr v-if="!lockers.length">
                  <td colspan="7" class="text-center pm-muted">No lockers found.</td>
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
const lockers = ref([])
const openForm = ref(false)
const editingId = ref(null)
const error = ref('')
const form = ref({
  code: '',
  facility_name: '',
  location: '',
  status: 'active',
  capacity: 0,
  notes: '',
})
const filters = ref({
  code: '',
  facility_name: '',
  status: '',
})
const importInput = ref(null)

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

const loadLockers = async () => {
  try {
    const params = {
      q: searchQuery.value || undefined,
      facility_name: filters.value.facility_name || undefined,
      status: filters.value.status || undefined,
    }
    if (filters.value.code) params.q = filters.value.code
    const { data } = await client.get('/lockers', { params })
    if (data?.success) {
      lockers.value = data.data || []
    }
  } catch {
    lockers.value = []
  }
}

const saveLocker = async () => {
  error.value = ''
  if (!form.value.code) {
    error.value = 'Locker code is required.'
    return
  }
  try {
    if (editingId.value) {
      await client.put(`/lockers/${editingId.value}`, form.value)
      setFlash('Locker updated.', 'success', 2000)
    } else {
      await client.post('/lockers', form.value)
      setFlash('Locker created.', 'success', 2000)
    }
    resetForm()
    openForm.value = false
    loadLockers()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to save locker.'
  }
}

const editLocker = (locker) => {
  editingId.value = locker.id
  form.value = {
    code: locker.code || '',
    facility_name: locker.facility_name || '',
    location: locker.location || '',
    status: locker.status || 'active',
    capacity: locker.capacity || 0,
    notes: locker.notes || '',
  }
  openForm.value = true
}

const deleteLocker = async (locker) => {
  try {
    await client.delete(`/lockers/${locker.id}`)
    setFlash('Locker deleted.', 'success', 2000)
    loadLockers()
  } catch {
    setFlash('Failed to delete locker.', 'danger', 2500)
  }
}

const resetForm = () => {
  editingId.value = null
  form.value = {
    code: '',
    facility_name: '',
    location: '',
    status: 'active',
    capacity: 0,
    notes: '',
  }
}

const resetFilters = () => {
  filters.value = { code: '', facility_name: '', status: '' }
  searchQuery.value = ''
  loadLockers()
}

const triggerImport = () => {
  importInput.value?.click()
}

const handleImport = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  const text = await file.text()
  const rows = text.split(/\r?\n/).filter(Boolean)
  const created = []
  for (let i = 1; i < rows.length; i += 1) {
    const [code, facility_name, location, status, capacity] = rows[i].split(',')
    if (!code) continue
    try {
      await client.post('/lockers', {
        code: code.trim(),
        facility_name: (facility_name || '').trim(),
        location: (location || '').trim(),
        status: (status || 'active').trim(),
        capacity: Number(capacity || 0),
      })
      created.push(code)
    } catch {
      // ignore import errors
    }
  }
  setFlash(`Imported ${created.length} lockers.`, 'success', 2500)
  event.target.value = ''
  loadLockers()
}

const statusClass = (value) => {
  if (value === 'active') return 'success'
  if (value === 'maintenance') return 'warning'
  return 'pending'
}

const formatStatus = (value) => {
  if (!value) return '-'
  return value.replace(/_/g, ' ')
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleDateString()
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
  loadLockers()
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>
