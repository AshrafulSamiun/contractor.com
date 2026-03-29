<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Email Overview</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Overview</div>
        </div>
        <div class="pm-page-actions">
          <RouterLink v-if="canCreateEmail" class="btn btn-primary" to="/email/new">Compose</RouterLink>
          <RouterLink class="btn btn-outline-secondary" to="/email/inbox">Open Inbox</RouterLink>
          <button class="btn btn-outline-secondary" type="button" @click="refreshAll">Refresh</button>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4 pm-overview-shell">
      <div v-if="!canReadEmail" class="pm-empty-state">
        You do not have permission to view email dashboard stats.
      </div>

      <template v-else>
        <div class="pm-overview-kpis">
          <article class="pm-kpi-card">
            <span>Total Mailbox</span>
            <strong>{{ totalMessages }}</strong>
            <small>All folders combined</small>
          </article>
          <article class="pm-kpi-card">
            <span>Inbox</span>
            <strong>{{ stats.inbox }}</strong>
            <small>{{ unreadRate }}% unread ratio</small>
          </article>
          <article class="pm-kpi-card">
            <span>Unread</span>
            <strong>{{ stats.unread }}</strong>
            <small>Needs immediate review</small>
          </article>
          <article class="pm-kpi-card">
            <span>Sent</span>
            <strong>{{ stats.sent }}</strong>
            <small>{{ deliveryRate }}% delivery completion</small>
          </article>
          <article class="pm-kpi-card">
            <span>Drafts</span>
            <strong>{{ stats.drafts }}</strong>
            <small>Pending authoring</small>
          </article>
          <article class="pm-kpi-card danger">
            <span>Trash / Spam</span>
            <strong>{{ stats.trash + stats.spam }}</strong>
            <small>Cleanup recommended</small>
          </article>
        </div>

        <div class="pm-overview-grid mt-3">
          <section class="pm-overview-card">
            <div class="pm-overview-card-head">
              <h5>Recent Inbox Activity</h5>
              <RouterLink class="btn btn-sm btn-outline-secondary" to="/email/inbox">View Inbox</RouterLink>
            </div>
            <div v-if="loadingFeed" class="pm-muted">Loading inbox activity...</div>
            <div v-else-if="!recentInbox.length" class="pm-muted">No recent inbox activity.</div>
            <div v-else class="pm-feed-list">
              <article v-for="item in recentInbox" :key="item.id" class="pm-feed-item">
                <div>
                  <strong>{{ item.subject || '(No subject)' }}</strong>
                  <p>{{ item.from_email || 'Unknown sender' }}</p>
                </div>
                <span>{{ formatDate(item.created_at) }}</span>
              </article>
            </div>
          </section>

          <section class="pm-overview-card">
            <div class="pm-overview-card-head">
              <h5>Draft Queue</h5>
              <RouterLink class="btn btn-sm btn-outline-secondary" to="/email/drafts">Open Drafts</RouterLink>
            </div>
            <div v-if="loadingFeed" class="pm-muted">Loading draft queue...</div>
            <div v-else-if="!recentDrafts.length" class="pm-muted">No drafts pending.</div>
            <div v-else class="pm-feed-list">
              <article v-for="item in recentDrafts" :key="item.id" class="pm-feed-item">
                <div>
                  <strong>{{ item.subject || '(No subject)' }}</strong>
                  <p>{{ item.to_email || 'Recipient not set' }}</p>
                </div>
                <span>{{ formatDate(item.updated_at || item.created_at) }}</span>
              </article>
            </div>
          </section>

          <section class="pm-overview-card pm-overview-health">
            <h5>Sync Health</h5>
            <div class="pm-health-pill" :class="healthState.kind">{{ healthState.label }}</div>
            <div class="pm-health-meta">Last sync: {{ lastSync ? formatDate(lastSync) : 'No sync yet' }}</div>
            <div v-if="lastError" class="pm-health-error">{{ lastError }}</div>
            <div class="pm-health-bars">
              <div class="pm-health-row">
                <span>Inbox share</span>
                <strong>{{ inboxShare }}%</strong>
              </div>
              <div class="pm-health-row">
                <span>Draft share</span>
                <strong>{{ draftShare }}%</strong>
              </div>
              <div class="pm-health-row">
                <span>Trash share</span>
                <strong>{{ trashShare }}%</strong>
              </div>
            </div>
            <div class="pm-overview-actions">
              <RouterLink class="btn btn-sm btn-outline-secondary" to="/email/templates">Templates</RouterLink>
              <RouterLink class="btn btn-sm btn-outline-secondary" to="/email/settings">Settings</RouterLink>
            </div>
          </section>
        </div>
      </template>
    </section>
  </EmailLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import EmailLayout from '../components/EmailLayout.vue'
import client from '../../api/client'
import { fetchMessages } from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'

const stats = ref({ inbox: 0, unread: 0, sent: 0, drafts: 0, trash: 0, spam: 0 })
const lastSync = ref(null)
const lastError = ref(null)
const recentInbox = ref([])
const recentDrafts = ref([])
const loadingFeed = ref(false)

const canReadEmail = computed(() => hasUserPermission(authState.user, 'email', 'read'))
const canCreateEmail = computed(() => hasUserPermission(authState.user, 'email', 'create'))

const totalMessages = computed(() =>
  Number(stats.value.inbox || 0) +
  Number(stats.value.sent || 0) +
  Number(stats.value.drafts || 0) +
  Number(stats.value.trash || 0) +
  Number(stats.value.spam || 0)
)

