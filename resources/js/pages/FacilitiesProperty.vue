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
          <input v-model="searchQuery" class="form-control" placeholder="Search parcels, residents, tracking..." />
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

      <div class="container pm-ops-page pm-facility-premium-page">
        <section class="pm-facility-hero">
          <div>
            <div class="pm-facility-kicker">Profiles Module</div>
            <h2 class="pm-facility-title">Facilities / Property</h2>
            <div class="pm-page-subtitle pm-facility-subtitle">
              Dashboard &gt; Profiles &gt; Facilities / Property &gt; {{ viewMode === 'form' ? 'Editor' : 'Directory' }}
            </div>
          </div>
          <div class="pm-facility-hero-actions">
            <span class="pm-facility-chip">{{ viewMode === 'form' ? (activeId ? 'Editing' : 'Create') : 'List View' }}</span>
            <div class="pm-page-actions">
              <button class="btn btn-primary" type="button" @click="switchToForm">New</button>
              <button class="btn btn-outline-primary" type="button" @click="switchToList">Facility List</button>
            </div>
          </div>
        </section>

        <div v-if="viewMode === 'form'" class="pm-facility-form-shell">
          <section class="pm-card pm-ops-card p-4 pm-facility-section">
            <div class="pm-facility-section-head">
              <h5 class="pm-form-title">Facility Information</h5>
              <span class="pm-facility-section-tag">Core</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Facility Name <span class="pm-required-star">*</span></label>
                <input class="form-control" v-model="form.facility_name" placeholder="Enter facility name" required />
                <div v-if="errors.facility_name" class="pm-form-error">{{ errors.facility_name }}</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Facility Type <span class="pm-required-star">*</span></label>
                <select class="form-control" v-model="form.facility_type" required>
                  <option value="">Select facility type</option>
                  <option
                    v-if="form.facility_type && !facilityTypeById[String(form.facility_type)]"
                    :value="form.facility_type"
                  >
                    {{ form.facility_type }}
                  </option>
                  <option v-for="type in facilityTypeOptions" :key="type.id" :value="String(type.id)">
                    {{ type.label }}
                  </option>
                </select>
                <div v-if="errors.facility_type" class="pm-form-error">{{ errors.facility_type }}</div>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4 pm-facility-section">
            <div class="pm-facility-section-head">
              <h5 class="pm-form-title">Address</h5>
              <span class="pm-facility-section-tag">Location</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Unit / Building No.</label>
                <input class="form-control" v-model="form.unit_no" placeholder="Building/Unit number" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Street <span class="pm-required-star">*</span></label>
                <input class="form-control" v-model="form.street" placeholder="Street name" required />
                <div v-if="errors.street" class="pm-form-error">{{ errors.street }}</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">City <span class="pm-required-star">*</span></label>
                <input class="form-control" v-model="form.city" placeholder="City" required />
                <div v-if="errors.city" class="pm-form-error">{{ errors.city }}</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">State / Province</label>
                <input class="form-control" v-model="form.state" placeholder="State or Province" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Country <span class="pm-required-star">*</span></label>
                <select class="form-control" v-model.number="form.country_id" required>
                  <option value="">Select country</option>
                  <option v-for="c in countries" :key="c.id" :value="c.id">
                    {{ c.country_name }}
                  </option>
                </select>
                <div v-if="errors.country_id" class="pm-form-error">{{ errors.country_id }}</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Zip / Postal Code</label>
                <input class="form-control" v-model="form.postal_code" placeholder="Zip or Postal Code" />
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4 pm-facility-section">
            <div class="pm-facility-section-head">
              <h5 class="pm-form-title">Contact Information</h5>
              <span class="pm-facility-section-tag">Communication</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Ph. Office <span class="pm-required-star">*</span></label>
                <input class="form-control" v-model="form.office_phone" placeholder="+1 (555) 123-4567" />
                <div v-if="errors.office_phone" class="pm-form-error">{{ errors.office_phone }}</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Ph. Mobile</label>
                <input class="form-control" v-model="form.mobile_phone" placeholder="+1 (555) 987-6543" />
                <div v-if="errors.mobile_phone" class="pm-form-error">{{ errors.mobile_phone }}</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Email <span class="pm-required-star">*</span></label>
                <input class="form-control" v-model="form.email" placeholder="contact@facility.com" />
                <div v-if="errors.email" class="pm-form-error">{{ errors.email }}</div>
              </div>
              <div class="col-md-12">
                <div class="pm-help-text">At least one contact is required: Office Phone or Email.</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Fax</label>
                <input class="form-control" v-model="form.fax" placeholder="+1 (555) 123-4568" />
              </div>
              <div class="col-md-12">
                <label class="pm-field-label">Website</label>
                <input class="form-control" v-model="form.website" placeholder="https://www.facility.com" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Status</label>
                <select class="form-control" v-model="form.status">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-3 pm-facility-actions-sticky">
            <div class="pm-form-actions pm-form-actions-right">
              <button class="btn btn-outline-primary" type="button" :disabled="!!activeId" @click="resetForm">
                New
              </button>
              <button class="btn btn-outline-danger" type="button" :disabled="!activeId" @click="deleteCurrent">
                Delete
              </button>
              <button class="btn btn-primary" type="button" :disabled="saving" @click="saveFacility">
                {{ saving ? 'Saving...' : (activeId ? 'Update' : 'Save') }}
              </button>
            </div>
          </section>
        </div>

        <div v-else class="pm-dash-card pm-facility-list-shell">
          <div class="pm-facility-list-head">
            <div>
              <h5 class="pm-form-title">Facility Directory</h5>
              <div class="pm-page-subtitle">Search and manage all facility records from one table.</div>
            </div>
          </div>
          <div class="pm-list-filters pm-facility-filters">
            <div class="pm-filter-group">
              <label class="pm-field-label">Facility Name</label>
              <input class="form-control" v-model="filters.facility_name" placeholder="Search by name..." />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Facility Type</label>
              <select class="form-control" v-model="filters.facility_type">
                <option value="">All types</option>
                <option
                  v-if="filters.facility_type && !facilityTypeById[String(filters.facility_type)]"
                  :value="filters.facility_type"
                >
                  {{ filters.facility_type }}
                </option>
                <option v-for="type in facilityTypeOptions" :key="`filter-${type.id}`" :value="String(type.id)">
                  {{ type.label }}
                </option>
              </select>
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">City</label>
              <input class="form-control" v-model="filters.city" placeholder="Search by city..." />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Status</label>
              <select class="form-control" v-model="filters.status">
                <option value="">All</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <button class="btn btn-outline-secondary pm-filter-btn" type="button" @click="resetFilters">
              Reset
            </button>
            <button class="btn btn-primary pm-filter-btn" type="button" :disabled="listLoading" @click="fetchList">
              Search
            </button>
          </div>

          <div class="pm-table-wrap pm-facility-table-wrap">
            <table class="table pm-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Facility Name</th>
                  <th>Facility Type</th>
                  <th>City</th>
                  <th>Country</th>
                  <th>Office Phone</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody v-if="listLoading">
                <tr v-for="index in 4" :key="`loading-${index}`" class="pm-skeleton-row">
                  <td colspan="9">
                    <div class="pm-skeleton-line"></div>
                  </td>
                </tr>
              </tbody>
              <tbody v-else-if="!facilities.length">
                <tr>
                  <td colspan="9" class="text-center pm-empty-table-cell">No facilities found.</td>
                </tr>
              </tbody>
              <tbody v-else>
                <tr v-for="facility in facilities" :key="facility.id">
                  <td>{{ facility.id }}</td>
                  <td>{{ facility.facility_name }}</td>
                  <td>{{ formatFacilityType(facility.facility_type) }}</td>
                  <td>{{ facility.city || '-' }}</td>
                  <td>{{ displayCountry(facility) }}</td>
                  <td>{{ facility.office_phone || '-' }}</td>
                  <td>{{ facility.email || '-' }}</td>
                  <td>
                    <span class="pm-status-pill" :class="facility.status === 'Inactive' ? 'warning' : 'active'">
                      {{ facility.status || 'Active' }}
                    </span>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary me-2" type="button" @click="editFacility(facility)">
                      Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger" type="button" @click="deleteFacility(facility.id)">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import {
  FACILITY_TYPE_BY_ID,
  FACILITY_TYPE_OPTIONS,
} from '../config/facilityTypes'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const viewMode = ref('form')
const facilities = ref([])
const countries = ref([])
const listLoading = ref(false)
const saving = ref(false)
const activeId = ref(null)
const errors = reactive({})

