<template>
  <SuperAdminLayout>
    <main class="sa-announcements">
      <template v-if="mode === 'list'">
        <header class="sa-page-heading">
          <h1>ANNOUNCEMENTS LIST</h1>
          <button class="sa-primary" type="button" @click="openNew"><span>＋</span> New Announcement</button>
        </header>
        <section class="sa-filter-card">
          <strong>Filter By Date Range:</strong>
          <div class="sa-filters">
            <label>From Date<input v-model="filters.from" type="date" /></label>
            <label>To Date<input v-model="filters.to" type="date" /></label>
            <label>To (Customer):<select v-model="filters.customer"><option value="">All Customers</option><option value="all">All Customers</option><option value="specific">Specific Customer</option></select></label>
            <label>Subject:<input v-model="filters.subject" placeholder="Search subject..." /></label>
            <div class="sa-filter-actions"><button class="sa-primary" type="button" @click="applyFilters">Search</button><button class="sa-outline" type="button" @click="clearFilters">Clear</button></div>
          </div>
        </section>
        <section class="sa-table-card">
          <div class="sa-table-wrap">
            <table>
              <thead><tr><th>No.</th><th>Posted Date <span>⌄</span></th><th>To (Customer)</th><th>Admin Name</th><th>Ph. No.</th><th>Admin Position</th><th>Subject</th><th></th></tr></thead>
              <tbody>
                <tr v-if="loading"><td colspan="8" class="sa-empty">Loading announcements…</td></tr>
                <tr v-else-if="!visibleRows.length"><td colspan="8" class="sa-empty">No announcements match these filters.</td></tr>
                <tr v-for="item in pagedRows" :key="item.id">
                  <td class="sa-number">{{ item.announcement_no || `AN-${String(item.id).padStart(4, '0')}` }}</td>
                  <td>{{ formatDateTime(item.publish_at) }}</td>
                  <td>{{ audienceText(item) }}</td>
                  <td>{{ item.admin_name || item.created_by || 'Super Admin' }}</td>
                  <td>{{ item.phone || '—' }}</td>
                  <td>{{ item.position || 'System Admin' }}</td>
                  <td class="sa-subject">{{ item.title }}</td>
                  <td><button class="sa-view" type="button" @click="showDetails(item)">View Details</button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <footer class="sa-pagination"><strong>Total Announcements: {{ visibleRows.length }}</strong><span class="sa-pager"><button :disabled="page === 1" @click="page--">‹</button><b>{{ page }}</b><button :disabled="page >= totalPages" @click="page++">›</button> Page {{ page }} of {{ totalPages }}</span></footer>
        </section>
      </template>

      <template v-else>
        <header class="sa-form-heading"><div><h1>Announcements</h1><h2>New Announcement</h2></div><button class="sa-outline sa-list-button" type="button" @click="mode = 'list'">Announcements List</button></header>
        <form class="sa-announcement-form" @submit.prevent="sendAnnouncement">
          <section class="sa-form-section">
            <h3><i>1</i> Announcement Info</h3>
            <div class="sa-info-grid">
              <label>Announcement No.<input v-model="form.announcement_no" placeholder="Auto-generated" /></label>
              <label>Date<input v-model="form.occurred_at" type="datetime-local" required /></label>
              <label>To<select v-model="form.recipient_type"><option value="admins">Customer System Admins Only</option><option value="all">All Customers</option><option value="specific">Specific Customer</option></select></label>
            </div>
          </section>
          <section class="sa-form-section"><h3><i>2</i> Subject</h3><label>Subject<input v-model.trim="form.title" required /></label></section>
          <section class="sa-form-section"><h3><i>3</i> Announcement Details</h3><label>Details<textarea v-model.trim="form.body" rows="5" required></textarea></label></section>
          <section class="sa-form-section sa-delivery">
            <h3><i>4</i> Delivery Options</h3>
            <div class="sa-delivery-grid"><label>Recipient Type<select v-model="form.recipient_type"><option value="admins">Customers</option><option value="all">All Customers</option><option value="specific">Specific Customer</option></select></label><div><p class="sa-note"><b>ⓘ</b> This announcement is sent by Super Admin to Customer System Admin only.</p><div class="sa-checks"><label><input v-model="form.in_app" type="checkbox" /> In-App Notification</label><label><input v-model="form.email" type="checkbox" /> Email Notification</label></div></div></div>
          </section>
          <div class="sa-form-actions"><button class="sa-primary" :disabled="saving"><span>✈</span> {{ saving ? 'Sending…' : 'Send Announcement' }}</button><button class="sa-outline" type="button" @click="saveDraft"><span>▣</span> Save Draft</button><button class="sa-outline" type="button" @click="mode = 'list'"><span>×</span> Cancel</button></div>
        </form>
      </template>

      <div v-if="selected" class="sa-modal" @click.self="selected = null"><article><button class="sa-close" @click="selected = null">×</button><p class="sa-modal-kicker">{{ selected.announcement_no || 'ANNOUNCEMENT' }}</p><h2>{{ selected.title }}</h2><dl><div><dt>Posted</dt><dd>{{ formatDateTime(selected.publish_at) }}</dd></div><div><dt>Recipients</dt><dd>{{ audienceText(selected) }}</dd></div><div><dt>Posted by</dt><dd>{{ selected.created_by || 'Super Admin' }}</dd></div></dl><p class="sa-modal-body">{{ selected.body }}</p></article></div>
    </main>
  </SuperAdminLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import SuperAdminLayout from '../components/SuperAdminLayout.vue'
