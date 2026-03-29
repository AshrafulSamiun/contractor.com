<template>
  <DeliveryOpsPage
    title="Delivery Status"
    subtitle="Monitor all delivery activity and update parcel status."
    list-title="All Delivery Activity"
    list-subtitle="Latest parcel updates across the system."
    :show-create="false"
    :allow-scan="true"
  />
</template>

<script setup>
import DeliveryOpsPage from '../components/DeliveryOpsPage.vue'
</script>
