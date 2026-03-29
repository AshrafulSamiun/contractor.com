<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Compose</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Compose</div>
        </div>
        <div class="pm-page-actions">
          <button
            class="btn btn-outline-secondary"
            type="button"
            :disabled="ui.loading || ui.saving || !canCreateEmail"
            @click="saveDraft"
          >
            {{ ui.saving ? 'Saving...' : 'Save Draft' }}
          </button>
          <button
            class="btn btn-primary"
            type="button"
            :disabled="ui.loading || ui.sending || !canSendEmail"
            @click="sendNow"
          >
            {{ ui.sending ? 'Sending...' : 'Send Email' }}
          </button>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4 pm-compose-shell">
      <div v-if="!canCreateEmail" class="pm-empty-state">
        You do not have permission to compose emails.
      </div>

      <div v-else class="pm-compose-grid">
        <article class="pm-compose-main">
          <section class="pm-compose-card">
            <div class="pm-compose-card-head">
              <h5>Message Details</h5>
              <span class="pm-muted small">All fields marked with * are recommended for delivery.</span>
            </div>

            <div class="row g-3">
              <div class="col-md-12">
                <label class="pm-field-label">To Email *</label>
                <input v-model="form.to_email" class="form-control" type="email" placeholder="recipient@company.com" />
                <div v-if="errors.to_email" class="pm-form-error">{{ errors.to_email }}</div>
              </div>

              <div class="col-md-6">
                <label class="pm-field-label">CC</label>
                <input v-model="form.cc" class="form-control" placeholder="cc1@company.com, cc2@company.com" />
                <div v-if="errors.cc" class="pm-form-error">{{ errors.cc }}</div>
              </div>

              <div class="col-md-6">
                <label class="pm-field-label">BCC</label>
                <input v-model="form.bcc" class="form-control" placeholder="bcc@company.com" />
                <div v-if="errors.bcc" class="pm-form-error">{{ errors.bcc }}</div>
              </div>

              <div class="col-md-6">
                <label class="pm-field-label">Reply-To</label>
                <input v-model="form.reply_to" class="form-control" type="email" placeholder="reply@company.com" />
                <div v-if="errors.reply_to" class="pm-form-error">{{ errors.reply_to }}</div>
              </div>

              <div class="col-md-6">
                <label class="pm-field-label">Template</label>
                <select class="form-control" v-model="selectedTemplateId" :disabled="loadingTemplates" @change="applyTemplate">
                  <option value="">Select template</option>
                  <option v-for="template in templates" :key="template.id" :value="template.id">
                    {{ template.name }}
                  </option>
                </select>
              </div>

              <div class="col-md-12">
                <label class="pm-field-label">Subject *</label>
                <input v-model="form.subject" class="form-control" placeholder="Enter email subject" />
                <div v-if="errors.subject" class="pm-form-error">{{ errors.subject }}</div>
              </div>

              <div class="col-md-12">
                <div class="pm-compose-editor-head">
                  <label class="pm-field-label mb-0">Body *</label>
                  <button class="btn btn-sm btn-outline-secondary" type="button" @click="toggleEditor">
                    {{ ui.rich ? 'Use Plain Editor' : 'Use Rich Editor' }}
                  </button>
                </div>

                <div v-if="ui.rich" class="pm-rich-toolbar">
                  <button class="pm-rich-btn" type="button" @click="formatRich('bold')"><strong>B</strong></button>
                  <button class="pm-rich-btn" type="button" @click="formatRich('italic')"><em>I</em></button>
                  <button class="pm-rich-btn" type="button" @click="formatRich('underline')"><u>U</u></button>
                  <button class="pm-rich-btn" type="button" @click="formatRich('insertUnorderedList')">- List</button>
                  <button class="pm-rich-btn" type="button" @click="formatRich('insertOrderedList')">1. List</button>
                </div>

                <textarea
                  v-if="!ui.rich"
                  class="form-control"
                  rows="8"
                  v-model="form.body_text"
                  placeholder="Write your message..."
                ></textarea>
                <div
                  v-else
                  class="pm-rich-editor"
                  contenteditable="true"
                  :placeholder="'Write your message...'"
                  ref="richRef"
                  @input="onRichInput"
                ></div>
                <div v-if="errors.body_html || errors.body_text" class="pm-form-error">
                  {{ errors.body_html || errors.body_text }}
                </div>
              </div>

              <div class="col-md-12">
                <label class="pm-field-label">Attachments</label>
                <input
                  class="form-control"
                  type="file"
                  multiple
                  :disabled="!canEditEmail || ui.loading || ui.saving || ui.sending"
                  @change="onFileChange"
                />

                <div v-if="pendingAttachments.length" class="pm-attach-zone mt-2">
                  <div class="pm-attach-caption">Queued for upload</div>
                  <div class="pm-attach-list">
                    <div v-for="(file, index) in pendingAttachments" :key="`${file.name}-${index}`" class="pm-attach-item">
                      <span>{{ file.name }} ({{ formatBytes(file.size) }})</span>
                      <button
                        class="pm-attach-remove"
                        type="button"
                        :disabled="!canEditEmail"
                        @click="removePendingAttachment(index)"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>

                <div v-if="draftAttachments.length" class="pm-attach-zone mt-2">
                  <div class="pm-attach-caption">Saved attachments</div>
                  <div class="pm-attach-list">
                    <div v-for="file in draftAttachments" :key="file.id" class="pm-attach-item">
                      <span>{{ file.original_name }} ({{ formatBytes(file.size) }})</span>
                      <button
                        class="pm-attach-remove"
                        type="button"
                        :disabled="!canDeleteEmail || ui.saving || ui.sending"
                        @click="removeSavedAttachment(file.id)"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </article>

        <aside class="pm-compose-side">
          <section class="pm-compose-insight">
            <h6>Delivery Checklist</h6>
            <ul>
              <li :class="{ done: hasRecipient }">Recipient email added</li>
              <li :class="{ done: hasSubject }">Subject added</li>
              <li :class="{ done: hasBody }">Message body added</li>
              <li :class="{ done: attachmentReady }">Attachment status ready</li>
            </ul>
          </section>

          <section class="pm-compose-insight">
            <h6>Compose Summary</h6>
            <div class="pm-compose-stat"><span>Mode</span><strong>{{ ui.rich ? 'Rich Text' : 'Plain Text' }}</strong></div>
            <div class="pm-compose-stat"><span>Total recipients</span><strong>{{ totalRecipients }}</strong></div>
            <div class="pm-compose-stat"><span>Queued files</span><strong>{{ pendingAttachments.length }}</strong></div>
            <div class="pm-compose-stat"><span>Saved files</span><strong>{{ draftAttachments.length }}</strong></div>
            <div class="pm-compose-stat"><span>Message length</span><strong>{{ bodyLength }} chars</strong></div>
          </section>

          <section class="pm-compose-insight" v-if="replyContext">
            <h6>Reply Context</h6>
            <div class="pm-muted small">Replying to: <strong>{{ replyContext.from_email || 'Unknown sender' }}</strong></div>
            <div class="pm-muted small mt-1">Original subject: {{ replyContext.subject || '(No subject)' }}</div>
          </section>

          <section class="pm-compose-insight">
            <h6>Status</h6>
            <div class="pm-muted small">Draft ID: {{ currentDraftId || 'Not created yet' }}</div>
            <div class="pm-muted small" v-if="ui.lastSavedAt">Last saved: {{ formatDate(ui.lastSavedAt) }}</div>
            <div class="pm-muted small" v-else>Not saved yet.</div>
          </section>

          <section class="pm-compose-insight pm-compose-actions">
            <button
              class="btn btn-outline-secondary"
              type="button"
              :disabled="ui.loading || ui.saving || !canCreateEmail"
              @click="saveDraft"
            >
              {{ ui.saving ? 'Saving...' : 'Save Draft' }}
            </button>
            <button
              class="btn btn-primary"
              type="button"
              :disabled="ui.loading || ui.sending || !canSendEmail"
              @click="sendNow"
            >
              {{ ui.sending ? 'Sending...' : 'Send Email' }}
            </button>
            <button class="btn btn-outline-danger" type="button" :disabled="ui.loading || ui.saving || ui.sending" @click="resetCompose">
              Reset
            </button>
          </section>
        </aside>
      </div>
    </section>
  </EmailLayout>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import EmailLayout from '../components/EmailLayout.vue'
