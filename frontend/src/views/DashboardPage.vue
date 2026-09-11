<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/AppLayout.vue'

const auth   = useAuthStore()
const router = useRouter()
const API    = 'http://localhost:8000/api'

interface ChartDay    { date: string; label: string; count: number; revenue: number }
interface TopProduct  { name: string; total_qty: number; total_revenue: number }
interface RecentOrder {
  id: number; invoice_number: string; total_amount: number
  payment_method: string; created_at: string
  user?: { id: number; name: string }
}
interface DashData {
  today:        { revenue: number; orders: number }
  this_month:   { revenue: number; orders: number }
  products:     { total: number; low_stock: number; out: number }
  chart:        ChartDay[]
  top_products: TopProduct[]
  recent_orders: RecentOrder[]
}

const data    = ref<DashData | null>(null)
const loading = ref(true)
const error   = ref('')

async function load() {
  loading.value = true; error.value = ''
  try {
    const res = await fetch(`${API}/dashboard`, {
      headers: { Authorization: `Bearer ${auth.token}`, Accept: 'application/json' },
    })
    if (!res.ok) throw new Error('Gagal memuat data.')
    data.value = await res.json()
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Error.'
  } finally { loading.value = false }
}

function barMax() {
  if (!data.value?.chart.length) return 1
  return Math.max(...data.value.chart.map(d => d.revenue), 1)
}
function barH(rev: number) { return Math.max(4, (rev / barMax()) * 100) }

function fmt(n: number) {
  if (n >= 1_000_000) return 'Rp\u00A0' + (n / 1_000_000).toFixed(1) + ' jt'
  if (n >= 1_000)     return 'Rp\u00A0' + (n / 1_000).toFixed(0) + ' rb'
  return 'Rp\u00A0' + n.toLocaleString('id-ID')
}
function fmtFull(n: number) { return 'Rp\u00A0' + n.toLocaleString('id-ID') }
function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}
function payLabel(m: string) {
  return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' } as Record<string, string>)[m] ?? m
}

onMounted(async () => { await auth.fetchUser(); load() })
</script>