const unreadRate = computed(() => {
  const inbox = Number(stats.value.inbox || 0)
  if (!inbox) return 0
  return Math.round((Number(stats.value.unread || 0) / inbox) * 100)
})

const deliveryRate = computed(() => {
  const sent = Number(stats.value.sent || 0)
  const drafts = Number(stats.value.drafts || 0)
  const totalOutbound = sent + drafts
  if (!totalOutbound) return 0
  return Math.round((sent / totalOutbound) * 100)
})

const inboxShare = computed(() => {
  if (!totalMessages.value) return 0
  return Math.round((Number(stats.value.inbox || 0) / totalMessages.value) * 100)
})

const draftShare = computed(() => {
  if (!totalMessages.value) return 0
  return Math.round((Number(stats.value.drafts || 0) / totalMessages.value) * 100)
})

const trashShare = computed(() => {
  if (!totalMessages.value) return 0
  return Math.round(((Number(stats.value.trash || 0) + Number(stats.value.spam || 0)) / totalMessages.value) * 100)
})

const healthState = computed(() => {
  if (lastError.value) {
    return { label: 'Attention Required', kind: 'danger' }
  }
  if (!lastSync.value) {
    return { label: 'Not Synced', kind: 'warn' }
  }
  const minutes = (Date.now() - new Date(lastSync.value).getTime()) / 60000
  if (minutes > 180) {
    return { label: 'Sync Stale', kind: 'warn' }
  }
  return { label: 'Healthy', kind: 'good' }
})

const loadStats = async () => {
  if (!canReadEmail.value) {
    stats.value = { inbox: 0, unread: 0, sent: 0, drafts: 0, trash: 0, spam: 0 }
    lastSync.value = null
    lastError.value = null
    return
  }

  const { data } = await client.get('/email/stats')
  const payload = data?.data
  if (!payload) return
  stats.value = payload.stats || stats.value
  lastSync.value = payload.last_sync_at
  lastError.value = payload.last_error
}

const loadFeed = async () => {
  if (!canReadEmail.value) {
    recentInbox.value = []
    recentDrafts.value = []
    return
  }

  loadingFeed.value = true
  try {
    const [inboxRes, draftsRes] = await Promise.all([
      fetchMessages('inbox', { per_page: 5 }),
      fetchMessages('drafts', { per_page: 5 }),
    ])

    recentInbox.value = inboxRes?.data?.data?.data || []
    recentDrafts.value = draftsRes?.data?.data?.data || []
  } finally {
    loadingFeed.value = false
  }
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString()
}

const refreshAll = async () => {
  await Promise.all([loadStats(), loadFeed()])
}

onMounted(() => {
  refreshAll()
})
</script>

<style scoped>
.pm-overview-shell {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-overview-kpis {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.pm-kpi-card {
  border: 1px solid #dbe7ff;
  border-radius: 12px;
  background:
    radial-gradient(circle at 86% -30%, rgba(59, 130, 246, 0.14), transparent 60%),
    #ffffff;
  padding: 12px;
  display: grid;
  gap: 4px;
}

.pm-kpi-card span {
  color: #64748b;
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-weight: 700;
}

.pm-kpi-card strong {
  color: #0f172a;
  font-size: 1.4rem;
  line-height: 1;
}

.pm-kpi-card small {
  color: #64748b;
  font-size: 0.76rem;
}

.pm-kpi-card.danger {
  border-color: #fecaca;
  background:
    radial-gradient(circle at 86% -30%, rgba(239, 68, 68, 0.14), transparent 60%),
    #ffffff;
}

.pm-overview-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.pm-overview-card {
  border: 1px solid #dbe7ff;
  border-radius: 12px;
  background: #ffffff;
  padding: 12px;
  min-height: 250px;
}

.pm-overview-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 10px;
}

.pm-overview-card h5 {
  margin: 0;
  color: #0f172a;
  font-weight: 800;
}

.pm-feed-list {
  display: grid;
  gap: 8px;
}

.pm-feed-item {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.pm-feed-item strong {
  font-size: 0.9rem;
  color: #1e293b;
}

.pm-feed-item p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 0.82rem;
}

.pm-feed-item span {
  color: #94a3b8;
  font-size: 0.75rem;
  white-space: nowrap;
}

.pm-overview-health {
  background:
    radial-gradient(circle at 90% -20%, rgba(14, 116, 144, 0.16), transparent 56%),
    #ffffff;
}

.pm-health-pill {
  display: inline-flex;
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.78rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.pm-health-pill.good {
  color: #166534;
  background: #dcfce7;
}

.pm-health-pill.warn {
  color: #92400e;
  background: #fef3c7;
}

.pm-health-pill.danger {
  color: #991b1b;
  background: #fee2e2;
}

.pm-health-meta {
  color: #334155;
  font-size: 0.84rem;
}

.pm-health-error {
  color: #b91c1c;
  font-size: 0.8rem;
  margin-top: 6px;
}

.pm-health-bars {
  margin-top: 12px;
  border: 1px solid #dbe7ff;
  border-radius: 10px;
  padding: 10px;
  display: grid;
  gap: 8px;
}

.pm-health-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  font-size: 0.84rem;
}

.pm-health-row span {
  color: #64748b;
}

.pm-health-row strong {
  color: #0f172a;
}

.pm-overview-actions {
  margin-top: 12px;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

@media (max-width: 1100px) {
  .pm-overview-kpis,
  .pm-overview-grid {
    grid-template-columns: 1fr;
  }

  .pm-overview-card {
    min-height: auto;
  }
}
</style>
