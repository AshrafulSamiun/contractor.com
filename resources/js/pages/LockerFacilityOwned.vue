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

      <div class="container pm-ops-page pm-locker-page">
        <section class="pm-locker-head">
          <div>
            <h2>{{ viewMode === 'list' ? 'Locker List - Facility Owned' : 'Locker Profile' }}</h2>
            <div class="pm-page-subtitle">
              Dashboard &gt; Profiles &gt; Lockers &gt; Facility Owned &gt; {{ viewMode === 'list' ? 'List' : 'New' }}
            </div>
          </div>
          <div class="pm-page-actions">
            <button class="btn btn-primary" type="button" @click="openNewLocker">New Locker</button>
            <button class="btn btn-outline-primary" type="button" @click="openList">Locker List</button>
          </div>
        </section>

        <template v-if="viewMode === 'list'">
          <section class="pm-card pm-ops-card p-4">
            <div class="pm-list-filters">
              <div class="pm-filter-group">
                <label class="pm-field-label">Locker No</label>
                <input v-model="filters.code" class="form-control" placeholder="Search..." />
              </div>
              <div class="pm-filter-group">
                <label class="pm-field-label">Locker Name</label>
                <input v-model="filters.locker_name" class="form-control" placeholder="Search..." />
              </div>
              <div class="pm-filter-group">
                <label class="pm-field-label">Type</label>
                <input class="form-control" value="Facility Owned" disabled />
              </div>
              <div class="pm-filter-group">
                <label class="pm-field-label">Location</label>
                <input v-model="filters.location" class="form-control" placeholder="Search..." />
              </div>
              <div class="pm-filter-group">
                <label class="pm-field-label">Status</label>
                <select v-model="filters.status" class="form-control">
                  <option value="">All</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="maintenance">Maintenance</option>
                </select>
              </div>
              <button class="btn btn-outline-secondary pm-filter-btn" type="button" @click="resetFilters">Reset</button>
              <button class="btn btn-primary pm-filter-btn" type="button" :disabled="listLoading" @click="loadLockers">
                {{ listLoading ? 'Loading...' : 'Search' }}
              </button>
            </div>

            <div class="table-responsive">
              <table class="table pm-dash-table">
                <thead>
                  <tr>
                    <th>Locker No</th>
                    <th>Locker Name</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th class="text-center">Small</th>
                    <th class="text-center">Medium</th>
                    <th class="text-center">Large</th>
                    <th class="text-center">XL</th>
                    <th class="text-center">Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="locker in lockers" :key="locker.id">
                    <td class="pm-locker-code">
                      <button class="btn btn-link p-0 pm-locker-link" type="button" @click="viewLocker(locker.id)">
                        {{ locker.code }}
                      </button>
                    </td>
                    <td>{{ locker.locker_name }}</td>
                    <td><span class="pm-type-pill">{{ locker.locker_type_label || 'Facility Owned' }}</span></td>
                    <td>{{ locker.location || '-' }}</td>
                    <td class="text-center"><span class="pm-count-pill small">{{ locker.small_total || 0 }}</span></td>
                    <td class="text-center"><span class="pm-count-pill medium">{{ locker.medium_total || 0 }}</span></td>
                    <td class="text-center"><span class="pm-count-pill large">{{ locker.large_total || 0 }}</span></td>
                    <td class="text-center"><span class="pm-count-pill xl">{{ locker.xl_total || 0 }}</span></td>
                    <td class="text-center"><span class="pm-count-pill total">{{ locker.total_compartments || 0 }}</span></td>
                    <td><span :class="['pm-status-pill', statusClass(locker.status)]">{{ formatStatus(locker.status) }}</span></td>
                    <td class="text-end">
                      <div class="dropdown d-inline-block pm-list-action-dropdown">
                        <button
                          class="btn btn-sm pm-list-action-btn dropdown-toggle"
                          type="button"
                          data-bs-toggle="dropdown"
                          aria-expanded="false"
                        >
                          Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end pm-list-action-menu">
                          <li><button class="dropdown-item pm-list-action-item" type="button" @click="editLocker(locker.id)">Edit</button></li>
                          <li><button class="dropdown-item pm-list-action-item text-danger" type="button" @click="deleteLocker(locker)">Delete</button></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!lockers.length">
                    <td colspan="11" class="text-center pm-muted">No lockers found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </template>

        <template v-else>
          <section class="pm-card pm-ops-card p-4">
            <div class="pm-panel-head"><h5 class="pm-form-title">Locker Information</h5></div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Locker No *</label>
                <input v-model="form.code" class="form-control" :disabled="readOnly" placeholder="Enter locker number" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Locker Name *</label>
                <input v-model="form.locker_name" class="form-control" :disabled="readOnly" placeholder="Enter locker name" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Type *</label>
                <input class="form-control" value="Facility Owned" disabled />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Location *</label>
                <input v-model="form.location" class="form-control" :disabled="readOnly" placeholder="Enter location" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Status</label>
                <select v-model="form.status" class="form-control" :disabled="readOnly">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="maintenance">Maintenance</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Notes</label>
                <input v-model="form.notes" class="form-control" :disabled="readOnly" placeholder="Optional notes" />
              </div>
            </div>
            <div v-if="error" class="text-danger mt-2">{{ error }}</div>
          </section>

          <section class="pm-locker-note-card">
            <h5 class="pm-form-title mb-1">Compartment Configuration</h5>
            <div class="pm-page-subtitle">Manage compartments by size category</div>
          </section>

          <section v-for="size in sizeConfigs" :key="size.key" class="pm-card pm-ops-card p-4">
            <div class="pm-compartment-head">
              <h6 :class="['pm-compartment-title', size.className]">{{ size.label }}</h6>
              <button class="btn btn-sm btn-primary" type="button" :disabled="readOnly" @click="addCompartment(size.key)">+ Add Compartment</button>
            </div>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Compartment Code</th>
                    <th>Quantity</th>
                    <th>Activation Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in compartments[size.key]" :key="row.local_id">
                    <td><input v-model="row.compartment_code" class="form-control form-control-sm" :disabled="readOnly" /></td>
                    <td><input v-model.number="row.quantity" type="number" min="1" class="form-control form-control-sm" :disabled="readOnly" /></td>
                    <td>
                      <select v-model="row.activation_status" class="form-control form-control-sm" :disabled="readOnly">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                      </select>
                    </td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-outline-danger" type="button" :disabled="readOnly" @click="removeCompartment(size.key, index)">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="pm-page-subtitle">Total {{ size.short }} Compartments: <strong>{{ totals[size.key] }}</strong></div>
          </section>

          <section class="pm-locker-summary">
            <div class="pm-locker-summary-grid">
              <div><small>Total Small</small><strong class="small">{{ totals.small }}</strong></div>
              <div><small>Total Medium</small><strong class="medium">{{ totals.medium }}</strong></div>
              <div><small>Total Large</small><strong class="large">{{ totals.large }}</strong></div>
              <div><small>Total XL</small><strong class="xl">{{ totals.xl }}</strong></div>
              <div><small>Total Compartments</small><strong class="total">{{ totalCompartments }}</strong></div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-3">
            <div class="pm-form-actions pm-form-actions-right">
              <button
                v-if="editingId && readOnly"
                class="btn btn-outline-secondary"
                type="button"
                @click="readOnly = false"
              >
                Edit
              </button>
              <button
                v-if="editingId"
                class="btn btn-outline-danger"
                type="button"
                :disabled="saving"
                @click="deleteCurrent"
              >
                Delete
              </button>
              <button
                v-if="!editingId || !readOnly"
                class="btn btn-primary"
                type="button"
                :disabled="saving"
                @click="saveLocker"
              >
                {{ saving ? (editingId ? 'Updating...' : 'Saving...') : (editingId ? 'Update' : 'Save') }}
              </button>
            </div>
          </section>
        </template>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
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
const viewMode = ref('list')
const listLoading = ref(false)
const editingId = ref(null)
const error = ref('')
const saving = ref(false)
const readOnly = ref(false)
const form = ref({
  code: '',
  locker_name: '',
  location: '',
  status: 'active',
  notes: '',
})
const filters = ref({
  code: '',
  locker_name: '',
  location: '',
  status: '',
})
const sizeConfigs = [
  { key: 'small', short: 'Small', label: 'Small Compartments', className: 'small', prefix: 'S' },
  { key: 'medium', short: 'Medium', label: 'Medium Compartments', className: 'medium', prefix: 'M' },
  { key: 'large', short: 'Large', label: 'Large Compartments', className: 'large', prefix: 'L' },
  { key: 'xl', short: 'XL', label: 'Extra Large Compartments', className: 'xl', prefix: 'XL' },
]
const compartments = ref({
  small: [],
  medium: [],
  large: [],
  xl: [],
})
let localRowId = 1

