<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Inbox</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Inbox</div>
        </div>
        <div class="pm-page-actions">
          <RouterLink v-if="canCreateEmail" class="btn btn-primary" to="/email/new">New Email</RouterLink>
          <button class="btn btn-outline-secondary" type="button" @click="sync">Sync</button>
          <button class="btn btn-outline-secondary" type="button" @click="loadMessages">Refresh</button>
        </div>
        <div class="pm-muted small mt-2">
          <span v-if="syncInfo.lastSync">Last sync: {{ formatDate(syncInfo.lastSync) }}</span>
          <span v-else>No sync yet</span>
          <span v-if="syncInfo.error" class="pm-sync-error"> | {{ syncInfo.error }}</span>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4 pm-inbox-shell">
      <div class="pm-inbox-toolbar">
        <div class="pm-inbox-search">
          <label class="pm-field-label">Search</label>
          <input v-model="query" class="form-control" placeholder="Search subject, sender, or preview..." />
        </div>
        <div class="pm-inbox-toolbar-actions">
          <button class="btn btn-outline-secondary" type="button" @click="loadMessages">Filter</button>
          <button class="btn btn-outline-secondary" type="button" @click="clearSearch">Clear</button>
        </div>
      </div>

      <div class="pm-inbox-grid">
        <aside class="pm-inbox-list">
          <div v-if="loading" class="pm-inbox-empty">Loading conversations...</div>
          <div v-else-if="!threads.length" class="pm-inbox-empty">No conversations found.</div>
          <button
            v-else
            v-for="thread in threads"
            :key="thread.thread_id"
            class="pm-inbox-row"
            :class="{
              active: selectedThreadId === thread.thread_id,
              unread: !!thread.unread_count,
            }"
            @click="selectThread(thread)"
            @contextmenu="onContextMenu($event, thread.thread_id)"
          >
            <div class="pm-inbox-row-head">
              <div class="pm-thread-sender">
                <div
                  class="pm-thread-avatar"
                  :style="{ background: avatarBg(thread.from_email), color: avatarColor(thread.from_email) }"
                >
                  {{ initials(thread.from_email) }}
                </div>
                <span class="pm-thread-name">{{ thread.from_email || 'Unknown' }}</span>
              </div>
              <span class="pm-inbox-time">{{ formatDate(thread.latest_at) }}</span>
            </div>
            <div class="pm-inbox-subject">
              <span v-if="thread.unread_count" class="pm-thread-dot"></span>
              <strong>{{ thread.subject || '(No subject)' }}</strong>
            </div>
            <div class="pm-inbox-preview-line">
              <span v-if="thread.spam_flag" class="pm-spam-badge">Spam</span>
              <span>{{ thread.preview || '--' }}</span>
            </div>
            <div class="pm-inbox-row-foot">
              <span v-if="thread.unread_count" class="pm-unread-badge">{{ thread.unread_count }}</span>
              <span v-else class="pm-muted">Read</span>
              <span class="pm-inbox-thread-id">#{{ thread.thread_id }}</span>
            </div>
          </button>
        </aside>

        <article class="pm-inbox-preview-panel">
          <div v-if="!selectedThread" class="pm-inbox-empty">
            Select a conversation to preview details.
          </div>
          <div v-else class="pm-inbox-preview-content">
            <div class="pm-inbox-preview-title">
              <h5>{{ selectedThread.subject || '(No subject)' }}</h5>
              <span v-if="selectedThread.spam_flag" class="pm-spam-badge">Spam</span>
            </div>
            <div class="pm-inbox-preview-meta">
              <span><strong>From:</strong> {{ selectedThread.from_email || 'Unknown' }}</span>
              <span><strong>Latest:</strong> {{ formatDate(selectedThread.latest_at) }}</span>
              <span><strong>Unread:</strong> {{ selectedThread.unread_count || 0 }}</span>
            </div>
            <p class="pm-inbox-preview-body">
              {{ selectedThread.preview || 'No preview available for this thread.' }}
            </p>

            <div class="pm-thread-actions">
              <button class="btn btn-sm btn-outline-secondary" @click="openThread(selectedThread.thread_id)">Open Thread</button>
              <button v-if="canQuickReply" class="btn btn-sm btn-outline-primary" @click="openQuickReply(selectedThread.thread_id)">Quick Reply</button>
              <button v-if="canEditEmail" class="btn btn-sm btn-outline-secondary" @click="toggleRead(selectedThread)">
                {{ selectedThread.unread_count ? 'Mark Read' : 'Mark Unread' }}
              </button>
              <button v-if="canEditEmail" class="btn btn-sm btn-outline-danger" @click="trashThread(selectedThread.thread_id)">Trash</button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section v-if="quickReply.threadId" class="pm-card pm-ops-card p-4">
      <h5 class="pm-form-title">Quick Reply</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pm-field-label">To</label>
          <input class="form-control" v-model="quickReply.to" disabled />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Subject</label>
          <input class="form-control" v-model="quickReply.subject" />
        </div>
        <div class="col-md-6">
          <label class="pm-field-label">Template</label>
          <select class="form-control" v-model="quickReply.templateId" @change="applyQuickTemplate">
            <option value="">Select template</option>
            <option v-for="template in templates" :key="template.id" :value="template.id">
              {{ template.name }}
            </option>
          </select>
        </div>
        <div class="col-md-6 d-flex align-items-end justify-content-end">
          <button class="btn btn-outline-secondary" type="button" @click="toggleEditor">
            {{ quickReply.rich ? 'Use Plain Text' : 'Use Rich Text' }}
          </button>
        </div>
        <div class="col-md-12">
          <label class="pm-field-label">Message</label>
          <div v-if="quickReply.rich" class="pm-rich-toolbar">
            <button class="pm-rich-btn" type="button" @click="formatRich('bold')"><strong>B</strong></button>
            <button class="pm-rich-btn" type="button" @click="formatRich('italic')"><em>I</em></button>
            <button class="pm-rich-btn" type="button" @click="formatRich('underline')"><u>U</u></button>
            <button class="pm-rich-btn" type="button" @click="formatRich('insertUnorderedList')">- List</button>
            <button class="pm-rich-btn" type="button" @click="formatRich('insertOrderedList')">1. List</button>
          </div>
          <textarea
            v-if="!quickReply.rich"
            class="form-control"
            rows="4"
            v-model="quickReply.body"
            placeholder="Write a quick reply..."
          ></textarea>
          <div
            v-else
            class="pm-rich-editor"
            contenteditable="true"
            :placeholder="'Write a quick reply...'"
            @input="onRichInput"
            ref="richRef"
          ></div>
        </div>
        <div class="col-md-12">
          <label class="pm-field-label">Attachments</label>
          <input class="form-control" type="file" multiple :disabled="!canEditEmail" @change="onFileChange" />
          <div v-if="quickReply.attachments.length" class="pm-attach-list">
            <div v-for="(file, index) in quickReply.attachments" :key="file.name + index" class="pm-attach-item">
              <span>{{ file.name }}</span>
              <button class="pm-attach-remove" type="button" :disabled="!canEditEmail" @click="removeAttachment(index)">Remove</button>
            </div>
          </div>
          <div class="pm-muted small mt-2">Attachments are queued for sending (max 5MB each).</div>
        </div>
      </div>
      <div class="pm-form-actions pm-form-actions-right mt-3">
        <button class="btn btn-outline-secondary" type="button" @click="closeQuickReply">Cancel</button>
        <button class="btn btn-primary" type="button" :disabled="quickReply.sending || !canQuickReply" @click="sendQuickReply">
          {{ quickReply.sending ? 'Sending...' : 'Send Reply' }}
        </button>
      </div>
    </section>

    <div
      v-if="contextMenu.visible"
      class="pm-context-menu"
      :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
      @click="contextMenu.visible = false"
    >
      <button class="pm-context-item" type="button" @click="openThread(contextMenu.threadId)">Open</button>
      <button v-if="canQuickReply" class="pm-context-item" type="button" @click="openQuickReply(contextMenu.threadId)">Quick Reply</button>
      <button
        v-if="canEditEmail"
        class="pm-context-item"
        type="button"
        @click="toggleRead({ thread_id: contextMenu.threadId, unread_count: contextMenu.unreadCount })"
      >
        {{ contextMenu.unreadCount ? 'Mark Read' : 'Mark Unread' }}
      </button>
      <button v-if="canEditEmail" class="pm-context-item danger" type="button" @click="trashThread(contextMenu.threadId)">Trash</button>
    </div>
  </EmailLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import EmailLayout from '../components/EmailLayout.vue'
