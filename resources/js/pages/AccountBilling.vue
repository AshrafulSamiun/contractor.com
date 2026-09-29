<template>
  <AccountLayout bare>
    <main class="billing-page">
      <header class="billing-page__header"><h1>Billing &amp; Payments</h1><span>Last Updated: {{ formatDate(billing.updated_at) }}</span></header>

      <section><h2>1. Billing Summary</h2><div class="billing-card billing-summary">
        <Info label="Currency" :value="billing.currency || 'Not set'" />
        <Info label="Outstanding Balance" :value="money(billing.outstanding_balance, true)" />
        <Info label="Next Payment Date" :value="formatDate(billing.next_payment_date)" />
        <Info label="Next Payment Amount" :value="money(billing.next_payment_amount, true)" />
        <Info label="Tax Type" :value="billing.tax_rate ? `GST ${billing.tax_rate}%` : 'Not set'" />
      </div></section>

      <section><h2>2. Payment Method</h2><div class="billing-card billing-payment">
        <Info label="Card Type" :value="billing.card_type || 'Not configured'" />
        <Info label="Card Number" :value="billing.card_last_four ? `•••• ${billing.card_last_four}` : 'Not configured'" />
        <Info label="Expiry Date" :value="billing.card_expiry || 'Not configured'" />
        <Info label="Status" :value="billing.card_status || 'Not configured'" />
        <button class="billing-button billing-button--outline" type="button" @click="editing = !editing">{{ editing ? 'Cancel' : 'Update Payment Method' }}</button>
      </div>
      <form v-if="editing" class="billing-editor" @submit.prevent="savePaymentMethod">
        <input v-model="payment.primary_cardholder_name" placeholder="Cardholder name" />
        <input v-model="payment.primary_card_type" placeholder="Card type (e.g. Visa)" />
        <input v-model="payment.primary_card_last_four" maxlength="4" inputmode="numeric" placeholder="Last 4 digits" />
        <input v-model="payment.primary_card_expiry" maxlength="5" placeholder="MM/YY" />
        <button class="billing-button" :disabled="saving">{{ saving ? 'Saving...' : 'Save Payment Method' }}</button>
      </form>
      </section>

      <section><h2>3. Billing History &amp; Invoices</h2><div class="billing-card table-wrap"><table><thead><tr><th>Payment No.</th><th>Service Period</th><th>Payment Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
        <tr v-for="item in billing.payment_schedule || []" :key="item.payment_number"><td>{{ item.payment_number }}</td><td>{{ formatDate(item.service_period_start) }} – {{ formatDate(item.service_period_end) }}</td><td>{{ formatDate(item.auto_charge_date) }}</td><td>{{ money(item.amount) }}</td><td>{{ paymentStatus(item.auto_charge_date) }}</td></tr>
        <tr v-if="!(billing.payment_schedule || []).length"><td colspan="5" class="empty">No payment schedule has been saved in Account Setup yet.</td></tr>
      </tbody></table></div></section>
      <p v-if="message" class="billing-feedback">{{ message }}</p><p v-if="error" class="billing-feedback billing-feedback--error">{{ error }}</p>
      <div class="billing-actions"><button class="billing-button billing-button--outline" type="button" @click="downloadSchedule">Download Payment Schedule</button><button class="billing-button" type="button" :disabled="!billing.next_payment_amount">Pay Bill</button></div>
    </main>
  </AccountLayout>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, ref } from 'vue'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'

