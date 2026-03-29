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

      <div class="container pm-ops-page pm-item-shell">
        <div class="pm-page-head">
          <div>
            <h2>Parcel / Delivery Items</h2>
            <div class="pm-page-subtitle">Dashboard &gt; Profiles &gt; Parcel / Delivery Items &gt; {{ pageStateLabel }}</div>
          </div>
          <div class="pm-page-actions">
            <button class="btn btn-primary" type="button" @click="openCreate">New Item</button>
            <RouterLink class="btn btn-outline-primary" to="/profiles/items/list">Item List</RouterLink>
          </div>
        </div>

        <section class="pm-card pm-ops-card p-4 pm-item-form-card">
          <h5 class="pm-form-title">Item Information</h5>
          <div class="row g-3">
            <div class="col-md-12">
              <label class="pm-field-label">No / Name <span class="text-danger">*</span></label>
              <input class="form-control" v-model="form.item_name" :disabled="readOnly" placeholder="Enter item name" />
              <div v-if="errors.item_name" class="pm-form-error">{{ errors.item_name }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Item Category <span class="text-danger">*</span></label>
              <div class="pm-item-category-picker">
                <div class="pm-item-selected-row">
                  <span
                    v-for="category in form.categories"
                    :key="`selected-${category}`"
                    class="pm-item-chip"
                    :class="chipClass(category)"
                  >
                    {{ category }}
                    <button
                      v-if="!readOnly"
                      class="pm-item-chip-remove"
                      type="button"
                      aria-label="Remove category"
                      @click="removeCategory(category)"
                    >
                      &times;
                    </button>
                  </span>
                  <span v-if="!form.categories.length" class="pm-item-empty-chip">Select one or more categories</span>
                </div>

                <div class="pm-item-options-row" v-if="!readOnly">
                  <button
                    v-for="category in availableCategories"
                    :key="`option-${category}`"
                    class="pm-item-option-btn"
                    type="button"
                    @click="addCategory(category)"
                  >
                    + {{ category }}
                  </button>
                </div>
                <div class="pm-item-custom-row" v-if="!readOnly">
                  <input
                    class="form-control"
                    v-model="customCategory"
                    placeholder="Add custom category"
                    maxlength="50"
                    @keyup.enter.prevent="addCustomCategory"
                  />
                  <button class="btn btn-outline-primary" type="button" @click="addCustomCategory">Add</button>
                </div>
              </div>
              <div class="pm-help-text">{{ readOnly ? 'Click Edit to add or remove categories' : 'Select one or more categories' }}</div>
              <div v-if="errors.categories || errors['categories.0']" class="pm-form-error">
                {{ errors.categories || errors['categories.0'] }}
              </div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Active <span class="text-danger">*</span></label>
              <div class="pm-item-status-toggle" role="group" aria-label="Active status">
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
              <div class="pm-help-text">Item is selectable during parcel intake</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Note</label>
              <textarea
                class="form-control"
                rows="4"
                v-model="form.note"
                :disabled="readOnly"
                placeholder="Add any additional notes about this item..."
                @input="enforceNoteLimit"
              ></textarea>
              <div class="pm-help-text">{{ form.note.length }}/{{ MAX_NOTE_LENGTH }} characters</div>
              <div v-if="errors.note" class="pm-form-error">{{ errors.note }}</div>
            </div>
          </div>
        </section>

        <section class="pm-card pm-ops-card p-3">
          <div class="pm-form-actions pm-form-actions-right pm-item-actions">
            <button class="btn btn-outline-secondary" type="button" :disabled="!activeId && !hasDirty" @click="openCreate">New</button>
            <button class="btn btn-outline-primary" type="button" :disabled="!activeId || !readOnly" @click="startEdit">Edit</button>
            <button class="btn btn-outline-danger" type="button" :disabled="!activeId || saving" @click="deleteCurrent">Delete</button>
            <button class="btn btn-primary" type="button" :disabled="saving || readOnly" @click="saveItem">
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

const MAX_NOTE_LENGTH = 300
const CATEGORY_OPTIONS = [
  'Food',
  'Grocery',
  'Appliance',
  'Furniture',
  'Medicine',
  'Letters',
  'Parcels',
]

const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const customCategory = ref('')

const saving = ref(false)
const activeId = ref(null)
const readOnly = ref(false)
const errors = reactive({})

const form = reactive({
  item_name: '',
  categories: [],
  is_active: true,
  note: '',
})

const pageStateLabel = computed(() => {
  if (!activeId.value) return 'New'
  return readOnly.value ? 'Details' : 'Edit'
})

const availableCategories = computed(() => CATEGORY_OPTIONS.filter((item) => !hasCategory(item)))

const hasDirty = computed(() => (
  !!String(form.item_name || '').trim()
  || form.categories.length > 0
  || !form.is_active
  || !!String(form.note || '').trim()
))

const chipClass = (category) => {
  const map = {
    Food: 'is-food',
    Grocery: 'is-grocery',
    Appliance: 'is-appliance',
    Furniture: 'is-furniture',
    Medicine: 'is-medicine',
    Letters: 'is-letters',
    Parcels: 'is-parcels',
  }
  return map[category] || 'is-default'
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

const addCategory = (category) => {
  if (readOnly.value) return
  const value = sanitizeCategoryValue(category)
  if (!value) return
  if (hasCategory(value)) return
  form.categories = [...form.categories, value]
}

const removeCategory = (category) => {
  if (readOnly.value) return
  form.categories = form.categories.filter((item) => item.toLowerCase() !== String(category || '').trim().toLowerCase())
}

const addCustomCategory = () => {
  if (readOnly.value) return

  const value = sanitizeCategoryValue(customCategory.value)
  if (!value) return
  if (hasCategory(value)) {
    customCategory.value = ''
    return
  }

  form.categories = [...form.categories, value]
  customCategory.value = ''
}

const sanitizeCategoryValue = (value) => String(value || '').trim().replace(/\s+/g, ' ')

const hasCategory = (value) => {
  const normalized = sanitizeCategoryValue(value).toLowerCase()
  if (!normalized) return false
  return form.categories.some((item) => sanitizeCategoryValue(item).toLowerCase() === normalized)
}

const normalizeSelectedCategories = (input) => {
  if (!Array.isArray(input)) return []

  const cleaned = []
  input.forEach((category) => {
    const value = sanitizeCategoryValue(category)
    if (!value) return
    if (cleaned.some((item) => item.toLowerCase() === value.toLowerCase())) return
    cleaned.push(value)
  })

  return cleaned
}

const resetForm = () => {
  form.item_name = ''
  form.categories = []
  form.is_active = true
  form.note = ''
  customCategory.value = ''
  activeId.value = null
  readOnly.value = false
  mapErrors(null)
}

const openCreate = async () => {
  resetForm()
  await router.replace({ path: '/profiles/items' })
}

const syncReadOnlyFromRoute = () => {
  if (!activeId.value) {
    readOnly.value = false
    return
  }

  const mode = String(route.query?.mode || '').trim().toLowerCase()
  readOnly.value = mode !== 'edit'
}

const loadItem = async (id) => {
  if (!id) return

  try {
    const { data } = await client.get(`/delivery-items/${id}`)
    const item = data?.data
    if (!item) return

    activeId.value = item.id
    form.item_name = item.item_name || ''
    form.categories = normalizeSelectedCategories(item.categories)
    form.is_active = !!item.is_active
    form.note = item.note || ''
    customCategory.value = ''
    syncReadOnlyFromRoute()
    mapErrors(null)
  } catch {
    setFlash('Failed to load item details.', 'danger', 2500)
  }
}

const validateForm = () => {
  mapErrors(null)

  if (!String(form.item_name || '').trim()) {
    errors.item_name = 'Item name is required.'
  }

  if (!Array.isArray(form.categories) || !form.categories.length) {
    errors.categories = 'At least one category is required.'
  }

  if (String(form.note || '').length > MAX_NOTE_LENGTH) {
    errors.note = `Note cannot be more than ${MAX_NOTE_LENGTH} characters.`
  }

  return Object.keys(errors).length === 0
}

const saveItem = async () => {
  enforceNoteLimit()
  if (!validateForm()) return

  saving.value = true
  mapErrors(null)

  try {
    const payload = {
      item_name: String(form.item_name || '').trim(),
      categories: normalizeSelectedCategories(form.categories),
      is_active: !!form.is_active,
      note: String(form.note || '').trim() || null,
    }

    if (activeId.value) {
      await client.put(`/delivery-items/${activeId.value}`, payload)
      setFlash('Item updated successfully.', 'success', 2200)
    } else {
      const { data } = await client.post('/delivery-items', payload)
      activeId.value = data?.data?.id || null
      setFlash('Item created successfully.', 'success', 2200)
    }

    readOnly.value = true
    await router.replace({ path: '/profiles/items', query: { id: activeId.value, mode: 'view' } })
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    } else {
      setFlash('Failed to save item.', 'danger', 2500)
    }
  } finally {
    saving.value = false
  }
}

const startEdit = async () => {
  if (!activeId.value) return
  readOnly.value = false
  await router.replace({ path: '/profiles/items', query: { id: activeId.value, mode: 'edit' } })
}

const deleteCurrent = async () => {
  if (!activeId.value) return
  if (!confirm('Delete this item?')) return

  try {
    await client.delete(`/delivery-items/${activeId.value}`)
    setFlash('Item deleted successfully.', 'success', 2200)
    await openCreate()
  } catch {
    setFlash('Failed to delete item.', 'danger', 2500)
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
      await loadItem(id)
      return
    }
    resetForm()
  },
  { immediate: true }
)
</script>

