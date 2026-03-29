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
          <h4 class="mb-3">Recover your password</h4>
          <p class="mb-4">Secure account recovery</p>
          <p class="mb-0">Request a reset code and set a new password.</p>
        </div>
      </div>
      <div class="col-lg-7 pm-auth-right">
        <div class="pm-auth-tabs">
          <RouterLink to="/register">Sign up</RouterLink>
          <RouterLink to="/login">Sign In</RouterLink>
          <RouterLink class="active" to="/forgot-password">Forgot Password</RouterLink>
        </div>
        <div class="pm-auth-divider"></div>

        <form @submit.prevent="onResetPassword" novalidate>
          <div class="mb-3">
            <label class="pm-field-label">Email Address</label>
            <input v-model="form.email" class="form-control" placeholder="Enter your email" />
            <div v-if="errors.email" class="text-danger small mt-1">{{ errors.email[0] }}</div>
          </div>

          <div class="mb-3">
            <ImageCaptcha ref="captchaRef" @change="onCaptchaChange" />
            <div v-if="errors.captcha_value" class="text-danger small mt-2">{{ errors.captcha_value[0] }}</div>
          </div>

          <div class="mb-3">
            <button class="btn btn-outline-primary w-100" type="button" :disabled="sendLoading" @click="onSendCode">
              {{ sendLoading ? 'Sending...' : 'Send Reset Code' }}
            </button>
          </div>

          <div class="pm-auth-divider"></div>

          <div class="mb-3">
            <label class="pm-field-label">Reset Code</label>
            <input v-model="form.code" class="form-control" maxlength="6" placeholder="Enter 6-digit code" />
            <div v-if="errors.code" class="text-danger small mt-1">{{ errors.code[0] }}</div>
          </div>

          <div class="mb-3">
            <label class="pm-field-label">New Password</label>
            <input v-model="form.password" type="password" class="form-control" placeholder="Enter new password" />
            <div v-if="errors.password" class="text-danger small mt-1">{{ errors.password[0] }}</div>
          </div>

          <div class="mb-3">
            <label class="pm-field-label">Confirm New Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="form-control"
              placeholder="Confirm new password"
            />
            <div v-if="errors.password_confirmation" class="text-danger small mt-1">{{ errors.password_confirmation[0] }}</div>
          </div>

          <div v-if="info" class="pm-form-success mb-3">{{ info }}</div>
          <div v-if="error" class="text-danger mb-3">{{ error }}</div>

          <button type="submit" class="btn btn-primary w-100 pm-auth-submit" :disabled="resetLoading">
            {{ resetLoading ? 'Updating...' : 'Reset Password' }}
          </button>

          <div class="text-center mt-3 pm-muted">
            Remembered your password?
            <RouterLink to="/login">Back to Sign In</RouterLink>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import logoWhite from '../assets/logo-white.png'
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { normalizeApiError } from '../api/client'
import { requestPasswordReset, resetPassword } from '../api/auth'
import { setFlash } from '../store/flash'
import ImageCaptcha from '../components/ImageCaptcha.vue'

const router = useRouter()
const sendLoading = ref(false)
const resetLoading = ref(false)
const error = ref('')
const info = ref('')
const errors = ref({})
const captchaRef = ref(null)
const captchaKey = ref('')
const captchaValue = ref('')

const form = reactive({
  email: '',
  code: '',
  password: '',
  password_confirmation: '',
})

const onCaptchaChange = (payload) => {
  captchaKey.value = payload.captcha_key
  captchaValue.value = payload.captcha_value
  errors.value.captcha_value = null
}

const onSendCode = async () => {
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

  sendLoading.value = true
  try {
    const res = await requestPasswordReset({
      email: form.email,
      captcha_key: captchaKey.value,
      captcha_value: captchaValue.value,
    })
    info.value = res?.message || 'If your account exists, a reset code has been sent.'
    captchaRef.value?.refresh?.()
  } catch (e) {
    const parsed = normalizeApiError(e)
    errors.value = parsed.errors || {}
    error.value = parsed.message || 'Unable to send reset code.'
    if (errors.value?.captcha_value) {
      captchaRef.value?.refresh?.()
    }
  } finally {
    sendLoading.value = false
  }
}

const onResetPassword = async () => {
  error.value = ''
  info.value = ''
  errors.value = {}

  if (!form.email) {
    errors.value.email = ['Email is required.']
    return
  }
  if (!form.code || form.code.trim().length < 6) {
    errors.value.code = ['Reset code is required.']
    return
  }
  if (!form.password) {
    errors.value.password = ['New password is required.']
    return
  }
  if (form.password.length < 8) {
    errors.value.password = ['Password must be at least 8 characters.']
    return
  }
  if (!form.password_confirmation) {
    errors.value.password_confirmation = ['Please confirm the password.']
    return
  }
  if (form.password !== form.password_confirmation) {
    errors.value.password_confirmation = ['Password confirmation does not match.']
    return
  }

  resetLoading.value = true
  try {
    const res = await resetPassword({
      email: form.email,
      code: form.code,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })
    setFlash(res?.message || 'Password reset successful. Please sign in.', 'success', 4000)
    router.push('/login')
  } catch (e) {
    const parsed = normalizeApiError(e)
    errors.value = parsed.errors || {}
    error.value = parsed.message || 'Password reset failed.'
  } finally {
    resetLoading.value = false
  }
}
</script>