import {
  createMessage,
  updateMessage,
  sendMessage,
  getMessage,
  fetchTemplates,
  uploadAttachments,
  deleteAttachment,
  fetchEmailSettings,
} from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'
import { setFlash } from '../../store/flash'

const route = useRoute()
const router = useRouter()

const templates = ref([])
const loadingTemplates = ref(false)
const selectedTemplateId = ref('')
const currentDraftId = ref(null)
const draftAttachments = ref([])
const pendingAttachments = ref([])
const richRef = ref(null)
const replyContext = ref(null)
const defaultReplyTo = ref('')

const form = reactive({
  to_email: '',
  cc: '',
  bcc: '',
  reply_to: '',
  subject: '',
  body_html: '',
  body_text: '',
  thread_id: '',
  in_reply_to: '',
})

const ui = reactive({
  loading: false,
  saving: false,
  sending: false,
  rich: false,
  lastSavedAt: null,
})

const errors = reactive({})

const canCreateEmail = computed(() => hasUserPermission(authState.user, 'email', 'create'))
const canEditEmail = computed(() => hasUserPermission(authState.user, 'email', 'edit'))
const canDeleteEmail = computed(() => hasUserPermission(authState.user, 'email', 'delete'))
const canSendEmail = computed(() => hasUserPermission(authState.user, 'email', 'send'))

