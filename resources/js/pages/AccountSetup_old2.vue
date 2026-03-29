<template>
  <div class="pm-setup-page">
    <div class="pm-setup-frame">
      <div class="pm-setup-topbar">
        <div class="pm-setup-topbar-inner">
          <button class="pm-setup-help" type="button" @click="goSupport">Help &amp; Support</button>
        </div>
      </div>

      <section class="pm-setup">
        <div class="pm-setup-grid">
          <aside class="pm-setup-sidebar">
            <div class="pm-setup-brand">
              <img :src="brandLogo" :alt="brandName" />
              <div>
                <div class="pm-setup-title">{{ brandName }}</div>
                <div class="pm-setup-subtitle">Account Setup System</div>
              </div>
            </div>

            <p class="pm-setup-copy">Complete all steps to create your account</p>

            <div class="pm-setup-progress">
              <div class="pm-progress-circle">
                <svg viewBox="0 0 120 120" aria-hidden="true">
                  <circle class="pm-progress-track" cx="60" cy="60" r="46" />
                  <circle class="pm-progress-value" cx="60" cy="60" r="46" :stroke-dasharray="progressDashArray" />
                </svg>
                <div class="pm-progress-content">
                  <strong>{{ progressPercent }}%</strong>
                  <small>Complete</small>
                </div>
              </div>
            </div>

            <div class="pm-setup-steps">
              <button
                v-for="(step, index) in steps"
                :key="step.key"
                type="button"
                :class="['pm-setup-step', { active: currentStep === index + 1, done: currentStep > index + 1 }]"
                @click="goToStep(index + 1)"
              >
                <span class="pm-step-line" aria-hidden="true" />
                <span class="pm-step-index" aria-hidden="true">{{ index + 1 }}</span>
                <div class="pm-step-copy">
                  <div class="pm-step-title">{{ step.title }}</div>
                  <div class="pm-step-status">{{ stepStatus(index + 1) }}</div>
                </div>
              </button>
            </div>

            <div class="pm-setup-footer">
              <div>Step {{ currentStep }} of {{ steps.length }}</div>
              <strong>{{ activeStep.title }}</strong>
            </div>
          </aside>

          <main class="pm-setup-content">
            <div class="pm-setup-card">
              <div class="pm-setup-card-header">
                <h1>{{ activeStep.title }}</h1>
                <p>{{ activeStep.subtitle }}</p>
              </div>

              <div v-if="error" class="pm-setup-alert pm-setup-alert-error">{{ error }}</div>
              <div v-if="success" class="pm-setup-alert pm-setup-alert-success">Step saved successfully.</div>

              <div v-if="currentStep === 1" class="pm-setup-form">
                <label class="pm-field pm-field-full">
                  <span>Full Name <em>*</em></span>
                  <input v-model="form.full_name" type="text" placeholder="Enter your full name" />
                </label>

                <label class="pm-field pm-field-full">
                  <span>Company Name <em>*</em></span>
                  <input v-model="form.company_name" type="text" placeholder="Enter your company name" />
                </label>

                <label class="pm-field pm-field-full">
                  <span>Business Registration Number <em>*</em></span>
                  <input v-model="form.business_registration_number" type="text" placeholder="Enter registration number" />
                </label>

                <label class="pm-field pm-field-full">
                  <span>Trade / Service Type <em>*</em></span>
                  <select v-model="form.trade_service_type">
                    <option value="">Select trade type</option>
                    <option v-for="option in tradeOptions" :key="option" :value="option">{{ option }}</option>
                  </select>
                </label>
              </div>

              <div v-else-if="currentStep === 2" class="pm-setup-form">
                <label class="pm-field pm-field-full">
                  <span>Street Address <em>*</em></span>
                  <input v-model="form.company_address" type="text" placeholder="Enter business address" />
                </label>

                <label class="pm-field">
                  <span>City <em>*</em></span>
                  <input v-model="form.company_city" type="text" placeholder="City" />
                </label>

                <label class="pm-field">
                  <span>State / Province <em>*</em></span>
                  <input v-model="form.company_state" type="text" placeholder="State / Province" />
                </label>

                <label class="pm-field">
                  <span>ZIP / Postal Code <em>*</em></span>
                  <input v-model="form.company_zip" type="text" placeholder="ZIP / Postal Code" />
                </label>

                <label class="pm-field">
                  <span>Country <em>*</em></span>
                  <select v-model.number="form.company_country_id">
                    <option value="">Select country</option>
                    <option v-for="country in countries" :key="country.id" :value="country.id">
                      {{ country.country_name }}
                    </option>
                  </select>
                </label>
              </div>

              <div v-else-if="currentStep === 3" class="pm-setup-form">
                <label class="pm-field">
                  <span>Registration Authority <em>*</em></span>
                  <input v-model="form.registration_authority" type="text" placeholder="Authority / Registrar name" />
                </label>

                <label class="pm-field">
                  <span>Registration Issue Date <em>*</em></span>
                  <input v-model="form.registration_issue_date" type="date" />
                </label>

                <label class="pm-field pm-field-full">
                  <span>Website</span>
                  <input v-model="form.contact_website" type="url" placeholder="https://yourcompany.com" />
                </label>

                <label class="pm-field pm-field-full">
                  <span>Registration Notes</span>
                  <textarea
                    v-model="form.registration_notes"
                    rows="4"
                    placeholder="Add anything important about your registration or license."
                  ></textarea>
                </label>

                <div class="pm-field pm-field-full">
                  <span>Company Logo</span>
                  <label class="pm-upload">
                    <input type="file" accept="image/png,image/jpeg" @change="onLogoSelect" />
                    <span>{{ form.company_logo_url ? 'Replace company logo' : 'Upload company logo' }}</span>
                    <small>PNG or JPG, max 5MB</small>
                  </label>
                  <div v-if="form.company_logo_url || form.company_logo_preview" class="pm-logo-preview">
                    <img :src="form.company_logo_url || form.company_logo_preview" alt="Company logo preview" />
                  </div>
                </div>
              </div>

              <div v-else-if="currentStep === 4" class="pm-setup-form">
                <label class="pm-field pm-field-full">
                  <span>Primary Contact Name <em>*</em></span>
                  <input v-model="form.contact_full_name" type="text" placeholder="Enter contact person name" />
                </label>

                <label class="pm-field">
                  <span>Business Email <em>*</em></span>
                  <input v-model="form.contact_business_email" type="email" placeholder="name@company.com" />
                </label>

                <label class="pm-field">
                  <span>Primary Phone <em>*</em></span>
                  <input v-model="form.contact_primary_phone" type="text" placeholder="+14165550100" />
                </label>

                <label class="pm-field pm-field-full">
                  <span>Mobile Phone</span>
                  <input v-model="form.contact_mobile_phone" type="text" placeholder="+14165550100" />
                </label>
              </div>

              <div v-else-if="currentStep === 5" class="pm-plan-grid">
                <button
                  v-for="plan in planOptions"
                  :key="plan.value"
                  type="button"
                  class="pm-plan"
                  :class="{ selected: form.subscription_plan === plan.value }"
                  @click="form.subscription_plan = plan.value"
                >
                  <span v-if="plan.badge" class="pm-plan-badge">{{ plan.badge }}</span>
                  <strong>{{ plan.label }}</strong>
                  <span class="pm-plan-price">{{ plan.price }}</span>
                  <p>{{ plan.description }}</p>
                </button>
              </div>

              <div v-else-if="currentStep === 6" class="pm-setup-form">
                <label class="pm-field pm-field-full">
                  <span>Username <em>*</em></span>
                  <input v-model="form.security_username" type="text" placeholder="Choose a username" />
                </label>

                <label class="pm-field">
                  <span>Password <em>*</em></span>
                  <input v-model="form.security_password" type="password" placeholder="Create a password" />
                </label>

                <label class="pm-field">
                  <span>Confirm Password <em>*</em></span>
                  <input v-model="form.security_password_confirm" type="password" placeholder="Confirm password" />
                </label>
              </div>

              <div v-else class="pm-review">
                <div class="pm-summary">
                  <div class="pm-summary-row">
                    <span>Full Name</span>
                    <strong>{{ form.full_name || 'Not provided' }}</strong>
                  </div>
                  <div class="pm-summary-row">
                    <span>Company</span>
                    <strong>{{ form.company_name || 'Not provided' }}</strong>
                  </div>
                  <div class="pm-summary-row">
                    <span>Registration Number</span>
                    <strong>{{ form.business_registration_number || 'Not provided' }}</strong>
                  </div>
                  <div class="pm-summary-row">
                    <span>Plan</span>
                    <strong>{{ selectedPlanLabel }}</strong>
                  </div>
                  <div class="pm-summary-row">
                    <span>Email</span>
                    <strong>{{ form.contact_business_email || 'Not provided' }}</strong>
                  </div>
                </div>

                <label class="pm-check">
                  <input v-model="form.terms_ack" type="checkbox" />
                  <span>I agree to the Terms and Conditions.</span>
                </label>
                <label class="pm-check">
                  <input v-model="form.privacy_ack" type="checkbox" />
                  <span>I agree to the Privacy Policy.</span>
                </label>
                <label class="pm-check">
                  <input v-model="form.final_ack" type="checkbox" />
                  <span>I confirm that the information provided is accurate.</span>
                </label>
              </div>

              <footer class="pm-setup-actions">
                <button class="pm-btn pm-btn-muted" type="button" :disabled="currentStep === 1 || loading" @click="prev">
                  Back
                </button>
                <button class="pm-btn pm-btn-primary" type="button" :disabled="loading" @click="next">
                  {{ currentStep === steps.length ? 'Finish' : 'Next' }}
                </button>
              </footer>
            </div>
          </main>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import logoWhite from '../assets/logo-white.png'