const totals = computed(() => ({
  small: sumSize('small'),
  medium: sumSize('medium'),
  large: sumSize('large'),
  xl: sumSize('xl'),
}))

const totalCompartments = computed(() => (
  totals.value.small + totals.value.medium + totals.value.large + totals.value.xl
))

const sumSize = (key) => compartments.value[key].reduce((sum, row) => sum + Number(row.quantity || 0), 0)

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

const newRow = (sizeKey, values = {}) => ({
  local_id: localRowId += 1,
  size_category: sizeKey,
  compartment_code: values.compartment_code || `${sizeKey.toUpperCase()}-01`,
  quantity: Number(values.quantity || 1),
  activation_status: values.activation_status || 'active',
  sort_order: Number(values.sort_order || 0),
})

const seedDefaultCompartments = () => {
  const next = {
    small: [],
    medium: [],
    large: [],
    xl: [],
  }

  sizeConfigs.forEach((size) => {
    for (let i = 1; i <= 1; i += 1) {
      next[size.key].push(newRow(size.key, {
        compartment_code: `${size.prefix}-${String(i).padStart(2, '0')}`,
        quantity: 1,
        activation_status: 'active',
        sort_order: i - 1,
      }))
    }
  })

  compartments.value = next
}

const openNewLocker = () => {
  viewMode.value = 'form'
  readOnly.value = false
  resetForm()
}

