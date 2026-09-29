<template>
  <AccountLayout bare>
    <main class="plan-manager">
      <header class="plan-manager__header">
        <h1>Manage Service Plan</h1>
        <span>Last Updated: {{ formatDate(billing.updated_at) }}</span>
      </header>

      <section class="plan-section">
        <h2>1. Current Service Plan</h2>
        <div class="current-plan-card">
          <div v-for="item in currentPlanDetails" :key="item.label" class="current-plan-card__item">
            <span>{{ item.label }}</span>
            <strong>{{ item.value }}</strong>
          </div>
        </div>
      </section>

      <section class="plan-section plan-section--change">
        <h2>2. Change Service Plan</h2>
        <p class="plan-question">Do you want to upgrade or downgrade your service plan?</p>

        <div class="plan-options" role="radiogroup" aria-label="Choose a plan change">
          <button
            v-for="option in planOptions"
            :key="option.type"
            class="plan-option"
            :class="{ selected: selectedChange === option.type }"
            type="button"
            role="radio"
            :aria-checked="selectedChange === option.type"
            @click="selectedChange = option.type"
          >
            <span class="plan-option__title">
              <span class="plan-option__radio" aria-hidden="true"></span>
              {{ option.title }}
            </span>
            <span class="plan-option__summary">
              <span v-for="item in option.summary" :key="item.label" class="plan-option__row">
                <span>{{ item.label }}</span><b>{{ item.value }}</b>
              </span>
            </span>
            <span class="plan-option__divider"></span>
            <span class="plan-option__details">
              <span v-for="item in option.details" :key="item.label" class="plan-option__row">
                <span>{{ item.label }}</span><b>{{ item.value }}</b>
              </span>
            </span>
            <span class="plan-option__notice">
              <span class="plan-option__info">i</span>
              <span>{{ option.notice }}</span>
            </span>
          </button>
        </div>
      </section>

      <p v-if="error" class="plan-feedback plan-feedback--error">{{ error }}</p>
      <p v-else-if="message" class="plan-feedback">{{ message }}</p>
      <div class="plan-actions">
        <button class="plan-button plan-button--secondary" type="button" @click="cancelChange">Cancel</button>
        <button class="plan-button" type="button" :disabled="saving" @click="confirmChange">
          {{ saving ? 'Updating Plan...' : 'Confirm Plan Change' }}
        </button>
      </div>
    </main>
  </AccountLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'
import { authState, setUser } from '../store/auth'

const selectedChange = ref('upgrade')
const saving = ref(false)
const error = ref('')
const message = ref('')

const setupPlan = ref('')
const billing = ref({})
const planNames = { basic: 'Basic Plan', pro: 'Medium Plan', premium: 'Premium Plan', enterprise: 'Enterprise Plan' }
const currentPlanName = computed(() => planNames[setupPlan.value] || 'Not selected')
const billingCycleLabel = computed(() => billing.value.billing_cycle === 'annually' ? 'Yearly' : billing.value.billing_cycle === 'monthly' ? 'Monthly' : 'Not selected')
const paymentLabel = computed(() => billing.value.billing_cycle === 'annually' ? 'Yearly Payment' : 'Monthly Payment')
const formatDate = (value) => {
  if (!value) return 'Not scheduled'
  const parsed = new Date(`${value}T00:00:00`)
  return Number.isNaN(parsed.getTime()) ? 'Not scheduled' : new Intl.DateTimeFormat('en-CA', { month: 'long', day: 'numeric', year: 'numeric' }).format(parsed)
}
const formatCurrency = (value) => {
  const amount = Number(value)
  return Number.isFinite(amount) ? `${amount.toFixed(2)} CAD` : 'Not scheduled'
}
const currentPlanDetails = computed(() => [
  { label: 'Current Plan', value: currentPlanName.value },
  { label: paymentLabel.value, value: formatCurrency(billing.value.monthly_payment) },
  { label: 'Last Payment Date', value: formatDate(billing.value.last_payment_date) },
  { label: 'Service Charge Date', value: formatDate(billing.value.service_charge_date) },
  { label: 'Billing Cycle', value: billingCycleLabel.value },
  { label: 'Next Payment Date', value: formatDate(billing.value.next_payment_date) },
])
const daysRemaining = computed(() => {
  if (!billing.value.service_charge_date) return 'Not scheduled'
  const date = new Date(`${billing.value.service_charge_date}T00:00:00`)
  const today = new Date(); today.setHours(0, 0, 0, 0)
  return `${Math.max(0, Math.ceil((date - today) / 86400000))} days`
})