import client from '../api/client'
import { clearToken, logout } from '../api/auth'
import { authState } from '../store/auth'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const success = ref(false)
const countries = ref([])
const currentStep = ref(1)

const steps = [
  { key: 'personal', title: 'Personal Information', subtitle: 'Provide your basic personal and business details.' },
  { key: 'location', title: 'Business Location', subtitle: 'Enter your official business address.' },
  { key: 'registration', title: 'Company Registration', subtitle: 'Add your registration details and company branding.' },
  { key: 'contact', title: 'Contact Information', subtitle: 'Set the main contact details for your account.' },
  { key: 'plan', title: 'Service Plan Selection', subtitle: 'Choose the plan that fits your team and workload.' },
  { key: 'security', title: 'Account Security', subtitle: 'Create your account login credentials.' },
  { key: 'confirmation', title: 'Confirmation & Agreement', subtitle: 'Review your details and confirm the setup.' },
]

const tradeOptions = [
  'General Contractor',
  'Electrical Services',
  'Plumbing Services',
  'HVAC Services',
  'Roofing Services',
  'Painting Services',
  'Civil Construction',
  'Interior Fit-Out',
  'Landscaping',
  'Other',
]

const planOptions = [
  {
    value: 'starter',
    label: 'Starter',
    price: '$19 / month',
    description: 'Good for small contractors getting started.',
  },
  {
    value: 'growth',
    label: 'Growth',
    price: '$49 / month',
    description: 'Built for active teams managing multiple projects.',
    badge: 'Popular',
  },
  {
    value: 'enterprise',
    label: 'Enterprise',
    price: 'Custom pricing',
    description: 'For larger operations with advanced support needs.',
  },
]

