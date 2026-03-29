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

      <div class="container pm-ops-page pm-courier-shell">
        <div class="pm-page-head">
          <div>
            <h2>Couriers</h2>
            <div class="pm-page-subtitle">Dashboard &gt; Profiles &gt; Couriers &gt; {{ pageStateLabel }}</div>
          </div>
          <div class="pm-page-actions">
            <button class="btn btn-primary" type="button" @click="openCreate">New Courier</button>
            <RouterLink class="btn btn-outline-primary" to="/profiles/couriers/list">Courier List</RouterLink>
          </div>
        </div>

        <section class="pm-card pm-ops-card p-4 pm-courier-form-card">
          <h5 class="pm-form-title">Courier Information</h5>
          <div class="row g-3">
            <div class="col-md-12">
              <label class="pm-field-label">Company No / Name <span class="text-danger">*</span></label>
              <input class="form-control" v-model="form.company_name" :disabled="readOnly" placeholder="Enter courier company name" />
              <div v-if="errors.company_name" class="pm-form-error">{{ errors.company_name }}</div>
            </div>

            <div class="col-md-6">
              <label class="pm-field-label">Email</label>
              <input class="form-control" v-model="form.email" :disabled="readOnly" placeholder="courier@company.com" />
              <div v-if="errors.email" class="pm-form-error">{{ errors.email }}</div>
            </div>

            <div class="col-md-6">
              <label class="pm-field-label">Phone</label>
              <input class="form-control" v-model="form.phone" :disabled="readOnly" placeholder="+1 (555) 123-4567" />
              <div v-if="errors.phone" class="pm-form-error">{{ errors.phone }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Website</label>
              <input class="form-control" v-model="form.website" :disabled="readOnly" placeholder="https://www.couriercompany.com" />
              <div v-if="errors.website" class="pm-form-error">{{ errors.website }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Active <span class="text-danger">*</span></label>
              <div class="pm-courier-status-toggle" role="group" aria-label="Active status">
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
              <div class="pm-help-text">Courier is available for delivery methods</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Notes</label>
              <textarea
                class="form-control"
                rows="4"
                v-model="form.notes"
                :disabled="readOnly"
                placeholder="Add any additional notes about this courier..."
                @input="enforceNotesLimit"
              ></textarea>
              <div class="pm-help-text">{{ form.notes.length }}/{{ MAX_NOTES_LENGTH }} characters</div>
              <div v-if="errors.notes" class="pm-form-error">{{ errors.notes }}</div>
            </div>
          </div>
        </section>

        <section class="pm-card pm-ops-card p-3">
          <div class="pm-form-actions pm-form-actions-right pm-courier-actions">
            <button class="btn btn-outline-secondary" type="button" :disabled="!activeId && !hasDirty" @click="openCreate">New</button>
            <button class="btn btn-outline-primary" type="button" :disabled="!activeId || !readOnly" @click="startEdit">Edit</button>
            <button class="btn btn-outline-danger" type="button" :disabled="!activeId || saving" @click="deleteCurrent">Delete</button>
            <button class="btn btn-primary" type="button" :disabled="saving || readOnly" @click="saveCourier">
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

const MAX_NOTES_LENGTH = 500

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
const errors = reactive({})

const form = reactive({
  company_name: '',
  email: '',
  phone: '',
  website: '',
  is_active: true,
  notes: '',
})

const pageStateLabel = computed(() => {
  if (!activeId.value) return 'New'
  return readOnly.value ? 'Details' : 'Edit'
})

const hasDirty = computed(() => (
  !!String(form.company_name || '').trim()
  || !!String(form.email || '').trim()
  || !!String(form.phone || '').trim()
  || !!String(form.website || '').trim()
  || !!String(form.notes || '').trim()
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

const mapErrors = (errs) => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!errs) return
  Object.entries(errs).forEach(([key, value]) => {
    errors[key] = Array.isArray(value) ? value[0] : value
  })
}

const enforceNotesLimit = () => {
  if (String(form.notes || '').length > MAX_NOTES_LENGTH) {
    form.notes = String(form.notes || '').slice(0, MAX_NOTES_LENGTH)
  }
}

const setActiveValue = (value) => {
  if (readOnly.value) return
  form.is_active = !!value
}

const resetForm = () => {
  form.company_name = ''
  form.email = ''
  form.phone = ''
  form.website = ''
  form.is_active = true
  form.notes = ''
  activeId.value = null
  readOnly.value = false
  mapErrors(null)
}

const openCreate = async () => {
  resetForm()
  await router.replace({ path: '/profiles/couriers' })
}

const syncReadOnlyFromRoute = () => {
  if (!activeId.value) {
    readOnly.value = false
    return
  }

  const mode = String(route.query?.mode || '').trim().toLowerCase()
  readOnly.value = mode !== 'edit'
}

const loadCourier = async (id) => {
  if (!id) return

  try {
    const { data } = await client.get(`/couriers/${id}`)
    const item = data?.data
    if (!item) return

    activeId.value = item.id
    form.company_name = item.company_name || ''
    form.email = item.email || ''
    form.phone = item.phone || ''
    form.website = item.website || ''
    form.is_active = !!item.is_active
    form.notes = item.notes || ''
    syncReadOnlyFromRoute()
    mapErrors(null)
  } catch {
    setFlash('Failed to load courier details.', 'danger', 2500)
  }
}

const validateForm = () => {
  mapErrors(null)

  if (!String(form.company_name || '').trim()) {
    errors.company_name = 'Company name is required.'
  }

  if (String(form.email || '').trim()) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailPattern.test(String(form.email || '').trim())) {
      errors.email = 'Provide a valid email address.'
    }
  }

  if (String(form.website || '').trim()) {
    const websiteValue = String(form.website || '').trim()
    if (!/^https?:\/\//i.test(websiteValue)) {
      errors.website = 'Website must start with http:// or https://'
    }
  }

  const phoneValue = normalizePhoneNumber(form.phone)
  form.phone = phoneValue
  if (phoneValue && !isE164PhoneNumber(phoneValue)) {
    errors.phone = 'Phone number must be in E.164 format (e.g. +14165550100).'
  }

  if (String(form.notes || '').length > MAX_NOTES_LENGTH) {
    errors.notes = `Notes cannot be more than ${MAX_NOTES_LENGTH} characters.`
  }

  return Object.keys(errors).length === 0
}

