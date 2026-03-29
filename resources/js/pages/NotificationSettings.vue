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
          <input v-model="searchQuery" class="form-control" placeholder="Search parcels, recipients, tracking..." />
          <button v-if="searchQuery" class="pm-clear-btn" type="button" aria-label="Clear search" @click="searchQuery = ''">
            &times;
          </button>
        </div>
        <div class="pm-topbar-actions">
          <button class="pm-icon-btn" type="button" aria-label="Notifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
            <span class="pm-topbar-badge"></span>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
          <div class="pm-topbar-avatar">JA</div>
          <span class="pm-topbar-name">{{ userName }}</span>
          <span class="pm-topbar-pill">Admin</span>
          <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="m7 10 5 5 5-5H7Z" />
            </svg>
          </button>
          <div v-if="userMenuOpen" class="pm-user-menu">
            <button class="pm-user-item" type="button" @click="goProfile">Profile</button>
            <button class="pm-user-item" type="button" @click="goAccount">Account</button>
            <button class="pm-user-item danger" type="button" @click="logout">Log out</button>
          </div>
        </div>
      </div>

      <div class="container pm-ops-page">
        <div class="pm-page-head">
          <div>
            <h2>Notification Settings</h2>
            <div class="pm-page-subtitle">Dashboard &gt; System Settings &gt; Notification Settings</div>
          </div>
        </div>

        <div class="pm-settings-shell">
          <SystemSettingsMenu />
          <div class="pm-settings-shell-content">
            <div class="pm-form-grid">
          <section class="pm-card pm-ops-card p-4" v-if="authState.user?.role === 'admin'">
            <h5 class="pm-form-title">Admin Defaults</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Use global defaults *</label>
                <select class="form-control" v-model="form.use_global_notifications">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
                <div class="pm-muted small mt-2">
                  When enabled, this account uses admin-wide defaults.
                </div>
              </div>
              <div class="col-md-6 d-flex align-items-end justify-content-end">
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  :disabled="saving"
                  @click="saveGlobalDefaults"
                >
                  Save as global defaults
                </button>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4" v-else>
            <h5 class="pm-form-title">Defaults</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Use global defaults *</label>
                <select class="form-control" v-model="form.use_global_notifications">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
                <div class="pm-muted small mt-2">
                  When enabled, your notifications follow admin defaults.
                </div>
              </div>
            </div>
          </section>

          <fieldset :disabled="form.use_global_notifications && authState.user?.role !== 'admin'">
            <section class="pm-card pm-ops-card p-4">
              <h5 class="pm-form-title">Events</h5>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="pm-field-label">Parcel arrival *</label>
                <select class="form-control" v-model="form.notify_parcel_arrival">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Pickup request *</label>
                <select class="form-control" v-model="form.notify_pickup_request">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Parcel return *</label>
                <select class="form-control" v-model="form.notify_parcel_return">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Delivery completed *</label>
                <select class="form-control" v-model="form.notify_delivery_completed">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Rejected parcel *</label>
                <select class="form-control" v-model="form.notify_rejected_parcel">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Lost / damaged *</label>
                <select class="form-control" v-model="form.notify_lost_damaged">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Expiry reminder *</label>
                <select class="form-control" v-model="form.notify_expiry_reminder">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4">
            <h5 class="pm-form-title">Channels</h5>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="pm-field-label">Email *</label>
                <select class="form-control" v-model="form.notify_channel_email">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">SMS *</label>
                <select class="form-control" v-model="form.notify_channel_sms">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">In-app *</label>
                <select class="form-control" v-model="form.notify_channel_in_app">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">WhatsApp *</label>
                <select class="form-control" v-model="form.notify_channel_whatsapp">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="pm-field-label">Push *</label>
                <select class="form-control" v-model="form.notify_channel_push">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4">
            <h5 class="pm-form-title">Quiet Hours</h5>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="pm-field-label">Enable *</label>
                <select class="form-control" v-model="form.quiet_hours_enabled">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
              </div>
              <div class="col-md-8 d-flex flex-wrap align-items-end justify-content-end gap-2">
                <div class="input-group" style="max-width: 260px;">
                  <select class="form-control" v-model="copyFrom">
                    <option disabled value="">Copy from day...</option>
                    <option v-for="day in quietDays" :key="`copy-${day}`" :value="day">{{ day }}</option>
                  </select>
                  <button class="btn btn-outline-secondary" type="button" :disabled="!form.quiet_hours_enabled || !copyFrom" @click="copyFromDay(copyFrom)">
                    Copy to all
                  </button>
                </div>
                <button class="btn btn-outline-secondary" type="button" :disabled="!form.quiet_hours_enabled" @click="applyToWeekdays">
                  Apply to weekdays
                </button>
                <button class="btn btn-outline-secondary" type="button" :disabled="!form.quiet_hours_enabled" @click="applyToWeekends">
                  Apply to weekends
                </button>
              </div>
              <div class="col-md-12">
                <div class="pm-muted small mb-2">Set different quiet hours per day.</div>
                <div class="row g-2">
                  <div
                    v-for="slot in form.quiet_hours_schedule"
                    :key="slot.day"
                    class="col-12 col-lg-6"
                  >
                    <div class="pm-toggle-row">
                      <div>
                        <div class="pm-field-label">{{ slot.day }}</div>
                        <div class="pm-muted small">
                          {{ slot.enabled ? 'Active' : 'Disabled' }}
                        </div>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <input
                          type="checkbox"
                          v-model="slot.enabled"
                          :disabled="!form.quiet_hours_enabled"
                        />
                        <input
                          class="form-control"
                          type="time"
                          v-model="slot.start"
                          :disabled="!form.quiet_hours_enabled || !slot.enabled"
                        />
                        <span class="pm-muted small">to</span>
                        <input
                          class="form-control"
                          type="time"
                          v-model="slot.end"
                          :disabled="!form.quiet_hours_enabled || !slot.enabled"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="errors.quiet_hours_schedule" class="col-md-12">
                <div class="pm-form-error">{{ errors.quiet_hours_schedule }}</div>
              </div>
              <div class="col-md-12">
                <div class="pm-muted small">
                  Quiet hours are applied in the user timezone settings.
                </div>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4">
            <h5 class="pm-form-title">Templates</h5>
            <div class="row g-3">
              <div class="col-md-12">
                <label class="pm-field-label">Email subject *</label>
                <input class="form-control" v-model="form.template_email_subject" placeholder="Parcel update" />
              </div>
              <div class="col-md-12">
                <label class="pm-field-label">Email body</label>
                <textarea class="form-control" rows="4" v-model="form.template_email_body" placeholder="Email template body..."></textarea>
              </div>
              <div class="col-md-12">
                <div class="pm-muted small">
                  You can use placeholders: {recipient_name}, {parcel_id}, {tracking_code}, {status}, {pickup_by},
                  {carrier_name}, {property_name}, {facility_name}, {location}, {received_at}, {delivered_at},
                  {picked_up_at}, {user_name}, {company_name}, {phone}.
                </div>
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">SMS body</label>
                <input class="form-control" v-model="form.template_sms_body" placeholder="SMS template (160 chars)" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">In-app body</label>
                <input class="form-control" v-model="form.template_in_app_body" placeholder="In-app template (160 chars)" />
              </div>
              <div class="col-md-6">
                <label class="pm-field-label">WhatsApp body</label>
                <input class="form-control" v-model="form.template_whatsapp_body" placeholder="WhatsApp template (300 chars)" />
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">Push title</label>
                <input class="form-control" v-model="form.template_push_title" placeholder="Push title" />
              </div>
              <div class="col-md-3">
                <label class="pm-field-label">Push body</label>
                <input class="form-control" v-model="form.template_push_body" placeholder="Push body (160 chars)" />
              </div>
              <div class="col-md-12">
                <div class="pm-info-card">
                  <div class="pm-info-title">
                    <span class="pm-info-icon">i</span>
                    Template preview
                  </div>
                  <div class="pm-muted small mb-2">Email</div>
                  <div class="mb-2"><strong>Subject:</strong> {{ renderTemplate(form.template_email_subject) }}</div>
                  <div class="mb-3"><strong>Body:</strong> {{ renderTemplate(form.template_email_body) }}</div>
                  <div class="pm-muted small mb-2">SMS</div>
                  <div class="mb-3">{{ renderTemplate(form.template_sms_body) }}</div>
                  <div class="pm-muted small mb-2">In-app</div>
                  <div class="mb-3">{{ renderTemplate(form.template_in_app_body) }}</div>
                  <div class="pm-muted small mb-2">WhatsApp</div>
                  <div class="mb-3">{{ renderTemplate(form.template_whatsapp_body) }}</div>
                  <div class="pm-muted small mb-2">Push</div>
                  <div class="mb-1"><strong>Title:</strong> {{ renderTemplate(form.template_push_title) }}</div>
                  <div>{{ renderTemplate(form.template_push_body) }}</div>
                </div>
              </div>
            </div>
          </section>

          <section class="pm-card pm-ops-card p-4">
            <div class="pm-form-actions pm-form-actions-right">
              <button class="btn btn-outline-secondary" type="button" :disabled="saving" @click="resetToSaved">
                Reset
              </button>
              <button class="btn btn-primary" type="button" :disabled="saving" @click="saveSettings">
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </section>
          </fieldset>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import SystemSettingsMenu from '../components/SystemSettingsMenu.vue'