const form = ref({
  full_name: '',
  company_name: '',
  business_registration_number: '',
  trade_service_type: '',
  company_address: '',
  company_city: '',
  company_state: '',
  company_zip: '',
  company_country_id: '',
  company_country: '',
  registration_authority: '',
  registration_issue_date: '',
  registration_notes: '',
  company_logo_url: '',
  company_logo_path: '',
  company_logo_preview: '',
  contact_full_name: '',
  contact_primary_phone: '',
  contact_mobile_phone: '',
  contact_business_email: '',
  contact_website: '',
  subscription_plan: 'growth',
  security_username: '',
  security_password: '',
  security_password_confirm: '',
  terms_ack: false,
  privacy_ack: false,
  final_ack: false,
})

const activeStep = computed(() => steps[currentStep.value - 1] || steps[0])
const brandName = computed(() => form.value.company_name || authState.user?.company_name || 'Contractor')
const brandLogo = computed(() => form.value.company_logo_url || form.value.company_logo_preview || logoWhite)
const progressPercent = computed(() => Math.max(14, Math.round((currentStep.value / steps.length) * 100)))
const progressDashArray = computed(() => {
  const circumference = 2 * Math.PI * 46
  const offset = circumference - (progressPercent.value / 100) * circumference
  return `${circumference - offset} ${circumference}`
})
const selectedPlanLabel = computed(() => {
  const found = planOptions.find((plan) => plan.value === form.value.subscription_plan)
  return found?.label || 'Not selected'
})

