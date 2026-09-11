<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

interface OrderItem {
  id: number
  product_id: number
  product_name: string
  quantity: number
  unit_price: number
  subtotal: number
}

interface CashShift {
  id: number
  starting_cash: number
  status: string
}

interface Order {
  id: number
  invoice_number: string
  total_amount: number
  discount_amount: number
  payment_method: string
  created_at: string
  user?: { id: number; name: string }
  cashShift?: CashShift
  items: OrderItem[]
}

const orders    = ref<Order[]>([])
const loading   = ref(false)
const error     = ref<string | null>(null)
const searchQuery = ref('')
const selectedOrder = ref<Order | null>(null)
const showDetailModal = ref(false)

onMounted(() => {
  fetchOrders()
})

async function fetchOrders() {
  loading.value = true
  error.value   = null
  try {
    const res = await api.get('/orders')
    orders.value = res.data
  } catch (e: any) {
    error.value = e.response?.data?.message || e.message || 'Gagal mengambil riwayat transaksi.'
  } finally {
    loading.value = false
  }
}

const filteredOrders = computed(() => {
  if (!searchQuery.value.trim()) return orders.value
  const q = searchQuery.value.toLowerCase().trim()
  return orders.value.filter(o => 
    o.invoice_number.toLowerCase().includes(q) ||
    (o.user?.name && o.user.name.toLowerCase().includes(q))
  )
})

function viewDetail(order: Order) {
  selectedOrder.value = order
  showDetailModal.value = true
}

function fmt(n: number) {
  return 'Rp\u00A0' + Number(n).toLocaleString('id-ID')
}

