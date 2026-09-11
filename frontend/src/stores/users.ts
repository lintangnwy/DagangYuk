import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../utils/axios'

export interface Role {
  id: number
  name: string
}

export interface User {
  id: number
  name: string
  email: string
  role_id: number
  role: Role
  tenant_id: number | null
}

export interface CreateUserPayload {
  name: string
  email: string
  password?: string
  role_id: number
}

export const useUserStore = defineStore('user', () => {
  const users   = ref<User[]>([])
  const loading = ref(false)
  const error   = ref<string | null>(null)
  const saving  = ref(false)

  async function fetchUsers() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/users')
      users.value = res.data.data ?? res.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat data pengguna.'
    } finally {
      loading.value = false
    }
  }

  async function createUser(payload: CreateUserPayload) {
    saving.value = true
    error.value = null
    try {
      const res = await api.post('/users', payload)
      // fetch again to get the full relations (role) or just push
      await fetchUsers()
      return res.data
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Gagal membuat pengguna.'
      error.value = msg
      throw new Error(msg)
    } finally {
      saving.value = false
    }
  }

  async function updateUser(id: number, payload: CreateUserPayload) {
    saving.value = true
    error.value = null
    try {
      await api.put(`/users/${id}`, payload)
      await fetchUsers()
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Gagal mengubah pengguna.'
      error.value = msg
      throw new Error(msg)
    } finally {
      saving.value = false
    }
  }
  
  async function deleteUser(id: number) {
    try {
      await api.delete(`/users/${id}`)
      users.value = users.value.filter(u => u.id !== id)
    } catch (e: any) {
      throw new Error(e.response?.data?.message || 'Gagal menghapus pengguna.')
    }
  }

  return { users, loading, error, saving, fetchUsers, createUser, updateUser, deleteUser }
})