import client from '../api/client'
import { authState } from '../store/auth'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const userName = ref('User')

const saving = ref(false)
const errors = reactive({})
const savedState = ref(null)
const copyFrom = ref('')

const sampleContext = {
  recipient_name: 'John Doe',
  parcel_id: 'PKG-1029',
  tracking_code: 'TRK-8841',
  status: 'pending',
  pickup_by: '2026-02-10',
  carrier_name: 'DHL Express',
  property_name: 'Riverside Complex',
  facility_name: 'Riverside Complex',
  location: 'Front Desk',
  received_at: '2026-02-04 10:15',
  delivered_at: '2026-02-04 14:20',
  picked_up_at: '2026-02-05 11:00',
  user_name: 'Admin User',
  company_name: 'DropDesk',
  phone: '+1 (555) 123-4567',
}

const quietDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const defaultSchedule = () =>
  quietDays.map((day) => ({
    day,
    enabled: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(day),
    start: '22:00',
    end: '07:00',
  }))

const form = reactive({
  notify_parcel_arrival: true,
  notify_pickup_request: true,
  notify_parcel_return: true,
  notify_channel_email: true,
  notify_channel_sms: false,
  notify_channel_in_app: true,
  notify_delivery_completed: true,
  notify_rejected_parcel: true,
  notify_lost_damaged: true,
  notify_expiry_reminder: true,
  notify_channel_whatsapp: false,
  notify_channel_push: true,
  use_global_notifications: true,
  quiet_hours_enabled: false,
  quiet_hours_start: '',
  quiet_hours_end: '',
  quiet_hours_days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
  quiet_hours_schedule: defaultSchedule(),
  template_email_subject: 'Parcel update',
  template_email_body: '',
  template_sms_body: '',
  template_in_app_body: '',
  template_whatsapp_body: '',
  template_push_title: '',
  template_push_body: '',
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

const goProfile = () => {
  userMenuOpen.value = false
  router.push('/account/profile')
}

const goAccount = () => {
  userMenuOpen.value = false
  router.push('/account')
}

const logout = () => {
  userMenuOpen.value = false
  router.push('/login')
}

const handleOutsideClick = (event) => {
  if (!userMenuRef.value) return
  if (!userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

const handleEsc = (event) => {
  if (event.key === 'Escape') {
    userMenuOpen.value = false
  }
}

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

const mapErrors = (errs) => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!errs) return
  Object.entries(errs).forEach(([key, value]) => {
    errors[key] = Array.isArray(value) ? value[0] : value
  })
}

const validateSchedule = () => {
  if (!form.quiet_hours_enabled) return true
  const invalid = form.quiet_hours_schedule.find((slot) => {
    if (!slot.enabled) return false
    if (!slot.start || !slot.end) return true
    return slot.start === slot.end
  })
  if (invalid) {
    errors.quiet_hours_schedule = 'Start and end time cannot be the same for enabled days.'
    return false
  }
  return true
}

const renderTemplate = (text) => {
  if (!text) return ''
  return text.replace(/\{(\w+)\}/g, (_, key) => {
    return sampleContext[key] ?? ''
  })
}

const copyFromDay = (day) => {
  const source = form.quiet_hours_schedule.find((slot) => slot.day === day)
  if (!source) return
  form.quiet_hours_schedule = form.quiet_hours_schedule.map((slot) => ({
    ...slot,
    enabled: source.enabled,
    start: source.start,
    end: source.end,
  }))
}

const applyToWeekdays = () => {
  const source = form.quiet_hours_schedule.find((slot) => slot.day === 'Mon')
  if (!source) return
  form.quiet_hours_schedule = form.quiet_hours_schedule.map((slot) => {
    if (['Sat', 'Sun'].includes(slot.day)) return slot
    return {
      ...slot,
      enabled: source.enabled,
      start: source.start,
      end: source.end,
    }
  })
}

const applyToWeekends = () => {
  const source = form.quiet_hours_schedule.find((slot) => slot.day === 'Sat')
  if (!source) return
  form.quiet_hours_schedule = form.quiet_hours_schedule.map((slot) => {
    if (!['Sat', 'Sun'].includes(slot.day)) return slot
    return {
      ...slot,
      enabled: source.enabled,
      start: source.start,
      end: source.end,
    }
  })
}

const loadSettings = async () => {
  try {
    const { data } = await client.get('/settings/notifications')
    const item = data?.data
    if (!item) return
    form.notify_parcel_arrival = item.notify_parcel_arrival ?? true
    form.notify_pickup_request = item.notify_pickup_request ?? true
    form.notify_parcel_return = item.notify_parcel_return ?? true
    form.notify_delivery_completed = item.notify_delivery_completed ?? true
    form.notify_rejected_parcel = item.notify_rejected_parcel ?? true
    form.notify_lost_damaged = item.notify_lost_damaged ?? true
    form.notify_expiry_reminder = item.notify_expiry_reminder ?? true
    form.notify_channel_email = item.notify_channel_email ?? true
    form.notify_channel_sms = item.notify_channel_sms ?? false
    form.notify_channel_in_app = item.notify_channel_in_app ?? true
    form.notify_channel_whatsapp = item.notify_channel_whatsapp ?? false
    form.notify_channel_push = item.notify_channel_push ?? true
    form.use_global_notifications = item.use_global_notifications ?? true
    form.quiet_hours_enabled = item.quiet_hours_enabled ?? false
    form.quiet_hours_start = item.quiet_hours_start || ''
    form.quiet_hours_end = item.quiet_hours_end || ''
    form.quiet_hours_days = item.quiet_hours_days && item.quiet_hours_days.length
      ? item.quiet_hours_days
      : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']
    form.quiet_hours_schedule = Array.isArray(item.quiet_hours_schedule) && item.quiet_hours_schedule.length
      ? item.quiet_hours_schedule.map((slot) => ({
        day: slot.day,
        enabled: !!slot.enabled,
        start: slot.start || '22:00',
        end: slot.end || '07:00',
      }))
      : defaultSchedule()
    form.template_email_subject = item.template_email_subject || 'Parcel update'
    form.template_email_body = item.template_email_body || ''
    form.template_sms_body = item.template_sms_body || ''
    form.template_in_app_body = item.template_in_app_body || ''
    form.template_whatsapp_body = item.template_whatsapp_body || ''
    form.template_push_title = item.template_push_title || ''
    form.template_push_body = item.template_push_body || ''
    savedState.value = JSON.parse(JSON.stringify(form))
  } catch {
    // handled by toast
  }
}

const resetToSaved = () => {
  if (!savedState.value) return
  form.notify_parcel_arrival = savedState.value.notify_parcel_arrival
  form.notify_pickup_request = savedState.value.notify_pickup_request
  form.notify_parcel_return = savedState.value.notify_parcel_return
  form.notify_delivery_completed = savedState.value.notify_delivery_completed
  form.notify_rejected_parcel = savedState.value.notify_rejected_parcel
  form.notify_lost_damaged = savedState.value.notify_lost_damaged
  form.notify_expiry_reminder = savedState.value.notify_expiry_reminder
  form.notify_channel_email = savedState.value.notify_channel_email
  form.notify_channel_sms = savedState.value.notify_channel_sms
  form.notify_channel_in_app = savedState.value.notify_channel_in_app
  form.notify_channel_whatsapp = savedState.value.notify_channel_whatsapp
  form.notify_channel_push = savedState.value.notify_channel_push
  form.use_global_notifications = savedState.value.use_global_notifications
  form.quiet_hours_enabled = savedState.value.quiet_hours_enabled
  form.quiet_hours_start = savedState.value.quiet_hours_start
  form.quiet_hours_end = savedState.value.quiet_hours_end
  form.quiet_hours_days = savedState.value.quiet_hours_days
  form.quiet_hours_schedule = JSON.parse(JSON.stringify(savedState.value.quiet_hours_schedule))
  form.template_email_subject = savedState.value.template_email_subject
  form.template_email_body = savedState.value.template_email_body
  form.template_sms_body = savedState.value.template_sms_body
  form.template_in_app_body = savedState.value.template_in_app_body
  form.template_whatsapp_body = savedState.value.template_whatsapp_body
  form.template_push_title = savedState.value.template_push_title
  form.template_push_body = savedState.value.template_push_body
}

const saveSettings = async () => {
  saving.value = true
  mapErrors(null)
  if (!validateSchedule()) {
    saving.value = false
    return
  }
  try {
  const payload = {
    ...form,
    use_global_notifications: form.use_global_notifications,
    quiet_hours_start: form.quiet_hours_enabled ? form.quiet_hours_start || null : null,
      quiet_hours_end: form.quiet_hours_enabled ? form.quiet_hours_end || null : null,
      quiet_hours_days: form.quiet_hours_enabled ? form.quiet_hours_days : [],
      quiet_hours_schedule: form.quiet_hours_enabled
        ? form.quiet_hours_schedule.map((slot) => ({
          day: slot.day,
          enabled: !!slot.enabled,
          start: slot.enabled ? slot.start || null : null,
          end: slot.enabled ? slot.end || null : null,
        }))
        : [],
    }
    const { data } = await client.put('/settings/notifications', payload)
    savedState.value = JSON.parse(JSON.stringify(data?.data || payload))
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    }
  } finally {
    saving.value = false
  }
}

const saveGlobalDefaults = async () => {
  saving.value = true
  mapErrors(null)
  if (!validateSchedule()) {
    saving.value = false
    return
  }
  try {
    const payload = {
      ...form,
      use_global_notifications: form.use_global_notifications,
      quiet_hours_start: form.quiet_hours_enabled ? form.quiet_hours_start || null : null,
      quiet_hours_end: form.quiet_hours_enabled ? form.quiet_hours_end || null : null,
      quiet_hours_days: form.quiet_hours_enabled ? form.quiet_hours_days : [],
      quiet_hours_schedule: form.quiet_hours_enabled
        ? form.quiet_hours_schedule.map((slot) => ({
          day: slot.day,
          enabled: !!slot.enabled,
          start: slot.enabled ? slot.start || null : null,
          end: slot.enabled ? slot.end || null : null,
        }))
        : [],
    }
    const { data } = await client.put('/settings/notifications?scope=global', payload)
    savedState.value = JSON.parse(JSON.stringify(payload))
  } catch (err) {
    if (err?.response?.status === 422) {
      mapErrors(err.response.data.errors)
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  if (authState.user?.name) userName.value = authState.user.name
  loadSettings()
  document.addEventListener('click', handleOutsideClick)
  document.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  document.removeEventListener('keydown', handleEsc)
})
</script>
