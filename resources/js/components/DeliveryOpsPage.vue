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
          <input v-model="filters.q" class="form-control" placeholder="Search parcels, recipients, tracking..." />
          <button v-if="filters.q" class="pm-clear-btn" type="button" @click="filters.q = ''">&times;</button>
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
              <h2>{{ title }}</h2>
              <div class="pm-page-subtitle">{{ subtitle }}</div>
            </div>
            <div class="d-flex gap-2">
              <button v-if="showCreate && canCreateParcels" class="btn btn-primary" type="button" @click="openForm = !openForm">
                {{ openForm ? 'Close' : 'Add Parcel' }}
              </button>
              <button class="btn btn-outline-primary" type="button" :disabled="!canExportReports" @click="downloadReport">Export CSV</button>
            </div>
          </div>

          <div v-if="showCreate && canCreateParcels && openForm" class="pm-dash-card pm-panel pm-ops-card mb-4">
            <h4 class="mb-3">New Parcel Intake</h4>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Recipient Name *</label>
                <input v-model="form.recipient_name" class="form-control" placeholder="Recipient name" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Facility</label>
                <input v-model="form.facility_name" class="form-control" placeholder="Facility name" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Location</label>
                <input v-model="form.location" class="form-control" placeholder="Locker / Front desk" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Tracking Code</label>
                <input v-model="form.tracking_code" class="form-control" placeholder="Tracking code" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Status</label>
                <select v-model="form.status" class="form-control">
                  <option value="pending">Pending</option>
                  <option value="picked_up">Picked Up</option>
                  <option value="delivered">Delivered</option>
                  <option value="held">Held</option>
                  <option value="returned">Returned</option>
                  <option value="rejected">Rejected</option>
                  <option value="lost">Lost</option>
                  <option value="damaged">Damaged</option>
                  <option value="expired">Expired</option>
                </select>
              </div>
              <div class="col-12">
                <label class="pm-field-label">Notes</label>
                <textarea v-model="form.notes" class="form-control" rows="3" placeholder="Notes"></textarea>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-primary" type="button" :disabled="!canCreateParcels" @click="createParcel">Save Parcel</button>
              <button class="btn btn-outline-primary" type="button" :disabled="!canCreateParcels" @click="resetForm">Reset</button>
            </div>
            <div v-if="error" class="text-danger mt-2">{{ error }}</div>
          </div>

          <div v-if="allowScan && canEditParcels" class="pm-dash-card pm-panel pm-ops-card mb-4">
            <h4 class="mb-3">Scan / Update Status</h4>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Tracking Code *</label>
                <input v-model="scanForm.tracking_code" class="form-control" placeholder="Scan or enter code" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">Status</label>
                <select v-model="scanForm.status" class="form-control">
                  <option value="pending">Pending</option>
                  <option value="picked_up">Picked Up</option>
                  <option value="delivered">Delivered</option>
                  <option value="held">Held</option>
                  <option value="returned">Returned</option>
                  <option value="rejected">Rejected</option>
                  <option value="lost">Lost</option>
                  <option value="damaged">Damaged</option>
                  <option value="expired">Expired</option>
                </select>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-primary" type="button" :disabled="!canEditParcels" @click="scanUpdate">Update Status</button>
              <button class="btn btn-outline-primary" type="button" :disabled="!canEditParcels" @click="startCamera">Start Camera</button>
              <button class="btn btn-outline-primary" type="button" :disabled="!canEditParcels" @click="resetScan">Reset</button>
            </div>
            <div v-if="cameraActive" class="pm-scan-frame">
              <div class="pm-scan-view">
                <video ref="videoRef" autoplay muted playsinline></video>
                <div class="pm-scan-overlay">Align QR code within the frame</div>
                <div class="pm-scan-target"></div>
              </div>
              <button class="btn btn-light btn-sm" type="button" @click="stopCamera">Stop</button>
            </div>
            <div v-if="scanError" class="text-danger mt-2">{{ scanError }}</div>
          </div>

          <div class="pm-dash-card pm-panel pm-ops-card mb-4">
            <div class="pm-panel-head">
              <div>
                <h4>Filters</h4>
                <p class="pm-muted">Search and narrow down by date.</p>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="pm-field-label">Search</label>
                <input v-model="filters.q" class="form-control" placeholder="Recipient, tracking, facility..." />
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">From</label>
                <input v-model="filters.from" type="date" class="form-control" />
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">To</label>
                <input v-model="filters.to" type="date" class="form-control" />
              </div>
              <div class="col-md-2 d-flex align-items-end gap-2">
                <button class="btn btn-outline-primary w-100" type="button" @click="loadParcels">Apply</button>
                <button class="btn btn-light w-100" type="button" @click="resetFilters">Reset</button>
              </div>
            </div>
          </div>

          <div class="pm-dash-card pm-panel pm-ops-card">
            <div class="pm-panel-head">
              <div>
                <h4>{{ listTitle }}</h4>
                <p class="pm-muted">{{ listSubtitle }}</p>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table pm-dash-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Recipient</th>
                    <th>Status</th>
                    <th>Facility</th>
                    <th>Location</th>
                    <th>Updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="parcel in parcels" :key="parcel.id">
                    <td>PK-{{ parcel.id }}</td>
                    <td>{{ parcel.recipient_name }}</td>
                    <td><span :class="['pm-status-pill', statusClass(parcel.status)]">{{ formatStatus(parcel.status) }}</span></td>
                    <td>{{ parcel.facility_name || '-' }}</td>
                    <td>{{ parcel.location || '-' }}</td>
                    <td>{{ formatDate(parcel.updated_at) }}</td>
                    <td>
                      <div class="pm-ops-actions">
                        <select class="form-control form-control-sm" v-model="quickStatus[parcel.id]" :disabled="!canEditParcels">
                          <option value="pending">Pending</option>
                          <option value="picked_up">Picked Up</option>
                          <option value="delivered">Delivered</option>
                          <option value="held">Held</option>
                          <option value="returned">Returned</option>
                          <option value="rejected">Rejected</option>
                          <option value="lost">Lost</option>
                          <option value="damaged">Damaged</option>
                          <option value="expired">Expired</option>
                        </select>
                        <button class="btn btn-outline-primary btn-sm" type="button" :disabled="!canEditParcels" @click="applyQuick(parcel.id)">
                          Update
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!parcels.length">
                    <td colspan="7" class="text-center pm-muted">No records found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import AppSidebar from './AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { setFlash } from '../store/flash'
