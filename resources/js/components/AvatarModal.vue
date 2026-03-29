<template>
  <div class="pm-modal-backdrop" @click.self="close">
    <div class="pm-modal">
      <h5 class="mb-3">Update Avatar</h5>
      <form @submit.prevent="onSave">
        <div class="mb-3">
          <label class="pm-field-label">Avatar URL<span class="pm-required">*</span></label>
          <input v-model="url" class="form-control" placeholder="https://example.com/avatar.png" />
          <div v-if="error" class="text-danger small mt-1">{{ error }}</div>
        </div>
        <div v-if="url" class="mb-3">
          <div class="pm-field-label">Preview</div>
          <img :src="url" alt="Avatar preview" class="pm-avatar-preview" @error="onImgError" />
          <div v-if="imgError" class="text-danger small mt-2">Preview failed. Please check the URL.</div>
        </div>
        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-light" @click="close">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import client, { normalizeApiError } from '../api/client'
import { setFlash } from '../store/flash'
import { FLASH } from '../config/messages'
import { setUser } from '../store/auth'

const emit = defineEmits(['close'])

const url = ref('')
const error = ref('')
const loading = ref(false)
const imgError = ref(false)

const close = () => emit('close')

const onImgError = () => {
  imgError.value = true
}

const onSave = async () => {
  error.value = ''
  imgError.value = false
  if (!url.value) {
    error.value = 'Avatar URL is required.'
    return
  }
  loading.value = true
  try {
    const { data } = await client.post('/profile/avatar', { avatar_url: url.value })
    if (data?.success && data?.data) {
      setUser(data.data)
      setFlash(FLASH.AVATAR_UPDATED, 'success')
      close()
    } else {
      error.value = 'Unable to update avatar.'
    }
  } catch (e) {
    const parsed = normalizeApiError(e)
    error.value = parsed.message || 'Unable to update avatar.'
  } finally {
    loading.value = false
  }
}
</script>
