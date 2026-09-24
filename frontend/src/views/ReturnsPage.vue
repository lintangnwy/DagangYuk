<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

interface OrderItem {
  id: number
  product_name: string
  quantity: number
  unit_price: number
  subtotal: number
}

interface Order {
  id: number
  invoice_number: string
  total_amount: number
  payment_status: string
  created_at: string
  items: OrderItem[]
}

const orders = ref<Order[]>([])
const loading = ref(false)
const searchQuery = ref('')
const selectedOrder = ref<Order | null>(null)
const returnReason = ref('')
const itemCondition = ref<'sellable' | 'damaged'>('sellable')
const returning = ref(false)

const filteredOrders = computed(() => {
  const q = searchQuery.value.toLowerCase()
  if (!q) return orders.value
  return orders.value.filter(o => o.invoice_number.toLowerCase().includes(q))
})

onMounted(async () => {
  await fetchOrders()
})

async function fetchOrders() {
  loading.value = true
  try {
    const res = await api.get('/orders')
    orders.value = res.data
  } catch (e: any) {
    alert('Gagal memuat riwayat pesanan.')
  } finally {
    loading.value = false
  }
}

async function processReturn() {
  if (!selectedOrder.value) return
  if (!returnReason.value.trim()) {
    alert('Mohon masukkan alasan return.')
    return
  }

  if (!confirm(`Proses return untuk invoice ${selectedOrder.value.invoice_number}? Stok produk akan dikembalikan.`)) return

  returning.value = true
  try {
    await api.post('/returns/process', {
      order_id: selectedOrder.value.id,
      reason: returnReason.value,
      item_condition: itemCondition.value,
    })
    alert('Return pesanan berhasil diproses. Stok telah dikembalikan sesuai kondisi barang.')
    selectedOrder.value = null
    returnReason.value = ''
    itemCondition.value = 'sellable'
    await fetchOrders()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal memproses return.')
  } finally {
    returning.value = false
  }
}

function fmt(n: number) {
  return 'Rp\u00A0' + Number(n).toLocaleString('id-ID')
}

function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}
</script>

