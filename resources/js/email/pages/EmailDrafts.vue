<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Drafts</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Drafts</div>
        </div>
        <div class="pm-page-actions">
          <RouterLink v-if="canCreateEmail" class="btn btn-primary" to="/email/new">New Email</RouterLink>
          <button class="btn btn-outline-secondary" type="button" @click="loadMessages">Refresh</button>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4 pm-draft-shell">
      <div class="pm-mail-toolbar">
        <div class="pm-mail-search">
          <label class="pm-field-label">Search</label>
          <input v-model="query" class="form-control" placeholder="Search draft subject or text..." />
        </div>
        <div class="pm-mail-toolbar-actions">
          <button class="btn btn-outline-secondary" type="button" @click="loadMessages">Filter</button>
          <button class="btn btn-outline-secondary" type="button" @click="clearSearch">Clear</button>
        </div>
      </div>

      <div class="pm-mail-split">
        <aside class="pm-mail-list">
          <div v-if="loading" class="pm-mail-empty">Loading drafts...</div>
          <div v-else-if="!messages.length" class="pm-mail-empty">No drafts found.</div>
          <button
            v-else
            v-for="message in messages"
            :key="message.id"
            class="pm-mail-row"
            :class="{ active: selectedMessageId === message.id }"
            @click="selectMessage(message)"
          >
            <div class="pm-mail-row-head">
              <strong>{{ message.to_email || 'No recipient yet' }}</strong>
              <span>{{ formatDate(message.updated_at || message.created_at) }}</span>
            </div>
            <div class="pm-mail-row-subject">{{ message.subject || '(No subject)' }}</div>
            <div class="pm-mail-row-preview">{{ messagePreview(message) }}</div>
          </button>
        </aside>

        <article class="pm-mail-preview">
          <div v-if="!selectedMessage" class="pm-mail-empty">
            Select a draft to preview details.
          </div>
          <div v-else class="pm-mail-preview-content">
            <div class="pm-mail-preview-head">
              <h5>{{ selectedMessage.subject || '(No subject)' }}</h5>
              <span class="pm-mail-status-pill">Draft</span>
            </div>
            <div class="pm-mail-preview-meta">
              <span><strong>To:</strong> {{ selectedMessage.to_email || 'Not set' }}</span>
              <span><strong>Updated:</strong> {{ formatDate(selectedMessage.updated_at || selectedMessage.created_at) }}</span>
              <span><strong>Draft ID:</strong> #{{ selectedMessage.id }}</span>
            </div>
            <p class="pm-mail-preview-body">{{ messagePreview(selectedMessage) }}</p>

            <div class="pm-mail-preview-actions">
              <button
                v-if="canEditEmail"
                class="btn btn-sm btn-primary"
                type="button"
                @click="editDraft(selectedMessage.id)"
              >
                Edit Draft
              </button>
              <button class="btn btn-sm btn-outline-secondary" type="button" @click="openDetail(selectedMessage.id)">
                Open Details
              </button>
              <button
                v-if="canEditEmail"
                class="btn btn-sm btn-outline-danger"
                type="button"
                @click="trash(selectedMessage.id)"
              >
                Move to Trash
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>
  </EmailLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import EmailLayout from '../components/EmailLayout.vue'
import { fetchMessages, trashMessage } from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'
import { setFlash } from '../../store/flash'

const router = useRouter()
const messages = ref([])
const loading = ref(false)
const query = ref('')
const selectedMessageId = ref(null)

const canReadEmail = computed(() => hasUserPermission(authState.user, 'email', 'read'))
const canCreateEmail = computed(() => hasUserPermission(authState.user, 'email', 'create'))
const canEditEmail = computed(() => hasUserPermission(authState.user, 'email', 'edit'))
const selectedMessage = computed(() => messages.value.find((item) => item.id === selectedMessageId.value) || null)

