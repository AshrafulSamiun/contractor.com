<template>
  <SuperAdminLayout>
    <section class="dashboard-page">
      <header class="dashboard-header">
        <div>
          <h1>Dashboard</h1>
          <p>Welcome back, Super Admin. Here’s what’s happening in your business today.</p>
        </div>
        <label class="date-control"><span aria-hidden="true">▣</span><input v-model="selectedDate" type="date" /></label>
      </header>

      <p v-if="error" class="error">{{ error }}</p>
      <h2 class="summary-title"><span aria-hidden="true">▱</span> 1. Heads-Up Summary</h2>

      <div class="summary-grid">
        <article v-for="card in cards" :key="card.title" class="summary-card">
          <h3><span class="card-icon" :class="card.icon" aria-hidden="true">{{ card.symbol }}</span>{{ card.title }}</h3>
          <dl>
            <div v-for="row in card.rows" :key="row.label">
              <dt>{{ row.label }}</dt>
              <dd :class="row.tone || ''">{{ row.value }}</dd>
            </div>
          </dl>
        </article>
      </div>
    </section>
  </SuperAdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import SuperAdminLayout from '../components/SuperAdminLayout.vue'
import client from '../api/client'

const data = ref({ summary: {}, heads_up: {} })
const error = ref('')
const selectedDate = ref(new Date().toISOString().slice(0, 10))
const summary = computed(() => data.value.summary || {})
const headsUp = computed(() => data.value.heads_up || {})
const amount = value => Number(value || 0).toLocaleString('en-CA', { style: 'currency', currency: 'CAD' })
const count = value => Number(value || 0).toLocaleString('en-CA')

const cards = computed(() => [
  { title: 'A. Sales & Payments', symbol: '$', icon: 'sales', rows: [
    { label: 'Sales Today', value: amount(headsUp.value.sales_today) },
    { label: 'Sales Last Week', value: amount(headsUp.value.sales_last_week) },
    { label: 'Sales Last Month', value: amount(summary.value.monthly_revenue) },
    { label: 'Payments Received Today', value: amount(headsUp.value.payments_today) },
    { label: 'Outstanding Customer Balances', value: amount(headsUp.value.outstanding_balance) },
    { label: 'NSF Payments', value: count(headsUp.value.nsf_payments), tone: 'warn' },
    { label: 'Failed Payments', value: count(summary.value.failed_payments), tone: 'danger' },
    { label: 'Scheduled Payments Due', value: amount(headsUp.value.scheduled_due) },
    { label: 'Payment Gateway Issues', value: count(headsUp.value.gateway_issues), tone: 'danger' },
  ] },
  { title: 'B. Customer Accounts', symbol: '♙', icon: 'customers', rows: [
    { label: 'New Accounts Under Review', value: count(headsUp.value.pending_accounts), tone: 'badge amber' },
    { label: 'Active Customer Accounts', value: count(summary.value.active_customers) },
    { label: 'Suspended Customer Accounts', value: count(headsUp.value.suspended_accounts), tone: 'warn' },
    { label: 'Customers with Payment Issues', value: count(summary.value.failed_payments), tone: 'warn' },
    { label: 'Requests to Cancel Accounts', value: count(headsUp.value.cancel_requests), tone: 'warn' },
    { label: 'Items Requiring Owner Approval', value: count(headsUp.value.pending_accounts), tone: 'warn' },
  ] },
  { title: 'C. Users & Licences', symbol: '♙', icon: 'users', rows: [
    { label: 'Users Online Now', value: count(headsUp.value.users_online), tone: 'badge blue' },
    { label: 'Failed Login Attempts', value: count(headsUp.value.failed_logins), tone: 'danger' },
    { label: 'Locked User Accounts', value: count(headsUp.value.locked_users), tone: 'warn' },
    { label: 'Password Reset Requests', value: count(headsUp.value.password_resets), tone: 'blue' },
    { label: 'Unknown Device Attempts', value: count(headsUp.value.unknown_devices), tone: 'warn' },
    { label: 'Licence-Sharing Suspects', value: count(headsUp.value.licence_sharing), tone: 'warn' },
  ] },
  { title: 'D. Security & System', symbol: '♢', icon: 'security', rows: [
    { label: 'Critical Security Alerts', value: count(headsUp.value.critical_alerts), tone: 'danger' },
    { label: 'Internal Cybersecurity Alerts', value: count(headsUp.value.internal_alerts), tone: 'warn' },
    { label: 'External Cybersecurity Alerts', value: count(headsUp.value.external_alerts), tone: 'warn' },
    { label: 'System Issues', value: count(headsUp.value.system_issues), tone: 'warn' },
    { label: 'Server or Database Issues', value: count(headsUp.value.server_issues), tone: 'warn' },
    { label: 'Backup Status', value: headsUp.value.backup_status || 'Healthy', tone: 'badge healthy' },
  ] },
  { title: 'E. Customer Service', symbol: '♧', icon: 'service', rows: [
    { label: 'Urgent Customer Messages', value: count(headsUp.value.urgent_messages), tone: 'danger' },
    { label: 'Open High-Priority Support Tickets', value: count(headsUp.value.high_priority_tickets), tone: 'warn' },
    { label: 'Unanswered Support Tickets', value: count(headsUp.value.unanswered_tickets), tone: 'warn' },
    { label: 'Customer Complaints', value: count(headsUp.value.customer_complaints), tone: 'warn' },
  ] },
])

