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

      <div class="container pm-ops-page pm-recipient-premium-page">
        <section class="pm-recipient-hero">
          <div>
            <div class="pm-recipient-kicker">Profiles Module</div>
            <h2 class="pm-recipient-title">Recipients</h2>
            <div class="pm-page-subtitle pm-recipient-subtitle">
              Dashboard &gt; Profiles &gt; Recipients &gt; {{ activeId ? 'Editor' : 'Create' }}
            </div>
          </div>
          <div class="pm-recipient-hero-actions">
            <span class="pm-recipient-chip">{{ activeId ? 'Editing' : 'New Entry' }}</span>
            <div class="pm-page-actions">
              <RouterLink class="btn btn-primary" to="/profiles/recipients">New Recipient</RouterLink>
              <RouterLink class="btn btn-outline-primary" to="/profiles/recipients/list">Recipient List</RouterLink>
            </div>
          </div>
        </section>

        <div class="pm-recipient-form-shell">
          <section class="pm-card pm-ops-card p-4 pm-recipient-section">
            <div class="pm-recipient-section-head">
              <h5 class="pm-form-title">Recipient Information</h5>
              <span class="pm-recipient-section-tag">Core</span>
            </div>
            <div class="row g-3">
              <div class="col-md-12">
                <label class="pm-field-label">Recipient Name <span class="pm-required-star">*</span></label>
                <input class="form-control" v-model="form.recipient_name" placeholder="Enter recipient name" />
                <div v-if="errors.recipient_name" class="pm-form-error">{{ errors.recipient_name }}</div>
              </div>
              <div class="col-md-12">
                <label class="pm-field-label">Recipient Type <span class="pm-required-star">*</span></label>
                <select class="form-control" v-model="form.recipient_type">
                  <option value="" disabled>Select type</option>
                  <option v-for="type in recipientTypeOptions" :key="type" :value="type">{{ type }}</option>
                </select>
                <div v-if="errors.recipient_types || errors['recipient_types.0']" class="pm-form-error">
                  {{ errors.recipient_types || errors['recipient_types.0'] }}
                </div>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4 pm-recipient-section">
            <div class="pm-recipient-section-head">
              <h5 class="pm-form-title">Residency / Location</h5>
              <span class="pm-recipient-section-tag">Address</span>
            </div>
            <div class="row g-3">
              <div class="col-md-12">
                <label class="pm-field-label">Facility <span class="pm-required-star">*</span></label>
                <select class="form-control" v-model="form.facility_id">
                  <option value="">Select facility</option>
                  <option
                    v-if="form.facility_id && !facilities.some((facility) => String(facility.id) === String(form.facility_id))"
                    :value="String(form.facility_id)"
                  >
                    {{ form.facility_name || `Facility #${form.facility_id}` }}
                  </option>
                  <option
                    v-for="facility in facilities"
                    :key="facility.id"
                    :value="String(facility.id)"
                  >
                    {{ facility.facility_name }}
                  </option>
                </select>
                <div v-if="errors.facility_id || errors.facility_name" class="pm-form-error">
                  {{ errors.facility_id || errors.facility_name }}
                </div>
                <div class="pm-help-text" v-if="!facilities.length">
                  No facility found. Create a facility first from Profiles &gt; Facilities / Property.
                </div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Floor No</label>
                <input class="form-control" v-model="form.floor_no" placeholder="e.g., Ground Floor, 1st Floor" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Residential Suite No</label>
                <input class="form-control" v-model="form.residential_suite_no" placeholder="e.g., Suite 501" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Commercial Unit No</label>
                <input class="form-control" v-model="form.commercial_unit_no" placeholder="e.g., Unit 102-A" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Office</label>
                <input class="form-control" v-model="form.office" placeholder="e.g., Office 305" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Store</label>
                <input class="form-control" v-model="form.store" placeholder="e.g., Store #42" />
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4 pm-recipient-section">
            <div class="pm-recipient-section-head">
              <h5 class="pm-form-title">Contact Information</h5>
              <span class="pm-recipient-section-tag">Communication</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Phone</label>
                <input class="form-control" v-model="form.phone" placeholder="+1 (555) 123-4567" />
                <div v-if="errors.phone" class="pm-form-error">{{ errors.phone }}</div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Email</label>
                <input class="form-control" v-model="form.email" placeholder="recipient@example.com" />
                <div v-if="errors.email" class="pm-form-error">{{ errors.email }}</div>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4 pm-recipient-section">
            <div class="pm-recipient-section-head">
              <h5 class="pm-form-title">Status &amp; Notes</h5>
              <span class="pm-recipient-section-tag">Governance</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Active <span class="pm-required-star">*</span></label>
                <select class="form-control" v-model="form.is_active">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
                <div v-if="errors.is_active" class="pm-form-error">{{ errors.is_active }}</div>
              </div>
              <div class="col-md-12">
                <label class="pm-field-label">Notes</label>
                <textarea class="form-control" rows="4" v-model="form.notes" placeholder="Add any additional notes..."></textarea>
                <div class="pm-help-text">{{ form.notes.length }}/2000 characters</div>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-3 pm-recipient-actions-sticky">
            <div class="pm-form-actions pm-form-actions-right">
              <button class="btn btn-outline-primary" type="button" :disabled="!!activeId" @click="resetForm">
                New
              </button>
              <button class="btn btn-outline-danger" type="button" :disabled="!activeId" @click="deleteCurrent">
                Delete
              </button>
              <button class="btn btn-primary" type="button" :disabled="saving" @click="saveRecipient">
                {{ saving ? 'Saving...' : (activeId ? 'Update' : 'Save') }}
              </button>
            </div>
          </section>
        </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'

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
const errors = reactive({})
const facilities = ref([])

