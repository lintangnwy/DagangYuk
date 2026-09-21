<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

const auth = useAuthStore()

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
  payment_method: string
  created_at: string
  user?: { id: number; name: string }
  items: OrderItem[]
}

const orders  = ref<Order[]>([])
const loading = ref(true)
const error   = ref('')
const search  = ref('')
const expandedId = ref<number | null>(null)

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return orders.value
  return orders.value.filter(o =>
    o.invoice_number.toLowerCase().includes(q) ||
    o.user?.name?.toLowerCase().includes(q)
  )
})

async function load() {
  loading.value = true; error.value = ''
  try {
    const res = await api.get('/orders')
    orders.value = res.data
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Gagal memuat riwayat.'
  } finally { loading.value = false }
}

function toggle(id: number) {
  expandedId.value = expandedId.value === id ? null : id
}

function fmt(n: number) { return 'Rp\u00A0' + n.toLocaleString('id-ID') }

function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function payLabel(m: string) {
  return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' } as Record<string, string>)[m] ?? m
}

function printOrder(order: Order) {
  const html = `
    <!DOCTYPE html><html><head><meta charset="UTF-8">
    <title>Struk ${order.invoice_number}</title>
    <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 8px; }
      .c { text-align: center; } .b { font-weight: bold; } .line { border-top: 1px dashed #000; margin: 6px 0; }
      .row { display: flex; justify-content: space-between; margin: 3px 0; }
    </style></head><body>
    <div class="c b" style="font-size:14px">DagangYuk</div>
    <div class="c">Point of Sale</div>
    <div class="line"></div>
    <div class="row"><span>Invoice</span><span>${order.invoice_number}</span></div>
    <div class="row"><span>Tanggal</span><span>${fmtDate(order.created_at)}</span></div>
    <div class="row"><span>Kasir</span><span>${order.user?.name ?? '-'}</span></div>
    <div class="row"><span>Metode</span><span>${payLabel(order.payment_method)}</span></div>
    <div class="line"></div>
    ${order.items.map(i => `
      <div class="row"><span>${i.product_name}</span></div>
      <div class="row" style="padding-left:8px">
        <span>${i.quantity}x${i.unit_price.toLocaleString('id-ID')}</span>
        <span>Rp ${i.subtotal.toLocaleString('id-ID')}</span>
      </div>`).join('')}
    <div class="line"></div>
    <div class="row b"><span>TOTAL</span><span>Rp ${order.total_amount.toLocaleString('id-ID')}</span></div>
    <div class="c" style="margin-top:10px;font-size:11px">Terima kasih!</div>
    </body></html>`
  const w = window.open('', '_blank', 'width=400,height=550')
  if (w) { w.document.write(html); w.document.close(); setTimeout(() => { w.print(); w.close() }, 300) }
}

onMounted(async () => { await auth.fetchUser(); load() })
</script>

