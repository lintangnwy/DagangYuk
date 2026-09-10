import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAuthStore } from './auth'

const API = 'http://localhost:8000/api'

export interface Product {
  id: number
  name: string
  price: number
  stock: number
  category_id: number | null
  category?: { id: number; name: string }
  sku?: string
  image?: string | null
}

export interface CartItem {
  product: Product
  qty: number
}

export interface CashShift {
  id: number
  tenant_id: number
  user_id: number
  starting_cash: number
  ending_cash: number | null
  expected_cash: number | null
  status: 'open' | 'closed'
  opened_at: string
  closed_at: string | null
}

export const usePosStore = defineStore('pos', () => {
  const auth = useAuthStore()

  const products    = ref<Product[]>([])
  const cart        = ref<CartItem[]>([])
  const loading     = ref(false)
  const error       = ref<string | null>(null)
  const activeShift = ref<CashShift | null>(null)
  const shiftLoading = ref(false)

  // ── Computed ──────────────────────────────────────
  const subtotal  = computed(() => cart.value.reduce((s, i) => s + i.product.price * i.qty, 0))
  const tax       = computed(() => Math.round(subtotal.value * 0.08))
  const total     = computed(() => subtotal.value + tax.value)
  const cartCount = computed(() => cart.value.reduce((s, i) => s + i.qty, 0))
  const hasShift  = computed(() => !!activeShift.value)

  function h(): HeadersInit {
    return {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      Authorization: `Bearer ${auth.token}`,
    }
  }

  // ── Products ──────────────────────────────────────
  async function fetchProducts() {
    loading.value = true; error.value = null
    try {
      const res = await fetch(`${API}/products`, { headers: h() })
      if (!res.ok) throw new Error('Gagal memuat produk.')
      products.value = await res.json()
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Error.'
    } finally { loading.value = false }
  }

  // ── Shift ─────────────────────────────────────────
  async function fetchActiveShift() {
    shiftLoading.value = true
    try {
      const res = await fetch(`${API}/cash-shifts`, { headers: h() })
      if (!res.ok) return
      const shifts: CashShift[] = await res.json()
      // Cari shift milik user ini yang statusnya open
      activeShift.value = shifts.find(
        s => s.status === 'open' && s.user_id === auth.user?.id
      ) ?? null
    } catch { /* ignore */ }
    finally { shiftLoading.value = false }
  }

  async function openShift(startingCash: number): Promise<void> {
    const tenantId = auth.user?.tenant_id ?? 1
    const res = await fetch(`${API}/cash-shifts`, {
      method: 'POST',
      headers: h(),
      body: JSON.stringify({ tenant_id: tenantId, starting_cash: startingCash }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message ?? 'Gagal membuka shift.')
    activeShift.value = data.data
  }

  async function closeShift(endingCash: number): Promise<void> {
    if (!activeShift.value) throw new Error('Tidak ada shift aktif.')
    const res = await fetch(`${API}/cash-shifts/${activeShift.value.id}/close`, {
      method: 'PUT',
      headers: h(),
      body: JSON.stringify({ ending_cash: endingCash }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message ?? 'Gagal menutup shift.')
    activeShift.value = null
  }

  // ── Cart ──────────────────────────────────────────
  function addToCart(product: Product) {
    if (product.stock <= 0) return
    const existing = cart.value.find(i => i.product.id === product.id)
    if (existing) {
      if (existing.qty < product.stock) existing.qty++
    } else {
      cart.value.push({ product, qty: 1 })
    }
  }

  function changeQty(item: CartItem, delta: number) {
    item.qty += delta
    if (item.qty <= 0) {
      cart.value = cart.value.filter(i => i !== item)
    } else if (item.qty > item.product.stock) {
      item.qty = item.product.stock
    }
  }

  function clearCart() { cart.value = [] }

  // ── Checkout ──────────────────────────────────────
  async function checkout(paymentMethod: 'cash' | 'qris' | 'transfer') {
    if (!cart.value.length) throw new Error('Keranjang kosong.')
    if (!activeShift.value) throw new Error('Buka shift kasir terlebih dahulu.')

    const body = {
      cash_shift_id:  activeShift.value.id,
      payment_method: paymentMethod,
      products: cart.value.map(i => ({
        product_id: i.product.id,
        quantity:   i.qty,
      })),
    }

    const res = await fetch(`${API}/orders`, {
      method: 'POST',
      headers: h(),
      body: JSON.stringify(body),
    })

    const data = await res.json()
    if (!res.ok) throw new Error(data.message ?? 'Transaksi gagal.')

    // Update stok lokal
    cart.value.forEach(item => {
      const p = products.value.find(p => p.id === item.product.id)
      if (p) p.stock -= item.qty
    })

    clearCart()
    return data
  }

  return {
    products, cart, loading, error,
    activeShift, shiftLoading, hasShift,
    subtotal, tax, total, cartCount,
    fetchProducts, fetchActiveShift, openShift, closeShift,
    addToCart, changeQty, clearCart, checkout,
  }
})
