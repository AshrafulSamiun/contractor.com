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
          <input v-model="search" class="form-control" placeholder="Search users..." />
          <button v-if="search" class="pm-clear-btn" type="button" @click="search = ''">&times;</button>
        </div>
        <div class="pm-topbar-actions">
          <button class="pm-icon-btn" type="button" aria-label="Notifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
          <div class="pm-topbar-avatar">{{ userInitials }}</div>
          <span class="pm-topbar-name">{{ userName }}</span>
          <span class="pm-topbar-pill">Admin</span>
          <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="m7 10 5 5 5-5H7Z" />
            </svg>
          </button>
          <div v-if="userMenuOpen" class="pm-user-menu">
            <button class="pm-user-item" type="button">Profile</button>
            <button class="pm-user-item" type="button">Account</button>
            <button class="pm-user-item danger" type="button">Log out</button>
          </div>
        </div>
      </div>

      <section class="pm-dashboard-content">
        <div class="container pm-ops-page">
          <div class="pm-page-head">
            <div>
              <h2>{{ pageTitle }}</h2>
              <div class="pm-page-subtitle">{{ pageSubtitle }}</div>
            </div>
            <button v-if="showCreate && canCreateUsers" class="btn btn-primary" type="button" @click="openForm = !openForm">
              {{ openForm ? 'Close' : 'Add User' }}
            </button>
          </div>

          <div v-if="showCreate && canCreateUsers && openForm" class="pm-dash-card pm-panel pm-ops-card mb-4">
            <h4 class="mb-3">Create User</h4>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Full Name *</label>
                <input v-model="form.name" class="form-control" placeholder="Full name" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Email *</label>
                <input v-model="form.email" class="form-control" placeholder="email@company.com" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Password *</label>
                <input v-model="form.password" type="password" class="form-control" placeholder="Password" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Role</label>
                <select v-model="form.role" class="form-control">
                  <option value="staff">Staff</option>
                  <option value="manager">Manager</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-primary" type="button" @click="createUser">Save User</button>
              <button class="btn btn-outline-primary" type="button" @click="resetForm">Reset</button>
            </div>
            <div v-if="error" class="text-danger mt-2">{{ error }}</div>
          </div>

          <div class="pm-dash-card pm-panel">
            <div class="pm-panel-head">
              <div>
                <h4>{{ tableTitle }}</h4>
                <p class="pm-muted">{{ tableSubtitle }}</p>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table pm-dash-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th v-if="showRoleColumn">Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in filteredUsers" :key="user.id">
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td v-if="showRoleColumn">
                      <select v-model="user.role" class="form-control form-control-sm" :disabled="!canEditUsers" @change="updateRole(user)">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                      </select>
                    </td>
                    <td>
                      <span :class="['pm-status-pill', user.is_active ? 'success' : 'warning']">
                        {{ user.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="d-flex gap-2">
                      <button class="btn btn-outline-primary btn-sm" :disabled="!canEditUsers" @click="toggleUser(user)">
                        {{ user.is_active ? 'Deactivate' : 'Activate' }}
                      </button>
                      <button
                        v-if="pageMode === 'roles'"
                        class="btn btn-outline-primary btn-sm"
                        :disabled="!canEditUsers"
                        @click="openPermissionEditor(user)"
                      >
                        Permissions
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!filteredUsers.length">
                    <td :colspan="showRoleColumn ? 5 : 4" class="text-center pm-muted">No users found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="pageMode === 'roles' && selectedPermissionUser" class="pm-dash-card pm-panel mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Permission Matrix</h4>
                <p class="pm-muted mb-0">
                  {{ selectedPermissionUser.name }} ({{ selectedPermissionUser.email }})
                </p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" @click="closePermissionEditor">Close</button>
            </div>

            <div v-if="permissionLoading" class="pm-muted">Loading permissions...</div>
            <div v-else>
              <div v-for="group in groupedPermissionRows" :key="group.module" class="mb-3">
                <h6 class="text-capitalize mb-2">{{ formatModuleName(group.module) }}</h6>
                <div class="row g-2">
                  <div v-for="row in group.rows" :key="`${row.module}.${row.action}`" class="col-md-4 col-lg-3">
                    <label class="form-check-label d-flex align-items-center gap-2">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        v-model="row.allowed"
                        :disabled="permissionSaving || !canEditUsers"
                      />
                      <span>{{ row.action.toUpperCase() }}</span>
                    </label>
                    <div class="pm-muted small">Source: {{ row.source }}</div>
                  </div>
                </div>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="button" :disabled="permissionSaving || !canEditUsers" @click="savePermissions">
                  {{ permissionSaving ? 'Saving...' : 'Save Permissions' }}
                </button>
                <button class="btn btn-outline-primary" type="button" :disabled="permissionSaving" @click="loadUserPermissions">
                  Reload
                </button>
              </div>

              <div v-if="permissionError" class="text-danger mt-2">{{ permissionError }}</div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import AppSidebar from '../components/AppSidebar.vue'
import { authState } from '../store/auth'
import client from '../api/client'
import { useRoute } from 'vue-router'
import { hasUserPermission } from '../config/permissions'

const users = ref([])
const openForm = ref(false)
const error = ref('')
const search = ref('')
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const route = useRoute()
const selectedPermissionUser = ref(null)
const permissionRows = ref([])
const permissionLoading = ref(false)
const permissionSaving = ref(false)
const permissionError = ref('')
const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'staff',
})

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})