<template>
  <AppLayout>
    <template #title>Riwayat Pesanan</template>

    <div class="page-head">
      <div>
        <h1>Riwayat Pesanan</h1>
        <p>Semua transaksi yang telah diproses</p>
      </div>
      <button class="btn-refresh" @click="load" :disabled="loading">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :style="loading ? 'animation:spin .7s linear infinite' : ''">
          <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
          <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
        </svg>
        Refresh
      </button>
    </div>

    <div v-if="error" class="alert-err">{{ error }}</div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="search-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input v-model="search" type="search" placeholder="Cari invoice atau nama kasir…" />
      </div>
      <span class="count-txt">{{ filtered.length }} transaksi</span>
    </div>

    <!-- Table -->
    <div class="tcard">
      <div v-if="loading" class="empty-state">Memuat riwayat...</div>
      <div v-else-if="!filtered.length" class="empty-state">
        {{ search ? 'Tidak ditemukan.' : 'Belum ada transaksi.' }}
      </div>

      <div v-else>
        <div v-for="order in filtered" :key="order.id" class="order-row">
          <!-- Header baris -->
          <div class="order-header" @click="toggle(order.id)">
            <div class="order-inv">
              <span class="inv-num">{{ order.invoice_number }}</span>
              <span class="inv-date">{{ fmtDate(order.created_at) }}</span>
            </div>
            <div class="order-meta">
              <span class="pay-badge" :class="order.payment_method">{{ payLabel(order.payment_method) }}</span>
              <span class="kasir-name">{{ order.user?.name ?? '—' }}</span>
              <span class="total-amt">{{ fmt(order.total_amount) }}</span>
              <button class="print-btn" @click.stop="printOrder(order)" title="Cetak Struk">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 6 2 18 2 18 9"/>
                  <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                  <rect x="6" y="14" width="12" height="8"/>
                </svg>
              </button>
              <span class="expand-icon" :class="{ open: expandedId === order.id }">▾</span>
            </div>
          </div>

          <!-- Detail item (collapsible) -->
          <Transition name="expand">
            <div v-if="expandedId === order.id" class="order-items">
              <table>
                <thead>
                  <tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                  <tr v-for="item in order.items" :key="item.id">
                    <td>{{ item.product_name }}</td>
                    <td class="center">{{ item.quantity }}</td>
                    <td>{{ fmt(item.unit_price) }}</td>
                    <td class="fw">{{ fmt(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3" class="total-label">Total</td>
                    <td class="fw accent">{{ fmt(order.total_amount) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </Transition>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
.page-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.page-head h1 { font-size: 20px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13px; color: var(--muted); margin-top: 2px; }

.btn-refresh {
  display: flex; align-items: center; gap: 6px;
  background: none; border: 1.5px solid var(--border); color: var(--muted);
  padding: 8px 14px; border-radius: 8px; font-size: 13px; cursor: pointer;
  transition: border-color .15s, color .15s;
}
.btn-refresh:hover { border-color: var(--accent); color: var(--accent); }
.btn-refresh:disabled { opacity: .5; cursor: not-allowed; }

.alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 13px; padding: 10px 14px; border-radius: 7px; margin-bottom: 14px; }

.toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.search-box { display: flex; align-items: center; gap: 8px; background: var(--white); border: 1px solid var(--border); border-radius: 8px; padding: 0 12px; max-width: 320px; flex: 1; transition: border-color .15s; }
.search-box:focus-within { border-color: var(--accent); }
.search-box svg { color: #9ca3af; flex-shrink: 0; }
.search-box input { border: none; outline: none; font-size: 13px; background: transparent; color: var(--ink); height: 38px; width: 100%; }
.search-box input::placeholder { color: #d1d5db; }
.count-txt { font-size: 13px; color: var(--muted); white-space: nowrap; }

.tcard { background: var(--white); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.empty-state { text-align: center; padding: 52px; color: #d1d5db; font-size: 14px; }

/* Order rows */
.order-row { border-bottom: 1px solid var(--border); }
.order-row:last-child { border-bottom: none; }

.order-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px; cursor: pointer; transition: background .12s; gap: 12px;
  flex-wrap: wrap;
}
.order-header:hover { background: var(--surface); }

.order-inv { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.inv-num  { font-size: 13.5px; font-weight: 700; color: var(--ink); font-family: monospace; }
.inv-date { font-size: 11.5px; color: var(--muted); }

.order-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

.pay-badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 600; }
.pay-badge.cash     { background: var(--accent-bg); color: var(--accent-dark); }
.pay-badge.qris     { background: #ede9fe; color: #7c3aed; }
.pay-badge.transfer { background: #fef3c7; color: #d97706; }

.kasir-name { font-size: 12.5px; color: var(--muted); }
.total-amt  { font-size: 14px; font-weight: 800; color: var(--ink); white-space: nowrap; }

.print-btn {
  background: none; border: 1px solid var(--border); color: var(--muted);
  width: 30px; height: 30px; border-radius: 6px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: all .15s;
}
.print-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-bg); }

.expand-icon { font-size: 14px; color: var(--muted); transition: transform .2s; display: inline-block; }
.expand-icon.open { transform: rotate(180deg); }

/* Detail items */
.order-items {
  border-top: 1px solid var(--border);
  background: var(--surface);
  padding: 0 18px 14px;
  overflow: hidden;
}

.order-items table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.order-items th {
  text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .5px; color: var(--muted); padding: 0 0 8px;
  border-bottom: 1px solid var(--border);
}
.order-items td { padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13.5px; }
.order-items tr:last-child td { border-bottom: none; }
.order-items tfoot td { font-size: 14px; padding-top: 10px; border-top: 1px solid var(--border); }

.center { text-align: center; }
.fw { font-weight: 700; }
.total-label { color: var(--muted); font-size: 13px; }
.accent { color: var(--accent); font-size: 15px; }

/* Expand animation */
.expand-enter-active { transition: all .22s ease-out; }
.expand-leave-active  { transition: all .18s ease-in; }
.expand-enter-from   { opacity: 0; max-height: 0; }
.expand-leave-to     { opacity: 0; max-height: 0; }

@keyframes spin { to { transform: rotate(360deg); } }
</style>
