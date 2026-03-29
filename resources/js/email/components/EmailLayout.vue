<template>
  <div class="pm-dashboard-layout" :class="{ 'pm-sidebar-hidden': sidebarHidden }">
    <AppSidebar :isOpen="sidebarOpen" @close="closeSidebar" />
    <div class="pm-dashboard-main">
      <div class="pm-dashboard-topbar">
        <button class="pm-icon-btn pm-menu-btn" type="button" aria-label="Open menu" @click="toggleSidebar">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16" />
          </svg>
        </button>
        <button class="pm-icon-btn pm-hide-btn" type="button" aria-label="Toggle sidebar" @click="toggleSidebarHidden">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 6 3 12l6 6M21 12H4" />
          </svg>
        </button>
        <div class="pm-topbar-search">
          <input v-model="searchQuery" class="form-control" placeholder="Search conversations, people, tags..." />
          <button v-if="searchQuery" class="pm-clear-btn" type="button" aria-label="Clear search" @click="searchQuery = ''">
            &times;
          </button>
        </div>
        <div class="pm-topbar-actions">
          <button class="pm-icon-btn" type="button" aria-label="Notifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
            <span class="pm-topbar-badge"></span>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
          <div class="pm-topbar-avatar">JA</div>
          <span class="pm-topbar-name">{{ userName }}</span>
          <span class="pm-topbar-pill">Admin</span>
          <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="m7 10 5 5 5-5H7Z" />
            </svg>
          </button>
          <div v-if="userMenuOpen" class="pm-user-menu">
            <button class="pm-user-item" type="button" @click="goProfile">Profile</button>
            <button class="pm-user-item" type="button" @click="goAccount">Account</button>
            <button class="pm-user-item danger" type="button" @click="logout">Log out</button>
          </div>
        </div>
      </div>

      <div class="container pm-mail-hub">
        <section class="pm-mail-hero">
          <div class="pm-mail-hero-copy">
            <span class="pm-mail-kicker">DeskDrop Message Center</span>
            <h1>Operational Messaging Workspace</h1>
            <p>Unified communication for parcel operations, staff coordination, and customer updates.</p>
          </div>
          <div class="pm-mail-hero-metrics">
            <div class="pm-mail-metric-pill">
              <span>Inbox</span>
              <strong>{{ counters.inbox }}</strong>
            </div>
            <div class="pm-mail-metric-pill">
              <span>Unread</span>
              <strong>{{ counters.unread }}</strong>
            </div>
            <div class="pm-mail-metric-pill">
              <span>Drafts</span>
              <strong>{{ counters.drafts }}</strong>
            </div>
          </div>
        </section>

        <nav class="pm-mail-nav" aria-label="Message folders">
          <RouterLink
            v-for="item in navItems"
            :key="item.to"
            class="pm-mail-nav-item"
            :class="{ active: isNavActive(item) }"
            :to="item.to"
          >
            <span>{{ item.label }}</span>
            <small v-if="item.countKey && counters[item.countKey] > 0">{{ counters[item.countKey] }}</small>
          </RouterLink>
        </nav>

        <div class="pm-mail-workspace">
          <slot name="head"></slot>
          <slot></slot>
        </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { authState } from '../../store/auth'
import AppSidebar from '../../components/AppSidebar.vue'
import client from '../../api/client'
import { hasUserPermission } from '../../config/permissions'

const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const counters = ref({ inbox: 0, unread: 0, drafts: 0 })

const canReadEmail = computed(() => hasUserPermission(authState.user, 'email', 'read'))
const canCreateEmail = computed(() => hasUserPermission(authState.user, 'email', 'create'))

const navItems = computed(() => {
  const items = [
    { label: 'Overview', to: '/email', exact: true },
    { label: 'Inbox', to: '/email/inbox', countKey: 'unread' },
    { label: 'Sent', to: '/email/sent' },
    { label: 'Drafts', to: '/email/drafts', countKey: 'drafts' },
    { label: 'Trash', to: '/email/trash' },
    { label: 'Templates', to: '/email/templates' },
    { label: 'Settings', to: '/email/settings' },
  ]

  if (canCreateEmail.value) {
    items.unshift({ label: 'Compose', to: '/email/new' })
  }

  return items
})

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleSidebarHidden = () => {
  sidebarHidden.value = !sidebarHidden.value
}

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const goProfile = () => {
  userMenuOpen.value = false
  router.push('/account/profile')
}

const goAccount = () => {
  userMenuOpen.value = false
  router.push('/account/status')
}

const logout = () => {
  userMenuOpen.value = false
  router.push('/login')
}

