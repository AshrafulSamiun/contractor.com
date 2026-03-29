<template>
  <div class="pm-auth-split pm-auth-register">
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
          <RouterLink class="active" to="/register">{{ t('auth.signUp') }}</RouterLink>
          <RouterLink to="/login">{{ t('auth.signIn') }}</RouterLink>
        </div>
        <div v-if="summaryErrors.length" class="pm-form-summary">
          <strong>Please fix the following ({{ summaryErrors.length }}):</strong>
          <ul>
            <li v-for="(item, idx) in summaryErrors" :key="idx">{{ item }}</li>
          </ul>
        </div>
        <form class="pm-auth-form-grid" @submit.prevent="onRegister" novalidate>
          <div class="pm-grid-full pm-form-note mb-1"><strong>Basic Info</strong></div>
          <div class="mb-3">
            <label class="pm-field-label">Full Name<span class="pm-required">*</span></label>
            <input v-model="form.name" class="form-control" :class="{ 'pm-input-error': errors.name }" placeholder="Enter Full Name" />
            <div v-if="errors.name" class="text-danger small mt-1">{{ errors.name[0] }}</div>
          </div>
          <div class="mb-3">
            <label class="pm-field-label">Company Name<span class="pm-required">*</span></label>
            <input v-model="form.company" class="form-control" :class="{ 'pm-input-error': errors.company }" placeholder="Enter Company Name" />
            <div v-if="errors.company" class="text-danger small mt-1">{{ errors.company[0] }}</div>
          </div>
          <div class="mb-3">
            <label class="pm-field-label">User Name</label>
            <input v-model="form.username" class="form-control" :class="{ 'pm-input-error': errors.username }" placeholder="Enter User Name" />
            <div v-if="errors.username" class="text-danger small mt-1">{{ errors.username[0] }}</div>
          </div>
          <div class="mb-3">
            <label class="pm-field-label">Email Address<span class="pm-required">*</span></label>
            <input v-model="form.email" class="form-control" :class="{ 'pm-input-error': errors.email }" placeholder="Enter Email Address" />
            <div v-if="errors.email" class="text-danger small mt-1">{{ errors.email[0] }}</div>
          </div>
          <div class="mb-3">
            <label class="pm-field-label">Phone Number<span class="pm-required">*</span></label>
            <input v-model="form.phoneNo" class="form-control" :class="{ 'pm-input-error': errors.phoneNo }" placeholder="Enter Phone Number" />
            <div v-if="errors.phoneNo" class="text-danger small mt-1">{{ errors.phoneNo[0] }}</div>
          </div>
          <div class="mb-3">
            <label class="pm-field-label">Country<span class="pm-required">*</span></label>
            <select v-model.number="form.country_id" class="form-control" :class="{ 'pm-input-error': errors.country_id }">
              <option value="">Select Country</option>
              <option v-for="c in countries" :key="c.id" :value="c.id">
                {{ c.country_name }}
              </option>
            </select>
            <div v-if="errors.country_id" class="text-danger small mt-1">{{ errors.country_id[0] }}</div>
          </div>
          <div class="mb-3">
            <label class="pm-field-label">Zip / Postal Code<span class="pm-required">*</span></label>
            <input v-model="form.zip" class="form-control" :class="{ 'pm-input-error': errors.zip }" placeholder="Enter Zip / Postal Code" />
            <div v-if="errors.zip" class="text-danger small mt-1">{{ errors.zip[0] }}</div>
          </div>
          <div class="pm-grid-full pm-form-note mb-1 mt-2"><strong>Security</strong></div>

          <div class="mb-3">
            <label class="pm-field-label">Password<span class="pm-required">*</span></label>
            <input v-model="form.password" type="password" class="form-control" :class="{ 'pm-input-error': errors.password }" placeholder="Enter Password" />
            <span class="pm-form-note">Password required (minimum 8 characters)</span>
            <div v-if="errors.password" class="text-danger small mt-1">{{ errors.password[0] }}</div>
          </div>
          <div class="mb-3">
            <label class="pm-field-label">Confirm Password<span class="pm-required">*</span></label>
            <input v-model="form.password_confirmation" type="password" class="form-control" :class="{ 'pm-input-error': errors.password_confirmation }" placeholder="Confirm Password" />
            <span class="pm-form-note">Must match the password.</span>
            <div v-if="errors.password_confirmation" class="text-danger small mt-1">
              {{ errors.password_confirmation[0] }}
            </div>
          </div>
          <div class="mb-3 pm-grid-full">
            <div class="pm-form-note mb-2">Send Verification Code Via</div>
            <div class="form-check form-check-inline">
              <input v-model="form.verifyVia" class="form-check-input" type="radio" value="email" name="verifyVia" />
              <label class="form-check-label">Email</label>
            </div>
            <div class="form-check form-check-inline">
              <input v-model="form.verifyVia" class="form-check-input" type="radio" value="sms" name="verifyVia" />
              <label class="form-check-label">Phone (SMS)</label>
            </div>
          </div>
          <div class="mb-3 pm-grid-full">
            <ImageCaptcha ref="captchaRef" @change="onCaptchaChange" />
            <div v-if="errors.captcha_value" class="text-danger small mt-2">{{ errors.captcha_value[0] }}</div>
          </div>
          <div class="pm-grid-full mb-3 pm-terms-row">
            <div class="form-check">
              <input v-model="form.agree" class="form-check-input" type="checkbox" id="agreeTerms" />
              <label class="form-check-label" for="agreeTerms">
                I Agree to the <a :href="termsUrl" target="_blank" rel="noopener">Terms &amp; Conditions</a>
              </label>
              <div v-if="errors.agree" class="text-danger small mt-1">{{ errors.agree[0] }}</div>
            </div>
          </div>
          <div v-if="error" class="text-danger mb-3 pm-grid-full">{{ error }}</div>
          <div class="pm-grid-full d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 pm-auth-submit" :disabled="loading">
              {{ loading ? t('common.loading') : t('auth.signUp') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import logoWhite from '../assets/logo-white.png'
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { register, saveToken } from '../api/auth'
import { normalizeApiError } from '../api/client'
import { setFlash } from '../store/flash'
import { FLASH } from '../config/messages'
import { setUser } from '../store/auth'
import ImageCaptcha from '../components/ImageCaptcha.vue'
import LanguageSwitcher from '../components/LanguageSwitcher.vue'
import client from '../api/client'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()
const loading = ref(false)
const error = ref('')
const errors = ref({})
const captchaRef = ref(null)
const termsUrl = `${import.meta.env.BASE_URL}terms`

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  username: '',
  phoneNo: '',
  company: '',
  country_id: '',
  zip: '',
  verifyVia: 'email',
  plan: 'standard',
  agree: false,
})
const captchaKey = ref('')
const captchaValue = ref('')
const countries = ref([])