const billing = ref({})
const editing = ref(false); const saving = ref(false); const message = ref(''); const error = ref('')
const payment = ref({ primary_cardholder_name: '', primary_card_type: '', primary_card_last_four: '', primary_card_expiry: '' })
const Info = defineComponent({ props: { label: String, value: String }, setup: (props) => () => h('div', { class: 'billing-info' }, [h('span', props.label), h('strong', props.value)]) })
const formatDate = (value) => { if (!value) return 'Not scheduled'; const date = new Date(`${value}T00:00:00`); return Number.isNaN(date) ? 'Not scheduled' : new Intl.DateTimeFormat('en-CA', { month: 'long', day: 'numeric', year: 'numeric' }).format(date) }
const money = (value, tax = false) => { const amount = Number(value); if (!Number.isFinite(amount)) return 'Not scheduled'; return `${amount.toFixed(2)} ${billing.value.currency || 'CAD'}${tax ? ' + Tax' : ''}` }
const paymentStatus = (date) => new Date(`${date}T00:00:00`) < new Date() ? 'Scheduled / Past' : 'Scheduled'
const loadBilling = async () => { error.value = ''; try { const { data } = await client.get('/account/billing'); if (!data?.success) throw new Error(data?.message); billing.value = data.data || {}; payment.value = { primary_cardholder_name: '', primary_card_type: billing.value.card_type || '', primary_card_last_four: billing.value.card_last_four || '', primary_card_expiry: billing.value.card_expiry || '' } } catch (e) { error.value = e?.response?.data?.message || e?.message || 'Unable to load billing information.' } }
const savePaymentMethod = async () => { saving.value = true; error.value = ''; try { await client.put('/account/billing', payment.value); editing.value = false; message.value = 'Payment method updated.'; await loadBilling() } catch (e) { error.value = e?.response?.data?.message || 'Unable to update payment method.' } finally { saving.value = false } }
const downloadSchedule = () => { const rows = billing.value.payment_schedule || []; if (!rows.length) return; const csv = ['Payment No.,Service Start,Service End,Payment Date,Amount', ...rows.map((i) => [i.payment_number, i.service_period_start, i.service_period_end, i.auto_charge_date, i.amount].join(','))].join('\n'); const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv' })); const a = document.createElement('a'); a.href = url; a.download = 'payment-schedule.csv'; a.click(); URL.revokeObjectURL(url) }
onMounted(loadBilling)
</script>

<style scoped>
.billing-page{max-width:1280px;margin:auto;padding:28px 44px 36px;color:#082477;font-family:"Arial Narrow","Roboto Condensed",Arial,sans-serif}.billing-page__header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}.billing-page h1{margin:0;font-size:clamp(2rem,3vw,3.4rem);font-weight:800;letter-spacing:-.045em}.billing-page__header span{font-weight:700;font-size:1.05rem}.billing-page section{margin-bottom:22px}.billing-page h2{font-size:1.6rem;margin:0 0 9px;font-weight:800;letter-spacing:-.035em}.billing-card{border:1px solid #bac1d1;border-radius:8px;background:rgba(255,255,255,.35);padding:19px 28px}.billing-summary,.billing-payment{display:grid;grid-template-columns:repeat(5,1fr);gap:20px}.billing-payment{grid-template-columns:repeat(4,1fr) 1.45fr;align-items:center}.billing-info{display:grid;gap:12px}.billing-info span{font-weight:600;font-size:.94rem}.billing-info strong{font-size:1.4rem;white-space:nowrap}.billing-button{border:1px solid #0869f7;border-radius:6px;background:#0869f7;color:#fff;padding:11px 24px;font-size:1.1rem;white-space:nowrap}.billing-button--outline{background:transparent;color:#0869f7}.billing-editor{display:grid;grid-template-columns:1.2fr 1fr .8fr .8fr auto;gap:12px;margin-top:12px}.billing-editor input{border:1px solid #b9c0d0;border-radius:5px;padding:10px;color:#082477}.table-wrap{padding:0;overflow:auto}table{width:100%;border-collapse:collapse;min-width:760px}th,td{padding:13px 23px;border-bottom:1px solid #d2d5dd;text-align:left;font-size:.95rem}th{font-weight:700}.empty{text-align:center;color:#667085}.billing-actions{display:flex;gap:20px}.billing-feedback{font-weight:700;color:#087334}.billing-feedback--error{color:#b42318}@media(max-width:1000px){.billing-summary{grid-template-columns:repeat(3,1fr)}.billing-payment{grid-template-columns:repeat(3,1fr)}.billing-editor{grid-template-columns:1fr 1fr}}@media(max-width:700px){.billing-page{padding:22px 18px}.billing-page__header{align-items:flex-start;flex-direction:column;gap:8px}.billing-summary,.billing-payment{grid-template-columns:1fr 1fr;padding:18px}.billing-info strong{font-size:1.1rem}.billing-editor{grid-template-columns:1fr}.billing-actions{gap:10px}.billing-button{flex:1;padding:11px 9px;font-size:.95rem}}
</style>
