<template>
  <div class="login-page">
    <aside class="login-showcase" :style="{ backgroundImage: `linear-gradient(180deg,rgba(1,18,55,.72),rgba(1,18,55,.91)),url(${signupImage})` }">
      <RouterLink to="/" class="login-brand"><img :src="logoWhite" alt="Contractor.com" /><strong>Contractor.com</strong></RouterLink>
      <h1>Welcome to <span>Contractor.com</span></h1><p>The complete contractor business management platform for estimates, quotations, jobs, customers, vehicles, invoicing, and accounting.</p>
      <div class="login-features"><div v-for="item in features" :key="item.title"><b>{{ item.icon }}</b><p><strong>{{ item.title }}</strong><span>{{ item.text }}</span></p></div></div>
      <div class="login-support">🎧 <span><strong>Dedicated Contractor Support</strong>Our support team is here to assist you every step of the way.</span></div>
    </aside>
    <main class="login-main"><section class="login-card">
      <div class="login-steps"><div class="active"><b>1</b><span>Account Credentials</span></div><i></i><div><b>2</b><span>Verification</span></div><i></i><div><b>3</b><span>Access Granted</span></div></div>
      <div class="login-heading"><b>♢</b><div><h2>Secure Login</h2><p>All fields are required for account verification.</p></div></div>
      <form class="login-form" @submit.prevent="onLogin" novalidate>
        <div class="login-fields"><div><label>Username / Email <em>*</em></label><input v-model="form.login" placeholder="Enter your username or email" @input="clearVerificationSession" /><small v-if="errors.login">{{ errors.login[0] }}</small></div>
          <div class="login-input-eye"><label>Password <em>*</em></label><input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password" @input="clearVerificationSession" /><button type="button" @click="showPassword=!showPassword">◉</button><small v-if="errors.password">{{ errors.password[0] }}</small></div>
          <div class="login-input-eye"><label>PIN / 2FA Code <em>*</em></label><input v-model="form.security_pin" :type="showPin ? 'text' : 'password'" placeholder="Enter your PIN or 2FA code" inputmode="numeric" @input="clearVerificationSession" /><button type="button" @click="showPin=!showPin">◉</button><small v-if="errors.security_pin">{{ errors.security_pin[0] }}</small></div>
          <div class="login-otp-fields"><label>Phone Verification Code <em>*</em></label><div><input v-model="form.phone_code" inputmode="numeric" maxlength="6" placeholder="Enter the 6-digit code sent to your phone" /><button type="button" :class="{selected:form.verifyVia==='sms'}" :disabled="verificationSending" @click="requestVerification('sms')">{{ verificationSending && form.verifyVia === 'sms' ? 'Sending...' : 'Send Code' }}</button></div><small v-if="errors.phone_code">{{ errors.phone_code[0] }}</small><label>Email Verification Code <em>*</em></label><div><input v-model="form.email_code" inputmode="numeric" maxlength="6" placeholder="Enter the 6-digit code sent to your email" /><button type="button" :class="{selected:form.verifyVia==='email'}" :disabled="verificationSending" @click="requestVerification('email')">{{ verificationSending && form.verifyVia === 'email' ? 'Sending...' : 'Send Code' }}</button></div><small v-if="errors.email_code">{{ errors.email_code[0] }}</small><p>Choose email or phone, send one code, then enter it and click Log In Securely.</p><small v-if="verificationFeedback" :class="verificationFeedbackType === 'error' ? 'login-error' : 'login-success'">{{ verificationFeedback }}</small></div>
          <ImageCaptcha ref="captchaRef" @change="onCaptchaChange" /><small v-if="errors.captcha_value">{{ errors.captcha_value[0] }}</small>
          <label class="login-trust"><input v-model="form.agree" type="checkbox" /> I agree to the <a :href="termsUrl" target="_blank">Terms &amp; Conditions</a></label><div class="login-links"><RouterLink to="/forgot-password">Forgot Password?</RouterLink><RouterLink to="/forgot-username">Forgot Username?</RouterLink></div>
          <p v-if="error" class="login-error">{{ error }}</p><button class="login-submit" type="submit" :disabled="loading">🔒 {{ loading ? 'Checking credentials...' : 'Log In Securely' }}</button><p class="login-register">Don't have an account? <RouterLink to="/register">Create Account</RouterLink></p>
        </div>
        <aside class="login-status"><section><h3>♢ Verification Status</h3><p>Complete all steps to access your account.</p><ul><li>○ Account Credentials <span>Required</span></li><li>○ PIN / 2FA Code <span>Required</span></li><li>☎ {{ form.verifyVia === 'sms' ? 'Phone' : 'Email' }} Verification <span>Selected</span></li></ul></section><section class="login-assurance"><h3>♢ Security Assurance</h3><p><b>✓</b> SSL Encrypted Connection</p><p><b>✓</b> Multi-Factor Authentication</p><p><b>✓</b> Secure Cloud Access</p><p><b>✓</b> Session Protected</p></section><section><h3>▣ Login Details</h3><p>Device: This browser</p><p>Location: Secured session</p></section></aside>
      </form>
      <footer class="login-footer"><span>🎧 Need help? Visit our Help Center or contact support.</span><span>💬 Live Support · Mon–Fri 8:00 AM–6:00 PM</span></footer>
    </section></main>
  </div>
