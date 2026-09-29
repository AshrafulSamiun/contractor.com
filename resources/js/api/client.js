import axios from 'axios'
import { setFlash } from '../store/flash'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api/v1'

const client = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

client.interceptors.request.use((config) => {
  let deviceId = localStorage.getItem('pm_device_id')
  if (!deviceId) {
    deviceId = window.crypto?.randomUUID?.() || `device-${Date.now()}-${Math.random().toString(36).slice(2)}`
    localStorage.setItem('pm_device_id', deviceId)
  }
  config.headers['X-Device-ID'] = deviceId
  return config
})

export const setAuthToken = (token) => {
  if (token) {
    client.defaults.headers.common.Authorization = `Bearer ${token}`
  } else {
    delete client.defaults.headers.common.Authorization
  }
}

import { API_ERRORS } from '../config/messages'

export const normalizeApiError = (error) => {
  const data = error?.response?.data
  const status = error?.response?.status
  const errors = data?.errors || {}
  let message = data?.message
  if (!message && errors && Object.keys(errors).length) {
    const firstKey = Object.keys(errors)[0]
    message = errors[firstKey]?.[0]
  }
  if (!message) {
    if (status === 422) message = API_ERRORS.INVALID
    else if (status === 401) message = API_ERRORS.UNAUTHORIZED
    else if (status === 403) message = API_ERRORS.FORBIDDEN
    else if (status === 429) message = API_ERRORS.RATE_LIMIT
    else message = API_ERRORS.DEFAULT
  }
  return { status, message, errors }
}

client.interceptors.response.use(
  (response) => {
    const method = (response.config?.method || 'get').toLowerCase()
    // Show success toast for write operations
    if (['post', 'put', 'patch', 'delete'].includes(method)) {
      const msg = response.data?.message || 'Saved successfully.'
      setFlash(msg, 'success')
    }
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('pm-content-updated'))
    }
    return response
  },
  (error) => {
    const { message } = normalizeApiError(error)
    setFlash(message, 'warning')
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('pm-content-updated'))
    }
    return Promise.reject(error)
  }
)

export default client
