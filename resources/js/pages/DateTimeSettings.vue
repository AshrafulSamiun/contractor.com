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
            <h2>Date &amp; Time</h2>
            <div class="pm-page-subtitle">Dashboard &gt; System Settings &gt; Date &amp; Time</div>
          </div>
        </div>

        <div class="pm-settings-shell">
          <SystemSettingsMenu />
          <div class="pm-settings-shell-content">
            <div class="pm-form-grid">
              <section class="pm-card pm-ops-card p-4">
                <h5 class="pm-form-title">Date &amp; Time Preferences</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="pm-field-label">Timezone *</label>
                    <select class="form-control" v-model="form.timezone" :disabled="!canEditSettings || saving">
                      <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                    </select>
                    <div v-if="errors.timezone" class="pm-form-error">{{ errors.timezone }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="pm-field-label">Week Start *</label>
                    <select class="form-control" v-model="form.week_start" :disabled="!canEditSettings || saving">
                      <option value="Monday">Monday</option>
                      <option value="Sunday">Sunday</option>
                    </select>
                    <div v-if="errors.week_start" class="pm-form-error">{{ errors.week_start }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="pm-field-label">Date Format *</label>
                    <select class="form-control" v-model="form.date_format" :disabled="!canEditSettings || saving">
                      <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                      <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                      <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                    </select>
                    <div v-if="errors.date_format" class="pm-form-error">{{ errors.date_format }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="pm-field-label">Time Format *</label>
                    <select class="form-control" v-model="form.time_format" :disabled="!canEditSettings || saving">
                      <option value="12h">12-hour (AM/PM)</option>
                      <option value="24h">24-hour</option>
                    </select>
                    <div v-if="errors.time_format" class="pm-form-error">{{ errors.time_format }}</div>
                  </div>
                  <div class="col-md-12">
                    <div class="pm-dash-card pm-muted-card">
                      <div class="pm-muted">Preview</div>
                      <strong>{{ preview }}</strong>
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
import { RouterLink, useRouter } from 'vue-router'
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

const timezones = Intl.supportedValuesOf ? Intl.supportedValuesOf('timeZone') : [
  'UTC',
  'America/New_York',
  'America/Chicago',
  'America/Denver',
  'America/Los_Angeles',
  'Europe/London',
  'Europe/Paris',
  'Asia/Dubai',
  'Asia/Dhaka',
  'Asia/Kolkata',
  'Asia/Singapore',
  'Australia/Sydney',
]

const form = reactive({
  timezone: 'UTC',
  date_format: 'MM/DD/YYYY',
  time_format: '12h',
  week_start: 'Monday',
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
    const { data } = await client.get('/settings/date-time')
    const item = data?.data
    if (!item) return
    form.timezone = item.timezone || 'UTC'
    form.date_format = item.date_format || 'MM/DD/YYYY'
    form.time_format = item.time_format || '12h'
    form.week_start = item.week_start || 'Monday'
    savedState.value = { ...form }
  } catch {
    // handled by toast
  }
}

const resetToSaved = () => {
  if (!savedState.value) return
  form.timezone = savedState.value.timezone
  form.date_format = savedState.value.date_format
  form.time_format = savedState.value.time_format
  form.week_start = savedState.value.week_start
}

const saveSettings = async () => {
  if (!canEditSettings.value) return

  saving.value = true
  mapErrors(null)
  try {
    const payload = { ...form }
    const { data } = await client.put('/settings/date-time', payload)
    savedState.value = { ...(data?.data || payload) }
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    }
  } finally {
    saving.value = false
  }
}

const preview = computed(() => {
  const now = new Date()
  const options = {
    timeZone: form.timezone,
    year: 'numeric',
    month: form.date_format === 'YYYY-MM-DD' ? '2-digit' : '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: form.time_format === '12h',
  }
  const formatter = new Intl.DateTimeFormat('en-US', options)
  const formatted = formatter.format(now)
  if (form.date_format === 'DD/MM/YYYY') {
    const parts = formatted.split(',')[0].split('/')
    if (parts.length === 3) {
      return `${parts[1]}/${parts[0]}/${parts[2]} ${formatted.split(',')[1]?.trim() || ''}`.trim()
    }
  }
  if (form.date_format === 'YYYY-MM-DD') {
    const parts = formatted.split(',')[0].split('/')
    if (parts.length === 3) {
      return `${parts[2]}-${parts[0]}-${parts[1]} ${formatted.split(',')[1]?.trim() || ''}`.trim()
    }
  }
  return formatted
})

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