const stepStatus = (step) => {
  if (step === currentStep.value) return 'In Progress'
  if (step < currentStep.value) return 'Completed'
  return 'Pending'
}

const goSupport = () => {
  router.push('/contact')
}

const goToStep = (step) => {
  if (step <= currentStep.value) {
    currentStep.value = step
  }
}

const prev = () => {
  if (currentStep.value > 1) {
    currentStep.value -= 1
  }
}

const resolveCountryNameById = (id) => {
  const found = countries.value.find((country) => Number(country.id) === Number(id))
  return found?.country_name || ''
}

const resolveCountryIdByName = (name) => {
  const normalized = String(name || '').trim().toLowerCase()
  if (!normalized) return ''
  const found = countries.value.find((country) => String(country.country_name || '').trim().toLowerCase() === normalized)
  return found?.id || ''
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

const applyAuthDefaults = () => {
  if (!form.value.company_name && authState.user?.company_name) {
    form.value.company_name = authState.user.company_name
  }
}

const firstErrorMessage = (responseData) => {
  if (responseData?.message) return responseData.message
  const errors = responseData?.errors
  if (!errors || typeof errors !== 'object') return 'Failed to save. Please try again.'
  const firstKey = Object.keys(errors)[0]
  if (!firstKey || !Array.isArray(errors[firstKey]) || !errors[firstKey].length) {
    return 'Failed to save. Please try again.'
  }
  return errors[firstKey][0]
}

const loadCountries = async () => {
  try {
    const { data } = await client.get('/countries')
    if (data?.success && Array.isArray(data.data)) {
      countries.value = data.data
      return
    }
  } catch {
    // Keep page usable if countries cannot be loaded.
  }
  countries.value = []
}

const loadSetup = async () => {
  try {
    const { data } = await client.get('/account-setup')
    if (!data?.success || !data?.data) return

    const setup = data.data
    const next = { ...form.value }

    Object.keys(next).forEach((key) => {
      if (setup[key] !== undefined && setup[key] !== null) {
        next[key] = setup[key]
      }
    })

    if (!next.company_country_id && next.company_country) {
      next.company_country_id = resolveCountryIdByName(next.company_country)
    }
    if (!next.company_country && next.company_country_id) {
      next.company_country = resolveCountryNameById(next.company_country_id)
    }

    form.value = next
    currentStep.value = Math.min(steps.length, Math.max(1, Number(setup.current_step) || 1))
  } catch {
    applyAuthDefaults()
  }
}

const onLogoSelect = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  error.value = ''
  success.value = false

  if (form.value.company_logo_preview?.startsWith('blob:')) {
    URL.revokeObjectURL(form.value.company_logo_preview)
  }

  form.value.company_logo_preview = URL.createObjectURL(file)
  const formData = new FormData()
  formData.append('logo', file)

  try {
    const { data } = await client.post('/account-setup/logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    if (data?.success) {
      form.value.company_logo_url = data.data.url
      form.value.company_logo_path = data.data.path
      form.value.company_logo_preview = data.data.url || form.value.company_logo_preview
    }
  } catch (e) {
    error.value = firstErrorMessage(e?.response?.data)
  }
}