</template>
<script setup>
import logoWhite from '../assets/logo-white.png'
import signupImage from '../assets/signup.png'
import { onMounted, reactive, ref } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { requestLoginVerificationCode, saveToken, verifyLoginCode } from '../api/auth'
import { normalizeApiError } from '../api/client'
import { setFlash } from '../store/flash'
import { FLASH } from '../config/messages'
import { setUser } from '../store/auth'
import ImageCaptcha from '../components/ImageCaptcha.vue'
const router = useRouter()
const route = useRoute()
const termsUrl = `${import.meta.env.BASE_URL}terms`
const loading = ref(false)
const verificationSending = ref(false)
const verificationFeedback = ref('')
const verificationFeedbackType = ref('success')
const verifySession = ref(localStorage.getItem('pm_verify_session') || '')
const error = ref('')
const errors = ref({})
const captchaRef = ref(null)
const showPassword = ref(false)
const showPin = ref(false)
const form = reactive({
  login: '',
  password: '',
  security_pin: '',
  phone_code: '',
  email_code: '',
  verifyVia: 'email',
  agree: false,
})
const captchaKey = ref('')
const captchaValue = ref('')
const features = [
  { icon: '▣', title: 'Estimates & Quotations', text: 'Create professional estimates and quotations quickly.' },
  { icon: '◉', title: 'Customer Management', text: 'Manage customers, contacts, and project history.' },
  { icon: '▣', title: 'Job & Project Management', text: 'Track schedules, work orders, and progress.' },
  { icon: '▰', title: 'Vehicle Management', text: 'Monitor maintenance, mileage, and drivers.' },
  { icon: '▦', title: 'Accounting & Invoicing', text: 'Manage invoices, payments, expenses, and taxes.' },
  { icon: '♢', title: 'Secure Cloud Platform', text: 'Your data is protected with secure cloud encryption.' },
]

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
  const verificationCode = form.verifyVia === 'sms' ? form.phone_code : form.email_code
  if (!verifySession.value) {
    error.value = 'Please click Send Code, then enter the verification code.'
    return
  }
  if (!/^\d{4,6}$/.test(verificationCode || '')) {
    errors.value[form.verifyVia === 'sms' ? 'phone_code' : 'email_code'] = ['Enter the 4 to 6 digit verification code.']
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
    const res = await verifyLoginCode({
      verify_session: verifySession.value,
      code: verificationCode,
      captcha_key: captchaKey.value,
      captcha_value: captchaValue.value,
    })
    if (res?.success && res.data?.token) {
      saveToken(res.data.token)
      if (res.data.user) setUser(res.data.user)
      clearVerificationSession()
      setFlash(FLASH.LOGIN_SUCCESS, 'success', 2500)
      if (res.data.user?.is_super_admin || String(res.data.user?.role || '').trim().toLowerCase().replace(/[\s-]+/g, '_') === 'super_admin') {
        router.push('/super-admin/dashboard')
      } else if (res.data.user?.account_access_ready) {
        router.push(route.query.redirect || '/dashboard')
      } else {
        router.push('/account-setup')
      }
    } else {
      error.value = 'Login failed.'
    }
  } catch (e) {
    const parsed = normalizeApiError(e)
    errors.value = parsed.errors || {}
    if (errors.value.code) {
      errors.value[form.verifyVia === 'sms' ? 'phone_code' : 'email_code'] = errors.value.code
    }
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
const requestVerification = async (via) => {
  form.verifyVia = via
  error.value = ''
  verificationFeedback.value = ''
  // Sending an OTP must not submit the form or refresh the captcha. Keep the
  // captcha key/value untouched; only clear fields related to verification.
  errors.value = {
    ...errors.value,
    login: null,
    password: null,
    security_pin: null,
    phone_code: null,
    email_code: null,
  }

  if (!form.login) {
    errors.value.login = ['User name or email is required.']
    return
  }
  if (!form.password) {
    errors.value.password = ['Password is required.']
    return
  }

  verificationSending.value = true
  try {
    const res = await requestLoginVerificationCode({
      login: form.login,
      password: form.password,
      security_pin: form.security_pin,
      verifyVia: via,
    })
    const deliveredVia = res?.data?.verify_via || via
    form.verifyVia = deliveredVia
    verifySession.value = res?.data?.verify_session || ''
    localStorage.setItem('pm_verify_session', verifySession.value)
    localStorage.setItem('pm_verify_via', deliveredVia)
    form.phone_code = ''
    form.email_code = ''
    verificationFeedbackType.value = 'success'
    verificationFeedback.value = res?.message || `Verification code sent to your ${deliveredVia === 'sms' ? 'phone' : 'email'}.`
  } catch (e) {
    const parsed = normalizeApiError(e)
    errors.value = parsed.errors || {}
    verificationFeedbackType.value = 'error'
    verificationFeedback.value = parsed.message || 'Unable to send verification code.'
  } finally {
    verificationSending.value = false
  }
}
const clearVerificationSession = () => {
  verifySession.value = ''
  localStorage.removeItem('pm_verify_session')
  localStorage.removeItem('pm_verify_via')
  verificationFeedback.value = ''
}
const onCaptchaChange = (payload) => {
  captchaKey.value = payload.captcha_key
  captchaValue.value = payload.captcha_value
  errors.value.captcha_value = null
}
</script>

<style scoped>
.pm-auth-login { min-height:100vh; padding:28px; background:linear-gradient(135deg,#123dcc,#4868ec); display:grid; place-items:center; }
.pm-auth-login .pm-auth-card { width:min(1120px,100%); min-height:720px; overflow:hidden; border:0; border-radius:12px; box-shadow:0 24px 60px rgba(7,20,75,.28); background:#fff; }
.pm-auth-login .pm-auth-left { color:#fff; background:linear-gradient(160deg,#021a49,#063b93); }.pm-auth-login .pm-auth-left-panel { min-height:100%; padding:36px 28px; }
.pm-auth-login .pm-auth-right { padding:42px 54px; }.pm-auth-login .pm-auth-tabs { display:flex; justify-content:flex-end; gap:18px; }.pm-auth-login .pm-auth-tabs a { color:#587; text-decoration:none; }.pm-auth-login .pm-auth-tabs .active { color:#0c43e5; font-weight:700; }
.pm-auth-login form { max-width:540px; margin:42px auto 0; }.pm-auth-login form::before { content:'Secure Login\A All fields are required for account verification.'; white-space:pre-line; display:block; margin-bottom:28px; color:#071c50; text-align:center; font-size:1.65rem; font-weight:800; }.pm-auth-login form::after { content:'Verification status · Password, PIN, and one selected email or phone code are required.'; display:block; margin-top:24px; padding:14px; color:#18356e; background:#f2f6ff; border:1px solid #cbd9ff; border-radius:8px; font-size:.82rem; }
.pm-auth-login .form-control { min-height:48px; border-color:#cad4e8; }.pm-login-field-label,.pm-login-verify-title { display:block; margin-bottom:7px; color:#0b1e4a; font-weight:700; }.pm-login-verify-title { margin-top:8px; }.pm-auth-login .pm-auth-submit { min-height:48px; background:#0842de; border-color:#0842de; font-weight:700; }
@media (max-width:991px) { .pm-auth-login { padding:0; }.pm-auth-login .pm-auth-card { min-height:100vh; border-radius:0; }.pm-auth-login .pm-auth-right { padding:28px 22px; } }
</style>

<style scoped>
.login-otp-fields{display:grid;gap:8px}.login-otp-fields>div{display:grid;grid-template-columns:1fr 100px}.login-otp-fields>div input{margin:0;border-radius:6px 0 0 6px}.login-otp-fields button{border:1px solid #7da0ff;border-left:0;border-radius:0 6px 6px 0;color:#0644e5;background:#fff;font-weight:700}.login-otp-fields button:disabled{cursor:not-allowed;opacity:.65}.login-otp-fields button.selected{background:#edf3ff}.login-otp-fields p{margin:0;color:#697897;font-size:11px}.login-success{color:#17803d}
.login-card{min-height:0!important}.login-heading{display:flex;flex-direction:row;align-items:center;justify-content:center;gap:14px;margin:18px 0!important;text-align:left}.login-heading>b{flex:0 0 auto;margin:0!important}.login-heading h2{margin:0 0 3px!important}.login-fields input[type="checkbox"]{width:16px!important;height:16px!important;min-height:0!important;margin:0!important;padding:0!important;vertical-align:middle;accent-color:#0644e5}.login-trust{display:flex!important;align-items:center!important;gap:8px!important;min-height:20px}.login-trust a{display:inline!important}.login-assurance p{display:flex;align-items:center;gap:7px}.login-assurance p b{display:grid;place-items:center;width:14px;height:14px;border-radius:50%;color:#fff;background:#54a85d;font-size:9px;line-height:1}.login-footer{margin-top:22px!important}.login-showcase{padding-top:20px;padding-bottom:20px}.login-features{gap:10px}.login-support{margin-top:14px}.login-main{padding:18px}.login-card{padding-top:20px}.login-form{gap:26px}.login-fields{gap:11px}
</style>

<style scoped>
.login-page{min-height:100vh;display:grid;grid-template-columns:26% 74%;background:#3557df;color:#091943}.login-showcase{display:flex;flex-direction:column;padding:28px 18px;color:#fff;background-size:cover;background-position:center}.login-brand{display:flex;align-items:center;gap:9px;color:#fff;text-decoration:none;font-size:22px}.login-brand img{width:55px;height:55px;object-fit:contain}.login-showcase h1{margin:27px 0 10px;font-size:26px}.login-showcase h1 span{display:block;color:#1688ff}.login-showcase>p{max-width:250px;line-height:1.55}.login-features{display:grid;gap:15px;margin-top:auto}.login-features>div{display:flex;gap:12px}.login-features b{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:#064be8}.login-features p{display:grid;gap:3px;margin:0;font-size:12px}.login-features span{color:#d5ddf2}.login-support{display:flex;gap:12px;margin-top:22px;padding:14px;border:1px solid #1959bc;border-radius:7px}.login-support span{display:grid;gap:3px;font-size:12px}.login-main{padding:27px;display:grid;place-items:center}.login-card{width:min(990px,100%);min-height:860px;background:#fff;border-radius:10px;box-shadow:0 20px 55px #152d9b;padding:28px 39px 0}.login-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px}.login-steps>div{display:grid;justify-items:center;gap:8px;color:#66708c;font-size:11px}.login-steps b{display:grid;place-items:center;width:31px;height:31px;border:1px solid #c8d1e5;border-radius:50%}.login-steps .active{color:#0644e5}.login-steps .active b{color:#fff;background:#0644e5;border-color:#0644e5}.login-steps i{width:26%;margin-top:15px;border-top:1px solid #d5dcec}.login-heading{text-align:center;margin:25px 0}.login-heading>b{display:grid;place-items:center;margin:auto;width:50px;height:50px;border-radius:50%;color:#0644e5;background:#eaf1ff;font-size:29px}.login-heading h2{margin:12px 0 3px;font-size:29px}.login-heading p{margin:0}.login-form{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(270px,1fr);gap:34px}.login-fields{display:grid;gap:15px}.login-fields label{font-weight:700;font-size:13px}.login-fields em{color:#e32a34}.login-fields input{width:100%;height:46px;margin-top:7px;padding:0 14px;border:1px solid #ccd5e7;border-radius:6px;outline:none}.login-input-eye{position:relative}.login-input-eye button{position:absolute;right:8px;top:32px;border:0;background:transparent;color:#0c317d}.login-fields small,.login-error{color:#bf1d2d}.login-choice{display:grid;grid-template-columns:1fr 1fr;gap:8px}.login-choice label,.login-choice p{grid-column:1/-1}.login-choice button{padding:12px;border:1px solid #cbd6ec;border-radius:6px;color:#163675;background:#fff;text-align:left}.login-choice button.selected{border-color:#0750e9;background:#eef4ff;color:#0644e5;font-weight:700}.login-choice p{margin:0;color:#697897;font-size:11px}.login-trust{font-size:12px}.login-links{display:flex;justify-content:space-between;font-size:12px}.login-submit{height:48px;border:0;border-radius:5px;color:#fff;background:#0644e5;font-weight:700}.login-register{text-align:center;font-size:13px}.login-register a,.login-links a{color:#0644e5;font-weight:700}.login-status{display:grid;align-content:start;gap:20px;border-left:1px solid #dce3ef;padding-left:33px}.login-status section{padding:16px;border:1px solid #d0dcf5;border-radius:7px;background:#f7faff}.login-status h3{margin:0 0 7px;color:#123876;font-size:14px}.login-status p{margin:7px 0;font-size:12px}.login-status ul{display:grid;gap:12px;padding:0;list-style:none;font-size:12px}.login-status li{display:flex;justify-content:space-between}.login-status li span{color:#71809b}.login-footer{display:flex;justify-content:space-between;gap:20px;margin:34px -39px 0;padding:22px 39px;color:#425273;background:#f4f7ff;font-size:12px}@media(max-width:900px){.login-page{grid-template-columns:1fr}.login-showcase{display:none}.login-main{padding:12px}.login-card{padding:24px 20px}.login-form{grid-template-columns:1fr}.login-status{border-left:0;padding-left:0}.login-footer{margin:25px -20px 0;padding:18px 20px;flex-direction:column}.login-steps i{width:15%}}
</style>