const facilityTypeOptions = FACILITY_TYPE_OPTIONS
const facilityTypeById = FACILITY_TYPE_BY_ID
const facilityTypeLabelLookup = FACILITY_TYPE_OPTIONS.reduce((acc, item) => {
  acc[item.label.toLowerCase()] = String(item.id)
  return acc
}, {})

const form = reactive({
  facility_name: '',
  facility_type: '',
  unit_no: '',
  street: '',
  city: '',
  state: '',
  postal_code: '',
  country_id: '',
  country: '',
  office_phone: '',
  mobile_phone: '',
  email: '',
  fax: '',
  website: '',
  status: 'Active',
})

const filters = reactive({
  facility_name: '',
  facility_type: '',
  city: '',
  status: '',
})

const normalizeFacilityTypeToId = (value) => {
  const raw = String(value || '').trim()
  if (!raw) return ''

  if (facilityTypeById[raw]) {
    return raw
  }

  const byLabel = facilityTypeLabelLookup[raw.toLowerCase()]
  if (byLabel) {
    return byLabel
  }

  return raw
}

const formatFacilityType = (value) => {
  const id = normalizeFacilityTypeToId(value)
  const type = facilityTypeById[String(id)]
  if (type) {
    return type.label
  }

  return String(value || '-')
}

