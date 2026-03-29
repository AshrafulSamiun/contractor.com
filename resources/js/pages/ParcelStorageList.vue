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

      <div class="container pm-ops-page pm-storage-list-shell">
        <div class="pm-page-head">
          <div>
            <h2>Parcel Storage List</h2>
            <div class="pm-page-subtitle">Manage all parcel room and storage areas</div>
          </div>
          <div class="pm-page-actions">
            <RouterLink class="btn btn-primary" to="/profiles/storage">+ Add New Storage</RouterLink>
          </div>
        </div>

        <section class="pm-card pm-ops-card p-4 pm-storage-list-card">
          <div class="pm-list-filters pm-storage-list-filters">
            <div class="pm-filter-group">
              <label class="pm-field-label">Storage Name</label>
              <input class="form-control" v-model="filters.storage_name" placeholder="Search by name..." @keyup.enter="fetchList(1)" />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Property / Facility</label>
              <input class="form-control" v-model="filters.facility_name" placeholder="Search by facility..." @keyup.enter="fetchList(1)" />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Floor No</label>
              <input class="form-control" v-model="filters.floor_no" placeholder="Search by floor..." @keyup.enter="fetchList(1)" />
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
            <button class="btn btn-primary pm-filter-btn" type="button" :disabled="listLoading" @click="fetchList(1)">
              {{ listLoading ? 'Loading...' : 'Search' }}
            </button>
          </div>

          <div class="table-responsive">
            <table class="table pm-dash-table pm-storage-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Storage Name</th>
                  <th>Property / Facility</th>
                  <th>Floor No</th>
                  <th>Area / Section</th>
                  <th>Dedicated Item</th>
                  <th>Active</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="listLoading">
                  <td colspan="8" class="text-center pm-muted">Loading...</td>
                </tr>
                <tr v-else-if="!storages.length">
                  <td colspan="8" class="text-center pm-muted">No storage records found.</td>
                </tr>
                <tr v-else v-for="(storage, index) in storages" :key="storage.id">
                  <td>{{ rowStart + index }}</td>
                  <td>
                    <button class="btn btn-link p-0 pm-storage-link" type="button" @click="openStorage(storage.id, 'view')">
                      {{ storage.storage_name }}
                    </button>
                  </td>
                  <td>{{ storage.facility_name }}</td>
                  <td>{{ storage.floor_no || '-' }}</td>
                  <td>{{ storage.area_section || '-' }}</td>
                  <td>{{ storage.dedicated_item || '-' }}</td>
                  <td>
                    <span class="pm-storage-active-pill" :class="{ 'is-inactive': !storage.is_active }">
                      {{ storage.is_active ? 'Yes' : 'No' }}
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="pm-storage-row-actions">
                      <button
                        class="pm-storage-action-btn"
                        type="button"
                        title="Edit"
                        aria-label="Edit"
                        @click="openStorage(storage.id, 'edit')"
                      >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M4 15.5V20h4.5L19 9.5 14.5 5 4 15.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <button
                        class="pm-storage-action-btn"
                        type="button"
                        title="View"
                        aria-label="View"
                        @click="openStorage(storage.id, 'view')"
                      >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" fill="none" stroke="currentColor" stroke-width="1.8" />
                          <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                      </button>
                      <button
                        class="pm-storage-action-btn danger"
                        type="button"
                        title="Delete"
                        aria-label="Delete"
                        @click="deleteStorage(storage.id)"
                      >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M5 7h14M9 7V5h6v2M8 7l1 12h6l1-12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pm-storage-table-footer">
            <div class="pm-storage-per-page">
              <span>Rows per page:</span>
              <select v-model.number="perPage" class="form-control">
                <option :value="5">5</option>
                <option :value="8">8</option>
                <option :value="10">10</option>
                <option :value="25">25</option>
              </select>
            </div>
            <div class="pm-storage-row-meta">{{ rowMetaText }}</div>
            <div class="pm-storage-pagination">
              <button class="btn btn-sm btn-outline-secondary" type="button" :disabled="currentPage <= 1" @click="previousPage">Previous</button>
              <span>Page {{ currentPage }} of {{ totalPages }}</span>
              <button class="btn btn-sm btn-outline-secondary" type="button" :disabled="currentPage >= totalPages" @click="nextPage">Next</button>
            </div>
          </div>
        </section>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { setFlash } from '../store/flash'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const storages = ref([])
const listLoading = ref(false)
const currentPage = ref(1)
const perPage = ref(8)
const totalPages = ref(1)
const totalItems = ref(0)
const rowFrom = ref(0)
const rowTo = ref(0)

const filters = reactive({
  storage_name: '',
  facility_name: '',
  floor_no: '',
  is_active: '',
})