const openList = () => {
  viewMode.value = 'list'
  readOnly.value = false
  loadLockers()
}

const loadLockers = async () => {
  listLoading.value = true
  try {
    const params = {
      q: searchQuery.value || undefined,
      locker_type: 'facility_owned',
      code: filters.value.code || undefined,
      locker_name: filters.value.locker_name || undefined,
      location: filters.value.location || undefined,
      status: filters.value.status || undefined,
    }
    const { data } = await client.get('/lockers', { params })
    if (data?.success) {
      lockers.value = data.data || []
    }
  } catch {
    lockers.value = []
  } finally {
    listLoading.value = false
  }
}

const saveLocker = async () => {
  error.value = ''
  if (!form.value.code) {
    error.value = 'Locker number is required.'
    return
  }
  if (!form.value.locker_name) {
    error.value = 'Locker name is required.'
    return
  }
  if (!form.value.location) {
    error.value = 'Location is required.'
    return
  }

  const payload = {
    code: form.value.code.trim(),
    locker_name: form.value.locker_name.trim(),
    locker_type: 'facility_owned',
    location: form.value.location.trim(),
    status: form.value.status || 'active',
    notes: form.value.notes || null,
    compartments: flattenCompartments(),
  }

  if (!payload.compartments.length) {
    error.value = 'At least one compartment is required.'
    return
  }

  saving.value = true
  try {
    if (editingId.value) {
      await client.put(`/lockers/${editingId.value}`, payload)
      setFlash('Locker updated.', 'success', 2000)
    } else {
      const { data } = await client.post('/lockers', payload)
      editingId.value = data?.data?.id || null
      setFlash('Locker created.', 'success', 2000)
    }
    readOnly.value = false
    loadLockers()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to save locker.'
  } finally {
    saving.value = false
  }
}

const editLocker = async (lockerId) => {
  await loadLocker(lockerId, false)
}

const loadLocker = async (lockerId, asReadOnly) => {
  try {
    const { data } = await client.get(`/lockers/${lockerId}`)
    const locker = data?.data
    if (!locker) return

    editingId.value = locker.id
    readOnly.value = asReadOnly
    viewMode.value = 'form'
    form.value = {
      code: locker.code || '',
      locker_name: locker.locker_name || '',
      location: locker.location || '',
      status: locker.status || 'active',
      notes: locker.notes || '',
    }
    applyCompartments(locker.compartments || [])
  } catch {
    setFlash('Failed to load locker details.', 'danger', 2500)
  }
}

const applyCompartments = (rows) => {
  const grouped = {
    small: [],
    medium: [],
    large: [],
    xl: [],
  }
  ;(rows || []).forEach((row) => {
    const key = String(row.size_category || '').toLowerCase()
    if (!grouped[key]) return
    grouped[key].push(newRow(key, row))
  })

  sizeConfigs.forEach((size) => {
    if (!grouped[size.key].length) {
      grouped[size.key].push(newRow(size.key, {
        compartment_code: `${size.prefix}-01`,
        quantity: 1,
      }))
    }
  })

  compartments.value = grouped
}

const flattenCompartments = () => {
  const rows = []
  sizeConfigs.forEach((size) => {
    compartments.value[size.key].forEach((row, index) => {
      const code = String(row.compartment_code || '').trim()
      const quantity = Number(row.quantity || 0)
      if (!code || quantity < 1) return
      rows.push({
        size_category: size.key,
        compartment_code: code,
        quantity,
        activation_status: row.activation_status === 'inactive' ? 'inactive' : 'active',
        sort_order: index,
      })
    })
  })
  return rows
}

