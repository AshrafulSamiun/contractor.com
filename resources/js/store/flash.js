import { reactive } from 'vue'

let timer
const defaultTimeout = Number(import.meta.env.VITE_FLASH_TIMEOUT || 3000)
let startedAt = 0

const state = reactive({
  message: '',
  type: 'info',
  duration: defaultTimeout,
  remaining: defaultTimeout,
  paused: false,
})

export const flashState = state

export const setFlash = (message, type = 'info', timeout = defaultTimeout) => {
  state.message = message
  state.type = type
  state.duration = timeout
  state.remaining = timeout
  state.paused = false
  startedAt = Date.now()
  if (timer) clearTimeout(timer)
  if (timeout) {
    timer = setTimeout(() => {
      state.message = ''
    }, timeout)
  }
}

export const clearFlash = () => {
  if (timer) clearTimeout(timer)
  state.message = ''
}

export const pauseFlash = () => {
  if (!state.message || state.paused) return
  state.paused = true
  if (timer) clearTimeout(timer)
  const elapsed = Date.now() - startedAt
  state.remaining = Math.max(0, state.remaining - elapsed)
}

export const resumeFlash = () => {
  if (!state.message || !state.paused) return
  state.paused = false
  startedAt = Date.now()
  if (state.remaining > 0) {
    timer = setTimeout(() => {
      state.message = ''
    }, state.remaining)
  }
}