const pageMode = computed(() => {
  if (route.path.includes('/admin/users/roles')) return 'roles'
  if (route.path.includes('/admin/users/status')) return 'status'
  return 'create'
})

const showCreate = computed(() => pageMode.value === 'create')
const showRoleColumn = computed(() => pageMode.value !== 'status')
const canReadUsers = computed(() => hasUserPermission(authState.user, 'users', 'read'))
const canCreateUsers = computed(() => hasUserPermission(authState.user, 'users', 'create'))
const canEditUsers = computed(() => hasUserPermission(authState.user, 'users', 'edit'))

const pageTitle = computed(() => {
  if (pageMode.value === 'roles') return 'Roles & Permissions'
  if (pageMode.value === 'status') return 'Activate / Deactivate'
  return 'User Management'
})

const pageSubtitle = computed(() => {
  if (pageMode.value === 'roles') return 'Assign roles and access levels.'
  if (pageMode.value === 'status') return 'Activate or deactivate user accounts.'
  return 'Manage staff access and roles.'
})

const tableTitle = computed(() => {
  if (pageMode.value === 'roles') return 'User Roles'
  if (pageMode.value === 'status') return 'Account Status'
  return 'All Users'
})

const tableSubtitle = computed(() => {
  if (pageMode.value === 'roles') return 'Change roles and permissions for staff.'
  if (pageMode.value === 'status') return 'Toggle account access for team members.'
  return 'Current account users and roles.'
})

const filteredUsers = computed(() => {
  let list = users.value
  if (!search.value) return list
  const term = search.value.toLowerCase()
  return list.filter((user) => {
    return (
      user.name?.toLowerCase().includes(term) ||
      user.email?.toLowerCase().includes(term) ||
      user.role?.toLowerCase().includes(term)
    )
  })
})

const groupedPermissionRows = computed(() => {
  const groups = {}
  for (const row of permissionRows.value) {
    if (!groups[row.module]) groups[row.module] = []
    groups[row.module].push(row)
  }

  return Object.keys(groups)
    .sort()
    .map((module) => ({
      module,
      rows: groups[module].sort((a, b) => a.action.localeCompare(b.action)),
    }))
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

const loadUsers = async () => {
  if (!canReadUsers.value) {
    users.value = []
    return
  }
  try {
    const { data } = await client.get('/admin/users')
    if (data?.success) {
      users.value = data.data?.data || []
    }
  } catch {
    users.value = []
  }
}

const createUser = async () => {
  if (!canCreateUsers.value) {
    error.value = 'You do not have permission to create users.'
    return
  }
  error.value = ''
  if (!form.value.name || !form.value.email || !form.value.password) {
    error.value = 'Name, email, and password are required.'
    return
  }
  try {
    await client.post('/admin/users', form.value)
    resetForm()
    openForm.value = false
    loadUsers()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to create user.'
  }
}

const toggleUser = async (user) => {
  if (!canEditUsers.value) return
  try {
    await client.patch(`/admin/users/${user.id}`, { is_active: !user.is_active })
    loadUsers()
  } catch {
    // ignore
  }
}

const updateRole = async (user) => {
  if (!canEditUsers.value) return
  try {
    await client.patch(`/admin/users/${user.id}`, { role: user.role })
    loadUsers()
  } catch {
    // ignore
  }
}

const formatModuleName = (module) => String(module || '').replace(/_/g, ' ')

const openPermissionEditor = async (user) => {
  selectedPermissionUser.value = user
  await loadUserPermissions()
}

const closePermissionEditor = () => {
  selectedPermissionUser.value = null
  permissionRows.value = []
  permissionError.value = ''
}

const loadUserPermissions = async () => {
  if (!selectedPermissionUser.value) return
  permissionLoading.value = true
  permissionError.value = ''
  try {
    const { data } = await client.get(`/admin/users/${selectedPermissionUser.value.id}/permissions`)
    if (data?.success) {
      permissionRows.value = (data.data?.rows || []).map((row) => ({
        module: row.module,
        action: row.action,
        allowed: Boolean(row.allowed),
        source: row.source || 'default',
      }))
    } else {
      permissionRows.value = []
    }
  } catch (e) {
    permissionRows.value = []
    permissionError.value = e?.response?.data?.message || 'Failed to load permissions.'
  } finally {
    permissionLoading.value = false
  }
}

const savePermissions = async () => {
  if (!selectedPermissionUser.value) return
  if (!canEditUsers.value) {
    permissionError.value = 'You do not have permission to edit user permissions.'
    return
  }

  permissionSaving.value = true
  permissionError.value = ''
  try {
    await client.put(`/admin/users/${selectedPermissionUser.value.id}/permissions`, {
      permissions: permissionRows.value.map((row) => ({
        module: row.module,
        action: row.action,
        allowed: Boolean(row.allowed),
      })),
    })
    await loadUserPermissions()
  } catch (e) {
    permissionError.value = e?.response?.data?.message || 'Failed to save permissions.'
  } finally {
    permissionSaving.value = false
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    password: '',
    role: 'staff',
  }
}

onMounted(loadUsers)
onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
})

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

watch(
  () => route.path,
  () => {
    if (pageMode.value !== 'roles') {
      closePermissionEditor()
    }
  }
)
</script>
