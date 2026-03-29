<template>
  <DeliveryOpsPage
    title="Rejected Parcels"
    subtitle="Review parcels rejected at intake or delivery."
    list-title="Rejected Parcels"
    list-subtitle="Rejected parcel records."
    status="rejected"
    :show-create="false"
    :allow-scan="true"
    default-status="rejected"
  />
</template>

<script setup>
import DeliveryOpsPage from '../components/DeliveryOpsPage.vue'
</script>
