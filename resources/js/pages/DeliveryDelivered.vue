<template>
  <DeliveryOpsPage
    title="Delivered"
    subtitle="Track parcels that have been delivered."
    list-title="Delivered Parcels"
    list-subtitle="Delivery completion records."
    status="delivered"
    :show-create="false"
    :allow-scan="true"
    default-status="delivered"
  />
</template>

<script setup>
import DeliveryOpsPage from '../components/DeliveryOpsPage.vue'
</script>
