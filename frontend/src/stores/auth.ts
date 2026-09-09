import { defineStore } from 'pinia'
import { ref } from 'vue'

interface User {
  id: number
  name: string
  email: string
}

const API_BASE = 'http://localhost:8000/api'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))

  function setToken(t: string) {
    token.value = t
    localStorage.setItem('token', t)
  }

  function clearToken() {
    token.value = null
    localStorage.removeItem('token')
  }

  async function login(email: string, password: string): Promise<void> {
    const res = await fetch(`${API_BASE}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({ email, password }),
    })

    const data = await res.json()

    if (!res.ok) {
      // Laravel validation error → ambil pesan pertama
      const msg =
        data?.errors?.email?.[0] ??
        data?.errors?.password?.[0] ??
        data?.message ??
        'Login gagal.'
      throw new Error(msg)
    }

    setToken(data.token)
    user.value = data.user
  }

  async function logout(): Promise<void> {
    if (token.value) {
      await fetch(`${API_BASE}/logout`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: 'application/json',
        },
      }).catch(() => {})
    }
    clearToken()
    user.value = null
  }

  async function fetchUser(): Promise<void> {
    if (!token.value) return
    const res = await fetch(`${API_BASE}/user`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json',
      },
    })
    if (res.ok) {
      user.value = await res.json()
    } else {
      clearToken()
    }
  }

  const isLoggedIn = () => !!token.value

  return { user, token, login, logout, fetchUser, isLoggedIn }
})
