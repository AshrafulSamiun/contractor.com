<template>
    <aside
        ref="sidebarRef"
        class="pm-app-sidebar"
        :class="{ 'pm-open': isOpen }"
    >
        <div class="pm-sidebar-brand">
            <div class="pm-sidebar-logo">
                <img
                    :src="logoWhite"
                    alt="Contractor logo"
                    class="pm-sidebar-logo-img"
                />
            </div>
            <div class="pm-sidebar-brand-copy">
                <div class="pm-sidebar-title">CONTRACTOR.COM</div>
                <div class="pm-sidebar-sub">CONTRACTOR MANAGEMENT</div>
            </div>
            <button
                class="pm-sidebar-close"
                type="button"
                aria-label="Close menu"
                @click="$emit('close')"
            >
                &times;
            </button>
        </div>

        <div ref="scrollRef" class="pm-sidebar-scroll">
            <nav class="pm-sidebar-nav">
                <RouterLink
                    class="pm-sidebar-link"
                    active-class="active"
                    to="/dashboard"
                    @click="closeSidebar"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect
                                    x="4"
                                    y="4"
                                    width="7"
                                    height="7"
                                    rx="2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <rect
                                    x="13"
                                    y="4"
                                    width="7"
                                    height="7"
                                    rx="2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <rect
                                    x="4"
                                    y="13"
                                    width="7"
                                    height="7"
                                    rx="2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <rect
                                    x="13"
                                    y="13"
                                    width="7"
                                    height="7"
                                    rx="2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                            </svg>
                        </span>
                        Dashboard
                    </span>
                </RouterLink>

                <RouterLink
                    v-if="canAccess('', 'calendar', 'read')"
                    class="pm-sidebar-link"
                    active-class="active"
                    to="/calendar"
                    @click="closeSidebar"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🗓️</span>
                        Calendar
                    </span>
                </RouterLink>

                <button
                    class="pm-sidebar-link"
                    type="button"
                    @click="noopMenuAction"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🗂️</span>
                        Scheduled
                    </span>
                </button>

                <RouterLink
                    class="pm-sidebar-link"
                    active-class="active"
                    to="/todo"
                    @click="closeSidebar"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">📋</span>
                        To Do List
                    </span>
                    <span
                        v-if="todoOpenCount > 0"
                        class="pm-sidebar-menu-badge"
                        >{{ formatBadgeCount(todoOpenCount) }}</span
                    >
                </RouterLink>

                <button
                    v-if="canProfiles"
                    class="pm-sidebar-link pm-has-children"
                    :class="{ active: open.profiles }"
                    type="button"
                    @click="toggleAndPin('profiles')"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">👥</span>
                        Profile
                    </span>
                    <span class="pm-chevron" :class="{ open: open.profiles }"
                        >v</span
                    >
                </button>
                <div v-if="canProfiles" v-show="open.profiles" class="pm-submenu">
                    <button
                        class="pm-sidebar-sublink pm-sidebar-subgroup"
                        :class="{ active: isSubActive('/profiles/account-holders') }"
                        type="button"
                        @click="open.accountHolders = !open.accountHolders"
                    >
                        <span class="pm-profile-menu-label"><span class="pm-profile-menu-icon pm-icon-accounts">♟</span>Account Holders</span>
                        <span class="pm-sub-chevron" :class="{ open: open.accountHolders }"></span>
                    </button>
                    <div v-show="open.accountHolders" class="pm-submenu pm-submenu-nested">
                        <RouterLink
                            v-for="item in accountHolderMenuItems"
                            :key="item.label"
                            class="pm-sidebar-sublink"
                            :class="{ active: isAccountHolderTypeActive(item.type) }"
                            :to="item.type === 'bank' ? '/profiles/account-holders/banks' : item.type === 'seller' ? '/profiles/account-holders/sellers' : item.type === 'insurance-company' ? '/profiles/account-holders/insurance-companies' : item.type === 'credit-card' ? '/profiles/account-holders/credit-cards' : item.type === 'customer' ? '/profiles/account-holders/customers' : item.type === 'employee' ? '/profiles/account-holders/employees' : { path: '/profiles/account-holders', query: { type: item.type } }"
                            @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon" :class="`pm-icon-${item.tone}`">{{ item.icon }}</span>{{ item.label }}</RouterLink>
                    </div>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/profiles/fixed-assets') }"
                        to="/profiles/fixed-assets"
                        @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon pm-icon-inventory">▣</span>Fixed Assets</RouterLink
                    >
                    <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/account-groups') }" to="/profiles/account-groups" @click="setActiveAndClose('profiles')"><span class="pm-profile-menu-icon pm-icon-accounts">▤</span>Account Groups</RouterLink>
                    <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/chart-of-accounts') }" to="/profiles/chart-of-accounts" @click="setActiveAndClose('profiles')"><span class="pm-profile-menu-icon pm-icon-accounts">▣</span>Chart of Accounts</RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/profiles/payment-methods') }"
                        to="/profiles/payment-methods"
                        @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon pm-icon-payment">▣</span>Payment Method</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/profiles/sales-taxes') }"
                        to="/profiles/sales-taxes"
                        @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon pm-icon-tax">%</span>Sales Tax</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/profiles/invoice-terms') }"
                        to="/profiles/invoice-terms"
                        @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon pm-icon-invoice">▤</span>Invoice Terms</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/profiles/inventory-items') }"
                        to="/profiles/inventory-items"
                        @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon pm-icon-inventory">□</span>Inventory Items</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/profiles/service-items') }"
                        to="/profiles/service-items"
                        @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon pm-icon-service">⚒</span>Service Items</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/profiles/job-sites') }"
                        to="/profiles/job-sites"
                        @click="setActiveAndClose('profiles')"
                        ><span class="pm-profile-menu-icon pm-icon-site">●</span>Job Sites</RouterLink
                    >
                </div>

                <button
                    class="pm-sidebar-link pm-has-children"
                    :class="{ active: open.jobOrders }"
                    type="button"
                    @click="toggleAndPin('jobOrders')"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🧑‍🔧</span>
                        Job Orders
                    </span>
                    <span class="pm-chevron" :class="{ open: open.jobOrders }"
                        >v</span
                    >
                </button>
                <div v-show="open.jobOrders" class="pm-submenu">
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/job-orders/estimation') }"
                        to="/job-orders/estimation"
                        @click="setActiveAndClose('jobOrders')"
                        >Estimate / Quote</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/job-orders/orders') }"
                        to="/job-orders/orders"
                        @click="setActiveAndClose('jobOrders')"
                        >Job Orders</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/job-orders/daily-work-log') }"
                        to="/job-orders/daily-work-log"
                        @click="setActiveAndClose('jobOrders')"
                        >Technician Report</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/job-orders/sales-invoice') }"
                        to="/job-orders/sales-invoice"
                        @click="setActiveAndClose('jobOrders')"
                        >Sales Invoice</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/job-orders/payment-close') }"
                        to="/job-orders/payment-close"
                        @click="setActiveAndClose('jobOrders')"
                        >Pay Bills / Close</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/job-orders/tracker') }"
                        to="/job-orders/tracker"
                        @click="setActiveAndClose('jobOrders')"
                        >Job Tracker</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/job-orders/full-status') }"
                        to="/job-orders/full-status"
                        @click="setActiveAndClose('jobOrders')"
                        >Job Orders List</RouterLink
                    >
                </div>

                <button
                    v-if="canAccess('workforce_management', 'workforce', 'read')"
                    class="pm-sidebar-link pm-has-children"
                    :class="{ active: open.workflow }"
                    type="button"
                    @click="toggleAndPin('workflow')"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🧰</span>
                        Staff Workflow
                    </span>
                    <span class="pm-chevron" :class="{ open: open.workflow }"
                        >v</span
                    >
                </button>
                <div
                    v-if="canAccess('workforce_management', 'workforce', 'read')"
                    v-show="open.workflow"
                    class="pm-submenu"
                >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/workforce/daily-reports') }"
                        to="/workforce/daily-reports"
                        @click="setActiveAndClose('workflow')"
                        >{{ t("sidebar.dailyReports") }}</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{
                            active: isSubActive('/workforce/incident-reports'),
                        }"
                        to="/workforce/incident-reports"
                        @click="setActiveAndClose('workflow')"
                        >{{ t("sidebar.incidentReports") }}</RouterLink
                    >
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/workforce/staff-attendance') }"
                        to="/workforce/staff-attendance"
                        @click="setActiveAndClose('workflow')"
                        >Staff Attendance</RouterLink
                    >
                    <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/workforce/staff-requests') }" to="/workforce/staff-requests" @click="setActiveAndClose('workflow')">Staff Request</RouterLink>
                    <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/workforce/task-assignments') }" to="/workforce/task-assignments" @click="setActiveAndClose('workflow')">Task Assignment</RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/workforce/timesheets') }"
                        to="/workforce/timesheets"
                        @click="setActiveAndClose('workflow')"
                        >{{ t("sidebar.timeSheet") }}</RouterLink
                    >
                    <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/workforce/work-schedules') }" to="/workforce/work-schedules" @click="setActiveAndClose('workflow')">Work Schedule</RouterLink>
                </div>

                <button
                    class="pm-sidebar-link pm-has-children"
                    :class="{ active: open.vehicleManagement }"
                    type="button"
                    @click="toggleAndPin('vehicleManagement')"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🚚</span>
                        Vehicle Management
                    </span>
                    <span
                        class="pm-chevron"
                        :class="{ open: open.vehicleManagement }"
                        >v</span
                    >
                </button>
                <div v-show="open.vehicleManagement" class="pm-submenu">
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{
                            active: isSubActive('/vehicle-management/vehicles'),
                        }"
                        to="/vehicle-management/vehicles"
                        @click="setActiveAndClose('vehicleManagement')"
                    >
                        Vehicles
                    </RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{
                            active: isSubActive('/vehicle-management/drivers'),
                        }"
                        to="/vehicle-management/drivers"
                        @click="setActiveAndClose('vehicleManagement')"
                    >
                        Drivers
                    </RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{
                            active: isSubActive('/vehicle-management/insurance'),
                        }"
                        to="/vehicle-management/insurance"
                        @click="setActiveAndClose('vehicleManagement')"
                    >
                        Insurance
                    </RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{ active: isSubActive('/vehicle-management/fuel') }"
                        to="/vehicle-management/fuel"
                        @click="setActiveAndClose('vehicleManagement')"
                    >
                        Expenses & Fuel
                    </RouterLink>
                    <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/vehicle-management/repairs-maintenance') }" to="/vehicle-management/repairs-maintenance" @click="setActiveAndClose('vehicleManagement')">
                        Repair & Maintenance
                    </RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{
                            active: isSubActive('/vehicle-management/accidents-damages'),
                        }"
                        to="/vehicle-management/accidents-damages"
                        @click="setActiveAndClose('vehicleManagement')"
                    >
                        Accidents & Incidents
                    </RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{
                            active: isSubActive('/vehicle-management/daily-log'),
                        }"
                        to="/vehicle-management/daily-log"
                        @click="setActiveAndClose('vehicleManagement')"
                    >
                        Milage & Trips Tracks
                    </RouterLink>
                    <RouterLink
                        class="pm-sidebar-sublink"
                        :class="{
                            active: isSubActive('/vehicle-management/ticketing-complience'),
                        }"
                        to="/vehicle-management/ticketing-complience"
                        @click="setActiveAndClose('vehicleManagement')"
                    >
                        Violation Tickets
                    </RouterLink>
                </div>

                <RouterLink
                    v-if="canAccess('notification_center', 'notifications', 'read')"
                    class="pm-sidebar-link"
                    :class="{ active: isSubActive('/notifications') }"
                    to="/notifications"
                    @click="closeSidebar"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🔔</span>
                        Notifications
                    </span>
                </RouterLink>

                <button
                    class="pm-sidebar-link pm-has-children"
                    :class="{ active: open.accounting }"
                    type="button"
                    @click="toggleAndPin('accounting')"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🧾</span>
                        Accounting
                    </span>
                    <span class="pm-chevron" :class="{ open: open.accounting }">v</span>
                </button>
                <div v-show="open.accounting" class="pm-submenu">
                    <button class="pm-sidebar-sublink pm-has-children" :class="{ active: isSubActive('/accounting/purchase') || isSubActive('/accounting/seller-') || isSubActive('/accounting/bill-payments') }" type="button" @click="open.purchase = !open.purchase">
                        <span>Purchase</span><span class="pm-chevron" :class="{ open: open.purchase }">v</span>
                    </button>
                    <div v-show="open.purchase" class="pm-submenu pm-submenu-nested">
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/purchase-orders') }" to="/accounting/purchase-orders" @click="closeSidebar">Purchase Order</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/purchase-invoices') }" to="/accounting/purchase-invoices" @click="closeSidebar">Purchase Invoice</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/purchase-returns') }" to="/accounting/purchase-returns" @click="closeSidebar">Purchase Return</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/seller-debit-notes') }" to="/accounting/seller-debit-notes" @click="closeSidebar">Sellers Debit Note</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/seller-credit-notes') }" to="/accounting/seller-credit-notes" @click="closeSidebar">Seller Credit Note</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/bill-payments') }" to="/accounting/bill-payments" @click="closeSidebar">Bill Entry</RouterLink>
                        <button v-for="item in purchaseMenuItems" :key="item" class="pm-sidebar-sublink" type="button" @click="noopMenuAction">{{ item }}</button>
                    </div>
                    <button class="pm-sidebar-sublink pm-has-children" :class="{ active: isSubActive('/accounting/sales') }" type="button" @click="open.sales = !open.sales">
                        <span>Sales</span><span class="pm-chevron" :class="{ open: open.sales }">v</span>
                    </button>
                    <div v-show="open.sales" class="pm-submenu pm-submenu-nested">
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/sales-estimations') }" to="/accounting/sales-estimations" @click="closeSidebar">Estimation / Quotes</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/sales-orders') }" to="/accounting/sales-orders" @click="closeSidebar">Sales Order</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/sales-invoices') }" to="/accounting/sales-invoices" @click="closeSidebar">Sales Invoice</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/sales-returns') }" to="/accounting/sales-returns" @click="closeSidebar">Sales Return</RouterLink>
                        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/accounting/customer-credit-notes') }" to="/accounting/customer-credit-notes" @click="closeSidebar">Customer Credit Note</RouterLink>
                    </div>
                </div>

                <RouterLink
                    v-if="canReportsGroup"
                    class="pm-sidebar-link"
                    :class="{ active: isSubActive('/reports') }"
                    :to="reportsEntryRoute"
                    @click="closeSidebar"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">📊</span>
                        Reports
                    </span>
                </RouterLink>

                <RouterLink
                    v-if="canSettings"
                    class="pm-sidebar-link"
                    :class="{ active: isSubActive('/settings') }"
                    to="/settings/date-time"
                    @click="closeSidebar"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">⚙️</span>
                        Settings
                    </span>
                </RouterLink>

                <RouterLink
                    v-if="canEmail"
                    class="pm-sidebar-link"
                    :class="{ active: isSubActive('/email') }"
                    to="/email"
                    @click="closeSidebar"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">✉️</span>
                        Emails
                    </span>
                </RouterLink>

                <button
                    v-if="canAccount"
                    class="pm-sidebar-link pm-has-children"
                    :class="{ active: isRouteWithin('/account') || open.account }"
                    type="button"
                    @click="toggleAndPin('account')"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">👤</span>
                        My Account
                    </span>
                    <span class="pm-chevron">{{ open.account ? '^' : 'v' }}</span>
                </button>
                <div v-if="canAccount && open.account" class="pm-submenu pm-account-submenu">
                    <RouterLink v-for="(item, index) in accountMenuItems" :key="item.label" class="pm-sidebar-sublink" :class="{ active: isSubActive(item.activePath, item.exact) }" :to="item.to" @click="setActiveAndClose('account')">
                        <span class="pm-profile-menu-icon pm-account-menu-icon"><AccountMenuIcon :name="accountMenuIcons[index]" /></span>
                        <span class="pm-account-menu-label">{{ item.label }}</span>
                    </RouterLink>
                </div>
            </nav>

            <div class="pm-sidebar-footer">
                <button
                    class="pm-sidebar-link"
                    type="button"
                    @click="goSupport"
                >
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🆘</span>
                        Help & Support
                    </span>
                </button>
                <button class="pm-sidebar-link" type="button" @click="doLogout">
                    <span class="pm-link-content">
                        <span class="pm-link-icon pm-link-icon-emoji" aria-hidden="true">🚪</span>
                        Log Out
                    </span>
                </button>
            </div>
        </div>
    </aside>