<template>
  <AppLayout>
    <template #title>Dashboard</template>

    <!-- Header -->
    <div class="dash-head">
      <div>
        <h1>Dashboard</h1>
        <p>Selamat datang, <strong>{{ auth.user?.name }}</strong> —
          {{ new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) }}
        </p>
      </div>
      <div class="head-btns">
        <button class="btn-outline" @click="load" :disabled="loading">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
            :style="loading ? 'animation:spin .7s linear infinite' : ''">
            <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
            <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
          </svg>
          Refresh
        </button>
        <button class="btn-primary" @click="router.push('/pos')">
          + Buat Transaksi
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="state-center">
      <span class="spin-ring"></span> Memuat...
    </div>
    <div v-else-if="error" class="state-center err">{{ error }}</div>

    <template v-else-if="data">
      <!-- Stat cards -->
      <div class="stats-grid">
        <div class="scard accent">
          <div class="scard-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
          </div>
          <div>
            <p class="scard-label">Pendapatan Hari Ini</p>
            <p class="scard-value">{{ fmtFull(data.today.revenue) }}</p>
            <p class="scard-sub">{{ data.today.orders }} transaksi</p>
          </div>
        </div>

        <div class="scard">
          <div class="scard-icon muted">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
          </div>
          <div>
            <p class="scard-label">Bulan Ini</p>
            <p class="scard-value">{{ fmtFull(data.this_month.revenue) }}</p>
            <p class="scard-sub">{{ data.this_month.orders }} transaksi</p>
          </div>
        </div>

        <template v-if="auth.isSuperAdmin && data.tenants">
          <div class="scard">
            <div class="scard-icon muted">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
              </svg>
            </div>
            <div>
              <p class="scard-label">Total Tenant</p>
              <p class="scard-value">{{ data.tenants.total }}</p>
              <p class="scard-sub">{{ data.tenants.active }} aktif</p>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="scard">
            <div class="scard-icon muted">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
              </svg>
            </div>
            <div>
              <p class="scard-label">Total Produk</p>
              <p class="scard-value">{{ data.products.total }}</p>
              <p class="scard-sub">{{ data.products.out }} habis stok</p>
            </div>
          </div>
        </template>

        <div class="scard" :class="data.products.low_stock > 0 ? 'warn' : ''">
          <div class="scard-icon" :class="data.products.low_stock > 0 ? 'warn-i' : 'muted'">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div>
            <p class="scard-label">Stok Menipis</p>
            <p class="scard-value">{{ data.products.low_stock }}</p>
            <p class="scard-sub">produk stok &lt; 5</p>
          </div>
        </div>
      </div>

      <!-- Chart + Top -->
      <div class="mid-grid">
        <div class="card">
          <div class="card-head"><h3>Penjualan 7 Hari Terakhir</h3></div>
          <div class="chart-area">
            <div class="bars">
              <div v-for="d in data.chart" :key="d.date" class="bar-col"
                :title="`${d.label}: ${fmtFull(d.revenue)}`">
                <span class="bval">{{ d.revenue > 0 ? fmt(d.revenue) : '' }}</span>
                <div class="btrack"><div class="bfill" :style="`height:${barH(d.revenue)}%`"></div></div>
                <span class="blabel">{{ d.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-head"><h3>Produk Terlaris</h3></div>
          <div v-if="!data.top_products.length" class="card-empty">Belum ada transaksi</div>
          <div v-else>
            <div v-for="(p,i) in data.top_products" :key="p.name" class="top-row">
              <span class="top-num">{{ i+1 }}</span>
              <div class="top-info">
                <p class="top-name">{{ p.name }}</p>
                <p class="top-sub">{{ p.total_qty }} terjual</p>
              </div>
              <span class="top-rev">{{ fmt(p.total_revenue) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Transaksi terbaru -->
      <div class="card">
        <div class="card-head">
          <h3>Transaksi Terbaru</h3>
          <button class="btn-link" @click="router.push('/pos')">Lihat semua →</button>
        </div>
        <div v-if="!data.recent_orders.length" class="card-empty">Belum ada transaksi</div>
        <div v-else class="table-wrap">
          <table>
            <thead><tr><th>Invoice</th><th>Kasir</th><th>Metode</th><th>Total</th><th>Waktu</th></tr></thead>
            <tbody>
              <tr v-for="o in data.recent_orders" :key="o.id">
                <td class="mono">{{ o.invoice_number }}</td>
                <td>{{ o.user?.name ?? '—' }}</td>
                <td><span class="pay-b" :class="o.payment_method">{{ payLabel(o.payment_method) }}</span></td>
                <td class="fw">{{ fmtFull(o.total_amount) }}</td>
                <td class="muted">{{ fmtDate(o.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

  </AppLayout>
</template>

<style scoped>
.dash-head {
  display: flex; align-items: flex-start;
  justify-content: space-between; flex-wrap: wrap;
  gap: 12px; margin-bottom: 24px;
}
.dash-head h1 { font-size: 22px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.dash-head p  { font-size: 13px; color: var(--muted); margin-top: 3px; }
.dash-head strong { color: var(--ink); }

.head-btns { display: flex; gap: 10px; align-items: center; }

.btn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 8px 18px; border-radius: 8px; font-size: 13.5px;
  font-weight: 600; cursor: pointer; transition: background .15s;
}
.btn-primary:hover { background: var(--accent-dark); }

.btn-outline {
  display: flex; align-items: center; gap: 6px;
  background: none; border: 1.5px solid var(--border);
  color: var(--muted); padding: 8px 14px; border-radius: 8px;
  font-size: 13px; cursor: pointer; transition: border-color .15s, color .15s;
}
.btn-outline:hover { border-color: var(--accent); color: var(--accent); }
.btn-outline:disabled { opacity: .5; cursor: not-allowed; }

/* State */
.state-center {
  display: flex; align-items: center; gap: 10px;
  justify-content: center; padding: 64px; color: var(--muted);
}
.state-center.err { color: #ef4444; }

.spin-ring {
  display: inline-block; width: 20px; height: 20px;
  border: 2.5px solid var(--border); border-top-color: var(--accent);
  border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Stat cards */
.stats-grid {
  display: grid; grid-template-columns: repeat(4,1fr);
  gap: 14px; margin-bottom: 20px;
}

.scard {
  background: var(--white); border: 1px solid var(--border);
  border-radius: 12px; padding: 18px 20px;
  display: flex; gap: 14px; align-items: flex-start;
  transition: box-shadow .15s;
}
.scard:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
.scard.accent { border-color: var(--accent-ring); background: var(--accent-bg); }
.scard.warn   { border-color: #fde68a; background: #fffbeb; }

.scard-icon {
  width: 38px; height: 38px; border-radius: 9px;
  background: var(--accent-bg); color: var(--accent);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: background .3s, color .3s;
}
.scard-icon.muted   { background: var(--surface); color: var(--muted); }
.scard-icon.warn-i  { background: #fef3c7; color: #d97706; }

.scard-label { font-size: 11.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
.scard-value { font-size: 22px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); line-height: 1.25; margin-top: 2px; }
.scard.accent .scard-value { color: var(--accent-dark); }
.scard-sub   { font-size: 12px; color: var(--muted); margin-top: 2px; }

/* Card */
.card {
  background: var(--white); border: 1px solid var(--border);
  border-radius: 12px; overflow: hidden; margin-bottom: 0;
}

.card-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px; border-bottom: 1px solid var(--border);
}
.card-head h3 { font-size: 14.5px; font-weight: 700; color: var(--ink); }

.btn-link { background: none; border: none; font-size: 13px; color: var(--accent); cursor: pointer; font-weight: 500; }
.btn-link:hover { text-decoration: underline; }

.card-empty { padding: 28px; text-align: center; color: #d1d5db; font-size: 13px; }

.mid-grid { display: grid; grid-template-columns: 1fr 320px; gap: 14px; margin-bottom: 20px; }

/* Chart */
.chart-area { padding: 16px 20px 8px; height: 200px; display: flex; align-items: flex-end; }
.bars { display: flex; align-items: flex-end; gap: 8px; width: 100%; height: 100%; }
.bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; height: 100%; cursor: default; }
.bval   { font-size: 10px; color: var(--muted); white-space: nowrap; min-height: 14px; }
.btrack { flex: 1; width: 100%; background: var(--surface); border-radius: 6px; display: flex; align-items: flex-end; overflow: hidden; }
.bfill  { width: 100%; background: var(--accent); border-radius: 6px 6px 0 0; transition: height .5s cubic-bezier(.34,1.56,.64,1); opacity: .85; }
.blabel { font-size: 11px; color: var(--muted); font-weight: 500; }

/* Top */
.top-row {
  display: flex; align-items: center; gap: 12px;
  padding: 11px 20px; border-bottom: 1px solid var(--border);
  transition: background .15s;
}
.top-row:last-child { border-bottom: none; }
.top-row:hover { background: var(--surface); }
.top-num  { width: 22px; height: 22px; border-radius: 50%; background: var(--accent-bg); color: var(--accent); font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.top-info { flex: 1; min-width: 0; }
.top-name { font-size: 13px; font-weight: 600; color: var(--ink); }
.top-sub  { font-size: 11.5px; color: var(--muted); }
.top-rev  { font-size: 13px; font-weight: 700; color: var(--ink); white-space: nowrap; }

/* Table */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--surface); }
th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); padding: 10px 20px; border-bottom: 1px solid var(--border); }
td { padding: 11px 20px; border-bottom: 1px solid var(--border); font-size: 13.5px; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface); }
.mono  { font-family: monospace; font-size: 12px; color: var(--muted); }
.fw    { font-weight: 700; }
.muted { color: var(--muted); font-size: 12px; }
.pay-b { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
.pay-b.cash     { background: var(--accent-bg); color: var(--accent-dark); }
.pay-b.qris     { background: #ede9fe; color: #7c3aed; }
.pay-b.transfer { background: #fef3c7; color: #d97706; }

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid { grid-template-columns: repeat(2,1fr); }
  .mid-grid   { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .stats-grid { grid-template-columns: 1fr 1fr; }
  th:nth-child(2), td:nth-child(2) { display: none; }
}
</style>
