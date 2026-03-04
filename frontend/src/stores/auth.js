import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || null)
  const loading = ref(false)
  const errors = ref({})

  const isAuthenticated = computed(() => !!token.value)

  async function login(credentials) {
    loading.value = true
    errors.value = {}
    try {
      const { data } = await api.post('/login', credentials)
      setAuth(data)
      return true
    } catch (e) {
      errors.value = e.response?.data?.errors || { email: [e.response?.data?.message || 'Erro ao fazer login'] }
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    errors.value = {}
    try {
      const { data } = await api.post('/register', userData)
      setAuth(data)
      return true
    } catch (e) {
      errors.value = e.response?.data?.errors || {}
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // Ignore errors on logout
    }
    clearAuth()
  }

  async function fetchUser() {
    try {
      const { data } = await api.get('/user')
      user.value = data
      localStorage.setItem('user', JSON.stringify(data))
    } catch {
      clearAuth()
    }
  }

  function setAuth(data) {
    user.value = data.user
    token.value = data.token
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
  }

  function clearAuth() {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  return {
    user, token, loading, errors,
    isAuthenticated,
    login, register, logout, fetchUser,
  }
})
