<template>
  <DeliveryOpsPage
    title="New Arrival"
    subtitle="Log incoming parcels and start tracking."
    list-title="Pending Arrivals"
    list-subtitle="Recently received parcels awaiting pickup."
    status="pending"
    :show-create="true"
    :allow-scan="true"
    default-status="pending"
  />
</template>

<script setup>
import DeliveryOpsPage from '../components/DeliveryOpsPage.vue'
</script>
