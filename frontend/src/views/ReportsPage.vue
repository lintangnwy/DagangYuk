<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

interface ReportSummary {
  revenue: number
  cogs: number
  profit: number
  total_orders: number
}

interface ProductBreakdown {
  name: string
  qty: number
  revenue: number
  cogs: number
  profit: number
}

const summary = ref<ReportSummary | null>(null)
const breakdown = ref<ProductBreakdown[]>([])
const loading = ref(false)
const error = ref('')

const startDate = ref('')
const endDate = ref('')

// Set default to this month
function initDates() {
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  
  // Format YYYY-MM-DD
  const format = (d: Date) => {
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    return `${d.getFullYear()}-${mm}-${dd}`
  }
  
  startDate.value = format(firstDay)
  endDate.value = format(now)
}

function setFilter(type: 'today' | 'this_month' | 'last_month') {
  const now = new Date()
  const format = (d: Date) => {
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    return `${d.getFullYear()}-${mm}-${dd}`
  }

  if (type === 'today') {
    startDate.value = format(now)
    endDate.value = format(now)
  } else if (type === 'this_month') {
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
    startDate.value = format(firstDay)
    endDate.value = format(now)
  } else if (type === 'last_month') {
    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1)
    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0)
    startDate.value = format(firstDay)
    endDate.value = format(lastDay)
  }
  
  loadReport()
}

async function loadReport() {
  loading.value = true
  error.value = ''
  
  try {
    const params = new URLSearchParams()
    if (startDate.value) params.append('start_date', startDate.value)
    if (endDate.value) params.append('end_date', endDate.value)
    
    const res = await api.get(`/reports/profit?${params.toString()}`)
    summary.value = res.data.summary
    breakdown.value = res.data.breakdown
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat laporan'
  } finally {
    loading.value = false
  }
}

function fmt(n: number) {
  return 'Rp\u00A0' + Number(n).toLocaleString('id-ID')
}

