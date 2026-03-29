<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Message Details</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Message</div>
        </div>
        <div class="pm-page-actions">
          <RouterLink class="btn btn-outline-secondary" to="/email/inbox">Back to Inbox</RouterLink>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4">
      <div v-if="loading">Loading...</div>
      <div v-else-if="!message">Message not found.</div>
      <div v-else>
        <h5 class="mb-2">{{ message.subject || '(No subject)' }}</h5>
        <div class="pm-muted mb-3">
          From: {{ message.from_name ? `${message.from_name} <${message.from_email}>` : (message.from_email || 'Unknown') }} |
          To: {{ message.to_email || 'Unknown' }} |
          {{ formatDate(message.created_at) }}
        </div>
        <div v-if="message.send_status" class="pm-muted small mb-3">
          Send status: <strong>{{ message.send_status }}</strong>
          <span v-if="message.send_error"> | Error: {{ message.send_error }}</span>
        </div>
        <div v-if="message.audit_logs && message.audit_logs.length" class="pm-muted small mb-3">
          Last activity:
          {{ message.audit_logs[message.audit_logs.length - 1].status }}
          <span v-if="message.audit_logs[message.audit_logs.length - 1].error">
            ({{ message.audit_logs[message.audit_logs.length - 1].error }})
          </span>
        </div>
        <div class="pm-muted small mb-3">
          <span v-if="message.reply_to">Reply-To: {{ message.reply_to }}</span>
          <span v-if="message.cc && message.cc.length"> | CC: {{ message.cc.join(', ') }}</span>
          <span v-if="message.bcc && message.bcc.length"> | BCC: {{ message.bcc.join(', ') }}</span>
        </div>
        <div class="pm-card pm-ops-card p-3" style="background:#fff;">
          <div v-if="message.body_html" v-html="message.body_html"></div>
          <pre v-else class="mb-0">{{ message.body_text }}</pre>
        </div>
        <div v-if="message.attachments && message.attachments.length" class="mt-3">
          <h6>Attachments</h6>
          <div class="pm-attach-grid">
            <div v-for="file in message.attachments" :key="file.id" class="pm-attach-card">
              <div class="pm-attach-thumb">
                <img v-if="isImage(file.mime)" :src="previewUrl(file)" alt="" @click="openLightbox(file)" />
                <div v-else-if="isPdf(file.mime)" class="pm-attach-icon">PDF</div>
                <div v-else class="pm-attach-icon">FILE</div>
              </div>
              <div class="pm-attach-name">{{ file.original_name }}</div>
              <div class="pm-attach-actions">
                <button v-if="isPdf(file.mime)" class="btn btn-sm btn-outline-secondary" type="button" @click="previewPdf(file)">
                  Preview
                </button>
                <button v-else-if="isImage(file.mime)" class="btn btn-sm btn-outline-secondary" type="button" @click="openLightbox(file)">
                  Preview
                </button>
                <button class="btn btn-sm btn-outline-primary" type="button" @click="download(file)">Download</button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="pdfViewer.visible" class="pm-modal-overlay" @click.self="closePdf">
          <div class="pm-modal">
            <div class="pm-modal-header">
              <strong>{{ pdfViewer.name }}</strong>
              <div class="pm-modal-actions">
                <button class="btn btn-sm btn-outline-secondary" type="button" @click="downloadPdf">Download</button>
                <button class="pm-modal-close" type="button" @click="closePdf">&times;</button>
              </div>
            </div>
            <iframe class="pm-pdf-frame" :src="pdfViewer.url"></iframe>
          </div>
        </div>

        <div v-if="lightbox.visible" class="pm-modal-overlay" @click.self="closeLightbox">
          <div
            class="pm-modal pm-lightbox"
            @touchstart="onTouchStart"
            @touchmove="onTouchMove"
            @touchend="onTouchEnd"
            ref="lightboxRef"
          >
            <button class="pm-modal-close" type="button" @click="closeLightbox">&times;</button>
            <div class="pm-lightbox-toolbar">
              <button class="btn btn-sm btn-outline-secondary" type="button" @click="zoomOut">-</button>
              <span class="pm-muted">Zoom {{ Math.round(lightbox.zoom * 100) }}%</span>
              <button class="btn btn-sm btn-outline-secondary" type="button" @click="zoomIn">+</button>
              <button class="btn btn-sm btn-outline-secondary" type="button" @click="zoomToFit">Fit</button>
              <button class="btn btn-sm btn-outline-secondary" type="button" @click="resetZoom">Reset</button>
              <input
                class="pm-zoom-slider"
                type="range"
                min="0.5"
                max="3"
                step="0.1"
                v-model.number="lightbox.zoom"
                @input="onZoomSlider"
              />
            </div>
            <img
              :src="lightbox.url"
              alt=""
              :style="{ transform: `translate(${lightbox.offsetX}px, ${lightbox.offsetY}px) scale(${lightbox.zoom})` }"
              ref="lightboxImageRef"
              @load="onImageLoad"
            />
          </div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <RouterLink v-if="canCreateEmail" class="btn btn-outline-primary" :to="`/email/new?reply=${message.id}`">Reply</RouterLink>
          <button v-if="canEditEmail" class="btn btn-outline-danger" type="button" @click="trash">Move to Trash</button>
          <RouterLink v-if="message.thread_id" class="btn btn-outline-secondary" :to="`/email/threads/${message.thread_id}`">
            View Thread
          </RouterLink>
        </div>
      </div>
    </section>
  </EmailLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import EmailLayout from '../components/EmailLayout.vue'
