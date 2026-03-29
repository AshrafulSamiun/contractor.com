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
          <input
            v-model="searchQuery"
            class="form-control"
            placeholder="Search parcels, recipients, tracking..."
          />
          <button
            v-if="searchQuery"
            class="pm-clear-btn"
            type="button"
            aria-label="Clear search"
            @click="searchQuery = ''"
          >
            &times;
          </button>
        </div>
        <div class="pm-topbar-actions">
          <button class="pm-icon-btn" type="button" aria-label="Notifications" @click="goNotifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
            <span class="pm-topbar-badge"></span>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings" @click="goSettings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user">
          <div class="pm-topbar-avatar">JA</div>
          <span class="pm-topbar-name">User</span>
          <span class="pm-topbar-pill">Admin</span>
        </div>
      </div>

      <div class="container pm-ops-page">
        <div class="pm-page-head">
          <div>
            <h2>To-Do List</h2>
            <p class="pm-muted">Manage, assign, and track daily operational tasks.</p>
          </div>
          <RouterLink v-if="canCreateTask" class="btn btn-primary px-4" to="/todo/new">+ Add Task</RouterLink>
        </div>

        <div class="pm-dash-card pm-todo-card">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>

          <div class="pm-todo-filters">
            <div>
              <label class="pm-field-label">Task No</label>
              <input v-model="filters.task_no" class="form-control" placeholder="Search task number..." />
            </div>
            <div>
              <label class="pm-field-label">Employee Name</label>
              <input v-model="filters.employee_name" class="form-control" placeholder="Search employee..." />
            </div>
            <div>
              <label class="pm-field-label">Task Status</label>
              <select v-model="filters.status" class="form-control">
                <option value="">All statuses</option>
                <option v-for="status in statusOptions" :key="status" :value="status">
                  {{ status }}
                </option>
              </select>
            </div>
            <div class="pm-filter-actions">
              <button class="btn btn-outline-primary pm-filter-reset" type="button" @click="resetFilters">Reset</button>
              <button class="btn btn-primary" type="button" :disabled="loading" @click="fetchTasks">Search</button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table pm-dash-table">
              <thead>
                <tr>
                  <th>Task No</th>
                  <th>Task Details</th>
                  <th>Employee Name</th>
                  <th>Due Date/Time</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="task in tasks" :key="task.id">
                  <td>
                    <button
                      class="btn btn-link p-0 fw-semibold text-primary text-decoration-none"
                      type="button"
                      @click="openTaskDetails(task)"
                    >
                      {{ task.task_no }}
                    </button>
                  </td>
                  <td>{{ task.details }}</td>
                  <td>{{ task.assignee?.name || task.employee_name || '-' }}</td>
                  <td>{{ formatDate(task.due_at) }}</td>
                  <td>
                    <div>{{ task.status }}</div>
                    <div v-if="task.reviewed_at" class="pm-help-text">
                      Reviewed by {{ task.reviewer?.name || 'Manager' }}
                    </div>
                  </td>
                  <td class="pm-table-actions">
                    <div class="pm-action-menu">
                      <button
                        class="pm-action-btn"
                        type="button"
                        aria-haspopup="menu"
                        :aria-expanded="openAction === task.id"
                        @click.stop="toggleAction(task.id)"
                      >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <circle cx="12" cy="5" r="1.8" />
                          <circle cx="12" cy="12" r="1.8" />
                          <circle cx="12" cy="19" r="1.8" />
                        </svg>
                      </button>
                      <div v-if="openAction === task.id" class="pm-action-dropdown" role="menu">
                        <RouterLink class="pm-action-item" :to="`/todo/${task.id}`">Edit</RouterLink>
                        <button
                          v-if="canReviewTask(task)"
                          class="pm-action-item"
                          type="button"
                          @click="reviewTask(task)"
                        >
                          Review
                        </button>
                        <button
                          v-if="canManageTask"
                          class="pm-action-item danger"
                          type="button"
                          @click="removeTask(task.id)"
                        >
                          Delete
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr v-if="!loading && tasks.length === 0">
                  <td colspan="6" class="text-center pm-muted">No tasks found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-if="detailOpen" class="pm-modal-backdrop" @click.self="closeTaskDetails">
      <div class="pm-modal-card">
        <div class="pm-modal-header">
          <div class="pm-modal-title">Task Details</div>
          <div class="pm-modal-actions">
            <button class="btn btn-sm btn-outline-secondary" type="button" @click="closeTaskDetails">Close</button>
          </div>
        </div>
        <div class="pm-modal-body">
          <div v-if="detailLoading" class="pm-muted">Loading task details...</div>
          <div v-else-if="detailError" class="alert alert-danger mb-0">{{ detailError }}</div>
          <div v-else-if="detailTask" class="row g-3">
            <div class="col-md-4">
              <div class="pm-help-text">Task No</div>
              <div class="fw-semibold">{{ detailTask.task_no || '-' }}</div>
            </div>
            <div class="col-md-4">
              <div class="pm-help-text">Status</div>
              <div class="fw-semibold">{{ detailTask.status || '-' }}</div>
            </div>
            <div class="col-md-4">
              <div class="pm-help-text">Due Date/Time</div>
              <div class="fw-semibold">{{ formatDate(detailTask.due_at) }}</div>
            </div>
            <div class="col-md-6">
              <div class="pm-help-text">Assigned Employee</div>
              <div class="fw-semibold">{{ detailTask.assignee?.name || detailTask.employee_name || '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="pm-help-text">Created By</div>
              <div class="fw-semibold">{{ detailTask.creator?.name || '-' }}</div>
            </div>
            <div class="col-12">
              <div class="pm-help-text">Task Details</div>
              <div>{{ detailTask.details || '-' }}</div>
            </div>
            <div class="col-md-4">
              <div class="pm-help-text">Reminder 1</div>
              <div>{{ formatDate(detailTask.reminder_1) }}</div>
            </div>
            <div class="col-md-4">
              <div class="pm-help-text">Reminder 2</div>
              <div>{{ formatDate(detailTask.reminder_2) }}</div>
            </div>
            <div class="col-md-4">
              <div class="pm-help-text">Reminder 3</div>
              <div>{{ formatDate(detailTask.reminder_3) }}</div>
            </div>
            <div class="col-md-6">
              <div class="pm-help-text">Action</div>
              <div>{{ detailTask.action || '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="pm-help-text">Action Date</div>
              <div>{{ formatDate(detailTask.action_date) }}</div>
            </div>
            <div class="col-md-6">
              <div class="pm-help-text">Reviewed By</div>
              <div>{{ detailTask.reviewer?.name || '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="pm-help-text">Reviewed At</div>
              <div>{{ formatDate(detailTask.reviewed_at) }}</div>
            </div>
            <div class="col-12">
              <div class="pm-help-text">Review Note</div>
              <div>{{ detailTask.review_note || '-' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { ref, watch, reactive, onMounted, onBeforeUnmount, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'

const TODO_OPEN_ACCESS = String(import.meta.env.VITE_TODO_OPEN_ACCESS ?? 'true').trim().toLowerCase() === 'true'
const router = useRouter()

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const loading = ref(false)
const error = ref('')
const detailOpen = ref(false)
const detailLoading = ref(false)
const detailError = ref('')
const detailTask = ref(null)

const tasks = ref([])
const openAction = ref(null)
const statusOptions = ['Pending', 'In Progress', 'Completed', 'On Hold']
const normalizedRole = computed(() => String(authState.user?.role || '').trim().toLowerCase())
const isSupervisor = computed(() => TODO_OPEN_ACCESS || ['admin', 'administrator', 'manager', 'super_admin', 'superadmin'].includes(normalizedRole.value))
const canCreateTask = computed(() => isSupervisor.value)
const canManageTask = computed(() => isSupervisor.value)
const filters = reactive({
  task_no: '',
  employee_name: '',
  status: '',
})

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const toggleSidebarHidden = () => {
  sidebarHidden.value = !sidebarHidden.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const goNotifications = () => {
  router.push('/notifications')
}

const goSettings = () => {
  router.push('/settings/theme')
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

const looksLikeDateTime = (value) => {
  if (!value) return false
  const text = String(value).trim()
  if (!text) return false
  return /^(\d{4}[-/]\d{1,2}[-/]\d{1,2})(?:[ T]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d{1,6})?(?:Z|[+-]\d{2}:?\d{2})?)?$/.test(text)
}

const formatDate = (value) => {
  if (!looksLikeDateTime(value)) return '-'
  const date = new Date(String(value).replace(' ', 'T'))
  if (Number.isNaN(date.getTime())) return '-'
  const year = date.getFullYear()
  if (year < 2000 || year > 2100) return '-'
  return date.toLocaleString()
}

const fetchTasks = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await client.get('/todo', { params: { ...filters } })
    tasks.value = data?.data || []
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load tasks.'
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.task_no = ''
  filters.employee_name = ''
  filters.status = ''
  fetchTasks()
}

const toggleAction = (id) => {
  openAction.value = openAction.value === id ? null : id
}

const canReviewTask = (task) => canManageTask.value && task.status === 'Completed' && !task.reviewed_at

const handleClickOutside = () => {
  openAction.value = null
}

const closeTaskDetails = () => {
  detailOpen.value = false
}

const openTaskDetails = async (task) => {
  detailOpen.value = true
  detailLoading.value = true
  detailError.value = ''
  detailTask.value = task || null
  try {
    const { data } = await client.get(`/todo/${task.id}`)
    detailTask.value = data?.data || task
  } catch (err) {
    detailError.value = err?.response?.data?.message || 'Failed to load task details.'
  } finally {
    detailLoading.value = false
  }
}

const handleEsc = (event) => {
  if (event.key === 'Escape') {
    closeTaskDetails()
  }
}

const removeTask = async (id) => {
  if (!confirm('Delete this task?')) return
  try {
    await client.delete(`/todo/${id}`)
    tasks.value = tasks.value.filter((task) => task.id !== id)
    window.dispatchEvent(new Event('pm-todo-updated'))
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to delete task.'
  }
}

const reviewTask = async (task) => {
  const note = prompt('Manager review note (optional):', task.review_note || '')
  if (note === null) return
  try {
    await client.post(`/todo/${task.id}/review`, { review_note: note })
    await fetchTasks()
    window.dispatchEvent(new Event('pm-todo-updated'))
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to review task.'
  } finally {
    openAction.value = null
  }
}

onMounted(() => {
  fetchTasks()
  window.addEventListener('click', handleClickOutside)
  window.addEventListener('keydown', handleEsc)
})

onBeforeUnmount(() => {
  window.removeEventListener('click', handleClickOutside)
  window.removeEventListener('keydown', handleEsc)
})
</script>