function formatDate(dtStr: string) {
  if (!dtStr) return '-'
  return new Date(dtStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function printReceipt(order: Order) {
  const payLabel: Record<string, string> = { cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' }

  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Struk - ${order.invoice_number}</title>
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 8px; }
        .center { text-align: center; }
        .bold   { font-weight: bold; }
        .big    { font-size: 14px; }
        .line   { border-top: 1px dashed #000; margin: 6px 0; }
        .row    { display: flex; justify-content: space-between; margin: 3px 0; }
        .row .name { flex: 1; }
        .row .price { text-align: right; white-space: nowrap; margin-left: 8px; }
        .total-row  { display: flex; justify-content: space-between; font-weight: bold; font-size: 13px; margin: 4px 0; }
        .footer { text-align: center; margin-top: 10px; font-size: 11px; }
      </style>
    </head>
    <body>
      <div class="center bold big">DagangYuk</div>
      <div class="center">Point of Sale</div>
      <div class="line"></div>
      <div class="row"><span>No. Invoice</span><span>${order.invoice_number}</span></div>
      <div class="row"><span>Tanggal</span><span>${formatDate(order.created_at)}</span></div>
      <div class="row"><span>Kasir</span><span>${order.user?.name ?? '-'}</span></div>
      <div class="row"><span>Metode</span><span>${payLabel[order.payment_method] ?? order.payment_method}</span></div>
      <div class="line"></div>
      <div class="bold" style="margin-bottom:4px">ITEM PESANAN</div>
      ${order.items.map(item => `
        <div class="row">
          <span class="name">${item.product_name}</span>
        </div>
        <div class="row" style="padding-left:8px">
          <span>${item.quantity} x ${Number(item.unit_price).toLocaleString('id-ID')}</span>
          <span class="price">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</span>
        </div>
      `).join('')}
      <div class="line"></div>
      ${order.discount_amount > 0 ? `
        <div class="row"><span>Diskon</span><span>- Rp ${Number(order.discount_amount).toLocaleString('id-ID')}</span></div>
      ` : ''}
      <div class="total-row">
        <span>TOTAL DIBAYAR</span>
        <span>Rp ${Number(order.total_amount).toLocaleString('id-ID')}</span>
      </div>
      <div class="footer">
        <div class="line"></div>
        <div>CETAK ULANG (RE-PRINT)</div>
        <div>Terima kasih telah berbelanja!</div>
        <div>Powered by DagangYuk</div>
      </div>
    </body>
    </html>
  `

  const win = window.open('', '_blank', 'width=400,height=600')
  if (!win) { alert('Popup diblokir browser. Izinkan popup untuk mencetak.'); return }
  win.document.write(html)
  win.document.close()
  win.focus()
  setTimeout(() => { win.print(); win.close() }, 300)
}
</script>

<template>
  <AppLayout>
    <template #title>Riwayat Pesanan</template>

    <div class="orders-wrap">
      <!-- Top header -->
      <div class="page-hd">
        <div>
          <h1 class="page-title">Riwayat Transaksi POS</h1>
          <p class="page-sub">Lihat dan cetak ulang struk transaksi pelanggan.</p>
        </div>
        <div class="hd-actions">
          <label class="search-box">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input v-model="searchQuery" type="text" placeholder="Cari no. invoice / kasir..." />
          </label>
          <button class="btn-refresh" @click="fetchOrders">↺ Segarkan</button>
        </div>
      </div>

      <!-- Content -->
      <div v-if="loading" class="state-box">
        <span class="spin-lg"></span> Memuat riwayat transaksi...
      </div>
      <div v-else-if="error" class="state-box err">
        <p>{{ error }}</p>
        <button class="btn-retry" @click="fetchOrders">Coba Lagi</button>
      </div>
      <div v-else-if="!filteredOrders.length" class="state-box empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5">
          <rect x="3" y="4" width="18" height="16" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <p>Belum ada transaksi ditemukan.</p>
      </div>
      <div v-else class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>No. Invoice</th>
              <th>Tanggal</th>
              <th>Kasir</th>
              <th>Jumlah Item</th>
              <th>Metode Bayar</th>
              <th>Diskon</th>
              <th>Total Akhir</th>
              <th class="txt-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id">
              <td class="inv-col">{{ order.invoice_number }}</td>
              <td>{{ formatDate(order.created_at) }}</td>
              <td>{{ order.user?.name ?? '-' }}</td>
              <td>{{ order.items.reduce((s, i) => s + i.quantity, 0) }} item</td>
              <td>
                <span class="pay-badge" :class="order.payment_method">
                  {{ order.payment_method.toUpperCase() }}
                </span>
              </td>
              <td>{{ order.discount_amount > 0 ? fmt(order.discount_amount) : '-' }}</td>
              <td class="font-bold">{{ fmt(order.total_amount) }}</td>
              <td class="txt-right actions">
                <button class="btn-view" @click="viewDetail(order)">Lihat Detail</button>
                <button class="btn-print" @click="printReceipt(order)" title="Cetak Struk">🖨️ Struk</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Detail Order -->
    <Teleport to="body">
      <div v-if="showDetailModal && selectedOrder" class="modal-overlay" @click.self="showDetailModal = false">
        <div class="modal-card">
          <div class="modal-hd">
            <div>
              <h2>Detail Transaksi</h2>
              <p class="sub-inv">{{ selectedOrder.invoice_number }}</p>
            </div>
            <button class="modal-x" @click="showDetailModal = false">✕</button>
          </div>
          <div class="modal-bd">
            <div class="meta-grid">
              <div class="meta-item">
                <span class="meta-lbl">Tanggal & Waktu</span>
                <span class="meta-val">{{ formatDate(selectedOrder.created_at) }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-lbl">Kasir</span>
                <span class="meta-val">{{ selectedOrder.user?.name ?? '-' }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-lbl">Metode Pembayaran</span>
                <span class="meta-val uppercase">{{ selectedOrder.payment_method }}</span>
              </div>
            </div>

            <div class="items-head">Daftar Produk</div>
            <div class="items-list">
              <div v-for="item in selectedOrder.items" :key="item.id" class="item-row">
                <div class="item-info">
                  <span class="item-name">{{ item.product_name }}</span>
                  <span class="item-qty">{{ item.quantity }} × {{ fmt(item.unit_price) }}</span>
                </div>
                <span class="item-sub">{{ fmt(item.subtotal) }}</span>
              </div>
            </div>

            <div class="sum-box">
              <div class="sum-row" v-if="selectedOrder.discount_amount > 0">
                <span>Diskon</span>
                <span>- {{ fmt(selectedOrder.discount_amount) }}</span>
              </div>
              <div class="sum-row total">
                <span>Total Tagihan</span>
                <strong>{{ fmt(selectedOrder.total_amount) }}</strong>
              </div>
            </div>
          </div>
          <div class="modal-ft">
            <button class="btn-ghost" @click="showDetailModal = false">Tutup</button>
            <button class="btn-primary" @click="printReceipt(selectedOrder)">
              🖨️ Cetak Struk (PDF)
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<style scoped>
.orders-wrap {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.page-hd {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--ink);
}

.page-sub {
  font-size: 13px;
  color: var(--muted);
}

.hd-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0 12px;
  height: 38px;
}

.search-box input {
  border: none;
  outline: none;
  font-size: 13px;
  background: transparent;
  width: 200px;
}

.btn-refresh {
  height: 38px;
  padding: 0 14px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  font-weight: 500;

  &:hover { border-color: var(--accent); color: var(--accent); }
}

.table-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13.5px;
  text-align: left;
}

.data-table th {
  background: var(--surface);
  padding: 12px 16px;
  font-weight: 600;
  color: var(--muted);
  border-bottom: 1px solid var(--border);
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
}

.inv-col {
  font-weight: 700;
  font-family: monospace;
  color: var(--accent);
}

.pay-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;

  &.cash { background: #dcfce7; color: #15803d; }
  &.qris { background: #e0e7ff; color: #4338ca; }
  &.transfer { background: #fef3c7; color: #b45309; }
}

.txt-right { text-align: right; }
.font-bold { font-weight: 700; }
.uppercase { text-transform: uppercase; }

.actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn-view {
  padding: 6px 12px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  font-weight: 600;
  &:hover { border-color: var(--accent); color: var(--accent); }
}

.btn-print {
  padding: 6px 12px;
  background: var(--accent-bg);
  border: 1px solid var(--accent);
  color: var(--accent);
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  font-weight: 600;
  &:hover { background: var(--accent); color: #fff; }
}

.state-box {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 48px;
  text-align: center;
  color: var(--muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-card {
  background: var(--white);
  border-radius: 14px;
  width: 100%;
  max-width: 480px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-hd {
  padding: 18px 20px;
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;

  h2 { font-size: 17px; font-weight: 700; }
  .sub-inv { font-size: 12px; font-family: monospace; color: var(--muted); }
}

.modal-x {
  background: transparent;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: var(--muted);
}

.modal-bd {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.meta-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  background: var(--surface);
  padding: 12px;
  border-radius: 8px;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.meta-lbl { font-size: 11px; color: var(--muted); }
.meta-val { font-size: 12.5px; font-weight: 600; }

.items-head {
  font-size: 12px;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 200px;
  overflow-y: auto;
}

.item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
}

.item-info {
  display: flex;
  flex-direction: column;
}

.item-name { font-weight: 600; }
.item-qty { font-size: 11.5px; color: var(--muted); }
.item-sub { font-weight: 700; }

.sum-box {
  border-top: 1px dashed var(--border);
  padding-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.sum-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  &.total { font-size: 15px; font-weight: 700; border-top: 1px solid var(--border); padding-top: 8px; margin-top: 4px; }
}

.modal-ft {
  padding: 14px 20px;
  background: var(--surface);
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn-ghost {
  padding: 8px 16px;
  border: 1px solid var(--border);
  background: var(--white);
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
}

.btn-primary {
  padding: 8px 18px;
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  &:hover { opacity: 0.9; }
}
</style>