import { getMessage, trashMessage, downloadAttachment } from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'
import { setFlash } from '../../store/flash'

const route = useRoute()
const router = useRouter()
const message = ref(null)
const loading = ref(false)
const canCreateEmail = computed(() => hasUserPermission(authState.user, 'email', 'create'))
const canEditEmail = computed(() => hasUserPermission(authState.user, 'email', 'edit'))

const loadMessage = async () => {
  loading.value = true
  try {
    const { data } = await getMessage(route.params.id)
    message.value = data?.data || null
    setActiveEmailFolder(message.value?.folder)
  } finally {
    loading.value = false
  }
}

const trash = async () => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to move emails to trash.', 'warning', 2500)
    return
  }

  if (!message.value) return
  await trashMessage(message.value.id)
  router.push('/email/trash')
}

const download = async (file) => {
  if (!message.value) return
  const { data } = await downloadAttachment(message.value.id, file.id)
  const blob = new Blob([data])
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = file.original_name
  a.click()
  window.URL.revokeObjectURL(url)
}

const isImage = (mime) => (mime || '').startsWith('image/')
const isPdf = (mime) => mime === 'application/pdf'

const pdfViewer = ref({ visible: false, url: '', name: '' })
const lightbox = ref({ visible: false, url: '', zoom: 1, offsetX: 0, offsetY: 0 })
const attachments = ref([])
const activeIndex = ref(0)
const lightboxRef = ref(null)
const lightboxImageRef = ref(null)
const imageSize = ref({ width: 0, height: 0 })
const lastZoomKey = 'email_lightbox_zoom'
const fitZoom = ref(1)
const touchState = ref({
  startX: 0,
  startY: 0,
  lastX: 0,
  lastY: 0,
  velocityX: 0,
  velocityY: 0,
  startDistance: 0,
  startZoom: 1,
  pinching: false,
  panning: false,
  lastTap: 0,
})

const previewPdf = async (file) => {
  if (!message.value) return
  const { data } = await downloadAttachment(message.value.id, file.id)
  const blob = new Blob([data], { type: 'application/pdf' })
  const url = window.URL.createObjectURL(blob)
  pdfViewer.value = { visible: true, url, name: file.original_name }
}

const closePdf = () => {
  if (pdfViewer.value.url) {
    window.URL.revokeObjectURL(pdfViewer.value.url)
  }
  pdfViewer.value = { visible: false, url: '', name: '' }
}

const openLightbox = async (file) => {
  if (!message.value) return
  const { data } = await downloadAttachment(message.value.id, file.id)
  const blob = new Blob([data], { type: file.mime || 'image/*' })
  const url = window.URL.createObjectURL(blob)
  activeIndex.value = attachments.value.findIndex((f) => f.id === file.id)
  const savedZoom = parseFloat(localStorage.getItem(lastZoomKey) || '1')
  lightbox.value = { visible: true, url, zoom: isNaN(savedZoom) ? 1 : savedZoom, offsetX: 0, offsetY: 0 }
}

