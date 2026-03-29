import { reactive } from 'vue'

const state = reactive({
  token: localStorage.getItem('pm_token'),
  user: null,
})

export const authState = state

export const setAuthTokenState = (token) => {
  state.token = token
}

export const setUser = (user) => {
  state.user = user
}

export const clearUser = () => {
  state.user = null
}