</template>

<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch,
} from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { logout as apiLogout, clearToken } from "../api/auth";
import client from "../api/client";
import { authState } from "../store/auth";
import { hasPlanFeature } from "../config/planFeatures";
import { hasUserPermission } from "../config/permissions";
import logoWhite from "../assets/logo-white.png";
import AccountMenuIcon from "./AccountMenuIcon.vue";

const emit = defineEmits(["close"]);

defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const sidebarRef = ref(null);
const scrollRef = ref(null);
const scrollKey = "pm_sidebar_scroll";
const activeKey = "pm_sidebar_active_group";
const todoOpenCount = ref(0);
const activeGroup = ref(sessionStorage.getItem(activeKey) || "");
let todoCountIntervalId = null;

const open = reactive({
    account: false,
    profiles: false,
    accountHolders: false,
    jobOrders: false,
    workflow: false,
    vehicleManagement: false,
    accounting: false,
    purchase: false,
    sales: false,
});

const accountMenuItems = [
    { label: 'Account Info', to: '/account/profile', activePath: '/account/profile', exact: true, icon: '▤' },
    { label: 'System Admin Profile', to: '/account/status', activePath: '/account/status', exact: true, icon: '♙' },
    { label: 'Company Profile', to: '/account/profile', activePath: '/account/profile', exact: true, icon: '▥' },
    { label: 'Service Plans & User Licences', to: '/plans/history', activePath: '/plans', icon: '♧' },
    { label: 'Billing & Payments', to: '/account/billing', activePath: '/account/billing', exact: true, icon: '▣' },
    { label: 'Account Statements', to: '/account/statements', activePath: '/account/statements', exact: true, icon: '▤' },
    { label: 'Tax Reports', to: '/account/billing', activePath: '/account/billing', exact: true, icon: '▧' },
    { label: 'Account Security', to: '/account/security', activePath: '/account/security', exact: true, icon: '♢' },
    { label: 'Messages', to: '/account/messages', activePath: '/account/messages', icon: '✉' },
    { label: 'Notifications', to: '/account/notifications', activePath: '/account/notifications', icon: '♧' },
    { label: 'Help & Support', to: '/account/help-support', activePath: '/account/help-support', exact: true, icon: '?' },
];