const closeLightbox = () => {
  if (lightbox.value.url) {
    window.URL.revokeObjectURL(lightbox.value.url)
  }
  lightbox.value = { visible: false, url: '', zoom: 1, offsetX: 0, offsetY: 0 }
}

const previewUrl = (file) => {
  if (!message.value) return ''
  return `/api/v1/email/messages/${message.value.id}/attachments/${file.id}`
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString()
}

const openByIndex = async (index) => {
  const file = attachments.value[index]
  if (!file) return
  if (isPdf(file.mime)) {
    await previewPdf(file)
  } else if (isImage(file.mime)) {
    await openLightbox(file)
  }
}

const nextAttachment = () => {
  if (!attachments.value.length) return
  const next = (activeIndex.value + 1) % attachments.value.length
  openByIndex(next)
}

const prevAttachment = () => {
  if (!attachments.value.length) return
  const prev = (activeIndex.value - 1 + attachments.value.length) % attachments.value.length
  openByIndex(prev)
}

const zoomIn = () => {
  lightbox.value.zoom = Math.min(3, (lightbox.value.zoom || 1) + 0.1)
  constrainOffsets()
  localStorage.setItem(lastZoomKey, String(lightbox.value.zoom))
}

const zoomOut = () => {
  lightbox.value.zoom = Math.max(0.5, (lightbox.value.zoom || 1) - 0.1)
  constrainOffsets()
  localStorage.setItem(lastZoomKey, String(lightbox.value.zoom))
}

const resetZoom = () => {
  lightbox.value.zoom = 1
  lightbox.value.offsetX = 0
  lightbox.value.offsetY = 0
  constrainOffsets()
  localStorage.setItem(lastZoomKey, String(lightbox.value.zoom))
}

const downloadPdf = async () => {
  if (!message.value || !pdfViewer.value.url) return
  const file = attachments.value.find((f) => f.original_name === pdfViewer.value.name)
  if (!file) return
  await download(file)
}

const onKey = (event) => {
  if (event.key === 'Escape') {
    closeLightbox()
    closePdf()
  }
  if (event.key === 'ArrowRight') {
    nextAttachment()
  }
  if (event.key === 'ArrowLeft') {
    prevAttachment()
  }
}

const distance = (t1, t2) => {
  const dx = t1.clientX - t2.clientX
  const dy = t1.clientY - t2.clientY
  return Math.sqrt(dx * dx + dy * dy)
}

const onTouchStart = (event) => {
  const now = Date.now()
  if (event.touches.length === 1) {
    if (now - touchState.value.lastTap < 280) {
      // double tap toggle between fit and 100%
      if (Math.abs(lightbox.value.zoom - fitZoom.value) < 0.05) {
        lightbox.value.zoom = 1
      } else {
        lightbox.value.zoom = fitZoom.value
      }
      lightbox.value.offsetX = 0
      lightbox.value.offsetY = 0
      constrainOffsets()
      localStorage.setItem(lastZoomKey, String(lightbox.value.zoom))
      touchState.value.lastTap = 0
      return
    }
    touchState.value.lastTap = now
  }

  if (event.touches.length === 1) {
    touchState.value.startX = event.touches[0].clientX
    touchState.value.startY = event.touches[0].clientY
    touchState.value.lastX = touchState.value.startX
    touchState.value.lastY = touchState.value.startY
    touchState.value.velocityX = 0
    touchState.value.velocityY = 0
    touchState.value.pinching = false
    touchState.value.panning = lightbox.value.zoom > 1
  } else if (event.touches.length === 2) {
    touchState.value.startDistance = distance(event.touches[0], event.touches[1])
    touchState.value.startZoom = lightbox.value.zoom
    touchState.value.pinching = true
    touchState.value.panning = false
  }
}

