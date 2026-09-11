import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../utils/axios'

export interface AuthUser {
  id: number
  name: string
  email: string
  role?: string
  tenant_id?: number | null
}

const API_BASE  = 'http://localhost:8000/api'
const USER_KEY  = 'dagang_user'
const TOKEN_KEY = 'dagang_token'

export const useAuthStore = defineStore('auth', () => {
  // Cek key baru dulu, fallback ke key lama
  const savedToken = localStorage.getItem(TOKEN_KEY) ?? localStorage.getItem('token')
  const savedUser  = localStorage.getItem(USER_KEY)  ?? localStorage.getItem('user')

  const user  = ref<AuthUser | null>(savedUser ? JSON.parse(savedUser) : null)
  const token = ref<string | null>(savedToken)

  const isAdmin      = computed(() =>
    user.value?.role === 'admin' || user.value?.role === 'super_admin'
  )
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')

  function setSession(t: string, u: AuthUser) {
    token.value = t
    user.value  = u
    localStorage.setItem(TOKEN_KEY, t)
    localStorage.setItem(USER_KEY,  JSON.stringify(u))
  }

  function clearSession() {
    token.value = null
    user.value  = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
    // bersihkan key lama jika ada
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function login(email: string, password: string): Promise<void> {
    try {
      const res = await api.post('/login', { email, password })
      const data = res.data
      
      setSession(data.data.token, {
        id:        data.data.user.id,
        name:      data.data.user.name,
        email:     data.data.user.email,
        role:      data.data.user.role?.name ?? data.data.user.role_id,
        tenant_id: data.data.user.tenant_id,
      })
    } catch (err: any) {
      throw new Error(
        err.response?.data?.message || 'Login gagal.'
      )
    }
  }

  async function logout(): Promise<void> {
    if (token.value) {
      try {
        await api.post('/logout')
      } catch (e) {}
    }
    clearSession()
  }

  async function fetchUser(): Promise<void> {
    if (!token.value) return
    try {
      const res = await api.get('/user')
      const data = res.data
      const u: AuthUser = {
        id:        data.id,
        name:      data.name,
        email:     data.email,
        role:      data.role?.name ?? data.role,
        tenant_id: data.tenant_id,
      }
      user.value = u
      localStorage.setItem(USER_KEY, JSON.stringify(u))
    } catch (e) {
      clearSession()
    }
  }

  const isLoggedIn = () => !!token.value

  // Auto-fetch user jika token ada tapi user belum di-load
  if (token.value && !user.value) {
    fetchUser()
  }

  return { user, token, isAdmin, isSuperAdmin, login, logout, fetchUser, isLoggedIn, clearSession }
})