const saveCourier = async () => {
  enforceNotesLimit()
  if (!validateForm()) return

  saving.value = true
  mapErrors(null)

  try {
    const payload = {
      company_name: String(form.company_name || '').trim(),
      email: String(form.email || '').trim() || null,
      phone: String(form.phone || '').trim() || null,
      website: String(form.website || '').trim() || null,
      is_active: !!form.is_active,
      notes: String(form.notes || '').trim() || null,
    }

    if (activeId.value) {
      await client.put(`/couriers/${activeId.value}`, payload)
      setFlash('Courier updated successfully.', 'success', 2200)
    } else {
      const { data } = await client.post('/couriers', payload)
      activeId.value = data?.data?.id || null
      setFlash('Courier created successfully.', 'success', 2200)
    }

    readOnly.value = true
    await router.replace({ path: '/profiles/couriers', query: { id: activeId.value, mode: 'view' } })
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    } else {
      setFlash('Failed to save courier.', 'danger', 2500)
    }
  } finally {
    saving.value = false
  }
}

const startEdit = async () => {
  if (!activeId.value) return
  readOnly.value = false
  await router.replace({ path: '/profiles/couriers', query: { id: activeId.value, mode: 'edit' } })
}

const deleteCurrent = async () => {
  if (!activeId.value) return
  if (!confirm('Delete this courier?')) return

  try {
    await client.delete(`/couriers/${activeId.value}`)
    setFlash('Courier deleted successfully.', 'success', 2200)
    await openCreate()
  } catch {
    setFlash('Failed to delete courier.', 'danger', 2500)
  }
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})

watch(
  () => [route.query?.id, route.query?.mode],
  async ([id]) => {
    if (id) {
      await loadCourier(id)
      return
    }
    resetForm()
  },
  { immediate: true }
)
</script>

<style scoped>
.pm-courier-shell {
  display: grid;
  gap: 14px;
}

.pm-courier-form-card {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-courier-status-toggle {
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

.pm-courier-actions .btn {
  min-width: 110px;
}

.pm-form-error {
  margin-top: 6px;
  color: #dc2626;
  font-size: 0.82rem;
  font-weight: 600;
}

@media (max-width: 992px) {
  .pm-courier-actions {
    flex-wrap: wrap;
  }

  .pm-courier-actions .btn {
    width: 100%;
  }
}
</style>
