<template>
  <AccountLayout title="Profile" subtitle="Manage your account profile and contact details.">
    <template #actions>
      <button class="btn btn-primary" type="button" @click="saveProfile">Save Changes</button>
    </template>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Primary Information</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pm-field-label">Full Name *</label>
          <input v-model="form.name" class="form-control" placeholder="Full name" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Email *</label>
          <input v-model="form.email" class="form-control" type="email" placeholder="name@company.com" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Phone</label>
          <input v-model="form.phone" class="form-control" placeholder="+1 (555) 123-4567" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Company</label>
          <input v-model="form.company_name" class="form-control" placeholder="Company name" />
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Address</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pm-field-label">Country</label>
          <select v-model.number="form.country_id" class="form-control">
            <option value="">Select country</option>
            <option v-for="c in countries" :key="c.id" :value="c.id">
              {{ c.country_name }}
            </option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Postal Code</label>
          <input v-model="form.postal_code" class="form-control" placeholder="Postal code" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Address Line 1</label>
          <input v-model="form.address_line1" class="form-control" placeholder="Street address" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Address Line 2</label>
          <input v-model="form.address_line2" class="form-control" placeholder="Suite / unit" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">City</label>
          <input v-model="form.city" class="form-control" placeholder="City" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">State / Region</label>
          <input v-model="form.state" class="form-control" placeholder="State / Region" />
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel">
      <h4 class="mb-3">Preferences</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pm-field-label">Preferred Language</label>
          <select v-model="form.preferred_language" class="form-control">
            <option value="">Select language</option>
            <option
              v-if="form.preferred_language && !languageCodeSet.has(String(form.preferred_language).toLowerCase())"
              :value="form.preferred_language"
            >
              {{ form.preferred_language }}
            </option>
            <option v-for="option in languageOptions" :key="option.code" :value="option.code">
              {{ option.label }}
            </option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Timezone</label>
          <input v-model="form.timezone" class="form-control" placeholder="UTC" />
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
import { LANGUAGE_OPTIONS, LANGUAGE_CODES } from '../config/languages'

const form = ref({
  name: '',
  email: '',
  phone: '',
  company_name: '',
  country_id: '',
  country: '',
  postal_code: '',
  address_line1: '',
  address_line2: '',
  city: '',
  state: '',
  preferred_language: '',
  timezone: '',
})

const countries = ref([])
const message = ref('')
const error = ref('')
const languageOptions = LANGUAGE_OPTIONS
const languageCodeSet = new Set(LANGUAGE_CODES)

const resolveCountryIdByName = (name) => {
  const normalized = (name || '').trim().toLowerCase()
  if (!normalized) return ''
  const found = countries.value.find((country) => (country.country_name || '').toLowerCase() === normalized)
  return found?.id || ''
}

const resolveCountryNameById = (id) => {
  if (!id) return ''
  const found = countries.value.find((country) => Number(country.id) === Number(id))
  return found?.country_name || ''
}

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

const loadCountries = async () => {
  try {
    const { data } = await client.get('/countries')
    if (data?.success && Array.isArray(data.data)) {
      countries.value = data.data
      return
    }
  } catch {
    // ignore
  }
  countries.value = []
}

const loadProfile = async () => {
  try {
    const { data } = await client.get('/account/profile')
    if (data?.success) {
      const user = data.data?.user || {}
      const profile = data.data?.profile || {}
      form.value = {
        ...form.value,
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        company_name: user.company_name || '',
        country_id: user.country_id || resolveCountryIdByName(user.country),
        country: user.country || '',
        postal_code: user.postal_code || '',
        address_line1: profile.address_line1 || '',
        address_line2: profile.address_line2 || '',
        city: profile.city || '',
        state: profile.state || '',
        preferred_language: profile.preferred_language || '',
        timezone: profile.timezone || '',
      }
    }
  } catch {
    // ignore
  }
}

const saveProfile = async () => {
  message.value = ''
  error.value = ''

  form.value.phone = normalizePhoneNumber(form.value.phone)
  if (form.value.phone && !isE164PhoneNumber(form.value.phone)) {
    error.value = 'Phone number must be in E.164 format (e.g. +14165550100).'
    return
  }

  try {
    const payload = { ...form.value }
    if (payload.country_id) {
      payload.country = resolveCountryNameById(payload.country_id) || payload.country || ''
    } else if (payload.country) {
      payload.country_id = resolveCountryIdByName(payload.country) || null
    } else {
      payload.country = null
      payload.country_id = null
    }
    await client.put('/account/profile', payload)
    message.value = 'Profile updated successfully.'
    setFlash('Profile updated.', 'success', 2000)
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to update profile.'
  }
}

onMounted(async () => {
  await loadCountries()
  await loadProfile()
})
</script>
