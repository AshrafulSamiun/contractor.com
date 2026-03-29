<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Email Settings</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Settings</div>
        </div>
        <div class="pm-page-actions">
          <button class="btn btn-outline-secondary" type="button" :disabled="loading || saving" @click="loadSettings">Refresh</button>
          <button class="btn btn-primary" type="button" :disabled="loading || saving || !canEditEmail" @click="saveSettings">
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4 pm-settings-shell">
      <div v-if="loading" class="pm-empty-state">Loading email settings...</div>

      <div v-else class="pm-settings-grid">
        <aside class="pm-settings-summary">
          <section class="pm-settings-box">
            <h6>Configuration Status</h6>
            <div class="pm-settings-item">
              <span>Access</span>
              <strong>{{ canEditEmail ? 'Edit Enabled' : 'Read Only' }}</strong>
            </div>
            <div class="pm-settings-item">
              <span>Global Mode</span>
              <strong>{{ form.use_global_settings ? 'Enabled' : 'Disabled' }}</strong>
            </div>
            <div class="pm-settings-item">
              <span>Sender Identity</span>
              <strong :class="senderConfigured ? 'ok' : 'warn'">{{ senderConfigured ? 'Configured' : 'Incomplete' }}</strong>
            </div>
            <div class="pm-settings-item">
              <span>IMAP Sync</span>
              <strong :class="imapConfigured ? 'ok' : 'warn'">{{ imapConfigured ? 'Configured' : 'Not configured' }}</strong>
            </div>
          </section>

          <section class="pm-settings-box" v-if="isAdmin">
            <h6>Admin Scope</h6>
            <p class="pm-muted small mb-2">
              Save global defaults to apply your baseline for all users who enable global mode.
            </p>
            <button
              class="btn btn-outline-secondary w-100"
              type="button"
              :disabled="saving || !canEditEmail"
              @click="saveGlobalDefaults"
            >
              Save as Global Defaults
            </button>
          </section>

          <section class="pm-settings-box">
            <h6>Actions</h6>
            <div class="pm-settings-actions">
              <button class="btn btn-outline-secondary" type="button" :disabled="saving || !canEditEmail" @click="resetToSaved">Reset</button>
              <button class="btn btn-primary" type="button" :disabled="saving || !canEditEmail" @click="saveSettings">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </section>
        </aside>

        <article class="pm-settings-main">
          <section class="pm-settings-card">
            <h5>{{ isAdmin ? 'Admin Defaults' : 'User Defaults' }}</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="pm-field-label">Use global defaults *</label>
                <select class="form-control" v-model="form.use_global_settings" :disabled="!canEditEmail || saving">
                  <option :value="true">Enabled</option>
                  <option :value="false">Disabled</option>
                </select>
                <div class="pm-muted small mt-2" v-if="isAdmin">Admin can still edit all fields while global mode is enabled.</div>
                <div class="pm-muted small mt-2" v-else>When enabled, your account follows admin-wide defaults.</div>
              </div>
            </div>
          </section>

          <fieldset :disabled="(form.use_global_settings && !isAdmin) || !canEditEmail || saving" class="pm-settings-fieldset">
            <section class="pm-settings-card">
              <h5>Sender Identity</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="pm-field-label">From name</label>
                  <input class="form-control" v-model="form.from_name" placeholder="Company name" />
                  <div v-if="errors.from_name" class="pm-form-error">{{ errors.from_name }}</div>
                </div>
                <div class="col-md-6">
                  <label class="pm-field-label">From email</label>
                  <input class="form-control" v-model="form.from_email" placeholder="sender@example.com" />
                  <div v-if="errors.from_email" class="pm-form-error">{{ errors.from_email }}</div>
                </div>
                <div class="col-md-6">
                  <label class="pm-field-label">Reply-to</label>
                  <input class="form-control" v-model="form.reply_to" placeholder="reply@example.com" />
                  <div v-if="errors.reply_to" class="pm-form-error">{{ errors.reply_to }}</div>
                </div>
                <div class="col-md-6">
                  <label class="pm-field-label">Default CC</label>
                  <input class="form-control" v-model="form.default_cc" placeholder="cc1@example.com, cc2@example.com" />
                  <div v-if="errors.default_cc" class="pm-form-error">{{ errors.default_cc }}</div>
                </div>
                <div class="col-md-6">
                  <label class="pm-field-label">Default BCC</label>
                  <input class="form-control" v-model="form.default_bcc" placeholder="bcc@example.com" />
                  <div v-if="errors.default_bcc" class="pm-form-error">{{ errors.default_bcc }}</div>
                </div>
              </div>
            </section>

            <section class="pm-settings-card">
              <h5>IMAP Sync</h5>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="pm-field-label">Enable IMAP</label>
                  <select class="form-control" v-model="form.imap_enabled">
                    <option :value="true">Enabled</option>
                    <option :value="false">Disabled</option>
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="pm-field-label">Host</label>
                  <input class="form-control" v-model="form.imap_host" placeholder="imap.example.com" />
                  <div v-if="errors.imap_host" class="pm-form-error">{{ errors.imap_host }}</div>
                </div>
                <div class="col-md-4">
                  <label class="pm-field-label">Port</label>
                  <input class="form-control" v-model="form.imap_port" placeholder="993" />
                  <div v-if="errors.imap_port" class="pm-form-error">{{ errors.imap_port }}</div>
                </div>
                <div class="col-md-4">
                  <label class="pm-field-label">Encryption</label>
                  <select class="form-control" v-model="form.imap_encryption">
                    <option value="ssl">SSL</option>
                    <option value="tls">TLS</option>
                    <option value="none">None</option>
                  </select>
                  <div v-if="errors.imap_encryption" class="pm-form-error">{{ errors.imap_encryption }}</div>
                </div>
                <div class="col-md-4">
                  <label class="pm-field-label">Folder</label>
                  <input class="form-control" v-model="form.imap_folder" placeholder="INBOX" />
                  <div v-if="errors.imap_folder" class="pm-form-error">{{ errors.imap_folder }}</div>
                </div>
                <div class="col-md-6">
                  <label class="pm-field-label">Username</label>
                  <input class="form-control" v-model="form.imap_username" placeholder="user@example.com" />
                  <div v-if="errors.imap_username" class="pm-form-error">{{ errors.imap_username }}</div>
                </div>
                <div class="col-md-6">
                  <label class="pm-field-label">Password</label>
                  <input class="form-control" v-model="form.imap_password" type="password" placeholder="********" />
                  <div v-if="errors.imap_password" class="pm-form-error">{{ errors.imap_password }}</div>
                </div>
              </div>
            </section>

            <section class="pm-settings-card">
              <h5>Signature</h5>
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="pm-field-label">Signature (HTML)</label>
                  <textarea class="form-control" rows="4" v-model="form.signature_html"></textarea>
                  <div v-if="errors.signature_html" class="pm-form-error">{{ errors.signature_html }}</div>
                </div>
                <div class="col-md-12">
                  <label class="pm-field-label">Signature (Text)</label>
                  <textarea class="form-control" rows="3" v-model="form.signature_text"></textarea>
                  <div v-if="errors.signature_text" class="pm-form-error">{{ errors.signature_text }}</div>
                </div>
              </div>
            </section>
          </fieldset>

          <section class="pm-settings-card pm-settings-footer">
            <div class="pm-settings-actions">
              <button class="btn btn-outline-secondary" type="button" :disabled="saving || !canEditEmail" @click="resetToSaved">
                Reset
              </button>
              <button class="btn btn-primary" type="button" :disabled="saving || !canEditEmail" @click="saveSettings">
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
            <p v-if="!canEditEmail" class="pm-muted mt-3 mb-0">You have read-only access to email settings.</p>
          </section>
        </article>
      </div>
    </section>
  </EmailLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import EmailLayout from '../components/EmailLayout.vue'
