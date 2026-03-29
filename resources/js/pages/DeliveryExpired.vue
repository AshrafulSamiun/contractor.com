<template>
  <DeliveryOpsPage
    title="Expired Holding"
    subtitle="Parcels that exceeded holding limits."
    list-title="Expired Holding"
    list-subtitle="Parcels marked as expired."
    status="expired"
    :show-create="false"
    :allow-scan="true"
    default-status="expired"
  />
</template>

<script setup>
import DeliveryOpsPage from '../components/DeliveryOpsPage.vue'
</script>
