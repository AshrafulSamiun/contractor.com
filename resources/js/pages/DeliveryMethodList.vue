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

      <div class="container pm-ops-page pm-method-list-shell">
        <div class="pm-page-head">
          <div>
            <h2>Delivery Method List</h2>
            <div class="pm-page-subtitle">Manage all delivery handover methods</div>
          </div>
          <div class="pm-page-actions">
            <RouterLink class="btn btn-primary" to="/profiles/delivery-methods">+ Add New Method</RouterLink>
          </div>
        </div>

        <section class="pm-card pm-ops-card p-4 pm-method-list-card">
          <div class="pm-list-filters pm-method-list-filters">
            <div class="pm-filter-group">
              <label class="pm-field-label">Method Name</label>
              <input class="form-control" v-model="filters.method_name" placeholder="Search by name..." />
            </div>
            <div class="pm-filter-group">
              <label class="pm-field-label">Method Type</label>
              <select class="form-control" v-model="filters.method_type">
                <option value="">All types</option>
                <option v-for="type in METHOD_TYPE_OPTIONS" :key="type" :value="type">{{ type }}</option>
              </select>
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
            <table class="table pm-dash-table pm-method-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Method Name</th>
                  <th>Method Type</th>
                  <th>Active</th>
                  <th>Note</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="listLoading">
                  <td colspan="6" class="text-center pm-muted">Loading...</td>
                </tr>
                <tr v-else-if="!methods.length">
                  <td colspan="6" class="text-center pm-muted">No delivery methods found.</td>
                </tr>
                <tr v-else v-for="(method, index) in methods" :key="method.id">
                  <td>{{ rowStart + index }}</td>
                  <td>
                    <button class="btn btn-link p-0 pm-method-link" type="button" @click="openMethod(method.id, 'view')">
                      {{ method.method_name }}
                    </button>
                  </td>
                  <td>{{ method.method_type }}</td>
                  <td>
                    <span class="pm-method-active-pill" :class="{ 'is-inactive': !method.is_active }">
                      {{ method.is_active ? 'Yes' : 'No' }}
                    </span>
                  </td>
                  <td>
                    <button v-if="method.note" class="pm-note-link" type="button" @click="openNote(method)">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8" />
                        <path d="M12 10v5M12 7h.01" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                      </svg>
                      View note
                    </button>
                    <span v-else>-</span>
                  </td>
                  <td class="text-end">
                    <div class="pm-method-row-actions">
                      <button class="pm-method-action-btn" type="button" title="Edit" aria-label="Edit" @click="openMethod(method.id, 'edit')">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M4 15.5V20h4.5L19 9.5 14.5 5 4 15.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <button class="pm-method-action-btn" type="button" title="View" aria-label="View" @click="openMethod(method.id, 'view')">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" fill="none" stroke="currentColor" stroke-width="1.8" />
                          <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                      </button>
                      <button class="pm-method-action-btn danger" type="button" title="Delete" aria-label="Delete" @click="deleteMethod(method.id)">
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

          <div class="pm-method-table-footer">
            <div class="pm-method-per-page">
              <span>Rows per page:</span>
              <select v-model.number="perPage" class="form-control">
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="25">25</option>
              </select>
            </div>
            <div class="pm-method-row-meta">{{ rowMetaText }}</div>
            <div class="pm-method-pagination">
              <button class="btn btn-sm btn-outline-secondary" type="button" :disabled="currentPage <= 1" @click="previousPage">Previous</button>
              <span>Page {{ currentPage }} of {{ totalPages }}</span>
              <button class="btn btn-sm btn-outline-secondary" type="button" :disabled="currentPage >= totalPages" @click="nextPage">Next</button>
            </div>
          </div>
        </section>
      </div>
    </div>

    <div v-if="noteModal.open" class="pm-method-note-modal" @click.self="closeNote">
      <div class="pm-method-note-card" role="dialog" aria-modal="true" aria-label="Method note">
        <div class="pm-method-note-head">
          <h6>Method Note</h6>
          <button class="pm-method-note-close" type="button" aria-label="Close" @click="closeNote">&times;</button>
        </div>
        <div class="pm-method-note-subtitle">{{ noteModal.methodName }}</div>
        <p class="pm-method-note-body">{{ noteModal.note }}</p>
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
import { DELIVERY_METHOD_TYPE_OPTIONS } from '../config/deliveryMethodTypes'

const METHOD_TYPE_OPTIONS = DELIVERY_METHOD_TYPE_OPTIONS

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const methods = ref([])
const listLoading = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const totalPages = ref(1)
const totalItems = ref(0)
const rowFrom = ref(0)
const rowTo = ref(0)
const autoSearchTimer = ref(null)

const noteModal = reactive({
  open: false,
  methodName: '',
  note: '',
})

