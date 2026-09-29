import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import './assets/app.css'
import './assets/account-messages-compact.css'
import './assets/super-admin-tickets.css'
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
import SuperAdminDashboard from './pages/SuperAdminDashboard.vue'
import SuperAdminCustomers from './pages/SuperAdminCustomers.vue'
import SuperAdminBilling from './pages/SuperAdminBilling.vue'
import SuperAdminTaxReport from './pages/SuperAdminTaxReport.vue'
import SuperAdminCustomerProfile from './pages/SuperAdminCustomerProfile.vue'
import SuperAdminReviewList from './pages/SuperAdminReviewList.vue'
import SuperAdminPendingAccounts from './pages/SuperAdminPendingAccounts.vue'
import SuperAdminPlatformList from './pages/SuperAdminPlatformList.vue'
import SuperAdminWebsiteVisitors from './pages/SuperAdminWebsiteVisitors.vue'
import SuperAdminShutdownControl from './pages/SuperAdminShutdownControl.vue'
import SuperAdminShutdownReport from './pages/SuperAdminShutdownReport.vue'
import SuperAdminRestoreSystem from './pages/SuperAdminRestoreSystem.vue'
import SuperAdminExternalSecurity from './pages/SuperAdminExternalSecurity.vue'
import SuperAdminSystemMonitoring from './pages/SuperAdminSystemMonitoring.vue'
import SuperAdminPaymentGateway from './pages/SuperAdminPaymentGateway.vue'
import SuperAdminReports from './pages/SuperAdminReports.vue'
import SuperAdminNotifications from './pages/SuperAdminNotifications.vue'
import SuperAdminSmsNotificationFormat from './pages/SuperAdminSmsNotificationFormat.vue'
import SuperAdminReportDetail from './pages/SuperAdminReportDetail.vue'
import SuperAdminAdvancedReport from './pages/SuperAdminAdvancedReport.vue'
import SuperAdminTickets from './pages/SuperAdminTickets.vue'
import SuperAdminTicketDetail from './pages/SuperAdminTicketDetail.vue'
import SuperAdminCustomerUsers from './pages/SuperAdminCustomerUsers.vue'
import SuperAdminCustomerUsageWeekly from './pages/SuperAdminCustomerUsageWeekly.vue'
import SuperAdminCustomerAccountStatus from './pages/SuperAdminCustomerAccountStatus.vue'
import SuperAdminCustomerLicenceReport from './pages/SuperAdminCustomerLicenceReport.vue'
import SuperAdminCustomerPaymentStatus from './pages/SuperAdminCustomerPaymentStatus.vue'
import SuperAdminCustomerLoginSecurity from './pages/SuperAdminCustomerLoginSecurity.vue'
import SuperAdminCustomerLifecycleReport from './pages/SuperAdminCustomerLifecycleReport.vue'
import SuperAdminCustomerRevenueReport from './pages/SuperAdminCustomerRevenueReport.vue'
import SuperAdminCustomerMasterReport from './pages/SuperAdminCustomerMasterReport.vue'
import SuperAdminUserBehaviour from './pages/SuperAdminUserBehaviour.vue'
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
import SystemAdminProfile from './pages/SystemAdminProfile.vue'
import CompanyProfile from './pages/CompanyProfile.vue'
import AccountBilling from './pages/AccountBilling.vue'
import AccountStatement from './pages/AccountStatement.vue'
import AccountTaxReport from './pages/AccountTaxReport.vue'
import AccountSecurity from './pages/AccountSecurity.vue'
import AccountMessages from './pages/AccountMessages.vue'
import AccountNotifications from './pages/AccountNotifications.vue'
import HelpSupport from './pages/HelpSupport.vue'
import AccountRecovery from './pages/AccountRecovery.vue'
import PlanHistory from './pages/PlanHistory.vue'
import Calendar from './pages/Calendar.vue'
import Announcements from './pages/Announcements.vue'
import SuperAdminAnnouncements from './pages/SuperAdminAnnouncements.vue'
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
import DailyReport from './pages/DailyReport.vue'
import DailyReports from './pages/DailyReports.vue'
import WorkforceDailyReports from './pages/WorkforceDailyReports.vue'
import WorkforceIncidentReports from './pages/WorkforceIncidentReports.vue'
import WorkforceStaffAttendance from './pages/WorkforceStaffAttendance.vue'
import WorkforceWorkflowRecords from './pages/WorkforceWorkflowRecords.vue'
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
import AccountHolder from './pages/AccountHolder.vue'
import AccountHolders from './pages/AccountHolder.vue'
import Banks from './pages/Banks.vue'
import InsuranceCompanies from './pages/InsuranceCompanies.vue'
import FixedAssets from './pages/FixedAssets.vue'
import AccountGroups from './pages/AccountGroups.vue'
import SellerProfiles from './pages/SellerProfiles.vue'
import ChartOfAccounts from './pages/ChartOfAccounts.vue'
import PurchaseOrders from './pages/PurchaseOrders.vue'
import PurchaseInvoices from './pages/PurchaseInvoices.vue'
import PurchaseReturns from './pages/PurchaseReturns.vue'
import SalesReturns from './pages/SalesReturns.vue'
import CustomerCreditNotes from './pages/CustomerCreditNotes.vue'
import SellerDebitNotes from './pages/SellerDebitNotes.vue'
import SellerCreditNotes from './pages/SellerCreditNotes.vue'
import BillPayments from './pages/BillPayments.vue'
import SalesOrders from './pages/SalesOrders.vue'
import SalesInvoices from './pages/SalesInvoices.vue'
import SalesEstimations from './pages/SalesEstimations.vue'
import CreditCards from './pages/CreditCards.vue'
import Customers from './pages/Customers.vue'
import Employees from './pages/Employees.vue'
import PaymentMethods from './pages/PaymentMethod.vue'
import SalesTax from './pages/SalesTax.vue'
import InvoiceTerms from './pages/InvoiceTerms.vue'
import InventoryItems from './pages/InventoryItems.vue'
import ServiceItems from './pages/ServiceItems.vue'
import JobSites from './pages/JobSites.vue'
import Estimation from './pages/Estimation.vue'
import Quotation from './pages/Quotation.vue'
import JobOrders from './pages/JobOrders.vue'
import JobOrderDailyWorkLog from './pages/JobOrderDailyWorkLog.vue'
import JobOrderShortStatus from './pages/JobOrderShortStatus.vue'
import JobOrderFullStatus from './pages/JobOrderFullStatus.vue'
import JobOrderTracker from './pages/JobOrderTracker.vue'
import JobOrderFinance from './pages/JobOrderFinance.vue'
import Accident from './pages/AccidentV2.vue'
import Drivers from './pages/Drivers.vue'
import Insurance from './pages/InsuranceV2.vue'
import Fuel from './pages/Fuel.vue'
import RepairMaintenance from './pages/RepairMaintenance.vue'
import TicketingComplience from './pages/TicketingComplience.vue'
import DailyLog from './pages/MileageTripsV2.vue'
import Vehicle from './pages/Vehicle.vue'

