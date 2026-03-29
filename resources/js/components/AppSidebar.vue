<template>
  <aside ref="sidebarRef" class="pm-app-sidebar" :class="{ 'pm-open': isOpen }">
    <div class="pm-sidebar-brand">
      <div class="pm-sidebar-logo">
        <img :src="logoWhite" alt="DeskDrop logo" class="pm-sidebar-logo-img" />
      </div>
      <div>
        <div class="pm-sidebar-title">Parcel MS</div>
        <div class="pm-sidebar-sub">Front Desk Ops</div>
      </div>
      <button class="pm-sidebar-close" type="button" aria-label="Close menu" @click="$emit('close')">&times;</button>
    </div>

    <div ref="scrollRef" class="pm-sidebar-scroll">
    <nav class="pm-sidebar-nav">
      <RouterLink class="pm-sidebar-link" active-class="active" to="/dashboard" @click="closeSidebar">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <rect x="4" y="4" width="7" height="7" rx="2" stroke="currentColor" stroke-width="1.5"/>
              <rect x="13" y="4" width="7" height="7" rx="2" stroke="currentColor" stroke-width="1.5"/>
              <rect x="4" y="13" width="7" height="7" rx="2" stroke="currentColor" stroke-width="1.5"/>
              <rect x="13" y="13" width="7" height="7" rx="2" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </span>
          {{ t('sidebar.dashboard') }}
        </span>
      </RouterLink>
      <RouterLink v-if="canAccess('', 'calendar', 'read')" class="pm-sidebar-link" active-class="active" to="/calendar" @click="closeSidebar">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <rect x="4" y="6" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
              <path d="M8 4v4M16 4v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
          {{ t('sidebar.calendar') }}
        </span>
      </RouterLink>
      <RouterLink class="pm-sidebar-link" active-class="active" to="/todo" @click="closeSidebar">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M7 6h10M7 12h7M7 18h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
          {{ t('sidebar.todo') }}
        </span>
        <span v-if="todoOpenCount > 0" class="pm-sidebar-menu-badge">{{ formatBadgeCount(todoOpenCount) }}</span>
      </RouterLink>
      <RouterLink v-if="canAccess('', 'announcements', 'read')" class="pm-sidebar-link" active-class="active" to="/announcements" @click="closeSidebar">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M5 7h14M5 12h14M5 17h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
          {{ t('sidebar.announcement') }}
        </span>
      </RouterLink>
    </nav>

    <div class="pm-sidebar-divider"></div>

      <button v-if="canProfiles" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('profiles')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M4 11l8-6 8 6v8a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1v-8z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
          </span>
          {{ t('sidebar.profiles') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.profiles }">v</span>
      </button>
      <div v-if="canProfiles" v-show="open.profiles" class="pm-submenu">
        <RouterLink
          v-if="canAccess('profiles_core', 'profiles', 'read')"
          class="pm-sidebar-sublink"
          :class="{ active: isSubActive('/profiles/facilities') }"
          to="/profiles/facilities"
          @click="setActiveGroup('profiles'); closeSidebar"
        >{{ t('sidebar.facilitiesProperty') }}</RouterLink>

        <button
          v-if="canStorage"
          class="pm-sidebar-sublink pm-subtoggle"
          :class="{ open: open.lockers }"
          type="button"
          @click="toggle('lockers'); setActiveGroup('profiles')"
        >
          <span>{{ t('sidebar.lockers') }}</span>
          <span class="pm-sub-chevron" :class="{ open: open.lockers }"></span>
        </button>
        <div v-show="open.lockers" class="pm-submenu pm-submenu-nested">
        <RouterLink v-if="canStorage" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/lockers', '', true) }" to="/profiles/lockers" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.lockerList') }}</RouterLink>
          <RouterLink
            v-if="canLockerAccess"
            class="pm-sidebar-sublink"
            :class="{ active: isSubActive('/profiles/lockers/access', '', true) }"
            to="/profiles/lockers/access"
            @click="setActiveGroup('profiles'); closeSidebar"
          >{{ t('sidebar.lockerAccess') }}</RouterLink>
          <RouterLink
            v-if="canStorage"
            class="pm-sidebar-sublink"
            :class="{ active: isSubActive('/profiles/lockers/facility-owned', '', true) }"
            to="/profiles/lockers/facility-owned"
            @click="setActiveGroup('profiles'); closeSidebar"
          >{{ t('sidebar.facilityOwned') }}</RouterLink>
          <RouterLink
            v-if="canLockerAccess"
            class="pm-sidebar-sublink"
            :class="{ active: isSubActive('/profiles/lockers/third-party-owned', '', true) }"
            to="/profiles/lockers/third-party-owned"
            @click="setActiveGroup('profiles'); closeSidebar"
          >{{ t('sidebar.thirdPartyOwned') }}</RouterLink>
        </div>

        <button
          v-if="canStorage"
          class="pm-sidebar-sublink pm-subtoggle"
          :class="{ open: open.parcelsRoom }"
          type="button"
          @click="toggle('parcelsRoom'); setActiveGroup('profiles')"
        >
          <span>{{ t('sidebar.parcelsRoom') }}</span>
          <span class="pm-sub-chevron" :class="{ open: open.parcelsRoom }"></span>
        </button>
        <div v-show="open.parcelsRoom" class="pm-submenu pm-submenu-nested">
          <RouterLink v-if="canStorageCreate" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/storage', '', true) }" to="/profiles/storage" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.newStorage') }}</RouterLink>
          <RouterLink v-if="canStorage" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/storage/list', '', true) }" to="/profiles/storage/list" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.storageList') }}</RouterLink>
        </div>

        <RouterLink v-if="canAccess('profiles_core', 'profiles', 'read')" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/recipients') }" to="/profiles/recipients" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.recipients') }}</RouterLink>
        <RouterLink v-if="canAccess('profiles_core', 'profiles', 'read')" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/couriers') }" to="/profiles/couriers" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.couriers') }}</RouterLink>
        <RouterLink v-if="canAccess('profiles_core', 'profiles', 'read')" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/items') }" to="/profiles/items" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.parcelDeliveryItems') }}</RouterLink>
        <RouterLink v-if="canAccess('profiles_core', 'profiles', 'read')" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/delivery-methods') }" to="/profiles/delivery-methods" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.deliveryMethods') }}</RouterLink>
        <RouterLink v-if="canAccess('profiles_core', 'profiles', 'read')" class="pm-sidebar-sublink" :class="{ active: isSubActive('/profiles/sellers') }" to="/profiles/sellers" @click="setActiveGroup('profiles'); closeSidebar">{{ t('sidebar.seller') }}</RouterLink>
      </div>

      <button v-if="canAccess('pickup_management', 'pickup', 'read')" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('pickup')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M4 7h16M4 12h10M4 17h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
          {{ t('sidebar.parcelDeliveryItems') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.pickup }">v</span>
      </button>
      <div v-if="canAccess('pickup_management', 'pickup', 'read')" v-show="open.pickup" class="pm-submenu">
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/pickup/front-desk') }" to="/pickup/front-desk" @click="setActiveGroup('pickup'); setActiveSub('pickup-frontdesk'); closeSidebar">{{ t('pickupMethods.frontDesk') }}</RouterLink>
         <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/pickup/facility-locker') }" to="/pickup/facility-locker" @click="setActiveGroup('pickup'); setActiveSub('pickup-facility'); closeSidebar">{{ t('pickupMethods.facilityLocker') }}</RouterLink>
        <RouterLink
          v-if="canAccess('pickup_external_locker', 'pickup', 'read')"
          class="pm-sidebar-sublink"
          :class="{ active: isSubActive('/pickup/external-locker') }"
          to="/pickup/external-locker"
          @click="setActiveGroup('pickup'); setActiveSub('pickup-external'); closeSidebar"
        >{{ t('pickupMethods.externalLocker') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/pickup/counter-staff') }" to="/pickup/counter-staff" @click="setActiveGroup('pickup'); setActiveSub('pickup-counter'); closeSidebar">{{ t('pickupMethods.counterStaff') }}</RouterLink>
      </div>

      <button v-if="canDelivery" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('delivery')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M6 17h12M7 7h7l4 4v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <circle cx="8" cy="17" r="2" stroke="currentColor" stroke-width="1.5"/>
              <circle cx="16" cy="17" r="2" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </span>
          {{ t('sidebar.deliveryPickup') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.delivery }">v</span>
      </button>
      <div v-if="canDelivery" v-show="open.delivery" class="pm-submenu">
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/delivery/status', 'delivery') }" to="/delivery/status" @click="setActiveGroup('delivery'); closeSidebar">{{ t('sidebar.deliveryStatus') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/delivery/new-arrival', 'delivery') }" to="/delivery/new-arrival" @click="setActiveGroup('delivery'); closeSidebar">{{ t('sidebar.newArrival') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/delivery/pick-up', 'delivery') }" to="/delivery/pick-up" @click="setActiveGroup('delivery'); closeSidebar">{{ t('sidebar.pickUp') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/delivery/delivered', 'delivery') }" to="/delivery/delivered" @click="setActiveGroup('delivery'); closeSidebar">{{ t('sidebar.delivered') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/delivery/rejected', 'delivery') }" to="/delivery/rejected" @click="setActiveGroup('delivery'); closeSidebar">{{ t('sidebar.rejectedParcels') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/delivery/lost-damaged', 'delivery') }" to="/delivery/lost-damaged" @click="setActiveGroup('delivery'); closeSidebar">{{ t('sidebar.lostDamaged') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/delivery/expired', 'delivery') }" to="/delivery/expired" @click="setActiveGroup('delivery'); closeSidebar">{{ t('sidebar.expiredHolding') }}</RouterLink>
      </div>

      <button v-if="canAccess('user_management', 'users', 'read')" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('users')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.5"/>
              <path d="M4 19c0-3 3-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.5"/>
              <path d="M16 11a3 3 0 1 0 0-6" stroke="currentColor" stroke-width="1.5"/>
              <path d="M15 14c2.5 0 5 1.5 5 4.5" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </span>
         {{ t('sidebar.usersManagement') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.users }">v</span>
      </button>
      <div v-if="canAccess('user_management', 'users', 'read')" v-show="open.users" class="pm-submenu">
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/admin/users') }" to="/admin/users" @click="setActiveGroup('users'); closeSidebar">{{ t('sidebar.addNewUser') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/admin/users/roles') }" to="/admin/users/roles" @click="setActiveGroup('users'); closeSidebar">{{ t('sidebar.rolesPermissions') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/admin/users/status') }" to="/admin/users/status" @click="setActiveGroup('users'); closeSidebar">{{ t('sidebar.activateDeactivate') }}</RouterLink>
      </div>

      <button v-if="canAccess('workforce_management', 'workforce', 'read')" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('workforce')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M4 19V5m5 14V9m5 10V7m5 12v-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
        {{ t('sidebar.workforceManagement') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.workforce }">v</span>
      </button>
      <div v-if="canAccess('workforce_management', 'workforce', 'read')" v-show="open.workforce" class="pm-submenu">
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/workforce/daily-reports') }" to="/workforce/daily-reports" @click="setActiveGroup('workforce'); closeSidebar">{{ t('sidebar.dailyReports') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/workforce/incident-reports') }" to="/workforce/incident-reports" @click="setActiveGroup('workforce'); closeSidebar">{{ t('sidebar.incidentReports') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/workforce/timesheets') }" to="/workforce/timesheets" @click="setActiveGroup('workforce'); closeSidebar">{{ t('sidebar.timeSheet') }}</RouterLink>
      </div>

      <button v-if="canReportsGroup" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('reports')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M6 4h12v16H6z" stroke="currentColor" stroke-width="1.5"/>
              <path d="M9 8h6M9 12h6M9 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
          {{ t('sidebar.parcelReports') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.reports }">v</span>
      </button>
      <div v-if="canReportsGroup" v-show="open.reports" class="pm-submenu">
        <RouterLink v-if="canParcelReports" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/parcels/pending') }" to="/reports/parcels/pending" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.pending') }}</RouterLink>
        <RouterLink v-if="canParcelReports" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/parcels/picked-up') }" to="/reports/parcels/picked-up" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.pickedUp') }}</RouterLink>
        <RouterLink v-if="canParcelReports" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/parcels/delivered') }" to="/reports/parcels/delivered" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.delivered') }}</RouterLink>
        <RouterLink v-if="canParcelReports" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/parcels/rejected') }" to="/reports/parcels/rejected" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.rejectedParcels') }}</RouterLink>
        <RouterLink v-if="canParcelReports" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/parcels/lost-damaged') }" to="/reports/parcels/lost-damaged" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.lostDamaged') }}</RouterLink>
        <RouterLink v-if="canParcelReports" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/parcels/expired') }" to="/reports/parcels/expired" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.expiredHolding') }}</RouterLink>
        <RouterLink v-if="canApprovalSlaReport" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/approval-sla') }" to="/reports/approval-sla" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.approvalSla') }}</RouterLink>
        <RouterLink v-if="canPickupSlaReport" class="pm-sidebar-sublink" :class="{ active: isSubActive('/reports/pickup-sla') }" to="/reports/pickup-sla" @click="setActiveGroup('reports'); closeSidebar">{{ t('sidebar.pickupSla') }}</RouterLink>
      </div>

      <RouterLink
        v-if="canSettings"
        class="pm-sidebar-link"
        :class="{ active: isSubActive('/settings') }"
        to="/settings/date-time"
        @click="setActiveGroup('settings'); closeSidebar"
      >
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M12 3v3M12 18v3M4.7 4.7l2.1 2.1M17.2 17.2l2.1 2.1M3 12h3M18 12h3M4.7 19.3l2.1-2.1M17.2 6.8l2.1-2.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </span>
          {{ t('sidebar.systemSettings') }}
        </span>
      </RouterLink>

      <button v-if="canAccess('notification_center', 'notifications', 'read')" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('notify')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M6 17h12l-1-2v-4a5 5 0 1 0-10 0v4l-1 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
              <path d="M10 19a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
        {{ t('sidebar.notifications') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.notify }">v</span>
      </button>
      <div v-if="canAccess('notification_center', 'notifications', 'read')" v-show="open.notify" class="pm-submenu">
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/notifications/parcel-arrival') }" to="/notifications/parcel-arrival" @click="setActiveGroup('notify'); closeSidebar">{{ t('sidebar.parcelArrival') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/notifications/pickup-request') }" to="/notifications/pickup-request" @click="setActiveGroup('notify'); closeSidebar">{{ t('sidebar.pickupRequest') }}</RouterLink>
        <RouterLink class="pm-sidebar-sublink" :class="{ active: isSubActive('/notifications/parcel-return') }" to="/notifications/parcel-return" @click="setActiveGroup('notify'); closeSidebar">{{ t('sidebar.parcelReturn') }}</RouterLink>
      </div>

      <button v-if="canEmail" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('email')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <rect x="4" y="6" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
              <path d="M4 8l8 5 8-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </span>
          {{ t('sidebar.email') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.email }">v</span>
      </button>
      <div v-if="canEmail" v-show="open.email" class="pm-submenu">
        <RouterLink v-if="canEmail" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email', '', true, 'email-dashboard') }" to="/email" @click="setActiveGroup('email'); setActiveSub('email-dashboard'); closeSidebar">{{ t('sidebar.overview') }}</RouterLink>
        <RouterLink v-if="canEmailCreate" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email/new', '', true, 'email-new') }" to="/email/new" @click="setActiveGroup('email'); setActiveSub('email-new'); closeSidebar">{{ t('sidebar.new') }}</RouterLink>
        <RouterLink v-if="canEmail" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email/inbox', '', true, 'email-inbox') }" to="/email/inbox" @click="setActiveGroup('email'); setActiveSub('email-inbox'); closeSidebar">{{ t('sidebar.inbox') }}</RouterLink>
        <RouterLink v-if="canEmail" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email/sent', '', true, 'email-sent') }" to="/email/sent" @click="setActiveGroup('email'); setActiveSub('email-sent'); closeSidebar">{{ t('sidebar.sent') }}</RouterLink>
        <RouterLink v-if="canEmail" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email/drafts', '', true, 'email-drafts') }" to="/email/drafts" @click="setActiveGroup('email'); setActiveSub('email-drafts'); closeSidebar">{{ t('sidebar.drafts') }}</RouterLink>
        <RouterLink v-if="canEmail" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email/trash', '', true, 'email-trash') }" to="/email/trash" @click="setActiveGroup('email'); setActiveSub('email-trash'); closeSidebar">{{ t('sidebar.trash') }}</RouterLink>
        <RouterLink v-if="canEmail" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email/templates', '', true, 'email-templates') }" to="/email/templates" @click="setActiveGroup('email'); setActiveSub('email-templates'); closeSidebar">{{ t('sidebar.templates') }}</RouterLink>
        <RouterLink v-if="canEmail" class="pm-sidebar-sublink" :class="{ active: isSubActive('/email/settings', '', true, 'email-settings') }" to="/email/settings" @click="setActiveGroup('email'); setActiveSub('email-settings'); closeSidebar">{{ t('sidebar.settings') }}</RouterLink>
      </div>

      <button v-if="canAccount" class="pm-sidebar-link pm-has-children" type="button" @click="toggleAndPin('account')">
        <span class="pm-link-content">
          <span class="pm-link-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="8" r="3" stroke="currentColor" stroke-width="1.5"/>
              <path d="M5 20c1-4 4-6 7-6s6 2 7 6" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </span>
          {{ t('sidebar.myAccount') }}
        </span>
        <span class="pm-chevron" :class="{ open: open.account }">v</span>
      </button>
      <div v-if="canAccount" v-show="open.account" class="pm-submenu">
        <RouterLink v-if="canAccount" class="pm-sidebar-sublink" :class="{ active: isSubActive('/account/profile', 'account') }" to="/account/profile" @click="setActiveGroup('account'); setActiveSub('account-profile'); closeSidebar">{{ t('common.profile') }}</RouterLink>
        <RouterLink v-if="canAccount" class="pm-sidebar-sublink" :class="{ active: isSubActive('/account/status', 'account') }" to="/account/status" @click="setActiveGroup('account'); setActiveSub('account-status'); closeSidebar">{{ t('sidebar.accountStatus') }}</RouterLink>
        <RouterLink v-if="canAccountEdit" class="pm-sidebar-sublink" :class="{ active: isSubActive('/account/billing', 'account') }" to="/account/billing" @click="setActiveGroup('account'); setActiveSub('account-billing'); closeSidebar">{{ t('sidebar.billingSubscription') }}</RouterLink>
        <RouterLink v-if="canAccountEdit" class="pm-sidebar-sublink" :class="{ active: isSubActive('/account/security', 'account') }" to="/account/security" @click="setActiveGroup('account'); setActiveSub('account-security'); closeSidebar">{{ t('sidebar.accountSecurity') }}</RouterLink>
        <RouterLink v-if="canAccountEdit" class="pm-sidebar-sublink" :class="{ active: isSubActive('/account/recovery', 'account') }" to="/account/recovery" @click="setActiveGroup('account'); setActiveSub('account-recovery'); closeSidebar">{{ t('sidebar.accountRecovery') }}</RouterLink>
      </div>

    <div class="pm-sidebar-language">
      <div class="pm-sidebar-language-title">{{ t('sidebar.languageTitle') }}</div>
      <LanguageSwitcher sidebar :show-label="false" />
    </div>

    <div class="pm-sidebar-footer">
      <button class="pm-sidebar-link" type="button" @click="goSupport">{{ t('sidebar.helpSupport') }}</button>
      <button class="pm-sidebar-link" type="button" @click="doLogout">{{ t('sidebar.logOut') }}</button>
    </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { logout as apiLogout, clearToken } from '../api/auth'
import client from '../api/client'
import { authState } from '../store/auth'
import { hasPlanFeature } from '../config/planFeatures'
import { hasUserPermission } from '../config/permissions'
import LanguageSwitcher from './LanguageSwitcher.vue'
import logoWhite from '../assets/logo-white.png'

const emit = defineEmits(['close'])

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const sidebarRef = ref(null)
const scrollRef = ref(null)
const scrollKey = 'pm_sidebar_scroll'
const activeKey = 'pm_sidebar_active_group'
const activeSubKey = 'pm_sidebar_active_sub'

const open = reactive({
  profiles: false,
  lockers: false,
  parcelsRoom: false,
  pickup: false,
  delivery: false,
  users: false,
  workforce: false,
  reports: false,
  settings: false,
  notify: false,
  email: false,
  account: false,
})

const toggle = (key) => {
  open[key] = !open[key]
}

const canFeature = (feature) => !feature || hasPlanFeature(authState.user?.selected_plan, feature)
const canAccess = (feature, module, action = 'read') => (
  canFeature(feature) && hasUserPermission(authState.user, module, action)
)

const canProfiles = computed(() => canAccess('profiles_core', 'profiles', 'read'))
const canStorage = computed(() => canAccess('storage_management', 'parcels', 'read'))
const canStorageCreate = computed(() => canAccess('storage_management', 'parcels', 'create'))
const canLockerAccess = computed(() => canAccess('locker_access_management', 'parcels', 'read'))
const canDelivery = computed(() => canAccess('', 'parcels', 'read'))
const canSettings = computed(() => canAccess('', 'settings', 'read'))
const canEmail = computed(() => canAccess('', 'email', 'read'))
const canEmailCreate = computed(() => canAccess('', 'email', 'create'))
const canAccount = computed(() => canAccess('', 'account', 'read'))
const canAccountEdit = computed(() => canAccess('', 'account', 'edit'))
const canParcelReports = computed(() => canAccess('reporting', 'parcels', 'read'))
const canApprovalSlaReport = computed(() => canAccess('reporting', 'workforce', 'read'))
const canPickupSlaReport = computed(() => canAccess('reporting', 'pickup', 'read'))
const canReportsGroup = computed(() => canParcelReports.value || canApprovalSlaReport.value || canPickupSlaReport.value)

const activeGroup = ref(sessionStorage.getItem(activeKey) || '')
const activeSub = ref(sessionStorage.getItem(activeSubKey) || '')
const todoOpenCount = ref(0)
let todoCountIntervalId = null
const emailFolderHandler = (event) => {
  const value = event?.detail
  if (!value) return
  activeSub.value = value
  sessionStorage.setItem(activeSubKey, value)
  setActiveGroup('email')
}
const todoUpdatedHandler = () => {
  fetchTodoOpenCount()
}

const topGroups = [
  'profiles',
  'pickup',
  'delivery',
  'users',
  'workforce',
  'reports',
  'settings',
  'notify',
  'email',
  'account',
]

const closeAllGroups = (except = '') => {
  topGroups.forEach((group) => {
    open[group] = group === except
  })
}

const setActiveGroup = (key) => {
  if (!key) return
  activeGroup.value = key
  sessionStorage.setItem(activeKey, key)
  if (open[key] !== undefined) {
    closeAllGroups(key)
  }
}

const setActiveSub = (key) => {
  if (!key) return
  activeSub.value = key
  sessionStorage.setItem(activeSubKey, key)
}

const toggleAndPin = (key) => {
  const nextState = !open[key]
  closeAllGroups(nextState ? key : '')
  if (nextState) {
    setActiveGroup(key)
  } else {
    activeGroup.value = ''
    sessionStorage.removeItem(activeKey)
  }
}

const closeSidebar = () => {
  if (window.innerWidth <= 992) {
    emit('close')
  }
}

const goSupport = async () => {
  closeAllGroups(activeGroup.value || '')
  await router.push('/contact')
}

const formatBadgeCount = (count) => (count > 99 ? '99+' : String(count))

const fetchTodoOpenCount = async () => {
  try {
    const { data } = await client.get('/todo')
    const tasks = Array.isArray(data?.data) ? data.data : []
    todoOpenCount.value = tasks.filter((task) => String(task?.status || '').toLowerCase() !== 'completed').length
  } catch {
    todoOpenCount.value = 0
  }
}

const doLogout = async () => {
  try {
    await apiLogout()
  } catch {
    // ignore logout errors
  }
  clearToken()
  await router.push('/login')
}

const isSubActive = (path, groupKey = '', exact = false, subKey = '') => {
  const matches = exact ? route.path === path : route.path.startsWith(path)
  const isEmailDetail = route.path.startsWith('/email/messages') || route.path.startsWith('/email/threads')
  if (!matches) {
    if (isEmailDetail && subKey) {
      return activeSub.value === subKey
    }
    return false
  }
  if (!groupKey) return true
  return activeGroup.value === groupKey
}

const deriveGroupFromRoute = (path) => {
  if (path.startsWith('/profiles')) return 'profiles'
  if (path.startsWith('/pickup')) return 'pickup'
  if (path.startsWith('/parcels')) return 'delivery'
  if (path.startsWith('/delivery')) return 'delivery'
  if (path.startsWith('/reports')) return 'reports'
  if (path.startsWith('/workforce')) return 'workforce'
  if (path.startsWith('/admin/users')) return 'users'
  if (path.startsWith('/settings')) return 'settings'
  if (path.startsWith('/notifications')) return 'notify'
  if (path.startsWith('/email')) return 'email'
  if (path.startsWith('/account')) return 'account'
  return ''
}

const syncOpenFromRoute = (path) => {
  const isEmailDetail = path.startsWith('/email/messages') || path.startsWith('/email/threads')
  const routeGroup = deriveGroupFromRoute(path)
  if (routeGroup && routeGroup !== activeGroup.value) {
    activeGroup.value = routeGroup
    sessionStorage.setItem(activeKey, routeGroup)
  }
  if (path.startsWith('/email/inbox')) setActiveSub('email-inbox')
  if (path.startsWith('/email/sent')) setActiveSub('email-sent')
  if (path.startsWith('/email/drafts')) setActiveSub('email-drafts')
  if (path.startsWith('/email/trash')) setActiveSub('email-trash')
  if (path.startsWith('/email/templates')) setActiveSub('email-templates')
  if (path.startsWith('/email/settings')) setActiveSub('email-settings')
  if (path.startsWith('/email/new')) setActiveSub('email-new')
  if (path === '/email') setActiveSub('email-dashboard')
  if (isEmailDetail && !activeSub.value) setActiveSub('email-inbox')
  if (path.startsWith('/pickup/front-desk')) setActiveSub('pickup-frontdesk')
  if (path.startsWith('/pickup/facility-locker')) setActiveSub('pickup-facility')
  if (path.startsWith('/pickup/external-locker')) setActiveSub('pickup-external')
  if (path.startsWith('/pickup/counter-staff')) setActiveSub('pickup-counter')
  if (path.startsWith('/account/profile')) setActiveSub('account-profile')
  if (path.startsWith('/account/status')) setActiveSub('account-status')
  if (path.startsWith('/account/billing')) setActiveSub('account-billing')
  if (path.startsWith('/account/security')) setActiveSub('account-security')
  if (path.startsWith('/account/recovery')) setActiveSub('account-recovery')

  const selectedGroup = routeGroup || activeGroup.value || ''
  closeAllGroups(selectedGroup)

  open.lockers = selectedGroup === 'profiles' && path.startsWith('/profiles/lockers')
  open.parcelsRoom = selectedGroup === 'profiles' && path.startsWith('/profiles/storage')
}

const restoreScroll = () => {
  const el = scrollRef.value || sidebarRef.value
  if (!el) return
  const saved = sessionStorage.getItem(scrollKey)
  if (saved !== null) {
    const value = parseInt(saved, 10)
    el.scrollTop = Number.isFinite(value) ? value : 0
  }
}

const saveScroll = () => {
  const el = scrollRef.value || sidebarRef.value
  if (!el) return
  sessionStorage.setItem(scrollKey, String(el.scrollTop || 0))
}

const handleSidebarWheel = (event) => {
  const scrollEl = scrollRef.value || sidebarRef.value
  if (!scrollEl || window.innerWidth <= 992) return

  const maxTop = Math.max(scrollEl.scrollHeight - scrollEl.clientHeight, 0)
  if (maxTop <= 0) {
    event.preventDefault()
    event.stopPropagation()
    return
  }

  const nextTop = Math.max(0, Math.min(maxTop, scrollEl.scrollTop + event.deltaY))
  scrollEl.scrollTop = nextTop
  event.preventDefault()
  event.stopPropagation()
}

onMounted(() => {
  closeAllGroups(activeGroup.value || '')
  nextTick(restoreScroll)
  fetchTodoOpenCount()
  todoCountIntervalId = window.setInterval(fetchTodoOpenCount, 60000)
  const el = scrollRef.value || sidebarRef.value
  const host = sidebarRef.value
  if (el) {
    el.addEventListener('scroll', saveScroll, { passive: true })
  }
  if (host) {
    host.addEventListener('wheel', handleSidebarWheel, { passive: false })
  }
  window.addEventListener('pm-email-folder', emailFolderHandler)
  window.addEventListener('pm-todo-updated', todoUpdatedHandler)
})

onBeforeUnmount(() => {
  saveScroll()
  if (todoCountIntervalId) {
    clearInterval(todoCountIntervalId)
    todoCountIntervalId = null
  }
  const el = scrollRef.value || sidebarRef.value
  const host = sidebarRef.value
  if (el) {
    el.removeEventListener('scroll', saveScroll)
  }
  if (host) {
    host.removeEventListener('wheel', handleSidebarWheel)
  }
  window.removeEventListener('pm-email-folder', emailFolderHandler)
  window.removeEventListener('pm-todo-updated', todoUpdatedHandler)
})

watch(
  () => route.path,
  (path) => {
    syncOpenFromRoute(path)
    fetchTodoOpenCount()
    nextTick(restoreScroll)
  },
  { immediate: true }
)
</script>