const handleOutsideClick = (event) => {
  if (!userMenuRef.value) return
  if (!userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

const handleEsc = (event) => {
  if (event.key === 'Escape') {
    userMenuOpen.value = false
  }
}

const isNavActive = (item) => {
  if (item.exact) return route.path === item.to
  if (item.to === '/email/inbox') {
    return route.path.startsWith('/email/inbox') || route.path.startsWith('/email/messages') || route.path.startsWith('/email/threads')
  }
  return route.path.startsWith(item.to)
}

const loadCounters = async () => {
  if (!canReadEmail.value) {
    counters.value = { inbox: 0, unread: 0, drafts: 0 }
    return
  }

  try {
    const { data } = await client.get('/email/stats')
    const stats = data?.data?.stats || {}
    counters.value = {
      inbox: Number(stats.inbox || 0),
      unread: Number(stats.unread || 0),
      drafts: Number(stats.drafts || 0),
    }
  } catch {
    counters.value = { inbox: 0, unread: 0, drafts: 0 }
  }
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

watch(
  () => route.fullPath,
  () => {
    if (route.path.startsWith('/email')) {
      loadCounters()
    }
  }
)

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  if (route.path.startsWith('/email')) {
    loadCounters()
  }
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-mail-hub {
  padding-top: 16px;
  padding-bottom: 24px;
  display: grid;
  gap: 14px;
}

.pm-mail-hero {
  border: 1px solid rgba(37, 99, 235, 0.24);
  border-radius: 18px;
  background:
    radial-gradient(circle at 18% -20%, rgba(56, 189, 248, 0.24), transparent 58%),
    radial-gradient(circle at 82% 10%, rgba(59, 130, 246, 0.2), transparent 54%),
    linear-gradient(135deg, #0f1f42 0%, #193b84 52%, #225dd0 100%);
  color: #eff6ff;
  padding: 18px 20px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.pm-mail-hero-copy {
  max-width: 680px;
}

.pm-mail-kicker {
  display: inline-flex;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 0.72rem;
  letter-spacing: 0.04em;
  font-weight: 700;
  text-transform: uppercase;
  background: rgba(219, 234, 254, 0.2);
  border: 1px solid rgba(191, 219, 254, 0.32);
  margin-bottom: 8px;
}

.pm-mail-hero h1 {
  margin: 0;
  font-size: 1.28rem;
  font-weight: 800;
  letter-spacing: 0.01em;
}

.pm-mail-hero p {
  margin: 8px 0 0;
  color: rgba(239, 246, 255, 0.9);
  max-width: 60ch;
}

.pm-mail-hero-metrics {
  display: inline-flex;
  gap: 10px;
  flex-wrap: wrap;
}

.pm-mail-metric-pill {
  min-width: 116px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(219, 234, 254, 0.32);
  padding: 9px 12px;
  display: grid;
  gap: 2px;
}

.pm-mail-metric-pill span {
  font-size: 0.72rem;
  color: rgba(239, 246, 255, 0.84);
}

.pm-mail-metric-pill strong {
  font-size: 1.08rem;
  color: #ffffff;
  font-weight: 800;
}

.pm-mail-nav {
  border: 1px solid rgba(148, 163, 184, 0.28);
  border-radius: 16px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  padding: 8px;
  display: flex;
  gap: 8px;
  overflow-x: auto;
}

.pm-mail-nav-item {
  flex-shrink: 0;
  border-radius: 11px;
  border: 1px solid transparent;
  background: transparent;
  color: #1e293b;
  text-decoration: none;
  padding: 8px 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  font-size: 0.9rem;
}

.pm-mail-nav-item small {
  min-width: 20px;
  height: 20px;
  border-radius: 999px;
  padding: 0 6px;
  background: #dbeafe;
  color: #1e40af;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.72rem;
  font-weight: 700;
}

.pm-mail-nav-item:hover {
  color: #1d4ed8;
  background: #edf4ff;
  border-color: rgba(59, 130, 246, 0.24);
}

.pm-mail-nav-item.active {
  background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
  border-color: rgba(37, 99, 235, 0.36);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.24);
}

.pm-mail-nav-item.active small {
  background: rgba(219, 234, 254, 0.24);
  color: #ffffff;
}

.pm-mail-workspace {
  display: grid;
  gap: 12px;
}

@media (max-width: 992px) {
  .pm-mail-hero {
    padding: 14px;
  }

  .pm-mail-hero h1 {
    font-size: 1.1rem;
  }

  .pm-mail-hero-metrics {
    width: 100%;
  }

  .pm-mail-metric-pill {
    flex: 1 1 90px;
    min-width: 0;
  }
}
</style>