import {
  fetchMessages,
  trashMessage,
  syncInbox,
  fetchThread,
  sendMessage,
  createMessage,
  fetchTemplates,
  uploadAttachments,
  markThread,
  fetchEmailSettings,
} from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'
import { setFlash } from '../../store/flash'

const router = useRouter()
const threads = ref([])
const loading = ref(false)
const query = ref('')
const selectedThreadId = ref(null)
const templates = ref([])
const syncInfo = ref({ lastSync: null, error: null })
const richRef = ref(null)
const quickReply = ref({
  threadId: null,
  to: '',
  subject: '',
  body: '',
  inReplyTo: '',
  sending: false,
  templateId: '',
  rich: false,
  attachments: [],
})
const contextMenu = ref({
  visible: false,
  x: 0,
  y: 0,
  threadId: null,
  unreadCount: 0,
})
const canReadEmail = computed(() => hasUserPermission(authState.user, 'email', 'read'))
const canCreateEmail = computed(() => hasUserPermission(authState.user, 'email', 'create'))
const canEditEmail = computed(() => hasUserPermission(authState.user, 'email', 'edit'))
const canSendEmail = computed(() => hasUserPermission(authState.user, 'email', 'send'))
const canQuickReply = computed(() => canCreateEmail.value && canSendEmail.value)
const selectedThread = computed(() => threads.value.find((item) => item.thread_id === selectedThreadId.value) || null)

