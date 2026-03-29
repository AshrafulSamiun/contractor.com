<template>
  <AccountLayout title="Billing & Subscription" subtitle="Manage billing details, invoice preferences, and renewal.">
    <template #actions>
      <button class="btn btn-primary" type="button" @click="saveBilling">Save Billing</button>
    </template>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Billing Address</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pm-field-label">Billing Address</label>
          <input v-model="form.billing_address" class="form-control" placeholder="Street address" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">City</label>
          <input v-model="form.billing_city" class="form-control" placeholder="City" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">State</label>
          <input v-model="form.billing_state" class="form-control" placeholder="State" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">ZIP</label>
          <input v-model="form.billing_zip" class="form-control" placeholder="ZIP" />
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Country</label>
          <select v-model.number="form.billing_country_id" class="form-control">
            <option value="">Select country</option>
            <option v-for="c in countries" :key="c.id" :value="c.id">
              {{ c.country_name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Subscription</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="pm-field-label">Billing Cycle</label>
          <select v-model="form.billing_cycle" class="form-control">
            <option value="monthly">Monthly</option>
            <option value="annual">Annual</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Auto Renew</label>
          <select v-model="form.auto_renew" class="form-control">
            <option :value="true">Enabled</option>
            <option :value="false">Disabled</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Invoice Email</label>
          <input v-model="form.invoice_email" class="form-control" type="email" placeholder="billing@company.com" />
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Stripe Subscription (Test)</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="pm-field-label">Plan</label>
          <select v-model="stripePlan" class="form-control">
            <option value="basic">Basic</option>
            <option value="standard">Standard</option>
            <option value="enterprise">Enterprise</option>
          </select>
        </div>
        <div class="col-md-8 d-flex align-items-end gap-2">
          <button class="btn btn-outline-primary" type="button" @click="startCheckout" :disabled="stripeLoading">
            {{ stripeLoading ? 'Redirecting...' : 'Start Test Subscription' }}
          </button>
          <button class="btn btn-light" type="button" @click="openPortal" :disabled="stripeLoading">
            Manage Billing
          </button>
        </div>
      </div>
      <div class="pm-muted mt-2">
        Use Stripe test cards (e.g., 4242 4242 4242 4242) during checkout.
      </div>
      <div v-if="stripeError" class="text-danger mt-2">{{ stripeError }}</div>
    </div>

    <div class="pm-dash-card pm-panel mb-4">
      <h4 class="mb-3">Payment Method</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pm-field-label">Payment Method</label>
          <input v-model="form.payment_method" class="form-control" placeholder="Visa / Mastercard" />
        </div>
        <div class="col-md-3">
          <label class="pm-field-label">Last 4 Digits</label>
          <input v-model="form.last4" class="form-control" placeholder="1234" />
        </div>
        <div class="col-md-3">
          <label class="pm-field-label">Tax ID</label>
          <input v-model="form.tax_id" class="form-control" placeholder="Tax ID" />
        </div>
      </div>
      <div v-if="message" class="text-success mt-3">{{ message }}</div>
      <div v-if="error" class="text-danger mt-3">{{ error }}</div>
    </div>

    <div class="pm-dash-card pm-panel">
      <h4 class="mb-3">Recent Invoices</h4>
      <div class="table-responsive">
        <table class="table pm-dash-table">
          <thead>
            <tr>
              <th>Invoice</th>
              <th>Status</th>
              <th>Amount Due</th>
              <th>Period</th>
              <th>Link</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices" :key="invoice.id">
              <td>{{ invoice.stripe_invoice_id }}</td>
              <td>
                <span :class="['pm-status-pill', invoice.status === 'paid' ? 'success' : 'warning']">
                  {{ invoice.status || 'unknown' }}
                </span>
              </td>
              <td>{{ formatAmount(invoice.amount_due, invoice.currency) }}</td>
              <td>{{ formatPeriod(invoice.period_start, invoice.period_end) }}</td>
              <td>
                <a v-if="invoice.hosted_invoice_url" :href="invoice.hosted_invoice_url" target="_blank">View</a>
              </td>
            </tr>
            <tr v-if="!invoices.length">
              <td colspan="5" class="text-center pm-muted">No invoices yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AccountLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'
import { setFlash } from '../store/flash'

const form = ref({
  billing_address: '',
  billing_city: '',
  billing_state: '',
  billing_zip: '',
  billing_country_id: '',
  billing_country: '',
  tax_id: '',
  invoice_email: '',
  billing_cycle: 'monthly',
  auto_renew: true,
  payment_method: '',
  last4: '',
})

const countries = ref([])
const message = ref('')
const error = ref('')
const stripePlan = ref('basic')
const stripeLoading = ref(false)
const stripeError = ref('')
const invoices = ref([])

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

const loadBilling = async () => {
  try {
    const { data } = await client.get('/account/billing')
    if (data?.success && data.data) {
      form.value = { ...form.value, ...data.data }
      if (!form.value.billing_country_id && form.value.billing_country) {
        form.value.billing_country_id = resolveCountryIdByName(form.value.billing_country)
      }
      if (form.value.billing_country_id && !form.value.billing_country) {
        form.value.billing_country = resolveCountryNameById(form.value.billing_country_id)
      }
    }
    const invoiceRes = await client.get('/account/invoices')
    if (invoiceRes?.data?.success) {
      invoices.value = invoiceRes.data.data || []
    }
  } catch {
    // ignore
  }
}

const saveBilling = async () => {
  message.value = ''
  error.value = ''
  try {
    const payload = { ...form.value }
    if (payload.billing_country_id) {
      payload.billing_country = resolveCountryNameById(payload.billing_country_id) || payload.billing_country || ''
    } else if (payload.billing_country) {
      payload.billing_country_id = resolveCountryIdByName(payload.billing_country) || null
    } else {
      payload.billing_country = null
      payload.billing_country_id = null
    }
    await client.put('/account/billing', payload)
    message.value = 'Billing details saved.'
    setFlash('Billing updated.', 'success', 2000)
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to save billing.'
  }
}

const startCheckout = async () => {
  stripeError.value = ''
  stripeLoading.value = true
  try {
    const { data } = await client.post('/billing/checkout', { plan: stripePlan.value })
    if (data?.success && data.data?.url) {
      window.location.href = data.data.url
      return
    }
    stripeError.value = data?.message || 'Unable to start checkout.'
  } catch (e) {
    stripeError.value = e?.response?.data?.message || 'Stripe checkout failed.'
  } finally {
    stripeLoading.value = false
  }
}

const openPortal = async () => {
  stripeError.value = ''
  stripeLoading.value = true
  try {
    const { data } = await client.post('/billing/portal')
    if (data?.success && data.data?.url) {
      window.location.href = data.data.url
      return
    }
    stripeError.value = data?.message || 'Unable to open portal.'
  } catch (e) {
    stripeError.value = e?.response?.data?.message || 'Stripe portal failed.'
  } finally {
    stripeLoading.value = false
  }
}

const formatAmount = (value, currency) => {
  if (!value) return '-'
  const amount = value / 100
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: (currency || 'USD').toUpperCase(),
  }).format(amount)
}

const formatPeriod = (start, end) => {
  if (!start && !end) return '-'
  const startLabel = start ? new Date(start).toLocaleDateString() : ''
  const endLabel = end ? new Date(end).toLocaleDateString() : ''
  return `${startLabel} - ${endLabel}`.trim()
}

onMounted(async () => {
  await loadCountries()
  await loadBilling()
})
</script>
