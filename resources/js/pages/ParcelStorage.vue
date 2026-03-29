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
          <input v-model="searchQuery" class="form-control" placeholder="Search parcels, recipients, tracking..." />
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

      <div class="container pm-ops-page pm-storage-shell">
        <div class="pm-page-head">
          <div>
            <h2>Parcel Storage</h2>
            <div class="pm-page-subtitle">Dashboard &gt; Profiles &gt; Parcel Room / Storage &gt; {{ pageStateLabel }}</div>
          </div>
          <div class="pm-page-actions">
            <button class="btn btn-primary" type="button" @click="openCreate">New Storage</button>
            <RouterLink class="btn btn-outline-primary" to="/profiles/storage/list">Storage List</RouterLink>
          </div>
        </div>

        <section class="pm-card pm-ops-card p-4 pm-storage-form-card">
          <h5 class="pm-form-title">Storage Information</h5>
          <div class="row g-3">
            <div class="col-md-12">
              <label class="pm-field-label">No / Name <span class="text-danger">*</span></label>
              <input
                class="form-control"
                v-model="form.storage_name"
                :disabled="readOnly"
                placeholder="Enter storage name or number"
              />
              <div v-if="errors.storage_name" class="pm-form-error">{{ errors.storage_name }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Property / Facility <span class="text-danger">*</span></label>
              <select class="form-control" v-model="form.facility_id" :disabled="readOnly || facilitiesLoading">
                <option value="">{{ facilitiesLoading ? 'Loading facilities...' : 'Select facility' }}</option>
                <option v-for="facility in facilities" :key="facility.id" :value="String(facility.id)">{{ facility.facility_name }}</option>
              </select>
              <div class="pm-help-text" v-if="!facilitiesLoading && !facilities.length">No facility found. Please create facility first.</div>
              <div v-if="errors.facility_id" class="pm-form-error">{{ errors.facility_id }}</div>
            </div>

            <div class="col-md-6">
              <label class="pm-field-label">Floor No</label>
              <input
                class="form-control"
                v-model="form.floor_no"
                :disabled="readOnly"
                placeholder="e.g., Ground Floor, 1st Floor, B1"
              />
            </div>

            <div class="col-md-6">
              <label class="pm-field-label">Area / Section</label>
              <input
                class="form-control"
                v-model="form.area_section"
                :disabled="readOnly"
                placeholder="e.g., North Wing, Section A"
              />
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Dedicated Item</label>
              <input class="form-control" v-model="form.dedicated_item" :disabled="readOnly" placeholder="Dedicated item" />
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Active <span class="text-danger">*</span></label>
              <div class="pm-storage-status-toggle" role="group" aria-label="Active status">
                <button
                  class="pm-status-toggle-btn"
                  type="button"
                  :class="{ active: form.is_active }"
                  :disabled="readOnly"
                  @click="setActiveValue(true)"
                >
                  Yes
                </button>
                <button
                  class="pm-status-toggle-btn"
                  type="button"
                  :class="{ active: !form.is_active }"
                  :disabled="readOnly"
                  @click="setActiveValue(false)"
                >
                  No
                </button>
              </div>
              <div class="pm-help-text">Storage is available for parcel allocation</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Note</label>
              <textarea
                class="form-control"
                rows="4"
                v-model="form.note"
                :disabled="readOnly"
                placeholder="Add any additional notes or instructions..."
                @input="enforceNoteLimit"
              ></textarea>
              <div class="pm-help-text">{{ form.note.length }}/{{ MAX_NOTE_LENGTH }} characters</div>
              <div v-if="errors.note" class="pm-form-error">{{ errors.note }}</div>
            </div>
          </div>
        </section>

        <section class="pm-card pm-ops-card p-3">
          <div class="pm-form-actions pm-form-actions-right pm-storage-actions">
            <button class="btn btn-outline-secondary" type="button" :disabled="!activeId && !hasDirty" @click="openCreate">New</button>

            <button
              v-if="activeId && readOnly"
              class="btn btn-outline-primary"
              type="button"
              @click="startEdit"
            >
              Edit
            </button>

            <button v-if="activeId" class="btn btn-outline-danger" type="button" :disabled="saving" @click="deleteCurrent">
              Delete
            </button>

            <button v-if="!readOnly" class="btn btn-primary" type="button" :disabled="saving" @click="saveStorage">
              {{ saving ? (activeId ? 'Updating...' : 'Saving...') : (activeId ? 'Update' : 'Save') }}
            </button>
          </div>
        </section>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { setFlash } from '../store/flash'

const MAX_NOTE_LENGTH = 500

const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const saving = ref(false)
const activeId = ref(null)
const readOnly = ref(false)
const facilities = ref([])
const facilitiesLoading = ref(false)
const errors = reactive({})

const form = reactive({
  storage_name: '',
  facility_id: '',
  floor_no: '',
  area_section: '',
  dedicated_item: '',
  is_active: true,
  note: '',
})

const pageStateLabel = computed(() => {
  if (!activeId.value) return 'New'
  return readOnly.value ? 'Details' : 'Edit'
})

const hasDirty = computed(() => (
  !!form.storage_name
  || !!form.facility_id
  || !!form.floor_no
  || !!form.area_section
  || !!form.dedicated_item
  || !!form.note
  || !form.is_active
))

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

const enforceNoteLimit = () => {
  if (String(form.note || '').length > MAX_NOTE_LENGTH) {
    form.note = String(form.note || '').slice(0, MAX_NOTE_LENGTH)
  }
}

const setActiveValue = (value) => {
  if (readOnly.value) return
  form.is_active = !!value
}

const resetForm = () => {
  form.storage_name = ''
  form.facility_id = ''
  form.floor_no = ''
  form.area_section = ''
  form.dedicated_item = ''
  form.is_active = true
  form.note = ''
  activeId.value = null
  readOnly.value = false
  mapErrors(null)
}

const openCreate = async () => {
  resetForm()
  await router.replace({ path: '/profiles/storage' })
}

const loadFacilities = async () => {
  facilitiesLoading.value = true
  try {
    const { data } = await client.get('/facilities')
    const items = Array.isArray(data?.data) ? data.data : []
    facilities.value = items
      .map((item) => ({
        id: item?.id,
        facility_name: String(item?.facility_name || '').trim(),
      }))
      .filter((item) => !!item.id && !!item.facility_name)
      .sort((a, b) => a.facility_name.localeCompare(b.facility_name))
  } catch {
    facilities.value = []
  } finally {
    facilitiesLoading.value = false
  }
}

const resolveFacilityIdByName = (facilityName) => {
  const normalized = String(facilityName || '').trim().toLowerCase()
  if (!normalized) return ''

  const matched = facilities.value.find((item) => String(item.facility_name || '').trim().toLowerCase() === normalized)
  return matched?.id ? String(matched.id) : ''
}

const syncReadOnlyFromRoute = () => {
  if (!activeId.value) {
    readOnly.value = false
    return
  }

  const mode = String(route.query?.mode || '').trim().toLowerCase()
  readOnly.value = mode !== 'edit'
}

const loadStorage = async (id) => {
  if (!id) return
  try {
    const { data } = await client.get(`/parcel-storages/${id}`)
    const item = data?.data
    if (!item) return

    if (!item.facility_id && !facilities.value.length && !facilitiesLoading.value) {
      await loadFacilities()
    }

    activeId.value = item.id
    form.storage_name = item.storage_name || ''
    form.facility_id = item.facility_id ? String(item.facility_id) : resolveFacilityIdByName(item.facility_name)
    form.floor_no = item.floor_no || ''
    form.area_section = item.area_section || ''
    form.dedicated_item = item.dedicated_item || ''
    form.is_active = !!item.is_active
    form.note = item.note || ''
    syncReadOnlyFromRoute()
    mapErrors(null)
  } catch {
    setFlash('Failed to load storage details.', 'danger', 2500)
  }
}

const validateForm = () => {
  mapErrors(null)

  if (!String(form.storage_name || '').trim()) {
    errors.storage_name = 'Storage name is required.'
  }

  if (!String(form.facility_id || '').trim()) {
    errors.facility_id = 'Facility is required.'
  }

  if (String(form.note || '').length > MAX_NOTE_LENGTH) {
    errors.note = `Note cannot be more than ${MAX_NOTE_LENGTH} characters.`
  }

  return Object.keys(errors).length === 0
}

const saveStorage = async () => {
  enforceNoteLimit()
  if (!validateForm()) return

  saving.value = true
  mapErrors(null)

  try {
    const payload = {
      storage_name: String(form.storage_name || '').trim(),
      facility_id: Number(form.facility_id),
      floor_no: String(form.floor_no || '').trim() || null,
      area_section: String(form.area_section || '').trim() || null,
      dedicated_item: String(form.dedicated_item || '').trim() || null,
      is_active: !!form.is_active,
      note: String(form.note || '').trim() || null,
    }

    if (activeId.value) {
      await client.put(`/parcel-storages/${activeId.value}`, payload)
      setFlash('Storage updated successfully.', 'success', 2200)
    } else {
      const { data } = await client.post('/parcel-storages', payload)
      activeId.value = data?.data?.id || null
      setFlash('Storage created successfully.', 'success', 2200)
    }

    readOnly.value = true
    await router.replace({ path: '/profiles/storage', query: { id: activeId.value, mode: 'view' } })
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    } else {
      setFlash('Failed to save storage.', 'danger', 2500)
    }
  } finally {
    saving.value = false
  }
}