import client from '../api/client'

const mode = ref('list'), loading = ref(false), saving = ref(false), rows = ref([]), selected = ref(null), page = ref(1)
const filters = reactive({ from: '', to: '', customer: '', subject: '' })
const form = reactive({ announcement_no: '', occurred_at: toDateInput(new Date()), recipient_type: 'admins', title: '', body: '', in_app: true, email: true })
const perPage = 10
function toDateInput(date) { const offset = date.getTimezoneOffset() * 60000; return new Date(date - offset).toISOString().slice(0, 16) }
const visibleRows = computed(() => rows.value.filter(item => {
  const date = String(item.publish_at || '')
  return (!filters.from || date >= filters.from) && (!filters.to || date.slice(0, 10) <= filters.to) && (!filters.subject || String(item.title || '').toLowerCase().includes(filters.subject.toLowerCase())) && (!filters.customer || (filters.customer === 'all' ? item.audience === 'all' : item.audience !== 'all'))
}))
const totalPages = computed(() => Math.max(1, Math.ceil(visibleRows.value.length / perPage)))
const pagedRows = computed(() => visibleRows.value.slice((page.value - 1) * perPage, page.value * perPage))
function formatDateTime(value) { if (!value) return '—'; const date = new Date(value); return Number.isNaN(date) ? value : `${date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}\n${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}` }
function audienceText(item) { return item.audience === 'all' ? 'All Customers' : item.audience === 'admins' ? 'Customer System Admins Only' : 'Selected Customers' }
function applyFilters() { page.value = 1 }
function clearFilters() { Object.assign(filters, { from: '', to: '', customer: '', subject: '' }); page.value = 1 }
function openNew() { Object.assign(form, { announcement_no: '', occurred_at: toDateInput(new Date()), recipient_type: 'admins', title: '', body: '', in_app: true, email: true }); mode.value = 'form' }
function showDetails(item) { selected.value = item }
async function load() { loading.value = true; try { const { data } = await client.get('/super-admin/announcements'); rows.value = data?.data || [] } finally { loading.value = false } }
async function submit(status) { saving.value = true; try { await client.post('/super-admin/announcements', { announcement_no: form.announcement_no || null, occurred_at: form.occurred_at, title: form.title, body: form.body, audience: form.recipient_type === 'all' ? 'all' : 'admins', status, in_app: form.in_app, email: form.email }); await load(); mode.value = 'list' } finally { saving.value = false } }
function sendAnnouncement() { submit('published') }
function saveDraft() { if (!form.title || !form.body) return; submit('draft') }
onMounted(load)
</script>

