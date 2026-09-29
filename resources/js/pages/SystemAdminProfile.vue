<template>
  <AccountLayout bare>
    <main class="system-admin-page">
      <div class="system-admin-shell">
        <h1>System Admin Profile</h1>
        <div v-if="message" class="system-admin-alert success">{{ message }}</div>
        <div v-if="error" class="system-admin-alert error">{{ error }}</div>
        <form class="system-admin-grid" @submit.prevent="save">
          <label>Admin Name<input v-model.trim="form.name" required /></label>
          <label>Position<input v-model.trim="form.position" placeholder="System Administrator" /></label>
          <label>Email Address<input v-model.trim="form.email" type="email" required /></label>
          <label>Phone Number<input v-model.trim="form.phone" placeholder="+14165550100" /></label>
          <label class="system-admin-timezone">Time Zone<input v-model.trim="form.timezone" placeholder="Asia/Dhaka" /></label>
          <div class="system-admin-actions">
            <button class="system-admin-button outline" type="button" @click="load">Reset</button>
            <button class="system-admin-button primary" type="submit" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button>
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

const form = ref({ name: '', position: '', email: '', phone: '', timezone: '' })
const saving = ref(false)
const message = ref('')
const error = ref('')

const normalizePhone = (value) => {
  const raw = String(value || '').trim()
  if (!raw) return ''
  const number = raw.replace(/[^\d+]/g, '')
  return number.startsWith('00') ? `+${number.slice(2)}` : number.startsWith('+') ? `+${number.slice(1).replace(/\D/g, '')}` : `+${number.replace(/\D/g, '')}`
}

const load = async () => {
  error.value = ''
  try {
    const { data } = await client.get('/account/profile')
    const user = data?.data?.user || {}
    const profile = data?.data?.profile || {}
    const admin = data?.data?.system_admin || {}
    form.value = { name: user.name || '', position: admin.position || user.role || '', email: user.email || '', phone: user.phone || '', timezone: profile.timezone || '' }
  } catch {
    error.value = 'Unable to load system admin profile.'
  }
}

const save = async () => {
  message.value = ''
  error.value = ''
  const phone = normalizePhone(form.value.phone)
  if (phone && !/^\+[1-9]\d{7,14}$/.test(phone)) {
    error.value = 'Enter a valid international phone number, such as +14165550100.'
    return
  }
  saving.value = true
  try {
    await client.put('/account/profile', { ...form.value, phone })
    form.value.phone = phone
    message.value = 'System admin profile updated successfully.'
    setFlash('System admin profile updated.', 'success', 2000)
  } catch (e) {
    error.value = e?.response?.data?.message || 'Unable to save system admin profile.'
  } finally { saving.value = false }
}

onMounted(load)
</script>

<style scoped>
.system-admin-page { min-height: 100%; padding: 18px 48px 20px; background: #f9f9fb; color: #092c67; }
.system-admin-shell { max-width: 1000px; }.system-admin-shell h1 { margin: 0 0 28px; color: #0a3372; font-size: clamp(2rem, 3.1vw, 3rem); font-weight: 800; letter-spacing: -.04em; }
.system-admin-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px 68px; }.system-admin-grid label { display: grid; gap: 7px; color: #092c67; font-size: 1.05rem; font-weight: 800; }.system-admin-grid input { width: 100%; min-height: 46px; padding: 7px 12px; border: 2px solid #c3c3c7; border-radius: 7px; background: #fff; color: #17294b; font-size: .98rem; outline: none; }.system-admin-grid input:focus { border-color: #096deb; box-shadow: 0 0 0 3px rgba(9,109,235,.14); }.system-admin-timezone { grid-column: 1 / -1; }.system-admin-actions { grid-column: 1 / -1; display: flex; gap: 18px; margin-top: 4px; }.system-admin-button { min-width: 142px; min-height: 48px; border-radius: 7px; font-size: 1.08rem; font-weight: 800; cursor: pointer; }.system-admin-button.outline { border: 2px solid #0870ef; background: #fff; color: #0870ef; }.system-admin-button.primary { border: 2px solid #0870ef; background: #0870ef; color: #fff; }.system-admin-button:disabled { cursor: wait; opacity: .65; }.system-admin-alert { margin: -18px 0 18px; max-width: 1000px; padding: 8px 12px; border-radius: 8px; font-weight: 600; }.system-admin-alert.success { color: #126638; background: #e8f8ee; }.system-admin-alert.error { color: #9f1e2f; background: #fdecee; }
@media (max-width: 900px) { .system-admin-page { padding: 18px 22px; }.system-admin-grid { grid-template-columns: 1fr; gap: 14px; }.system-admin-timezone,.system-admin-actions { grid-column: auto; } }
</style>