const planOptions = computed(() => [
  {
    type: 'upgrade', title: 'Upgrade',
    summary: [
      { label: 'Change Request Date', value: formatDate(new Date().toISOString().slice(0, 10)) }, { label: 'Days Remaining', value: daysRemaining.value },
      { label: 'New Plan', value: 'Medium Plan' }, { label: 'New Monthly Payment', value: '$90.00 CAD' },
      { label: 'Unused Basic Plan Credit (16 Days)', value: '($25.81)' }, { label: 'Prorated Medium Plan Charge (16 Days)', value: '$46.45' },
    ],
    details: [
      { label: 'Amount Due Today', value: '$20.64 + Tax' }, { label: 'Effective Date', value: 'Immediately After Payment' },
      { label: 'Service Charge Date', value: formatDate(billing.value.service_charge_date) }, { label: 'Next Regular Payment', value: '$90.00 + Tax' },
    ],
    notice: 'Your billing date stays the same. Pay only the prorated difference today, and the upgraded plan starts after payment is successful.',
  },
  {
    type: 'downgrade', title: 'Downgrade',
    summary: [
      { label: 'Change Request Date', value: formatDate(new Date().toISOString().slice(0, 10)) }, { label: 'Days Remaining', value: daysRemaining.value },
      { label: 'New Plan', value: 'Starter Plan' }, { label: 'New Monthly Payment', value: '$30.00 CAD' },
      { label: 'Monthly Payment Difference', value: '($20.00)' }, { label: 'Amount Due Today', value: '$0.00' },
    ],
    details: [
      { label: 'Effective Date', value: formatDate(billing.value.next_payment_date) }, { label: 'Service Charge Date', value: formatDate(billing.value.next_payment_date) },
      { label: 'Next Regular Payment', value: '$30.00 + Tax' }, { label: 'Monthly Savings', value: '($20.00)' },
    ],
    notice: 'Your current plan remains active until the next billing date. The lower plan and its new payment start on that date.',
  },
])

const cancelChange = () => { selectedChange.value = 'upgrade'; error.value = ''; message.value = '' }
const confirmChange = async () => {
  saving.value = true; error.value = ''; message.value = ''
  try {
    const plan = selectedChange.value === 'upgrade' ? 'pro' : 'basic'
    const { data } = await client.post('/plans/select', { plan })
    if (!data?.success) throw new Error(data?.message || 'Unable to update the service plan.')
    setupPlan.value = data.data?.selected_plan || plan
    if (authState.user) setUser({ ...authState.user, selected_plan: setupPlan.value })
    message.value = `Your ${selectedChange.value} request has been confirmed.`
  } catch (e) {
    error.value = e?.response?.data?.message || e?.message || 'Unable to update the service plan.'
  } finally { saving.value = false }
}

const loadCurrentPlan = async () => {
  error.value = ''
  try {
    const { data } = await client.get('/plans/current')
    if (!data?.success) throw new Error(data?.message || 'Unable to load the service plan.')
    setupPlan.value = data.data?.subscription_plan || ''
    billing.value = data.data || {}
  } catch (e) {
    error.value = e?.response?.data?.message || e?.message || 'Unable to load the service plan.'
  }
}

onMounted(loadCurrentPlan)
</script>

