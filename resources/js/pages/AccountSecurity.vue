<template>
  <AccountLayout title="Account Security" subtitle="Update passwords and security preferences.">
    <template #actions>
      <button class="btn btn-primary" type="button" @click="saveSecurity">Save Security</button>
    </template>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Password Update</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="pm-field-label">Current Password</label>
          <input v-model="form.current_password" type="password" class="form-control" placeholder="Current password" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">New Password</label>
          <input v-model="form.new_password" type="password" class="form-control" placeholder="New password" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Security PIN</label>
          <input v-model="form.security_pin" class="form-control" placeholder="PIN" />
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel">
      <h4 class="mb-3">Security Preferences</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="pm-field-label">MFA Enabled</label>
          <select v-model="form.mfa_enabled" class="form-control">
            <option :value="true">Enabled</option>
            <option :value="false">Disabled</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Login Alerts</label>
          <select v-model="form.login_alerts" class="form-control">
            <option :value="true">Enabled</option>
            <option :value="false">Disabled</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Session Timeout (min)</label>
          <input v-model.number="form.session_timeout" type="number" class="form-control" min="5" max="240" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Device Limit</label>
          <input v-model.number="form.device_limit" type="number" class="form-control" min="1" max="50" />
        </div>
      </div>
      <div v-if="message" class="text-success mt-3">{{ message }}</div>
      <div v-if="error" class="text-danger mt-3">{{ error }}</div>
    </div>
  </AccountLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'
import { setFlash } from '../store/flash'

const form = ref({
  security_pin: '',
  mfa_enabled: false,
  login_alerts: true,
  session_timeout: 30,
  device_limit: 5,
  current_password: '',
  new_password: '',
})

const message = ref('')
const error = ref('')

const loadSecurity = async () => {
  try {
    const { data } = await client.get('/account/security')
    if (data?.success && data.data) {
      form.value = { ...form.value, ...data.data }
    }
  } catch {
    // ignore
  }
}

const saveSecurity = async () => {
  message.value = ''
  error.value = ''
  try {
    await client.put('/account/security', form.value)
    message.value = 'Security settings saved.'
    setFlash('Security updated.', 'success', 2000)
    form.value.current_password = ''
    form.value.new_password = ''
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to update security.'
  }
}

onMounted(loadSecurity)
</script>
