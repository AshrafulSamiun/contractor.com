<template>
  <div class="pm-auth-split pm-auth-login">
    <div class="pm-auth-card row g-0">
      <div class="col-lg-5 pm-auth-left">
        <div class="pm-auth-left-panel">
          <div class="pm-auth-logo">
            <RouterLink to="/" aria-label="Go to home">
              <img :src="logoWhite" alt="ParcelPro logo" class="pm-auth-logo-img" />
            </RouterLink>
          </div>
          <h4 class="mb-3">Recover your username</h4>
          <p class="mb-4">Account access help</p>
          <p class="mb-0">Enter your registered email to receive your username.</p>
        </div>
      </div>
      <div class="col-lg-7 pm-auth-right">
        <div class="pm-auth-tabs">
          <RouterLink to="/register">Sign up</RouterLink>
          <RouterLink to="/login">Sign In</RouterLink>
          <RouterLink class="active" to="/forgot-username">Forgot Username</RouterLink>
        </div>
        <div class="pm-auth-divider"></div>

        <form @submit.prevent="onSubmit" novalidate>
          <div class="mb-3">
            <label class="pm-field-label">Registered Email</label>
            <input v-model="form.email" class="form-control" placeholder="Enter your email" />
            <div v-if="errors.email" class="text-danger small mt-1">{{ errors.email[0] }}</div>
          </div>

          <div class="mb-3">
            <ImageCaptcha ref="captchaRef" @change="onCaptchaChange" />
            <div v-if="errors.captcha_value" class="text-danger small mt-2">{{ errors.captcha_value[0] }}</div>
          </div>

          <div v-if="info" class="pm-form-success mb-3">{{ info }}</div>
          <div v-if="error" class="text-danger mb-3">{{ error }}</div>

          <button type="submit" class="btn btn-primary w-100 pm-auth-submit" :disabled="loading">
            {{ loading ? 'Sending...' : 'Send Username Details' }}
          </button>

          <div class="text-center mt-3 pm-muted">
            Back to
            <RouterLink to="/login">Sign In</RouterLink>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import logoWhite from '../assets/logo-white.png'
import { reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { normalizeApiError } from '../api/client'
import { requestUsernameReminder } from '../api/auth'
import ImageCaptcha from '../components/ImageCaptcha.vue'

const loading = ref(false)
const error = ref('')
const info = ref('')
const errors = ref({})
const captchaRef = ref(null)
const captchaKey = ref('')
const captchaValue = ref('')

const form = reactive({
  email: '',
})

const onCaptchaChange = (payload) => {
  captchaKey.value = payload.captcha_key
  captchaValue.value = payload.captcha_value
  errors.value.captcha_value = null
}

const onSubmit = async () => {
  error.value = ''
  info.value = ''
  errors.value = {}

  if (!form.email) {
    errors.value.email = ['Email is required.']
    return
  }
  if (!captchaKey.value || !captchaValue.value) {
    errors.value.captcha_value = ['Please enter the captcha.']
    return
  }

  loading.value = true
  try {
    const res = await requestUsernameReminder({
      email: form.email,
      captcha_key: captchaKey.value,
      captcha_value: captchaValue.value,
    })
    info.value = res?.message || 'If your account exists, username details have been sent.'
    captchaRef.value?.refresh?.()
  } catch (e) {
    const parsed = normalizeApiError(e)
    errors.value = parsed.errors || {}
    error.value = parsed.message || 'Unable to send username reminder.'
    if (errors.value?.captcha_value) {
      captchaRef.value?.refresh?.()
    }
  } finally {
    loading.value = false
  }
}
</script>
