<template>
  <div class="pm-captcha">
    <div class="pm-captcha-row">
      <img v-if="captchaImg" :src="captchaImg" alt="Captcha" class="pm-captcha-img" />
      <button type="button" class="btn btn-light btn-sm" @click="refresh" :disabled="loading">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>
    <input v-model="value" class="form-control" placeholder="Enter captcha" @input="emitValue" />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import client from '../api/client'

const emit = defineEmits(['change'])

const captchaImg = ref('')
const captchaKey = ref('')
const value = ref('')
const loading = ref(false)

const emitValue = () => {
  emit('change', { captcha_key: captchaKey.value, captcha_value: value.value })
}

const refresh = async () => {
  loading.value = true
  try {
    const { data } = await client.get('/captcha')
    if (data?.success && data?.data) {
      captchaImg.value = data.data.img
      captchaKey.value = data.data.key
      value.value = ''
      emitValue()
    }
  } finally {
    loading.value = false
  }
}

onMounted(refresh)

defineExpose({ refresh })
</script>