import { fetchEmailSettings, updateEmailSettings } from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'
import { setFlash } from '../../store/flash'

const isAdmin = computed(() => authState.user?.role === 'admin')
const canEditEmail = computed(() => hasUserPermission(authState.user, 'email', 'edit'))

const loading = ref(false)
const saving = ref(false)
const savedState = ref(null)
const errors = reactive({})

const form = reactive({
  use_global_settings: true,
  from_name: '',
  from_email: '',
  reply_to: '',
  default_cc: '',
  default_bcc: '',
  signature_html: '',
  signature_text: '',
  imap_enabled: false,
  imap_host: '',
  imap_port: '',
  imap_encryption: 'ssl',
  imap_username: '',
  imap_password: '',
  imap_folder: 'INBOX',
})

const senderConfigured = computed(() => !!(form.from_name && form.from_email))
const imapConfigured = computed(() => !!(form.imap_enabled && form.imap_host && form.imap_username))

const toList = (value) => {
  if (!value) return []
  return value
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
}

const mapErrors = (input) => {
  Object.keys(errors).forEach((key) => delete errors[key])
  if (!input) return
  Object.entries(input).forEach(([key, value]) => {
    errors[key] = Array.isArray(value) ? value[0] : value
  })
}

const payloadFromForm = () => {
  return {
    use_global_settings: form.use_global_settings,
    from_name: form.from_name || null,
    from_email: form.from_email || null,
    reply_to: form.reply_to || null,
    default_cc: toList(form.default_cc),
    default_bcc: toList(form.default_bcc),
    signature_html: form.signature_html || null,
    signature_text: form.signature_text || null,
    imap_enabled: form.imap_enabled,
    imap_host: form.imap_host || null,
    imap_port: form.imap_port ? Number(form.imap_port) : null,
    imap_encryption: form.imap_encryption || null,
    imap_username: form.imap_username || null,
    imap_password: form.imap_password || null,
    imap_folder: form.imap_folder || null,
  }
}

