<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { useStockStore, type CreateAdjustmentPayload, type StockProduct } from '@/stores/stock'
import { useAuthStore } from '@/stores/auth'

const store = useStockStore()
const auth  = useAuthStore()

// ── Modal State ──
const showModal      = ref(false)
const showHistoryModal = ref(false)
const formError      = ref('')
const formSuccess    = ref('')
const selectedProduct = ref<StockProduct | null>(null)

const form = ref<CreateAdjustmentPayload>({
  product_id: 0,
  type: 'in',
  quantity: 1,
  reason: '',
  notes: '',
})

// ── Search & Filter ──
const searchQuery = ref('')
const stockFilter = ref<'all' | 'low' | 'out'>('all')

const filteredProducts = computed(() => {
  let list = store.products

  // Search
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(p =>
      p.name.toLowerCase().includes(q) ||
      (p.sku && p.sku.toLowerCase().includes(q)) ||
      (p.category?.name && p.category.name.toLowerCase().includes(q))
    )
  }

  // Stock filter
  if (stockFilter.value === 'low') {
    list = list.filter(p => p.stock > 0 && p.stock < 5)
  } else if (stockFilter.value === 'out') {
    list = list.filter(p => p.stock === 0)
  }

  return list
})

// ── Reason Presets ──
const reasonPresets = {
  in: ['Restock / Pembelian', 'Retur Pelanggan', 'Koreksi Stok', 'Barang Ditemukan'],
  out: ['Barang Rusak', 'Barang Hilang', 'Barang Kedaluwarsa', 'Koreksi Stok', 'Pemakaian Internal'],
}

// ── Methods ──
function openAdjustModal(product: StockProduct, type: 'in' | 'out') {
  selectedProduct.value = product
  form.value = {
    product_id: product.id,
    type,
    quantity: 1,
    reason: '',
    notes: '',
  }
  formError.value   = ''
  formSuccess.value = ''
  showModal.value   = true
}

async function openHistoryModal(product: StockProduct) {
  selectedProduct.value = product
  await store.fetchAdjustments(product.id)
  showHistoryModal.value = true
}

function setReason(reason: string) {
  form.value.reason = reason
}

async function submitAdjustment() {
  formError.value   = ''
  formSuccess.value = ''

  if (form.value.quantity < 1) { formError.value = 'Jumlah minimal 1.'; return }
  if (!form.value.reason.trim()) { formError.value = 'Alasan wajib diisi.'; return }

  try {
    await store.createAdjustment(form.value)
    formSuccess.value = 'Penyesuaian stok berhasil!'
    setTimeout(() => {
      showModal.value   = false
      formSuccess.value = ''
    }, 1000)
  } catch (e: any) {
    formError.value = e.message
  }
}

