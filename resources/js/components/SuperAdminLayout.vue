<template>
  <div class="sa-shell">
    <aside class="sa-sidebar" :class="{ open: menuOpen }">
      <RouterLink class="sa-brand" to="/super-admin/dashboard"><span class="sa-brand-mark" aria-hidden="true">♛</span><span>Super Admin</span></RouterLink>
      <nav aria-label="Super Admin navigation">
        <template v-for="item in displayMenu" :key="item.label">
          <div v-if="item.submenu" class="sa-customer-menu">
            <button type="button" class="sa-link sa-customer-toggle" :class="{ active: openSubmenu === item.label }" @click="openSubmenu = openSubmenu === item.label ? '' : item.label"><span class="sa-icon" :class="`sa-icon--${item.icon}`" aria-hidden="true"></span><span>{{ item.label }}</span><span class="sa-customer-chevron">{{ openSubmenu === item.label ? '⌃' : '⌄' }}</span></button>
            <div v-show="openSubmenu === item.label" class="sa-customer-submenu">
              <template v-for="child in (item.children || customerSubmenu)" :key="child.label">
                <RouterLink v-if="child.to" :to="submenuLink(child.to, item.label)" class="sa-sub-link" @click="menuOpen = false">{{ child.number }} {{ child.label }}</RouterLink>
                <button v-else type="button" class="sa-sub-link sa-sub-link--pending">{{ child.number }} {{ child.label }}</button>
              </template>
            </div>
          </div>
          <button v-if="item.action" type="button" class="sa-link" @click="logout"><span class="sa-icon" :class="`sa-icon--${item.icon}`" aria-hidden="true"></span><span>{{ item.label }}</span></button>
          <RouterLink v-else-if="!item.submenu" :to="item.to" class="sa-link" @click="menuOpen = false"><span class="sa-icon" :class="`sa-icon--${item.icon}`" aria-hidden="true"></span><span>{{ item.label }}</span></RouterLink>
        </template>
      </nav>
    </aside>
    <main class="sa-main">
      <header class="sa-topbar"><button class="sa-menu" type="button" @click="menuOpen = !menuOpen">☰</button><div class="sa-search"><span>⌕</span><input placeholder="Search customers, invoices, tickets…" /></div><div class="sa-user"><span class="sa-avatar">{{ initials }}</span><span>{{ userName }}<small>Super Admin</small></span></div></header>
      <section class="sa-content"><slot /></section>
    </main>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { authState } from '../store/auth'
import { logout as apiLogout, clearToken } from '../api/auth'

