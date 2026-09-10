<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePosStore, type Product } from '@/stores/pos'
import { gsap } from 'gsap'
import AppLayout from '@/components/AppLayout.vue'

const auth   = useAuthStore()
const pos    = usePosStore()
const router = useRouter()

onMounted(async () => {
  await auth.fetchUser()
  await Promise.all([pos.fetchProducts(), pos.fetchActiveShift()])
  await nextTick()

  gsap.from('.panel-products', { opacity: 0, x: -20, duration: 0.45, ease: 'power2.out', delay: 0.1 })
  gsap.from('.panel-order', { opacity: 0, x: 20, duration: 0.45, ease: 'power2.out', delay: 0.1 })
})

async function logout() {
  await auth.logout()
  router.push('/login')
}

// ── UI state ───────────────────────────────────────────
const isProcessing   = ref(false)
const successMsg     = ref('')

// Modal shift
const showShiftModal = ref(false)
const shiftModalMode = ref<'open' | 'close'>('open')
const startingCash   = ref('0')
const endingCash     = ref('0')
const shiftSaving    = ref(false)
const shiftError     = ref('')

// Modal pembayaran
const showPayModal   = ref(false)
const payMethod      = ref<'cash' | 'qris' | 'transfer'>('cash')

// ── UI state ───────────────────────────────────────────
const activeCategory = ref('Semua')
const searchQuery    = ref('')
const customerName   = ref('')
const orderType      = ref('Dine in')
const tableNo        = ref('Meja 01')

// ── Shift actions ──────────────────────────────────────
function promptOpenShift()  { shiftModalMode.value = 'open';  shiftError.value = ''; startingCash.value = '0'; showShiftModal.value = true }
function promptCloseShift() { shiftModalMode.value = 'close'; shiftError.value = ''; endingCash.value   = '0'; showShiftModal.value = true }

async function doShiftAction() {
  shiftSaving.value = true; shiftError.value = ''
  try {
    if (shiftModalMode.value === 'open') {
      await pos.openShift(Number(startingCash.value) || 0)
    } else {
      await pos.closeShift(Number(endingCash.value) || 0)
    }
    showShiftModal.value = false
  } catch (e: unknown) {
    shiftError.value = e instanceof Error ? e.message : 'Gagal.'
  } finally { shiftSaving.value = false }
}

// ── Computed ───────────────────────────────────────────
const categories = computed(() => {
  const cats = new Set(pos.products.map(p => p.category?.name ?? 'Lainnya'))
  return ['Semua', ...Array.from(cats)]
})