const recipientTypeOptions = [
  'Customer',
  'Employees',
  'Guest',
  'Landlord',
  'Student',
  'Tenant',
  'Visitor',
]

const form = reactive({
  recipient_name: '',
  recipient_type: '',
  facility_id: '',
  facility_name: '',
  floor_no: '',
  residential_suite_no: '',
  commercial_unit_no: '',
  office: '',
  store: '',
  phone: '',
  email: '',
  is_active: true,
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

const resolveFacilityIdByName = (facilityName) => {
  const normalized = String(facilityName || '').trim().toLowerCase()
  if (!normalized) return ''
  const matched = facilities.value.find((facility) => String(facility.facility_name || '').trim().toLowerCase() === normalized)
  return matched ? String(matched.id) : ''
}

const loadFacilities = async () => {
  try {
    const { data } = await client.get('/facilities')
    facilities.value = Array.isArray(data?.data) ? data.data : []
  } catch {
    facilities.value = []
  }
}

const resetForm = () => {
  form.recipient_name = ''
  form.recipient_type = ''
  form.facility_id = ''
  form.facility_name = ''
  form.floor_no = ''
  form.residential_suite_no = ''
  form.commercial_unit_no = ''
  form.office = ''
  form.store = ''
  form.phone = ''
  form.email = ''
  form.is_active = true
  form.notes = ''
  activeId.value = null
  mapErrors(null)
}

const loadRecipient = async (id) => {
  if (!id) return
  try {
    const { data } = await client.get(`/recipients/${id}`)
    const item = data?.data
    if (!item) return
    activeId.value = item.id
    form.recipient_name = item.recipient_name || ''
    form.recipient_type = Array.isArray(item.recipient_types) ? (item.recipient_types[0] || '') : ''
    form.facility_name = item.facility_name || ''
    form.facility_id = item.facility_id ? String(item.facility_id) : resolveFacilityIdByName(item.facility_name)
    form.floor_no = item.floor_no || ''
    form.residential_suite_no = item.residential_suite_no || ''
    form.commercial_unit_no = item.commercial_unit_no || ''
    form.office = item.office || ''
    form.store = item.store || ''
    form.phone = item.phone || ''
    form.email = item.email || ''
    form.is_active = !!item.is_active
    form.notes = item.notes || ''
  } catch {
    // handled by toast
  }
}

const saveRecipient = async () => {
  saving.value = true
  mapErrors(null)
  try {
    const selectedFacility = facilities.value.find((facility) => Number(facility.id) === Number(form.facility_id))
    const payload = {
      ...form,
      facility_id: form.facility_id ? Number(form.facility_id) : null,
      facility_name: selectedFacility?.facility_name || form.facility_name || '',
      phone: normalizePhoneNumber(form.phone),
      recipient_types: form.recipient_type ? [form.recipient_type] : [],
    }
    form.phone = payload.phone
    form.facility_name = payload.facility_name

    if (!payload.facility_id) {
      errors.facility_id = 'Facility is required.'
      return
    }

    if (payload.phone && !isE164PhoneNumber(payload.phone)) {
      errors.phone = 'Phone number must be in E.164 format (e.g. +14165550100).'
      return
    }
    if (activeId.value) {
      await client.put(`/recipients/${activeId.value}`, payload)
    } else {
      const { data } = await client.post('/recipients', payload)
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

const deleteCurrent = async () => {
  if (!activeId.value) return
  if (!confirm('Delete this recipient?')) return
  try {
    await client.delete(`/recipients/${activeId.value}`)
    resetForm()
  } catch {
    // handled by toast
  }
}

onMounted(async () => {
  if (authState.user?.name) userName.value = authState.user.name
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
  await loadFacilities()
  if (route.query?.id) loadRecipient(route.query.id)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})

watch(
  () => route.query?.id,
  async (id) => {
    if (id) {
      if (!facilities.value.length) {
        await loadFacilities()
      }
      loadRecipient(id)
    } else {
      resetForm()
    }
  }
)
</script>

<style scoped>
.pm-recipient-premium-page {
  display: grid;
  gap: 16px;
  padding-bottom: 24px;
}

.pm-recipient-hero {
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

.pm-recipient-kicker {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #1d4ed8;
  font-weight: 700;
  margin-bottom: 4px;
}

.pm-recipient-title {
  margin: 0;
  font-size: 1.5rem;
  color: #0f172a;
}

.pm-recipient-subtitle {
  margin-top: 5px;
}

.pm-recipient-hero-actions {
  display: grid;
  justify-items: end;
  align-content: space-between;
  gap: 10px;
}

.pm-recipient-chip {
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

.pm-recipient-form-shell {
  display: grid;
  gap: 14px;
}

.pm-recipient-section {
  border: 1px solid #dbe5f6;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
  border-radius: 16px;
  animation: pmFadeUp 0.36s ease both;
}

.pm-recipient-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 12px;
}

.pm-recipient-section-tag {
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  padding: 4px 10px;
  font-size: 0.75rem;
  font-weight: 700;
}

.pm-recipient-actions-sticky {
  position: sticky;
  bottom: 12px;
  z-index: 4;
  backdrop-filter: blur(4px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid #dbe5f6;
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.09);
  border-radius: 14px;
}

.pm-recipient-premium-page .form-control {
  height: 44px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background-color: #fff;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.pm-recipient-premium-page textarea.form-control {
  min-height: 116px;
  height: auto;
  resize: vertical;
}

.pm-recipient-premium-page select.form-control {
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

.pm-recipient-premium-page .form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.pm-required-star {
  color: #dc2626;
  font-weight: 800;
}

@media (max-width: 992px) {
  .pm-recipient-hero {
    grid-template-columns: 1fr;
  }

  .pm-recipient-hero-actions {
    justify-items: start;
  }

  .pm-recipient-actions-sticky {
    position: static;
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
</style>
