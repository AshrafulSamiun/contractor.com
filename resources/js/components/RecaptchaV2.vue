<template>
  <div class="pm-recaptcha-v2">
    <div :id="widgetId"></div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'

const emit = defineEmits(['verified', 'expired'])

const widgetId = `recaptcha-${Math.random().toString(36).slice(2, 9)}`
const widgetRef = ref(null)

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY || ''

const renderWidget = () => {
  if (!siteKey || !window.grecaptcha || !window.grecaptcha.render) return
  widgetRef.value = window.grecaptcha.render(widgetId, {
    sitekey: siteKey,
    callback: (token) => emit('verified', token),
    'expired-callback': () => emit('expired'),
  })
}

const loadScript = () =>
  new Promise((resolve) => {
    if (window.grecaptcha) return resolve()
    const existing = document.querySelector('script[data-recaptcha]')
    if (existing) {
      existing.addEventListener('load', resolve)
      return
    }
    const script = document.createElement('script')
    script.src = 'https://www.google.com/recaptcha/api.js?render=explicit'
    script.async = true
    script.defer = true
    script.setAttribute('data-recaptcha', 'true')
    script.onload = resolve
    document.head.appendChild(script)
  })

onMounted(async () => {
  await loadScript()
  if (window.grecaptcha?.ready) {
    window.grecaptcha.ready(() => renderWidget())
  } else {
    renderWidget()
  }
})

onBeforeUnmount(() => {
  if (window.grecaptcha && widgetRef.value !== null) {
    window.grecaptcha.reset(widgetRef.value)
  }
})
</script>
