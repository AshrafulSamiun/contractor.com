import client, { setAuthToken } from './client'
import { setAuthTokenState, setUser, clearUser } from '../store/auth'

export const login = async (payload) => {
  const { data } = await client.post('/login', payload)
  return data
}

export const register = async (payload) => {
  const { data } = await client.post('/register', payload)
  return data
}

export const requestPasswordReset = async (payload) => {
  const { data } = await client.post('/forgot-password/request', payload)
  return data
}

export const resetPassword = async (payload) => {
  const { data } = await client.post('/forgot-password/reset', payload)
  return data
}

export const requestUsernameReminder = async (payload) => {
  const { data } = await client.post('/forgot-username/request', payload)
  return data
}

export const getMe = async () => {
  const { data } = await client.get('/me')
  return data
}

export const logout = async () => {
  const { data } = await client.post('/logout')
  return data
}

export const initAuth = () => {
  const token = localStorage.getItem('pm_token')
  if (token) setAuthToken(token)
  setAuthTokenState(token)
}

export const saveToken = (token) => {
  localStorage.setItem('pm_token', token)
  setAuthToken(token)
  setAuthTokenState(token)
}

export const clearToken = () => {
  localStorage.removeItem('pm_token')
  setAuthToken(null)
  setAuthTokenState(null)
  clearUser()
}

export const loadMe = async () => {
  const { data } = await getMe()
  setUser(data?.data ?? null)
  return data
}
