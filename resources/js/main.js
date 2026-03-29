import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import './assets/app.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import { i18n, initI18n } from './i18n'
import { initPageTranslator } from './i18n/pageTranslator'
import { initAuth, loadMe, clearToken } from './api/auth'
import { authState, setUser } from './store/auth'
import client from './api/client'
import { setFlash } from './store/flash'
import { FLASH } from './config/messages'
import { formatPlanLabel, getRequiredPlan, hasPlanFeature } from './config/planFeatures'
import { hasUserPermission } from './config/permissions'

import Home from './pages/Home.vue'
import About from './pages/About.vue'
import Plans from './pages/Plans.vue'
import Contact from './pages/Contact.vue'
import Login from './pages/Login.vue'
import Register from './pages/Register.vue'
import ForgotPassword from './pages/ForgotPassword.vue'
import ForgotUsername from './pages/ForgotUsername.vue'
import Terms from './pages/Terms.vue'
import Verify from './pages/Verify.vue'
import AccountSetup from './pages/AccountSetup.vue'
import AccountSetupSuccess from './pages/AccountSetupSuccess.vue'
import Dashboard from './pages/Dashboard.vue'
import TodoList from './pages/TodoList.vue'
import TodoEntry from './pages/TodoEntry.vue'
import Parcels from './pages/Parcels.vue'
import Users from './pages/Users.vue'
import NotificationLogs from './pages/NotificationLogs.vue'
import NotificationParcelArrival from './pages/NotificationParcelArrival.vue'
import NotificationPickupRequest from './pages/NotificationPickupRequest.vue'
import NotificationParcelReturn from './pages/NotificationParcelReturn.vue'
import DeliveryStatus from './pages/DeliveryStatus.vue'
import DeliveryNewArrival from './pages/DeliveryNewArrival.vue'
import DeliveryPickUp from './pages/DeliveryPickUp.vue'
import DeliveryDelivered from './pages/DeliveryDelivered.vue'
import DeliveryRejected from './pages/DeliveryRejected.vue'
import DeliveryLostDamaged from './pages/DeliveryLostDamaged.vue'
import DeliveryExpired from './pages/DeliveryExpired.vue'
import AccountProfile from './pages/AccountProfile.vue'
import AccountStatus from './pages/AccountStatus.vue'
import AccountBilling from './pages/AccountBilling.vue'
import AccountSecurity from './pages/AccountSecurity.vue'
import AccountRecovery from './pages/AccountRecovery.vue'
import PlanHistory from './pages/PlanHistory.vue'
import Calendar from './pages/Calendar.vue'
import Announcements from './pages/Announcements.vue'
import FacilitiesProperty from './pages/FacilitiesProperty.vue'
import LockerList from './pages/LockerList.vue'
import LockerAccess from './pages/LockerAccess.vue'
import LockerFacilityOwned from './pages/LockerFacilityOwned.vue'
import LockerThirdPartyOwned from './pages/LockerThirdPartyOwned.vue'
import ParcelStorage from './pages/ParcelStorage.vue'
import ParcelStorageList from './pages/ParcelStorageList.vue'
import Recipients from './pages/Recipients.vue'
import RecipientList from './pages/RecipientList.vue'
import Couriers from './pages/Couriers.vue'
import CourierList from './pages/CourierList.vue'
import DeliveryItems from './pages/DeliveryItems.vue'
import DeliveryItemList from './pages/DeliveryItemList.vue'
import DeliveryMethods from './pages/DeliveryMethods.vue'
import DeliveryMethodList from './pages/DeliveryMethodList.vue'
import Sellers from './pages/Sellers.vue'
import SellerList from './pages/SellerList.vue'
import DateTimeSettings from './pages/DateTimeSettings.vue'
import ThemeSettings from './pages/ThemeSettings.vue'
import LanguageSettings from './pages/LanguageSettings.vue'
import ParcelHoldingLimits from './pages/ParcelHoldingLimits.vue'
import NotificationSettings from './pages/NotificationSettings.vue'
import ApprovalSettings from './pages/ApprovalSettings.vue'
import ApprovalSlaDashboard from './pages/ApprovalSlaDashboard.vue'
import EmailInbox from './email/pages/EmailInbox.vue'
import EmailSent from './email/pages/EmailSent.vue'
import EmailDrafts from './email/pages/EmailDrafts.vue'
import EmailTrash from './email/pages/EmailTrash.vue'
import EmailCompose from './email/pages/EmailCompose.vue'
import EmailDetail from './email/pages/EmailDetail.vue'
import EmailTemplates from './email/pages/EmailTemplates.vue'
import EmailSettings from './email/pages/EmailSettings.vue'
import EmailThread from './email/pages/EmailThread.vue'
import EmailDashboard from './email/pages/EmailDashboard.vue'
import WorkforceDailyReports from './pages/WorkforceDailyReports.vue'
import WorkforceIncidentReports from './pages/WorkforceIncidentReports.vue'
import WorkforceTimesheets from './pages/WorkforceTimesheets.vue'
import PickupFrontDesk from './pages/PickupFrontDesk.vue'
import PickupFacilityLocker from './pages/PickupFacilityLocker.vue'
import PickupExternalLocker from './pages/PickupExternalLocker.vue'
import PickupCounterStaff from './pages/PickupCounterStaff.vue'
import PickupSlaDashboard from './pages/PickupSlaDashboard.vue'
import ParcelReportPending from './pages/ParcelReportPending.vue'
import ParcelReportPickedUp from './pages/ParcelReportPickedUp.vue'
import ParcelReportDelivered from './pages/ParcelReportDelivered.vue'
import ParcelReportRejected from './pages/ParcelReportRejected.vue'
import ParcelReportLostDamaged from './pages/ParcelReportLostDamaged.vue'
import ParcelReportExpired from './pages/ParcelReportExpired.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: Home },
    { path: '/about', name: 'about', component: About },
    { path: '/plans', name: 'plans', component: Plans },
    { path: '/contact', name: 'contact', component: Contact },
    { path: '/terms', name: 'terms', component: Terms },
    { path: '/verify', name: 'verify', component: Verify },
    { path: '/account-setup', name: 'account-setup', component: AccountSetup, meta: { requiresAuth: true } },
    { path: '/account-setup/success', name: 'account-setup-success', component: AccountSetupSuccess },
    { path: '/login', name: 'login', component: Login, meta: { guestOnly: true } },
    { path: '/register', name: 'register', component: Register, meta: { guestOnly: true } },
    { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword, meta: { guestOnly: true } },
    { path: '/forgot-username', name: 'forgot-username', component: ForgotUsername, meta: { guestOnly: true } },
    { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/todo', name: 'todo-list', component: TodoList, meta: { requiresAuth: true } },
    { path: '/todo/new', name: 'todo-entry', component: TodoEntry, meta: { requiresAuth: true } },
    { path: '/todo/:id', name: 'todo-edit', component: TodoEntry, meta: { requiresAuth: true } },
    { path: '/notifications', name: 'notifications', component: NotificationLogs, meta: { requiresAuth: true, planFeature: 'notification_center', permission: { module: 'notifications', action: 'read' } } },
    { path: '/notifications/parcel-arrival', name: 'notifications-parcel-arrival', component: NotificationParcelArrival, meta: { requiresAuth: true, planFeature: 'notification_center', permission: { module: 'notifications', action: 'read' } } },
    { path: '/notifications/pickup-request', name: 'notifications-pickup-request', component: NotificationPickupRequest, meta: { requiresAuth: true, planFeature: 'notification_center', permission: { module: 'notifications', action: 'read' } } },
    { path: '/notifications/parcel-return', name: 'notifications-parcel-return', component: NotificationParcelReturn, meta: { requiresAuth: true, planFeature: 'notification_center', permission: { module: 'notifications', action: 'read' } } },
    { path: '/delivery/status', name: 'delivery-status', component: DeliveryStatus, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'read' } } },
    { path: '/delivery/new-arrival', name: 'delivery-new-arrival', component: DeliveryNewArrival, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'create' } } },
    { path: '/delivery/pick-up', name: 'delivery-pick-up', component: DeliveryPickUp, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'edit' } } },
    { path: '/delivery/delivered', name: 'delivery-delivered', component: DeliveryDelivered, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'read' } } },
    { path: '/delivery/rejected', name: 'delivery-rejected', component: DeliveryRejected, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'read' } } },
    { path: '/delivery/lost-damaged', name: 'delivery-lost-damaged', component: DeliveryLostDamaged, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'read' } } },
    { path: '/delivery/expired', name: 'delivery-expired', component: DeliveryExpired, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'read' } } },
    { path: '/account/profile', name: 'account-profile', component: AccountProfile, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/status', name: 'account-status', component: AccountStatus, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/billing', name: 'account-billing', component: AccountBilling, meta: { requiresAuth: true, permission: { module: 'account', action: 'edit' } } },
    { path: '/account/security', name: 'account-security', component: AccountSecurity, meta: { requiresAuth: true, permission: { module: 'account', action: 'edit' } } },
    { path: '/account/recovery', name: 'account-recovery', component: AccountRecovery, meta: { requiresAuth: true, permission: { module: 'account', action: 'edit' } } },
    { path: '/admin/users', name: 'users', component: Users, meta: { requiresAuth: true, planFeature: 'user_management', permission: { module: 'users', action: 'read' } } },
    { path: '/admin/users/roles', name: 'users-roles', component: Users, meta: { requiresAuth: true, planFeature: 'user_management', permission: { module: 'users', action: 'read' } } },
    { path: '/admin/users/status', name: 'users-status', component: Users, meta: { requiresAuth: true, planFeature: 'user_management', permission: { module: 'users', action: 'read' } } },
    { path: '/calendar', name: 'calendar', component: Calendar, meta: { requiresAuth: true, permission: { module: 'calendar', action: 'read' } } },
    { path: '/announcements', name: 'announcements', component: Announcements, meta: { requiresAuth: true, permission: { module: 'announcements', action: 'read' } } },
    { path: '/plans/history', name: 'plan-history', component: PlanHistory, meta: { requiresAuth: true, permission: { module: 'plans', action: 'read' } } },
    { path: '/parcels', name: 'parcels', component: Parcels, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'read' } } },
    { path: '/profiles/facilities', name: 'profiles-facilities', component: FacilitiesProperty, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/lockers', name: 'profiles-lockers', component: LockerList, meta: { requiresAuth: true, planFeature: 'storage_management', permission: { module: 'parcels', action: 'read' } } },
    { path: '/profiles/lockers/access', name: 'profiles-lockers-access', component: LockerAccess, meta: { requiresAuth: true, planFeature: 'locker_access_management', permission: { module: 'parcels', action: 'read' } } },
    { path: '/profiles/lockers/facility-owned', name: 'profiles-lockers-facility-owned', component: LockerFacilityOwned, meta: { requiresAuth: true, planFeature: 'storage_management', permission: { module: 'parcels', action: 'read' } } },
    { path: '/profiles/lockers/third-party-owned', name: 'profiles-lockers-third-party-owned', component: LockerThirdPartyOwned, meta: { requiresAuth: true, planFeature: 'locker_access_management', permission: { module: 'parcels', action: 'read' } } },
    { path: '/profiles/storage', name: 'profiles-storage', component: ParcelStorage, meta: { requiresAuth: true, planFeature: 'storage_management', permission: { module: 'parcels', action: 'read' } } },
    { path: '/profiles/storage/list', name: 'profiles-storage-list', component: ParcelStorageList, meta: { requiresAuth: true, planFeature: 'storage_management', permission: { module: 'parcels', action: 'read' } } },
    { path: '/profiles/recipients', name: 'profiles-recipients', component: Recipients, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/recipients/list', name: 'profiles-recipients-list', component: RecipientList, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/couriers', name: 'profiles-couriers', component: Couriers, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/couriers/list', name: 'profiles-couriers-list', component: CourierList, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/items', name: 'profiles-items', component: DeliveryItems, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/items/list', name: 'profiles-items-list', component: DeliveryItemList, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/delivery-methods', name: 'profiles-delivery-methods', component: DeliveryMethods, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/delivery-methods/list', name: 'profiles-delivery-methods-list', component: DeliveryMethodList, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/sellers', name: 'profiles-sellers', component: Sellers, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/sellers/list', name: 'profiles-sellers-list', component: SellerList, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/settings/date-time', name: 'settings-date-time', component: DateTimeSettings, meta: { requiresAuth: true, permission: { module: 'settings', action: 'read' } } },
    { path: '/settings/theme', name: 'settings-theme', component: ThemeSettings, meta: { requiresAuth: true, permission: { module: 'settings', action: 'read' } } },
    { path: '/settings/language', name: 'settings-language', component: LanguageSettings, meta: { requiresAuth: true, permission: { module: 'settings', action: 'read' } } },
    { path: '/settings/holding-limits', name: 'settings-holding-limits', component: ParcelHoldingLimits, meta: { requiresAuth: true, permission: { module: 'settings', action: 'read' } } },
    { path: '/settings/notifications', name: 'settings-notifications', component: NotificationSettings, meta: { requiresAuth: true, planFeature: 'notification_center', permission: { module: 'settings', action: 'read' } } },
    { path: '/settings/approvals', name: 'settings-approvals', component: ApprovalSettings, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'settings', action: 'read' } } },
    { path: '/reports/approval-sla', name: 'reports-approval-sla', component: ApprovalSlaDashboard, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'workforce', action: 'read' } } },
    { path: '/email/new', name: 'email-new', component: EmailCompose, meta: { requiresAuth: true, permission: { module: 'email', action: 'create' } } },
    { path: '/email', name: 'email-dashboard', component: EmailDashboard, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/inbox', name: 'email-inbox', component: EmailInbox, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/sent', name: 'email-sent', component: EmailSent, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/drafts', name: 'email-drafts', component: EmailDrafts, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/trash', name: 'email-trash', component: EmailTrash, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/messages/:id', name: 'email-detail', component: EmailDetail, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/threads/:id', name: 'email-thread', component: EmailThread, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/templates', name: 'email-templates', component: EmailTemplates, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/email/settings', name: 'email-settings', component: EmailSettings, meta: { requiresAuth: true, permission: { module: 'email', action: 'read' } } },
    { path: '/workforce/daily-reports', name: 'workforce-daily-reports', component: WorkforceDailyReports, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/workforce/incident-reports', name: 'workforce-incident-reports', component: WorkforceIncidentReports, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/workforce/timesheets', name: 'workforce-timesheets', component: WorkforceTimesheets, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/pickup/front-desk', name: 'pickup-front-desk', component: PickupFrontDesk, meta: { requiresAuth: true, planFeature: 'pickup_management', permission: { module: 'pickup', action: 'read' } } },
    { path: '/pickup/facility-locker', name: 'pickup-facility-locker', component: PickupFacilityLocker, meta: { requiresAuth: true, planFeature: 'pickup_management', permission: { module: 'pickup', action: 'read' } } },
    { path: '/pickup/external-locker', name: 'pickup-external-locker', component: PickupExternalLocker, meta: { requiresAuth: true, planFeature: 'pickup_external_locker', permission: { module: 'pickup', action: 'read' } } },
    { path: '/pickup/counter-staff', name: 'pickup-counter-staff', component: PickupCounterStaff, meta: { requiresAuth: true, planFeature: 'pickup_management', permission: { module: 'pickup', action: 'read' } } },
    { path: '/reports/pickup-sla', name: 'reports-pickup-sla', component: PickupSlaDashboard, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'pickup', action: 'read' } } },
    { path: '/reports/parcels/pending', name: 'reports-parcels-pending', component: ParcelReportPending, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'parcels', action: 'read' } } },
    { path: '/reports/parcels/picked-up', name: 'reports-parcels-picked-up', component: ParcelReportPickedUp, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'parcels', action: 'read' } } },
    { path: '/reports/parcels/delivered', name: 'reports-parcels-delivered', component: ParcelReportDelivered, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'parcels', action: 'read' } } },
    { path: '/reports/parcels/rejected', name: 'reports-parcels-rejected', component: ParcelReportRejected, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'parcels', action: 'read' } } },
    { path: '/reports/parcels/lost-damaged', name: 'reports-parcels-lost-damaged', component: ParcelReportLostDamaged, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'parcels', action: 'read' } } },
    { path: '/reports/parcels/expired', name: 'reports-parcels-expired', component: ParcelReportExpired, meta: { requiresAuth: true, planFeature: 'reporting', permission: { module: 'parcels', action: 'read' } } },
  ],
})