function exportCSV() {
  if (!summary.value || breakdown.value.length === 0) return
  
  const headers = ['Produk', 'Terjual', 'Pendapatan', 'Harga Modal', 'Laba/Rugi']
  const rows = breakdown.value.map(item => [
    `"${item.name}"`, 
    item.qty, 
    item.revenue, 
    item.cogs, 
    item.profit
  ])
  
  const csvContent = [
    headers.join(','),
    ...rows.map(r => r.join(','))
  ].join('\n')
  
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `Laporan_Laba_Rugi_${startDate.value}_sd_${endDate.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function printPDF() {
  window.print()
}

onMounted(() => {
  initDates()
  loadReport()
})
</script>

<template>
  <AppLayout>
    <template #title>Laporan Laba/Rugi</template>

    <div class="page-head">
      <div>
        <h1>Laporan Laba/Rugi</h1>
        <p>Analisis pendapatan, modal, dan keuntungan penjualan.</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-presets">
        <button class="btn-preset" @click="setFilter('today')">Hari Ini</button>
        <button class="btn-preset" @click="setFilter('this_month')">Bulan Ini</button>
        <button class="btn-preset" @click="setFilter('last_month')">Bulan Lalu</button>
      </div>
      
      <div class="filter-custom">
        <input type="date" v-model="startDate" class="date-input" />
        <span>-</span>
        <input type="date" v-model="endDate" class="date-input" />
        <button class="btn-primary" @click="loadReport" :disabled="loading">Tampilkan</button>
      </div>
    </div>

    <!-- Export Actions -->
    <div class="export-actions" v-if="summary">
      <button class="btn-outline" @click="exportCSV">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export CSV/Excel
      </button>
      <button class="btn-outline" @click="printPDF">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
        </svg>
        Cetak PDF
      </button>
    </div>

    <div v-if="loading" class="state-center">
      <span class="spin-ring"></span> Memuat laporan...
    </div>
    
    <div v-else-if="error" class="alert error">{{ error }}</div>

    <template v-else-if="summary">
      <!-- Summary Cards -->
      <div class="stats-grid">
        <div class="scard">
          <p class="scard-label">Pendapatan Kotor</p>
          <p class="scard-value">{{ fmt(summary.revenue) }}</p>
          <p class="scard-sub">Total nilai penjualan</p>
        </div>
        <div class="scard">
          <p class="scard-label">Total Harga Modal</p>
          <p class="scard-value cogs">{{ fmt(summary.cogs) }}</p>
          <p class="scard-sub">HPP barang terjual</p>
        </div>
        <div class="scard green">
          <p class="scard-label">Laba Bersih</p>
          <p class="scard-value">{{ fmt(summary.profit) }}</p>
          <p class="scard-sub">Berdasarkan {{ summary.total_orders }} transaksi</p>
        </div>
      </div>

      <!-- Breakdown Table -->
      <div class="card">
        <div class="card-head">
          <h3>Rincian Penjualan Produk</h3>
        </div>
        <div v-if="!breakdown.length" class="card-empty">
          Tidak ada data penjualan pada periode ini.
        </div>
        <div v-else class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Produk</th>
                <th class="right">Terjual</th>
                <th class="right">Pendapatan</th>
                <th class="right">Harga Modal</th>
                <th class="right">Laba/Rugi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in breakdown" :key="idx">
                <td class="fw">{{ item.name }}</td>
                <td class="right">{{ item.qty }}</td>
                <td class="right">{{ fmt(item.revenue) }}</td>
                <td class="right muted">{{ fmt(item.cogs) }}</td>
                <td class="right fw" :class="item.profit >= 0 ? 'text-green' : 'text-red'">
                  {{ fmt(item.profit) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </AppLayout>
</template>

<style scoped>
.page-head {
  margin-bottom: 24px;
}
.page-head h1 { font-size: 22px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13.5px; color: var(--muted); margin-top: 4px; }

.filter-bar {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  background: var(--white);
  padding: 16px;
  border-radius: 12px;
  border: 1px solid var(--border);
  margin-bottom: 24px;
}

.filter-presets {
  display: flex;
  gap: 8px;
}

.btn-preset {
  background: var(--surface);
  border: 1px solid var(--border);
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--muted);
  cursor: pointer;
  transition: all 0.15s;
}
.btn-preset:hover {
  background: var(--accent-bg);
  color: var(--accent);
  border-color: var(--accent-ring);
}

.filter-custom {
  display: flex;
  align-items: center;
  gap: 10px;
}
.date-input {
  border: 1px solid var(--border);
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  outline: none;
}
.date-input:focus { border-color: var(--accent); }

.btn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 8px 16px; border-radius: 8px; font-size: 13.5px;
  font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: var(--accent-dark); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.export-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-bottom: 16px;
}

.btn-outline {
  display: flex; align-items: center; gap: 6px;
  background: var(--white); border: 1.5px solid var(--border);
  color: var(--ink); padding: 8px 14px; border-radius: 8px;
  font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;
}
.btn-outline:hover { border-color: var(--accent); color: var(--accent); }

/* Stats */
.stats-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 16px; margin-bottom: 24px;
}
.scard {
  background: var(--white); border: 1px solid var(--border);
  border-radius: 12px; padding: 20px;
  transition: box-shadow .15s;
}
.scard:hover { box-shadow: 0 4px 16px rgba(0,0,0,.05); }
.scard.green { border-color: #bbf7d0; background: #f0fdf4; }

.scard-label { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
.scard-value { font-size: 24px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); line-height: 1.2; margin-top: 6px; }
.scard-value.cogs { color: #dc2626; } /* Red for cost */
.scard.green .scard-value { color: #166534; }
.scard-sub   { font-size: 12px; color: var(--muted); margin-top: 4px; }

/* Card / Table */
.card {
  background: var(--white); border: 1px solid var(--border);
  border-radius: 12px; overflow: hidden;
}
.card-head {
  padding: 16px 20px; border-bottom: 1px solid var(--border);
}
.card-head h3 { font-size: 15px; font-weight: 700; color: var(--ink); }
.card-empty { padding: 32px; text-align: center; color: var(--muted); font-size: 14px; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--surface); }
th { text-align: left; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); padding: 12px 20px; border-bottom: 1px solid var(--border); }
td { padding: 14px 20px; border-bottom: 1px solid var(--border); font-size: 14px; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface); }

.right { text-align: right; }
.fw { font-weight: 600; }
.muted { color: var(--muted); }
.text-green { color: #16a34a; }
.text-red { color: #dc2626; }

/* State */
.state-center { display: flex; align-items: center; gap: 10px; justify-content: center; padding: 64px; color: var(--muted); }
.spin-ring { display: inline-block; width: 24px; height: 24px; border: 2.5px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 12px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 16px; }
.alert.error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }

@media (max-width: 768px) {
  .stats-grid { grid-template-columns: 1fr; }
  .filter-bar { flex-direction: column; align-items: stretch; }
  .filter-custom { flex-wrap: wrap; }
}

@media print {
  .topbar, .sidebar, .filter-bar, .export-actions { display: none !important; }
  .page-content { padding: 0 !important; }
  .card { border: none; box-shadow: none; }
}
</style>