const purchaseMenuItems = [];

const accountMenuIcons = ['info', 'admin', 'company', 'plans', 'billing', 'statement', 'tax', 'security', 'messages', 'notifications', 'support'];
accountMenuItems[1].to = '/account/system-admin';
accountMenuItems[1].activePath = '/account/system-admin';
accountMenuItems[2].to = '/account/company-profile';
accountMenuItems[2].activePath = '/account/company-profile';
accountMenuItems[6].to = '/account/tax-report';
accountMenuItems[6].activePath = '/account/tax-report';

const accountHolderMenuItems = [
    { label: 'Banks', type: 'bank', icon: '⌂', tone: 'bank' },
    { label: 'Contractors', type: 'contractor', icon: '♟', tone: 'contractor' },
    { label: 'Credit Cards', type: 'credit-card', icon: '▣', tone: 'card' },
    { label: 'Customers', type: 'customer', icon: '●', tone: 'customer' },
    { label: 'Employees', type: 'employee', icon: '▤', tone: 'employee' },
    { label: 'Governments', type: 'government', icon: '⌂', tone: 'government' },
    { label: 'Insurance Companies', type: 'insurance-company', icon: '◇', tone: 'insurance' },
    { label: 'Loan Providers', type: 'loan-provider', icon: '$', tone: 'loan' },
    { label: 'Sellers', type: 'seller', icon: '●', tone: 'seller' },
    { label: 'Shareholders', type: 'shareholder', icon: '♚', tone: 'shareholder' },
    { label: 'Subcontractors', type: 'subcontractor', icon: '♚', tone: 'subcontractor' },
    { label: 'Suppliers', type: 'supplier', icon: '▱', tone: 'supplier' },
    { label: 'Utility Providers', type: 'utility-provider', icon: 'ϟ', tone: 'utility' },
];

