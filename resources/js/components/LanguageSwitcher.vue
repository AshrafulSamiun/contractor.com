<template>
  <div class="pm-language-switcher" :class="wrapperClasses" data-no-auto-translate="true">
    <label v-if="showLabel" class="pm-language-switcher-label">{{ t('common.language') }}</label>
    <div class="pm-language-switcher-control">
      <span class="pm-language-switcher-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none">
          <path d="M12 3a9 9 0 1 0 9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          <path d="M3 12h18M12 3c2.8 2.8 2.8 15.2 0 18M12 3c-2.8 2.8-2.8 15.2 0 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
      </span>
      <select
        class="pm-language-select"
        :value="currentLocale"
        :disabled="switching"
        @change="onLocaleChange"
      >
        <option v-for="option in localeOptions" :key="option.code" :value="option.code">
          {{ option.label }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useLocale } from '../composables/useLocale'

const props = defineProps({
  compact: { type: Boolean, default: false },
  sidebar: { type: Boolean, default: false },
  showLabel: { type: Boolean, default: true },
})

const { t, currentLocale, localeOptions, changeLocale } = useLocale()
const switching = ref(false)

const wrapperClasses = computed(() => ({
  'pm-language-switcher-compact': props.compact,
  'pm-language-switcher-sidebar': props.sidebar,
}))

const onLocaleChange = async (event) => {
  const nextLocale = event?.target?.value
  if (!nextLocale || nextLocale === currentLocale.value) return

  switching.value = true
  try {
    await changeLocale(nextLocale)
  } finally {
    switching.value = false
  }
}
</script>