const messagePreview = (message) => {
  if (!message) return '--'
  const text = String(message.body_text || '').trim()
  if (text) return text.slice(0, 220)
  const html = String(message.body_html || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
  return html ? html.slice(0, 220) : '--'
}

const loadMessages = async () => {
  if (!canReadEmail.value) {
    messages.value = []
    selectedMessageId.value = null
    return
  }

  loading.value = true
  try {
    const { data } = await fetchMessages('drafts', { q: query.value, per_page: 100 })
    messages.value = data?.data?.data || []

    if (!messages.value.length) {
      selectedMessageId.value = null
    } else if (!messages.value.some((item) => item.id === selectedMessageId.value)) {
      selectedMessageId.value = messages.value[0].id
    }
  } finally {
    loading.value = false
  }
}

const selectMessage = (message) => {
  if (!message?.id) return
  selectedMessageId.value = message.id
}

const clearSearch = () => {
  query.value = ''
  loadMessages()
}

const editDraft = (id) => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to edit drafts.', 'warning', 2500)
    return
  }

  router.push(`/email/new?draft=${id}`)
}

const openDetail = (id) => {
  router.push(`/email/messages/${id}`)
}

const trash = async (id) => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to move emails to trash.', 'warning', 2500)
    return
  }

  await trashMessage(id)
  if (selectedMessageId.value === id) {
    selectedMessageId.value = null
  }
  loadMessages()
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString()
}

onMounted(() => {
  loadMessages()
})
</script>

<style scoped>
.pm-draft-shell {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-mail-toolbar {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.pm-mail-search {
  min-width: min(460px, 100%);
  flex: 1 1 420px;
}

.pm-mail-toolbar-actions {
  display: inline-flex;
  gap: 8px;
}

.pm-mail-split {
  border: 1px solid #dbe7ff;
  border-radius: 14px;
  overflow: hidden;
  display: grid;
  grid-template-columns: minmax(300px, 36%) 1fr;
  min-height: 420px;
}

.pm-mail-list {
  border-right: 1px solid #dbe7ff;
  background: #f8fbff;
  max-height: 620px;
  overflow: auto;
}

.pm-mail-row {
  width: 100%;
  border: 0;
  border-bottom: 1px solid #e6edf9;
  text-align: left;
  background: transparent;
  padding: 12px;
  display: grid;
  gap: 6px;
}

.pm-mail-row:hover {
  background: #eef5ff;
}

.pm-mail-row.active {
  background: #e2ecff;
}

.pm-mail-row-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.pm-mail-row-head strong {
  color: #0f172a;
  font-size: 0.88rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pm-mail-row-head span {
  color: #64748b;
  font-size: 0.74rem;
  white-space: nowrap;
}

.pm-mail-row-subject {
  color: #1e293b;
  font-size: 0.93rem;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pm-mail-row-preview {
  color: #64748b;
  font-size: 0.84rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pm-mail-preview {
  background: #ffffff;
}

.pm-mail-preview-content {
  padding: 18px;
  display: grid;
  gap: 12px;
}

.pm-mail-preview-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 10px;
}

.pm-mail-preview-head h5 {
  margin: 0;
  color: #0f172a;
  font-weight: 800;
}

.pm-mail-status-pill {
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 0.74rem;
  font-weight: 700;
  background: #ede9fe;
  color: #6d28d9;
}

.pm-mail-preview-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 14px;
  font-size: 0.84rem;
  color: #334155;
}

.pm-mail-preview-body {
  margin: 0;
  color: #475569;
  line-height: 1.6;
  min-height: 110px;
  white-space: pre-wrap;
}

.pm-mail-preview-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pm-mail-empty {
  color: #64748b;
  font-weight: 600;
  padding: 18px;
}

@media (max-width: 1100px) {
  .pm-mail-split {
    grid-template-columns: 1fr;
  }

  .pm-mail-list {
    max-height: 320px;
    border-right: 0;
    border-bottom: 1px solid #dbe7ff;
  }
}

@media (max-width: 640px) {
  .pm-mail-toolbar-actions {
    width: 100%;
  }

  .pm-mail-toolbar-actions .btn {
    flex: 1;
  }
}
</style>

