<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

interface Branch { id: number; name: string }

interface WarehouseStock {
  id: number
  product_id: number
  quantity: number
  product: { id: number; name: string; sku: string | null; price: number }
}

interface Warehouse {
  id: number
  name: string
  address: string | null
  is_active: boolean
  branch_id: number | null
  branch?: Branch
  stocks?: WarehouseStock[]
}

interface Transfer {
  id: number
  from_warehouse: { id: number; name: string }
  to_warehouse: { id: number; name: string }
  product: { id: number; name: string }
  user: { id: number; name: string }
  quantity: number
  note: string | null
  status: string
  created_at: string
}

const warehouses = ref<Warehouse[]>([])
const branches   = ref<Branch[]>([])
const transfers  = ref<Transfer[]>([])
const loading    = ref(true)
const error      = ref('')

// Active tab
const activeTab = ref<'warehouses' | 'transfers'>('warehouses')

// Selected warehouse for stock view
const selectedWh   = ref<Warehouse | null>(null)
const loadingStocks = ref(false)

// Modal states
const whModal  = ref(false)
const editingWh = ref<Warehouse | null>(null)
const savingWh  = ref(false)
const whErr    = ref('')
const whForm   = ref({ name: '', address: '', branch_id: '' })

const transferModal  = ref(false)
const savingTransfer = ref(false)
const transferErr    = ref('')
const transferForm   = ref({
  from_warehouse_id: '',
  to_warehouse_id: '',
  product_id: '',
  quantity: 1,
  note: '',
})
const availableProducts = computed(() => {
  const wh = warehouses.value.find(w => w.id === Number(transferForm.value.from_warehouse_id))
  return wh?.stocks ?? []
})

// Filters
const whSearch = ref('')
const filteredWarehouses = computed(() => {
  const q = whSearch.value.toLowerCase()
  if (!q) return warehouses.value
  return warehouses.value.filter(w => w.name.toLowerCase().includes(q) || w.branch?.name?.toLowerCase().includes(q))
})

function fmt(n: number) { return 'Rp\u00A0' + n.toLocaleString('id-ID') }
function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
}

async function load() {
  loading.value = true; error.value = ''
  try {
    const [whRes, brRes, trRes] = await Promise.all([
      api.get('/warehouses'),
      api.get('/branches'),
      api.get('/stock-transfers'),
    ])
    warehouses.value = whRes.data
    branches.value   = brRes.data
    transfers.value  = trRes.data
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Gagal memuat data.'
  } finally { loading.value = false }
}

async function selectWarehouse(wh: Warehouse) {
  if (selectedWh.value?.id === wh.id) { selectedWh.value = null; return }
  selectedWh.value = wh
  loadingStocks.value = true
  try {
    const res = await api.get(`/warehouses/${wh.id}/stocks`)
    wh.stocks = res.data
    // Also update in list (wh is the same reactive object referenced from warehouses.value)
    const found = warehouses.value.find(w => w.id === wh.id)
    selectedWh.value = found ?? wh
  } catch {} finally { loadingStocks.value = false }
}

// Warehouse CRUD
function openCreateWh() {
  editingWh.value = null
  whForm.value = { name: '', address: '', branch_id: '' }
  whErr.value = ''
  whModal.value = true
}
function openEditWh(wh: Warehouse) {
  editingWh.value = wh
  whForm.value = { name: wh.name, address: wh.address ?? '', branch_id: wh.branch_id ? String(wh.branch_id) : '' }
  whErr.value = ''
  whModal.value = true
}
function closeWhModal() { whModal.value = false }
async function saveWh() {
  if (!whForm.value.name.trim()) { whErr.value = 'Nama gudang wajib diisi.'; return }
  savingWh.value = true; whErr.value = ''
  const payload = {
    name: whForm.value.name,
    address: whForm.value.address || null,
    branch_id: whForm.value.branch_id ? Number(whForm.value.branch_id) : null,
  }
  try {
    if (editingWh.value) {
      await api.put(`/warehouses/${editingWh.value.id}`, payload)
    } else {
      await api.post('/warehouses', payload)
    }
    closeWhModal()
    await load()
  } catch (e: any) {
    whErr.value = e.response?.data?.message ?? 'Gagal menyimpan.'
  } finally { savingWh.value = false }
}
async function deleteWh(wh: Warehouse) {
  if (!confirm(`Hapus gudang "${wh.name}"?`)) return
  try {
    await api.delete(`/warehouses/${wh.id}`)
    warehouses.value = warehouses.value.filter(w => w.id !== wh.id)
    if (selectedWh.value?.id === wh.id) selectedWh.value = null
  } catch (e: any) {
    alert(e.response?.data?.message ?? 'Gagal menghapus.')
  }
}

