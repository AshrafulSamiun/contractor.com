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

      <div class="container pm-ops-page pm-courier-list-shell">
        <div class="pm-page-head">
          <div>
            <h2>Courier List</h2>
            <div class="pm-page-subtitle">Manage all courier companies and delivery services</div>
          </div>
          <div class="pm-page-actions">
            <RouterLink class="btn btn-primary" to="/profiles/couriers">+ Add New Courier</RouterLink>
          </div>
        </div>

        <section class="pm-card pm-ops-card p-4 pm-courier-list-card">
          <div class="pm-list-filters pm-courier-list-filters">
            <div class="pm-filter-group">
              <label class="pm-field-label">Company Name</label>
              <input class="form-control" v-model="filters.company_name" placeholder="Search by company..." />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Email</label>
              <input class="form-control" v-model="filters.email" placeholder="Search by email..." />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Phone</label>
              <input class="form-control" v-model="filters.phone" placeholder="Search by phone..." />
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
          </div>

          <div class="table-responsive">
            <table class="table pm-dash-table pm-courier-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Company Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Website</th>
                  <th>Active</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="listLoading">
                  <td colspan="7" class="text-center pm-muted">Loading...</td>
                </tr>
                <tr v-else-if="!couriers.length">
                  <td colspan="7" class="text-center pm-muted">No couriers found.</td>
                </tr>
                <tr v-else v-for="(courier, index) in couriers" :key="courier.id">
                  <td>{{ rowStart + index }}</td>
                  <td>
                    <button class="btn btn-link p-0 pm-courier-link" type="button" @click="openCourier(courier.id, 'view')">
                      {{ courier.company_name }}
                    </button>
                  </td>
                  <td>{{ courier.email || '-' }}</td>
                  <td>{{ courier.phone || '-' }}</td>
                  <td>
                    <a v-if="courier.website" :href="courier.website" target="_blank" rel="noreferrer" class="pm-courier-visit-link">
                      Visit
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 5h5v5M10 14 19 5M19 14v5h-5M5 10V5h5M5 19h5v-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </a>
                    <span v-else>-</span>
                  </td>
                  <td>
                    <span class="pm-courier-active-pill" :class="{ 'is-inactive': !courier.is_active }">
                      {{ courier.is_active ? 'Yes' : 'No' }}
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="pm-courier-row-actions">
                      <button
                        class="pm-courier-action-btn"
                        type="button"
                        title="Edit"
                        aria-label="Edit"
                        @click="openCourier(courier.id, 'edit')"
                      >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M4 15.5V20h4.5L19 9.5 14.5 5 4 15.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <button
                        class="pm-courier-action-btn"
                        type="button"
                        title="View"
                        aria-label="View"
                        @click="openCourier(courier.id, 'view')"
                      >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" fill="none" stroke="currentColor" stroke-width="1.8" />
                          <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                      </button>
                      <button
                        class="pm-courier-action-btn danger"
                        type="button"
                        title="Delete"
                        aria-label="Delete"
                        @click="deleteCourier(courier.id)"
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

          <div class="pm-courier-table-footer">
            <div class="pm-courier-per-page">
              <span>Rows per page:</span>
              <select v-model.number="perPage" class="form-control">
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="25">25</option>
              </select>
            </div>
            <div class="pm-courier-row-meta">{{ rowMetaText }}</div>
            <div class="pm-courier-pagination">
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

const couriers = ref([])
const listLoading = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const autoSearchTimer = ref(null)
const totalPages = ref(1)
const totalItems = ref(0)
const rowFrom = ref(0)
const rowTo = ref(0)