const hasRecipient = computed(() => !!form.to_email.trim())
const hasSubject = computed(() => !!form.subject.trim())
const hasBody = computed(() => bodyLength.value > 0)
const attachmentReady = computed(() => pendingAttachments.value.every((file) => file.size <= 5 * 1024 * 1024))
const totalRecipients = computed(() => {
  const ccCount = parseEmailList(form.cc).length
  const bccCount = parseEmailList(form.bcc).length
  return (form.to_email.trim() ? 1 : 0) + ccCount + bccCount
})
const bodyLength = computed(() => {
  const value = ui.rich ? htmlToText(form.body_html) : form.body_text
  return value.trim().length
})

const mapErrors = (input) => {
  Object.keys(errors).forEach((key) => delete errors[key])
  if (!input) return
  Object.entries(input).forEach(([key, value]) => {
    errors[key] = Array.isArray(value) ? value[0] : value
  })
}

const parseEmailList = (value) => {
  if (!value) return []
  return value
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
}

const htmlToText = (value) => {
  return String(value || '')
    .replace(/<br\s*\/?\s*>/gi, '\n')
    .replace(/<\/p>/gi, '\n')
    .replace(/<[^>]*>/g, ' ')
    .replace(/\u00a0/g, ' ')
    .replace(/\s+\n/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim()
}

const formatBytes = (bytes) => {
  if (!bytes && bytes !== 0) return '--'
  const units = ['B', 'KB', 'MB', 'GB']
  let value = bytes
  let index = 0
  while (value >= 1024 && index < units.length - 1) {
    value /= 1024
    index += 1
  }
  return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString()
}

const buildPayload = () => {
  const html = ui.rich ? (richRef.value?.innerHTML || form.body_html || '') : form.body_text
  const text = ui.rich ? htmlToText(html) : form.body_text

  form.body_html = ui.rich ? html : ''
  form.body_text = text || ''

  return {
    to_email: form.to_email.trim() || null,
    subject: form.subject.trim() || null,
    body_html: form.body_html || null,
    body_text: form.body_text || null,
    cc: parseEmailList(form.cc),
    bcc: parseEmailList(form.bcc),
    reply_to: form.reply_to.trim() || null,
    thread_id: form.thread_id || null,
    in_reply_to: form.in_reply_to || null,
    status: 'draft',
  }
}

const resetCompose = () => {
  currentDraftId.value = null
  replyContext.value = null
  selectedTemplateId.value = ''
  draftAttachments.value = []
  pendingAttachments.value = []
  ui.lastSavedAt = null
  ui.rich = false
  mapErrors(null)

  form.to_email = ''
  form.cc = ''
  form.bcc = ''
  form.reply_to = defaultReplyTo.value || ''
  form.subject = ''
  form.body_html = ''
  form.body_text = ''
  form.thread_id = ''
  form.in_reply_to = ''

  if (richRef.value) {
    richRef.value.innerHTML = ''
  }
}

const hydrateRichEditor = async () => {
  await nextTick()
  if (!ui.rich || !richRef.value) return
  richRef.value.innerHTML = form.body_html || form.body_text.replace(/\n/g, '<br>')
}

const loadTemplates = async () => {
  loadingTemplates.value = true
  try {
    const { data } = await fetchTemplates()
    templates.value = data?.data || []
  } finally {
    loadingTemplates.value = false
  }
}

const loadDefaults = async () => {
  try {
    const { data } = await fetchEmailSettings()
    defaultReplyTo.value = data?.data?.reply_to || data?.data?.from_email || ''
    if (!form.reply_to) {
      form.reply_to = defaultReplyTo.value
    }
  } catch {
    defaultReplyTo.value = ''
  }
}

const loadDraft = async (draftId) => {
  const { data } = await getMessage(draftId)
  const draft = data?.data
  if (!draft) return

  currentDraftId.value = draft.id
  form.to_email = draft.to_email || ''
  form.cc = (draft.cc || []).join(', ')
  form.bcc = (draft.bcc || []).join(', ')
  form.reply_to = draft.reply_to || defaultReplyTo.value || ''
  form.subject = draft.subject || ''
  form.body_html = draft.body_html || ''
  form.body_text = draft.body_text || htmlToText(draft.body_html || '')
  form.thread_id = draft.thread_id || ''
  form.in_reply_to = draft.in_reply_to || ''
  draftAttachments.value = draft.attachments || []

  ui.rich = /<\/?[a-z][\s\S]*>/i.test(form.body_html)
  await hydrateRichEditor()
}

const loadReply = async (messageId) => {
  const { data } = await getMessage(messageId)
  const message = data?.data
  if (!message) return

  replyContext.value = {
    from_email: message.from_email,
    subject: message.subject,
  }

  form.to_email = message.from_email || ''
  form.subject = message.subject?.startsWith('Re:') ? message.subject : `Re: ${message.subject || '(No subject)'}`
  form.thread_id = message.thread_id || ''
  form.in_reply_to = message.message_id || ''
  form.reply_to = defaultReplyTo.value || form.reply_to
  form.body_text = `\n\n---- Original Message ----\nFrom: ${message.from_email || 'Unknown'}\nSubject: ${message.subject || '(No subject)'}\n${htmlToText(message.body_html || message.body_text || '')}`
  form.body_html = ''
  ui.rich = false
}

const initializeFromRoute = async () => {
  if (!canCreateEmail.value) return

  ui.loading = true
  try {
    resetCompose()

    const draftId = Number(route.query.draft || 0)
    const replyId = Number(route.query.reply || 0)

    if (draftId > 0) {
      await loadDraft(draftId)
      return
    }

    if (replyId > 0) {
      await loadReply(replyId)
    }
  } finally {
    ui.loading = false
  }
}

const flushPendingAttachments = async () => {
  if (!pendingAttachments.value.length) return
  if (!canEditEmail.value) {
    setFlash('You do not have permission to manage email attachments.', 'warning', 2500)
    return
  }
  if (!currentDraftId.value) return

  await uploadAttachments(currentDraftId.value, pendingAttachments.value)
  pendingAttachments.value = []
  const { data } = await getMessage(currentDraftId.value)
  draftAttachments.value = data?.data?.attachments || []
}

const upsertDraft = async (showFlashMessage = true) => {
  if (!canCreateEmail.value) {
    setFlash('You do not have permission to create drafts.', 'warning', 2500)
    return false
  }

  mapErrors(null)
  const payload = buildPayload()

  try {
    if (currentDraftId.value) {
      if (!canEditEmail.value) {
        setFlash('You do not have permission to edit drafts.', 'warning', 2500)
        return false
      }
      await updateMessage(currentDraftId.value, payload)
    } else {
      const { data } = await createMessage(payload)
      const item = data?.data
      currentDraftId.value = item?.id || null
      if (item?.thread_id) {
        form.thread_id = item.thread_id
      }
    }

    await flushPendingAttachments()
    ui.lastSavedAt = new Date().toISOString()

    if (showFlashMessage) {
      setFlash('Draft saved successfully.', 'success', 1800)
    }

    return true
  } catch (error) {
    if (error?.response?.status === 422) {
      mapErrors(error.response.data?.errors)
      if (error.response.data?.message) {
        setFlash(error.response.data.message, 'warning', 2500)
      }
    }
    return false
  }
}

const saveDraft = async () => {
  ui.saving = true
  try {
    await upsertDraft(true)
  } finally {
    ui.saving = false
  }
}

const sendNow = async () => {
  if (!canSendEmail.value) {
    setFlash('You do not have permission to send emails.', 'warning', 2500)
    return
  }

  ui.sending = true
  mapErrors(null)
  try {
    const saved = await upsertDraft(false)
    if (!saved || !currentDraftId.value) return

    const payload = buildPayload()
    delete payload.status

    await sendMessage(currentDraftId.value, payload)
    setFlash('Email sent successfully.', 'success', 2200)
    router.push('/email/sent')
  } catch (error) {
    if (error?.response?.status === 422) {
      mapErrors(error.response.data?.errors)
      if (error.response.data?.message) {
        setFlash(error.response.data.message, 'warning', 2500)
      }
    } else {
      setFlash('Failed to send email. Please try again.', 'danger', 3000)
    }
  } finally {
    ui.sending = false
  }
}

const applyTemplate = () => {
  if (!selectedTemplateId.value) return
  const template = templates.value.find((item) => String(item.id) === String(selectedTemplateId.value))
  if (!template) return

  if (template.subject) {
    form.subject = template.subject
  }

  const body = template.body_html || template.body_text || ''
  if (ui.rich) {
    form.body_html = body
    hydrateRichEditor()
  } else {
    form.body_text = template.body_text || htmlToText(body)
  }
}

const toggleEditor = () => {
  if (ui.rich) {
    const html = richRef.value?.innerHTML || form.body_html || ''
    form.body_text = htmlToText(html)
    form.body_html = html
    ui.rich = false
    return
  }

  form.body_html = form.body_html || form.body_text.replace(/\n/g, '<br>')
  ui.rich = true
  hydrateRichEditor()
}

const onRichInput = () => {
  form.body_html = richRef.value?.innerHTML || ''
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
  pendingAttachments.value = [...pendingAttachments.value, ...files]
  event.target.value = ''
}

const removePendingAttachment = (index) => {
  if (!canEditEmail.value) return
  pendingAttachments.value.splice(index, 1)
}

const removeSavedAttachment = async (attachmentId) => {
  if (!canDeleteEmail.value || !currentDraftId.value) {
    setFlash('You do not have permission to delete attachments.', 'warning', 2500)
    return
  }

  await deleteAttachment(currentDraftId.value, attachmentId)
  draftAttachments.value = draftAttachments.value.filter((file) => file.id !== attachmentId)
}

watch(
  () => route.fullPath,
  () => {
    initializeFromRoute()
  }
)

onMounted(async () => {
  await Promise.all([loadDefaults(), loadTemplates()])
  await initializeFromRoute()
})
</script>

<style scoped>
.pm-compose-shell {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-compose-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
  gap: 14px;
}

.pm-compose-main {
  min-width: 0;
}

.pm-compose-card {
  border: 1px solid #dbe7ff;
  border-radius: 14px;
  background: #ffffff;
  padding: 16px;
  display: grid;
  gap: 12px;
}

.pm-compose-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 10px;
}

