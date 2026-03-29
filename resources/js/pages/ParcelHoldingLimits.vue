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

      <div class="container pm-ops-page">
        <div class="pm-page-head">
          <div>
            <h2>Parcel Holding Limits</h2>
            <div class="pm-page-subtitle">Dashboard &gt; System Settings &gt; Parcel Holding Limits</div>
          </div>
        </div>

        <div class="pm-settings-shell">
          <SystemSettingsMenu />
          <div class="pm-settings-shell-content">
            <div class="pm-form-grid">
              <section class="pm-card pm-ops-card p-4">
                <h5 class="pm-form-title">Holding Rules</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="pm-field-label">Default holding days *</label>
                    <input v-model.number="form.holding_default_days" type="number" min="1" max="365" class="form-control" :disabled="!canEditSettings || saving" />
                    <div v-if="errors.holding_default_days" class="pm-form-error">{{ errors.holding_default_days }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="pm-field-label">Max holding days *</label>
                    <input v-model.number="form.holding_max_days" type="number" min="1" max="365" class="form-control" :disabled="!canEditSettings || saving" />
                    <div v-if="errors.holding_max_days" class="pm-form-error">{{ errors.holding_max_days }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="pm-field-label">Reminder days before expiry *</label>
                    <input v-model.number="form.holding_reminder_days" type="number" min="0" max="90" class="form-control" :disabled="!canEditSettings || saving" />
                    <div v-if="errors.holding_reminder_days" class="pm-form-error">{{ errors.holding_reminder_days }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="pm-field-label">Auto-expire parcels *</label>
                    <select class="form-control" v-model="form.holding_auto_expire" :disabled="!canEditSettings || saving">
                      <option :value="true">Yes</option>
                      <option :value="false">No</option>
                    </select>
                    <div class="pm-muted small mt-2">
                      When enabled, parcels will be marked expired once they pass the max holding days.
                    </div>
                  </div>
                </div>
              </section>

              <section class="pm-card pm-ops-card p-4">
                <div class="pm-form-actions pm-form-actions-right">
                  <button class="btn btn-outline-secondary" type="button" :disabled="saving || !canEditSettings" @click="resetToSaved">
                    Reset
                  </button>
                  <button class="btn btn-primary" type="button" :disabled="saving || !canEditSettings" @click="saveSettings">
                    {{ saving ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>
                <p v-if="!canEditSettings" class="pm-muted mt-3 mb-0">You have read-only access to settings.</p>
              </section>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import SystemSettingsMenu from '../components/SystemSettingsMenu.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { hasUserPermission } from '../config/permissions'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const saving = ref(false)
const errors = reactive({})
const savedState = ref(null)

const form = reactive({
  holding_default_days: 7,
  holding_max_days: 30,
  holding_reminder_days: 2,
  holding_auto_expire: true,
})

const canEditSettings = computed(() => hasUserPermission(authState.user, 'settings', 'edit'))

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

const loadSettings = async () => {
  try {
    const { data } = await client.get('/settings/holding-limits')
    const item = data?.data
    if (!item) return
    form.holding_default_days = item.holding_default_days ?? 7
    form.holding_max_days = item.holding_max_days ?? 30
    form.holding_reminder_days = item.holding_reminder_days ?? 2
    form.holding_auto_expire = item.holding_auto_expire ?? true
    savedState.value = { ...form }
  } catch {
    // handled by toast
  }
}

const resetToSaved = () => {
  if (!savedState.value) return
  form.holding_default_days = savedState.value.holding_default_days
  form.holding_max_days = savedState.value.holding_max_days
  form.holding_reminder_days = savedState.value.holding_reminder_days
  form.holding_auto_expire = savedState.value.holding_auto_expire
}

const saveSettings = async () => {
  if (!canEditSettings.value) return

  saving.value = true
  mapErrors(null)
  try {
    const payload = { ...form }
    const { data } = await client.put('/settings/holding-limits', payload)
    savedState.value = { ...(data?.data || payload) }
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadSettings()
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>