const startEdit = async () => {
  if (!activeId.value) return
  readOnly.value = false
  await router.replace({ path: '/profiles/storage', query: { id: activeId.value, mode: 'edit' } })
}

const deleteCurrent = async () => {
  if (!activeId.value) return
  if (!confirm('Delete this storage?')) return

  try {
    await client.delete(`/parcel-storages/${activeId.value}`)
    setFlash('Storage deleted successfully.', 'success', 2200)
    await openCreate()
  } catch {
    setFlash('Failed to delete storage.', 'danger', 2500)
  }
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
  loadFacilities()
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})

watch(
  () => [route.query?.id, route.query?.mode],
  async ([id]) => {
    if (id) {
      await loadStorage(id)
      return
    }
    resetForm()
  },
  { immediate: true }
)
</script>

<style scoped>
.pm-storage-shell {
  display: grid;
  gap: 14px;
}

.pm-storage-form-card {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-storage-status-toggle {
  display: inline-flex;
  border: 1px solid #d7e4ff;
  border-radius: 12px;
  overflow: hidden;
}

.pm-status-toggle-btn {
  border: 0;
  background: #f8fafc;
  color: #475569;
  min-width: 62px;
  height: 36px;
  padding: 0 14px;
  font-weight: 700;
}

.pm-status-toggle-btn.active {
  background: #16a34a;
  color: #ffffff;
}

.pm-status-toggle-btn:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.pm-storage-actions .btn {
  min-width: 110px;
}

.pm-form-error {
  margin-top: 6px;
  color: #dc2626;
  font-size: 0.82rem;
  font-weight: 600;
}

@media (max-width: 992px) {
  .pm-storage-actions {
    flex-wrap: wrap;
  }

  .pm-storage-actions .btn {
    width: 100%;
  }
}
</style>