const filters = reactive({
  company_name: '',
  email: '',
  phone: '',
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
      company_name: filters.company_name || undefined,
      email: filters.email || undefined,
      phone: filters.phone || undefined,
      is_active: filters.is_active || undefined,
      page,
      per_page: perPage.value,
    }

    const { data } = await client.get('/couriers', { params })
    couriers.value = Array.isArray(data?.data) ? data.data : []
    const meta = data?.meta || {}
    currentPage.value = Number(meta.current_page || page || 1)
    totalPages.value = Number(meta.last_page || 1)
    totalItems.value = Number(meta.total || couriers.value.length)
    const fallbackFrom = totalItems.value ? ((currentPage.value - 1) * perPage.value) + 1 : 0
    rowFrom.value = Number(meta.from ?? fallbackFrom)
    const fallbackTo = totalItems.value ? (rowFrom.value + couriers.value.length - 1) : 0
    rowTo.value = Number(meta.to ?? fallbackTo)
  } catch {
    couriers.value = []
    currentPage.value = 1
    totalPages.value = 1
    totalItems.value = 0
    rowFrom.value = 0
    rowTo.value = 0
  } finally {
    listLoading.value = false
  }
}

const queueAutoSearch = () => {
  if (autoSearchTimer.value) {
    clearTimeout(autoSearchTimer.value)
  }

  autoSearchTimer.value = setTimeout(() => {
    fetchList(1)
  }, 260)
}

watch(
  () => [filters.company_name, filters.email, filters.phone, filters.is_active],
  () => {
    queueAutoSearch()
  }
)

const resetFilters = () => {
  filters.company_name = ''
  filters.email = ''
  filters.phone = ''
  filters.is_active = ''
  currentPage.value = 1
  fetchList(1)
}

const openCourier = (id, mode = 'view') => {
  router.push({ path: '/profiles/couriers', query: { id, mode } })
}

const deleteCourier = async (id) => {
  if (!id) return
  if (!confirm('Delete this courier?')) return

  try {
    await client.delete(`/couriers/${id}`)
    setFlash('Courier deleted successfully.', 'success', 2200)
    fetchList()
  } catch {
    setFlash('Failed to delete courier.', 'danger', 2500)
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
  if (autoSearchTimer.value) {
    clearTimeout(autoSearchTimer.value)
    autoSearchTimer.value = null
  }
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-courier-list-shell {
  display: grid;
  gap: 14px;
}

.pm-courier-list-card {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-courier-list-filters {
  grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
}

.pm-courier-table {
  margin-bottom: 0;
}

.pm-courier-link {
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.pm-courier-link:hover {
  color: #1e40af;
  text-decoration: underline;
}

.pm-courier-visit-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #1d4ed8;
  font-weight: 600;
}

.pm-courier-visit-link svg {
  width: 12px;
  height: 12px;
}

.pm-courier-active-pill {
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

.pm-courier-active-pill.is-inactive {
  background: #e2e8f0;
  color: #475569;
}

.pm-courier-row-actions {
  display: inline-flex;
  gap: 8px;
  align-items: center;
}

.pm-courier-action-btn {
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

.pm-courier-action-btn svg {
  width: 16px;
  height: 16px;
}

.pm-courier-action-btn:hover {
  border-color: #93c5fd;
  background: #eff6ff;
}

.pm-courier-action-btn.danger {
  color: #dc2626;
}

.pm-courier-action-btn.danger:hover {
  border-color: #fecaca;
  background: #fef2f2;
}

.pm-courier-table-footer {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e2e8f0;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 12px;
}

.pm-courier-per-page {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.pm-courier-per-page .form-control {
  width: 82px;
  height: 36px;
}

.pm-courier-row-meta {
  color: #64748b;
  font-size: 0.9rem;
}

.pm-courier-pagination {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  justify-self: end;
}

.pm-courier-pagination span {
  color: #475569;
  font-size: 0.88rem;
  font-weight: 600;
}

@media (max-width: 1100px) {
  .pm-courier-list-filters {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-courier-table-footer {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .pm-courier-pagination {
    justify-self: start;
  }
}

@media (max-width: 760px) {
  .pm-courier-list-filters {
    grid-template-columns: 1fr;
  }

  .pm-courier-row-actions {
    justify-content: flex-end;
    width: 100%;
  }
}
</style>
