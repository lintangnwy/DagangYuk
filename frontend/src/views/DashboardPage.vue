<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

const auth = useAuthStore()
const router = useRouter()

interface ChartDay { date: string; label: string; count: number; revenue: number }
interface TopProduct { name: string; total_qty: number; total_revenue: number }
interface RecentOrder {
  id: number; invoice_number: string; total_amount: number
  payment_method: string; created_at: string
  user?: { id: number; name: string }
}
interface DashData {
  period: { range: string; start_date: string; end_date: string; label: string }
  current: { revenue: number; profit: number; orders: number }
  today: { revenue: number; profit: number; orders: number }
  inventory: { total_products: number; low_stock_count: number; out_of_stock_count: number; low_stock_items: {id: number, name: string, stock: number, sku: string}[] }
  chart: ChartDay[]
  top_products: TopProduct[]
  recent_orders: RecentOrder[]
  tenants?: { total: number; active: number }
}

const data = ref<DashData | null>(null)
const loading = ref(true)
const error = ref('')
const rangeType = ref<'today' | 'last_7_days' | 'this_week' | 'this_month' | 'last_month' | 'custom'>('last_7_days')
const customStartDate = ref('')
const customEndDate = ref('')
const showCustomRange = ref(false)

const rangeLabelMap: Record<string, string> = {
  'today': 'Hari Ini',
  'last_7_days': '7 Hari Terakhir',
  'this_week': 'Minggu Ini',
  'this_month': 'Bulan Ini',
  'last_month': 'Bulan Lalu'
}

function initCustomDates() {
  const end = new Date()
  const start = new Date(end.getTime() - 6 * 24 * 60 * 60 * 1000)
  customStartDate.value = start.toISOString().split('T')[0] ?? ''
  customEndDate.value = end.toISOString().split('T')[0] ?? ''
}

async function load() {
  loading.value = true
  error.value = ''
  
  try {
    let url = `/dashboard?range=${rangeType.value}`
    if (rangeType.value === 'custom') {
      url += `&start_date=${customStartDate.value}&end_date=${customEndDate.value}`
    }
    
    const res = await api.get(url)
    data.value = res.data
  } catch (e: unknown) {
    const errorMsg = e instanceof Error ? e.message : 'Gagal memuat dashboard'
    error.value = errorMsg
  } finally {
    loading.value = false
  }
}

function setRange(type: 'today' | 'last_7_days' | 'this_week' | 'this_month' | 'last_month') {
  rangeType.value = type
  showCustomRange.value = false
  load()
}

function toggleCustomRange() {
  showCustomRange.value = !showCustomRange.value
}

function applyCustomRange() {
  rangeType.value = 'custom'
  load()
}

function fmt(n: number) {
  return 'Rp ' + n.toLocaleString('id-ID')
}

function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

function payLabel(m: string) {
  return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' } as Record<string, string>)[m] ?? m
}

const chartMax = computed(() => {
  if (!data.value?.chart.length) return 1
  return Math.max(...data.value.chart.map(d => d.revenue), 1)
})

onMounted(() => {
  initCustomDates()
  load()
})
</script>

