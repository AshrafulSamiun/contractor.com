<template>
  <AccountLayout bare>
    <main class="tax-report">
      <header><h1>Tax Report</h1><b>Report Date: {{ pretty(filters.to_date) }}</b></header>

      <h2>Tax Report Filter</h2>
      <form class="card filters" @submit.prevent="loadReport">
        <label>From Date<input v-model="filters.from_date" type="date" /></label>
        <label>To Date<input v-model="filters.to_date" type="date" /></label>
        <label>Tax Type<select disabled><option>{{ report.tax_name || 'GST' }} {{ report.tax_rate || 5 }}%</option></select></label>
        <label>Payment Status<select v-model="filters.status"><option value="paid">Paid</option><option value="all">All</option></select></label>
        <button :disabled="loading">{{ loading ? 'Generating…' : 'Generate Tax Report' }}</button>
      </form>

      <div class="card company-details">
        <Info label="Company Name" :value="report.company_name || 'Not available'" />
        <Info label="Account Number" :value="report.account_number || 'Not available'" />
        <Info label="Report Period" :value="`${pretty(filters.from_date)} – ${pretty(filters.to_date)}`" />
        <Info label="Country" :value="report.country || 'Not set'" />
        <Info label="Currency" :value="report.currency || 'CAD'" />
        <Info label="Tax Number" :value="report.tax_number || 'Not set'" />
      </div>

      <h2>Tax Details</h2>
      <div class="card table-wrap">
        <table>
          <thead><tr><th>No.</th><th>Date</th><th>Invoice No.</th><th>Details</th><th>Invoice Subtotal</th><th>Sales Tax</th><th>Tax Rate</th><th>Total Tax</th><th>Total Payment</th><th>Status</th></tr></thead>
          <tbody>
            <tr v-for="(row, index) in report.rows || []" :key="row.invoice_number"><td>{{ index + 1 }}</td><td>{{ pretty(row.date) }}</td><td>{{ row.invoice_number }}</td><td>{{ row.details }}</td><td>{{ money(row.subtotal) }}</td><td>{{ row.tax_name }}</td><td>{{ row.tax_rate }}%</td><td>{{ money(row.tax) }}</td><td>{{ money(row.total_payment) }}</td><td><span class="paid">{{ row.status }}</span></td></tr>
            <tr v-if="!(report.rows || []).length"><td colspan="10" class="empty">No paid service invoices were found for this report period.</td></tr>
          </tbody>
          <tfoot v-if="(report.rows || []).length"><tr><th colspan="4">TOTAL</th><th>{{ money(report.totals?.subtotal) }}</th><th>—</th><th>—</th><th>{{ money(report.totals?.tax) }}</th><th>{{ money(report.totals?.total_payment) }}</th><th></th></tr></tfoot>
        </table>
      </div>

      <div class="actions"><button class="outline" type="button" @click="download">⇩ Download Tax Report</button><button class="outline" type="button" @click="print">▣ Print</button><button type="button" @click="emailReport">✉ Email Tax Report</button></div>
      <p v-if="message" class="message">{{ message }}</p><p v-if="error" class="error">{{ error }}</p>
    </main>
  </AccountLayout>
</template>

<script setup>
import { defineComponent, h, onMounted, ref } from 'vue'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'

const date = new Date()
const dateValue = (value) => new Date(value.getFullYear(), value.getMonth(), value.getDate()).toISOString().slice(0, 10)
const filters = ref({ from_date: dateValue(new Date(date.getFullYear(), date.getMonth(), 1)), to_date: dateValue(date), status: 'paid' })
const report = ref({ rows: [], totals: {}, currency: 'CAD' })
const loading = ref(false), error = ref(''), message = ref('')
const Info = defineComponent({ props: { label: String, value: String }, setup: (props) => () => h('div', { class: 'info' }, [h('span', props.label), h('strong', props.value)]) })
const pretty = (value) => value ? new Intl.DateTimeFormat('en-CA', { month: 'long', day: 'numeric', year: 'numeric' }).format(new Date(`${value}T00:00:00`)) : 'Not set'
const money = (value) => Number(value || 0).toLocaleString('en-CA', { style: 'currency', currency: report.value.currency || 'CAD' })

