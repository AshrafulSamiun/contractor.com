<template>
  <div class="pm-dashboard">
    <div class="container pm-ops-page">
      <div class="pm-dashboard-header">
        <div>
          <h2>Parcels</h2>
          <p class="pm-muted">Create, track, and update parcel status.</p>
        </div>
        <button class="btn btn-primary" type="button" @click="openForm = !openForm">
          {{ openForm ? 'Close' : 'Add Parcel' }}
        </button>
      </div>

      <div v-if="openForm" class="pm-dash-card pm-panel pm-ops-card mb-4">
        <h4 class="mb-3">New Parcel</h4>
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
            </select>
          </div>
          <div class="col-12">
            <label class="pm-field-label">Notes</label>
            <textarea v-model="form.notes" class="form-control" rows="3" placeholder="Notes"></textarea>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="button" @click="createParcel">Save Parcel</button>
          <button class="btn btn-outline-primary" type="button" @click="resetForm">Reset</button>
        </div>
        <div v-if="error" class="text-danger mt-2">{{ error }}</div>
      </div>

      <div class="pm-dash-card pm-panel pm-ops-card mb-4">
        <h4 class="mb-3">Scan QR / Update Status</h4>
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
            </select>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="button" @click="scanUpdate">Update Status</button>
          <button class="btn btn-outline-primary" type="button" @click="startCamera">Start Camera</button>
          <button class="btn btn-outline-primary" type="button" @click="resetScan">Reset</button>
          <button class="btn btn-outline-primary" type="button" @click="downloadReport">Export CSV</button>
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

      <div class="pm-dash-card pm-panel pm-ops-card">
        <div class="pm-panel-head">
          <div>
            <h4>All Parcels</h4>
            <p class="pm-muted">Latest parcel activity.</p>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <input
              v-model="listQuery"
              class="form-control form-control-sm"
              style="max-width: 320px;"
              placeholder="Search recipient, tracking, facility"
              @keydown.enter.prevent="applyListSearch"
            />
            <button class="btn btn-outline-primary btn-sm" type="button" @click="applyListSearch">Search</button>
            <button v-if="listQuery" class="btn btn-outline-secondary btn-sm" type="button" @click="clearListSearch">Clear</button>
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
                <th>History</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="parcel in parcels" :key="parcel.id">
                <td>PK-{{ parcel.id }}</td>
                <td>{{ parcel.recipient_name }}</td>
                <td><span :class="['pm-status-pill', statusClass(parcel.status)]">{{ parcel.status }}</span></td>
                <td>{{ parcel.facility_name || '-' }}</td>
                <td>{{ parcel.location || '-' }}</td>
                <td>{{ formatDate(parcel.updated_at) }}</td>
                <td>
                  <button class="btn btn-outline-primary btn-sm" type="button" @click="loadHistory(parcel.id)">
                    History
                  </button>
                </td>
              </tr>
              <tr v-if="!parcels.length">
                <td colspan="7" class="text-center pm-muted">No parcels yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="historyItems.length" class="pm-history-modal">
        <div class="pm-history-card">
          <div class="pm-history-head">
            <h6 class="mb-0">Parcel Status History</h6>
            <button class="btn btn-outline-primary btn-sm" type="button" @click="historyItems = []">Close</button>
          </div>
          <ul>
            <li v-for="item in historyItems" :key="item.id">
              <strong>{{ formatStatus(item.to_status) }}</strong>
              <span class="pm-muted">from {{ item.from_status ? formatStatus(item.from_status) : 'new' }}</span>
              <span class="pm-muted">• {{ formatDate(item.created_at) }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div v-if="scanSuccess" class="pm-history-modal">
        <div class="pm-history-card">
          <div class="pm-history-head">
            <h6 class="mb-0">Scan Success</h6>
            <button class="btn btn-outline-primary btn-sm" type="button" @click="scanSuccess = null">Close</button>
          </div>
          <div class="pm-muted mb-3">
            Status updated for <strong>{{ scanSuccess.tracking_code }}</strong>.
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" type="button" @click="openHistoryFromSuccess">
              View History
            </button>
            <button class="btn btn-outline-primary btn-sm" type="button" @click="scanSuccess = null">
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import client from '../api/client'
import { setFlash } from '../store/flash'

const parcels = ref([])
const openForm = ref(false)
const error = ref('')
const scanError = ref('')
const cameraActive = ref(false)
const videoRef = ref(null)
const historyItems = ref([])
const scanSuccess = ref(null)
const route = useRoute()
const router = useRouter()
const listQuery = ref('')
const form = ref({
  recipient_name: '',
  facility_name: '',
  location: '',
  tracking_code: '',
  status: 'pending',
  notes: '',
})
const scanForm = ref({
  tracking_code: '',
  status: 'delivered',
})

let scanStream = null
let scanInterval = null

const loadParcels = async () => {
  try {
    const query = listQuery.value.trim()
    const params = query ? { q: query } : undefined
    const { data } = await client.get('/parcels', { params })
    if (data?.success) {
      parcels.value = data.data?.data || []
    }
  } catch {
    parcels.value = []
  }
}

const syncListQueryFromRoute = () => {
  listQuery.value = String(route.query.q || '').trim()
}

const applyListSearch = async () => {
  const query = listQuery.value.trim()
  const currentQuery = String(route.query.q || '').trim()
  if (query === currentQuery) {
    await loadParcels()
    return
  }
  const nextQuery = { ...route.query }
  if (query) {
    nextQuery.q = query
  } else {
    delete nextQuery.q
  }
  await router.replace({ path: route.path, query: nextQuery })
}

const clearListSearch = async () => {
  listQuery.value = ''
  await applyListSearch()
}

const createParcel = async () => {
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
    status: 'pending',
    notes: '',
  }
}

const scanUpdate = async () => {
  scanError.value = ''
  if (!scanForm.value.tracking_code) {
    scanError.value = 'Tracking code is required.'
    return
  }
  try {
    const { data } = await client.post('/parcels/scan', scanForm.value)
    if (data?.success && data?.data?.id) {
      scanSuccess.value = {
        id: data.data.id,
        tracking_code: data.data.tracking_code || scanForm.value.tracking_code,
      }
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
    status: 'delivered',
  }
}

const downloadReport = async () => {
  try {
    const response = await client.get('/reports/parcels', { responseType: 'blob' })
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'parcels.csv'
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  } catch {
    // ignore
  }
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

const statusClass = (status) => {
  if (status === 'delivered' || status === 'picked_up') return 'success'
  if (status === 'held' || status === 'returned') return 'warning'
  return 'pending'
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

const loadHistory = async (parcelId) => {
  try {
    const { data } = await client.get(`/parcels/${parcelId}/history`)
    if (data?.success) historyItems.value = data.data || []
  } catch {
    historyItems.value = []
  }
}

const openHistoryFromSuccess = () => {
  if (!scanSuccess.value?.id) return
  loadHistory(scanSuccess.value.id)
  scanSuccess.value = null
}

watch(
  () => route.query.q,
  () => {
    syncListQueryFromRoute()
    loadParcels()
  },
  { immediate: true },
)

onMounted(() => {
  if (route.query.scan) {
    startCamera()
  }
})
onUnmounted(() => stopCamera())
</script>