initAuth()
if (authState.token) {
  loadMe().catch(() => clearToken())
}

router.beforeEach(async (to) => {
  if (to.meta?.guestOnly && authState.token) {
    setFlash(FLASH.ALREADY_LOGGED_IN, 'info', 2000)
    return { path: '/dashboard' }
  }
  if (!to.meta?.requiresAuth) return true
  if (!authState.token) {
    setFlash(FLASH.LOGIN_REQUIRED, 'warning', 3500)
    return { path: '/login', query: { redirect: to.fullPath } }
  }
  try {
    if (!authState.user) await loadMe()
    if (!authState.user?.account_setup_completed_at) {
      try {
        const { data } = await client.get('/account-setup')
        if (data?.data?.completed_at) {
          setUser({ ...authState.user, account_setup_completed_at: data.data.completed_at })
        }
      } catch {
        // ignore
      }
    }
    if (!authState.user?.account_setup_completed_at && to.name !== 'account-setup') {
      return { path: '/account-setup' }
    }
    if (authState.user?.account_setup_completed_at && to.name === 'account-setup') {
      return { path: '/dashboard' }
    }
    const requiredFeature = to.meta?.planFeature
    if (requiredFeature && !hasPlanFeature(authState.user?.selected_plan, requiredFeature)) {
      const requiredPlanLabel = formatPlanLabel(getRequiredPlan(requiredFeature))
      setFlash(`This module requires the ${requiredPlanLabel} plan.`, 'warning', 3500)
      return { path: '/plans' }
    }
    const permissionMeta = to.meta?.permission
    if (permissionMeta && !hasUserPermission(authState.user, permissionMeta.module, permissionMeta.action || 'read')) {
      setFlash('You do not have permission to access this module.', 'warning', 3500)
      return { path: '/dashboard' }
    }
    return true
  } catch {
    clearToken()
    setFlash(FLASH.SESSION_EXPIRED, 'warning', 3500)
    return { path: '/login', query: { redirect: to.fullPath } }
  }
})

const bootstrap = async () => {
  const app = createApp(App)
  app.use(router)
  app.use(i18n)
  await initI18n()
  app.mount('#app')
  initPageTranslator(router)

  // Avoid blank screen caused by body opacity transition
  document.body.classList.add('loaded')
}

bootstrap()