const resolveCountryIdByName = (name) => {
  const normalized = (name || '').trim().toLowerCase()
  if (!normalized) return ''
  const found = countries.value.find((country) => (country.country_name || '').toLowerCase() === normalized)
  return found?.id || ''
}

const resolveCountryNameById = (id) => {
  if (!id) return ''
  const found = countries.value.find((country) => Number(country.id) === Number(id))
  return found?.country_name || ''
}

const displayCountry = (facility) => {
  if (facility?.country) return facility.country
  const byId = resolveCountryNameById(facility?.country_id)
  return byId || '-'
}

const normalizePhoneNumber = (value) => {
  const raw = String(value || '').trim()
  if (!raw) return ''

  let normalized = raw.replace(/[^\d+]/g, '')
  if (!normalized) return ''

  if (normalized.startsWith('00')) {
    normalized = `+${normalized.slice(2)}`
  }

  if (normalized.startsWith('+')) {
    normalized = `+${normalized.slice(1).replace(/\D/g, '')}`
  } else {
    normalized = `+${normalized.replace(/\D/g, '')}`
  }

  return normalized === '+' ? '' : normalized
}

const isE164PhoneNumber = (value) => /^\+[1-9]\d{7,14}$/.test(String(value || '').trim())

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

const mapErrors = (errs) => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!errs) return
  Object.entries(errs).forEach(([key, value]) => {
    errors[key] = Array.isArray(value) ? value[0] : value
  })
}

const resetForm = () => {
  form.facility_name = ''
  form.facility_type = ''
  form.unit_no = ''
  form.street = ''
  form.city = ''
  form.state = ''
  form.postal_code = ''
  form.country_id = ''
  form.country = ''
  form.office_phone = ''
  form.mobile_phone = ''
  form.email = ''
  form.fax = ''
  form.website = ''
  form.status = 'Active'
  activeId.value = null
  mapErrors(null)
}

const clearEdit = () => {
  activeId.value = null
}

const switchToList = () => {
  viewMode.value = 'list'
  fetchList()
}

const switchToForm = () => {
  viewMode.value = 'form'
}

const fetchList = async () => {
  listLoading.value = true
  try {
    const params = {
      ...filters,
      facility_type: normalizeFacilityTypeToId(filters.facility_type) || '',
    }
    const { data } = await client.get('/facilities', { params })
    facilities.value = data?.data || []
  } catch {
    facilities.value = []
  } finally {
    listLoading.value = false
  }
}

