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

      <div class="container pm-ops-page pm-theme-page">
        <div class="pm-page-head">
          <div>
            <h2>Settings</h2>
            <div class="pm-page-subtitle">Manage system preferences and configurations</div>
          </div>
        </div>

        <div class="pm-settings-shell pm-theme-shell">
          <div class="pm-theme-nav">
            <SystemSettingsMenu />
          </div>
          <div class="pm-settings-shell-content">
            <section class="pm-card pm-ops-card p-4 pm-theme-settings-card">
              <div class="pm-theme-settings-head">
                <div class="pm-theme-settings-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 3a9 9 0 1 0 9 9c0-.55-.45-1-1-1h-2.5A2.5 2.5 0 0 1 15 8.5V4a1 1 0 0 0-1-1h-2z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="7.5" cy="9.5" r="1" fill="currentColor"/>
                    <circle cx="10.5" cy="13.5" r="1" fill="currentColor"/>
                  </svg>
                </div>
                <div>
                  <h5 class="pm-form-title mb-0">Theme Settings</h5>
                  <p class="pm-theme-settings-sub mb-0">Customize the appearance of your application</p>
                </div>
              </div>

              <div class="pm-theme-settings-body">
                <div class="pm-theme-field">
                  <label class="pm-field-label">Appearance Mode</label>
                  <div class="pm-theme-mode-grid">
                    <button
                      v-for="mode in modeOptions"
                      :key="mode.value"
                      class="pm-theme-mode-btn"
                      :class="[mode.value, { active: form.theme_mode === mode.value }]"
                      type="button"
                      :disabled="!canEditSettings || saving"
                      @click="setThemeMode(mode.value)"
                    >
                      <span class="pm-theme-mode-icon" aria-hidden="true">
                        <svg v-if="mode.value === 'light'" viewBox="0 0 24 24" fill="none">
                          <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
                          <path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.64 5.64l2.12 2.12M16.24 16.24l2.12 2.12M5.64 18.36l2.12-2.12M16.24 7.76l2.12-2.12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <svg v-else-if="mode.value === 'dark'" viewBox="0 0 24 24" fill="none">
                          <path d="M19 14.5A7.5 7.5 0 1 1 9.5 5a6 6 0 0 0 9.5 9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none">
                          <rect x="4.5" y="5.5" width="15" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/>
                          <path d="M9 19h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                      </span>
                      <span class="pm-theme-mode-label">{{ mode.label }}</span>
                    </button>
                  </div>
                  <div v-if="errors.theme_mode" class="pm-form-error">{{ errors.theme_mode }}</div>
                </div>

                <div class="pm-theme-field">
                  <label class="pm-field-label">Primary Brand Color</label>
                  <div class="pm-theme-color-mode">
                    <button
                      class="pm-theme-color-mode-btn"
                      :class="{ active: brandColorMode === 'default' }"
                      type="button"
                      :disabled="!canEditSettings || saving"
                      @click="setBrandColorMode('default')"
                    >
                      Use Current Default
                    </button>
                    <button
                      class="pm-theme-color-mode-btn"
                      :class="{ active: brandColorMode === 'custom' }"
                      type="button"
                      :disabled="!canEditSettings || saving"
                      @click="setBrandColorMode('custom')"
                    >
                      Custom Color
                    </button>
                  </div>
                  <div class="pm-theme-color-row">
                    <button
                      class="pm-theme-color-swatch pm-theme-color-swatch-btn"
                      type="button"
                      :style="{ backgroundColor: effectiveBrandColor }"
                      :disabled="!canEditSettings || saving || !isCustomColorMode"
                      @click="openColorPicker"
                    ></button>
                    <input
                      ref="colorPickerRef"
                      class="pm-theme-color-picker"
                      type="color"
                      :value="effectiveBrandColor"
                      :disabled="!canEditSettings || saving || !isCustomColorMode"
                      @input="handleColorPickerInput"
                      @change="handleColorPickerInput"
                    />
                    <input
                      v-model="brandColorInput"
                      class="form-control pm-theme-color-input"
                      :disabled="!canEditSettings || saving || !isCustomColorMode"
                      @blur="applyBrandColor"
                    />
                  </div>
                  <div class="pm-theme-hint">Current brand color</div>
                  <div v-if="errors.theme_accent" class="pm-form-error">{{ errors.theme_accent }}</div>
                </div>

                <div class="pm-theme-field">
                  <label class="pm-field-label">Layout Density</label>
                  <div class="pm-theme-density-field">
                    <span class="pm-theme-density-icon" aria-hidden="true">
                      <svg viewBox="0 0 24 24" fill="none">
                        <rect x="3.5" y="5.5" width="17" height="11" rx="2" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M9 19h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                      </svg>
                    </span>
                    <select
                      v-model="layoutDensity"
                      class="form-control pm-theme-density-select"
                      :disabled="saving || !canEditSettings"
                    >
                      <option
                        v-for="density in layoutDensityOptions"
                        :key="density.value"
                        :value="density.value"
                      >
                        {{ density.label }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="pm-theme-actions">
                  <button
                    class="btn btn-primary pm-theme-apply-btn"
                    type="button"
                    :disabled="saving || !canEditSettings"
                    @click="saveSettings"
                  >
                    {{ saving ? 'Applying...' : 'Apply Theme' }}
                  </button>
                </div>
                <p v-if="!canEditSettings" class="pm-muted mb-0">You have read-only access to settings.</p>
              </div>
            </section>
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
const brandColorInput = ref('#2D63E5')
const layoutDensity = ref(localStorage.getItem('pm_layout_density') || 'comfortable')
const colorPickerRef = ref(null)
const customBrandColor = ref(localStorage.getItem('pm_theme_custom_brand_color') || '')
const brandColorMode = ref(localStorage.getItem('pm_theme_color_mode') || 'default')
if (!['default', 'custom'].includes(brandColorMode.value)) {
  brandColorMode.value = 'default'
}

const form = reactive({
  theme_mode: 'light',
  theme_accent: 'blue',
})

const canEditSettings = computed(() => hasUserPermission(authState.user, 'settings', 'edit'))
const modeOptions = Object.freeze([
  { value: 'light', label: 'Light' },
  { value: 'dark', label: 'Dark' },
  { value: 'system', label: 'System' },
])
const accentPalette = Object.freeze([
  { key: 'blue', hex: '#2D63E5' },
  { key: 'teal', hex: '#0F766E' },
  { key: 'indigo', hex: '#4338CA' },
  { key: 'emerald', hex: '#059669' },
  { key: 'orange', hex: '#EA580C' },
])
const layoutDensityOptions = Object.freeze([
  { value: 'comfortable', label: 'Comfortable' },
  { value: 'compact', label: 'Compact' },
  { value: 'spacious', label: 'Spacious' },
])
const DEFAULT_ACCENT_KEY = 'blue'
const selectedAccent = computed(() => (
  accentPalette.find((item) => item.key === form.theme_accent) || accentPalette[0]
))
const defaultAccent = computed(() => (
  accentPalette.find((item) => item.key === DEFAULT_ACCENT_KEY) || accentPalette[0]
))
const isCustomColorMode = computed(() => brandColorMode.value === 'custom')
const effectiveBrandColor = computed(() => (
  isCustomColorMode.value && customBrandColor.value ? customBrandColor.value : defaultAccent.value.hex
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

const normalizeHex = (value) => {
  const raw = String(value || '').trim().replace(/^#/, '')
  if (!/^[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/.test(raw)) return ''
  const full = raw.length === 3 ? raw.split('').map((item) => `${item}${item}`).join('') : raw
  return `#${full.toUpperCase()}`
}

const clampChannel = (value) => Math.max(0, Math.min(255, Math.round(value)))

const shiftHex = (hex, percent) => {
  const rgb = toRgb(hex)
  if (!rgb) return hex
  const ratio = percent / 100
  const shifted = {
    r: clampChannel(rgb.r + (ratio >= 0 ? (255 - rgb.r) * ratio : rgb.r * ratio)),
    g: clampChannel(rgb.g + (ratio >= 0 ? (255 - rgb.g) * ratio : rgb.g * ratio)),
    b: clampChannel(rgb.b + (ratio >= 0 ? (255 - rgb.b) * ratio : rgb.b * ratio)),
  }
  const toHex = (channel) => channel.toString(16).padStart(2, '0').toUpperCase()
  return `#${toHex(shifted.r)}${toHex(shifted.g)}${toHex(shifted.b)}`
}

const toRgb = (hex) => {
  const normalized = normalizeHex(hex)
  if (!normalized) return null
  const raw = normalized.slice(1)
  return {
    r: parseInt(raw.slice(0, 2), 16),
    g: parseInt(raw.slice(2, 4), 16),
    b: parseInt(raw.slice(4, 6), 16),
  }
}

const toRgbString = (hex) => {
  const rgb = toRgb(hex)
  if (!rgb) return ''
  return `${rgb.r}, ${rgb.g}, ${rgb.b}`
}

const nearestAccent = (hex) => {
  const source = toRgb(hex)
  if (!source) return accentPalette[0]
  let winner = accentPalette[0]
  let minDistance = Number.POSITIVE_INFINITY

  accentPalette.forEach((accent) => {
    const target = toRgb(accent.hex)
    if (!target) return
    const distance = (
      (source.r - target.r) ** 2 +
      (source.g - target.g) ** 2 +
      (source.b - target.b) ** 2
    )
    if (distance < minDistance) {
      minDistance = distance
      winner = accent
    }
  })

  return winner
}

const syncBrandColorInput = () => {
  brandColorInput.value = effectiveBrandColor.value
}

const applyBrandColor = () => {
  if (!isCustomColorMode.value) {
    form.theme_accent = DEFAULT_ACCENT_KEY
    customBrandColor.value = ''
    brandColorInput.value = defaultAccent.value.hex
    delete errors.theme_accent
    return true
  }

  const normalized = normalizeHex(brandColorInput.value)
  if (!normalized) {
    errors.theme_accent = 'Please enter a valid HEX color.'
    return false
  }

  const matched = nearestAccent(normalized)
  form.theme_accent = matched.key
  customBrandColor.value = normalized
  localStorage.setItem('pm_theme_custom_brand_color', normalized)
  brandColorInput.value = normalized
  delete errors.theme_accent
  return true
}

const setBrandColorMode = (mode) => {
  if (!canEditSettings.value || saving.value) return
  brandColorMode.value = mode === 'custom' ? 'custom' : 'default'
  localStorage.setItem('pm_theme_color_mode', brandColorMode.value)

  if (brandColorMode.value === 'default') {
    form.theme_accent = DEFAULT_ACCENT_KEY
    customBrandColor.value = ''
    localStorage.removeItem('pm_theme_custom_brand_color')
    brandColorInput.value = defaultAccent.value.hex
    delete errors.theme_accent
    return
  }

  if (!customBrandColor.value) {
    customBrandColor.value = selectedAccent.value.hex
    localStorage.setItem('pm_theme_custom_brand_color', customBrandColor.value)
  }
  brandColorInput.value = customBrandColor.value
}

const openColorPicker = () => {
  if (!canEditSettings.value || saving.value) return
  if (!isCustomColorMode.value) {
    setBrandColorMode('custom')
  }
  const picker = colorPickerRef.value
  if (!picker) return
  if (typeof picker.showPicker === 'function') {
    picker.showPicker()
    return
  }
  picker.click()
}

const handleColorPickerInput = (event) => {
  brandColorInput.value = normalizeHex(event?.target?.value) || effectiveBrandColor.value
  applyBrandColor()
}

const applyLayoutDensity = () => {
  document.body.setAttribute('data-density', layoutDensity.value)
  localStorage.setItem('pm_layout_density', layoutDensity.value)
}

const setThemeMode = (mode) => {
  if (!canEditSettings.value || saving.value) return
  form.theme_mode = mode
}

const applyThemeToBody = () => {
  const accentKey = isCustomColorMode.value ? form.theme_accent : DEFAULT_ACCENT_KEY
  document.body.setAttribute('data-theme', form.theme_mode)
  document.body.setAttribute('data-accent', accentKey)
  const normalized = isCustomColorMode.value ? normalizeHex(customBrandColor.value) : ''
  if (normalized) {
    const primaryStrong = shiftHex(normalized, -18)
    const primarySoft = shiftHex(normalized, 16)
    const primarySoft2 = shiftHex(normalized, 30)
    const primaryMid = shiftHex(normalized, -8)
    const primaryMidLight = shiftHex(normalized, 10)
    const primaryMidDeep = shiftHex(normalized, -14)
    const primaryDeep = shiftHex(normalized, -24)
    const primaryDeeper = shiftHex(normalized, -34)
    const accent = shiftHex(normalized, 20)
    const accentSoft = shiftHex(normalized, 36)
    const accentStrong = shiftHex(normalized, 6)

    document.body.style.setProperty('--pm-primary', normalized)
    document.body.style.setProperty('--pm-primary-strong', primaryStrong)
    document.body.style.setProperty('--pm-primary-soft', primarySoft)
    document.body.style.setProperty('--pm-primary-soft-2', primarySoft2)
    document.body.style.setProperty('--pm-primary-mid', primaryMid)
    document.body.style.setProperty('--pm-primary-mid-light', primaryMidLight)
    document.body.style.setProperty('--pm-primary-mid-deep', primaryMidDeep)
    document.body.style.setProperty('--pm-primary-deep', primaryDeep)
    document.body.style.setProperty('--pm-primary-deeper', primaryDeeper)
    document.body.style.setProperty('--pm-primary-rgb', toRgbString(normalized))
    document.body.style.setProperty('--pm-accent', accent)
    document.body.style.setProperty('--pm-accent-soft', accentSoft)
    document.body.style.setProperty('--pm-accent-strong', accentStrong)
  } else {
    document.body.style.removeProperty('--pm-primary')
    document.body.style.removeProperty('--pm-primary-strong')
    document.body.style.removeProperty('--pm-primary-soft')
    document.body.style.removeProperty('--pm-primary-soft-2')
    document.body.style.removeProperty('--pm-primary-mid')
    document.body.style.removeProperty('--pm-primary-mid-light')
    document.body.style.removeProperty('--pm-primary-mid-deep')
    document.body.style.removeProperty('--pm-primary-deep')
    document.body.style.removeProperty('--pm-primary-deeper')
    document.body.style.removeProperty('--pm-primary-rgb')
    document.body.style.removeProperty('--pm-accent')
    document.body.style.removeProperty('--pm-accent-soft')
    document.body.style.removeProperty('--pm-accent-strong')
  }
}

const loadSettings = async () => {
  try {
    const { data } = await client.get('/settings/theme')
    const item = data?.data
    if (!item) return
    form.theme_mode = item.theme_mode || 'light'
    form.theme_accent = item.theme_accent || DEFAULT_ACCENT_KEY
    if (!isCustomColorMode.value) {
      form.theme_accent = DEFAULT_ACCENT_KEY
    }
    savedState.value = { ...form }
    syncBrandColorInput()
    applyThemeToBody()
  } catch {
    // handled by toast
  }
}

const saveSettings = async () => {
  if (!canEditSettings.value) return

  if (!applyBrandColor()) return

  saving.value = true
  mapErrors(null)
  try {
    const payload = { ...form }
    const { data } = await client.put('/settings/theme', payload)
    savedState.value = { ...(data?.data || payload) }
    syncBrandColorInput()
    applyThemeToBody()
    applyLayoutDensity()
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    }
  } finally {
    saving.value = false
  }
}

watch(
  () => [form.theme_mode, form.theme_accent],
  () => {
    applyThemeToBody()
  }
)
watch(
  () => customBrandColor.value,
  () => {
    applyThemeToBody()
  }
)
watch(
  () => brandColorMode.value,
  () => {
    localStorage.setItem('pm_theme_color_mode', brandColorMode.value)
    if (brandColorMode.value === 'default') {
      localStorage.removeItem('pm_theme_custom_brand_color')
      customBrandColor.value = ''
      form.theme_accent = DEFAULT_ACCENT_KEY
    }
    syncBrandColorInput()
    applyThemeToBody()
  }
)
watch(
  () => layoutDensity.value,
  () => {
    applyLayoutDensity()
  }
)

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  syncBrandColorInput()
  applyLayoutDensity()
  loadSettings()
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-theme-page {
  max-width: 1320px;
}

.pm-theme-shell {
  grid-template-columns: minmax(238px, 252px) minmax(0, 1fr);
  gap: 20px;
}

.pm-theme-nav {
  min-width: 0;
}

.pm-theme-nav :deep(.pm-settings-menu-card) {
  border: 1px solid #dbe4f2;
  border-radius: 14px;
  box-shadow: none;
  background: #ffffff;
  padding: 8px;
}

.pm-theme-nav :deep(.pm-settings-menu-link) {
  min-height: 44px;
  padding: 10px 12px;
  border-radius: 10px;
  color: #334155;
}

.pm-theme-nav :deep(.pm-settings-menu-link:hover) {
  background: #f3f7ff;
  border-color: #dbeafe;
  color: #1e3a8a;
}

.pm-theme-nav :deep(.pm-settings-menu-link.active) {
  border-color: #dbeafe;
  background: #e8f0fb;
  color: #0f172a;
  box-shadow: none;
}

.pm-theme-nav :deep(.pm-settings-menu-icon) {
  width: 24px;
  height: 24px;
  border-radius: 8px;
}

.pm-theme-settings-card {
  border: 1px solid #d9e3f0;
  border-radius: 14px;
  box-shadow: none;
  padding: 28px 30px 30px !important;
}

.pm-theme-settings-head {
  margin-bottom: 20px;
}

.pm-theme-settings-sub {
  margin-top: 4px;
}

.pm-theme-mode-grid {
  max-width: 760px;
  grid-template-columns: repeat(3, minmax(170px, 1fr));
  gap: 14px;
}

.pm-theme-mode-btn {
  min-height: 126px;
  border-radius: 12px;
  border: 1px solid #cdd7e5;
}

.pm-theme-mode-btn.active {
  border-width: 2px;
  background: #edf3ff;
  box-shadow: none;
}

.pm-theme-mode-icon {
  width: 34px;
  height: 34px;
}

.pm-theme-mode-icon svg {
  width: 26px;
  height: 26px;
}

.pm-theme-color-row {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 14px;
}

.pm-theme-color-mode {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.pm-theme-color-mode-btn {
  min-height: 34px;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #334155;
  font-size: 0.82rem;
  font-weight: 700;
}

.pm-theme-color-mode-btn.active {
  border-color: #2563eb;
  background: #eff6ff;
  color: #1d4ed8;
}

.pm-theme-color-mode-btn:disabled {
  opacity: 0.65;
}

.pm-theme-color-swatch {
  width: 64px;
  height: 64px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  box-shadow: inset 0 1px 1px rgba(15, 23, 42, 0.06);
}

.pm-theme-color-swatch-btn {
  padding: 0;
  cursor: pointer;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
}

.pm-theme-color-swatch-btn:hover {
  transform: translateY(-1px);
  box-shadow: inset 0 1px 1px rgba(15, 23, 42, 0.06), 0 6px 14px rgba(15, 23, 42, 0.12);
}

.pm-theme-color-swatch-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
  box-shadow: inset 0 1px 1px rgba(15, 23, 42, 0.06);
}

.pm-theme-color-picker {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  pointer-events: none;
}

.pm-theme-color-input {
  width: 130px;
  height: 42px;
}

.pm-theme-density-field,
.pm-theme-density-select {
  max-width: 660px;
}

.pm-theme-density-select {
  height: 42px;
  border-radius: 12px;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: none;
}

.pm-theme-actions {
  padding-top: 2px;
}

.pm-theme-apply-btn {
  min-width: 144px;
  height: 42px;
  border-radius: 12px;
  box-shadow: 0 8px 16px rgba(29, 78, 216, 0.2);
}

@media (max-width: 992px) {
  .pm-theme-shell {
    grid-template-columns: 1fr;
    gap: 14px;
  }

  .pm-theme-settings-card {
    padding: 18px 16px 20px !important;
  }

  .pm-theme-mode-grid {
    grid-template-columns: 1fr;
    max-width: none;
  }

  .pm-theme-color-input {
    width: 100%;
  }
}
</style>
