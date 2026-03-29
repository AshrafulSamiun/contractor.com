<template>
  <AccountLayout title="Account Status" subtitle="Track account health, plan, and verification status.">
    <div class="pm-dash-card pm-panel mb-4">
      <div class="pm-panel-head">
        <div>
          <h4>Account Overview</h4>
          <p class="pm-muted">Current status and access level.</p>
        </div>
      </div>
      <div class="pm-stats-grid">
        <div class="pm-stat-card">
          <div class="pm-stat-label">Status</div>
          <div class="pm-stat-value">{{ statusLabel }}</div>
        </div>
        <div class="pm-stat-card">
          <div class="pm-stat-label">Role</div>
          <div class="pm-stat-value text-capitalize">{{ status.role || '-' }}</div>
        </div>
        <div class="pm-stat-card">
          <div class="pm-stat-label">Plan</div>
          <div class="pm-stat-value text-capitalize">{{ status.selected_plan || 'N/A' }}</div>
        </div>
        <div class="pm-stat-card">
          <div class="pm-stat-label">Subscription</div>
          <div class="pm-stat-value text-capitalize">{{ status.stripe_subscription_status || 'N/A' }}</div>
        </div>
        <div class="pm-stat-card">
          <div class="pm-stat-label">Setup Completed</div>
          <div class="pm-stat-value">{{ formatDate(status.account_setup_completed_at) }}</div>
        </div>
      </div>
    </div>

    <div class="pm-dash-card pm-panel">
      <h4 class="mb-3">Billing Snapshot</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="pm-field-label">Billing Cycle</label>
          <div class="form-control">{{ billing.billing_cycle || 'Monthly' }}</div>
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Auto Renew</label>
          <div class="form-control">{{ billing.auto_renew ? 'Enabled' : 'Disabled' }}</div>
        </div>
        <div class="col-md-4">
          <label class="pm-field-label">Invoice Email</label>
          <div class="form-control">{{ billing.invoice_email || 'Not set' }}</div>
        </div>
      </div>
    </div>
  </AccountLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import AccountLayout from '../components/AccountLayout.vue'
import client from '../api/client'

const status = ref({
  role: '',
  is_active: true,
  selected_plan: '',
  account_setup_completed_at: null,
})
const billing = ref({})

const statusLabel = computed(() => (status.value.is_active ? 'Active' : 'Suspended'))

const formatDate = (value) => {
  if (!value) return 'Not completed'
  return new Date(value).toLocaleDateString()
}

const loadStatus = async () => {
  try {
    const { data } = await client.get('/account/status')
    if (data?.success) {
      status.value = data.data || {}
      billing.value = data.data?.billing || {}
    }
  } catch {
    // ignore
  }
}

onMounted(loadStatus)
</script>