function formatCurrency(n: number) {
  return 'Rp ' + n.toLocaleString('id-ID')
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function stockStatus(stock: number) {
  if (stock === 0) return { label: 'Habis', class: 'status-out' }
  if (stock < 5)   return { label: 'Hampir Habis', class: 'status-low' }
  return { label: 'Tersedia', class: 'status-ok' }
}

function imageUrl(image: string | null) {
  if (!image) return null
  return `http://localhost:8000/storage/${image}`
}

onMounted(() => store.fetchSummary())
</script>

<template>
  <AppLayout>
    <template #title>Inventaris & Stok</template>

    <div class="stock-page">

      <!-- ── Summary Cards ── -->
      <div v-if="store.summary" class="summary-grid">
        <div class="summary-card">
          <div class="sc-icon blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
          </div>
          <div class="sc-data">
            <span class="sc-value">{{ store.summary.total_products }}</span>
            <span class="sc-label">Total Produk</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="sc-icon green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
          </div>
          <div class="sc-data">
            <span class="sc-value">{{ formatCurrency(store.summary.total_stock_value) }}</span>
            <span class="sc-label">Nilai Stok</span>
          </div>
        </div>

        <div class="summary-card" :class="{ 'card-warn': store.summary.low_stock > 0 }">
          <div class="sc-icon amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div class="sc-data">
            <span class="sc-value">{{ store.summary.low_stock }}</span>
            <span class="sc-label">Stok Hampir Habis</span>
          </div>
        </div>

        <div class="summary-card" :class="{ 'card-danger': store.summary.out_of_stock > 0 }">
          <div class="sc-icon red">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <div class="sc-data">
            <span class="sc-value">{{ store.summary.out_of_stock }}</span>
            <span class="sc-label">Stok Habis</span>
          </div>
        </div>
      </div>

      <!-- ── Toolbar ── -->
      <div class="toolbar">
        <div class="search-box">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input v-model="searchQuery" type="text" placeholder="Cari produk, SKU, kategori..." />
        </div>

        <div class="filter-tabs">
          <button :class="{ active: stockFilter === 'all' }" @click="stockFilter = 'all'">
            Semua
          </button>
          <button :class="{ active: stockFilter === 'low' }" @click="stockFilter = 'low'">
            <span class="dot amber"></span> Hampir Habis
          </button>
          <button :class="{ active: stockFilter === 'out' }" @click="stockFilter = 'out'">
            <span class="dot red"></span> Habis
          </button>
        </div>
      </div>

      <!-- ── Loading / Error ── -->
      <div v-if="store.loading && !store.products.length" class="state-center">
        <span class="spin-ring"></span> Memuat data inventaris...
      </div>
      <div v-else-if="store.error" class="state-center err">{{ store.error }}</div>

      <!-- ── Product Table ── -->
      <div v-else class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Produk</th>
                <th>SKU</th>
                <th>Kategori</th>
                <th class="text-right">Harga Modal</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-center">Stok</th>
                <th class="text-right">Nilai Stok</th>
                <th>Perubahan Terakhir</th>
                <th v-if="auth.isAdmin">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredProducts.length === 0">
                <td :colspan="auth.isAdmin ? 9 : 8" class="empty-row">
                  {{ searchQuery || stockFilter !== 'all' ? 'Tidak ada produk yang cocok.' : 'Belum ada produk.' }}
                </td>
              </tr>
              <tr v-for="p in filteredProducts" :key="p.id">
                <td>
                  <div class="product-cell">
                    <div class="product-img" v-if="p.image">
                      <img :src="imageUrl(p.image)" :alt="p.name" />
                    </div>
                    <div class="product-img placeholder" v-else>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    </div>
                    <span class="product-name">{{ p.name }}</span>
                  </div>
                </td>
                <td class="mono">{{ p.sku || '—' }}</td>
                <td>
                  <span v-if="p.category" class="badge-cat">{{ p.category.name }}</span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td class="text-right mono">{{ p.cost_price ? formatCurrency(p.cost_price) : '—' }}</td>
                <td class="text-right mono">{{ formatCurrency(p.price) }}</td>
                <td class="text-center">
                  <span class="stock-badge" :class="stockStatus(p.stock).class">
                    {{ p.stock }} <span class="stock-label">{{ stockStatus(p.stock).label }}</span>
                  </span>
                </td>
                <td class="text-right mono">{{ formatCurrency(p.stock_value) }}</td>
                <td>
                  <div v-if="p.last_adjustment" class="last-adj">
                    <span class="adj-type" :class="p.last_adjustment.type === 'in' ? 'adj-in' : 'adj-out'">
                      {{ p.last_adjustment.type === 'in' ? '+' : '-' }}{{ p.last_adjustment.quantity }}
                    </span>
                    <span class="adj-reason">{{ p.last_adjustment.reason }}</span>
                  </div>
                  <span v-else class="text-muted">—</span>
                </td>
                <td v-if="auth.isAdmin">
                  <div class="actions">
                    <button class="btn-action btn-in" @click="openAdjustModal(p, 'in')" title="Tambah Stok">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                    <button class="btn-action btn-out" @click="openAdjustModal(p, 'out')" title="Kurangi Stok">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                    <button class="btn-action btn-history" @click="openHistoryModal(p)" title="Riwayat">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 0 .5-4.5"/><polyline points="3 3 3 7 7 7"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── MODAL: Stock Adjustment ── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
          <div class="modal-card">
            <div class="modal-header" :class="form.type === 'in' ? 'header-in' : 'header-out'">
              <div class="mh-icon">
                <svg v-if="form.type === 'in'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </div>
              <div>
                <h2>{{ form.type === 'in' ? 'Tambah Stok' : 'Kurangi Stok' }}</h2>
                <p class="mh-product">{{ selectedProduct?.name }}</p>
              </div>
              <button class="modal-close" @click="showModal = false">&times;</button>
            </div>

            <form @submit.prevent="submitAdjustment" class="modal-body">
              <div class="current-stock-box">
                <span class="cs-label">Stok Saat Ini</span>
                <span class="cs-value">{{ selectedProduct?.stock }}</span>
                <span class="cs-arrow">→</span>
                <span class="cs-new" :class="form.type === 'in' ? 'cs-green' : 'cs-red'">
                  {{ form.type === 'in' ? (selectedProduct?.stock ?? 0) + form.quantity : Math.max(0, (selectedProduct?.stock ?? 0) - form.quantity) }}
                </span>
              </div>

              <div class="form-group">
                <label>Jumlah <span class="req">*</span></label>
                <input v-model.number="form.quantity" type="number" min="1" placeholder="Masukkan jumlah" />
              </div>

              <div class="form-group">
                <label>Alasan <span class="req">*</span></label>
                <div class="reason-chips">
                  <button
                    type="button"
                    v-for="r in reasonPresets[form.type]"
                    :key="r"
                    class="chip"
                    :class="{ 'chip-active': form.reason === r }"
                    @click="setReason(r)"
                  >{{ r }}</button>
                </div>
                <input v-model="form.reason" type="text" placeholder="Atau ketik alasan manual..." class="mt-2" />
              </div>

              <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea v-model="form.notes" rows="2" placeholder="Detail tambahan..."></textarea>
              </div>

              <div v-if="formError" class="alert alert-error">{{ formError }}</div>
              <div v-if="formSuccess" class="alert alert-success">{{ formSuccess }}</div>

              <div class="modal-actions">
                <button type="button" class="btn-outline" @click="showModal = false">Batal</button>
                <button type="submit" class="btn-primary" :class="form.type === 'out' ? 'btn-danger' : ''" :disabled="store.saving">
                  <span v-if="store.saving" class="spin-ring sm"></span>
                  {{ store.saving ? 'Menyimpan...' : (form.type === 'in' ? '+ Tambah Stok' : '- Kurangi Stok') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── MODAL: History ── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showHistoryModal" class="modal-overlay" @click.self="showHistoryModal = false">
          <div class="modal-card modal-lg">
            <div class="modal-header">
              <div>
                <h2>Riwayat Stok</h2>
                <p class="mh-product">{{ selectedProduct?.name }}</p>
              </div>
              <button class="modal-close" @click="showHistoryModal = false">&times;</button>
            </div>

            <div class="modal-body">
              <div v-if="store.loading" class="state-center sm">
                <span class="spin-ring"></span> Memuat riwayat...
              </div>
              <div v-else-if="store.adjustments.length === 0" class="state-center sm">
                Belum ada riwayat penyesuaian stok.
              </div>
              <div v-else class="history-list">
                <div v-for="adj in store.adjustments" :key="adj.id" class="history-item" :class="adj.type === 'in' ? 'hi-in' : 'hi-out'">
                  <div class="hi-badge">
                    <span>{{ adj.type === 'in' ? '+' : '-' }}{{ adj.quantity }}</span>
                  </div>
                  <div class="hi-detail">
                    <div class="hi-top">
                      <strong>{{ adj.reason }}</strong>
                      <span class="hi-stock">{{ adj.stock_before }} → {{ adj.stock_after }}</span>
                    </div>
                    <div class="hi-meta">
                      <span>{{ adj.user?.name || 'User' }}</span>
                      <span>{{ formatDate(adj.created_at) }}</span>
                    </div>
                    <p v-if="adj.notes" class="hi-notes">{{ adj.notes }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </AppLayout>
</template>

<style scoped>
.stock-page { display: flex; flex-direction: column; gap: 20px; }

/* ── Summary Cards ── */
.summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.summary-card {
  background: var(--white); border: 1px solid var(--border); border-radius: 14px;
  padding: 20px; display: flex; align-items: center; gap: 16px;
  transition: box-shadow .2s, border-color .2s;
}
.summary-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }
.card-warn  { border-color: #fbbf24; background: linear-gradient(135deg, #fffbeb, var(--white)); }
.card-danger { border-color: #f87171; background: linear-gradient(135deg, #fef2f2, var(--white)); }

.sc-icon {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sc-icon.blue  { background: #eff6ff; color: #2563eb; }
.sc-icon.green { background: #f0fdf4; color: #16a34a; }
.sc-icon.amber { background: #fffbeb; color: #d97706; }
.sc-icon.red   { background: #fef2f2; color: #ef4444; }

.sc-data  { display: flex; flex-direction: column; }
.sc-value { font-size: 22px; font-weight: 800; color: var(--ink); letter-spacing: -.5px; }
.sc-label { font-size: 12px; color: var(--muted); font-weight: 500; margin-top: 2px; }

/* ── Toolbar ── */
.toolbar { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.search-box {
  display: flex; align-items: center; gap: 8px;
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 10px; padding: 8px 14px; flex: 1; min-width: 220px;
  transition: border-color .15s;
}
.search-box:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }
.search-box svg { color: var(--muted); flex-shrink: 0; }
.search-box input { border: none; outline: none; background: none; font-size: 13.5px; color: var(--ink); flex: 1; }

.filter-tabs { display: flex; gap: 4px; background: var(--white); border: 1px solid var(--border); border-radius: 10px; padding: 3px; }
.filter-tabs button {
  display: flex; align-items: center; gap: 5px;
  padding: 6px 14px; border: none; border-radius: 8px;
  font-size: 12.5px; font-weight: 600; cursor: pointer;
  background: transparent; color: var(--muted); transition: all .15s;
}
.filter-tabs button.active { background: var(--accent); color: #fff; }
.filter-tabs button:not(.active):hover { background: var(--surface); }
.dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
.dot.amber { background: #f59e0b; }
.dot.red   { background: #ef4444; }

/* ── Table ── */
.card {
  background: var(--white); border: 1px solid var(--border);
  border-radius: 14px; overflow: hidden;
}
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--surface); }
th {
  text-align: left; font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .5px;
  color: var(--muted); padding: 10px 16px;
  border-bottom: 1px solid var(--border); white-space: nowrap;
}
td {
  padding: 12px 16px; border-bottom: 1px solid var(--border);
  font-size: 13.5px; color: var(--ink); vertical-align: middle;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface); }
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-muted { color: var(--muted); }
.mono { font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace; font-size: 12.5px; }
.empty-row { text-align: center; color: var(--muted); padding: 48px 16px !important; }

.product-cell { display: flex; align-items: center; gap: 10px; }
.product-img {
  width: 36px; height: 36px; border-radius: 8px; overflow: hidden;
  flex-shrink: 0; border: 1px solid var(--border);
}
.product-img img { width: 100%; height: 100%; object-fit: cover; }
.product-img.placeholder {
  display: flex; align-items: center; justify-content: center;
  background: var(--surface); color: var(--muted);
}
.product-name { font-weight: 600; white-space: nowrap; }

.badge-cat {
  display: inline-block; padding: 2px 10px; border-radius: 999px;
  font-size: 11.5px; font-weight: 600;
  background: #f0f0ff; color: #4338ca;
}

.stock-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 4px 12px; border-radius: 999px;
  font-size: 13px; font-weight: 700;
}
.stock-label { font-size: 10.5px; font-weight: 500; }
.status-ok  { background: #f0fdf4; color: #16a34a; }
.status-low { background: #fffbeb; color: #d97706; }
.status-out { background: #fef2f2; color: #ef4444; }

.last-adj { display: flex; flex-direction: column; gap: 2px; }
.adj-type {
  font-size: 12px; font-weight: 700; font-family: 'SF Mono', monospace;
}
.adj-in  { color: #16a34a; }
.adj-out { color: #ef4444; }
.adj-reason { font-size: 11.5px; color: var(--muted); }

/* ── Action Buttons ── */
.actions { display: flex; gap: 6px; }
.btn-action {
  width: 30px; height: 30px; border: 1.5px solid var(--border);
  border-radius: 8px; background: var(--white); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s;
}
.btn-in:hover  { border-color: #16a34a; color: #16a34a; background: #f0fdf4; }
.btn-out:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.btn-history:hover { border-color: var(--accent); color: var(--accent); background: #eff6ff; }

/* ── State ── */
.state-center {
  display: flex; align-items: center; gap: 10px;
  justify-content: center; padding: 64px; color: var(--muted);
}
.state-center.sm { padding: 32px; font-size: 13px; }
.state-center.err { color: #ef4444; }
.spin-ring {
  display: inline-block; width: 20px; height: 20px;
  border: 2.5px solid var(--border); border-top-color: var(--accent);
  border-radius: 50%; animation: spin .7s linear infinite;
}
.spin-ring.sm { width: 14px; height: 14px; border-width: 2px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Modal ── */
.modal-overlay {
  position: fixed; inset: 0; z-index: 999;
  background: rgba(0,0,0,.45); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; padding: 16px;
}
.modal-card {
  background: var(--white); border-radius: 16px;
  width: 100%; max-width: 520px; max-height: 90vh;
  overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.modal-lg { max-width: 600px; }

.modal-header {
  display: flex; align-items: center; gap: 12px;
  padding: 20px 24px; border-bottom: 1px solid var(--border);
}
.modal-header h2 { font-size: 17px; font-weight: 700; color: var(--ink); }
.mh-product { font-size: 13px; color: var(--muted); margin-top: 2px; }
.mh-icon {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.header-in .mh-icon  { background: #f0fdf4; color: #16a34a; }
.header-out .mh-icon { background: #fef2f2; color: #ef4444; }
.modal-close {
  background: none; border: none; font-size: 22px;
  color: var(--muted); cursor: pointer; padding: 0 4px; margin-left: auto;
}

.modal-body { padding: 24px; }

/* Current stock preview */
.current-stock-box {
  display: flex; align-items: center; justify-content: center; gap: 16px;
  background: var(--surface); border-radius: 12px;
  padding: 16px; margin-bottom: 20px;
}
.cs-label { font-size: 12px; color: var(--muted); font-weight: 500; }
.cs-value { font-size: 28px; font-weight: 800; color: var(--ink); }
.cs-arrow { font-size: 20px; color: var(--muted); }
.cs-new   { font-size: 28px; font-weight: 800; }
.cs-green { color: #16a34a; }
.cs-red   { color: #ef4444; }

/* Form */
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; }
.req { color: #ef4444; }
.form-group input, .form-group textarea {
  padding: 9px 12px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 13.5px; color: var(--ink);
  background: var(--white); outline: none; width: 100%; resize: vertical;
}
.form-group input:focus, .form-group textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }
.mt-2 { margin-top: 8px; }

.reason-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.chip {
  padding: 5px 12px; border: 1.5px solid var(--border); border-radius: 999px;
  font-size: 12px; font-weight: 500; cursor: pointer;
  background: var(--white); color: var(--muted); transition: all .15s;
}
.chip:hover { border-color: var(--accent); color: var(--accent); }
.chip-active { background: var(--accent); color: #fff; border-color: var(--accent); }

.alert {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 500;
  margin-bottom: 12px;
}
.alert-error   { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

.modal-actions {
  display: flex; justify-content: flex-end; gap: 10px;
  padding-top: 16px; border-top: 1px solid var(--border); margin-top: 8px;
}
.btn-primary {
  display: flex; align-items: center; gap: 6px;
  background: var(--accent); color: #fff; border: none;
  padding: 9px 20px; border-radius: 8px; font-size: 13.5px;
  font-weight: 600; cursor: pointer; transition: background .15s;
}
.btn-primary:hover { background: var(--accent-dark); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.btn-danger { background: #ef4444; }
.btn-danger:hover { background: #dc2626; }
.btn-outline {
  display: flex; align-items: center; gap: 6px;
  background: none; border: 1.5px solid var(--border);
  color: var(--muted); padding: 9px 20px; border-radius: 8px;
  font-size: 13px; cursor: pointer; transition: border-color .15s, color .15s;
}
.btn-outline:hover { border-color: var(--accent); color: var(--accent); }

/* ── History List ── */
.history-list { display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto; }
.history-item {
  display: flex; gap: 12px; padding: 12px 14px;
  border: 1px solid var(--border); border-radius: 10px;
  background: var(--white); transition: box-shadow .15s;
}
.history-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,.05); }
.hi-badge {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 800; flex-shrink: 0;
  font-family: 'SF Mono', monospace;
}
.hi-in .hi-badge  { background: #f0fdf4; color: #16a34a; }
.hi-out .hi-badge { background: #fef2f2; color: #ef4444; }
.hi-detail { flex: 1; min-width: 0; }
.hi-top { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.hi-top strong { font-size: 13.5px; color: var(--ink); }
.hi-stock {
  font-size: 12px; font-weight: 600; color: var(--muted);
  font-family: 'SF Mono', monospace; white-space: nowrap;
}
.hi-meta { display: flex; gap: 12px; font-size: 11.5px; color: var(--muted); margin-top: 3px; }
.hi-notes { font-size: 12px; color: var(--muted); margin-top: 4px; font-style: italic; }

/* ── Transitions ── */
.modal-enter-active, .modal-leave-active { transition: opacity .2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-card { animation: modal-slide .25s ease; }
@keyframes modal-slide {
  from { transform: translateY(20px); opacity: 0; }
  to   { transform: translateY(0); opacity: 1; }
}
</style>