const loadSettings = async () => {
  loading.value = true
  try {
    const { data } = await fetchEmailSettings()
    const item = data?.data
    if (!item) return

    form.use_global_settings = item.use_global_settings ?? true
    form.from_name = item.from_name || ''
    form.from_email = item.from_email || ''
    form.reply_to = item.reply_to || ''
    form.default_cc = (item.default_cc || []).join(', ')
    form.default_bcc = (item.default_bcc || []).join(', ')
    form.signature_html = item.signature_html || ''
    form.signature_text = item.signature_text || ''
    form.imap_enabled = item.imap_enabled ?? false
    form.imap_host = item.imap_host || ''
    form.imap_port = item.imap_port || ''
    form.imap_encryption = item.imap_encryption || 'ssl'
    form.imap_username = item.imap_username || ''
    form.imap_password = item.imap_password || ''
    form.imap_folder = item.imap_folder || 'INBOX'

    savedState.value = JSON.parse(JSON.stringify(form))
    mapErrors(null)
  } finally {
    loading.value = false
  }
}

const resetToSaved = () => {
  if (!savedState.value) return
  Object.assign(form, JSON.parse(JSON.stringify(savedState.value)))
  mapErrors(null)
}

const saveSettings = async () => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to edit email settings.', 'warning', 2500)
    return
  }

  saving.value = true
  mapErrors(null)
  try {
    await updateEmailSettings(payloadFromForm())
    savedState.value = JSON.parse(JSON.stringify(form))
    setFlash('Email settings updated.', 'success', 1800)
  } catch (error) {
    if (error?.response?.status === 422) {
      mapErrors(error.response.data?.errors)
      if (error.response.data?.message) {
        setFlash(error.response.data.message, 'warning', 2500)
      }
    }
  } finally {
    saving.value = false
  }
}

const saveGlobalDefaults = async () => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to edit email settings.', 'warning', 2500)
    return
  }

  saving.value = true
  mapErrors(null)
  try {
    await updateEmailSettings(payloadFromForm(), 'global')
    savedState.value = JSON.parse(JSON.stringify(form))
    setFlash('Global email defaults updated.', 'success', 1800)
  } catch (error) {
    if (error?.response?.status === 422) {
      mapErrors(error.response.data?.errors)
      if (error.response.data?.message) {
        setFlash(error.response.data.message, 'warning', 2500)
      }
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.pm-settings-shell {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-settings-grid {
  display: grid;
  grid-template-columns: minmax(260px, 0.78fr) minmax(0, 1.4fr);
  gap: 12px;
}

.pm-settings-summary {
  display: grid;
  gap: 10px;
  align-content: start;
}

.pm-settings-box,
.pm-settings-card {
  border: 1px solid #dbe7ff;
  border-radius: 12px;
  background: #ffffff;
  padding: 12px;
}

.pm-settings-box h6,
.pm-settings-card h5 {
  margin: 0 0 8px;
  color: #0f172a;
  font-weight: 800;
}

.pm-settings-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  border-bottom: 1px dashed #dbe7ff;
  padding: 7px 0;
  font-size: 0.86rem;
}

.pm-settings-item:last-child {
  border-bottom: 0;
}

.pm-settings-item span {
  color: #64748b;
}

.pm-settings-item strong {
  color: #0f172a;
}

.pm-settings-item strong.ok {
  color: #166534;
}

.pm-settings-item strong.warn {
  color: #92400e;
}

.pm-settings-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pm-settings-main {
  display: grid;
  gap: 10px;
}

.pm-settings-fieldset {
  display: grid;
  gap: 10px;
}

.pm-settings-footer {
  border-style: dashed;
}

@media (max-width: 1100px) {
  .pm-settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>