<template>
  <AppLayout>
    <template #title>Dashboard</template>

    <!-- Header -->
    <div class="dash-header">
      <div>
        <h1 class="text-4xl font-extrabold text-ink">Dashboard</h1>
        <p class="text-sm text-muted mt-1">Ringkasan penjualan dan performa toko Anda</p>
      </div>
      <button @click="load" class="btn-refresh" :disabled="loading">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="23 4 23 10 17 10"/>
          <polyline points="1 20 1 14 7 14"/>
          <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
        </svg>
        {{ loading ? 'Memuat...' : 'Refresh' }}
      </button>
    </div>

    <!-- Alert -->
    <div v-if="error" class="alert alert-error mb-6">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12" stroke="white" stroke-width="2"/>
        <line x1="12" y1="16" x2="12.01" y2="16" stroke="white" stroke-width="2"/>
      </svg>
      {{ error }}
    </div>

    <!-- Date Range Selector -->
    <div class="date-selector">
      <div class="range-buttons">
        <button 
          v-for="(label, key) in rangeLabelMap" 
          :key="key"
          :class="['btn-range', { active: rangeType === key }]"
          @click="setRange(key as any)"
        >
          {{ label }}
        </button>
        <button 
          :class="['btn-range', { active: rangeType === 'custom' }]"
          @click="toggleCustomRange"
        >
          Custom
        </button>
      </div>

      <Transition name="slide-down">
        <div v-if="showCustomRange" class="custom-range">
          <input v-model="customStartDate" type="date" class="input-date" />
          <span class="separator">→</span>
          <input v-model="customEndDate" type="date" class="input-date" />
          <button @click="applyCustomRange" class="btn-apply">Terapkan</button>
        </div>
      </Transition>
    </div>

    <!-- Stats Grid -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="i in 4" :key="i" class="stat-skeleton"></div>
    </div>

    <div v-else-if="data" class="stats-grid mb-6">
      <div class="stat-card">
        <div class="stat-icon revenue">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 1v6m0 6v4M4.22 4.22l4.24 4.24m2.12 2.12l4.24 4.24M1 12h6m6 0h4M4.22 19.78l4.24-4.24m2.12-2.12l4.24-4.24M19 12h2a2 2 0 012 2v2a2 2 0 01-2 2h-2"/>
          </svg>
        </div>
        <div>
          <p class="stat-label">Total Revenue</p>
          <p class="stat-value">{{ fmt(data.current.revenue) }}</p>
          <p class="stat-period">{{ data.period.label }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon profit">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/>
            <polyline points="13 2 13 9 20 9"/>
          </svg>
        </div>
        <div>
          <p class="stat-label">Total Profit</p>
          <p class="stat-value">{{ fmt(data.current.profit) }}</p>
          <p class="stat-period">{{ data.period.label }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon orders">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <circle cx="9" cy="21" r="1"/>
            <circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
          </svg>
        </div>
        <div>
          <p class="stat-label">Total Orders</p>
          <p class="stat-value">{{ data.current.orders }}</p>
          <p class="stat-period">{{ data.period.label }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon products">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
          </svg>
        </div>
        <div>
          <p class="stat-label">Total Produk</p>
          <p class="stat-value">{{ data.inventory.total_products }}</p>
          <p class="stat-low text-danger" v-if="data.inventory.low_stock_count > 0">
            {{ data.inventory.low_stock_count }} stok rendah
          </p>
        </div>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Chart Section -->
      <div class="lg:col-span-2">
        <div class="card">
          <div class="card-header">
            <h2 class="text-lg font-bold">Penjualan Harian</h2>
            <p class="text-xs text-muted">{{ data?.period.label }}</p>
          </div>
          <div class="chart-container">
            <div class="chart">
              <div v-for="day in data?.chart" :key="day.date" class="chart-bar">
                <div 
                  class="bar"
                  :style="{ height: `${(day.revenue / chartMax) * 100}%` }"
                  :title="`${day.label}: ${fmt(day.revenue)}`"
                ></div>
                <span class="label">{{ day.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock Alert -->
      <div class="card">
        <div class="card-header">
          <h2 class="text-lg font-bold">Stok Rendah</h2>
          <span v-if="data?.inventory.low_stock_count" class="badge">{{ data.inventory.low_stock_count }}</span>
        </div>
        <div v-if="!data?.inventory.low_stock_items?.length" class="empty-state">
          <p>Semua stok dalam kondisi baik</p>
        </div>
        <div v-else class="low-stock-list">
          <div v-for="item in data.inventory.low_stock_items" :key="item.id" class="stock-item">
            <div>
              <p class="item-name">{{ item.name }}</p>
              <p class="item-sku">{{ item.sku }}</p>
            </div>
            <span class="stock-badge">{{ item.stock }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
      <!-- Top Products -->
      <div class="card">
        <div class="card-header">
          <h2 class="text-lg font-bold">Produk Terlaris</h2>
        </div>
        <div class="product-list">
          <div v-for="(prod, idx) in data?.top_products.slice(0, 5)" :key="idx" class="product-row">
            <span class="rank">{{ idx + 1 }}</span>
            <div class="prod-info">
              <p class="prod-name">{{ prod.name }}</p>
              <p class="prod-stat">{{ prod.total_qty }} terjual • {{ fmt(prod.total_revenue) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="card">
        <div class="card-header">
          <h2 class="text-lg font-bold">Pesanan Terbaru</h2>
        </div>
        <div class="order-list">
          <div v-for="order in data?.recent_orders.slice(0, 5)" :key="order.id" class="order-row">
            <div class="order-info">
              <p class="order-id">{{ order.invoice_number }}</p>
              <p class="order-meta">{{ fmtDate(order.created_at) }} • {{ payLabel(order.payment_method) }}</p>
            </div>
            <p class="order-amt">{{ fmt(order.total_amount) }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin: -8px 0 28px;
  padding: 24px 28px;
  background: linear-gradient(120deg, #172033 0%, #1d3a3b 100%);
  border-radius: var(--rounded-xl);
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
  gap: 16px;
  flex-wrap: wrap;
}

.dash-header h1 { color: #f8fafc; }
.dash-header p { color: rgba(248, 250, 252, 0.68); }

.btn-refresh {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: var(--rounded-lg);
  font-size: var(--text-sm);
  font-weight: 600;
  color: #f8fafc;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-refresh:hover:not(:disabled) {
  border-color: var(--success);
  color: #ffffff;
  background: rgba(16, 185, 129, 0.22);
}

.btn-refresh:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: var(--rounded-xl);
  font-size: var(--text-sm);
}

.alert-error {
  background: var(--danger-bg);
  color: var(--danger);
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.date-selector {
  margin-bottom: 24px;
}

.range-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.btn-range {
  padding: 8px 16px;
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: var(--rounded-xl);
  font-size: var(--text-sm);
  font-weight: 500;
  color: var(--muted);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-range:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.btn-range.active {
  background: var(--accent);
  border-color: var(--accent);
  color: var(--white);
}

.custom-range {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: var(--surface);
  border-radius: var(--rounded-lg);
}

.input-date {
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: var(--rounded-md);
  font-size: var(--text-sm);
  outline: none;
  transition: border-color var(--transition-fast);
}

.input-date:focus {
  border-color: var(--accent);
}

.separator {
  color: var(--muted);
}

.btn-apply {
  padding: 8px 16px;
  background: var(--accent);
  color: var(--white);
  border: none;
  border-radius: var(--rounded-md);
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;
  transition: background var(--transition-fast);
}

.btn-apply:hover {
  background: var(--accent-dark);
}

.stat-skeleton {
  height: 120px;
  background: linear-gradient(90deg, var(--surface) 25%, var(--surface-alt) 50%, var(--surface) 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: var(--rounded-lg);
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.stat-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 20px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--rounded-lg);
  transition: all var(--transition-fast);
}

.stat-card:hover {
  box-shadow: var(--shadow-md);
  border-color: var(--accent-ring);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--rounded-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
  color: var(--white);
}

.stat-icon.revenue {
  background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
}

.stat-icon.profit {
  background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
}

.stat-icon.orders {
  background: linear-gradient(135deg, var(--info) 0%, #0891b2 100%);
}

.stat-icon.products {
  background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
}

.stat-label {
  font-size: var(--text-xs);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--muted);
  margin-bottom: 4px;
}

.stat-value {
  font-size: 22px;
  font-weight: var(--font-bold);
  color: var(--ink);
  line-height: 1.2;
}

.stat-period {
  font-size: var(--text-xs);
  color: var(--subtle);
  margin-top: 2px;
}

.stat-low {
  font-size: var(--text-xs);
  font-weight: 600;
}

.card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--rounded-lg);
  padding: 20px;
  transition: all var(--transition-fast);
}

.card:hover {
  box-shadow: var(--shadow-md);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  gap: 12px;
}

.badge {
  display: inline-block;
  background: var(--danger);
  color: var(--white);
  padding: 2px 8px;
  border-radius: var(--rounded-full);
  font-size: var(--text-xs);
  font-weight: 700;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--subtle);
  font-size: var(--text-sm);
}

.chart-container {
  height: 280px;
  display: flex;
  align-items: flex-end;
}

.chart {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  gap: 8px;
  padding: 0 0 12px 0;
}

.chart-bar {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.bar {
  width: 100%;
  background: linear-gradient(180deg, var(--accent) 0%, var(--accent-dark) 100%);
  border-radius: var(--rounded-md) var(--rounded-md) 0 0;
  min-height: 4px;
  transition: opacity var(--transition-fast);
  cursor: pointer;
}

.bar:hover {
  opacity: 0.8;
}

.chart-bar .label {
  font-size: var(--text-xs);
  font-weight: 600;
  color: var(--muted);
}

.low-stock-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stock-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: var(--surface);
  border-radius: var(--rounded-md);
}

.item-name {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--ink);
}

.item-sku {
  font-size: var(--text-xs);
  color: var(--muted);
  font-family: var(--font-mono);
}

.stock-badge {
  display: inline-block;
  background: var(--danger-bg);
  color: var(--danger);
  padding: 2px 8px;
  border-radius: var(--rounded-full);
  font-size: var(--text-xs);
  font-weight: 700;
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: var(--surface);
  border-radius: var(--rounded-md);
}

.rank {
  width: 28px;
  height: 28px;
  background: var(--accent-bg);
  color: var(--accent);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  flex-shrink: 0;
}

.prod-info {
  flex: 1;
  min-width: 0;
}

.prod-name {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--ink);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.prod-stat {
  font-size: var(--text-xs);
  color: var(--muted);
}

.order-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: var(--surface);
  border-radius: var(--rounded-md);
}

.order-info {
  flex: 1;
  min-width: 0;
}

.order-id {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--ink);
  font-family: var(--font-mono);
}

.order-meta {
  font-size: var(--text-xs);
  color: var(--muted);
}

.order-amt {
  font-size: var(--text-sm);
  font-weight: 700;
  color: var(--accent);
  white-space: nowrap;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all var(--transition-base);
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .chart-bar .label {
    font-size: 10px;
  }

  .dash-header {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .card {
    padding: 16px;
  }

  .chart-container {
    height: 200px;
  }
}
</style>
