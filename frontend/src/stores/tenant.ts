import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../utils/axios'

export interface Tenant {
  id: number
  name: string
  address: string | null
  phone: string | null
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface CreateTenantPayload {
  name: string
  address?: string
  phone?: string
  // Data Admin untuk tenant baru
  admin_name: string
  admin_email: string
  admin_password: string
}

export const useTenantStore = defineStore('tenant', () => {
  const tenants  = ref<Tenant[]>([])
  const loading  = ref(false)
  const error    = ref<string | null>(null)
  const saving   = ref(false)

  async function fetchTenants() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/tenants')
      tenants.value = res.data.data ?? res.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat data tenant.'
    } finally {
      loading.value = false
    }
  }

  async function createTenantWithAdmin(payload: CreateTenantPayload): Promise<void> {
    saving.value = true
    error.value = null
    try {
      // Step 1: Buat tenant
      const tenantRes = await api.post('/tenants', {
        name:    payload.name,
        address: payload.address || null,
        phone:   payload.phone || null,
      })

      const newTenant: Tenant = tenantRes.data.data ?? tenantRes.data
      tenants.value.push(newTenant)

      // Step 2: Buat admin untuk tenant tersebut
      await api.post('/users', {
        name:      payload.admin_name,
        email:     payload.admin_email,
        password:  payload.admin_password,
        role_id:   2, // Admin role
        tenant_id: newTenant.id,
      })
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Gagal membuat tenant.'
      error.value = msg
      throw new Error(msg)
    } finally {
      saving.value = false
    }
  }

  async function toggleTenantStatus(tenant: Tenant): Promise<void> {
    try {
      await api.put(`/tenants/${tenant.id}`, {
        name:      tenant.name,
        address:   tenant.address,
        phone:     tenant.phone,
        is_active: !tenant.is_active,
      })
      tenant.is_active = !tenant.is_active
    } catch (e: any) {
      throw new Error(e.response?.data?.message || 'Gagal mengubah status tenant.')
    }
  }

  return { tenants, loading, error, saving, fetchTenants, createTenantWithAdmin, toggleTenantStatus }
})
