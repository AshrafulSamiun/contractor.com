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
            <h2>{{ isEdit ? 'Edit To-Do Task' : 'To-Do Entry Information' }}</h2>
            <p class="pm-muted">Create and manage task assignments.</p>
          </div>
          <RouterLink class="btn btn-outline-primary" to="/todo">To-Do List</RouterLink>
        </div>

        <div class="pm-dash-card pm-todo-card">
          <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
          <div v-if="formSuccess" class="alert alert-success">{{ formSuccess }}</div>
          <div v-if="!canCreateTask && !isEdit" class="alert alert-warning">
            Only Admin or Manager can create tasks. Staff can update only assigned tasks.
          </div>

          <div class="pm-form-grid">
            <div class="pm-form-group">
              <label class="pm-field-label">Task No</label>
              <input
                class="form-control"
                v-model="form.task_no"
                placeholder="Auto-generated after save"
                readonly
              />
              <div class="pm-help-text">Auto-generated task number</div>
            </div>
            <div class="pm-form-group">
              <label class="pm-field-label">Task Details *</label>
              <textarea
                class="form-control"
                rows="4"
                v-model="form.details"
                :disabled="isRestrictedEditor"
                placeholder="Describe the task in detail (minimum 10 characters)"
              ></textarea>
              <div class="pm-help-text">{{ form.details.length }}/10 characters minimum</div>
              <div v-if="errors.details" class="pm-form-error">{{ errors.details }}</div>
            </div>
            <div class="pm-form-group">
              <label class="pm-field-label">Assigned Employee *</label>
              <select class="form-control" v-model="form.assignee_user_id" :disabled="assigneesLoading || isRestrictedEditor">
                <option value="">Select employee</option>
                <option
                  v-for="person in assignees"
                  :key="person.id"
                  :value="String(person.id)"
                >
                  {{ person.name }} ({{ person.role }})
                </option>
              </select>
              <div
                v-if="form.assignee_user_id && !assignees.some((item) => String(item.id) === String(form.assignee_user_id))"
                class="pm-help-text"
              >
                Current assignee is no longer active.
              </div>
              <div class="pm-help-text">Admin/Manager assigns employee here. Staff will get the task in their To-Do List.</div>
              <div v-if="errors.assignee_user_id" class="pm-form-error">{{ errors.assignee_user_id }}</div>
            </div>
            <div class="pm-form-group">
              <label class="pm-field-label">Due Date / Time *</label>
              <input class="form-control" type="datetime-local" v-model="form.due_at" :disabled="isRestrictedEditor" />
              <div v-if="errors.due_at" class="pm-form-error">{{ errors.due_at }}</div>
            </div>
          </div>

          <div class="pm-divider"></div>
          <div class="pm-form-section">
            <div class="pm-section-title">Reminders (Optional)</div>
            <div class="pm-help-text">Pick reminder date and time. At that time, a To-Do notification log will be created.</div>
            <div class="pm-form-grid">
              <div class="pm-form-group">
                <label class="pm-field-label">Reminder 1</label>
                <input class="form-control" type="datetime-local" v-model="form.reminder_1" :disabled="isRestrictedEditor" />
                <div v-if="errors.reminder_1" class="pm-form-error">{{ errors.reminder_1 }}</div>
              </div>
              <div class="pm-form-group">
                <label class="pm-field-label">Reminder 2</label>
                <input class="form-control" type="datetime-local" v-model="form.reminder_2" :disabled="isRestrictedEditor" />
                <div v-if="errors.reminder_2" class="pm-form-error">{{ errors.reminder_2 }}</div>
              </div>
              <div class="pm-form-group">
                <label class="pm-field-label">Reminder 3</label>
                <input class="form-control" type="datetime-local" v-model="form.reminder_3" :disabled="isRestrictedEditor" />
                <div v-if="errors.reminder_3" class="pm-form-error">{{ errors.reminder_3 }}</div>
              </div>
            </div>
          </div>

          <div class="pm-divider"></div>
          <div class="pm-form-section">
            <div class="pm-section-title">Action Details</div>
            <div class="pm-help-text">If status is Completed, Action and Action Date are required.</div>
            <div class="pm-form-grid">
              <div class="pm-form-group">
                <label class="pm-field-label">Action <span v-if="isCompletedStatus">*</span></label>
                <input class="form-control" v-model="form.action" />
                <div v-if="errors.action" class="pm-form-error">{{ errors.action }}</div>
              </div>
              <div class="pm-form-group">
                <label class="pm-field-label">Action Date <span v-if="isCompletedStatus">*</span></label>
                <input class="form-control" type="date" v-model="form.action_date" :max="todayDate" />
                <div v-if="errors.action_date" class="pm-form-error">{{ errors.action_date }}</div>
              </div>
              <div class="pm-form-group">
                <label class="pm-field-label">Status *</label>
                <select class="form-control" v-model="form.status">
                  <option value="">Select status</option>
                  <option v-for="opt in statusOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <div v-if="errors.status" class="pm-form-error">{{ errors.status }}</div>
              </div>
            </div>
          </div>

          <div class="pm-form-actions">
            <button class="btn btn-primary px-5" type="button" :disabled="loading || !canSubmit" @click="submit(false)">
              {{ loading ? 'Saving...' : (isEdit ? 'Update' : 'Save') }}
            </button>
            <button
              v-if="!isEdit"
              class="btn btn-outline-primary px-5"
              type="button"
              :disabled="loading || !canSubmit"
              @click="submit(true)"
            >
              {{ loading ? 'Saving...' : 'Save & New' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'

const TODO_OPEN_ACCESS = String(import.meta.env.VITE_TODO_OPEN_ACCESS ?? 'true').trim().toLowerCase() === 'true'

const route = useRoute()
const router = useRouter()

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const loading = ref(false)
const formError = ref('')
const formSuccess = ref('')
const errors = reactive({})
const assignees = ref([])
const assigneesLoading = ref(false)

const statusOptions = ['Pending', 'In Progress', 'Completed', 'On Hold']

const form = reactive({
  task_no: '',
  assignee_user_id: '',
  details: '',
  due_at: '',
  reminder_1: '',
  reminder_2: '',
  reminder_3: '',
  action: '',
  action_date: '',
  status: '',
})

const isEdit = computed(() => Boolean(route.params.id))
const normalizedRole = computed(() => String(authState.user?.role || '').trim().toLowerCase())
const isSupervisor = computed(() => TODO_OPEN_ACCESS || ['admin', 'administrator', 'manager', 'super_admin', 'superadmin'].includes(normalizedRole.value))
const canCreateTask = computed(() => isSupervisor.value)
const canManageAssignment = computed(() => isSupervisor.value)
const isRestrictedEditor = computed(() => isEdit.value && !canManageAssignment.value)
const canSubmit = computed(() => isEdit.value || canCreateTask.value)
const isCompletedStatus = computed(() => form.status === 'Completed')
const todayDate = computed(() => {
  const date = new Date()
  const pad = (v) => String(v).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
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

watch(() => form.status, (value) => {
  if (value === 'Completed' && !form.action_date) {
    form.action_date = todayDate.value
  }
})

const resetForm = () => {
  form.task_no = ''
  form.assignee_user_id = ''
  form.details = ''
  form.due_at = ''
  form.reminder_1 = ''
  form.reminder_2 = ''
  form.reminder_3 = ''
  form.action = ''
  form.action_date = ''
  form.status = ''
}

const mapErrors = (errs) => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!errs) return
  Object.entries(errs).forEach(([key, value]) => {
    errors[key] = Array.isArray(value) ? value[0] : value
  })
}

const toInputDateTime = (value) => {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  const pad = (v) => String(v).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

const loadTask = async () => {
  if (!isEdit.value) return
  loading.value = true
  formError.value = ''
  try {
    const { data } = await client.get(`/todo/${route.params.id}`)
    const task = data?.data
    if (task) {
      form.task_no = task.task_no || ''
      form.assignee_user_id = task.assignee_user_id ? String(task.assignee_user_id) : ''
      form.details = task.details || ''
      form.due_at = toInputDateTime(task.due_at)
      form.reminder_1 = toInputDateTime(task.reminder_1)
      form.reminder_2 = toInputDateTime(task.reminder_2)
      form.reminder_3 = toInputDateTime(task.reminder_3)
      form.action = task.action || ''
      form.action_date = task.action_date || ''
      form.status = task.status || ''
    }
  } catch (err) {
    formError.value = err?.response?.data?.message || 'Failed to load task.'
  } finally {
    loading.value = false
  }
}

const loadAssignees = async () => {
  assigneesLoading.value = true
  try {
    const { data } = await client.get('/todo/assignees')
    assignees.value = data?.data || []
    if (!isEdit.value && !form.assignee_user_id && assignees.value.length) {
      const preferred = assignees.value.find((item) => String(item.role || '').toLowerCase() === 'staff') || assignees.value[0]
      form.assignee_user_id = String(preferred.id)
    }
  } catch (err) {
    if (canCreateTask.value || isEdit.value) {
      formError.value = err?.response?.data?.message || 'Failed to load employee list.'
    }
  } finally {
    assigneesLoading.value = false
  }
}

const submit = async (saveAndNew) => {
  if (!canSubmit.value) {
    formError.value = 'Only Admin or Manager can create tasks.'
    return
  }
  loading.value = true
  formError.value = ''
  formSuccess.value = ''
  mapErrors(null)
  try {
    const payload = {
      assignee_user_id: form.assignee_user_id ? Number(form.assignee_user_id) : null,
      details: form.details,
      due_at: form.due_at,
      reminder_1: form.reminder_1,
      reminder_2: form.reminder_2,
      reminder_3: form.reminder_3,
      action: form.action,
      action_date: form.action_date,
      status: form.status,
    }
    if (isEdit.value) {
      await client.put(`/todo/${route.params.id}`, payload)
      formSuccess.value = 'Task updated successfully.'
    } else {
      await client.post('/todo', payload)
      formSuccess.value = 'Task created successfully.'
    }
    window.dispatchEvent(new Event('pm-todo-updated'))
    if (saveAndNew && !isEdit.value) {
      resetForm()
    } else if (!saveAndNew) {
      const delayMs = isEdit.value ? 1500 : 0
      if (delayMs) {
        setTimeout(() => {
          router.push('/todo')
        }, delayMs)
      } else {
        router.push('/todo')
      }
    }
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    }
    formError.value = err?.response?.data?.message || 'Unable to save task.'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadAssignees(), loadTask()])
})
</script>
