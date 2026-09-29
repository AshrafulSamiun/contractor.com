<template>
  <div class="pm-auth-split pm-auth-verify">
    <div class="pm-auth-card row g-0">
      <div class="col-lg-5 pm-auth-left">
        <div class="pm-auth-left-panel">
          <div class="pm-auth-logo">
            <RouterLink to="/" aria-label="Go to home">
              <img :src="logoWhite" alt="ParcelPro logo" class="pm-auth-logo-img" />
            </RouterLink>
          </div>
          <h4 class="mb-3">Verify your account</h4>
          <p class="mb-4">{{ verificationIntro }}</p>
          <p class="mb-0">Enter the code to finish sign in.</p>
        </div>
      </div>
      <div class="col-lg-7 pm-auth-right">
        <div class="pm-auth-tabs">
          <span class="active">Verification</span>
        </div>
        <div class="pm-auth-divider"></div>

        <div v-if="error" class="pm-form-summary">
          {{ error }}
          <div v-if="sessionExpired" class="mt-2">
            <RouterLink class="btn btn-outline-primary btn-sm" to="/login">Go to Login</RouterLink>
          </div>
        </div>
        <div v-if="success" class="pm-form-success">Verified! Redirecting...</div>

        <form @submit.prevent="onVerify" novalidate>
          <div class="mb-3">
            <label class="pm-field-label">Verification Code</label>
            <input
              v-model="code"
              class="form-control pm-otp-input"
              placeholder="Enter 6-digit code"
              maxlength="6"
              inputmode="numeric"
              autocomplete="one-time-code"
            />
            <div v-if="fieldError" class="text-danger small mt-1">{{ fieldError }}</div>
            <div class="pm-otp-expiry">Code valid for 10 minutes.</div>
          </div>
          <button type="submit" class="btn btn-primary w-100 pm-auth-submit" :disabled="loading">
            {{ loading ? 'Verifying...' : 'Verify Code' }}
          </button>
        </form>

        <div class="pm-otp-footer">
          <button
            class="btn btn-link p-0"
            type="button"
            @click="resend"
            :disabled="resendLoading || resendCooldown > 0"
          >
            {{ resendLoading ? 'Sending...' : resendLabel }}
          </button>
          <span class="pm-muted">{{ deliveryHint }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import logoWhite from '../assets/logo-white.png'
import client from '../api/client'
import { saveToken } from '../api/auth'
import { setUser } from '../store/auth'

const router = useRouter()
const code = ref('')
const loading = ref(false)
const resendLoading = ref(false)
const error = ref('')
const success = ref(false)
const fieldError = ref('')
const sessionExpired = ref(false)
const resendCooldown = ref(45)
let cooldownTimer = null

const verifySession = localStorage.getItem('pm_verify_session')
const verifyVia = ref(localStorage.getItem('pm_verify_via') || 'email')

const viaLabel = computed(() => (verifyVia.value === 'sms' ? 'phone' : 'email'))
const verificationIntro = computed(() =>
  verifyVia.value === 'sms'
    ? 'We sent a 6-digit code to your phone.'
    : 'We sent a 6-digit code and a secure verification link to your email.'
)
const deliveryHint = computed(() =>
  verifyVia.value === 'sms'
    ? "Didn't receive it? Check your SMS inbox."
    : "Didn't receive it? Check spam or resend."
)
const resendLabel = computed(() =>
  resendCooldown.value > 0
    ? `Resend in ${resendCooldown.value}s`
    : verifyVia.value === 'sms'
      ? 'Resend code'
      : 'Resend email'
)

const startCooldown = () => {
  resendCooldown.value = 45
  if (cooldownTimer) clearInterval(cooldownTimer)
  cooldownTimer = setInterval(() => {
    resendCooldown.value -= 1
    if (resendCooldown.value <= 0) {
      resendCooldown.value = 0
      clearInterval(cooldownTimer)
      cooldownTimer = null
    }
  }, 1000)
}

onMounted(() => {
  startCooldown()
})

onBeforeUnmount(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})

const onVerify = async () => {
  error.value = ''
  fieldError.value = ''
  if (!code.value || code.value.length < 4) {
    fieldError.value = 'Please enter the code.'
    return
  }
  if (!verifySession) {
    error.value = 'Verification session expired. Please login again.'
    sessionExpired.value = true
    return
  }
  loading.value = true
  try {
    const { data } = await client.post('/verify', {
      verify_session: verifySession,
      code: code.value,
    })
    if (data?.success && data?.data?.token) {
      saveToken(data.data.token)
      if (data.data.user) setUser(data.data.user)
      success.value = true
      localStorage.removeItem('pm_verify_session')
      localStorage.removeItem('pm_verify_via')
      const isSuperAdmin = data.data.user?.is_super_admin
        || String(data.data.user?.role || '').trim().toLowerCase().replace(/[\s-]+/g, '_') === 'super_admin'
      if (isSuperAdmin) {
        router.push('/super-admin/dashboard')
      } else if (data.data.user?.account_access_ready) {
        router.push('/dashboard')
      } else {
        router.push('/account-setup')
      }
    } else {
      error.value = data?.message || 'Verification failed.'
    }
  } catch (e) {
    if (e?.response?.status === 429) {
      error.value = 'Too many attempts. Please wait a minute and try again.'
    } else {
      error.value = e?.response?.data?.message || 'Verification failed.'
    }
    sessionExpired.value = error.value.toLowerCase().includes('expired')
  } finally {
    loading.value = false
  }
}

const resend = async () => {
  error.value = ''
  if (!verifySession) {
    error.value = 'Verification session expired. Please login again.'
    sessionExpired.value = true
    return
  }
  if (resendCooldown.value > 0) return
  resendLoading.value = true
  try {
    const { data } = await client.post('/verify/resend', { verify_session: verifySession })
    if (data?.data?.verify_via) {
      verifyVia.value = data.data.verify_via
      localStorage.setItem('pm_verify_via', data.data.verify_via)
    }
    startCooldown()
  } catch (e) {
    if (e?.response?.status === 429) {
      error.value = 'Please wait a bit before requesting another code.'
    } else {
      error.value = e?.response?.data?.message || 'Failed to resend.'
    }
  } finally {
    resendLoading.value = false
  }
}
</script>