const canFeature = (feature) =>
    !feature || hasPlanFeature(authState.user?.selected_plan, feature);
const canAccess = (feature, module, action = "read") =>
    canFeature(feature) && hasUserPermission(authState.user, module, action);

const canProfiles = computed(() =>
    canAccess("profiles_core", "profiles", "read"),
);
const canSettings = computed(() => canAccess("", "settings", "read"));
const canEmail = computed(() => canAccess("", "email", "read"));
const canAccount = computed(() => canAccess("", "account", "read"));
const canParcelReports = computed(() =>
    canAccess("reporting", "parcels", "read"),
);
const canApprovalSlaReport = computed(() =>
    canAccess("reporting", "workforce", "read"),
);
const canPickupSlaReport = computed(() =>
    canAccess("reporting", "pickup", "read"),
);
const canReportsGroup = computed(
    () =>
        canParcelReports.value ||
        canApprovalSlaReport.value ||
        canPickupSlaReport.value,
);
const reportsEntryRoute = computed(() => {
    if (canParcelReports.value) return "/reports/parcels/pending";
    if (canApprovalSlaReport.value) return "/reports/approval-sla";
    if (canPickupSlaReport.value) return "/reports/pickup-sla";
    return "/dashboard";
});

const topGroups = ["account", "profiles", "jobOrders", "workflow", "vehicleManagement", "accounting"];