const loadReport = async () => { loading.value = true; error.value = ''; message.value = ''; try { const { data } = await client.get('/account/tax-report', { params: filters.value }); if (!data?.success) throw new Error(data?.message || 'Unable to generate tax report.'); report.value = data.data } catch (e) { error.value = e?.response?.data?.message || e?.message || 'Unable to generate tax report.' } finally { loading.value = false } }
const print = () => window.print()
const download = () => { const rows = report.value.rows || []; const csv = [['Tax Report', `${filters.value.from_date} to ${filters.value.to_date}`], ['Date', 'Invoice No.', 'Details', 'Subtotal', 'Tax Type', 'Tax Rate', 'Tax', 'Total Payment', 'Status'], ...rows.map((row) => [row.date, row.invoice_number, row.details, row.subtotal, row.tax_name, `${row.tax_rate}%`, row.tax, row.total_payment, row.status])].map(row => row.map(value => `"${String(value ?? '').replaceAll('"', '""')}"`).join(',')).join('\n'); const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' })); const link = document.createElement('a'); link.href = url; link.download = `tax-report-${filters.value.from_date}-to-${filters.value.to_date}.csv`; link.click(); URL.revokeObjectURL(url) }
const emailReport = () => { message.value = 'Tax report email delivery will be available when an accounts email address is configured.' }
onMounted(loadReport)
</script>

<style scoped>
.tax-report{max-width:1280px;margin:auto;padding:22px 44px 36px;color:#082477;font-family:"Arial Narrow","Roboto Condensed",Arial,sans-serif}.tax-report header{display:flex;align-items:center;justify-content:space-between}.tax-report h1{font-size:3.35rem;margin:0 0 10px;font-weight:800;letter-spacing:-.05em}.tax-report header b{font-size:1.1rem}.tax-report h2{font-size:1.5rem;margin:8px 0}.card{border:1px solid #b9c1d2;border-radius:8px;background:rgba(255,255,255,.35)}.filters{display:grid;grid-template-columns:1fr 1fr .85fr .85fr auto;gap:24px;padding:14px 24px;align-items:end}.filters label{display:grid;gap:5px;font-weight:600}.filters input,.filters select{padding:10px 12px;border:1px solid #aeb7ca;border-radius:6px;background:#fff;color:#082477;font-size:1rem}.filters button,.actions button{border:1px solid #0869f7;border-radius:6px;background:#0869f7;color:#fff;padding:12px 22px;font-size:1rem;white-space:nowrap}.company-details{display:grid;grid-template-columns:repeat(6,1fr);gap:18px;padding:18px 20px;margin:18px 0}.info{display:grid;gap:10px;text-align:center}.info span{font-weight:600}.info strong{font-size:1.18rem;white-space:nowrap}.table-wrap{overflow:auto}table{width:100%;min-width:1080px;border-collapse:collapse}th,td{padding:13px 12px;border-bottom:1px solid #d1d5dd;text-align:center;white-space:nowrap}th{font-weight:700}td:nth-child(4){text-align:left;white-space:normal}.empty{padding:26px;color:#667085}tfoot th{font-size:1.05rem}.paid{font-weight:700;color:#087334}.actions{display:flex;gap:20px;margin-top:16px}.actions .outline{background:transparent;color:#0869f7}.message,.error{font-weight:700}.message{color:#087334}.error{color:#b42318}@media(max-width:1000px){.filters{grid-template-columns:1fr 1fr 1fr}.company-details{grid-template-columns:repeat(3,1fr)}}@media(max-width:600px){.tax-report{padding:20px 18px}.tax-report header{display:block}.tax-report h1{font-size:2.3rem}.filters,.company-details{grid-template-columns:1fr}.actions{gap:8px}.actions button{padding:10px 8px;font-size:.85rem}}@media print{.filters,.actions,.pm-dashboard-topbar,.pm-sidebar{display:none!important}.tax-report{padding:0;max-width:none}}
</style>
