<template>
    <aside class="pm-app-sidebar" :class="{ 'pm-open': isOpen }">
        <div class="pm-sidebar-brand">
            <div class="pm-sidebar-logo">PM</div>
            <div>
                <div class="pm-sidebar-title">Contractor</div>
                <div class="pm-sidebar-sub">Dashboard System</div>
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
            <button class="pm-sidebar-link" type="button" @click="closeSidebar">
                <span class="pm-link-content">
                    <span class="pm-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect
                                x="4"
                                y="6"
                                width="16"
                                height="14"
                                rx="2"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <path
                                d="M8 4v4M16 4v4"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                    Calendar
                </span>
            </button>
            <RouterLink
                class="pm-sidebar-link"
                active-class="active"
                to="/todo"
                @click="closeSidebar"
            >
                <span class="pm-link-content">
                    <span class="pm-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M7 6h10M7 12h7M7 18h6"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                    To Do
                </span>
            </RouterLink>
            <button class="pm-sidebar-link" type="button" @click="closeSidebar">
                <span class="pm-link-content">
                    <span class="pm-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M5 7h14M5 12h14M5 17h10"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                    Announcement
                </span>
            </button>
        </nav>

        <div class="pm-sidebar-divider"></div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('profiles')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M4 11l8-6 8 6v8a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1v-8z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>
                Profiles
            </span>
            <span class="pm-chevron" :class="{ open: open.profiles }">v</span>
        </button>
        <div v-show="open.profiles" class="pm-submenu">
            <RouterLink
                class="pm-sidebar-sublink"
                to="/profiles/facilities"
                @click="closeSidebar"
                >Facilities / Property</RouterLink
            >

            <button
                class="pm-sidebar-sublink pm-subtoggle"
                :class="{ open: open.lockers }"
                type="button"
                @click="toggle('lockers')"
            >
                <span>Lockers</span>
                <span class="pm-sub-chevron" :class="{ open: open.lockers }"
                    >v</span
                >
            </button>
            <div v-show="open.lockers" class="pm-submenu pm-submenu-nested">
                <RouterLink
                    class="pm-sidebar-sublink"
                    to="/profiles/lockers"
                    @click="closeSidebar"
                    >Locker List</RouterLink
                >
                <RouterLink
                    class="pm-sidebar-sublink"
                    to="/profiles/lockers/access"
                    @click="closeSidebar"
                    >Locker Access</RouterLink
                >
            </div>

            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Parcels Room
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Recipients
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Couriers
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Seller
            </button>
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('pickup')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M4 7h16M4 12h10M4 17h7"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                Parcel / Delivery Items
            </span>
            <span class="pm-chevron" :class="{ open: open.pickup }">v</span>
        </button>
        <div v-show="open.pickup" class="pm-submenu">
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Pick up at Front Desk
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Pick up from Facility Locker
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Pick up from External Locker
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Pick up from Counter Staff
            </button>
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('delivery')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M6 17h12M7 7h7l4 4v4"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                        <circle
                            cx="8"
                            cy="17"
                            r="2"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <circle
                            cx="16"
                            cy="17"
                            r="2"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                    </svg>
                </span>
                Delivery & Pick Up
            </span>
            <span class="pm-chevron" :class="{ open: open.delivery }">v</span>
        </button>
        <div v-show="open.delivery" class="pm-submenu">
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
            >
                Delivery Status</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >New Arrival</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Pick Up</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Delivered</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Rejected Parcels</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Lost / Damaged</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Expired Holding</RouterLink
            >
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('users')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle
                            cx="9"
                            cy="8"
                            r="3"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M4 19c0-3 3-5 5-5s5 2 5 5"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M16 11a3 3 0 1 0 0-6"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M15 14c2.5 0 5 1.5 5 4.5"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                    </svg>
                </span>
                Users Management
            </span>
            <span class="pm-chevron" :class="{ open: open.users }">v</span>
        </button>
        <div v-show="open.users" class="pm-submenu">
            <RouterLink
                class="pm-sidebar-sublink"
                to="/admin/users"
                @click="closeSidebar"
                >Add New User</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/admin/users"
                @click="closeSidebar"
                >Roles & Permissions</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/admin/users"
                @click="closeSidebar"
                >Activate / Deactivate</RouterLink
            >
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('workforce')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M4 19V5m5 14V9m5 10V7m5 12v-6"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                Workforce Management
            </span>
            <span class="pm-chevron" :class="{ open: open.workforce }">v</span>
        </button>
        <div v-show="open.workforce" class="pm-submenu">
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Daily Reports
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Incident Reports
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Time Sheet
            </button>
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('reports')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M6 4h12v16H6z"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M9 8h6M9 12h6M9 16h6"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                Parcel Reports
            </span>
            <span class="pm-chevron" :class="{ open: open.reports }">v</span>
        </button>
        <div v-show="open.reports" class="pm-submenu">
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Pending</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Picked Up</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Delivered</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Rejected Parcels</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Lost / Damaged</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/parcels"
                @click="closeSidebar"
                >Expired Holding</RouterLink
            >
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('settings')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M12 3v3M12 18v3M4.7 4.7l2.1 2.1M17.2 17.2l2.1 2.1M3 12h3M18 12h3M4.7 19.3l2.1-2.1M17.2 6.8l2.1-2.1"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                        <circle
                            cx="12"
                            cy="12"
                            r="3"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                    </svg>
                </span>
                System Settings
            </span>
            <span class="pm-chevron" :class="{ open: open.settings }">v</span>
        </button>
        <div v-show="open.settings" class="pm-submenu">
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Date & Time
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Theme
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Parcel Holding Limits
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Notification Settings
            </button>
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('notify')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M6 17h12l-1-2v-4a5 5 0 1 0-10 0v4l-1 2z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M10 19a2 2 0 0 0 4 0"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                Notifications
            </span>
            <span class="pm-chevron" :class="{ open: open.notify }">v</span>
        </button>
        <div v-show="open.notify" class="pm-submenu">
            <RouterLink
                class="pm-sidebar-sublink"
                to="/notifications"
                @click="closeSidebar"
                >Parcel Arrival</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/notifications"
                @click="closeSidebar"
                >Pickup Request</RouterLink
            >
            <RouterLink
                class="pm-sidebar-sublink"
                to="/notifications"
                @click="closeSidebar"
                >Parcel Return</RouterLink
            >
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('email')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <rect
                            x="4"
                            y="6"
                            width="16"
                            height="12"
                            rx="2"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M4 8l8 5 8-5"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                Email
            </span>
            <span class="pm-chevron" :class="{ open: open.email }">v</span>
        </button>
        <div v-show="open.email" class="pm-submenu">
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                New
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Inbox
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Sent
            </button>
        </div>

        <button
            class="pm-sidebar-link pm-has-children"
            type="button"
            @click="toggle('account')"
        >
            <span class="pm-link-content">
                <span class="pm-link-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle
                            cx="12"
                            cy="8"
                            r="3"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M5 20c1-4 4-6 7-6s6 2 7 6"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                    </svg>
                </span>
                My Account
            </span>
            <span class="pm-chevron" :class="{ open: open.account }">v</span>
        </button>
        <div v-show="open.account" class="pm-submenu">
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Profile
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Account Status
            </button>
            <RouterLink
                class="pm-sidebar-sublink"
                to="/plans"
                @click="closeSidebar"
                >Billing & Subscription</RouterLink
            >
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Account Security
            </button>
            <button
                class="pm-sidebar-sublink"
                type="button"
                @click="closeSidebar"
            >
                Account Recovery
            </button>
        </div>

        <div class="pm-sidebar-footer">
            <button class="pm-sidebar-link" type="button" @click="closeSidebar">
                Help & Support
            </button>
            <button class="pm-sidebar-link" type="button" @click="closeSidebar">
                Log Out
            </button>
        </div>
    </aside>
</template>

<script setup>
import { reactive } from "vue";
import { RouterLink } from "vue-router";

const emit = defineEmits(["close"]);

defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

const open = reactive({
    profiles: false,
    lockers: false,
    pickup: false,
    delivery: false,
    users: false,
    workforce: false,
    reports: false,
    settings: false,
    notify: false,
    email: false,
    account: false,
});

const toggle = (key) => {
    open[key] = !open[key];
};

const closeSidebar = () => {
    emit("close");
};
</script>