const loadCountries = async () => {
  try {
    const { data } = await client.get('/countries')
    if (data?.success && Array.isArray(data.data)) {
      countries.value = data.data
      return
    }
  } catch {
    // ignore
  }
  countries.value = []
}

const resetFilters = () => {
  filters.facility_name = ''
  filters.facility_type = ''
  filters.city = ''
  filters.status = ''
  fetchList()
}

const saveFacility = async () => {
  saving.value = true
  mapErrors(null)
  try {
    const payload = { ...form }
    payload.facility_type = normalizeFacilityTypeToId(payload.facility_type)
    payload.facility_name = String(payload.facility_name || '').trim()
    payload.street = String(payload.street || '').trim()
    payload.city = String(payload.city || '').trim()
    payload.office_phone = normalizePhoneNumber(payload.office_phone)
    payload.mobile_phone = normalizePhoneNumber(payload.mobile_phone)
    payload.email = String(payload.email || '').trim()

    form.facility_type = payload.facility_type
    form.facility_name = payload.facility_name
    form.street = payload.street
    form.city = payload.city
    form.office_phone = payload.office_phone
    form.mobile_phone = payload.mobile_phone
    form.email = payload.email

    if (!payload.facility_name) {
      errors.facility_name = 'Facility name is required.'
      return
    }
    if (!payload.facility_type) {
      errors.facility_type = 'Facility type is required.'
      return
    }
    if (!payload.street) {
      errors.street = 'Street is required.'
      return
    }
    if (!payload.city) {
      errors.city = 'City is required.'
      return
    }

    if (payload.office_phone && !isE164PhoneNumber(payload.office_phone)) {
      errors.office_phone = 'Phone number must be in E.164 format (e.g. +14165550100).'
      return
    }
    if (payload.mobile_phone && !isE164PhoneNumber(payload.mobile_phone)) {
      errors.mobile_phone = 'Phone number must be in E.164 format (e.g. +14165550100).'
      return
    }

    if (payload.country_id) {
      payload.country = resolveCountryNameById(payload.country_id) || payload.country || ''
    } else if (payload.country) {
      payload.country_id = resolveCountryIdByName(payload.country) || null
    } else {
      payload.country = null
      payload.country_id = null
    }

    if (!payload.country_id) {
      errors.country_id = 'Country is required.'
      return
    }

    if (!payload.office_phone && !payload.email) {
      errors.office_phone = 'Provide at least Office Phone or Email.'
      errors.email = 'Provide at least Office Phone or Email.'
      return
    }

    if (activeId.value) {
      await client.put(`/facilities/${activeId.value}`, payload)
    } else {
      const { data } = await client.post('/facilities', payload)
      activeId.value = data?.data?.id || null
    }
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    }
  } finally {
    saving.value = false
  }
}

const editFacility = (facility) => {
  activeId.value = facility.id
  form.facility_name = facility.facility_name || ''
  form.facility_type = normalizeFacilityTypeToId(facility.facility_type)
  form.unit_no = facility.unit_no || ''
  form.street = facility.street || ''
  form.city = facility.city || ''
  form.state = facility.state || ''
  form.postal_code = facility.postal_code || ''
  form.country_id = facility.country_id || resolveCountryIdByName(facility.country) || ''
  form.country = facility.country || ''
  form.office_phone = facility.office_phone || ''
  form.mobile_phone = facility.mobile_phone || ''
  form.email = facility.email || ''
  form.fax = facility.fax || ''
  form.website = facility.website || ''
  form.status = facility.status || 'Active'
  viewMode.value = 'form'
  mapErrors(null)
}

const deleteFacility = async (id) => {
  if (!id) return
  if (!confirm('Delete this facility?')) return
  try {
    await client.delete(`/facilities/${id}`)
    if (activeId.value === id) {
      resetForm()
    }
    fetchList()
  } catch {
    // errors handled by interceptor toast
  }
}