import { hasUserPermission } from '../config/permissions'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, required: true },
  listTitle: { type: String, required: true },
  listSubtitle: { type: String, required: true },
  status: { type: String, default: '' },
  statusIn: { type: Array, default: () => [] },
  showCreate: { type: Boolean, default: false },
  allowScan: { type: Boolean, default: true },
  defaultStatus: { type: String, default: 'pending' },
})

const parcels = ref([])
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')
const openForm = ref(false)
const error = ref('')
const scanError = ref('')
const cameraActive = ref(false)
const videoRef = ref(null)
const quickStatus = ref({})
const filters = ref({ q: '', from: '', to: '' })
const form = ref({
  recipient_name: '',
  facility_name: '',
  location: '',
  tracking_code: '',
  status: props.defaultStatus || 'pending',
  notes: '',
})
const scanForm = ref({
  tracking_code: '',
  status: props.defaultStatus || 'pending',
})

let scanStream = null
let scanInterval = null

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.split(' ')
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
})

const canReadParcels = computed(() => hasUserPermission(authState.user, 'parcels', 'read'))
const canCreateParcels = computed(() => hasUserPermission(authState.user, 'parcels', 'create'))
const canEditParcels = computed(() => hasUserPermission(authState.user, 'parcels', 'edit'))
const canExportReports = computed(() => hasUserPermission(authState.user, 'reports', 'export'))

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

const loadParcels = async () => {
  if (!canReadParcels.value) {
    parcels.value = []
    quickStatus.value = {}
    return
  }

  try {
    const params = {
      q: filters.value.q || undefined,
      from: filters.value.from || undefined,
      to: filters.value.to || undefined,
    }
    if (props.statusIn.length) {
      params.status_in = props.statusIn.join(',')
    } else if (props.status) {
      params.status = props.status
    }
    const { data } = await client.get('/parcels', { params })
    if (data?.success) {
      parcels.value = data.data?.data || []
      const nextQuick = {}
      parcels.value.forEach((parcel) => {
        nextQuick[parcel.id] = parcel.status || props.defaultStatus
      })
      quickStatus.value = nextQuick
    }
  } catch {
    parcels.value = []
  }
}