const addCompartment = (sizeKey) => {
  const size = sizeConfigs.find((item) => item.key === sizeKey)
  compartments.value[sizeKey].push(newRow(sizeKey, {
    compartment_code: `${size.prefix}-${String(compartments.value[sizeKey].length + 1).padStart(2, '0')}`,
    quantity: 1,
    activation_status: 'active',
    sort_order: compartments.value[sizeKey].length,
  }))
}

const removeCompartment = (sizeKey, index) => {
  compartments.value[sizeKey].splice(index, 1)
  if (!compartments.value[sizeKey].length) {
    const size = sizeConfigs.find((item) => item.key === sizeKey)
    compartments.value[sizeKey].push(newRow(sizeKey, {
      compartment_code: `${size.prefix}-01`,
      quantity: 1,
      activation_status: 'active',
      sort_order: 0,
    }))
  }
}

const deleteCurrent = async () => {
  if (!editingId.value) return
  if (!confirm('Delete this locker?')) return
  await deleteLocker({ id: editingId.value })
  resetForm()
  viewMode.value = 'list'
}

const viewLocker = async (lockerId) => {
  await loadLocker(lockerId, true)
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
    locker_name: '',
    location: '',
    status: 'active',
    notes: '',
  }
  error.value = ''
  seedDefaultCompartments()
}

const resetFilters = () => {
  filters.value = { code: '', locker_name: '', location: '', status: '' }
  searchQuery.value = ''
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

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
  seedDefaultCompartments()
  loadLockers()
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-locker-page {
  display: grid;
  gap: 14px;
}

.pm-locker-head {
  border: 1px solid #d7e4ff;
  border-radius: 16px;
  padding: 14px 16px;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 12px;
  background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
}

.pm-locker-head h2 {
  margin: 0;
}

.pm-list-filters {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr)) auto auto;
  gap: 10px;
  align-items: end;
  margin-bottom: 14px;
}

.pm-filter-btn {
  height: 44px;
}

.pm-locker-code {
  font-weight: 700;
  color: #1d4ed8;
}

.pm-locker-link {
  font-weight: 700;
  color: #1d4ed8;
  text-decoration: none;
}

.pm-locker-link:hover {
  color: #1e40af;
  text-decoration: underline;
}

.pm-type-pill {
  border-radius: 999px;
  background: #e8efff;
  color: #1d4ed8;
  padding: 4px 10px;
  font-size: 0.74rem;
  font-weight: 700;
}

.pm-count-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 34px;
  height: 34px;
  border-radius: 999px;
  font-weight: 700;
}

.pm-count-pill.small { background: #dbeafe; color: #2563eb; }
.pm-count-pill.medium { background: #fef3c7; color: #a16207; }
.pm-count-pill.large { background: #fee2e2; color: #dc2626; }
.pm-count-pill.xl { background: #f3e8ff; color: #7c3aed; }
.pm-count-pill.total { background: #dcfce7; color: #15803d; min-width: 42px; height: 42px; }

.pm-locker-note-card {
  border: 1px solid #bfd6ff;
  border-radius: 14px;
  padding: 14px 16px;
  background: linear-gradient(180deg, #e9f3ff 0%, #dceaff 100%);
}

.pm-compartment-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.pm-compartment-title {
  margin: 0;
  font-weight: 700;
}

.pm-compartment-title.small { color: #2563eb; }
.pm-compartment-title.medium { color: #ca8a04; }
.pm-compartment-title.large { color: #dc2626; }
.pm-compartment-title.xl { color: #7c3aed; }

.pm-locker-summary {
  border: 1px solid #b8e8cc;
  border-radius: 14px;
  padding: 12px;
  background: linear-gradient(180deg, #ecfff3 0%, #dff9e8 100%);
}

.pm-locker-summary-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 10px;
}

.pm-locker-summary-grid > div {
  text-align: center;
  border: 1px solid #d2ecdd;
  border-radius: 10px;
  padding: 8px;
  background: rgba(255, 255, 255, 0.8);
}

.pm-locker-summary-grid small {
  color: #64748b;
  display: block;
}

.pm-locker-summary-grid strong {
  font-size: 1.3rem;
  line-height: 1;
}

.pm-locker-summary-grid .small { color: #2563eb; }
.pm-locker-summary-grid .medium { color: #ca8a04; }
.pm-locker-summary-grid .large { color: #dc2626; }
.pm-locker-summary-grid .xl { color: #7c3aed; }
.pm-locker-summary-grid .total { color: #15803d; }

@media (max-width: 992px) {
  .pm-locker-head {
    grid-template-columns: 1fr;
  }

  .pm-list-filters {
    grid-template-columns: 1fr;
  }

  .pm-locker-summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
