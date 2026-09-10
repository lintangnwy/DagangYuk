<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/AppLayout.vue'

const auth = useAuthStore()
const API  = 'http://localhost:8000/api'
const IMG  = 'http://localhost:8000/storage'

interface Category { id: number; name: string }
interface Product  {
  id: number; name: string; sku: string | null
  price: number; cost_price: number; stock: number
  image: string | null; category_id: number | null
  category?: Category
}

const activeTab  = ref<'products' | 'categories'>('products')
const loading    = ref(false)
const saving     = ref(false)
const catSaving  = ref(false)
const error      = ref('')

function authH(): HeadersInit {
  return { Authorization: `Bearer ${auth.token}`, Accept: 'application/json' }
}

// ── Produk ────────────────────────────────────────────
const products   = ref<Product[]>([])
const categories = ref<Category[]>([])
const prodSearch = ref('')

const filteredProducts = computed(() => {
  const q = prodSearch.value.toLowerCase()
  return q ? products.value.filter(p => p.name.toLowerCase().includes(q) || p.sku?.toLowerCase().includes(q)) : products.value
})

const showProdModal = ref(false)
const editProd      = ref<Product | null>(null)
const imageFile     = ref<File | null>(null)
const imagePreview  = ref<string | null>(null)
const prodForm      = ref({ name: '', sku: '', price: '', cost_price: '', stock: '0', category_id: '' as string | number })
const showDelProd   = ref(false)
const delProdTarget = ref<Product | null>(null)

async function loadProducts() {
  loading.value = true; error.value = ''
  try {
    const [pR, cR] = await Promise.all([
      fetch(`${API}/products`,   { headers: authH() }),
      fetch(`${API}/categories`, { headers: authH() }),
    ])
    if (!pR.ok) throw new Error('Gagal memuat produk.')
    products.value   = await pR.json()
    categories.value = cR.ok ? await cR.json() : []
  } catch (e: unknown) { error.value = e instanceof Error ? e.message : 'Error.' }
  finally { loading.value = false }
}

function openCreateProd() {
  editProd.value = null; imageFile.value = null; imagePreview.value = null
  prodForm.value = { name: '', sku: '', price: '', cost_price: '', stock: '0', category_id: '' }
  showProdModal.value = true
}

function openEditProd(p: Product) {
  editProd.value = p; imageFile.value = null
  imagePreview.value = p.image ? `${IMG}/${p.image}` : null
  prodForm.value = { name: p.name, sku: p.sku ?? '', price: String(p.price), cost_price: String(p.cost_price), stock: String(p.stock), category_id: p.category_id ?? '' }
  showProdModal.value = true
}

function onImageChange(e: Event) {
  const f = (e.target as HTMLInputElement).files?.[0]; if (!f) return
  imageFile.value = f; imagePreview.value = URL.createObjectURL(f)
}

async function saveProd() {
  if (!prodForm.value.name || !prodForm.value.price) return
  saving.value = true; error.value = ''
  try {
    const fd = new FormData()
    fd.append('name', prodForm.value.name)
    fd.append('price', prodForm.value.price)
    fd.append('cost_price', prodForm.value.cost_price || '0')
    fd.append('stock', prodForm.value.stock || '0')
    fd.append('tenant_id', String(auth.user?.tenant_id ?? 1))
    if (prodForm.value.sku)         fd.append('sku', prodForm.value.sku)
    if (prodForm.value.category_id) fd.append('category_id', String(prodForm.value.category_id))
    if (imageFile.value)            fd.append('image', imageFile.value)
    const url = editProd.value ? `${API}/products/${editProd.value.id}` : `${API}/products`
    const res = await fetch(url, { method: 'POST', headers: { Authorization: `Bearer ${auth.token}`, Accept: 'application/json' }, body: fd })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message ?? 'Gagal menyimpan.')
    showProdModal.value = false; await loadProducts()
  } catch (e: unknown) { error.value = e instanceof Error ? e.message : 'Error.' }
  finally { saving.value = false }
}

function confirmDelProd(p: Product) { delProdTarget.value = p; showDelProd.value = true }

async function doDelProd() {
  if (!delProdTarget.value) return
  await fetch(`${API}/products/${delProdTarget.value.id}`, { method: 'DELETE', headers: authH() })
  products.value = products.value.filter(p => p.id !== delProdTarget.value!.id)
  showDelProd.value = false; delProdTarget.value = null
}