const loadMessages = async () => {
  if (!canReadEmail.value) {
    threads.value = []
    selectedThreadId.value = null
    return
  }

  loading.value = true
  try {
    const { data } = await fetchMessages('inbox', { q: query.value, threaded: true })
    threads.value = data?.data || []
    if (!threads.value.length) {
      selectedThreadId.value = null
    } else if (!threads.value.some((item) => item.thread_id === selectedThreadId.value)) {
      selectedThreadId.value = threads.value[0].thread_id
    }
  } finally {
    loading.value = false
  }
}

const selectThread = (thread) => {
  if (!thread?.thread_id) return
  selectedThreadId.value = thread.thread_id
}

const clearSearch = () => {
  query.value = ''
  loadMessages()
}

const openQuickReply = async (threadId) => {
  if (!canQuickReply.value) {
    setFlash('You do not have permission to send replies.', 'warning', 2500)
    return
  }

  quickReply.value.threadId = threadId
  quickReply.value.sending = false
  quickReply.value.templateId = ''
  quickReply.value.rich = false
  quickReply.value.attachments = []
  const { data } = await fetchThread(threadId)
  const items = data?.data || []
  const latest = items[items.length - 1]
  quickReply.value.to = latest?.from_email || ''
  const subject = latest?.subject || ''
  quickReply.value.subject = subject.startsWith('Re:') ? subject : `Re: ${subject}`
  quickReply.value.inReplyTo = latest?.message_id || ''
  quickReply.value.body = ''
  if (richRef.value) {
    richRef.value.innerHTML = ''
  }
}

const closeQuickReply = () => {
  quickReply.value = {
    threadId: null,
    to: '',
    subject: '',
    body: '',
    inReplyTo: '',
    sending: false,
    templateId: '',
    rich: false,
    attachments: [],
  }
}