const router = createRouter({
  // The application is hosted at the site root.  Do not inherit Vite's asset
  // build directory as the browser-history base, otherwise client navigation
  // can produce URLs such as /build/dashboard.
  history: createWebHistory('/'),
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
    // Preserve links that used the former misspelling and keep the user in the
    // platform administration area after a full browser refresh.
    { path: '/supper-admin/dashboard', redirect: '/super-admin/dashboard' },
    { path: '/super-admin/dashboard', name: 'super-admin-dashboard', component: SuperAdminDashboard, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customers', name: 'super-admin-customers', component: SuperAdminCustomers, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customers/:id', name: 'super-admin-customer-profile', component: SuperAdminCustomerProfile, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-users', name: 'super-admin-customer-users', component: SuperAdminCustomerUsers, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-usage-weekly', name: 'super-admin-customer-usage-weekly', component: SuperAdminCustomerUsageWeekly, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-account-status', name: 'super-admin-customer-account-status', component: SuperAdminCustomerAccountStatus, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-users-licence-report', name: 'super-admin-customer-users-licence-report', component: SuperAdminCustomerLicenceReport, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-payment-status-report', name: 'super-admin-customer-payment-status-report', component: SuperAdminCustomerPaymentStatus, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-login-security-report', name: 'super-admin-customer-login-security-report', component: SuperAdminCustomerLoginSecurity, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-lifecycle-report', name: 'super-admin-customer-lifecycle-report', component: SuperAdminCustomerLifecycleReport, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-revenue-report', name: 'super-admin-customer-revenue-report', component: SuperAdminCustomerRevenueReport, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-master-report', name: 'super-admin-customer-master-report', component: SuperAdminCustomerMasterReport, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-users/behaviour', name: 'super-admin-user-behaviour-default', component: SuperAdminUserBehaviour, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/customer-users/:id/behaviour', name: 'super-admin-user-behaviour', component: SuperAdminUserBehaviour, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/billing', name: 'super-admin-billing', component: SuperAdminBilling, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/tax-report', name: 'super-admin-tax-report', component: SuperAdminTaxReport, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/pending-accounts', name: 'super-admin-pending-accounts', component: SuperAdminPendingAccounts, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/support', name: 'super-admin-support', component: SuperAdminTickets, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/support/:id', name: 'super-admin-support-ticket', component: SuperAdminTicketDetail, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/audit-logs', name: 'super-admin-audit-logs', component: SuperAdminReviewList, meta: { requiresAuth: true, superAdmin: true, kind: 'audit' } },
    { path: '/super-admin/calendar', name: 'super-admin-calendar', component: SuperAdminPlatformList, meta: { requiresAuth: true, superAdmin: true, kind: 'calendar' } },
    { path: '/super-admin/announcements', name: 'super-admin-announcements', component: SuperAdminAnnouncements, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/website-visitors', name: 'super-admin-website-visitors', component: SuperAdminWebsiteVisitors, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/emergency-shutdown', name: 'super-admin-emergency-shutdown', component: SuperAdminShutdownControl, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/emergency-shutdown/report', name: 'super-admin-emergency-shutdown-report', component: SuperAdminShutdownReport, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/emergency-shutdown/restore', name: 'super-admin-restore-system', component: SuperAdminRestoreSystem, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/external-security', name: 'super-admin-external-security', component: SuperAdminExternalSecurity, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/system-monitoring', name: 'super-admin-system-monitoring', component: SuperAdminSystemMonitoring, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/payment-gateway', name: 'super-admin-payment-gateway', component: SuperAdminPaymentGateway, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/reports', name: 'super-admin-reports', component: SuperAdminReports, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/notifications', name: 'super-admin-notifications', component: SuperAdminNotifications, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/notifications/sms-format', name: 'super-admin-sms-notification-format', component: SuperAdminSmsNotificationFormat, meta: { requiresAuth: true, superAdmin: true } },
    { path: '/super-admin/reports/account-status', component: SuperAdminReportDetail, meta: { requiresAuth: true, superAdmin: true, kind: 'status' } },
    { path: '/super-admin/reports/login-history', component: SuperAdminReportDetail, meta: { requiresAuth: true, superAdmin: true, kind: 'login' } },
    { path: '/super-admin/reports/users-monitoring', component: SuperAdminReportDetail, meta: { requiresAuth: true, superAdmin: true, kind: 'users' } },
    { path: '/super-admin/reports/plan-history', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'plan-history' } },
    { path: '/super-admin/reports/audit-log', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'audit-log' } },
    { path: '/super-admin/reports/emergency-shutdown', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'emergency-shutdown' } },
    { path: '/super-admin/reports/system-usage', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'system-usage' } },
    { path: '/super-admin/reports/customer-master', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'customer-master' } },
    { path: '/super-admin/reports/sales-master', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'sales-master' } },
    { path: '/super-admin/reports/sales-master-all', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'sales-master-all' } },
    { path: '/super-admin/reports/failed-logins', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'failed-logins' } },
    { path: '/super-admin/reports/suspicious-devices', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'suspicious-devices' } },
    { path: '/super-admin/reports/suspicious-activities', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'suspicious-activities' } },
    { path: '/super-admin/reports/payment-failures', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'payment-failures' } },
    { path: '/super-admin/reports/customer-revenue', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'customer-revenue' } },
    { path: '/super-admin/reports/sales-master-customer', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'sales-master-customer' } },
    { path: '/super-admin/reports/sales-tax-master', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'sales-tax-master' } },
    { path: '/super-admin/reports/sales-tax-all', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'sales-tax-all' } },
    { path: '/super-admin/reports/sales-tax-customer', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'sales-tax-customer' } },
    { path: '/super-admin/reports/suspected-license-sharing', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'suspected-license-sharing' } },
    { path: '/super-admin/reports/sign-up-created-accounts', component: SuperAdminAdvancedReport, meta: { requiresAuth: true, superAdmin: true, report: 'sign-up-created-accounts' } },
    
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
    { path: '/account/system-admin', name: 'account-system-admin', component: SystemAdminProfile, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/company-profile', name: 'account-company-profile', component: CompanyProfile, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/billing', name: 'account-billing', component: AccountBilling, meta: { requiresAuth: true, permission: { module: 'account', action: 'edit' } } },
    { path: '/account/statements', name: 'account-statements', component: AccountStatement, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/tax-report', name: 'account-tax-report', component: AccountTaxReport, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/security', name: 'account-security', component: AccountSecurity, meta: { requiresAuth: true, permission: { module: 'account', action: 'edit' } } },
    { path: '/account/messages', name: 'account-messages', component: AccountMessages, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/notifications', name: 'account-notifications', component: AccountNotifications, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/help-support', name: 'account-help-support', component: HelpSupport, meta: { requiresAuth: true, permission: { module: 'account', action: 'read' } } },
    { path: '/account/recovery', name: 'account-recovery', component: AccountRecovery, meta: { requiresAuth: true, permission: { module: 'account', action: 'edit' } } },
    { path: '/admin/users', name: 'users', component: Users, meta: { requiresAuth: true, planFeature: 'user_management', permission: { module: 'users', action: 'read' } } },
    { path: '/admin/users/roles', name: 'users-roles', component: Users, meta: { requiresAuth: true, planFeature: 'user_management', permission: { module: 'users', action: 'read' } } },
    { path: '/admin/users/status', name: 'users-status', component: Users, meta: { requiresAuth: true, planFeature: 'user_management', permission: { module: 'users', action: 'read' } } },
    { path: '/calendar', name: 'calendar', component: Calendar, meta: { requiresAuth: true, permission: { module: 'calendar', action: 'read' } } },
    { path: '/announcements', name: 'announcements', component: Announcements, meta: { requiresAuth: true, permission: { module: 'announcements', action: 'read' } } },
    { path: '/plans/history', name: 'plan-history', component: PlanHistory, meta: { requiresAuth: true, permission: { module: 'plans', action: 'read' } } },
    { path: '/parcels', name: 'parcels', component: Parcels, meta: { requiresAuth: true, permission: { module: 'parcels', action: 'read' } } },
   
    { path: '/profiles/account-holders', name: 'profiles-account-holders', component: AccountHolders, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/account-holders/banks', name: 'profiles-banks', component: Banks, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/account-holders/insurance-companies', name: 'profiles-insurance-companies', component: InsuranceCompanies, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/account-holders/sellers', name: 'profiles-account-holder-sellers', component: SellerProfiles, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/fixed-assets', name: 'profiles-fixed-assets', component: FixedAssets, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/account-groups', name: 'profiles-account-groups', component: AccountGroups, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/chart-of-accounts', name: 'profiles-chart-of-accounts', component: ChartOfAccounts, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/accounting/purchase-orders', name: 'accounting-purchase-orders', component: PurchaseOrders, meta: { requiresAuth: true } },
    { path: '/accounting/purchase-invoices', name: 'accounting-purchase-invoices', component: PurchaseInvoices, meta: { requiresAuth: true } },
    { path: '/accounting/purchase-returns', name: 'accounting-purchase-returns', component: PurchaseReturns, meta: { requiresAuth: true } },
    { path: '/accounting/seller-debit-notes', name: 'accounting-seller-debit-notes', component: SellerDebitNotes, meta: { requiresAuth: true } },
    { path: '/accounting/seller-credit-notes', name: 'accounting-seller-credit-notes', component: SellerCreditNotes, meta: { requiresAuth: true } },
    { path: '/accounting/bill-payments', name: 'accounting-bill-payments', component: BillPayments, meta: { requiresAuth: true } },
    { path: '/accounting/sales-estimations', name: 'accounting-sales-estimations', component: SalesEstimations, meta: { requiresAuth: true, permission: { module: 'estimations', action: 'read' } } },
    { path: '/accounting/sales-orders', name: 'accounting-sales-orders', component: SalesOrders, meta: { requiresAuth: true } },
    { path: '/accounting/sales-invoices', name: 'accounting-sales-invoices', component: SalesInvoices, meta: { requiresAuth: true } },
    { path: '/accounting/sales-returns', name: 'accounting-sales-returns', component: SalesReturns, meta: { requiresAuth: true } },
    { path: '/accounting/customer-credit-notes', name: 'accounting-customer-credit-notes', component: CustomerCreditNotes, meta: { requiresAuth: true } },
    { path: '/profiles/account-holders/credit-cards', name: 'profiles-credit-cards', component: CreditCards, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/account-holders/customers', name: 'profiles-customers', component: Customers, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/account-holders/employees', name: 'profiles-employees', component: Employees, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/payment-methods', name: 'profiles-payment-methods', component: PaymentMethods, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/sales-taxes', name: 'profiles-sales-taxes', component: SalesTax, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/invoice-terms', name: 'profiles-invoice-terms', component: InvoiceTerms, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/inventory-items', name: 'profiles-inventory-items', component: InventoryItems, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/service-items', name: 'profiles-service-items', component: ServiceItems, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/profiles/job-sites', name: 'profiles-job-sites', component: JobSites, meta: { requiresAuth: true, planFeature: 'profiles_core', permission: { module: 'profiles', action: 'read' } } },
    { path: '/job-orders/estimation', name: 'job-orders-estimation', component: Estimation, meta: { requiresAuth: true, permission: { module: 'estimations', action: 'read' } } },
    { path: '/job-orders/quotation/:id?', name: 'job-orders-quotation', component: Quotation, meta: { requiresAuth: true, permission: { module: 'quotations', action: 'read' } } },
    { path: '/job-orders/orders/:id?', name: 'job-orders-orders', component: JobOrders, meta: { requiresAuth: true, permission: { module: 'job_orders', action: 'read' } } },
    { path: '/job-orders/daily-work-log/:id?', name: 'job-orders-daily-work-log', component: JobOrderDailyWorkLog, meta: { requiresAuth: true, permission: { module: 'job_orders', action: 'read' } } },
    { path: '/job-orders/short-status', name: 'job-orders-short-status', component: JobOrderShortStatus, meta: { requiresAuth: true, permission: { module: 'job_orders', action: 'read' } } },
    { path: '/job-orders/full-status', name: 'job-orders-full-status', component: JobOrderFullStatus, meta: { requiresAuth: true, permission: { module: 'job_orders', action: 'read' } } },
    { path: '/job-orders/tracker', name: 'job-orders-tracker', component: JobOrderTracker, meta: { requiresAuth: true, permission: { module: 'job_orders', action: 'read' } } },
    { path: '/job-orders/sales-invoice', name: 'job-orders-sales-invoice', component: JobOrderFinance, meta: { requiresAuth: true, permission: { module: 'job_orders', action: 'read' } } },
    { path: '/job-orders/payment-close', name: 'job-orders-payment-close', component: JobOrderFinance, meta: { requiresAuth: true, permission: { module: 'job_orders', action: 'read' } } },
    { path: '/vehicle-management/vehicles', name: 'vehicle-management-vehicles', component: Vehicle, meta: { requiresAuth: true } },
    { path: '/vehicle-management/drivers', name: 'vehicle-management-drivers', component: Drivers, meta: { requiresAuth: true } },
    { path: '/vehicle-management/insurance', name: 'vehicle-management-insurance', component: Insurance, meta: { requiresAuth: true } },
    { path: '/vehicle-management/fuel', name: 'vehicle-management-fuel', component: Fuel, meta: { requiresAuth: true } },
    { path: '/vehicle-management/repairs-maintenance', name: 'vehicle-management-repairs-maintenance', component: RepairMaintenance, meta: { requiresAuth: true } },
    { path: '/vehicle-management/accidents-damages', name: 'vehicle-management-accidents-damages', component: Accident, meta: { requiresAuth: true } },
    { path: '/vehicle-management/ticketing-complience', name: 'vehicle-management-ticketing-complience', component: TicketingComplience, meta: { requiresAuth: true } },
    { path: '/vehicle-management/daily-log', name: 'vehicle-management-daily-log', component: DailyLog, meta: { requiresAuth: true } },
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
    { path: '/workforce/daily-report', name: 'workforce-daily-report', component: DailyReport, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/workforce/incident-reports', name: 'workforce-incident-reports', component: WorkforceIncidentReports, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/workforce/staff-attendance', name: 'workforce-staff-attendance', component: WorkforceStaffAttendance, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/workforce/staff-requests', name: 'workforce-staff-requests', component: WorkforceWorkflowRecords, props: { type: 'request' }, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/workforce/task-assignments', name: 'workforce-task-assignments', component: WorkforceWorkflowRecords, props: { type: 'task' }, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
    { path: '/workforce/work-schedules', name: 'workforce-work-schedules', component: WorkforceWorkflowRecords, props: { type: 'schedule' }, meta: { requiresAuth: true, planFeature: 'workforce_management', permission: { module: 'workforce', action: 'read' } } },
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

const hasAccountAccess = (user) => Boolean(
  user?.account_access_ready
  || user?.account_setup_completed_at
  || user?.activation_status === 'active'
  || user?.activation_completed_at,
)
const isSuperAdmin = (user) => Boolean(user?.is_super_admin) || String(user?.role || '').trim().toLowerCase().replace(/[\s-]+/g, '_') === 'super_admin'
const homePath = (user) => isSuperAdmin(user) ? '/super-admin/dashboard' : '/dashboard'

router.beforeEach(async (to) => {
  if (to.meta?.guestOnly && authState.token) {
    // A page refresh restores the token before the user profile. Load it here
    // so a Super Admin is never treated as a company user and sent to /dashboard.
    try {
      if (!authState.user) await loadMe()
    } catch {
      clearToken()
      return true
    }
    setFlash(FLASH.ALREADY_LOGGED_IN, 'info', 2000)
    return { path: homePath(authState.user) }
  }
  if (!to.meta?.requiresAuth) return true
  if (!authState.token) {
    setFlash(FLASH.LOGIN_REQUIRED, 'warning', 3500)
    return { path: '/login', query: { redirect: to.fullPath } }
  }
  try {
    if (!authState.user) await loadMe()
    if (to.meta?.superAdmin && !isSuperAdmin(authState.user)) {
      setFlash('You do not have access to the Super Admin area.', 'warning', 3500)
      return { path: '/dashboard' }
    }
    if (isSuperAdmin(authState.user) && !to.meta?.superAdmin) {
      return { path: '/super-admin/dashboard' }
    }
    if (isSuperAdmin(authState.user)) return true
    if (!hasAccountAccess(authState.user)) {
      try {
        const { data } = await client.get('/account-setup')
        const setup = data?.data
        if (setup?.completed_at || setup?.activation_status === 'active' || setup?.activation_completed_at) {
          setUser({
            ...authState.user,
            account_setup_completed_at: setup.completed_at || authState.user?.account_setup_completed_at,
            activation_status: setup.activation_status,
            activation_completed_at: setup.activation_completed_at,
            account_access_ready: true,
          })
        }
      } catch {
        // ignore
      }
    }
    if (!hasAccountAccess(authState.user) && to.name !== 'account-setup') {
      return { path: '/account-setup' }
    }
    if (hasAccountAccess(authState.user) && to.name === 'account-setup') {
      return { path: homePath(authState.user) }
    }
    const requiredFeature = to.meta?.planFeature
    if (requiredFeature && !hasPlanFeature(authState.user?.selected_plan, requiredFeature)) {
      const requiredPlanLabel = formatPlanLabel(getRequiredPlan(requiredFeature))
      setFlash('This module requires the ${requiredPlanLabel} plan.', 'warning', 3500)
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
  // Resolve the authenticated role before the router starts. This prevents a
  // Super Admin refresh from briefly mounting, or being redirected to, the
  // Customer Admin dashboard.
  if (authState.token) {
    try {
      await loadMe()
    } catch {
      clearToken()
    }
  }

  const app = createApp(App)
  app.use(router)
  app.use(i18n)
  await initI18n()
  await router.isReady()
  app.mount('#app')
  initPageTranslator(router)

  // Avoid blank screen caused by body opacity transition
  document.body.classList.add('loaded')
}

bootstrap()