const closeAllGroups = (except = "") => {
    topGroups.forEach((group) => {
        open[group] = group === except;
    });
};

const setActiveGroup = (key) => {
    if (!key) return;
    activeGroup.value = key;
    sessionStorage.setItem(activeKey, key);
    closeAllGroups(key);
};

const setActiveAndClose = (key) => {
    setActiveGroup(key);
    closeSidebar();
};

const toggleAndPin = (key) => {
    const nextState = !open[key];
    closeAllGroups(nextState ? key : "");
    if (nextState) {
        activeGroup.value = key;
        sessionStorage.setItem(activeKey, key);
    } else {
        activeGroup.value = "";
        sessionStorage.removeItem(activeKey);
    }
};

const closeSidebar = () => {
    if (window.innerWidth <= 992) {
        emit("close");
    }
};

const noopMenuAction = () => {
    closeSidebar();
};

const goSupport = async () => {
    closeAllGroups(activeGroup.value || "");
    await router.push("/contact");
};

const formatBadgeCount = (count) => (count > 99 ? "99+" : String(count));

const fetchTodoOpenCount = async () => {
    try {
        const { data } = await client.get("/todo");
        const tasks = Array.isArray(data?.data) ? data.data : [];
        todoOpenCount.value = tasks.filter(
            (task) => String(task?.status || "").toLowerCase() !== "completed",
        ).length;
    } catch {
        todoOpenCount.value = 0;
    }
};

