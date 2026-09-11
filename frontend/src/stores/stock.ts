import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/axios'

export interface StockProduct {
  id: number
  name: string
  sku: string | null
  image: string | null
  stock: number
  cost_price: number | null
  price: number
  category: { id: number; name: string } | null
  stock_value: number
  last_adjustment: {
    type: 'in' | 'out'
    quantity: number
    reason: string
    created_at: string
  } | null
}

export interface StockSummary {
  total_products: number
  total_stock_value: number
  low_stock: number
  out_of_stock: number
}

export interface StockAdjustment {
  id: number
  product_id: number
  user_id: number
  type: 'in' | 'out'
  quantity: number
  stock_before: number
  stock_after: number
  reason: string
  notes: string | null
  created_at: string
  product?: { id: number; name: string; sku: string | null; image: string | null }
  user?: { id: number; name: string }
}

export interface CreateAdjustmentPayload {
  product_id: number
  type: 'in' | 'out'
  quantity: number
  reason: string
  notes?: string
}

export const useStockStore = defineStore('stock', () => {
  const products    = ref<StockProduct[]>([])
  const summary     = ref<StockSummary | null>(null)
  const adjustments = ref<StockAdjustment[]>([])
  const loading     = ref(false)
  const saving      = ref(false)
  const error       = ref('')

  async function fetchSummary() {
    loading.value = true
    error.value   = ''
    try {
      const res = await api.get('/stock-adjustments/summary')
      summary.value  = res.data.summary
      products.value = res.data.products
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat data inventaris.'
    } finally {
      loading.value = false
    }
  }

  async function fetchAdjustments(productId?: number) {
    loading.value = true
    error.value   = ''
    try {
      const params: Record<string, any> = {}
      if (productId) params.product_id = productId
      const res = await api.get('/stock-adjustments', { params })
      adjustments.value = res.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Gagal memuat riwayat stok.'
    } finally {
      loading.value = false
    }
  }

  async function createAdjustment(payload: CreateAdjustmentPayload) {
    saving.value = true
    try {
      const res = await api.post('/stock-adjustments', payload)
      // Refresh summary after adjustment
      await fetchSummary()
      return res.data
    } catch (e: any) {
      throw new Error(e.response?.data?.message || 'Gagal menyimpan penyesuaian stok.')
    } finally {
      saving.value = false
    }
  }

  return {
    products,
    summary,
    adjustments,
    loading,
    saving,
    error,
    fetchSummary,
    fetchAdjustments,
    createAdjustment,
  }
})