const filters = reactive({
  method_name: '',
  method_type: '',
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
    closeNote()
  }
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

const fetchList = async (page = currentPage.value) => {
  listLoading.value = true
  try {
    const params = {
      method_name: filters.method_name || undefined,
      method_type: filters.method_type || undefined,
      is_active: filters.is_active || undefined,
      page,
      per_page: perPage.value,
    }

    const { data } = await client.get('/delivery-methods', { params })
    methods.value = Array.isArray(data?.data) ? data.data : []
    const meta = data?.meta || {}
    currentPage.value = Number(meta.current_page || page || 1)
    totalPages.value = Number(meta.last_page || 1)
    totalItems.value = Number(meta.total || methods.value.length)
    const fallbackFrom = totalItems.value ? ((currentPage.value - 1) * perPage.value) + 1 : 0
    rowFrom.value = Number(meta.from ?? fallbackFrom)
    const fallbackTo = totalItems.value ? (rowFrom.value + methods.value.length - 1) : 0
    rowTo.value = Number(meta.to ?? fallbackTo)
  } catch {
    methods.value = []
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
  () => [filters.method_name, filters.method_type, filters.is_active],
  () => {
    queueAutoSearch()
  }
)

const resetFilters = () => {
  filters.method_name = ''
  filters.method_type = ''
  filters.is_active = ''
  currentPage.value = 1
  fetchList(1)
}

const openMethod = (id, mode = 'view') => {
  router.push({ path: '/profiles/delivery-methods', query: { id, mode } })
}

const deleteMethod = async (id) => {
  if (!id) return
  if (!confirm('Delete this delivery method?')) return

  try {
    await client.delete(`/delivery-methods/${id}`)
    setFlash('Delivery method deleted successfully.', 'success', 2200)
    fetchList(currentPage.value)
  } catch {
    setFlash('Failed to delete delivery method.', 'danger', 2500)
  }
}

const openNote = (method) => {
  noteModal.methodName = method?.method_name || 'Method'
  noteModal.note = method?.note || ''
  noteModal.open = true
}

const closeNote = () => {
  noteModal.open = false
  noteModal.methodName = ''
  noteModal.note = ''
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
.pm-method-list-shell {
  display: grid;
  gap: 14px;
}

.pm-method-list-card {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-method-list-filters {
  grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
}

.pm-method-table {
  margin-bottom: 0;
}

.pm-method-link {
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.pm-method-link:hover {
  color: #1e40af;
  text-decoration: underline;
}

.pm-method-active-pill {
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

.pm-method-active-pill.is-inactive {
  background: #e2e8f0;
  color: #475569;
}

.pm-note-link {
  border: 0;
  background: transparent;
  color: #475569;
  font-size: 0.86rem;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0;
}

.pm-note-link svg {
  width: 13px;
  height: 13px;
}

.pm-note-link:hover {
  color: #1d4ed8;
}

.pm-method-row-actions {
  display: inline-flex;
  gap: 8px;
  align-items: center;
}

.pm-method-action-btn {
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

.pm-method-action-btn svg {
  width: 16px;
  height: 16px;
}

.pm-method-action-btn:hover {
  border-color: #93c5fd;
  background: #eff6ff;
}

.pm-method-action-btn.danger {
  color: #dc2626;
}

.pm-method-action-btn.danger:hover {
  border-color: #fecaca;
  background: #fef2f2;
}

.pm-method-table-footer {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e2e8f0;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 12px;
}

.pm-method-per-page {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.pm-method-per-page .form-control {
  width: 82px;
  height: 36px;
}

.pm-method-row-meta {
  color: #64748b;
  font-size: 0.9rem;
}

.pm-method-pagination {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  justify-self: end;
}

.pm-method-pagination span {
  color: #475569;
  font-size: 0.88rem;
  font-weight: 600;
}

.pm-method-note-modal {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2200;
  padding: 16px;
}

.pm-method-note-card {
  width: min(560px, 100%);
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid #d7e4ff;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
  padding: 16px;
}

.pm-method-note-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
}

.pm-method-note-head h6 {
  margin: 0;
  color: #0f172a;
  font-size: 1rem;
  font-weight: 800;
}

.pm-method-note-close {
  border: 0;
  background: transparent;
  font-size: 1.5rem;
  line-height: 1;
  color: #475569;
}

.pm-method-note-subtitle {
  color: #1d4ed8;
  font-size: 0.88rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.pm-method-note-body {
  margin: 0;
  color: #334155;
  font-size: 0.94rem;
  line-height: 1.5;
  white-space: pre-wrap;
}

@media (max-width: 1100px) {
  .pm-method-list-filters {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-method-table-footer {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .pm-method-pagination {
    justify-self: start;
  }
}

@media (max-width: 760px) {
  .pm-method-list-filters {
    grid-template-columns: 1fr;
  }

  .pm-method-row-actions {
    justify-content: flex-end;
    width: 100%;
  }
}
</style>