const createParcel = async () => {
  if (!canCreateParcels.value) return

  error.value = ''
  if (!form.value.recipient_name) {
    error.value = 'Recipient name is required.'
    return
  }
  try {
    await client.post('/parcels', form.value)
    resetForm()
    openForm.value = false
    loadParcels()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to save parcel.'
  }
}

const resetForm = () => {
  form.value = {
    recipient_name: '',
    facility_name: '',
    location: '',
    tracking_code: '',
    status: props.defaultStatus || 'pending',
    notes: '',
  }
}

const scanUpdate = async () => {
  if (!canEditParcels.value) return

  scanError.value = ''
  if (!scanForm.value.tracking_code) {
    scanError.value = 'Tracking code is required.'
    return
  }
  try {
    const { data } = await client.post('/parcels/scan', scanForm.value)
    if (data?.success && data?.data?.id) {
      setFlash(`Status updated for ${data.data.tracking_code || scanForm.value.tracking_code}.`, 'success', 2500)
    }
    resetScan()
    loadParcels()
  } catch (e) {
    scanError.value = e?.response?.data?.message || 'Failed to update status.'
  }
}

const resetScan = () => {
  scanForm.value = {
    tracking_code: '',
    status: props.defaultStatus || 'pending',
  }
}

const applyQuick = async (parcelId) => {
  if (!canEditParcels.value) return

  const status = quickStatus.value[parcelId]
  if (!status) return
  try {
    await client.put(`/parcels/${parcelId}`, { status })
    setFlash('Status updated.', 'success', 2000)
    loadParcels()
  } catch (e) {
    setFlash(e?.response?.data?.message || 'Failed to update status.', 'danger', 2500)
  }
}

const downloadReport = async () => {
  if (!canExportReports.value) {
    setFlash('You do not have permission to export reports.', 'warning', 2500)
    return
  }

  const params = new URLSearchParams()
  if (props.statusIn.length) params.append('status_in', props.statusIn.join(','))
  if (props.status) params.append('status', props.status)
  if (filters.value.q) params.append('q', filters.value.q)
  if (filters.value.from) params.append('from', filters.value.from)
  if (filters.value.to) params.append('to', filters.value.to)
  window.location.href = `/api/v1/reports/parcels?${params.toString()}`
}

const resetFilters = () => {
  filters.value = { q: '', from: '', to: '' }
  loadParcels()
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

const formatStatus = (value) => {
  if (!value) return '-'
  const map = {
    pending: 'Pending',
    picked_up: 'Picked Up',
    delivered: 'Delivered',
    held: 'Held',
    returned: 'Returned',
    rejected: 'Rejected',
    lost: 'Lost',
    damaged: 'Damaged',
    expired: 'Expired',
  }
  return map[value] || value.replace(/_/g, ' ')
}

const statusClass = (status) => {
  if (status === 'delivered' || status === 'picked_up') return 'success'
  if (['held', 'returned', 'rejected', 'expired'].includes(status)) return 'warning'
  if (['lost', 'damaged'].includes(status)) return 'danger'
  return 'pending'
}

const startCamera = async () => {
  scanError.value = ''
  try {
    scanStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    if (videoRef.value) {
      videoRef.value.srcObject = scanStream
    }
    cameraActive.value = true
    startScanLoop()
  } catch {
    scanError.value = 'Camera access denied or unavailable.'
  }
}

const stopCamera = () => {
  if (scanStream) {
    scanStream.getTracks().forEach((track) => track.stop())
  }
  scanStream = null
  cameraActive.value = false
  if (scanInterval) clearInterval(scanInterval)
}

const startScanLoop = () => {
  if (!('BarcodeDetector' in window)) {
    scanError.value = 'BarcodeDetector not supported in this browser.'
    return
  }
  const detector = new window.BarcodeDetector({ formats: ['qr_code'] })
  scanInterval = setInterval(async () => {
    if (!videoRef.value) return
    try {
      const codes = await detector.detect(videoRef.value)
      if (codes.length) {
        scanForm.value.tracking_code = codes[0].rawValue || ''
        stopCamera()
      }
    } catch {
      // ignore
    }
  }, 700)
}

onMounted(loadParcels)
onUnmounted(() => stopCamera())

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
})
</script>