const normalizePhoneNumber = (value) => {
  const raw = String(value || '').trim()
  if (!raw) return ''

  let normalized = raw.replace(/[^\d+]/g, '')
  if (!normalized) return ''

  if (normalized.startsWith('00')) {
    normalized = `+${normalized.slice(2)}`
  }

  if (normalized.startsWith('+')) {
    normalized = `+${normalized.slice(1).replace(/\D/g, '')}`
  } else {
    normalized = `+${normalized.replace(/\D/g, '')}`
  }

  return normalized === '+' ? '' : normalized
}

const isE164PhoneNumber = (value) => /^\+[1-9]\d{7,14}$/.test(String(value || '').trim())

const summaryErrors = computed(() => {
  const list = []
  for (const key of Object.keys(errors.value || {})) {
    const msg = errors.value[key]?.[0]
    if (msg) list.push(msg)
  }
  return list
})

const loadCountries = async () => {
  try {
    const { data } = await client.get('/countries')
    if (data?.success) {
      countries.value = data.data || []
    }
  } catch {
    countries.value = []
  }
}

loadCountries()

onMounted(() => {
  const plan = route.query.plan
  if (plan === 'basic' || plan === 'standard' || plan === 'enterprise') {
    form.plan = plan
  }
})

const onRegister = async () => {
  error.value = ''
  errors.value = {}
  if (!form.name) errors.value.name = ['Full name is required.']
  if (!form.company) errors.value.company = ['Company name is required.']
  if (!form.email) {
    errors.value.email = ['Email is required.']
  } else {
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
    if (!emailOk) errors.value.email = ['Please enter a valid email address.']
  }
  if (!form.country_id) errors.value.country_id = ['Country is required.']
  if (!form.zip) errors.value.zip = ['Zip / Postal Code is required.']
  form.phoneNo = normalizePhoneNumber(form.phoneNo)
  if (!form.phoneNo) {
    errors.value.phoneNo = ['Phone number is required.']
  } else if (!isE164PhoneNumber(form.phoneNo)) {
    errors.value.phoneNo = ['Phone number must be in E.164 format (e.g. +14165550100).']
  }
  if (!form.password) errors.value.password = ['Password is required.']
  if (!form.password_confirmation) {
    errors.value.password_confirmation = ['Confirm your password.']
  } else if (form.password !== form.password_confirmation) {
    errors.value.password_confirmation = ['Password confirmation does not match.']
  }
  if (!captchaKey.value || !captchaValue.value) {
    errors.value.captcha_value = ['Please enter the captcha.']
  }
  if (!form.agree) {
    errors.value.agree = ['You must agree to the terms.']
  }
  if (Object.keys(errors.value).length) return
  loading.value = true
  try {
    const payload = {
      name: form.name,
      username: form.username,
      email: form.email,
      phoneNo: form.phoneNo,
      password: form.password,
      password_confirmation: form.password_confirmation,
      company: form.company,
      country_id: form.country_id,
      zip: form.zip,
      verifyVia: form.verifyVia,
      plan: form.plan,
      captcha_key: captchaKey.value,
      captcha_value: captchaValue.value,
    }
    const res = await register(payload)
    if (res?.verification_required) {
      localStorage.setItem('pm_verify_session', res.data?.verify_session || '')
      localStorage.setItem('pm_verify_via', res.data?.verify_via || 'email')
      router.push('/verify')
      return
    }
    if (res?.success && res.data?.token) {
      saveToken(res.data.token)
      if (res.data.user) setUser(res.data.user)
      setFlash(FLASH.REGISTER_SUCCESS, 'success', 3500)
      if (!res.data.user?.account_setup_completed_at) {
        router.push('/account-setup')
      } else {
        router.push('/')
      }
    } else {
      error.value = 'Registration failed.'
    }
  } catch (e) {
    const parsed = normalizeApiError(e)
    errors.value = parsed.errors || {}
    if (e?.response?.status === 429) {
      error.value = 'Too many attempts. Please wait a minute and try again.'
    } else {
      error.value = parsed.message || 'Registration failed.'
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