const doLogout = async () => {
    try {
        await apiLogout();
    } catch {
        // ignore logout errors
    }
    clearToken();
    window.location.assign("/login");
};

const isSubActive = (path, exact = false) =>
    exact ? route.path === path : route.path.startsWith(path);

const isRouteWithin = (basePath, path = route.path) =>
    path === basePath || path.startsWith(`${basePath}/`);

const isAccountHolderTypeActive = (type) =>
    (type === 'insurance-company' && route.path === '/profiles/account-holders/insurance-companies') ||
    (type === 'seller' && route.path === '/profiles/account-holders/sellers') ||
    (route.path === '/profiles/account-holders' && route.query.type === type);

const deriveGroupFromRoute = (path) => {
    if (isRouteWithin("/accounting", path)) return "accounting";
    if (isRouteWithin("/account", path) || path.startsWith("/plans") || path.startsWith("/email") || path.startsWith("/notifications") || path.startsWith("/contact")) return "account";
    if (path.startsWith("/profiles")) return "profiles";
    if (path.startsWith("/job-orders")) return "jobOrders";
    if (path.startsWith("/vehicle-management")) return "vehicleManagement";
    if (path.startsWith("/workforce")) return "workflow";
    return "";
};

const syncOpenFromRoute = (path) => {
    const routeGroup = deriveGroupFromRoute(path);
    if (routeGroup) {
        activeGroup.value = routeGroup;
        sessionStorage.setItem(activeKey, routeGroup);
    }
    closeAllGroups(routeGroup || "");
    open.purchase =
        path.startsWith("/accounting/purchase") ||
        path.startsWith("/accounting/seller-") ||
        path.startsWith("/accounting/bill-payments");
    open.sales = path.startsWith("/accounting/sales");
};

