import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

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
    const res = await fetch(`${API_BASE}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password }),
    })
    const data = await res.json()
    if (!res.ok) {
      throw new Error(
        data?.errors?.email?.[0] ??
        data?.errors?.password?.[0] ??
        data?.message ??
        'Login gagal.'
      )
    }
    setSession(data.token, {
      id:        data.user.id,
      name:      data.user.name,
      email:     data.user.email,
      role:      data.user.role,
      tenant_id: data.user.tenant_id,
    })
  }

  async function logout(): Promise<void> {
    if (token.value) {
      await fetch(`${API_BASE}/logout`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}`, Accept: 'application/json' },
      }).catch(() => {})
    }
    clearSession()
  }

  async function fetchUser(): Promise<void> {
    if (!token.value) return
    const res = await fetch(`${API_BASE}/user`, {
      headers: { Authorization: `Bearer ${token.value}`, Accept: 'application/json' },
    })
    if (res.ok) {
      const data = await res.json()
      const u: AuthUser = {
        id:        data.id,
        name:      data.name,
        email:     data.email,
        role:      data.role?.name ?? data.role,
        tenant_id: data.tenant_id,
      }
      user.value = u
      localStorage.setItem(USER_KEY, JSON.stringify(u))
    } else {
      clearSession()
    }
  }

  const isLoggedIn = () => !!token.value

  // Auto-fetch user jika token ada tapi user belum di-load
  if (token.value && !user.value) {
    fetchUser()
  }

  return { user, token, isAdmin, isSuperAdmin, login, logout, fetchUser, isLoggedIn }
})
