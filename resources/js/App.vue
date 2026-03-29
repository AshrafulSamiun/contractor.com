<template>
  <div
    v-if="flashState.message"
    class="pm-flash"
    :class="`pm-flash-${flashState.type}`"
    :style="{
      '--pm-flash-duration': `${flashState.remaining || flashState.duration}ms`,
      '--pm-flash-delay': `${Math.max((flashState.remaining || flashState.duration) - 300, 0)}ms`,
      '--pm-flash-width': '380px',
    }"
    @mouseenter="pauseFlash"
    @mouseleave="resumeFlash"
  >
    <div class="pm-flash-inner">
      <span class="pm-flash-icon" aria-hidden="true">
        <svg v-if="flashState.type === 'success'" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
          <path d="M8 12.5l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else-if="flashState.type === 'warning'" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
          <path d="M12 7v6M12 17h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        <svg v-else viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
          <path d="M12 8h.01M11 12h1v4h1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </span>
      <span class="pm-flash-text">{{ flashState.message }}</span>
      <button class="pm-flash-close" type="button" @click="clearFlash">&times;</button>
    </div>
    <div class="pm-flash-progress" :class="{ paused: flashState.paused }"></div>
  </div>
  <SalesChatWidget v-if="showChat" />
  <div data-translate-scope="page">
    <RouterView />
  </div>
</template>

<script setup>
import { flashState, clearFlash, pauseFlash, resumeFlash, setFlash } from './store/flash'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import SalesChatWidget from './components/SalesChatWidget.vue'
import { authState } from './store/auth'
import client from './api/client'
import { hasPlanFeature } from './config/planFeatures'
import { hasUserPermission } from './config/permissions'
import { setLocale } from './i18n'

const route = useRoute()
const showChat = computed(() => {
  return !route.meta?.requiresAuth
})
const canPollNotifications = computed(() => {
  if (!authState.token || !authState.user?.account_setup_completed_at) {
    return false
  }

  return (
    hasPlanFeature(authState.user?.selected_plan, 'notification_center') &&
    hasUserPermission(authState.user, 'notifications', 'read')
  )
})
const lastNoticeId = ref(Number(localStorage.getItem('pm_last_notice') || 0))
let pollTimer = null

const THEME_CUSTOM_VAR_KEYS = [
  '--pm-primary',
  '--pm-primary-strong',
  '--pm-primary-soft',
  '--pm-primary-soft-2',
  '--pm-primary-mid',
  '--pm-primary-mid-light',
  '--pm-primary-mid-deep',
  '--pm-primary-deep',
  '--pm-primary-deeper',
  '--pm-primary-rgb',
  '--pm-accent',
  '--pm-accent-soft',
  '--pm-accent-strong',
]

const normalizeHex = (value) => {
  const raw = String(value || '').trim().replace(/^#/, '')
  if (!/^[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/.test(raw)) return ''
  const full = raw.length === 3 ? raw.split('').map((item) => `${item}${item}`).join('') : raw
  return `#${full.toUpperCase()}`
}

const clampChannel = (value) => Math.max(0, Math.min(255, Math.round(value)))

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

const toRgbString = (hex) => {
  const rgb = toRgb(hex)
  if (!rgb) return ''
  return `${rgb.r}, ${rgb.g}, ${rgb.b}`
}

const clearCustomThemeOverrides = () => {
  THEME_CUSTOM_VAR_KEYS.forEach((key) => {
    document.body.style.removeProperty(key)
  })
}

const applyCustomThemeOverrides = (hex) => {
  const normalized = normalizeHex(hex)
  if (!normalized) {
    clearCustomThemeOverrides()
    return
  }

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
}

const applyTheme = (themeMode, themeAccent) => {
  const mode = themeMode || 'system'
  const accent = themeAccent || 'blue'
  document.body.setAttribute('data-theme', mode)
  document.body.setAttribute('data-accent', accent)
  const customMode = localStorage.getItem('pm_theme_color_mode') === 'custom'
  const customColor = localStorage.getItem('pm_theme_custom_brand_color') || ''
  if (customMode) {
    applyCustomThemeOverrides(customColor)
    return
  }
  clearCustomThemeOverrides()
}

const loadTheme = async () => {
  if (!authState.token) return
  try {
    const { data } = await client.get('/settings/theme')
    const item = data?.data
    if (!item) return
    applyTheme(item.theme_mode, item.theme_accent)
  } catch {
    // ignore settings load errors
  }
}

const loadLanguage = async () => {
  if (!authState.token) return
  try {
    const { data } = await client.get('/settings/language')
    const languageCode = String(data?.data?.language_code || '').trim().toLowerCase()
    if (languageCode) {
      await setLocale(languageCode)
    }
  } catch {
    // ignore settings load errors
  }
}

onMounted(() => {
  // Default to light for consistent public landing page visuals.
  applyTheme('light', 'blue')
  loadTheme()
  loadLanguage()
  if (canPollNotifications.value) {
    startPolling()
  }
})

function startPolling() {
  if (!canPollNotifications.value) return
  if (pollTimer) return
  pollTimer = setInterval(async () => {
    if (!canPollNotifications.value) {
      stopPolling()
      return
    }

    try {
      const { data } = await client.get('/notifications')
      const items = data?.data || []
      const latest = items[0]
      if (latest && latest.id && latest.id > lastNoticeId.value) {
        lastNoticeId.value = latest.id
        localStorage.setItem('pm_last_notice', String(latest.id))
        const context = latest.context || 'Notification'
        setFlash(`New ${context} received.`, 'info', 2500)
      }
    } catch {
      // ignore polling errors
    }
  }, 30000)
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

watch(
  () => authState.token,
  () => {
    if (authState.token) {
      loadTheme()
      loadLanguage()
    }
  },
)

watch(
  () => canPollNotifications.value,
  (allowed) => {
    if (allowed) {
      startPolling()
      return
    }
    stopPolling()
  },
  { immediate: true }
)

onUnmounted(() => {
  stopPolling()
})
</script>
