<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Conversation</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Thread</div>
        </div>
        <div class="pm-page-actions">
          <RouterLink class="btn btn-outline-secondary" to="/email/inbox">Back to Inbox</RouterLink>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4">
      <div v-if="loading">Loading...</div>
      <div v-else-if="!canReadEmail">You do not have permission to view this thread.</div>
      <div v-else-if="!messages.length">No messages in this thread.</div>
      <div v-else class="pm-thread">
        <div v-for="message in messages" :key="message.id" class="pm-thread-item">
          <div class="pm-thread-head">
            <strong>{{ message.subject || '(No subject)' }}</strong>
            <span class="pm-muted small">{{ formatDate(message.created_at) }}</span>
          </div>
          <div class="pm-muted small mb-2">
            From: {{ message.from_email || 'Unknown' }} |
            To: {{ message.to_email || 'Unknown' }}
          </div>
          <div class="pm-card pm-ops-card p-3" style="background:#fff;">
            <div v-if="message.body_html" v-html="message.body_html"></div>
            <pre v-else class="mb-0">{{ message.body_text }}</pre>
          </div>
        </div>
      </div>
    </section>
  </EmailLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import EmailLayout from '../components/EmailLayout.vue'
import { fetchThread } from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'

const route = useRoute()
const messages = ref([])
const loading = ref(false)
const canReadEmail = computed(() => hasUserPermission(authState.user, 'email', 'read'))

const loadThread = async () => {
  if (!canReadEmail.value) {
    messages.value = []
    return
  }

  loading.value = true
  try {
    const { data } = await fetchThread(route.params.id)
    messages.value = data?.data || []
    setActiveEmailFolder(messages.value)
  } finally {
    loading.value = false
  }
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString()
}

onMounted(() => loadThread())

const setActiveEmailFolder = (items) => {
  const key = 'pm_sidebar_active_sub'
  if (!items || !items.length) {
    sessionStorage.setItem(key, 'email-inbox')
    window.dispatchEvent(new CustomEvent('pm-email-folder', { detail: 'email-inbox' }))
    return
  }
  const latest = items[items.length - 1]
  const folder = latest?.folder
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
