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
          <input v-model="searchQuery" class="form-control" placeholder="Search parcels, recipients, tracking..." />
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

      <div class="container pm-ops-page pm-recipient-list-premium-page">
        <section class="pm-recipient-list-hero">
          <div>
            <div class="pm-recipient-list-kicker">Profiles Module</div>
            <h2 class="pm-recipient-list-title">Recipient Directory</h2>
            <div class="pm-page-subtitle pm-recipient-list-subtitle">
              Dashboard &gt; Profiles &gt; Recipients &gt; List
            </div>
          </div>
          <div class="pm-recipient-list-hero-actions">
            <span class="pm-recipient-list-chip">{{ recipients.length }} Record{{ recipients.length === 1 ? '' : 's' }}</span>
            <div class="pm-page-actions">
              <RouterLink class="btn btn-primary" to="/profiles/recipients">New Recipient</RouterLink>
            </div>
          </div>
        </section>

        <section class="pm-dash-card pm-recipient-list-shell">
          <div class="pm-recipient-list-head">
            <div>
              <h5 class="pm-form-title">Recipient List</h5>
              <div class="pm-page-subtitle">Search, review, and maintain recipient records.</div>
            </div>
          </div>

          <div class="pm-list-filters pm-recipient-filters">
            <div class="pm-filter-group">
              <label class="pm-field-label">Recipient Name</label>
              <input class="form-control" v-model="filters.recipient_name" placeholder="Search by name..." />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Recipient Type</label>
              <select class="form-control" v-model="filters.recipient_type">
                <option value="">All</option>
                <option v-for="type in recipientTypeOptions" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Property / Facility</label>
              <input class="form-control" v-model="filters.facility_name" placeholder="Search by facility..." />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Phone</label>
              <input class="form-control" v-model="filters.phone" placeholder="Search by phone..." @input="onPhoneFilterInput" />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Active Status</label>
              <select class="form-control" v-model="filters.is_active">
                <option value="">All</option>
                <option value="true">Yes</option>
                <option value="false">No</option>
              </select>
            </div>
            <button class="btn btn-outline-secondary pm-filter-btn" type="button" @click="resetFilters">Reset</button>
            <button class="btn btn-primary pm-filter-btn" type="button" :disabled="listLoading" @click="fetchList">Search</button>
          </div>

          <div class="pm-table-wrap pm-recipient-table-wrap">
            <table class="table pm-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Recipient Name</th>
                  <th>Recipient Type</th>
                  <th>Property / Facility</th>
                  <th>Floor</th>
                  <th>Unit / Suite</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody v-if="listLoading">
                <tr v-for="index in 4" :key="`loading-${index}`" class="pm-skeleton-row">
                  <td colspan="10">
                    <div class="pm-skeleton-line"></div>
                  </td>
                </tr>
              </tbody>
              <tbody v-else-if="!recipients.length">
                <tr>
                  <td colspan="10" class="text-center pm-empty-table-cell">No recipients found.</td>
                </tr>
              </tbody>
              <tbody v-else>
                <tr v-for="recipient in recipients" :key="recipient.id">
                  <td>{{ recipient.id }}</td>
                  <td>{{ recipient.recipient_name }}</td>
                  <td>
                    <div v-if="recipient.recipient_types && recipient.recipient_types.length" class="pm-type-pills">
                      <span v-for="type in recipient.recipient_types" :key="type" class="pm-type-pill">{{ type }}</span>
                    </div>
                    <span v-else>-</span>
                  </td>
                  <td>{{ recipient.facility_name || '-' }}</td>
                  <td>{{ recipient.floor_no || '-' }}</td>
                  <td>{{ getUnitLabel(recipient) }}</td>
                  <td>{{ recipient.phone || '-' }}</td>
                  <td>{{ recipient.email || '-' }}</td>
                  <td>
                    <span class="pm-status-pill" :class="recipient.is_active ? 'active' : 'warning'">
                      {{ recipient.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary me-2" type="button" @click="editRecipient(recipient.id)">
                      Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger" type="button" @click="deleteRecipient(recipient.id)">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const recipients = ref([])
const listLoading = ref(false)

const recipientTypeOptions = [
  'Customer',
  'Employees',
  'Guest',
  'Landlord',
  'Student',
  'Tenant',
  'Visitor',
]

const filters = reactive({
  recipient_name: '',
  recipient_type: '',
  facility_name: '',
  phone: '',
  is_active: '',
})

const normalizePhoneFilterValue = (value) => {
  const raw = String(value || '').trim()
  if (!raw) return ''

  let normalized = raw.replace(/[^\d+]/g, '')
  if (!normalized) return ''

  if (normalized.startsWith('00')) {
    normalized = `+${normalized.slice(2)}`
  }

  if (normalized.startsWith('+')) {
    return `+${normalized.slice(1).replace(/\D/g, '')}`
  }

  return normalized.replace(/\D/g, '')
}

const onPhoneFilterInput = () => {
  filters.phone = normalizePhoneFilterValue(filters.phone)
}

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
  router.push('/account')
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

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

const fetchList = async () => {
  listLoading.value = true
  try {
    const params = {
      ...filters,
      phone: normalizePhoneFilterValue(filters.phone),
    }
    filters.phone = params.phone
    const { data } = await client.get('/recipients', { params })
    recipients.value = data?.data || []
  } catch {
    recipients.value = []
  } finally {
    listLoading.value = false
  }
}

const resetFilters = () => {
  filters.recipient_name = ''
  filters.recipient_type = ''
  filters.facility_name = ''
  filters.phone = ''
  filters.is_active = ''
  fetchList()
}

const getUnitLabel = (recipient) => {
  return (
    recipient.residential_suite_no ||
    recipient.commercial_unit_no ||
    recipient.office ||
    recipient.store ||
    '-'
  )
}

const editRecipient = (id) => {
  router.push({ path: '/profiles/recipients', query: { id } })
}

const deleteRecipient = async (id) => {
  if (!id) return
  if (!confirm('Delete this recipient?')) return
  try {
    await client.delete(`/recipients/${id}`)
    fetchList()
  } catch {
    // handled by toast
  }
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  fetchList()
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-recipient-list-premium-page {
  display: grid;
  gap: 16px;
  padding-bottom: 24px;
}

.pm-recipient-list-hero {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 14px;
  align-items: start;
  border: 1px solid #d9e3ff;
  border-radius: 18px;
  padding: 16px 18px;
  background:
    radial-gradient(circle at 92% 14%, rgba(37, 99, 235, 0.16), transparent 52%),
    linear-gradient(180deg, #f8fbff 0%, #edf4ff 100%);
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
  animation: pmFadeUp 0.36s ease both;
}

.pm-recipient-list-kicker {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #1d4ed8;
  font-weight: 700;
  margin-bottom: 4px;
}

.pm-recipient-list-title {
  margin: 0;
  font-size: 1.5rem;
  color: #0f172a;
}

.pm-recipient-list-subtitle {
  margin-top: 5px;
}

.pm-recipient-list-hero-actions {
  display: grid;
  justify-items: end;
  align-content: space-between;
  gap: 10px;
}

.pm-recipient-list-chip {
  border-radius: 999px;
  padding: 7px 12px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #fff;
  background: linear-gradient(120deg, #2563eb 0%, #0ea5e9 100%);
  box-shadow: 0 10px 20px rgba(37, 99, 235, 0.26);
}

.pm-recipient-list-shell {
  border-radius: 16px;
  border: 1px solid #dbe5f6;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
  padding: 14px;
  animation: pmFadeUp 0.36s ease both;
}

.pm-recipient-list-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.pm-recipient-filters {
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fbff;
  padding: 12px;
  margin-bottom: 12px;
}

.pm-recipient-table-wrap {
  border: 1px solid #dbe5f6;
  border-radius: 14px;
  overflow: auto;
  max-height: calc(100vh - 310px);
}

.pm-recipient-table-wrap thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  background: #f8fbff;
}

.pm-recipient-table-wrap tbody tr {
  transition: background-color 0.18s ease;
}

.pm-recipient-table-wrap tbody tr:hover {
  background: #f8fafc;
}

.pm-empty-table-cell {
  color: #64748b;
  font-weight: 600;
  padding: 22px 10px;
}

.pm-skeleton-row td {
  border-bottom: 1px solid #edf2f7;
  padding: 10px 12px;
}

.pm-skeleton-line {
  height: 18px;
  border-radius: 999px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 220% 100%;
  animation: pmSkeletonPulse 1.2s linear infinite;
}

.pm-recipient-list-premium-page .form-control {
  height: 44px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background-color: #fff;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.pm-recipient-list-premium-page select.form-control {
  appearance: none;
  background-image:
    linear-gradient(45deg, transparent 50%, #64748b 50%),
    linear-gradient(135deg, #64748b 50%, transparent 50%);
  background-position:
    calc(100% - 18px) calc(50% - 2px),
    calc(100% - 12px) calc(50% - 2px);
  background-size: 6px 6px, 6px 6px;
  background-repeat: no-repeat;
  padding-right: 34px;
}

.pm-recipient-list-premium-page .form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

@media (max-width: 992px) {
  .pm-recipient-list-hero {
    grid-template-columns: 1fr;
  }

  .pm-recipient-list-hero-actions {
    justify-items: start;
  }

  .pm-recipient-table-wrap {
    max-height: none;
  }
}

@keyframes pmFadeUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pmSkeletonPulse {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -20% 0;
  }
}
</style>