// ── Kategori ──────────────────────────────────────────
const catSearch    = ref('')
const showCatModal = ref(false)
const editCat      = ref<Category | null>(null)
const catForm      = ref({ name: '' })
const showDelCat   = ref(false)
const delCatTarget = ref<Category | null>(null)

const filteredCats = computed(() => {
  const q = catSearch.value.toLowerCase()
  return q ? categories.value.filter(c => c.name.toLowerCase().includes(q)) : categories.value
})

function openCreateCat() { editCat.value = null; catForm.value = { name: '' }; showCatModal.value = true }
function openEditCat(c: Category) { editCat.value = c; catForm.value = { name: c.name }; showCatModal.value = true }

async function saveCat() {
  if (!catForm.value.name.trim()) return
  catSaving.value = true; error.value = ''
  try {
    const body = JSON.stringify({ name: catForm.value.name, tenant_id: auth.user?.tenant_id ?? 1 })
    const url  = editCat.value ? `${API}/categories/${editCat.value.id}` : `${API}/categories`
    const res  = await fetch(url, { method: editCat.value ? 'PUT' : 'POST', headers: { ...authH(), 'Content-Type': 'application/json' }, body })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message ?? 'Gagal menyimpan.')
    showCatModal.value = false
    const r = await fetch(`${API}/categories`, { headers: authH() })
    if (r.ok) categories.value = await r.json()
  } catch (e: unknown) { error.value = e instanceof Error ? e.message : 'Error.' }
  finally { catSaving.value = false }
}

function confirmDelCat(c: Category) { delCatTarget.value = c; showDelCat.value = true }

async function doDelCat() {
  if (!delCatTarget.value) return
  const res = await fetch(`${API}/categories/${delCatTarget.value.id}`, { method: 'DELETE', headers: authH() })
  if (res.ok) categories.value = categories.value.filter(c => c.id !== delCatTarget.value!.id)
  else { const d = await res.json(); error.value = d.message ?? 'Gagal menghapus.' }
  showDelCat.value = false; delCatTarget.value = null
}

function fmt(n: number) { return 'Rp\u00A0' + n.toLocaleString('id-ID') }

onMounted(async () => { await auth.fetchUser(); loadProducts() })
</script>