const menuOpen = ref(false)
const route = useRoute()
const openSubmenu = ref('')
const userName = computed(() => authState.user?.name || 'Platform Admin')
const initials = computed(() => userName.value.split(' ').map(word => word[0]).join('').slice(0, 2).toUpperCase())
const menu = [
  { label: 'Dashboard', to: '/super-admin/dashboard', icon: '▦' },
  { label: 'Calendar', to: '/super-admin/calendar', icon: '▣' },
  { label: 'Announcements', to: '/super-admin/announcements', icon: '◉' },
  { label: 'Website Visitors', to: '/super-admin/website-visitors', icon: '♙' },
  { label: 'Emergency Shutdown', to: '/super-admin/emergency-shutdown', icon: '⚠' },
  { label: 'External Cybersecurity', to: '/super-admin/external-security', icon: '♧' },
  { label: 'System Monitoring', to: '/super-admin/system-monitoring', icon: '▦' },
  { label: 'Payment Gateway', to: '/super-admin/payment-gateway', icon: '⇩' },
  { label: 'Reports', to: '/super-admin/reports', icon: '▤' },
  { label: 'Notifications', to: '/super-admin/notifications', icon: '◉' },
  { label: 'Customers', to: '/super-admin/customers', icon: '♙' },
  { label: 'New Accounts — Under Review', to: '/super-admin/pending-accounts', icon: '◉' },
  { label: 'Billing & Payments', to: '/super-admin/billing', icon: '▣' },
  { label: 'Reports', to: '/super-admin/tax-report', icon: '▤' },
  { label: 'Support Tickets', to: '/super-admin/support', icon: '?' },
  { label: 'Audit Logs', to: '/super-admin/audit-logs', icon: '♧' },
  { label: 'Announcements', to: '/super-admin/announcements', icon: '◉' },
]
const displayMenu = [
  { number: 0, label: 'Re-Login', icon: 'relogin', action: true }, { number: 1, label: 'Dashboard', to: '/super-admin/dashboard', icon: 'dashboard' }, { number: 2, label: 'Calendar', to: '/super-admin/calendar', icon: 'calendar' }, { number: 3, label: 'Announcements', to: '/super-admin/announcements', icon: 'announcements' }, { number: 4, label: 'Website Visitors', to: '/super-admin/website-visitors', icon: 'visitors' }, { number: 5, label: 'New Accounts — Under Review', to: '/super-admin/pending-accounts', icon: 'accounts' }, { number: 6, label: 'Customers Centre', icon: 'customers', submenu: true }, { number: 7, label: 'Emergency Shutdown', icon: 'emergency', submenu: true, children: [{ number: '7.1', label: 'Emergency Shutdown', to: '/super-admin/emergency-shutdown' }, { number: '7.2', label: 'Emergency Shutdown Report', to: '/super-admin/emergency-shutdown/report' }, { number: '7.3', label: 'Restore System', to: '/super-admin/emergency-shutdown/restore' }] }, { number: 8, label: 'External Cyber Security', icon: 'security', submenu: true, children: [{ number: '8.1', label: 'Login Attempt Overview' }, { number: '8.2', label: 'Login Attempt Report', to: '/super-admin/external-security' }, { number: '8.3', label: 'Blocked IPs' }, { number: '8.4', label: 'Country Access Summary' }, { number: '8.5', label: 'Suspicious Activity' }] }, { number: 9, label: 'System Monitoring', to: '/super-admin/system-monitoring', icon: 'monitoring' }, { number: 10, label: 'Audit Logs', to: '/super-admin/audit-logs', icon: 'audit' }, { number: 11, label: 'Import from Payment Gateway', to: '/super-admin/payment-gateway', icon: 'import' }, { number: 12, label: 'Reports', to: '/super-admin/reports', icon: 'reports' }, { number: 13, label: 'Notifications', to: '/super-admin/notifications', icon: 'notifications' }, { number: 14, label: 'Help Center', to: '/super-admin/support', icon: 'help' }, { number: 15, label: 'Log Out', icon: 'logout', action: true },
]
const customerSubmenu = [
  { number: '6.1', label: 'Customers List', to: '/super-admin/customers' },
  { number: '6.2', label: 'Customer / Users', to: '/super-admin/customer-users' },
  { number: '6.3', label: 'User Behaviour (Single User)', to: '/super-admin/customer-users/behaviour' },
  { number: '6.4', label: 'Usage Summary', to: '/super-admin/customer-usage-weekly' },
  { number: '6.5', label: 'Payment Summary' },
  { number: '6.6', label: 'Sales Invoices & Pmt', to: '/super-admin/billing' },
  { number: '6.7', label: 'Customer Account Status Report', to: '/super-admin/customer-account-status' },
  { number: '6.8', label: 'Customer Users & Licence Report', to: '/super-admin/customer-users-licence-report' },
  { number: '6.9', label: 'Customer Payment Status Report', to: '/super-admin/customer-payment-status-report' },
  { number: '6.10', label: 'Customer Revenue Report', to: '/super-admin/customer-revenue-report' },
  { number: '6.11', label: 'Customer Login & Security Report', to: '/super-admin/customer-login-security-report' },
  { number: '6.12', label: 'New, Active & Closed Customers Report', to: '/super-admin/customer-lifecycle-report' },
  { number: '6.13', label: 'Customer Revenue Report', to: '/super-admin/customer-revenue-report' },
  { number: '6.14', label: 'Customer Master Report', to: '/super-admin/customer-master-report' },
]
const reportsMenuItem = displayMenu.find(item => item.label === 'Reports')
if (reportsMenuItem) Object.assign(reportsMenuItem, {
  submenu: true,
  children: [
    { number: '12.1', label: 'Sales & Payments Report', to: '/super-admin/billing' },
    { number: '12.2', label: 'Sales Tax Report', to: '/super-admin/tax-report' },
    { number: '12.3', label: 'Customers List Report', to: '/super-admin/customers' },
    { number: '12.4', label: 'Customer Account Status', to: '/super-admin/reports/account-status' },
    { number: '12.5', label: 'Login / Logout History', to: '/super-admin/reports/login-history' },
    { number: '12.6', label: 'Users Monitoring', to: '/super-admin/reports/users-monitoring' },
    { number: '12.7', label: 'Failed Login Report', to: '/super-admin/reports/failed-logins' },
    { number: '12.8', label: 'Suspicious Device Access', to: '/super-admin/reports/suspicious-devices' },
    { number: '12.9', label: 'Suspicious Activities', to: '/super-admin/reports/suspicious-activities' },
    { number: '12.9', label: 'Payment Failure & NSF', to: '/super-admin/reports/payment-failures' },
    { number: '12.10', label: 'Customer Revenue', to: '/super-admin/reports/customer-revenue' },
    { number: '12.11', label: 'Service Plan History', to: '/super-admin/reports/plan-history' },
    { number: '12.12', label: 'Audit Log Report', to: '/super-admin/reports/audit-log' },
    { number: '12.13', label: 'Emergency Shutdown Report', to: '/super-admin/reports/emergency-shutdown' },
    { number: '12.14', label: 'System Usage Report', to: '/super-admin/reports/system-usage' },
    { number: '12.15', label: 'Customer Master Report', to: '/super-admin/reports/customer-master' },
    { number: '12.16', label: 'Sales Master Report', to: '/super-admin/reports/sales-master' },
    { number: '12.17', label: 'Sales Tax Master Report', to: '/super-admin/reports/sales-tax-master' },
    { number: '12.18', label: 'Suspected Licence Sharing', to: '/super-admin/reports/suspected-license-sharing' },
    { number: '12.19', label: 'Sign Up & Created Accounts', to: '/super-admin/reports/sign-up-created-accounts' },
  ],
})
const notificationsMenuItem = displayMenu.find(item => item.label === 'Notifications')
if (notificationsMenuItem) Object.assign(notificationsMenuItem, {
  submenu: true,
  children: [
    { number: '', label: 'Notification List', to: '/super-admin/notifications' },
    { number: '', label: 'SMS Notification Format', to: '/super-admin/notifications/sms-format' },
  ],
})

