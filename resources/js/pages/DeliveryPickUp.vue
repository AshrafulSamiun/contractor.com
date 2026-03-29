<template>
  <DeliveryOpsPage
    title="Pick Up"
    subtitle="Confirm parcel pickup and update statuses."
    list-title="Ready for Pickup"
    list-subtitle="Parcels marked as picked up."
    status="picked_up"
    :show-create="false"
    :allow-scan="true"
    default-status="picked_up"
  />
</template>

<script setup>
import DeliveryOpsPage from '../components/DeliveryOpsPage.vue'
</script>
