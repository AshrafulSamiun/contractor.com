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

      <div class="container pm-ops-page pm-method-shell">
        <div class="pm-page-head">
          <div>
            <h2>Delivery Methods</h2>
            <div class="pm-page-subtitle">Dashboard &gt; Profiles &gt; Delivery Methods &gt; {{ pageStateLabel }}</div>
          </div>
          <div class="pm-page-actions">
            <button class="btn btn-primary" type="button" @click="openCreate">New Method</button>
            <RouterLink class="btn btn-outline-primary" to="/profiles/delivery-methods/list">Method List</RouterLink>
          </div>
        </div>

        <section class="pm-card pm-ops-card p-4 pm-method-form-card">
          <h5 class="pm-form-title">Delivery Method Information</h5>
          <div class="row g-3">
            <div class="col-md-12">
              <label class="pm-field-label">No / Name <span class="text-danger">*</span></label>
              <input class="form-control" v-model="form.method_name" :disabled="readOnly" placeholder="Enter delivery method name" />
              <div v-if="errors.method_name" class="pm-form-error">{{ errors.method_name }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Delivery Method Type <span class="text-danger">*</span></label>
              <select class="form-control" v-model="form.method_type" :disabled="readOnly">
                <option value="">Select delivery method type</option>
                <option v-for="type in METHOD_TYPE_OPTIONS" :key="type" :value="type">{{ type }}</option>
              </select>
              <div class="pm-help-text">Choose how parcels are picked up or delivered.</div>
              <div v-if="errors.method_type" class="pm-form-error">{{ errors.method_type }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Active <span class="text-danger">*</span></label>
              <div class="pm-method-status-toggle" role="group" aria-label="Active status">
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
              <div class="pm-help-text">Method is selectable during parcel flow</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Note</label>
              <textarea
                class="form-control"
                rows="4"
                v-model="form.note"
                :disabled="readOnly"
                placeholder="Add any additional notes about this delivery method..."
                @input="enforceNoteLimit"
              ></textarea>
              <div class="pm-help-text">{{ form.note.length }}/{{ MAX_NOTE_LENGTH }} characters</div>
              <div v-if="errors.note" class="pm-form-error">{{ errors.note }}</div>
            </div>
          </div>
        </section>

        <section class="pm-card pm-ops-card p-3">
          <div class="pm-form-actions pm-form-actions-right pm-method-actions">
            <button class="btn btn-outline-secondary" type="button" :disabled="!activeId && !hasDirty" @click="openCreate">New</button>
            <button class="btn btn-outline-primary" type="button" :disabled="!activeId || !readOnly" @click="startEdit">Edit</button>
            <button class="btn btn-outline-danger" type="button" :disabled="!activeId || saving" @click="deleteCurrent">Delete</button>
            <button class="btn btn-primary" type="button" :disabled="saving || readOnly" @click="saveMethod">
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
import { DELIVERY_METHOD_TYPE_OPTIONS } from '../config/deliveryMethodTypes'

const MAX_NOTE_LENGTH = 300
const METHOD_TYPE_OPTIONS = DELIVERY_METHOD_TYPE_OPTIONS

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
  method_name: '',
  method_type: '',
  is_active: true,
  note: '',
})

const pageStateLabel = computed(() => {
  if (!activeId.value) return 'New'
  return readOnly.value ? 'Details' : 'Edit'
})

const hasDirty = computed(() => (
  !!String(form.method_name || '').trim()
  || !!String(form.method_type || '').trim()
  || !!String(form.note || '').trim()
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
  form.method_name = ''
  form.method_type = ''
  form.is_active = true
  form.note = ''
  activeId.value = null
  readOnly.value = false
  mapErrors(null)
}

const openCreate = async () => {
  resetForm()
  await router.replace({ path: '/profiles/delivery-methods' })
}

const syncReadOnlyFromRoute = () => {
  if (!activeId.value) {
    readOnly.value = false
    return
  }

  const mode = String(route.query?.mode || '').trim().toLowerCase()
  readOnly.value = mode !== 'edit'
}

const loadMethod = async (id) => {
  if (!id) return

  try {
    const { data } = await client.get(`/delivery-methods/${id}`)
    const item = data?.data
    if (!item) return

    activeId.value = item.id
    form.method_name = item.method_name || ''
    form.method_type = item.method_type || ''
    form.is_active = !!item.is_active
    form.note = item.note || ''
    syncReadOnlyFromRoute()
    mapErrors(null)
  } catch {
    setFlash('Failed to load delivery method details.', 'danger', 2500)
  }
}

const validateForm = () => {
  mapErrors(null)

  if (!String(form.method_name || '').trim()) {
    errors.method_name = 'Method name is required.'
  }

  if (!String(form.method_type || '').trim()) {
    errors.method_type = 'Delivery method type is required.'
  }

  if (String(form.note || '').length > MAX_NOTE_LENGTH) {
    errors.note = `Note cannot be more than ${MAX_NOTE_LENGTH} characters.`
  }

  return Object.keys(errors).length === 0
}

const saveMethod = async () => {
  enforceNoteLimit()
  if (!validateForm()) return

  saving.value = true
  mapErrors(null)

  try {
    const payload = {
      method_name: String(form.method_name || '').trim(),
      method_type: String(form.method_type || '').trim(),
      is_active: !!form.is_active,
      note: String(form.note || '').trim() || null,
    }

    if (activeId.value) {
      await client.put(`/delivery-methods/${activeId.value}`, payload)
      setFlash('Delivery method updated successfully.', 'success', 2200)
    } else {
      const { data } = await client.post('/delivery-methods', payload)
      activeId.value = data?.data?.id || null
      setFlash('Delivery method created successfully.', 'success', 2200)
    }

    readOnly.value = true
    await router.replace({ path: '/profiles/delivery-methods', query: { id: activeId.value, mode: 'view' } })
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    } else {
      setFlash('Failed to save delivery method.', 'danger', 2500)
    }
  } finally {
    saving.value = false
  }
}

const startEdit = async () => {
  if (!activeId.value) return
  readOnly.value = false
  await router.replace({ path: '/profiles/delivery-methods', query: { id: activeId.value, mode: 'edit' } })
}

const deleteCurrent = async () => {
  if (!activeId.value) return
  if (!confirm('Delete this delivery method?')) return

  try {
    await client.delete(`/delivery-methods/${activeId.value}`)
    setFlash('Delivery method deleted successfully.', 'success', 2200)
    await openCreate()
  } catch {
    setFlash('Failed to delete delivery method.', 'danger', 2500)
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
      await loadMethod(id)
      return
    }
    resetForm()
  },
  { immediate: true }
)
</script>

<style scoped>
.pm-method-shell {
  display: grid;
  gap: 14px;
}

.pm-method-form-card {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-method-status-toggle {
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

.pm-method-actions .btn {
  min-width: 110px;
}

.pm-form-error {
  margin-top: 6px;
  color: #dc2626;
  font-size: 0.82rem;
  font-weight: 600;
}

@media (max-width: 992px) {
  .pm-method-actions {
    flex-wrap: wrap;
  }

  .pm-method-actions .btn {
    width: 100%;
  }
}
</style>