const sendQuickReply = async () => {
  if (!canQuickReply.value) {
    setFlash('You do not have permission to send replies.', 'warning', 2500)
    return
  }
  if (!quickReply.value.threadId) return
  if (quickReply.value.attachments.length && !canEditEmail.value) {
    setFlash('You do not have permission to manage email attachments.', 'warning', 2500)
    return
  }
  quickReply.value.sending = true
  try {
    const payload = {
      to_email: quickReply.value.to,
      subject: quickReply.value.subject,
      body_html: quickReply.value.body,
      status: 'draft',
      thread_id: quickReply.value.threadId,
      in_reply_to: quickReply.value.inReplyTo,
    }
    const { data } = await createMessage(payload)
    const messageId = data?.data?.id
    if (messageId) {
      if (quickReply.value.attachments.length) {
        await uploadAttachments(messageId, quickReply.value.attachments)
      }
      await sendMessage(messageId, payload)
    }
    closeQuickReply()
  } finally {
    quickReply.value.sending = false
    loadMessages()
  }
}

const openThread = (threadId) => {
  router.push(`/email/threads/${threadId}`)
}

const trashThread = async (threadId) => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to move emails to trash.', 'warning', 2500)
    return
  }

  loading.value = true
  try {
    const { data } = await fetchMessages('inbox', { q: query.value })
    const list = data?.data?.data || []
    const toTrash = list.filter((msg) => msg.thread_id === threadId)
    for (const msg of toTrash) {
      await trashMessage(msg.id)
    }
  } finally {
    loading.value = false
    loadMessages()
  }
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString()
}

const initials = (email) => {
  if (!email) return 'NA'
  const name = email.split('@')[0].replace(/[._-]/g, ' ')
  const parts = name.split(' ').filter(Boolean)
  const letters = parts.slice(0, 2).map((p) => p[0].toUpperCase())
  return letters.join('') || 'NA'
}

const avatarColor = (email) => {
  if (!email) return '#2563eb'
  let hash = 0
  for (let i = 0; i < email.length; i++) {
    hash = email.charCodeAt(i) + ((hash << 5) - hash)
  }
  const hue = Math.abs(hash) % 360
  return `hsl(${hue} 70% 40%)`
}

const avatarBg = (email) => {
  if (!email) return 'rgba(37, 99, 235, 0.12)'
  let hash = 0
  for (let i = 0; i < email.length; i++) {
    hash = email.charCodeAt(i) + ((hash << 5) - hash)
  }
  const hue = Math.abs(hash) % 360
  return `hsl(${hue} 70% 92%)`
}

const sync = async () => {
  if (!canReadEmail.value) return
  await syncInbox()
  loadMessages()
  loadSyncInfo()
}

const onContextMenu = (event, threadId) => {
  event.preventDefault()
  const thread = threads.value.find((t) => t.thread_id === threadId)
  contextMenu.value = {
    visible: true,
    x: event.clientX,
    y: event.clientY,
    threadId,
    unreadCount: thread?.unread_count || 0,
  }
}

const closeContextMenu = () => {
  contextMenu.value.visible = false
}

const toggleRead = async (thread) => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to update email status.', 'warning', 2500)
    return
  }
  const status = thread.unread_count ? 'read' : 'unread'
  await markThread(thread.thread_id, status)
  loadMessages()
}

const loadTemplates = async () => {
  const { data } = await fetchTemplates()
  templates.value = data?.data || []
}

const loadSyncInfo = async () => {
  if (!canReadEmail.value) return
  const { data } = await fetchEmailSettings()
  const item = data?.data
  if (!item) return
  syncInfo.value = {
    lastSync: item.imap_last_sync_at,
    error: item.imap_last_error,
  }
}

const applyQuickTemplate = () => {
  if (!quickReply.value.templateId) return
  const template = templates.value.find((t) => t.id === quickReply.value.templateId)
  if (!template) return
  quickReply.value.subject = template.subject || quickReply.value.subject
  quickReply.value.body = template.body_html || template.body_text || quickReply.value.body
  if (quickReply.value.rich && richRef.value) {
    richRef.value.innerHTML = quickReply.value.body
  }
}