const rowStart = computed(() => {
  if (!totalItems.value) return 0
  if (rowFrom.value > 0) return rowFrom.value
  return ((currentPage.value - 1) * perPage.value) + 1
})

const rowMetaText = computed(() => {
  if (!totalItems.value) return '0-0 of 0'
  return `${rowFrom.value}-${rowTo.value} of ${totalItems.value}`
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

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

const fetchList = async (page = currentPage.value) => {
  listLoading.value = true
  try {
    const params = {
      storage_name: filters.storage_name || undefined,
      facility_name: filters.facility_name || undefined,
      floor_no: filters.floor_no || undefined,
      is_active: filters.is_active || undefined,
      page,
      per_page: perPage.value,
    }

    const { data } = await client.get('/parcel-storages', { params })
    storages.value = Array.isArray(data?.data) ? data.data : []
    const meta = data?.meta || {}
    currentPage.value = Number(meta.current_page || page || 1)
    totalPages.value = Number(meta.last_page || 1)
    totalItems.value = Number(meta.total || storages.value.length)
    const fallbackFrom = totalItems.value ? ((currentPage.value - 1) * perPage.value) + 1 : 0
    rowFrom.value = Number(meta.from ?? fallbackFrom)
    const fallbackTo = totalItems.value ? (rowFrom.value + storages.value.length - 1) : 0
    rowTo.value = Number(meta.to ?? fallbackTo)
  } catch {
    storages.value = []
    currentPage.value = 1
    totalPages.value = 1
    totalItems.value = 0
    rowFrom.value = 0
    rowTo.value = 0
  } finally {
    listLoading.value = false
  }
}

const resetFilters = () => {
  filters.storage_name = ''
  filters.facility_name = ''
  filters.floor_no = ''
  filters.is_active = ''
  currentPage.value = 1
  fetchList(1)
}

const openStorage = (id, mode = 'view') => {
  router.push({ path: '/profiles/storage', query: { id, mode } })
}

const deleteStorage = async (id) => {
  if (!id) return
  if (!confirm('Delete this storage?')) return

  try {
    await client.delete(`/parcel-storages/${id}`)
    setFlash('Storage deleted successfully.', 'success', 2200)
    fetchList()
  } catch {
    setFlash('Failed to delete storage.', 'danger', 2500)
  }
}

const previousPage = () => {
  if (currentPage.value > 1) {
    fetchList(currentPage.value - 1)
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    fetchList(currentPage.value + 1)
  }
}

watch(perPage, () => {
  currentPage.value = 1
  fetchList(1)
})

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
.pm-storage-list-shell {
  display: grid;
  gap: 14px;
}

.pm-storage-list-card {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-storage-list-filters {
  grid-template-columns: repeat(4, minmax(0, 1fr)) auto auto;
}

.pm-storage-table {
  margin-bottom: 0;
}

.pm-storage-link {
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.pm-storage-link:hover {
  color: #1e40af;
  text-decoration: underline;
}

.pm-storage-active-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 34px;
  height: 24px;
  padding: 0 10px;
  border-radius: 999px;
  background: #dcfce7;
  color: #15803d;
  font-size: 0.78rem;
  font-weight: 700;
}

.pm-storage-active-pill.is-inactive {
  background: #e2e8f0;
  color: #475569;
}

.pm-storage-row-actions {
  display: inline-flex;
  gap: 8px;
  align-items: center;
}

.pm-storage-action-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: 1px solid #dbe4f6;
  background: #ffffff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pm-storage-action-btn svg {
  width: 16px;
  height: 16px;
}

.pm-storage-action-btn:hover {
  border-color: #93c5fd;
  background: #eff6ff;
}

.pm-storage-action-btn.danger {
  color: #dc2626;
}

.pm-storage-action-btn.danger:hover {
  border-color: #fecaca;
  background: #fef2f2;
}

.pm-storage-table-footer {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e2e8f0;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 12px;
}

.pm-storage-per-page {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.pm-storage-per-page .form-control {
  width: 82px;
  height: 36px;
}

.pm-storage-row-meta {
  color: #64748b;
  font-size: 0.9rem;
}

.pm-storage-pagination {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  justify-self: end;
}

.pm-storage-pagination span {
  color: #475569;
  font-size: 0.88rem;
  font-weight: 600;
}

@media (max-width: 1100px) {
  .pm-storage-list-filters {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-storage-table-footer {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .pm-storage-pagination {
    justify-self: start;
  }
}

@media (max-width: 760px) {
  .pm-storage-list-filters {
    grid-template-columns: 1fr;
  }

  .pm-storage-row-actions {
    justify-content: flex-end;
    width: 100%;
  }
}
</style>