const onTouchMove = (event) => {
  if (event.touches.length === 2) {
    const currentDistance = distance(event.touches[0], event.touches[1])
    const scale = currentDistance / touchState.value.startDistance
    lightbox.value.zoom = Math.min(3, Math.max(0.5, touchState.value.startZoom * scale))
    lightbox.value.offsetX = 0
    lightbox.value.offsetY = 0
    constrainOffsets()
  }
  if (event.touches.length === 1 && touchState.value.panning) {
    const x = event.touches[0].clientX
    const y = event.touches[0].clientY
    const dx = x - touchState.value.lastX
    const dy = y - touchState.value.lastY
    lightbox.value.offsetX += dx
    lightbox.value.offsetY += dy
    touchState.value.velocityX = dx
    touchState.value.velocityY = dy
    touchState.value.lastX = x
    touchState.value.lastY = y
    constrainOffsets()
  }
}

const onTouchEnd = (event) => {
  if (touchState.value.pinching) {
    touchState.value.pinching = false
    return
  }
  if (event.changedTouches.length === 1) {
    const endX = event.changedTouches[0].clientX
    const deltaX = endX - touchState.value.startX
    if (!touchState.value.panning && Math.abs(deltaX) > 60) {
      if (deltaX < 0) {
        nextAttachment()
      } else {
        prevAttachment()
      }
    }
    if (touchState.value.panning) {
      const decay = () => {
        touchState.value.velocityX *= 0.9
        touchState.value.velocityY *= 0.9
        lightbox.value.offsetX += touchState.value.velocityX
        lightbox.value.offsetY += touchState.value.velocityY
        constrainOffsets()
        if (Math.abs(touchState.value.velocityX) > 0.5 || Math.abs(touchState.value.velocityY) > 0.5) {
          requestAnimationFrame(decay)
        }
      }
      decay()
    }
  }
}

const onImageLoad = () => {
  if (!lightboxImageRef.value) return
  imageSize.value = {
    width: lightboxImageRef.value.naturalWidth,
    height: lightboxImageRef.value.naturalHeight,
  }
  zoomToFit()
}

const zoomToFit = () => {
  if (!lightboxRef.value || !imageSize.value.width) return
  const box = lightboxRef.value.getBoundingClientRect()
  const maxW = box.width - 40
  const maxH = box.height - 120
  const scale = Math.min(maxW / imageSize.value.width, maxH / imageSize.value.height, 1)
  fitZoom.value = Math.max(0.5, Math.min(3, scale))
  lightbox.value.zoom = fitZoom.value
  lightbox.value.offsetX = 0
  lightbox.value.offsetY = 0
  localStorage.setItem(lastZoomKey, String(lightbox.value.zoom))
}

const onZoomSlider = () => {
  constrainOffsets()
  localStorage.setItem(lastZoomKey, String(lightbox.value.zoom))
}

const constrainOffsets = () => {
  if (!lightboxRef.value || !imageSize.value.width) return
  const box = lightboxRef.value.getBoundingClientRect()
  const maxW = box.width - 40
  const maxH = box.height - 120
  const scaledW = imageSize.value.width * lightbox.value.zoom
  const scaledH = imageSize.value.height * lightbox.value.zoom
  const maxX = Math.max(0, (scaledW - maxW) / 2)
  const maxY = Math.max(0, (scaledH - maxH) / 2)
  lightbox.value.offsetX = Math.min(maxX, Math.max(-maxX, lightbox.value.offsetX))
  lightbox.value.offsetY = Math.min(maxY, Math.max(-maxY, lightbox.value.offsetY))
}

onMounted(async () => {
  await loadMessage()
  attachments.value = message.value?.attachments || []
  window.addEventListener('keydown', onKey)
})

onUnmounted(() => {
  closePdf()
  closeLightbox()
  window.removeEventListener('keydown', onKey)
})

const setActiveEmailFolder = (folder) => {
  const key = 'pm_sidebar_active_sub'
  const map = {
    inbox: 'email-inbox',
    sent: 'email-sent',
    drafts: 'email-drafts',
    trash: 'email-trash',
  }
  const value = map[folder] || 'email-inbox'
  sessionStorage.setItem(key, value)
  window.dispatchEvent(new CustomEvent('pm-email-folder', { detail: value }))
}
</script>