const toggleEditor = () => {
  quickReply.value.rich = !quickReply.value.rich
  if (quickReply.value.rich && richRef.value) {
    richRef.value.innerHTML = quickReply.value.body
  }
  if (!quickReply.value.rich && richRef.value) {
    quickReply.value.body = richRef.value.innerHTML
  }
}

const onRichInput = () => {
  if (richRef.value) {
    quickReply.value.body = richRef.value.innerHTML
  }
}

const formatRich = (command) => {
  document.execCommand(command, false, null)
  onRichInput()
}

const onFileChange = (event) => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to manage email attachments.', 'warning', 2500)
    return
  }
  const files = Array.from(event.target.files || [])
  quickReply.value.attachments = files
}

const removeAttachment = (index) => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to manage email attachments.', 'warning', 2500)
    return
  }
  quickReply.value.attachments.splice(index, 1)
}

onMounted(() => {
  window.addEventListener('click', closeContextMenu)
  loadTemplates()
  loadSyncInfo()
  loadMessages()
})

onUnmounted(() => {
  window.removeEventListener('click', closeContextMenu)
})
</script>

<style scoped>
.pm-inbox-shell {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-inbox-toolbar {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}

.pm-inbox-search {
  min-width: min(460px, 100%);
  flex: 1 1 420px;
}

.pm-inbox-toolbar-actions {
  display: inline-flex;
  gap: 8px;
}

.pm-inbox-grid {
  border: 1px solid #dbe7ff;
  border-radius: 14px;
  overflow: hidden;
  display: grid;
  grid-template-columns: minmax(300px, 34%) 1fr;
  min-height: 420px;
}

.pm-inbox-list {
  border-right: 1px solid #dbe7ff;
  background: #f8fbff;
  max-height: 620px;
  overflow: auto;
}

.pm-inbox-row {
  width: 100%;
  text-align: left;
  border: 0;
  border-bottom: 1px solid #e6edf9;
  background: transparent;
  padding: 12px;
  display: grid;
  gap: 6px;
}

.pm-inbox-row:hover {
  background: #eef5ff;
}

.pm-inbox-row.active {
  background: #e2ecff;
}

.pm-inbox-row.unread .pm-inbox-subject strong {
  color: #0f172a;
}

.pm-inbox-row-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.pm-inbox-time {
  font-size: 0.74rem;
  color: #64748b;
  white-space: nowrap;
}

.pm-inbox-subject {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.pm-inbox-subject strong {
  display: block;
  font-size: 0.92rem;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pm-inbox-preview-line {
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.84rem;
}

.pm-inbox-preview-line span:last-child {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pm-inbox-row-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.pm-inbox-thread-id {
  color: #94a3b8;
  font-size: 0.74rem;
}

.pm-inbox-preview-panel {
  background: #ffffff;
  display: grid;
  align-content: start;
}

.pm-inbox-preview-content {
  padding: 18px;
  display: grid;
  gap: 12px;
}

.pm-inbox-preview-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 10px;
}

.pm-inbox-preview-title h5 {
  margin: 0;
  color: #0f172a;
  font-weight: 800;
}

.pm-inbox-preview-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 14px;
  font-size: 0.84rem;
  color: #334155;
}

.pm-inbox-preview-body {
  margin: 0;
  color: #475569;
  line-height: 1.6;
  min-height: 110px;
}

.pm-inbox-preview-panel .pm-thread-actions {
  opacity: 1;
  flex-wrap: wrap;
}

.pm-inbox-empty {
  color: #64748b;
  font-weight: 600;
  padding: 18px;
}

@media (max-width: 1100px) {
  .pm-inbox-grid {
    grid-template-columns: 1fr;
  }

  .pm-inbox-list {
    max-height: 320px;
    border-right: 0;
    border-bottom: 1px solid #dbe7ff;
  }
}

@media (max-width: 640px) {
  .pm-inbox-toolbar-actions {
    width: 100%;
  }

  .pm-inbox-toolbar-actions .btn {
    flex: 1;
  }
}
</style>
