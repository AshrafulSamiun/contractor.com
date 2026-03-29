<template>
  <EmailLayout>
    <template #head>
      <div class="pm-page-head">
        <div>
          <h2>Templates</h2>
          <div class="pm-page-subtitle">Dashboard &gt; Email &gt; Templates</div>
        </div>
        <div class="pm-page-actions">
          <button class="btn btn-outline-secondary" type="button" :disabled="!canCreateEmail" @click="resetForm">New Template</button>
          <button class="btn btn-outline-secondary" type="button" :disabled="loading" @click="loadTemplates">Refresh</button>
        </div>
      </div>
    </template>

    <section class="pm-card pm-ops-card p-4 pm-template-shell">
      <div v-if="!canReadEmail" class="pm-empty-state">You do not have permission to view templates.</div>

      <div v-else class="pm-template-grid">
        <article class="pm-template-editor">
          <div class="pm-template-card-head">
            <h5>{{ activeId ? 'Edit Template' : 'Create Template' }}</h5>
            <span class="pm-muted small">Reusable snippets for consistent communication.</span>
          </div>

          <div class="row g-3">
            <div class="col-md-12">
              <label class="pm-field-label">Template Name *</label>
              <input
                class="form-control"
                v-model="form.name"
                :disabled="!canManageTemplates || saving"
                placeholder="Template name"
              />
              <div v-if="errors.name" class="pm-form-error">{{ errors.name }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Subject</label>
              <input
                class="form-control"
                v-model="form.subject"
                :disabled="!canManageTemplates || saving"
                placeholder="Email subject"
              />
              <div v-if="errors.subject" class="pm-form-error">{{ errors.subject }}</div>
            </div>

            <div class="col-md-12">
              <label class="pm-field-label">Body</label>
              <textarea
                class="form-control"
                rows="9"
                v-model="form.body_html"
                :disabled="!canManageTemplates || saving"
                placeholder="Template body"
              ></textarea>
              <div v-if="errors.body_html" class="pm-form-error">{{ errors.body_html }}</div>
            </div>

            <div class="col-md-12" v-if="isAdmin && canManageTemplates">
              <label class="pm-field-label">Scope</label>
              <select class="form-control" v-model="form.is_global" :disabled="saving">
                <option :value="false">Private</option>
                <option :value="true">Global</option>
              </select>
            </div>
          </div>

          <div class="pm-template-actions">
            <button class="btn btn-outline-secondary" type="button" :disabled="saving || !canCreateEmail" @click="resetForm">Reset</button>
            <button class="btn btn-primary" type="button" :disabled="saving || !canManageTemplates" @click="saveTemplate">
              {{ saving ? 'Saving...' : activeId ? 'Update Template' : 'Save Template' }}
            </button>
          </div>
        </article>

        <aside class="pm-template-list-wrap">
          <div class="pm-template-card-head">
            <h5>Template Library</h5>
            <span class="pm-muted small">{{ filteredTemplates.length }} available</span>
          </div>

          <div class="pm-template-search">
            <input v-model="search" class="form-control" placeholder="Search template by name or subject" />
            <button class="btn btn-outline-secondary" type="button" @click="search = ''">Clear</button>
          </div>

          <div v-if="loading" class="pm-muted">Loading templates...</div>
          <div v-else-if="!filteredTemplates.length" class="pm-muted">No templates found.</div>

          <div v-else class="pm-template-list">
            <article
              v-for="template in filteredTemplates"
              :key="template.id"
              class="pm-template-item"
              :class="{ active: selectedId === template.id }"
              @click="selectTemplate(template)"
            >
              <div>
                <strong>{{ template.name }}</strong>
                <p>{{ template.subject || '(No subject)' }}</p>
                <small>{{ template.is_global ? 'Global' : 'Private' }}</small>
              </div>
              <div class="pm-template-item-actions">
                <button v-if="canEditEmail" class="btn btn-sm btn-outline-primary" type="button" @click.stop="editTemplate(template)">Edit</button>
                <button v-if="canDeleteEmail" class="btn btn-sm btn-outline-danger" type="button" @click.stop="removeTemplate(template.id)">Delete</button>
              </div>
            </article>
          </div>

          <section class="pm-template-preview" v-if="previewTemplate">
            <h6>Preview</h6>
            <div class="pm-muted small mb-2">{{ previewTemplate.subject || '(No subject)' }}</div>
            <div class="pm-template-preview-body">{{ previewBody }}</div>
          </section>
        </aside>
      </div>
    </section>
  </EmailLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import EmailLayout from '../components/EmailLayout.vue'
import { fetchTemplates, createTemplate, updateTemplate, deleteTemplate } from '../api'
import { authState } from '../../store/auth'
import { hasUserPermission } from '../../config/permissions'
import { setFlash } from '../../store/flash'

const templates = ref([])
const loading = ref(false)
const saving = ref(false)
const activeId = ref(null)
const selectedId = ref(null)
const search = ref('')
const errors = reactive({})

const isAdmin = computed(() => authState.user?.role === 'admin')
const canReadEmail = computed(() => hasUserPermission(authState.user, 'email', 'read'))
const canCreateEmail = computed(() => hasUserPermission(authState.user, 'email', 'create'))
const canEditEmail = computed(() => hasUserPermission(authState.user, 'email', 'edit'))
const canDeleteEmail = computed(() => hasUserPermission(authState.user, 'email', 'delete'))
const canManageTemplates = computed(() => (activeId.value ? canEditEmail.value : canCreateEmail.value))

const form = reactive({
  name: '',
  subject: '',
  body_html: '',
  is_global: false,
})

const filteredTemplates = computed(() => {
  const keyword = search.value.trim().toLowerCase()
  if (!keyword) return templates.value
  return templates.value.filter((template) => {
    return [template.name, template.subject]
      .filter(Boolean)
      .some((item) => String(item).toLowerCase().includes(keyword))
  })
})

const previewTemplate = computed(() => {
  if (selectedId.value) {
    const found = templates.value.find((item) => item.id === selectedId.value)
    if (found) return found
  }
  if (form.name || form.subject || form.body_html) {
    return {
      name: form.name,
      subject: form.subject,
      body_html: form.body_html,
      is_global: form.is_global,
    }
  }
  return null
})

const previewBody = computed(() => {
  if (!previewTemplate.value?.body_html) return 'No content available.'
  return String(previewTemplate.value.body_html)
    .replace(/<[^>]*>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .slice(0, 220)
})

const mapErrors = (input) => {
  Object.keys(errors).forEach((key) => delete errors[key])
  if (!input) return
  Object.entries(input).forEach(([key, value]) => {
    errors[key] = Array.isArray(value) ? value[0] : value
  })
}

const loadTemplates = async () => {
  if (!canReadEmail.value) {
    templates.value = []
    return
  }

  loading.value = true
  try {
    const { data } = await fetchTemplates()
    templates.value = data?.data || []
    if (!selectedId.value && templates.value.length) {
      selectedId.value = templates.value[0].id
    }
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  activeId.value = null
  selectedId.value = null
  form.name = ''
  form.subject = ''
  form.body_html = ''
  form.is_global = false
  mapErrors(null)
}

const selectTemplate = (template) => {
  selectedId.value = template.id
}

const editTemplate = (template) => {
  if (!canEditEmail.value) {
    setFlash('You do not have permission to edit templates.', 'warning', 2500)
    return
  }

  activeId.value = template.id
  selectedId.value = template.id
  form.name = template.name
  form.subject = template.subject || ''
  form.body_html = template.body_html || template.body_text || ''
  form.is_global = !!template.is_global
  mapErrors(null)
}

const saveTemplate = async () => {
  if (!canManageTemplates.value) {
    setFlash('You do not have permission to save templates.', 'warning', 2500)
    return
  }

  saving.value = true
  mapErrors(null)
  try {
    const payload = {
      name: form.name,
      subject: form.subject || null,
      body_html: form.body_html || null,
      is_global: !!form.is_global,
    }

    if (activeId.value) {
      await updateTemplate(activeId.value, payload)
      setFlash('Template updated successfully.', 'success', 1800)
    } else {
      const { data } = await createTemplate(payload)
      selectedId.value = data?.data?.id || null
      setFlash('Template created successfully.', 'success', 1800)
    }

    await loadTemplates()
    if (activeId.value) {
      const updated = templates.value.find((item) => item.id === activeId.value)
      if (updated) {
        editTemplate(updated)
      }
    } else {
      resetForm()
    }
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

const removeTemplate = async (id) => {
  if (!canDeleteEmail.value) {
    setFlash('You do not have permission to delete templates.', 'warning', 2500)
    return
  }

  if (!confirm('Delete this template?')) return

  await deleteTemplate(id)
  if (activeId.value === id) {
    resetForm()
  }
  if (selectedId.value === id) {
    selectedId.value = null
  }
  setFlash('Template deleted.', 'success', 1600)
  loadTemplates()
}

onMounted(() => {
  loadTemplates()
})
</script>

<style scoped>
.pm-template-shell {
  border: 1px solid #d7e4ff;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pm-template-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(300px, 1fr);
  gap: 12px;
}

.pm-template-editor,
.pm-template-list-wrap {
  border: 1px solid #dbe7ff;
  border-radius: 14px;
  background: #ffffff;
  padding: 14px;
  display: grid;
  gap: 12px;
  align-content: start;
}

.pm-template-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 8px;
}

.pm-template-card-head h5 {
  margin: 0;
  color: #0f172a;
  font-weight: 800;
}

.pm-template-actions {
  border-top: 1px solid #e2e8f0;
  padding-top: 10px;
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  flex-wrap: wrap;
}

.pm-template-search {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
}

.pm-template-list {
  display: grid;
  gap: 8px;
  max-height: 430px;
  overflow: auto;
}

.pm-template-item {
  border: 1px solid #dbe7ff;
  border-radius: 10px;
  padding: 10px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
  cursor: pointer;
}

.pm-template-item:hover {
  background: #f8fbff;
}

.pm-template-item.active {
  border-color: #93c5fd;
  background: #eff6ff;
}

.pm-template-item strong {
  color: #1e293b;
  display: block;
  margin-bottom: 2px;
}

.pm-template-item p {
  margin: 0;
  color: #64748b;
  font-size: 0.82rem;
}

.pm-template-item small {
  color: #94a3b8;
  font-size: 0.76rem;
}

.pm-template-item-actions {
  display: inline-flex;
  gap: 6px;
}

.pm-template-preview {
  border: 1px solid #dbe7ff;
  border-radius: 10px;
  background: #f8fbff;
  padding: 10px;
}

.pm-template-preview h6 {
  margin: 0;
  color: #0f172a;
  font-weight: 800;
}

.pm-template-preview-body {
  color: #475569;
  font-size: 0.84rem;
  line-height: 1.55;
}

@media (max-width: 1100px) {
  .pm-template-grid {
    grid-template-columns: 1fr;
  }

  .pm-template-list {
    max-height: none;
  }
}
</style>
