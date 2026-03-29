<template>
  <div class="pm-success">
    <div class="pm-success-card pm-success-premium">
      <div class="pm-success-head">
        <div>
          <div class="pm-success-kicker">Setup Complete</div>
          <h3>Registration Successful</h3>
          <p class="pm-muted">Welcome to DeskDrop, {{ summary.company || 'your team' }}.</p>
          <p class="pm-muted">Your account has been successfully created and is ready for activation.</p>
        </div>
        <div class="pm-success-icon">OK</div>
      </div>

      <div class="pm-success-redirect" :class="{ paused: !autoRedirectActive }">
        <strong v-if="autoRedirectActive">Auto redirect to login in {{ redirectIn }}s</strong>
        <strong v-else>Auto redirect paused</strong>
      </div>

      <div class="pm-success-summary pm-success-summary-premium">
        <div class="pm-success-summary-title">Account Snapshot</div>
        <div class="pm-success-summary-grid">
          <div class="pm-success-summary-item">
            <div class="pm-final-label">Company:</div>
            <div>{{ summary.company || '-' }}</div>
          </div>
          <div class="pm-success-summary-item">
            <div class="pm-final-label">Facility:</div>
            <div>{{ summary.facility || '-' }}</div>
          </div>
          <div class="pm-success-summary-item">
            <div class="pm-final-label">Username:</div>
            <div>{{ summary.username || '-' }}</div>
          </div>
          <div class="pm-success-summary-item">
            <div class="pm-final-label">Email:</div>
            <div>{{ summary.email || '-' }}</div>
          </div>
          <div class="pm-success-summary-item">
            <div class="pm-final-label">Plan:</div>
            <div>{{ summary.plan || '-' }}</div>
          </div>
          <div class="pm-success-summary-item">
            <div class="pm-final-label">Created:</div>
            <div>{{ summary.created || '-' }}</div>
          </div>
        </div>
      </div>

      <div class="pm-success-next">
        <strong>What's Next?</strong>
        <ul>
          <li>Check your email ({{ summary.email || 'your inbox' }}) for verification instructions.</li>
          <li>Click the verification link to activate your account.</li>
          <li>Log in and start managing your parcel deliveries.</li>
          <li>Remember to update your password and PIN every 30 days.</li>
        </ul>
      </div>

      <div class="pm-success-actions">
        <button class="btn btn-outline-primary" type="button" @click="downloadSummary">Download Summary</button>
        <button class="btn btn-primary" type="button" @click="goLogin">Go to Login</button>
        <button v-if="autoRedirectActive" class="btn btn-outline-secondary" type="button" @click="cancelAutoRedirect">
          Stay Here
        </button>
      </div>

      <div class="pm-success-help">
        Need assistance? Contact us at <a href="mailto:support@deskdrop.com">support@deskdrop.com</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const summary = ref({
  company: '',
  facility: '',
  username: '',
  email: '',
  plan: '',
  created: '',
})
const redirectIn = ref(8)
const autoRedirectActive = ref(true)
let redirectTimerId = null

const loadSummary = () => {
  try {
    const raw = sessionStorage.getItem('account_setup_summary')
    if (!raw) return
    const parsed = JSON.parse(raw)
    summary.value = { ...summary.value, ...parsed }
  } catch {
    // ignore
  }
}

const downloadSummary = () => {
  const text = [
    'DeskDrop Registration Summary',
    `Company: ${summary.value.company || '-'}`,
    `Facility: ${summary.value.facility || '-'}`,
    `Username: ${summary.value.username || '-'}`,
    `Email: ${summary.value.email || '-'}`,
    `Plan: ${summary.value.plan || '-'}`,
    `Created: ${summary.value.created || '-'}`,
  ].join('\n')
  const blob = new Blob([text], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'deskdrop-summary.txt'
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}

const goLogin = () => {
  clearRedirectTimer()
  router.push('/login')
}

const clearRedirectTimer = () => {
  if (redirectTimerId) {
    clearInterval(redirectTimerId)
    redirectTimerId = null
  }
}

const startAutoRedirect = () => {
  clearRedirectTimer()
  autoRedirectActive.value = true
  redirectIn.value = 8

  redirectTimerId = setInterval(() => {
    if (!autoRedirectActive.value) return
    if (redirectIn.value <= 1) {
      clearRedirectTimer()
      goLogin()
      return
    }
    redirectIn.value -= 1
  }, 1000)
}

const cancelAutoRedirect = () => {
  autoRedirectActive.value = false
  clearRedirectTimer()
}

onMounted(() => {
  loadSummary()
  startAutoRedirect()
})

onBeforeUnmount(() => {
  clearRedirectTimer()
})
</script>
