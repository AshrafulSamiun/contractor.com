<template>
  <AccountLayout bare>
    <main class="account-info-page">
      <div class="account-info-shell">
        <h1>Account Info</h1>
        <div v-if="error" class="account-alert error">{{ error }}</div>
        <div v-if="message" class="account-alert success">{{ message }}</div>

        <section class="account-info-grid account-info-summary">
          <label>Account Number<input :value="account.account_number" readonly /></label>
          <label>Account Created Date<input :value="formatDate(account.created_at)" readonly /></label>
          <label>Current Status<input :value="account.status" readonly /></label>
        </section>

        <h2>Created By</h2>
        <form class="account-info-grid account-info-form" @submit.prevent="saveProfile">
          <label>Name<input ref="nameInput" v-model.trim="form.name" required /></label>
          <label>Position<input :value="account.position" readonly /></label>
          <label>Phone<input v-model.trim="form.phone" placeholder="+14165550100" /></label>
          <label>Email<input v-model.trim="form.email" type="email" required /></label>
          <div class="account-info-actions">
            <button class="account-button outline" type="button" @click="nameInput?.focus()">Edit</button>
            <button class="account-button primary" type="submit" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button>
          </div>
        </form>
      </div>
    </main>
  </AccountLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'
import { setFlash } from '../store/flash'

const form = ref({ name: '', email: '', phone: '' })
const account = ref({ account_number: '', created_at: null, status: '', position: '' })
const nameInput = ref(null)
const saving = ref(false)
const message = ref('')
const error = ref('')

const formatDate = (value) => value ? new Date(value).toLocaleDateString() : '—'
const normalizePhone = (value) => {
  const raw = String(value || '').trim()
  if (!raw) return ''
  const digits = raw.replace(/[^\d+]/g, '')
  if (digits.startsWith('00')) return `+${digits.slice(2)}`
  return digits.startsWith('+') ? `+${digits.slice(1).replace(/\D/g, '')}` : `+${digits.replace(/\D/g, '')}`
}

const loadProfile = async () => {
  try {
    const { data } = await client.get('/account/profile')
    const user = data?.data?.user || {}
    account.value = { ...account.value, ...(data?.data?.account_info || {}) }
    form.value = { name: user.name || '', email: user.email || '', phone: user.phone || '' }
  } catch {
    error.value = 'Unable to load account information.'
  }
}

const saveProfile = async () => {
  error.value = ''
  message.value = ''
  const phone = normalizePhone(form.value.phone)
  if (phone && !/^\+[1-9]\d{7,14}$/.test(phone)) {
    error.value = 'Enter a valid international phone number, such as +14165550100.'
    return
  }
  saving.value = true
  try {
    await client.put('/account/profile', { ...form.value, phone })
    form.value.phone = phone
    message.value = 'Account information updated successfully.'
    setFlash('Account information updated.', 'success', 2000)
  } catch (e) {
    error.value = e?.response?.data?.message || 'Unable to save account information.'
  } finally {
    saving.value = false
  }
}

onMounted(loadProfile)
</script>

<style scoped>
.account-info-page { min-height: 100%; background: #f9f9fb; padding: 18px 48px 20px; color: #092c67; }
.account-info-shell { max-width: 1120px; }
h1 { margin: 0 0 28px; font-size: clamp(2rem, 3.1vw, 3rem); font-weight: 800; letter-spacing: -.04em; color: #0a3372; }
h2 { margin: 30px 0 14px; font-size: 1.65rem; font-weight: 800; color: #0a3372; }
.account-info-grid { display: grid; gap: 18px 42px; }
.account-info-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.account-info-form { grid-template-columns: repeat(2, minmax(0, 1fr)); max-width: 1060px; }
label { display: grid; gap: 7px; font-size: 1.05rem; font-weight: 800; color: #092c67; }
input { width: 100%; min-height: 46px; border: 2px solid #c3c3c7; border-radius: 7px; background: #fff; color: #17294b; font-size: .98rem; padding: 7px 12px; outline: none; }
input:read-only { background: #fafafa; color: #4c5770; }
input:not(:read-only):focus { border-color: #096deb; box-shadow: 0 0 0 3px rgba(9,109,235,.14); }
.account-info-actions { grid-column: 1 / -1; display: flex; gap: 18px; margin-top: 4px; }
.account-button { min-width: 142px; min-height: 48px; border-radius: 7px; font-size: 1.08rem; font-weight: 800; cursor: pointer; }
.account-button.outline { border: 2px solid #0870ef; color: #0870ef; background: white; }
.account-button.primary { border: 2px solid #0870ef; color: #fff; background: #0870ef; box-shadow: 0 10px 24px rgba(8,112,239,.18); }
.account-button:disabled { opacity: .65; cursor: wait; }
.account-alert { max-width: 1060px; margin: -18px 0 18px; padding: 8px 12px; border-radius: 8px; font-weight: 600; }
.account-alert.success { color: #126638; background: #e8f8ee; }.account-alert.error { color: #9f1e2f; background: #fdecee; }
@media (max-width: 900px) { .account-info-page { padding: 18px 22px; }.account-info-summary, .account-info-form { grid-template-columns: 1fr; gap: 14px; } h1 { margin-bottom: 22px; } h2 { margin-top: 26px; }.account-info-actions { grid-column: auto; gap: 12px; }.account-button { min-width: 122px; } }
</style>
