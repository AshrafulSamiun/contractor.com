<template>
  <ParcelReportPage
    title="Lost / Damaged Parcels"
    subtitle="Parcels reported lost or damaged."
    list-title="Lost / Damaged Parcels"
    list-subtitle="Loss & damage tracking."
    :status-in="['lost', 'damaged']"
  />
</template>

<script setup>
import ParcelReportPage from '../components/ParcelReportPage.vue'
</script>