const restoreScroll = () => {
    const el = scrollRef.value || sidebarRef.value;
    if (!el) return;
    const saved = sessionStorage.getItem(scrollKey);
    if (saved !== null) {
        const value = parseInt(saved, 10);
        el.scrollTop = Number.isFinite(value) ? value : 0;
    }
};

const saveScroll = () => {
    const el = scrollRef.value || sidebarRef.value;
    if (!el) return;
    sessionStorage.setItem(scrollKey, String(el.scrollTop || 0));
};

const handleSidebarWheel = (event) => {
    const scrollEl = scrollRef.value || sidebarRef.value;
    if (!scrollEl || window.innerWidth <= 992) return;

    const maxTop = Math.max(scrollEl.scrollHeight - scrollEl.clientHeight, 0);
    if (maxTop <= 0) {
        event.preventDefault();
        event.stopPropagation();
        return;
    }

    const nextTop = Math.max(
        0,
        Math.min(maxTop, scrollEl.scrollTop + event.deltaY),
    );
    scrollEl.scrollTop = nextTop;
    event.preventDefault();
    event.stopPropagation();
};

onMounted(() => {
    closeAllGroups(activeGroup.value || "");
    nextTick(restoreScroll);
    fetchTodoOpenCount();
    todoCountIntervalId = window.setInterval(fetchTodoOpenCount, 60000);
    const el = scrollRef.value || sidebarRef.value;
    const host = sidebarRef.value;
    if (el) {
        el.addEventListener("scroll", saveScroll, { passive: true });
    }
    if (host) {
        host.addEventListener("wheel", handleSidebarWheel, { passive: false });
    }
});

onBeforeUnmount(() => {
    saveScroll();
    if (todoCountIntervalId) {
        clearInterval(todoCountIntervalId);
        todoCountIntervalId = null;
    }
    const el = scrollRef.value || sidebarRef.value;
    const host = sidebarRef.value;
    if (el) {
        el.removeEventListener("scroll", saveScroll);
    }
    if (host) {
        host.removeEventListener("wheel", handleSidebarWheel);
    }
});

watch(
    () => route.path,
    (path) => {
        syncOpenFromRoute(path);
        fetchTodoOpenCount();
        nextTick(restoreScroll);
    },
    { immediate: true },
);
</script>
