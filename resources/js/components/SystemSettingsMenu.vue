<template>
  <nav class="pm-settings-menu-card" aria-label="System settings menu">
    <RouterLink
      v-for="item in visibleItems"
      :key="item.key"
      class="pm-settings-menu-link"
      :class="{ active: isActive(item.to) }"
      :to="item.to"
    >
      <span class="pm-settings-menu-icon" aria-hidden="true">
        <svg v-if="item.key === 'date-time'" viewBox="0 0 24 24" fill="none">
          <path d="M7 3v3M17 3v3M4 9h16M6 6h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        <svg v-else-if="item.key === 'theme'" viewBox="0 0 24 24" fill="none">
          <path d="M12 3a9 9 0 1 0 9 9c0-.5-.4-.9-.9-.9H17a3 3 0 0 1-3-3V4a1 1 0 0 0-1-1z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else-if="item.key === 'holding'" viewBox="0 0 24 24" fill="none">
          <path d="M5 6h14v12H5zM5 10h14M9 14h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        <svg v-else-if="item.key === 'notifications'" viewBox="0 0 24 24" fill="none">
          <path d="M6 17h12l-1.5-2v-4a4.5 4.5 0 1 0-9 0v4L6 17zm4 2a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else-if="item.key === 'language'" viewBox="0 0 24 24" fill="none">
          <path d="M12 3a9 9 0 1 0 9 9M3 12h18M12 3c2.8 2.8 2.8 15.2 0 18M12 3c-2.8 2.8-2.8 15.2 0 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        <svg v-else viewBox="0 0 24 24" fill="none">
          <path d="M9 3h6v4H9zM5 10h14v11H5zM12 10v11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
      </span>
      <span>{{ t(item.labelKey) }}</span>
    </RouterLink>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { authState } from '../store/auth'
import { hasUserPermission } from '../config/permissions'
import { hasPlanFeature } from '../config/planFeatures'

const route = useRoute()
const { t } = useI18n()

const canReadSettings = computed(() => hasUserPermission(authState.user, 'settings', 'read'))
const canNotificationsSettings = computed(() =>
  hasPlanFeature(authState.user?.selected_plan, 'notification_center') &&
  hasUserPermission(authState.user, 'settings', 'read')
)
const canApprovalSettings = computed(() =>
  hasPlanFeature(authState.user?.selected_plan, 'workforce_management') &&
  hasUserPermission(authState.user, 'settings', 'read')
)

const items = computed(() => {
  const result = []
  if (canReadSettings.value) {
    result.push(
      { key: 'date-time', labelKey: 'settingsMenu.dateTime', to: '/settings/date-time' },
      { key: 'theme', labelKey: 'settingsMenu.theme', to: '/settings/theme' },
      { key: 'language', labelKey: 'settingsMenu.language', to: '/settings/language' },
      { key: 'holding', labelKey: 'settingsMenu.holding', to: '/settings/holding-limits' }
    )
  }
  if (canNotificationsSettings.value) {
    result.push({ key: 'notifications', labelKey: 'settingsMenu.notifications', to: '/settings/notifications' })
  }
  if (canApprovalSettings.value) {
    result.push({ key: 'approvals', labelKey: 'settingsMenu.approvals', to: '/settings/approvals' })
  }
  return result
})

const visibleItems = computed(() => items.value)

const isActive = (to) => {
  return route.path === to
}
</script>