const deleteCurrent = () => {
  if (!activeId.value) return
  deleteFacility(activeId.value)
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadCountries()
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-facility-premium-page {
  display: grid;
  gap: 16px;
  padding-bottom: 24px;
}

.pm-facility-hero {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 14px;
  align-items: start;
  border: 1px solid #d9e3ff;
  border-radius: 18px;
  padding: 16px 18px;
  background:
    radial-gradient(circle at 92% 14%, rgba(37, 99, 235, 0.16), transparent 52%),
    linear-gradient(180deg, #f8fbff 0%, #edf4ff 100%);
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
  animation: pmFadeUp 0.36s ease both;
}

.pm-facility-kicker {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #1d4ed8;
  font-weight: 700;
  margin-bottom: 4px;
}

.pm-facility-title {
  margin: 0;
  font-size: 1.5rem;
  color: #0f172a;
}

.pm-facility-subtitle {
  margin-top: 5px;
}

.pm-facility-hero-actions {
  display: grid;
  justify-items: end;
  align-content: space-between;
  gap: 10px;
}

.pm-facility-chip {
  border-radius: 999px;
  padding: 7px 12px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #fff;
  background: linear-gradient(120deg, #2563eb 0%, #0ea5e9 100%);
  box-shadow: 0 10px 20px rgba(37, 99, 235, 0.26);
}

.pm-facility-form-shell {
  display: grid;
  gap: 14px;
}

.pm-facility-section {
  border: 1px solid #dbe5f6;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
  border-radius: 16px;
  animation: pmFadeUp 0.36s ease both;
}

.pm-facility-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 12px;
}

.pm-facility-section-tag {
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  padding: 4px 10px;
  font-size: 0.75rem;
  font-weight: 700;
}

.pm-facility-actions-sticky {
  position: sticky;
  bottom: 12px;
  z-index: 4;
  backdrop-filter: blur(4px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid #dbe5f6;
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.09);
  border-radius: 14px;
}

.pm-facility-list-shell {
  border-radius: 16px;
  border: 1px solid #dbe5f6;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
  padding: 14px;
  animation: pmFadeUp 0.36s ease both;
}

.pm-facility-list-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.pm-facility-filters {
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fbff;
  padding: 12px;
  margin-bottom: 12px;
}

.pm-facility-table-wrap {
  border: 1px solid #dbe5f6;
  border-radius: 14px;
  overflow: auto;
  max-height: calc(100vh - 310px);
}

.pm-facility-table-wrap thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  background: #f8fbff;
}

.pm-facility-table-wrap tbody tr {
  transition: background-color 0.18s ease;
}

.pm-facility-table-wrap tbody tr:hover {
  background: #f8fafc;
}

.pm-empty-table-cell {
  color: #64748b;
  font-weight: 600;
  padding: 22px 10px;
}

.pm-skeleton-row td {
  border-bottom: 1px solid #edf2f7;
  padding: 10px 12px;
}

.pm-skeleton-line {
  height: 18px;
  border-radius: 999px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 220% 100%;
  animation: pmSkeletonPulse 1.2s linear infinite;
}

.pm-facility-premium-page .form-control {
  height: 44px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background-color: #fff;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.pm-facility-premium-page select.form-control {
  appearance: none;
  background-image:
    linear-gradient(45deg, transparent 50%, #64748b 50%),
    linear-gradient(135deg, #64748b 50%, transparent 50%);
  background-position:
    calc(100% - 18px) calc(50% - 2px),
    calc(100% - 12px) calc(50% - 2px);
  background-size: 6px 6px, 6px 6px;
  background-repeat: no-repeat;
  padding-right: 34px;
}

.pm-facility-premium-page .form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.pm-required-star {
  color: #dc2626;
  font-weight: 800;
}

@media (max-width: 992px) {
  .pm-facility-hero {
    grid-template-columns: 1fr;
  }

  .pm-facility-hero-actions {
    justify-items: start;
  }

  .pm-facility-actions-sticky {
    position: static;
  }

  .pm-facility-table-wrap {
    max-height: none;
  }
}

@keyframes pmFadeUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pmSkeletonPulse {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -20% 0;
  }
}
</style>