<template>
  <AppLayout>
    <template #title>
      {{ activeTab === 'products' ? 'Produk' : 'Kategori' }}
    </template>

    <!-- Page header -->
    <div class="page-head">
      <div>
        <h1>{{ activeTab === 'products' ? 'Manajemen Produk' : 'Manajemen Kategori' }}</h1>
        <p>{{ activeTab === 'products' ? 'Kelola produk yang tersedia di kasir' : 'Kelola kategori produk toko' }}</p>
      </div>
      <div class="head-right">
        <!-- Tab switcher -->
        <div class="tab-pills">
          <button class="tab-pill" :class="{ active: activeTab === 'products' }"
            @click="activeTab = 'products'">
            Produk <span class="pill-count">{{ products.length }}</span>
          </button>
          <button class="tab-pill" :class="{ active: activeTab === 'categories' }"
            @click="activeTab = 'categories'">
            Kategori <span class="pill-count">{{ categories.length }}</span>
          </button>
        </div>
        <button class="btn-primary" @click="activeTab === 'products' ? openCreateProd() : openCreateCat()">
          + Tambah {{ activeTab === 'products' ? 'Produk' : 'Kategori' }}
        </button>
      </div>
    </div>

    <div v-if="error" class="alert-err">{{ error }}</div>

    <!-- ══ TAB PRODUK ══ -->
    <div v-if="activeTab === 'products'">
      <div class="toolbar">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input v-model="prodSearch" type="search" placeholder="Cari nama atau SKU…" />
        </div>
        <span class="count-txt">{{ filteredProducts.length }} produk</span>
      </div>

      <div class="tcard">
        <div v-if="loading" class="empty-state">Memuat...</div>
        <div v-else-if="!filteredProducts.length" class="empty-state">
          {{ prodSearch ? 'Produk tidak ditemukan.' : 'Belum ada produk. Klik "+ Tambah Produk".' }}
        </div>
        <table v-else>
          <thead><tr><th>Produk</th><th>Kategori</th><th>Harga Jual</th><th>Harga Beli</th><th>Stok</th><th>Aksi</th></tr></thead>
          <tbody>
            <tr v-for="p in filteredProducts" :key="p.id">
              <td>
                <div class="prod-cell">
                  <div class="prod-thumb" :style="p.image ? `background-image:url(${IMG}/${p.image})` : ''">
                    <span v-if="!p.image">{{ p.name.charAt(0) }}</span>
                  </div>
                  <div><p class="fw">{{ p.name }}</p><p class="muted-sm">{{ p.sku || '—' }}</p></div>
                </div>
              </td>
              <td><span class="cat-b">{{ p.category?.name ?? '—' }}</span></td>
              <td class="fw">{{ fmt(p.price) }}</td>
              <td class="muted">{{ fmt(p.cost_price) }}</td>
              <td><span class="stock-b" :class="p.stock > 0 ? 'ok' : 'out'">{{ p.stock }} pcs</span></td>
              <td>
                <div class="row-act">
                  <button class="btn-edit" @click="openEditProd(p)">Edit</button>
                  <button class="btn-del"  @click="confirmDelProd(p)">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══ TAB KATEGORI ══ -->
    <div v-else>
      <div class="toolbar">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input v-model="catSearch" type="search" placeholder="Cari kategori…" />
        </div>
        <span class="count-txt">{{ filteredCats.length }} kategori</span>
      </div>

      <div class="tcard">
        <div v-if="loading" class="empty-state">Memuat...</div>
        <div v-else-if="!filteredCats.length" class="empty-state">
          {{ catSearch ? 'Tidak ditemukan.' : 'Belum ada kategori. Klik "+ Tambah Kategori".' }}
        </div>
        <table v-else>
          <thead><tr><th>Nama Kategori</th><th>Jumlah Produk</th><th>Aksi</th></tr></thead>
          <tbody>
            <tr v-for="c in filteredCats" :key="c.id">
              <td>
                <div class="cat-cell">
                  <div class="cat-icon">{{ c.name.charAt(0) }}</div>
                  <span class="fw">{{ c.name }}</span>
                </div>
              </td>
              <td class="muted">{{ products.filter(p => p.category_id === c.id).length }} produk</td>
              <td>
                <div class="row-act">
                  <button class="btn-edit" @click="openEditCat(c)">Edit</button>
                  <button class="btn-del"  @click="confirmDelCat(c)">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══ MODAL PRODUK ══ -->
    <Transition name="modal">
      <div v-if="showProdModal" class="overlay" @click.self="showProdModal = false">
        <div class="modal">
          <div class="modal-head">
            <h2>{{ editProd ? 'Edit Produk' : 'Tambah Produk' }}</h2>
            <button class="modal-x" @click="showProdModal = false">✕</button>
          </div>
          <form @submit.prevent="saveProd" class="modal-body">
            <div class="upload-zone" @click="($refs.imgInput as HTMLInputElement).click()">
              <input ref="imgInput" type="file" accept="image/*" class="hidden" @change="onImageChange" />
              <div v-if="imagePreview" class="upload-prev" :style="`background-image:url(${imagePreview})`">
                <span class="upload-ov">Ganti foto</span>
              </div>
              <div v-else class="upload-ph">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                </svg>
                <span>Upload foto produk</span><small>JPG, PNG, WebP · maks 2MB</small>
              </div>
            </div>
            <div class="form-grid">
              <div class="field span2"><label>Nama Produk <span class="req">*</span></label><input v-model="prodForm.name" type="text" placeholder="Nama produk" required /></div>
              <div class="field"><label>SKU</label><input v-model="prodForm.sku" type="text" placeholder="Opsional" /></div>
              <div class="field"><label>Kategori</label>
                <select v-model="prodForm.category_id">
                  <option value="">— Pilih —</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="field"><label>Harga Jual <span class="req">*</span></label><input v-model="prodForm.price" type="number" min="0" step="100" placeholder="0" required /></div>
              <div class="field"><label>Harga Beli</label><input v-model="prodForm.cost_price" type="number" min="0" step="100" placeholder="0" /></div>
              <div class="field span2"><label>Stok</label><input v-model="prodForm.stock" type="number" min="0" placeholder="0" /></div>
            </div>
            <div v-if="error" class="alert-err sm">{{ error }}</div>
            <div class="modal-foot">
              <button type="button" class="btn-ghost" @click="showProdModal = false">Batal</button>
              <button type="submit" class="btn-primary" :disabled="saving">
                <span v-if="saving" class="spin"></span>
                {{ saving ? 'Menyimpan...' : (editProd ? 'Simpan Perubahan' : 'Tambah Produk') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ══ MODAL KATEGORI ══ -->
    <Transition name="modal">
      <div v-if="showCatModal" class="overlay" @click.self="showCatModal = false">
        <div class="modal modal-sm">
          <div class="modal-head"><h2>{{ editCat ? 'Edit Kategori' : 'Tambah Kategori' }}</h2><button class="modal-x" @click="showCatModal = false">✕</button></div>
          <form @submit.prevent="saveCat" class="modal-body">
            <div class="field"><label>Nama Kategori <span class="req">*</span></label><input v-model="catForm.name" type="text" placeholder="Contoh: Minuman" required autofocus /></div>
            <div v-if="error" class="alert-err sm">{{ error }}</div>
            <div class="modal-foot">
              <button type="button" class="btn-ghost" @click="showCatModal = false">Batal</button>
              <button type="submit" class="btn-primary" :disabled="catSaving">
                <span v-if="catSaving" class="spin"></span>
                {{ catSaving ? 'Menyimpan...' : (editCat ? 'Simpan' : 'Tambah') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ══ KONFIRMASI HAPUS ══ -->
    <Transition name="modal">
      <div v-if="showDelProd" class="overlay" @click.self="showDelProd = false">
        <div class="modal modal-sm confirm">
          <h2>Hapus Produk?</h2>
          <p>Produk <strong>{{ delProdTarget?.name }}</strong> akan dihapus permanen.</p>
          <div class="modal-foot"><button class="btn-ghost" @click="showDelProd = false">Batal</button><button class="btn-danger" @click="doDelProd">Hapus</button></div>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showDelCat" class="overlay" @click.self="showDelCat = false">
        <div class="modal modal-sm confirm">
          <h2>Hapus Kategori?</h2>
          <p>Kategori <strong>{{ delCatTarget?.name }}</strong> akan dihapus. Produk terkait tidak ikut terhapus.</p>
          <div class="modal-foot"><button class="btn-ghost" @click="showDelCat = false">Batal</button><button class="btn-danger" @click="doDelCat">Hapus</button></div>
        </div>
      </div>
    </Transition>

  </AppLayout>
</template>

<style scoped>
.page-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.page-head h1 { font-size: 20px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13px; color: var(--muted); margin-top: 2px; }
.head-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.tab-pills { display: flex; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 3px; gap: 2px; }
.tab-pill { background: none; border: none; cursor: pointer; padding: 5px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; color: var(--muted); transition: background .15s, color .15s; display: flex; align-items: center; gap: 6px; }
.tab-pill.active { background: var(--white); color: var(--accent); font-weight: 700; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.pill-count { background: var(--accent-bg); color: var(--accent); font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 999px; }
.tab-pill.active .pill-count { background: var(--accent); color: #fff; }

.btn-primary { display: inline-flex; align-items: center; gap: 6px; background: var(--accent); color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: background .15s; white-space: nowrap; }
.btn-primary:hover:not(:disabled) { background: var(--accent-dark); }
.btn-primary:disabled { opacity: .55; cursor: not-allowed; }

.btn-ghost { background: none; border: 1.5px solid var(--border); color: var(--muted); padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: border-color .15s, color .15s; }
.btn-ghost:hover { border-color: var(--accent-ring); color: var(--accent); }

.btn-danger { background: #ef4444; color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-danger:hover { background: #dc2626; }

.alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 13px; padding: 10px 14px; border-radius: 7px; margin-bottom: 14px; }
.alert-err.sm { margin-top: 4px; margin-bottom: 0; }

.toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.search-box { display: flex; align-items: center; gap: 8px; background: var(--white); border: 1px solid var(--border); border-radius: 8px; padding: 0 12px; max-width: 280px; flex: 1; transition: border-color .15s; }
.search-box:focus-within { border-color: var(--accent); }
.search-box svg { color: #9ca3af; flex-shrink: 0; }
.search-box input { border: none; outline: none; font-size: 13px; background: transparent; color: var(--ink); height: 38px; width: 100%; }
.search-box input::placeholder { color: #d1d5db; }
.count-txt { font-size: 13px; color: var(--muted); white-space: nowrap; }

.tcard { background: var(--white); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.empty-state { text-align: center; padding: 52px; color: #d1d5db; font-size: 14px; }

table { width: 100%; border-collapse: collapse; }
thead { background: var(--surface); }
th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); padding: 11px 16px; border-bottom: 1px solid var(--border); }
td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 13.5px; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface); }

.prod-cell { display: flex; align-items: center; gap: 10px; }
.prod-thumb { width: 38px; height: 38px; border-radius: 8px; background: var(--accent-bg); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800; color: var(--accent); flex-shrink: 0; }
.fw { font-weight: 600; color: var(--ink); }
.muted { color: var(--muted); }
.muted-sm { font-size: 12px; color: var(--muted); margin-top: 1px; }

.cat-b { background: var(--accent-bg); color: var(--accent); font-size: 11.5px; font-weight: 600; padding: 2px 10px; border-radius: 999px; }
.stock-b { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
.stock-b.ok  { background: var(--accent-bg); color: var(--accent-dark); }
.stock-b.out { background: #fee2e2; color: #dc2626; }

.cat-cell { display: flex; align-items: center; gap: 10px; }
.cat-icon { width: 32px; height: 32px; border-radius: 8px; background: var(--accent-bg); color: var(--accent); font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

.row-act { display: flex; gap: 6px; }
.btn-edit { font-size: 12px; font-weight: 600; color: var(--accent); background: var(--accent-bg); border: 1px solid var(--accent-ring); padding: 4px 12px; border-radius: 6px; cursor: pointer; transition: background .15s; }
.btn-edit:hover { background: var(--accent-ring); }
.btn-del  { font-size: 12px; font-weight: 600; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; padding: 4px 12px; border-radius: 6px; cursor: pointer; transition: background .15s; }
.btn-del:hover { background: #fee2e2; }

/* Modal */
.overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 200; padding: 20px; }
.modal { background: var(--white); border-radius: 14px; width: 100%; max-width: 520px; max-height: 90dvh; overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,.15); }
.modal-sm { max-width: 380px; }
.modal-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 24px 14px; border-bottom: 1px solid var(--border); }
.modal-head h2 { font-size: 17px; font-weight: 800; letter-spacing: -.3px; }
.modal-x { background: none; border: none; font-size: 16px; cursor: pointer; color: var(--muted); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: background .15s; }
.modal-x:hover { background: var(--surface); color: var(--ink); }
.modal-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }
.modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 24px; border-top: 1px solid var(--border); }

.confirm { padding: 28px 24px; }
.confirm h2 { font-size: 18px; font-weight: 800; margin-bottom: 10px; }
.confirm p  { font-size: 14px; color: var(--muted); margin-bottom: 24px; }

/* Upload */
.upload-zone { border: 2px dashed var(--border); border-radius: 10px; cursor: pointer; overflow: hidden; transition: border-color .15s; }
.upload-zone:hover { border-color: var(--accent); }
.upload-prev { height: 140px; background-size: cover; background-position: center; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 12px; }
.upload-ov { background: rgba(0,0,0,.55); color: #fff; font-size: 12px; padding: 4px 12px; border-radius: 6px; }
.upload-ph { height: 100px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; color: var(--muted); }
.upload-ph svg { color: #d1d5db; }
.upload-ph span { font-size: 13px; font-weight: 500; }
.upload-ph small { font-size: 11px; color: var(--subtle, #9ca3af); }
.hidden { display: none; }

/* Form grid */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { display: flex; flex-direction: column; gap: 5px; }
.field.span2 { grid-column: 1 / -1; }
.field label { font-size: 12.5px; font-weight: 600; color: #374151; }
.req { color: #ef4444; }
.field input, .field select { height: 40px; padding: 0 12px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 14px; color: var(--ink); background: var(--white); outline: none; -webkit-appearance: none; appearance: none; transition: border-color .15s, box-shadow .15s; }
.field input:focus, .field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }
.field input::placeholder { color: #d1d5db; }

/* Spin */
.spin { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: sp .55s linear infinite; flex-shrink: 0; }
@keyframes sp { to { transform: rotate(360deg); } }

/* Modal transition */
.modal-enter-active { transition: all .22s cubic-bezier(.34,1.56,.64,1); }
.modal-leave-active  { transition: all .16s ease-in; }
.modal-enter-from   { opacity: 0; transform: scale(.95) translateY(8px); }
.modal-leave-to     { opacity: 0; transform: scale(.97); }

@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
  .field.span2 { grid-column: auto; }
  th:nth-child(4), td:nth-child(4) { display: none; }
  .head-right { flex-direction: column; align-items: flex-start; }
}
</style>