<template>
  <AppLayout>
    <template #title>Return Pesanan</template>

    <div class="page-head">
      <div>
        <h1>Return & Pembatalan</h1>
        <p>Proses pengembalian dana dan stok untuk pesanan.</p>
      </div>
      <button class="btn-refresh" @click="fetchOrders" :disabled="loading">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :style="loading ? 'animation:spin .7s linear infinite' : ''">
          <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
          <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
        </svg>
        Refresh
      </button>
    </div>

    <div class="toolbar">
      <div class="search-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input v-model="searchQuery" type="search" placeholder="Cari nomor invoice..." />
      </div>
    </div>

    <div v-if="loading" class="state-center">
      <span class="spin-ring"></span> Memuat pesanan...
    </div>

    <div v-else-if="!filteredOrders.length" class="empty-state tcard">
      <p>Tidak ada pesanan ditemukan.</p>
    </div>

    <div v-else class="orders-grid">
      <div v-for="order in filteredOrders" :key="order.id" class="order-card">
        <div class="oc-hd">
          <div class="oc-inv">
            <span class="fw mono">{{ order.invoice_number }}</span>
            <span class="muted-sm">{{ fmtDate(order.created_at) }}</span>
          </div>
          <span class="status-badge" :class="order.payment_status">
            {{ order.payment_status === 'paid' ? 'Lunas' : order.payment_status === 'refunded' ? 'Direfund' : 'Tertunda' }}
          </span>
        </div>
        
        <div class="oc-items">
          <div v-for="item in order.items" :key="item.id" class="oc-item">
            <span class="item-name">{{ item.product_name }} <span class="muted">×{{ item.quantity }}</span></span>
            <span class="item-sub">{{ fmt(item.subtotal) }}</span>
          </div>
        </div>

        <div class="oc-ft">
          <div class="oc-total">
            <span class="muted-sm">Total</span>
            <span class="fw accent">{{ fmt(order.total_amount) }}</span>
          </div>
                <button v-if="order.payment_status === 'paid'" class="btn-return" @click="selectedOrder = order">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                  </svg>
                  Return
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Return -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="selectedOrder" class="modal-backdrop" @click.self="selectedOrder = null">
          <div class="modal-box">
            <div class="modal-header">
              <div class="modal-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                </svg>
                Return Pesanan
              </div>
              <button class="modal-close" @click="selectedOrder = null">✕</button>
            </div>
            <div class="modal-body">
              <div class="return-info">
                <div class="info-row">
                  <span class="muted">Invoice</span>
                  <strong class="mono">{{ selectedOrder.invoice_number }}</strong>
                </div>
                <div class="info-row">
                  <span class="muted">Total</span>
                  <strong>{{ fmt(selectedOrder.total_amount) }}</strong>
                </div>
              </div>
              
              <div class="alert-warn">
                Menyetujui return akan mengembalikan dana dan <strong>memulihkan stok produk</strong> ke inventaris.
              </div>

              <div class="field">
                <label>Kondisi Barang <span class="req">*</span></label>
                <select v-model="itemCondition" class="form-select">
                  <option value="sellable">Masih Bagus (Kembalikan ke stok layak jual)</option>
                  <option value="damaged">Rusak / Expired (Masukkan ke gudang barang rusak)</option>
                </select>
              </div>

              <div class="field">
                <label>Alasan Return <span class="req">*</span></label>
                <textarea v-model="returnReason" rows="3" placeholder="Contoh: Barang cacat, pelanggan batal..."></textarea>
              </div>

              <div class="modal-actions">
                <button class="btn-cancel" @click="selectedOrder = null" :disabled="returning">Batal</button>
                <button class="btn-danger" @click="processReturn" :disabled="returning">
                  {{ returning ? 'Memproses...' : 'Konfirmasi Return' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AppLayout>
</template>

<style scoped>
.page-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.page-head h1 { font-size: 20px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13.5px; color: var(--muted); margin-top: 4px; }

.btn-refresh {
  display: flex; align-items: center; gap: 6px;
  background: var(--white); border: 1.5px solid var(--border); color: var(--ink);
  padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all .15s;
}
.btn-refresh:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
.btn-refresh:disabled { opacity: .5; cursor: not-allowed; }

.toolbar { margin-bottom: 20px; }
.search-box {
  display: flex; align-items: center; gap: 8px;
  background: var(--white); border: 1px solid var(--border);
  border-radius: 8px; padding: 0 12px; max-width: 320px;
  transition: border-color .15s; height: 40px;
}
.search-box:focus-within { border-color: var(--accent); }
.search-box svg { color: #9ca3af; flex-shrink: 0; }
.search-box input { border: none; outline: none; font-size: 13.5px; background: transparent; color: var(--ink); width: 100%; height: 100%; }

.state-center { display: flex; align-items: center; gap: 10px; justify-content: center; padding: 64px; color: var(--muted); }
.spin-ring { display: inline-block; width: 24px; height: 24px; border: 2.5px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.tcard { background: var(--white); border: 1px solid var(--border); border-radius: 12px; }
.empty-state { text-align: center; padding: 64px 20px; color: var(--muted); font-size: 14px; }

.orders-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.order-card { background: var(--white); border: 1px solid var(--border); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; transition: box-shadow .15s; }
.order-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }

.oc-hd { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 10px; }
.oc-inv { display: flex; flex-direction: column; gap: 2px; }
.mono { font-family: monospace; }
.fw { font-weight: 700; color: var(--ink); }
.muted { color: var(--muted); }
.muted-sm { color: var(--muted); font-size: 11.5px; }
.accent { color: var(--accent); }

.status-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }
.status-badge.paid { background: #dcfce7; color: #166534; }
.status-badge.refunded { background: #fee2e2; color: #991b1b; }
.status-badge.pending { background: #fef9c3; color: #854d0e; }

.oc-items { background: var(--surface); padding: 12px; border-radius: 8px; margin-bottom: 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.oc-item { display: flex; justify-content: space-between; font-size: 13px; }
.item-name { color: var(--ink); }
.item-sub { font-weight: 600; color: var(--ink); }

.oc-ft { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 14px; }
.oc-total { display: flex; flex-direction: column; }
.oc-total .fw { font-size: 15px; }

.btn-return {
  display: flex; align-items: center; gap: 6px;
  background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;
  padding: 6px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 600;
  cursor: pointer; transition: all .15s;
}
.btn-return:hover { background: #fee2e2; border-color: #dc2626; }

/* Modal */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 16px; }
.modal-box { background: var(--white); border-radius: 14px; width: 100%; max-width: 440px; box-shadow: 0 24px 60px rgba(0,0,0,.18); overflow: hidden; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border); }
.modal-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; color: var(--ink); }
.modal-close { background: none; border: none; color: var(--muted); font-size: 16px; cursor: pointer; padding: 4px 8px; border-radius: 6px; }
.modal-close:hover { background: var(--surface); }

.modal-body { padding: 22px; display: flex; flex-direction: column; gap: 16px; }
.return-info { background: var(--surface); border-radius: 8px; padding: 12px 16px; }
.info-row { display: flex; justify-content: space-between; font-size: 13.5px; padding: 4px 0; }

.alert-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 12.5px; padding: 10px 14px; border-radius: 8px; }

.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13px; font-weight: 600; color: var(--ink); }
.req { color: #dc2626; }
.field textarea {
  width: 100%; padding: 10px 12px; border: 1px solid var(--border);
  border-radius: 8px; font-size: 13.5px; color: var(--ink);
  background: var(--white); outline: none; transition: border-color .15s;
  font-family: inherit; resize: vertical; box-sizing: border-box;
}
.form-select {
  width: 100%; padding: 10px 12px; border: 1px solid var(--border);
  border-radius: 8px; font-size: 13.5px; color: var(--ink);
  background: var(--white); outline: none; transition: border-color .15s;
}
.field textarea:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }

.modal-actions { display: flex; gap: 10px; margin-top: 8px; }
.btn-cancel { flex: 1; height: 40px; background: none; border: 1.5px solid var(--border); color: var(--muted); border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-cancel:hover { border-color: var(--ink); color: var(--ink); }
.btn-danger { flex: 2; height: 40px; background: #dc2626; border: none; color: white; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-danger:hover:not(:disabled) { background: #b91c1c; }
.btn-danger:disabled { opacity: .6; cursor: not-allowed; }

.modal-fade-enter-active { transition: opacity .2s ease; }
.modal-fade-leave-active { transition: opacity .15s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>