const load = async () => { error.value = ''; try { data.value = (await client.get('/super-admin/dashboard')).data.data || {} } catch (e) { error.value = e?.response?.data?.message || 'Unable to load platform dashboard.' } }
onMounted(load)
</script>

<style>
.dashboard-page{color:#071b4d;font-family:Roboto,Arial,sans-serif}.dashboard-page .dashboard-header{display:flex!important;justify-content:space-between;align-items:flex-start;gap:24px;margin:1px 5px 48px}.dashboard-page .dashboard-header h1{margin:0;font-size:2.55rem;line-height:1.08;letter-spacing:-.05em}.dashboard-page .dashboard-header p{margin:18px 0 0;color:#41557f;font-size:1.05rem}.dashboard-page .date-control{display:flex;align-items:center;gap:12px;min-width:185px;margin-top:82px;padding:12px 15px;border:1px solid #d7deec;border-radius:8px;background:#fff;color:#061a4d;font-size:1.25rem}.dashboard-page .date-control span{font-size:1.35rem;color:#061a4d}.dashboard-page .date-control input{min-width:0;border:0;outline:0;background:transparent;color:#071b4d;font:600 .95rem Roboto,Arial}.dashboard-page .summary-title{display:flex;align-items:center;gap:13px;margin:0 5px 29px;font-size:1.8rem;letter-spacing:-.04em}.dashboard-page .summary-title span{display:grid;place-items:center;width:34px;height:34px;border:3px solid #0757ff;border-radius:7px;color:#0757ff;font-size:1.75rem;line-height:1}.dashboard-page .summary-grid{display:grid!important;grid-template-columns:repeat(5,minmax(190px,1fr))!important;gap:20px!important}.dashboard-page .summary-card{display:block!important;min-width:0;min-height:500px;padding:27px 16px 18px;border:1px solid #dfe4ef;border-radius:10px;background:#fff;box-shadow:0 1px 4px rgba(7,27,77,.025)}.dashboard-page .summary-card h3{display:flex;align-items:center;gap:11px;min-height:48px;margin:0 0 31px;color:#0754f6;font-size:.94rem;line-height:1.15}.dashboard-page .card-icon{display:grid;place-items:center;width:32px;height:32px;border:2px solid #0757ff;border-radius:50%;font-size:1.22rem;font-weight:700}.dashboard-page .card-icon.security{border-radius:9px}.dashboard-page dl{margin:0}.dashboard-page dl>div{display:flex!important;justify-content:space-between;gap:8px;align-items:center;min-height:36px;margin:4px 0}.dashboard-page dt{font-size:.79rem;line-height:1.2}.dashboard-page dd{margin:0;white-space:nowrap;font-size:.83rem;font-weight:700}.dashboard-page .danger{color:#f10b0b}.dashboard-page .warn{color:#fb6a00}.dashboard-page .blue{color:#0757ff}.dashboard-page .badge{padding:7px 8px;border-radius:6px}.dashboard-page .amber{border:1px solid #ffd19d;background:#fff1dd;color:#f17100}.dashboard-page .blue.badge{border:1px solid #b7d3ff;background:#eaf3ff;color:#0757ff}.dashboard-page .healthy{border:1px solid #b6e6c0;background:#e9faeb;color:#168123}.dashboard-page .error{margin:0 5px 16px;color:#c51616;font-weight:700}@media(max-width:1360px){.dashboard-page .summary-grid{grid-template-columns:repeat(3,minmax(220px,1fr))!important}.dashboard-page .summary-card{min-height:350px}}@media(max-width:900px){.dashboard-page .dashboard-header{margin-bottom:30px}.dashboard-page .summary-grid{grid-template-columns:repeat(2,minmax(220px,1fr))!important}.dashboard-page .date-control{margin-top:0}}@media(max-width:620px){.dashboard-page .dashboard-header{display:block!important}.dashboard-page .date-control{width:max-content;margin-top:20px}.dashboard-page .dashboard-header h1{font-size:2.1rem}.dashboard-page .dashboard-header p{font-size:.94rem}.dashboard-page .summary-title{font-size:1.45rem}.dashboard-page .summary-grid{grid-template-columns:1fr!important}.dashboard-page .summary-card{min-height:0}}
</style>