// Transfer
function openTransfer() {
  transferForm.value = { from_warehouse_id: '', to_warehouse_id: '', product_id: '', quantity: 1, note: '' }
  transferErr.value = ''
  transferModal.value = true
}
async function onFromWhChange() {
  transferForm.value.product_id = ''
  const whId = Number(transferForm.value.from_warehouse_id)
  if (!whId) return
  const wh = warehouses.value.find(w => w.id === whId)
  if (!wh?.stocks) {
    const res = await api.get(`/warehouses/${whId}/stocks`)
    const found = warehouses.value.find(w => w.id === whId)
    if (found) found.stocks = res.data
  }
}
async function submitTransfer() {
  const f = transferForm.value
  if (!f.from_warehouse_id || !f.to_warehouse_id || !f.product_id || f.quantity < 1) {
    transferErr.value = 'Semua field wajib diisi dan jumlah minimal 1.'
    return
  }
  savingTransfer.value = true; transferErr.value = ''
  try {
    await api.post('/stock-transfers', {
      from_warehouse_id: Number(f.from_warehouse_id),
      to_warehouse_id: Number(f.to_warehouse_id),
      product_id: Number(f.product_id),
      quantity: Number(f.quantity),
      note: f.note || null,
    })
    transferModal.value = false
    await load()
    // Re-fetch selected wh stocks if it was involved
    if (selectedWh.value) {
      const res = await api.get(`/warehouses/${selectedWh.value.id}/stocks`)
      selectedWh.value.stocks = res.data
    }
  } catch (e: any) {
    transferErr.value = e.response?.data?.message ?? 'Gagal transfer stok.'
  } finally { savingTransfer.value = false }
}

onMounted(load)
</script>