const validateCurrentStep = () => {
  if (currentStep.value === 1) {
    if (!form.value.full_name) return 'Full name is required.'
    if (!form.value.company_name) return 'Company name is required.'
    if (!form.value.business_registration_number) return 'Business registration number is required.'
    if (!form.value.trade_service_type) return 'Trade / service type is required.'
  }

  if (currentStep.value === 2) {
    if (!form.value.company_address) return 'Street address is required.'
    if (!form.value.company_city) return 'City is required.'
    if (!form.value.company_state) return 'State / Province is required.'
    if (!form.value.company_zip) return 'ZIP / Postal Code is required.'
    if (!form.value.company_country_id) return 'Country is required.'
  }

  if (currentStep.value === 3) {
    if (!form.value.registration_authority) return 'Registration authority is required.'
    if (!form.value.registration_issue_date) return 'Registration issue date is required.'
  }

  if (currentStep.value === 4) {
    form.value.contact_primary_phone = normalizePhoneNumber(form.value.contact_primary_phone)
    form.value.contact_mobile_phone = normalizePhoneNumber(form.value.contact_mobile_phone)
    if (!form.value.contact_full_name) return 'Primary contact name is required.'
    if (!form.value.contact_business_email) return 'Business email is required.'
    if (!form.value.contact_primary_phone) return 'Primary phone is required.'
    if (!isE164PhoneNumber(form.value.contact_primary_phone)) return 'Phone number must be in E.164 format (e.g. +14165550100).'
    if (form.value.contact_mobile_phone && !isE164PhoneNumber(form.value.contact_mobile_phone)) {
      return 'Mobile phone must be in E.164 format (e.g. +14165550100).'
    }
  }

  if (currentStep.value === 5 && !form.value.subscription_plan) {
    return 'Please select a service plan.'
  }

  if (currentStep.value === 6) {
    if (!form.value.security_username) return 'Username is required.'
    if (!form.value.security_password) return 'Password is required.'
    if (form.value.security_password.length < 8) return 'Password must be at least 8 characters.'
    if (form.value.security_password !== form.value.security_password_confirm) return 'Passwords do not match.'
  }

  if (currentStep.value === 7) {
    if (!form.value.terms_ack) return 'You must accept the Terms and Conditions.'
    if (!form.value.privacy_ack) return 'You must accept the Privacy Policy.'
    if (!form.value.final_ack) return 'Please confirm the information is accurate.'
  }

  return ''
}

const buildPayload = () => {
  const payload = { ...form.value }
  payload.company_country_id = payload.company_country_id || null
  payload.company_country = payload.company_country_id ? resolveCountryNameById(payload.company_country_id) : ''

  if (currentStep.value !== 6) {
    delete payload.security_password
    delete payload.security_password_confirm
  }

  delete payload.company_logo_preview

  return payload
}

const saveStep = async () => {
  error.value = ''
  success.value = false

  const validationError = validateCurrentStep()
  if (validationError) {
    error.value = validationError
    return false
  }

  loading.value = true
  try {
    await client.post('/account-setup', {
      step: currentStep.value,
      data: buildPayload(),
    })
    success.value = true
    return true
  } catch (e) {
    error.value = firstErrorMessage(e?.response?.data)
    return false
  } finally {
    loading.value = false
  }
}

const next = async () => {
  const saved = await saveStep()
  if (!saved) return

  if (currentStep.value < steps.length) {
    currentStep.value += 1
    return
  }

  loading.value = true
  try {
    await client.post('/account-setup/complete')
    sessionStorage.setItem(
      'account_setup_summary',
      JSON.stringify({
        full_name: form.value.full_name,
        company_name: form.value.company_name,
        email: form.value.contact_business_email,
        plan: form.value.subscription_plan,
      })
    )

    try {
      await logout()
    } catch {
      // Ignore logout errors after successful completion.
    }

    clearToken()
    router.push('/account-setup/success')
  } catch (e) {
    error.value = firstErrorMessage(e?.response?.data)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadCountries()
  await loadSetup()
  applyAuthDefaults()
})

watch(
  () => authState.user?.company_name,
  () => {
    applyAuthDefaults()
  }
)
</script>