<style scoped>
.pm-item-shell {
  display: grid;
  gap: 14px;
}

.pm-item-form-card {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-item-category-picker {
  border: 1px solid #d7e4ff;
  border-radius: 12px;
  background: #fdfefe;
  padding: 10px;
  display: grid;
  gap: 10px;
}

.pm-item-selected-row,
.pm-item-options-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.pm-item-custom-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
}

.pm-item-custom-row .form-control {
  height: 36px;
}

.pm-item-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.78rem;
  font-weight: 700;
}

.pm-item-chip.is-food {
  background: #ffedd5;
  color: #c2410c;
}

.pm-item-chip.is-grocery {
  background: #dcfce7;
  color: #15803d;
}

.pm-item-chip.is-appliance {
  background: #f3e8ff;
  color: #9333ea;
}

.pm-item-chip.is-furniture {
  background: #f5f3ff;
  color: #6d28d9;
}

.pm-item-chip.is-medicine {
  background: #fee2e2;
  color: #dc2626;
}

.pm-item-chip.is-letters {
  background: #dbeafe;
  color: #1d4ed8;
}

.pm-item-chip.is-parcels {
  background: #e0e7ff;
  color: #4f46e5;
}

.pm-item-chip.is-default {
  background: #e2e8f0;
  color: #334155;
}

.pm-item-chip-remove {
  border: 0;
  background: transparent;
  color: inherit;
  font-size: 0.85rem;
  line-height: 1;
  padding: 0;
}

.pm-item-empty-chip {
  color: #64748b;
  font-size: 0.82rem;
  font-weight: 600;
}

.pm-item-option-btn {
  border: 1px solid #d7e4ff;
  background: #ffffff;
  color: #475569;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 4px 10px;
}

.pm-item-option-btn:hover {
  border-color: #93c5fd;
  color: #1d4ed8;
  background: #eff6ff;
}

.pm-item-status-toggle {
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

.pm-item-actions .btn {
  min-width: 110px;
}

.pm-form-error {
  margin-top: 6px;
  color: #dc2626;
  font-size: 0.82rem;
  font-weight: 600;
}

@media (max-width: 992px) {
  .pm-item-actions {
    flex-wrap: wrap;
  }

  .pm-item-actions .btn {
    width: 100%;
  }
}
</style>
