<template>
  <DeliveryOpsPage
    title="Lost / Damaged"
    subtitle="Track parcels that were lost or damaged."
    list-title="Lost / Damaged Parcels"
    list-subtitle="Incident records for lost or damaged items."
    :status-in="['lost', 'damaged']"
    :show-create="false"
    :allow-scan="true"
    default-status="lost"
  />
</template>

<script setup>
import DeliveryOpsPage from '../components/DeliveryOpsPage.vue'
</script>