const filteredProducts = computed(() => {
  let list = pos.products
  if (activeCategory.value !== 'Semua')
    list = list.filter(p => (p.category?.name ?? 'Lainnya') === activeCategory.value)
  if (searchQuery.value.trim())
    list = list.filter(p => p.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
  return list
})

const cart      = computed(() => pos.cart)
const subtotal  = computed(() => pos.subtotal)
const tax       = computed(() => pos.tax)
const total     = computed(() => pos.total)
const cartCount = computed(() => pos.cartCount)

// ── Cart actions ───────────────────────────────────────
function addToCart(product: Product) { pos.addToCart(product) }
function changeQty(item: typeof pos.cart[0], delta: number) { pos.changeQty(item, delta) }
function clearCart() { pos.clearCart() }

async function processTransaction() {
  if (!pos.cart.length || isProcessing.value) return
  if (!pos.hasShift) { promptOpenShift(); return }
  showPayModal.value = true
}

async function confirmPay() {
  isProcessing.value = true; successMsg.value = ''
  try {
    await pos.checkout(payMethod.value)
    showPayModal.value = false
    successMsg.value   = `Transaksi berhasil! Total: ${fmt(total.value)}`
    customerName.value = ''
    setTimeout(() => { successMsg.value = '' }, 4000)
  } catch (e: unknown) {
    alert(e instanceof Error ? e.message : 'Transaksi gagal.')
  } finally { isProcessing.value = false }
}

// ── Utils ──────────────────────────────────────────────
function fmt(n: number) { return 'Rp\u00A0' + n.toLocaleString('id-ID') }
</script>

<template>
  <AppLayout :full-height="true">
    <template #title>Kasir</template>
    <div class="pos-wrap">

      <!-- ── Panel kiri: Produk ── -->
      <section class="panel-products">

        <!-- Toolbar -->
        <div class="toolbar">
          <h2 class="panel-title">Produk</h2>
          <label class="search-wrap">
            <svg class="search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input v-model="searchQuery" type="search" placeholder="Cari produk…" class="search-inp" />
          </label>
        </div>

        <!-- Kategori -->
        <div class="cats" role="tablist">
          <button
            v-for="cat in categories" :key="cat"
            role="tab" :aria-selected="activeCategory === cat"
            class="cat-pill" :class="{ active: activeCategory === cat }"
            @click="activeCategory = cat"
          >{{ cat }}</button>
        </div>

        <!-- Grid -->
        <div v-if="pos.loading" class="empty">
          <span class="spinner-lg"></span> Memuat produk...
        </div>
        <div v-else-if="pos.error" class="empty err">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <span>{{ pos.error }}</span>
          <small>Pastikan <code>php artisan serve</code> sudah berjalan</small>
          <button class="btn-retry" @click="pos.fetchProducts()">↺ Coba lagi</button>
        </div>
        <div class="grid" v-else-if="filteredProducts.length">
          <button
            v-for="p in filteredProducts" :key="p.id"
            class="product-card" :class="{ 'product-card--out': p.stock <= 0 }"
            @click="addToCart(p)"
            :disabled="p.stock <= 0"
            :aria-label="'Tambah ' + p.name"
          >
            <!-- Gambar / foto -->
            <div class="card-img"
              :style="p.image ? `background-image:url(http://localhost:8000/storage/${p.image})` : ''">
              <div class="card-initial" v-if="!p.image">{{ p.name.charAt(0) }}</div>
              <span class="card-badge" :class="p.stock > 0 ? 'badge--on' : 'badge--off'">
                {{ p.stock > 0 ? 'Tersedia' : 'Habis' }}
              </span>
            </div>
            <div class="card-body">
              <p class="card-name">{{ p.name }}</p>
              <div class="card-foot">
                <span class="card-price">{{ fmt(p.price) }}</span>
                <span class="card-add" v-if="p.stock > 0">+ Tambah</span>
              </div>
            </div>
          </button>
        </div>
        <div v-else class="empty">Produk tidak ditemukan.</div>

      </section>

      <!-- ── Panel kanan: Order ── -->
      <aside class="panel-order">

        <!-- Status Shift -->
        <div class="shift-bar" :class="pos.hasShift ? 'shift-open' : 'shift-closed'">
          <div class="shift-info">
            <span class="shift-dot"></span>
            <span>{{ pos.hasShift ? 'Shift Aktif' : 'Shift Belum Dibuka' }}</span>
          </div>
          <button v-if="!pos.hasShift" class="shift-btn open-btn" @click="promptOpenShift">Buka Shift</button>
          <button v-else class="shift-btn close-btn" @click="promptCloseShift">Tutup</button>
        </div>

        <div class="order-head">
          <h2 class="panel-title">Detail Pesanan</h2>
          <span v-if="cartCount" class="order-badge">{{ cartCount }}</span>
        </div>

        <!-- Info pelanggan -->
        <div class="order-block">
          <p class="block-label">Informasi Pelanggan</p>
          <div class="field">
            <label for="cname">Nama</label>
            <input id="cname" v-model="customerName" type="text" placeholder="Nama pelanggan" />
          </div>
          <div class="field-row">
            <div class="field">
              <label>Tipe</label>
              <select v-model="orderType">
                <option>Dine in</option>
                <option>Take away</option>
              </select>
            </div>
            <div class="field">
              <label>Meja</label>
              <select v-model="tableNo">
                <option v-for="n in 10" :key="n">Meja {{ String(n).padStart(2,'0') }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Cart -->
        <div class="order-block order-block--grow">
          <div class="cart-head">
            <p class="block-label">{{ cartCount }} item dipilih</p>
            <button v-if="cart.length" class="btn-clear" @click="clearCart">Hapus semua</button>
          </div>

          <!-- Kosong -->
          <div v-if="!cart.length" class="cart-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none"
              stroke="#d4d4d4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 001.99 1.61h9.72a2 2 0 001.99-1.61L23 6H6"/>
            </svg>
            <span>Keranjang kosong</span>
          </div>

          <!-- Items -->
          <TransitionGroup v-else name="cart" tag="div" class="cart-list">
            <div v-for="item in cart" :key="item.product.id" class="cart-row">
              <div class="cart-thumb">{{ item.product.name.charAt(0) }}</div>
              <div class="cart-info">
                <p class="cart-name">{{ item.product.name }}</p>
                <div class="qty-row">
                  <button class="qty-btn" @click="changeQty(item, -1)">−</button>
                  <span class="qty-num">{{ item.qty }}</span>
                  <button class="qty-btn" @click="changeQty(item, +1)">+</button>
                </div>
              </div>
              <span class="cart-sub">{{ fmt(item.product.price * item.qty) }}</span>
            </div>
          </TransitionGroup>
        </div>

        <!-- Summary -->
        <div class="summary">
          <div class="sum-row">
            <span>Subtotal</span><span>{{ fmt(subtotal) }}</span>
          </div>
          <div class="sum-row">
            <span>Pajak (8%)</span><span>{{ fmt(tax) }}</span>
          </div>
          <div class="sum-row sum-total">
            <span>Total</span><strong>{{ fmt(total) }}</strong>
          </div>
        </div>

        <button class="btn-pay"
          :disabled="!cart.length || isProcessing"
          :class="{ 'btn-pay--no-shift': !pos.hasShift }"
          @click="processTransaction">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2"/>
            <line x1="1" y1="10" x2="23" y2="10"/>
          </svg>
          {{ pos.hasShift ? 'Proses Transaksi' : 'Buka Shift untuk Transaksi' }}
        </button>

        <!-- Success banner -->
        <Transition name="success">
          <div v-if="successMsg" class="success-msg">✓ {{ successMsg }}</div>
        </Transition>

      </aside>
    </div>

    <!-- ══ MODAL BUKA / TUTUP SHIFT ══ -->
    <Transition name="modal">
      <div v-if="showShiftModal" class="modal-overlay" @click.self="showShiftModal = false">
        <div class="modal-box">
          <div class="modal-hd">
            <h2>{{ shiftModalMode === 'open' ? '🔓 Buka Shift Kasir' : '🔒 Tutup Shift Kasir' }}</h2>
            <button class="modal-x" @click="showShiftModal = false">✕</button>
          </div>
          <div class="modal-bd">
            <div v-if="shiftModalMode === 'open'">
              <p class="modal-desc">Masukkan jumlah uang tunai awal di laci kasir sebelum mulai berjualan.</p>
              <div class="mfield">
                <label>Modal Awal (Rp)</label>
                <input v-model="startingCash" type="number" min="0" step="1000" placeholder="0" />
              </div>
            </div>
            <div v-else>
              <p class="modal-desc">Masukkan jumlah uang tunai yang ada di laci kasir saat ini untuk rekonsiliasi.</p>
              <div class="mfield">
                <label>Uang di Laci (Rp)</label>
                <input v-model="endingCash" type="number" min="0" step="1000" placeholder="0" />
              </div>
            </div>
            <div v-if="shiftError" class="modal-err">{{ shiftError }}</div>
          </div>
          <div class="modal-ft">
            <button class="mbtn-ghost" @click="showShiftModal = false">Batal</button>
            <button class="mbtn-primary" :disabled="shiftSaving" @click="doShiftAction">
              <span v-if="shiftSaving" class="mspin"></span>
              {{ shiftSaving ? 'Memproses...' : (shiftModalMode === 'open' ? 'Buka Shift' : 'Tutup Shift') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ MODAL PEMBAYARAN ══ -->
    <Transition name="modal">
      <div v-if="showPayModal" class="modal-overlay" @click.self="showPayModal = false">
        <div class="modal-box">
          <div class="modal-hd">
            <h2>💳 Pilih Metode Pembayaran</h2>
            <button class="modal-x" @click="showPayModal = false">✕</button>
          </div>
          <div class="modal-bd">
            <p class="modal-desc">Total yang harus dibayar: <strong>{{ fmt(total) }}</strong></p>
            <div class="pay-options">
              <button
                v-for="m in [{ key:'cash', label:'Tunai', icon:'💵' }, { key:'qris', label:'QRIS', icon:'📱' }, { key:'transfer', label:'Transfer', icon:'🏦' }]"
                :key="m.key"
                class="pay-opt"
                :class="{ active: payMethod === m.key }"
                @click="payMethod = m.key as 'cash'|'qris'|'transfer'"
              >
                <span class="pay-icon">{{ m.icon }}</span>
                <span>{{ m.label }}</span>
              </button>
            </div>
          </div>
          <div class="modal-ft">
            <button class="mbtn-ghost" @click="showPayModal = false">Batal</button>
            <button class="mbtn-primary" :disabled="isProcessing" @click="confirmPay">
              <span v-if="isProcessing" class="mspin"></span>
              {{ isProcessing ? 'Memproses...' : `Bayar ${fmt(total)}` }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </AppLayout>
</template>

<style scoped>
/* ── Reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Wrapper inside AppLayout ── */
.pos-wrap {
  --radius: 10px;
  display: grid;
  grid-template-columns: 1fr 300px;
  height: 100%;
  min-height: 0;
  font-size: 14px;
  color: var(--ink);
}

/* ── Panel produk ── */
.panel-products {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px 16px 18px 20px;
  overflow-y: auto;
  background: var(--surface);
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.panel-title {
  font-size: 17px;
  font-weight: 700;
  color: var(--ink);
  flex-shrink: 0;
}

.search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0 12px;
  flex: 1;
  max-width: 240px;
  transition: border-color 0.15s;
}

.search-wrap:focus-within { border-color: var(--accent); }

.search-ico { color: #9ca3af; flex-shrink: 0; }

.search-inp {
  border: none;
  outline: none;
  font-size: 13px;
  background: transparent;
  color: var(--ink);
  width: 100%;
  height: 36px;
}

.search-inp::placeholder { color: #d1d5db; }

/* Kategori */
.cats {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.cat-pill {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 999px;
  padding: 4px 14px;
  font-size: 12.5px;
  font-weight: 500;
  color: var(--muted);
  cursor: pointer;
  transition: all 0.15s;
}

.cat-pill:hover { border-color: var(--accent); color: var(--accent); }

.cat-pill.active {
  background: var(--accent-bg);
  border-color: var(--accent);
  color: var(--accent);
  font-weight: 600;
}

/* Grid produk */
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
}

.product-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  cursor: pointer;
  text-align: left;
  transition: box-shadow 0.18s, transform 0.18s, border-color 0.15s;
  position: relative;
  padding: 0;
}

.product-card:hover:not(:disabled) {
  box-shadow: 0 4px 16px rgba(0,0,0,0.07);
  transform: translateY(-2px);
  border-color: var(--accent-ring);
}

.product-card:active:not(:disabled) {
  transform: scale(0.98);
}

.product-card--out { opacity: 0.55; cursor: not-allowed; }

.card-img {
  height: 108px;
  background: var(--surface);
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.card-initial {
  width: 48px;
  height: 48px;
  background: var(--accent-bg);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 800;
  color: var(--accent);
}

.card-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
}

.badge--on  { background: var(--accent-bg); color: var(--accent-dark); }
.badge--off { background: #fee2e2; color: #dc2626; }

.card-body {
  padding: 10px 12px 12px;
}

.card-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--ink);
  line-height: 1.4;
  margin-bottom: 8px;
  min-height: 36px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
}

.card-price {
  font-size: 13px;
  font-weight: 700;
  color: var(--ink);
}

.card-add {
  font-size: 11px;
  font-weight: 600;
  color: var(--accent);
  background: var(--accent-bg);
  padding: 3px 8px;
  border-radius: 5px;
  white-space: nowrap;
}

.empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 48px 0;
  color: #d1d5db;
  font-size: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.empty.err { color: #ef4444; }
.empty.err span { font-size: 14px; font-weight: 600; }
.empty.err small { font-size: 12px; color: var(--muted); }
.empty.err code {
  background: var(--surface);
  padding: 1px 6px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 11px;
  color: var(--ink);
}

.spinner-lg {
  width: 24px;
  height: 24px;
  border: 2.5px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin-lg 0.7s linear infinite;
}

@keyframes spin-lg { to { transform: rotate(360deg); } }

.btn-retry {
  background: none;
  border: 1.5px solid var(--border);
  color: var(--muted);
  padding: 6px 16px;
  border-radius: 7px;
  font-size: 13px;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}
.btn-retry:hover { border-color: var(--accent); color: var(--accent); }

/* ── Panel order ── */
.panel-order {
  background: var(--white);
  border-left: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  padding: 18px 16px;
  gap: 14px;
  overflow-y: auto;
}

.order-head {
  display: flex;
  align-items: center;
  gap: 10px;
}

.order-badge {
  background: var(--accent);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 999px;
}

/* Block */
.order-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.order-block--grow { flex: 1; min-height: 0; }

.block-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Fields */
.field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.field label {
  font-size: 11px;
  font-weight: 500;
  color: #9ca3af;
}

.field input,
.field select {
  width: 100%;
  height: 38px;
  padding: 0 10px;
  border: 1px solid var(--border);
  border-radius: 7px;
  font-size: 13px;
  color: var(--ink);
  background: var(--surface);
  outline: none;
  -webkit-appearance: none;
  appearance: none;
  transition: border-color 0.15s;
}

.field input:focus,
.field select:focus {
  border-color: var(--accent);
  background: var(--white);
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

/* Cart */
.cart-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.btn-clear {
  background: none;
  border: none;
  font-size: 12px;
  color: #ef4444;
  cursor: pointer;
  font-weight: 500;
}
.btn-clear:hover { text-decoration: underline; }

.cart-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: #d1d5db;
  font-size: 13px;
  padding: 32px 0;
}

.cart-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  overflow-y: auto;
  max-height: 260px;
  padding-right: 2px;
}

.cart-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: var(--surface);
  border-radius: 8px;
  border: 1px solid var(--border);
}

.cart-thumb {
  width: 34px;
  height: 34px;
  background: var(--accent-bg);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
  color: var(--accent);
  flex-shrink: 0;
}

.cart-info { flex: 1; min-width: 0; }

.cart-name {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--ink);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 5px;
}

.qty-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.qty-btn {
  width: 22px;
  height: 22px;
  border: 1px solid var(--border);
  background: var(--white);
  border-radius: 5px;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--muted);
  transition: background 0.12s, border-color 0.12s, color 0.12s;
  line-height: 1;
}

.qty-btn:hover {
  background: var(--accent-bg);
  border-color: var(--accent-ring);
  color: var(--accent);
}

.qty-num {
  font-size: 13px;
  font-weight: 700;
  color: var(--ink);
  min-width: 18px;
  text-align: center;
}

.cart-sub {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--ink);
  white-space: nowrap;
  flex-shrink: 0;
}

/* Summary */
.summary {
  border-top: 1px solid var(--border);
  padding-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.sum-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: var(--muted);
}

.sum-total {
  font-size: 14.5px;
  color: var(--ink);
  font-weight: 600;
  padding-top: 6px;
  border-top: 1px dashed var(--border);
}

.sum-total strong {
  font-size: 16px;
  font-weight: 800;
  color: var(--accent-dark);
}

/* Tombol bayar */
.btn-pay {
  width: 100%;
  height: 46px;
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
  flex-shrink: 0;
}

.btn-pay:hover:not(:disabled) {
  background: var(--accent-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px color-mix(in srgb, var(--accent) 35%, transparent);
}

.btn-pay:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-pay--no-shift {
  background: var(--muted) !important;
}

/* Success banner */
.success-msg {
  background: var(--accent-bg);
  border: 1px solid var(--accent-ring);
  color: var(--accent-dark);
  font-size: 13px; font-weight: 600;
  padding: 10px 14px; border-radius: 8px;
  text-align: center; flex-shrink: 0;
  transition: background 0.3s, color 0.3s, border-color 0.3s;
}

.success-enter-active { transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.success-leave-active  { transition: all 0.2s ease-in; }
.success-enter-from   { opacity: 0; transform: translateY(8px) scale(0.97); }
.success-leave-to     { opacity: 0; transform: translateY(-4px); }

/* ── Cart TransitionGroup ── */
.cart-enter-active { transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1); }
.cart-leave-active  { transition: all 0.18s ease-in; }
.cart-enter-from    { opacity: 0; transform: translateX(-12px) scale(0.97); }
.cart-leave-to      { opacity: 0; transform: translateX(12px) scale(0.97); }
.cart-move          { transition: transform 0.22s ease; }

/* ── Responsive ── */
@media (max-width: 960px) {
  .pos-wrap { grid-template-columns: 1fr 280px; }
}

@media (max-width: 768px) {
  .pos-wrap {
    grid-template-columns: 1fr;
    height: auto;
    overflow: visible;
  }

  .panel-order {
    border-left: none;
    border-top: 1px solid var(--border);
    max-height: none;
  }
}

@media (max-width: 480px) {
  .panel-products { padding: 14px 12px; }
  .grid { grid-template-columns: repeat(2, 1fr); }
  .search-wrap { max-width: 160px; }
}

/* ── Shift bar ── */
.shift-bar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 14px; border-bottom: 1px solid var(--border);
  font-size: 12.5px; font-weight: 600; gap: 8px;
}
.shift-bar.shift-open   { background: #f0fdf4; color: #15803d; }
.shift-bar.shift-closed { background: #fef9c3; color: #92400e; }
.shift-info { display: flex; align-items: center; gap: 7px; }
.shift-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.shift-open .shift-dot   { background: #16a34a; box-shadow: 0 0 0 2px #bbf7d0; }
.shift-closed .shift-dot { background: #d97706; box-shadow: 0 0 0 2px #fde68a; }
.shift-btn { border: none; cursor: pointer; font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 6px; transition: opacity 0.15s; }
.shift-btn.open-btn  { background: #16a34a; color: #fff; }
.shift-btn.close-btn { background: #d97706; color: #fff; }
.shift-btn:hover { opacity: 0.85; }

/* ── Modal shift/pay ── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.45);
  display: flex; align-items: center; justify-content: center; z-index: 300; padding: 20px;
}
.modal-box { background: var(--white); border-radius: 14px; width: 100%; max-width: 420px; box-shadow: 0 24px 64px rgba(0,0,0,0.15); }
.modal-hd { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px 14px; border-bottom: 1px solid var(--border); }
.modal-hd h2 { font-size: 16px; font-weight: 800; color: var(--ink); }
.modal-x { background: none; border: none; font-size: 16px; cursor: pointer; color: var(--muted); width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
.modal-x:hover { background: var(--surface); }
.modal-bd { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.modal-desc { font-size: 13.5px; color: var(--muted); line-height: 1.5; }
.modal-desc strong { color: var(--ink); font-weight: 700; }
.modal-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 13px; padding: 8px 12px; border-radius: 6px; }
.mfield { display: flex; flex-direction: column; gap: 6px; }
.mfield label { font-size: 12.5px; font-weight: 600; color: #374151; }
.mfield input { height: 42px; padding: 0 14px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 15px; font-weight: 600; color: var(--ink); background: var(--white); outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
.mfield input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }
.pay-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.pay-opt { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 16px 8px; border: 2px solid var(--border); border-radius: 10px; cursor: pointer; background: var(--surface); font-size: 13px; font-weight: 600; color: var(--muted); transition: all 0.15s; }
.pay-opt:hover { border-color: var(--accent-ring); color: var(--accent); background: var(--accent-bg); }
.pay-opt.active { border-color: var(--accent); color: var(--accent); background: var(--accent-bg); }
.pay-icon { font-size: 26px; }
.modal-ft { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px; border-top: 1px solid var(--border); }
.mbtn-primary { background: var(--accent); color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.15s; display: flex; align-items: center; gap: 8px; }
.mbtn-primary:hover:not(:disabled) { background: var(--accent-dark); }
.mbtn-primary:disabled { opacity: 0.55; cursor: not-allowed; }
.mbtn-ghost { background: none; border: 1.5px solid var(--border); color: var(--muted); padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: border-color 0.15s; }
.mbtn-ghost:hover { border-color: var(--accent-ring); }
.mspin { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: mspin 0.55s linear infinite; }
@keyframes mspin { to { transform: rotate(360deg); } }
.modal-enter-active { transition: all 0.22s cubic-bezier(0.34,1.56,0.64,1); }
.modal-leave-active  { transition: all 0.16s ease-in; }
.modal-enter-from   { opacity: 0; transform: scale(0.95) translateY(8px); }
.modal-leave-to     { opacity: 0; transform: scale(0.97); }
</style>