<template>
  <AppLayout>
    <template #title>Manajemen Gudang</template>

    <div class="page-head">
      <div>
        <h1>Gudang &amp; Stok</h1>
        <p>Kelola gudang, lihat stok, dan transfer antar lokasi</p>
      </div>
      <div class="head-actions">
        <button class="btn-transfer" @click="openTransfer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          Transfer Stok
        </button>
        <button class="btn-primary" @click="openCreateWh">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Gudang
        </button>
      </div>
    </div>

    <div v-if="error" class="alert-err">{{ error }}</div>

    <!-- Tabs -->
    <div class="tabs">
      <button :class="['tab', {active: activeTab==='warehouses'}]" @click="activeTab='warehouses'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        Daftar Gudang
      </button>
      <button :class="['tab', {active: activeTab==='transfers'}]" @click="activeTab='transfers'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3L4 7l4 4M4 7h16M16 21l4-4-4-4M20 17H4"/></svg>
        Riwayat Transfer
      </button>
    </div>

    <!-- Warehouses Tab -->
    <div v-if="activeTab === 'warehouses'" class="wh-layout">
      <!-- Left: warehouse list -->
      <div class="wh-list-panel">
        <div class="wh-search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="whSearch" type="search" placeholder="Cari gudang..." />
        </div>

        <div v-if="loading" class="empty-state">Memuat...</div>
        <div v-else-if="!filteredWarehouses.length" class="empty-state">Belum ada gudang.</div>

        <div v-else class="wh-list">
          <div
            v-for="wh in filteredWarehouses"
            :key="wh.id"
            class="wh-item"
            :class="{ selected: selectedWh?.id === wh.id }"
            @click="selectWarehouse(wh)"
          >
            <div class="wh-item-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div class="wh-item-info">
              <div class="wh-item-name">{{ wh.name }}</div>
              <div class="wh-item-branch">
                {{ wh.branch?.name ?? 'Gudang Pusat' }}
              </div>
            </div>
            <div class="wh-item-actions">
              <button class="icon-btn" @click.stop="openEditWh(wh)" title="Edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4Z"/></svg>
              </button>
              <button class="icon-btn danger" @click.stop="deleteWh(wh)" title="Hapus">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: stock detail -->
      <div class="wh-stock-panel">
        <div v-if="!selectedWh" class="stock-placeholder">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#d1d5db"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          <p>Pilih gudang untuk melihat stok</p>
        </div>

        <div v-else>
          <div class="stock-header">
            <div>
              <h3>{{ selectedWh.name }}</h3>
              <p>{{ selectedWh.branch?.name ?? 'Gudang Pusat' }}
                <span v-if="selectedWh.address"> · {{ selectedWh.address }}</span>
              </p>
            </div>
          </div>

          <div v-if="loadingStocks" class="empty-state">Memuat stok...</div>
          <div v-else-if="!selectedWh.stocks?.length" class="empty-state">Belum ada stok di gudang ini.</div>
          <div v-else class="stock-table-wrap">
            <table class="stock-table">
              <thead>
                <tr>
                  <th>Produk</th>
                  <th>SKU</th>
                  <th class="right">Harga Jual</th>
                  <th class="right">Stok</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in selectedWh.stocks" :key="s.id" :class="{ 'low-stock': s.quantity < 5 }">
                  <td class="product-name">{{ s.product.name }}</td>
                  <td class="sku">{{ s.product.sku ?? '—' }}</td>
                  <td class="right">{{ fmt(s.product.price) }}</td>
                  <td class="right">
                    <span class="qty-badge" :class="s.quantity < 5 ? 'low' : 'ok'">{{ s.quantity }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Transfers Tab -->
    <div v-if="activeTab === 'transfers'">
      <div v-if="loading" class="empty-state">Memuat riwayat transfer...</div>
      <div v-else-if="!transfers.length" class="empty-state">Belum ada riwayat transfer stok.</div>
      <div v-else class="tcard">
        <table class="tr-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Produk</th>
              <th class="center">Jumlah</th>
              <th>Dari Gudang</th>
              <th>Ke Gudang</th>
              <th>Catatan</th>
              <th>Oleh</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in transfers" :key="t.id">
              <td class="date-cell">{{ fmtDate(t.created_at) }}</td>
              <td class="product-name">{{ t.product.name }}</td>
              <td class="center">
                <span class="qty-badge ok">{{ t.quantity }}</span>
              </td>
              <td>
                <span class="wh-label from">{{ t.from_warehouse.name }}</span>
              </td>
              <td>
                <span class="wh-label to">{{ t.to_warehouse.name }}</span>
              </td>
              <td class="note-cell">{{ t.note ?? '—' }}</td>
              <td class="muted">{{ t.user.name }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Warehouse Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="whModal" class="modal-backdrop" @click.self="closeWhModal">
          <div class="modal-box">
            <div class="modal-header">
              <div class="modal-title">{{ editingWh ? 'Edit Gudang' : 'Tambah Gudang Baru' }}</div>
              <button class="modal-close" @click="closeWhModal">✕</button>
            </div>
            <div class="modal-body">
              <div class="field">
                <label>Nama Gudang <span class="req">*</span></label>
                <input v-model="whForm.name" placeholder="Contoh: Gudang Utama" />
              </div>
              <div class="field">
                <label>Lokasi / Cabang</label>
                <select v-model="whForm.branch_id">
                  <option value="">Gudang Pusat (tidak terikat cabang)</option>
                  <option v-for="b in branches" :key="b.id" :value="String(b.id)">{{ b.name }}</option>
                </select>
              </div>
              <div class="field">
                <label>Alamat</label>
                <textarea v-model="whForm.address" placeholder="Alamat lengkap gudang..." rows="2" />
              </div>
              <div v-if="whErr" class="alert-err-sm">{{ whErr }}</div>
              <div class="modal-actions">
                <button class="btn-cancel" @click="closeWhModal" :disabled="savingWh">Batal</button>
                <button class="btn-save" @click="saveWh" :disabled="savingWh">
                  {{ savingWh ? 'Menyimpan...' : (editingWh ? 'Simpan' : 'Tambah Gudang') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Transfer Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="transferModal" class="modal-backdrop" @click.self="transferModal=false">
          <div class="modal-box modal-lg">
            <div class="modal-header">
              <div class="modal-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                Transfer Stok Antar Gudang
              </div>
              <button class="modal-close" @click="transferModal=false">✕</button>
            </div>
            <div class="modal-body">
              <div class="transfer-grid">
                <div class="field">
                  <label>Dari Gudang <span class="req">*</span></label>
                  <select v-model="transferForm.from_warehouse_id" @change="onFromWhChange">
                    <option value="">Pilih gudang asal</option>
                    <option v-for="w in warehouses" :key="w.id" :value="String(w.id)">{{ w.name }}</option>
                  </select>
                </div>
                <div class="transfer-arrow">→</div>
                <div class="field">
                  <label>Ke Gudang <span class="req">*</span></label>
                  <select v-model="transferForm.to_warehouse_id">
                    <option value="">Pilih gudang tujuan</option>
                    <option
                      v-for="w in warehouses"
                      :key="w.id"
                      :value="String(w.id)"
                      :disabled="w.id === Number(transferForm.from_warehouse_id)"
                    >{{ w.name }}</option>
                  </select>
                </div>
              </div>

              <div class="field">
                <label>Produk <span class="req">*</span></label>
                <select v-model="transferForm.product_id" :disabled="!transferForm.from_warehouse_id">
                  <option value="">{{ transferForm.from_warehouse_id ? 'Pilih produk' : 'Pilih gudang asal dulu' }}</option>
                  <option v-for="s in availableProducts" :key="s.product_id" :value="String(s.product_id)">
                    {{ s.product.name }} (Stok: {{ s.quantity }})
                  </option>
                </select>
              </div>

              <div class="field">
                <label>Jumlah <span class="req">*</span></label>
                <input v-model.number="transferForm.quantity" type="number" min="1" placeholder="1" />
              </div>

              <div class="field">
                <label>Catatan</label>
                <textarea v-model="transferForm.note" placeholder="Opsional: alasan transfer, referensi PO, dll." rows="2" />
              </div>

              <div v-if="transferErr" class="alert-err-sm">{{ transferErr }}</div>
              <div class="modal-actions">
                <button class="btn-cancel" @click="transferModal=false" :disabled="savingTransfer">Batal</button>
                <button class="btn-save" @click="submitTransfer" :disabled="savingTransfer">
                  {{ savingTransfer ? 'Memproses...' : 'Konfirmasi Transfer' }}
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
.page-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.page-head h1 { font-size:20px; font-weight:800; letter-spacing:-.5px; color:var(--ink); }
.page-head p  { font-size:13px; color:var(--muted); margin-top:2px; }
.head-actions { display:flex; gap:10px; align-items:center; }
.btn-primary { display:flex; align-items:center; gap:6px; background:var(--accent); color:#fff; border:none; padding:10px 18px; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-primary:hover { background:var(--accent-dark); }
.btn-transfer { display:flex; align-items:center; gap:6px; background:var(--white); border:1.5px solid var(--border); color:var(--ink); padding:9px 16px; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-transfer:hover { border-color:var(--accent); color:var(--accent); }
.alert-err { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; font-size:13px; padding:10px 14px; border-radius:7px; margin-bottom:16px; }

.tabs { display:flex; gap:4px; margin-bottom:16px; background:var(--surface); border-radius:10px; padding:4px; border:1px solid var(--border); width:fit-content; }
.tab { display:flex; align-items:center; gap:6px; padding:8px 16px; border-radius:7px; border:none; background:none; font-size:13.5px; font-weight:600; color:var(--muted); cursor:pointer; transition:all .15s; }
.tab.active { background:var(--white); color:var(--ink); box-shadow:0 1px 4px rgba(0,0,0,.1); }

/* Warehouse layout */
.wh-layout { display:flex; gap:16px; min-height:400px; }
.wh-list-panel { width:280px; flex-shrink:0; background:var(--white); border:1px solid var(--border); border-radius:12px; overflow:hidden; display:flex; flex-direction:column; }
.wh-search { display:flex; align-items:center; gap:8px; padding:0 12px; border-bottom:1px solid var(--border); }
.wh-search svg { color:#9ca3af; flex-shrink:0; }
.wh-search input { border:none; outline:none; font-size:13px; background:transparent; height:40px; width:100%; color:var(--ink); }
.wh-list { overflow-y:auto; flex:1; }
.wh-item { display:flex; align-items:center; gap:10px; padding:12px 14px; cursor:pointer; transition:background .12s; border-bottom:1px solid var(--border); }
.wh-item:last-child { border-bottom:none; }
.wh-item:hover { background:var(--surface); }
.wh-item.selected { background:var(--accent-bg); border-left:3px solid var(--accent); }
.wh-item-icon { width:32px; height:32px; background:var(--surface); border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--muted); flex-shrink:0; }
.wh-item.selected .wh-item-icon { background:var(--accent-bg); color:var(--accent); }
.wh-item-info { flex:1; min-width:0; }
.wh-item-name { font-size:13.5px; font-weight:600; color:var(--ink); }
.wh-item-branch { font-size:11.5px; color:var(--muted); }
.wh-item-actions { display:flex; gap:4px; opacity:0; transition:opacity .15s; }
.wh-item:hover .wh-item-actions { opacity:1; }
.icon-btn { background:none; border:none; cursor:pointer; padding:3px; border-radius:4px; font-size:12px; }
.icon-btn:hover { background:var(--surface); }

.wh-stock-panel { flex:1; background:var(--white); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.stock-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; gap:12px; color:#9ca3af; }
.stock-placeholder p { font-size:14px; }
.stock-header { padding:16px 18px; border-bottom:1px solid var(--border); }
.stock-header h3 { font-size:16px; font-weight:700; color:var(--ink); }
.stock-header p { font-size:12.5px; color:var(--muted); margin-top:2px; }
.empty-state { text-align:center; padding:52px; color:#d1d5db; font-size:14px; }

.stock-table-wrap { overflow-x:auto; }
.stock-table { width:100%; border-collapse:collapse; }
.stock-table th { text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); padding:10px 18px 8px; border-bottom:1px solid var(--border); }
.stock-table td { padding:10px 18px; border-bottom:1px solid var(--border); font-size:13.5px; }
.stock-table tr:last-child td { border-bottom:none; }
.stock-table .right { text-align:right; }
.product-name { font-weight:600; color:var(--ink); }
.sku { font-size:12px; color:var(--muted); font-family:monospace; }
.low-stock td { background:#fef9f9; }

.qty-badge { display:inline-block; padding:2px 10px; border-radius:999px; font-size:12px; font-weight:700; }
.qty-badge.ok  { background:#dcfce7; color:#15803d; }
.qty-badge.low { background:#fee2e2; color:#dc2626; }

/* Transfers table */
.tcard { background:var(--white); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.tr-table { width:100%; border-collapse:collapse; }
.tr-table th { text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); padding:10px 14px 8px; border-bottom:1px solid var(--border); }
.tr-table td { padding:10px 14px; border-bottom:1px solid var(--border); font-size:13px; }
.tr-table tr:last-child td { border-bottom:none; }
.tr-table .center { text-align:center; }
.date-cell { white-space:nowrap; color:var(--muted); font-size:12px; }
.note-cell { color:var(--muted); font-size:12px; max-width:200px; }
.muted { color:var(--muted); }
.wh-label { display:inline-block; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:600; }
.wh-label.from { background:#fee2e2; color:#dc2626; }
.wh-label.to   { background:#dcfce7; color:#16a34a; }

/* Modal */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center; z-index:9999; padding:16px; }
.modal-box { background:var(--white); border-radius:14px; width:100%; max-width:480px; box-shadow:0 24px 60px rgba(0,0,0,.18); overflow:hidden; }
.modal-lg { max-width:560px; }
.modal-header { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid var(--border); }
.modal-title { display:flex; align-items:center; gap:8px; font-size:15px; font-weight:700; color:var(--ink); }
.modal-close { background:none; border:none; color:var(--muted); font-size:16px; cursor:pointer; padding:4px 8px; border-radius:6px; }
.modal-close:hover { background:var(--surface); }
.modal-body { padding:22px; display:flex; flex-direction:column; gap:14px; }
.field { display:flex; flex-direction:column; gap:6px; }
.field label { font-size:13.5px; font-weight:600; color:var(--ink); }
.req { color:#dc2626; }
.field input, .field select, .field textarea {
  width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px;
  font-size:13.5px; color:var(--ink); background:var(--white); outline:none;
  font-family:inherit; transition:border-color .15s; box-sizing:border-box;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color:var(--accent); }
.field textarea { resize:vertical; }
.alert-err-sm { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; font-size:12.5px; padding:8px 12px; border-radius:7px; }
.modal-actions { display:flex; gap:10px; }
.btn-cancel { flex:1; height:40px; background:none; border:1.5px solid var(--border); color:var(--muted); border-radius:8px; font-size:14px; cursor:pointer; }
.btn-cancel:hover { border-color:var(--ink); color:var(--ink); }
.btn-save { flex:2; height:40px; background:var(--accent); border:none; color:#fff; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-save:hover { background:var(--accent-dark); }
.btn-save:disabled { opacity:.6; cursor:not-allowed; }

.transfer-grid { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; gap:10px; }
.transfer-arrow { font-size:22px; color:var(--muted); text-align:center; font-weight:300; }

.modal-fade-enter-active { transition:opacity .2s ease; }
.modal-fade-leave-active  { transition:opacity .15s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity:0; }
</style>
