<template>
  <div class="pm-auth-split pm-auth-login">
    <div class="pm-auth-card row g-0">
      <div class="col-lg-5 pm-auth-left">
        <div class="pm-auth-left-panel">
          <div class="pm-auth-lang">
            <LanguageSwitcher compact />
          </div>
          <div class="pm-auth-logo">
            <RouterLink to="/" aria-label="Go to home">
              <img :src="logoWhite" alt="ParcelPro logo" class="pm-auth-logo-img" />
            </RouterLink>
          </div>
          <h4 class="mb-3">{{ t('auth.welcome') }}</h4>
          <p class="mb-4">{{ t('auth.recordParcel') }}</p>
          <p class="mb-0">{{ t('auth.manageParcels') }}</p>
        </div>
      </div>
      <div class="col-lg-7 pm-auth-right">
        <div class="pm-auth-tabs">
          <RouterLink to="/register">{{ t('auth.signUp') }}</RouterLink>
          <RouterLink class="active" to="/login">{{ t('auth.signIn') }}</RouterLink>
        </div>
        <div class="pm-auth-divider"></div>
        <form @submit.prevent="onLogin" novalidate>
          <div class="mb-3">
            <input v-model="form.login" class="form-control" placeholder="Enter User Name / Email" />
            <div v-if="errors.login" class="text-danger small mt-1">{{ errors.login[0] }}</div>
            <div v-else-if="errors.email" class="text-danger small mt-1">{{ errors.email[0] }}</div>
          </div>
          <div class="mb-3 pm-input-eye">
            <input v-model="form.password" type="password" class="form-control" placeholder="Enter Password" />
            <span class="pm-eye">&#128065;</span>
            <div v-if="errors.password" class="text-danger small mt-1">{{ errors.password[0] }}</div>
          </div>
          <div class="pm-auth-assist mb-2">
            <RouterLink class="pm-auth-assist-link" to="/forgot-password">{{ t('auth.forgotPassword') }}</RouterLink>
            <RouterLink class="pm-auth-assist-link" to="/forgot-username">{{ t('auth.forgotUsername') }}</RouterLink>
          </div>
          <div class="pm-form-note mb-3">Tip: use your username or email.</div>
          <div class="pm-auth-divider"></div>
          <div class="mb-3">
            <ImageCaptcha ref="captchaRef" @change="onCaptchaChange" />
            <div v-if="errors.captcha_value" class="text-danger small mt-2">{{ errors.captcha_value[0] }}</div>
          </div>
          <div class="mb-3">
            <div class="pm-form-note mb-2">Send Verification Code via</div>
            <div class="form-check form-check-inline">
              <input v-model="form.verifyVia" class="form-check-input" type="radio" value="email" name="verifyVia" />
              <label class="form-check-label">Email</label>
            </div>
            <div class="form-check form-check-inline">
              <input v-model="form.verifyVia" class="form-check-input" type="radio" value="sms" name="verifyVia" />
              <label class="form-check-label">SMS (Phone)</label>
            </div>
          </div>
          <div class="form-check mb-3">
            <input v-model="form.agree" class="form-check-input" type="checkbox" id="agreeTerms" />
            <label class="form-check-label" for="agreeTerms">
              I agree to the <a :href="termsUrl" target="_blank" rel="noopener">Terms &amp; Conditions</a>
            </label>
          </div>
          <div v-if="error" class="text-danger mb-3">{{ error }}</div>
          <button type="submit" class="btn btn-primary w-100 pm-auth-submit" :disabled="loading">
            {{ loading ? `${t('common.loading')}` : t('auth.signIn') }}
          </button>
          <div class="text-center mt-3 pm-muted">{{ t('auth.dontHave') }} <RouterLink to="/register">{{ t('auth.signUp') }}</RouterLink></div>
        </form>
      </div>
    </div>
  </div>
</template>
<script setup>
import logoWhite from '../assets/logo-white.png'
import { onMounted, reactive, ref } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { login, saveToken } from '../api/auth'
import { normalizeApiError } from '../api/client'
import { setFlash } from '../store/flash'
import { FLASH } from '../config/messages'
import { setUser } from '../store/auth'
import ImageCaptcha from '../components/ImageCaptcha.vue'
import LanguageSwitcher from '../components/LanguageSwitcher.vue'
const router = useRouter()
const route = useRoute()
const { t } = useI18n()
const termsUrl = `${import.meta.env.BASE_URL}terms`
const loading = ref(false)
const error = ref('')
const errors = ref({})
const captchaRef = ref(null)
const form = reactive({
  login: '',
  password: '',
  verifyVia: 'email',
  agree: false,
})
const captchaKey = ref('')
const captchaValue = ref('')

onMounted(() => {
  const status = String(route.query.email_verified || '').toLowerCase()
  if (!status) return

  if (status === 'success') {
    setFlash('Email verified successfully. Please sign in.', 'success', 4000)
  } else if (status === 'invalid') {
    setFlash('Verification link is invalid or expired. Please request a new email.', 'warning', 4500)
  }

  const nextQuery = { ...route.query }
  delete nextQuery.email_verified
  router.replace({ path: '/login', query: nextQuery })
})

const onLogin = async () => {
  error.value = ''
  errors.value = {}
  if (!form.login) {
    errors.value.login = ['User name or email is required.']
    return
  }
  if (!form.password) {
    errors.value.password = ['Password is required.']
    return
  }
  if (!captchaKey.value || !captchaValue.value) {
    errors.value.captcha_value = ['Please enter the captcha.']
    return
  }
  if (!form.agree) {
    error.value = 'You must agree to the Terms & Conditions.'
    return
  }
  loading.value = true
  try {
    const payload = {
      login: form.login,
      password: form.password,
      captcha_key: captchaKey.value,
      captcha_value: captchaValue.value,
      verifyVia: form.verifyVia,
    }
    const res = await login(payload)
    if (res?.verification_required) {
      localStorage.setItem('pm_verify_session', res.data?.verify_session || '')
      localStorage.setItem('pm_verify_via', res.data?.verify_via || 'email')
      router.push('/verify')
      return
    }
    if (res?.success && res.data?.token) {
      saveToken(res.data.token)
      if (res.data.user) setUser(res.data.user)
      setFlash(FLASH.LOGIN_SUCCESS, 'success', 2500)
      if (!res.data.user?.account_setup_completed_at) {
        router.push('/account-setup')
      } else {
        router.push(route.query.redirect || '/dashboard')
      }
    } else {
      error.value = 'Login failed.'
    }
  } catch (e) {
    const parsed = normalizeApiError(e)
    errors.value = parsed.errors || {}
    if (e?.response?.status === 429) {
      error.value = 'Too many attempts. Please wait a minute and try again.'
    } else {
      error.value = parsed.message || 'Login failed.'
    }
    if (errors.value?.captcha_value) {
      captchaRef.value?.refresh?.()
    }
  } finally {
    loading.value = false
  }
}
const onCaptchaChange = (payload) => {
  captchaKey.value = payload.captcha_key
  captchaValue.value = payload.captcha_value
  errors.value.captcha_value = null
}
</script>