// The layout is mounted again after navigation. Keep the relevant menu open
// instead of always reopening Customers Centre on every report page.
const submenuLink = (to, menuLabel) => menuLabel === 'Reports'
  ? { path: to, query: { menu: 'reports' } }
  : to

const submenuForRoute = (path, menu) => {
  if (menu === 'reports' || path.startsWith('/super-admin/reports') || path === '/super-admin/tax-report') {
    return 'Reports'
  }

  if (path.startsWith('/super-admin/notifications')) {
    return 'Notifications'
  }

  return 'Customers Centre'
}

watch(() => [route.path, route.query.menu], ([path, menu]) => {
  openSubmenu.value = submenuForRoute(path, menu)
}, { immediate: true })

// Submenu labels are intentionally unnumbered throughout the Super Admin menu.
displayMenu.forEach(item => {
  ;(item.children || []).forEach(child => { child.number = '' })
})
customerSubmenu.forEach(child => { child.number = '' })
const logout = async () => { try { await apiLogout() } catch {} finally { clearToken(); window.location.assign('/login') } }
</script>

<style scoped>
.sa-shell{min-height:100vh;display:flex;background:#f4f7fb;color:#16233d;font-family:"Space Grotesk",Arial,sans-serif}.sa-sidebar{width:264px;flex:0 0 264px;background:linear-gradient(180deg,#072653,#073c77);color:#fff;padding:22px 14px;display:flex;flex-direction:column}.sa-brand{display:flex;gap:11px;align-items:center;padding:5px 12px 30px;color:#fff;text-decoration:none;font-size:1.18rem;font-weight:700}.sa-brand-mark{display:grid;place-items:center;width:35px;height:35px;border-radius:9px;background:#1b84ed;font-family:Georgia;font-size:1.5rem}.sa-brand small,.sa-user small{display:block;font-size:.65rem;letter-spacing:.12em;opacity:.7}.sa-sidebar nav{display:grid;gap:5px}.sa-link{display:flex;align-items:center;gap:12px;padding:12px;border-radius:7px;color:#dbeafe;text-decoration:none;font-size:.92rem}.sa-link span{font-size:1.1rem;width:21px;text-align:center}.sa-link:hover,.sa-link.router-link-active{background:#1172dd;color:#fff}.sa-sidebar-foot{margin-top:auto;border-top:1px solid #2c5d91;padding:18px 12px 3px;color:#b5d0f3;font-size:.75rem}.sa-sidebar-foot strong{font-size:.83rem;color:#fff}.sa-main{min-width:0;flex:1}.sa-topbar{height:70px;background:#fff;border-bottom:1px solid #e3e8f1;display:flex;align-items:center;gap:20px;padding:0 32px}.sa-menu{display:none;border:0;background:transparent;font-size:1.3rem}.sa-search{display:flex;align-items:center;gap:8px;min-width:260px;max-width:500px;flex:1;border:1px solid #d9e1ee;border-radius:7px;padding:8px 12px;color:#728098}.sa-search input{border:0;outline:0;width:100%;font:inherit;font-size:.85rem}.sa-user{margin-left:auto;display:flex;align-items:center;gap:9px;font-size:.85rem;font-weight:600}.sa-avatar{display:grid;place-items:center;width:33px;height:33px;border-radius:50%;background:#dbeafe;color:#1453a3}.sa-content{padding:30px;max-width:1600px;margin:auto}@media(max-width:800px){.sa-sidebar{position:fixed;z-index:20;top:0;bottom:0;left:-264px;transition:left .2s}.sa-sidebar.open{left:0}.sa-menu{display:block}.sa-topbar{padding:0 18px}.sa-content{padding:22px 16px}.sa-search{min-width:0}.sa-user>span:last-child{display:none}}
.sa-sidebar{overflow-y:auto;background:linear-gradient(145deg,#031b47,#052c65 52%,#031a45);padding:12px 10px}.sa-brand{min-height:68px;padding:0 12px 9px;border-bottom:1px solid rgba(155,196,255,.32);font-size:1.45rem}.sa-brand-mark{width:42px;height:48px;border:2px solid #ffc650;clip-path:polygon(50% 0,94% 16%,86% 74%,50% 100%,14% 74%,6% 16%);border-radius:0;background:linear-gradient(145deg,#ffd56a,#a96405);color:#09265b;font-size:1.8rem;text-shadow:0 1px #fff7c0}.sa-sidebar nav{gap:0}.sa-link{gap:10px;min-height:47px;margin:0;padding:0 10px;border:0;border-bottom:1px solid rgba(155,196,255,.2);border-radius:0;background:transparent;color:#f7f9ff;text-align:left;font:600 8px/1.12 "Space Grotesk",Arial,sans-serif;cursor:pointer}.sa-link span:not(.sa-icon){width:auto;text-align:left;font-size:40%!important}.sa-link:hover,.sa-link.router-link-active{background:rgba(44,123,235,.38);color:#fff}.sa-icon{display:grid!important;place-items:center;width:30px!important;flex:0 0 30px;font-family:"Segoe UI Emoji","Apple Color Emoji",sans-serif!important;font-size:1.38rem!important;line-height:1;filter:saturate(1.18)}.sa-icon::before{display:block}.sa-icon--relogin::before{content:'🔄'}.sa-icon--dashboard::before{content:'⏱️'}.sa-icon--calendar::before{content:'🗓️'}.sa-icon--announcements::before{content:'📣'}.sa-icon--visitors::before{content:'👥'}.sa-icon--accounts::before{content:'👤'}.sa-icon--customers::before{content:'🏢'}.sa-icon--emergency::before{content:'⚠️'}.sa-icon--security::before{content:'🛡️'}.sa-icon--monitoring::before{content:'🖥️'}.sa-icon--audit::before{content:'📋'}.sa-icon--import::before{content:'📥'}.sa-icon--reports::before{content:'📊'}.sa-icon--notifications::before{content:'🔔'}.sa-icon--help::before{content:'❓'}.sa-icon--logout::before{content:'🚪'}
/* Keep the sidebar navigation legible at normal desktop zoom. */
.sa-link{gap:12px;min-height:54px;padding:8px 12px;font:600 14px/1.25 "Space Grotesk",Arial,sans-serif}
.sa-link span:not(.sa-icon){font-size:inherit!important}
.sa-customer-menu{border-bottom:1px solid rgba(155,196,255,.2)}.sa-customer-toggle{width:100%;border-bottom:0}.sa-customer-toggle.active{background:rgba(44,123,235,.22)}.sa-customer-chevron{margin-left:auto!important;width:auto!important;font-size:1.15rem!important}.sa-customer-submenu{padding:3px 0 7px;background:rgba(0,8,41,.18)}.sa-sub-link{display:block;width:100%;border:0;background:transparent;color:#f7f9ff;text-decoration:none;text-align:left;padding:8px 12px 8px 50px;font:600 12px/1.25 "Space Grotesk",Arial,sans-serif;cursor:pointer}.sa-sub-link:hover,.sa-sub-link.router-link-active{background:#0a4bc3;color:#fff}.sa-sub-link--pending{opacity:.82}.sa-sub-link--pending:hover{background:rgba(44,123,235,.35)}
</style>
<style>
.uw-page,.sip-page,.cas,.cr,.rpt,.rep{width:100%;max-width:100%;min-width:0;box-sizing:border-box}.uw-page>form,.sip-page>form,.cas>form,.cr>form,.rpt>form,.rep>form,.uw-table-wrap,.sip-page .tablebox,.cas .tablebox,.cr .report,.rpt .tablebox,.rep .box,.rep .master{width:100%;max-width:100%;min-width:0;box-sizing:border-box;overflow:auto}.uw-page table,.sip-page table,.cas table,.cr table,.rpt table,.rep table{width:100%!important;min-width:0!important;max-width:100%;table-layout:fixed}.uw-page th,.sip-page th,.cas th,.cr th,.rpt th,.rep th{height:55px;background:linear-gradient(100deg,#03205f,#0751c7)!important;color:#fff!important}.uw-page th,.uw-page td,.sip-page th,.sip-page td,.cas th,.cas td,.cr th,.cr td,.rpt th,.rpt td,.rep th,.rep td{white-space:normal!important;overflow-wrap:anywhere}.uw-table-wrap footer,.sip-page footer,.cas .tablebox footer,.cr .report footer,.rpt .tablebox footer,.rep .box footer,.rep .master footer{display:flex;justify-content:space-between;align-items:center;margin-top:0;padding:20px 24px;border:0;border-top:1px solid #dce5ef;border-radius:0;background:#fff}.uw-table-wrap footer b,.sip-page footer b,.cas .tablebox footer b,.cr .report footer b,.rpt .tablebox footer b,.rep .box footer b,.rep .master footer b{display:grid;place-items:center;min-width:40px;height:40px;border:1px solid #0647d2;border-radius:6px;background:#0647d2;color:#fff}@media(max-width:700px){.uw-table-wrap footer,.sip-page footer,.cas .tablebox footer,.cr .report footer,.rpt .tablebox footer,.rep .box footer,.rep .master footer{flex-direction:column;align-items:flex-start;gap:12px}}
</style>
