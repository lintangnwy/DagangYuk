import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../utils/axios'

export interface AuthUser {
  id: number
  name: string
  email: string
  role?: string
  tenant_id?: number | null
  permissions?: string[]
}

const TOKEN_KEY = 'dagang_token'
const USER_KEY  = 'dagang_user'

export const useAuthStore = defineStore('auth', () => {
  const savedToken = localStorage.getItem(TOKEN_KEY) ?? localStorage.getItem('token')
  const savedUser  = localStorage.getItem(USER_KEY)  ?? localStorage.getItem('user')

  const user  = ref<AuthUser | null>(savedUser ? JSON.parse(savedUser) : null)
  const token = ref<string | null>(savedToken)

  const isAdmin      = computed(() =>
    user.value?.role === 'admin' || user.value?.role === 'super_admin'
  )
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')

  function can(permission: string): boolean {
    if (!user.value?.permissions) return false
    const perms = user.value.permissions
    if (perms.includes(permission)) return true
    for (const p of perms) {
      if (p.endsWith('.*')) {
        const prefix = p.slice(0, -2)
        if (permission === prefix || permission.startsWith(prefix + '.')) {
          return true
        }
      }
    }
    return false
  }

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
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function login(email: string, password: string): Promise<void> {
    try {
      console.log('Attempting login with:', email)
      
      const res = await api.post('/login', { email, password })
      
      console.log('Login API response:', res.data)
      
      if (!res.data?.data?.user) {
        throw new Error('User data tidak ditemukan di respons.')
      }
      
      const uData = res.data.data.user
      
      const role = (uData.role?.name ?? uData.role_id) || 'kasir'
      
      setSession(res.data.data.token, {
        id:          uData.id ?? 0,
        name:        uData.name || 'Unknown',
        email:       uData.email || '',
        role:        role,
        tenant_id:   uData.tenant_id || null,
        permissions: uData.permissions ?? [],
      })
      
      console.log('User stored successfully:', user.value)
      
    } catch (err: any) {
      console.error('Login failed:', err.response?.data || err.message)
      throw new Error(
        err.response?.data?.message || 'Login gagal. Coba lagi nanti.'
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
        id:          data.id,
        name:        data.name,
        email:       data.email,
        role:        data.role?.name ?? data.role,
        tenant_id:   data.tenant_id,
        permissions: data.permissions ?? [],
      }
      user.value = u
      localStorage.setItem(USER_KEY, JSON.stringify(u))
    } catch (e) {
      clearSession()
    }
  }

  const isLoggedIn = () => !!token.value

  if (token.value && !user.value) {
    fetchUser()
  }

  return { user, token, isAdmin, isSuperAdmin, can, login, logout, fetchUser, isLoggedIn, clearSession }
})