.pm-compose-card-head h5 {
  margin: 0;
  color: #0f172a;
  font-weight: 800;
}

.pm-compose-editor-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
  gap: 8px;
}

.pm-compose-side {
  display: grid;
  gap: 10px;
  align-content: start;
}

.pm-compose-insight {
  border: 1px solid #dbe7ff;
  border-radius: 12px;
  background: #ffffff;
  padding: 12px;
}

.pm-compose-insight h6 {
  margin: 0 0 8px;
  color: #0f172a;
  font-weight: 800;
}

.pm-compose-insight ul {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 6px;
}

.pm-compose-insight li {
  font-size: 0.86rem;
  color: #64748b;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  padding: 6px 8px;
}

.pm-compose-insight li.done {
  color: #166534;
  border-color: #bbf7d0;
  background: #f0fdf4;
}

.pm-compose-stat {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 0;
  border-bottom: 1px dashed #dbe7ff;
  font-size: 0.85rem;
}

.pm-compose-stat:last-child {
  border-bottom: 0;
}

.pm-compose-stat span {
  color: #64748b;
}

.pm-compose-stat strong {
  color: #0f172a;
}

.pm-compose-actions {
  display: grid;
  gap: 8px;
}

.pm-attach-zone {
  border: 1px solid #dbe7ff;
  border-radius: 12px;
  background: #f8fbff;
  padding: 10px;
}

.pm-attach-caption {
  font-size: 0.75rem;
  font-weight: 700;
  color: #1d4ed8;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.pm-rich-editor {
  min-height: 170px;
}

@media (max-width: 1100px) {
  .pm-compose-grid {
    grid-template-columns: 1fr;
  }
}
</style>