<style scoped>
.plan-manager { max-width: 1280px; margin: 0 auto; padding: 28px 44px 36px; color: #082477; font-family: "Arial Narrow", "Roboto Condensed", Arial, sans-serif; }
.plan-manager__header { display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 24px; }
.plan-manager h1 { margin: 0; font-size: clamp(2rem, 3vw, 3.4rem); line-height: 1; font-weight: 800; letter-spacing: -.045em; }
.plan-manager__header span { font-size: 1.05rem; font-weight: 700; }
.plan-section h2 { margin: 0 0 7px; font-size: 1.6rem; font-weight: 800; letter-spacing: -.035em; }
.current-plan-card { display: grid; grid-template-columns: repeat(6, 1fr); gap: 20px; padding: 20px 30px; border: 1px solid #bac1d1; border-radius: 8px; background: rgba(255,255,255,.38); }
.current-plan-card__item { display: grid; gap: 13px; }.current-plan-card__item span { font-weight: 600; font-size: .94rem; }.current-plan-card__item strong { font-size: 1.45rem; white-space: nowrap; }
.plan-section--change { margin-top: 24px; padding-top: 14px; border-top: 1px solid #d4d7dc; }.plan-question { margin: 0 0 8px; font-size: 1.08rem; font-weight: 600; }
.plan-options { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 20px; }.plan-option { display: flex; flex-direction: column; min-height: 475px; padding: 15px 21px 19px; text-align: left; color: #082477; border: 1px solid #bdc3d1; border-radius: 8px; background: rgba(255,255,255,.3); cursor: pointer; }.plan-option.selected { border: 2px solid #1268f5; padding: 14px 20px 18px; box-shadow: 0 0 0 1px rgba(18,104,245,.08); }.plan-option__title { display: flex; align-items: center; gap: 16px; margin-bottom: 12px; font-size: 1.65rem; font-weight: 800; }.plan-option__radio { width: 29px; height: 29px; flex: 0 0 29px; border: 3px solid #aeb4c2; border-radius: 50%; }.selected .plan-option__radio { border-color: #0869ff; box-shadow: inset 0 0 0 5px #fff; background: #0869ff; }.plan-option__summary, .plan-option__details { display: grid; gap: 7px; }.plan-option__row { display: grid; grid-template-columns: minmax(0, 1fr) 46%; gap: 10px; font-size: .97rem; line-height: 1.25; }.plan-option__row span, .plan-option__row b { font-weight: 600; }.plan-option__row b { text-align: left; }.plan-option__divider { height: 1px; margin: 10px 0 12px; background: #ccd0d8; }.plan-option__notice { display: flex; gap: 16px; align-items: flex-start; margin-top: 15px; padding: 13px 16px; border: 1px solid #8fb7ff; border-radius: 6px; line-height: 1.35; font-size: .94rem; font-weight: 600; }.plan-option__info { display: grid; place-items: center; flex: 0 0 27px; width: 27px; height: 27px; border-radius: 50%; background: #0869f7; color: #fff; font-family: Georgia, serif; font-weight: 700; }.plan-actions { display: flex; gap: 25px; margin-top: 17px; }.plan-button { min-width: 250px; padding: 11px 24px; border: 1px solid #0869f7; border-radius: 6px; background: #0869f7; color: #fff; font-size: 1.23rem; font-weight: 500; }.plan-button:disabled { opacity: .65; cursor: wait; }.plan-button--secondary { min-width: 140px; background: transparent; color: #0869f7; }.plan-feedback { margin: 12px 0 -7px; color: #087334; font-weight: 700; }.plan-feedback--error { color: #b42318; }
@media (max-width: 1100px) { .current-plan-card { grid-template-columns: repeat(3, 1fr); }.plan-options { grid-template-columns: 1fr; }.plan-option { min-height: 0; } }
@media (max-width: 700px) { .plan-manager { padding: 22px 18px 30px; }.plan-manager__header { align-items: flex-start; flex-direction: column; margin-bottom: 18px; }.plan-manager__header span { font-size: .9rem; }.current-plan-card { grid-template-columns: 1fr 1fr; padding: 18px; gap: 19px 13px; }.current-plan-card__item strong { font-size: 1.15rem; }.plan-section h2 { font-size: 1.35rem; }.plan-option { padding: 15px; }.plan-option.selected { padding: 14px; }.plan-option__row { grid-template-columns: 1fr 1fr; font-size: .86rem; }.plan-option__title { font-size: 1.4rem; }.plan-actions { gap: 10px; }.plan-button { min-width: 0; flex: 1; font-size: 1rem; padding: 11px 8px; } }
</style>
