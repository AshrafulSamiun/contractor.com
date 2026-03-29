<template>
  <AccountLayout title="Account Recovery" subtitle="Set recovery contacts and escalation paths.">
    <template #actions>
      <button class="btn btn-primary" type="button" @click="saveRecovery">Save Recovery</button>
    </template>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Recovery Contacts</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pm-field-label">Recovery Email</label>
          <input v-model="form.recovery_email" class="form-control" type="email" placeholder="recovery@company.com" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Recovery Phone</label>
          <input v-model="form.recovery_phone" class="form-control" placeholder="+1 (555) 123-4567" />
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Backup Contact</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="pm-field-label">Contact Name</label>
          <input v-model="form.backup_contact_name" class="form-control" placeholder="Backup contact name" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Contact Email</label>
          <input v-model="form.backup_contact_email" class="form-control" type="email" placeholder="contact@company.com" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Contact Phone</label>
          <input v-model="form.backup_contact_phone" class="form-control" placeholder="+1 (555) 123-4567" />
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel">
      <h4 class="mb-3">Escalation Notes</h4>
      <textarea v-model="form.escalation_notes" class="form-control" rows="4" placeholder="Add escalation instructions..."></textarea>
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
  recovery_email: '',
  recovery_phone: '',
  backup_contact_name: '',
  backup_contact_email: '',
  backup_contact_phone: '',
  escalation_notes: '',
})

const message = ref('')
const error = ref('')

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

const loadRecovery = async () => {
  try {
    const { data } = await client.get('/account/recovery')
    if (data?.success && data.data) {
      form.value = { ...form.value, ...data.data }
    }
  } catch {
    // ignore
  }
}

const saveRecovery = async () => {
  message.value = ''
  error.value = ''

  form.value.recovery_phone = normalizePhoneNumber(form.value.recovery_phone)
  form.value.backup_contact_phone = normalizePhoneNumber(form.value.backup_contact_phone)

  if (form.value.recovery_phone && !isE164PhoneNumber(form.value.recovery_phone)) {
    error.value = 'Recovery phone must be in E.164 format (e.g. +14165550100).'
    return
  }
  if (form.value.backup_contact_phone && !isE164PhoneNumber(form.value.backup_contact_phone)) {
    error.value = 'Backup contact phone must be in E.164 format (e.g. +14165550100).'
    return
  }

  try {
    await client.put('/account/recovery', form.value)
    message.value = 'Recovery settings saved.'
    setFlash('Recovery updated.', 'success', 2000)
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to save recovery.'
  }
}

onMounted(loadRecovery)
</script>
