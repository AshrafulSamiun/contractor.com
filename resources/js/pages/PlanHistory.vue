<template>
  <div class="pm-dashboard">
    <div class="container">
      <div class="pm-dashboard-header">
        <div>
          <h2>Plan History</h2>
          <p class="pm-muted">Track subscription plan changes over time.</p>
        </div>
        <div class="pm-dashboard-actions">
          <button class="btn btn-outline-primary" type="button" @click="reload">Refresh</button>
        </div>
      </div>

      <div class="pm-dash-card pm-panel">
        <div class="pm-panel-head">
          <div>
            <h4>Recent Plan Changes</h4>
            <p class="pm-muted">Latest 100 plan updates.</p>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table pm-dash-table">
            <thead>
              <tr>
                <th>From</th>
                <th>To</th>
                <th>Changed At</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in logs" :key="log.id">
                <td class="text-capitalize">{{ log.from_plan || '-' }}</td>
                <td class="text-capitalize">{{ log.to_plan || '-' }}</td>
                <td>{{ formatDate(log.created_at) }}</td>
              </tr>
              <tr v-if="!logs.length">
                <td colspan="3" class="text-center pm-muted">No plan changes yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import client from '../api/client'

const logs = ref([])

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

const loadHistory = async () => {
  try {
    const { data } = await client.get('/plans/history')
    if (data?.success) logs.value = data.data || []
  } catch {
    logs.value = []
  }
}

const reload = () => {
  loadHistory()
}

onMounted(loadHistory)
</script>
