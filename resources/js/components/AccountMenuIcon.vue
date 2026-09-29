<template>
  <svg class="pm-account-menu-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <template v-if="name === 'info'"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8" cy="10" r="2.2"/><path d="M5.5 16c.7-2 4.3-2 5 0M14 9h4M14 13h4M14 17h3"/></template>
    <template v-else-if="name === 'admin'"><circle cx="9" cy="8" r="3"/><path d="M3.5 19c.8-3.4 3-5 5.5-5s4.7 1.6 5.5 5M17 11l3 1.3v3.2c0 2-1.3 3.8-3 4.5-1.7-.7-3-2.5-3-4.5v-3.2L17 11Z"/><path d="m15.8 15.3.8.8 1.6-1.8"/></template>
    <template v-else-if="name === 'company'"><path d="M3 21h18M5 21V7h8v14M13 11h6v10M7.5 10h2M7.5 14h2M15.5 14h1M15.5 17h1"/><path d="M8 7V4h2v3"/></template>
    <template v-else-if="name === 'plans'"><circle cx="7" cy="8" r="3"/><circle cx="16.5" cy="9" r="2.5"/><path d="M2.5 19c.7-3.2 2.3-5 4.5-5s3.8 1.8 4.5 5M12 18c.5-2.4 1.8-3.8 3.8-3.8 1.7 0 3.1 1.1 3.7 3"/><path d="m18.5 17 .7 1.4 1.6.2-1.1 1.1.3 1.6-1.5-.8-1.4.8.3-1.6-1.1-1.1 1.6-.2.6-1.4Z"/></template>
    <template v-else-if="name === 'billing'"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18M7 15h3M15 15h2"/></template>
    <template v-else-if="name === 'statement'"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M15 3v5h5M8 12h7M8 16h7"/></template>
    <template v-else-if="name === 'tax'"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M15 3v5h5M7 15h10v4H7zM9 16v2M12 16v2M15 16v2"/></template>
    <template v-else-if="name === 'security'"><path d="M12 3 20 6v5c0 5.1-3.3 8.7-8 10-4.7-1.3-8-4.9-8-10V6l8-3Z"/><rect x="9" y="11" width="6" height="5" rx="1"/><path d="M10 11V9.5a2 2 0 0 1 4 0V11"/></template>
    <template v-else-if="name === 'messages'"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></template>
    <template v-else-if="name === 'notifications'"><path d="M18 10a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 22h4"/></template>
    <template v-else-if="name === 'support'"><path d="M4 13v-1a8 8 0 0 1 16 0v1M4 13h3v6H5a1 1 0 0 1-1-1v-5ZM20 13h-3v6h2a1 1 0 0 0 1-1v-5ZM17 20c0 1.1-.9 2-2 2h-2"/></template>
    <template v-else><circle cx="12" cy="8" r="3"/><path d="M5 20c.8-3.8 3.1-6 7-6s6.2 2.2 7 6"/></template>
  </svg>
</template>
<script setup>defineProps({ name: { type: String, default: 'account' } })</script>
<style scoped>.pm-account-menu-svg { width: 100%; height: 100%; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }</style>