<style scoped>
.sa-announcements{margin-block:-16px;color:#071c59;font-family:"Space Grotesk",Arial,sans-serif}.sa-page-heading,.sa-form-heading{display:flex;justify-content:space-between;align-items:center;margin:0 4px 14px}.sa-page-heading h1,.sa-form-heading h1{margin:0;font-size:2.55rem;line-height:1;font-weight:800;letter-spacing:-.05em}.sa-form-heading h2{font-size:1.75rem;margin:16px 0}.sa-list-button{align-self:flex-start}.sa-primary,.sa-outline,.sa-view{border:1px solid #062773;border-radius:5px;padding:12px 20px;background:#0a3c9c;color:#fff;font:700 1rem/1 "Space Grotesk",Arial,sans-serif;cursor:pointer}.sa-primary span{font-size:1.35em;margin-right:7px}.sa-outline,.sa-view{background:#fff;color:#071c59}.sa-filter-card,.sa-table-card,.sa-form-section{background:#fff;border:1px solid #d7dfed;border-radius:7px}.sa-filter-card{padding:16px 18px;margin-bottom:12px;color:#121b32}.sa-filters{display:grid;grid-template-columns:1.1fr 1.1fr 1.1fr 1.35fr auto;gap:30px;align-items:end;margin-top:8px}.sa-announcements label{display:grid;gap:5px;color:#141722;font-size:.83rem;font-weight:700}.sa-announcements input,.sa-announcements select,.sa-announcements textarea{box-sizing:border-box;width:100%;border:1px solid #bbc8dc;border-radius:5px;padding:10px 12px;background:#fff;color:#1d2434;font:inherit;font-size:1rem}.sa-filter-actions{display:flex;gap:13px}.sa-filter-actions button{height:53px}.sa-table-card{overflow:hidden}.sa-table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:1000px}th{background:linear-gradient(100deg,#07285f,#061c51);padding:13px 17px;color:#fff;text-align:left;font-size:.88rem;white-space:nowrap}th span{font-size:1.2rem}td{padding:13px 17px;border-bottom:1px solid #dfe5ef;color:#101931;white-space:pre-line;font-size:.9rem;line-height:1.45}.sa-number,.sa-subject{color:#063087;font-weight:800}.sa-subject{white-space:normal}.sa-view{padding:8px 14px;font-size:.8rem;white-space:nowrap}.sa-empty{text-align:center;padding:36px}.sa-pagination{display:flex;justify-content:space-between;align-items:center;padding:10px 18px}.sa-pager{display:flex;align-items:center;gap:12px}.sa-pager button{border:1px solid #c5cede;border-radius:4px;background:#fff;color:#082b75;font-size:1.3rem;line-height:1;padding:6px 12px;cursor:pointer}.sa-pager b{background:#06266c;color:#fff;padding:7px 13px;border-radius:4px}.sa-announcement-form{display:grid;gap:12px}.sa-form-section{padding:21px 26px}.sa-form-section h3{display:flex;align-items:center;gap:14px;margin:0 0 17px;font-size:1.38rem}.sa-form-section h3 i{display:grid;place-items:center;width:28px;height:28px;border:2px solid #09266c;border-radius:50%;font-size:1rem;font-style:normal}.sa-info-grid{display:grid;grid-template-columns:1fr .95fr 1fr;gap:42px}.sa-form-section textarea{resize:vertical;min-height:120px}.sa-delivery-grid{display:grid;grid-template-columns:32% 1fr;gap:34px;align-items:end}.sa-note{margin:0 0 20px;padding:15px 19px;border:1px solid #b9cdf5;border-radius:5px;background:#f0f6ff;color:#123070}.sa-note b{font-size:1.4rem;margin-right:10px;color:#075bd6}.sa-checks{display:flex;gap:38px}.sa-checks label{display:flex;align-items:center;gap:12px;color:#09236a;font-size:1rem}.sa-checks input{width:25px;height:25px;accent-color:#0754bf}.sa-form-actions{display:flex;justify-content:center;gap:28px;padding:6px 0;background:transparent!important}.sa-form-actions button{min-width:205px}.sa-modal{position:fixed;z-index:50;inset:0;display:grid;place-items:center;padding:20px;background:rgba(2,18,58,.48)}.sa-modal article{position:relative;width:min(590px,100%);border-radius:9px;padding:30px;background:#fff;box-shadow:0 24px 80px rgba(0,0,0,.26)}.sa-close{position:absolute;right:14px;top:10px;border:0;background:none;color:#082b74;font-size:2rem;cursor:pointer}.sa-modal-kicker{margin:0;color:#0a419c;font-weight:800}.sa-modal h2{margin:8px 0 20px}.sa-modal dl{display:flex;gap:30px;border-block:1px solid #e0e5ee;padding:14px 0}.sa-modal dl div{flex:1}.sa-modal dt{font-size:.72rem;color:#63708a}.sa-modal dd{margin:3px 0 0;font-weight:700}.sa-modal-body{line-height:1.6;white-space:pre-line}@media(max-width:1000px){.sa-filters{grid-template-columns:1fr 1fr}.sa-filter-actions{justify-content:flex-start}.sa-info-grid,.sa-delivery-grid{grid-template-columns:1fr;gap:15px}}@media(max-width:650px){.sa-page-heading{align-items:flex-start;gap:16px}.sa-page-heading h1,.sa-form-heading h1{font-size:1.75rem}.sa-page-heading,.sa-form-heading{flex-direction:column}.sa-list-button{align-self:auto}.sa-filters{grid-template-columns:1fr;gap:12px}.sa-form-section{padding:17px}.sa-form-actions{flex-direction:column;gap:10px}.sa-form-actions button{width:100%}.sa-checks{flex-direction:column;gap:14px}.sa-pagination{gap:12px;align-items:flex-start;flex-direction:column}}
</style>
