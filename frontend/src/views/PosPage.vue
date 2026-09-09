<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { gsap } from 'gsap'
import ThemeSwitcher from '@/components/ThemeSwitcher.vue'

const auth   = useAuthStore()
const router = useRouter()

onMounted(async () => {
  await auth.fetchUser()
  await nextTick()

  gsap.from('.navbar', { opacity: 0, y: -16, duration: 0.4, ease: 'power2.out' })
  gsap.from('.panel-products', { opacity: 0, x: -20, duration: 0.45, ease: 'power2.out', delay: 0.1 })
  gsap.from('.panel-order', { opacity: 0, x: 20, duration: 0.45, ease: 'power2.out', delay: 0.1 })
  gsap.from('.product-card', {
    opacity: 0, y: 16, scale: 0.97,
    duration: 0.35, stagger: 0.04, ease: 'power2.out', delay: 0.25,
  })
})

async function logout() {
  await auth.logout()
  router.push('/login')
}

// ── Types ──────────────────────────────────────────────
interface Product {
  id: number
  name: string
  price: number
  category: string
  available: boolean
}

interface CartItem {
  product: Product
  qty: number
}

// ── Data ───────────────────────────────────────────────
const allProducts: Product[] = [
  { id: 1,  name: 'Nasi Goreng Spesial', price: 25000, category: 'Makanan',  available: true  },
  { id: 2,  name: 'Mie Ayam Bakso',      price: 20000, category: 'Makanan',  available: true  },
  { id: 3,  name: 'Soto Ayam',           price: 18000, category: 'Makanan',  available: false },
  { id: 4,  name: 'Ayam Bakar',          price: 30000, category: 'Makanan',  available: true  },
  { id: 5,  name: 'Gado-Gado',           price: 15000, category: 'Makanan',  available: true  },
  { id: 6,  name: 'Pecel Lele',          price: 22000, category: 'Makanan',  available: false },
  { id: 7,  name: 'Es Teh Manis',        price: 5000,  category: 'Minuman',  available: true  },
  { id: 8,  name: 'Es Jeruk',            price: 8000,  category: 'Minuman',  available: true  },
  { id: 9,  name: 'Jus Alpukat',         price: 15000, category: 'Minuman',  available: true  },
  { id: 10, name: 'Kopi Hitam',          price: 8000,  category: 'Minuman',  available: true  },
  { id: 11, name: 'Kerupuk',             price: 3000,  category: 'Lainnya',  available: true  },
  { id: 12, name: 'Tempe Goreng',        price: 5000,  category: 'Lainnya',  available: true  },
]

const categories   = ['Semua', 'Makanan', 'Minuman', 'Lainnya']
const activeCategory = ref('Semua')
const searchQuery    = ref('')
const cart           = ref<CartItem[]>([])
const customerName   = ref('')
const orderType      = ref('Dine in')
const tableNo        = ref('Meja 01')
const activeNav      = ref('Kasir')

const navItems = ['Dashboard', 'Kasir', 'Riwayat', 'Transaksi', 'Stok']

// ── Computed ───────────────────────────────────────────
const filteredProducts = computed(() => {
  let list = allProducts
  if (activeCategory.value !== 'Semua')
    list = list.filter(p => p.category === activeCategory.value)
  if (searchQuery.value.trim())
    list = list.filter(p => p.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
  return list
})

const subtotal  = computed(() => cart.value.reduce((s, i) => s + i.product.price * i.qty, 0))
const tax       = computed(() => Math.round(subtotal.value * 0.08))
const total     = computed(() => subtotal.value + tax.value)
const cartCount = computed(() => cart.value.reduce((s, i) => s + i.qty, 0))

// ── Cart actions ───────────────────────────────────────
function addToCart(product: Product) {
  if (!product.available) return
  const existing = cart.value.find(i => i.product.id === product.id)
  if (existing) {
    existing.qty++
  } else {
    cart.value.push({ product, qty: 1 })
  }
}

function changeQty(item: CartItem, delta: number) {
  item.qty += delta
  if (item.qty <= 0) cart.value = cart.value.filter(i => i !== item)
}

function clearCart() { cart.value = [] }

function processTransaction() {
  if (!cart.value.length) return
  alert(`Transaksi berhasil!\nTotal: ${fmt(total.value)}`)
  clearCart()
  customerName.value = ''
}

// ── Utils ──────────────────────────────────────────────
function fmt(n: number) {
  return 'Rp\u00A0' + n.toLocaleString('id-ID')
}

function initial(name: string) {
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() || 'U'
}
</script>

<template>
  <div class="pos">

    <!-- ══ Navbar ══ -->
    <header class="navbar">
      <div class="nav-brand">
        <div class="brand-mark">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
        </div>
        <span class="brand-label">DagangYuk</span>
      </div>

      <nav class="nav-menu">
        <button
          v-for="item in navItems" :key="item"
          class="nav-btn" :class="{ 'nav-btn--active': activeNav === item }"
          @click="activeNav = item"
        >{{ item }}</button>
      </nav>

      <div class="nav-actions">
        <ThemeSwitcher />
        <button class="icon-btn" aria-label="Notifikasi">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 01-3.46 0"/>
          </svg>
        </button>
        <button class="icon-btn" aria-label="Pengaturan">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
          </svg>
        </button>
        <button class="avatar-btn" @click="logout" :title="'Keluar — ' + auth.user?.name">
          {{ initial(auth.user?.name ?? 'User') }}
        </button>
      </div>
    </header>

    <!-- ══ Body ══ -->
    <div class="pos-body">

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
        <div class="grid" v-if="filteredProducts.length">
          <button
            v-for="p in filteredProducts" :key="p.id"
            class="product-card" :class="{ 'product-card--out': !p.available }"
            @click="addToCart(p)"
            :disabled="!p.available"
            :aria-label="'Tambah ' + p.name"
          >
            <!-- Gambar placeholder -->
            <div class="card-img">
              <div class="card-initial">{{ p.name.charAt(0) }}</div>
              <span class="card-badge" :class="p.available ? 'badge--on' : 'badge--off'">
                {{ p.available ? 'Tersedia' : 'Habis' }}
              </span>
            </div>
            <div class="card-body">
              <p class="card-name">{{ p.name }}</p>
              <div class="card-foot">
                <span class="card-price">{{ fmt(p.price) }}</span>
                <span class="card-add" v-if="p.available">+ Tambah</span>
              </div>
            </div>
          </button>
        </div>
        <div v-else class="empty">Produk tidak ditemukan.</div>

      </section>

      <!-- ── Panel kanan: Order ── -->
      <aside class="panel-order">

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

        <button class="btn-pay" :disabled="!cart.length" @click="processTransaction">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2"/>
            <line x1="1" y1="10" x2="23" y2="10"/>
          </svg>
          Proses Transaksi
        </button>

      </aside>
    </div>
  </div>
</template>

<style scoped>
/* ── Reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Root ── */
.pos {
  --radius: 10px;

  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  background: var(--surface);
  font-size: 14px;
  color: var(--ink);
}

/* ── Navbar ── */
.navbar {
  height: 58px;
  background: var(--white);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 0 20px;
  position: sticky;
  top: 0;
  z-index: 50;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 9px;
  flex-shrink: 0;
}

.brand-mark {
  width: 32px;
  height: 32px;
  background: var(--accent);
  color: #fff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}

.brand-label {
  font-size: 15px;
  font-weight: 700;
  letter-spacing: -0.3px;
  color: var(--ink);
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 2px;
  flex: 1;
}

.nav-btn {
  background: none;
  border: none;
  padding: 6px 14px;
  border-radius: 7px;
  font-size: 13.5px;
  font-weight: 500;
  color: var(--muted);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
}

.nav-btn:hover { background: var(--surface); color: var(--ink); }

.nav-btn--active {
  background: var(--accent);
  color: #fff !important;
  font-weight: 600;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-left: auto;
}

.icon-btn {
  width: 34px;
  height: 34px;
  background: none;
  border: 1px solid var(--border);
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--muted);
  transition: background 0.15s, color 0.15s;
}

.icon-btn:hover { background: var(--surface); color: var(--ink); }

.avatar-btn {
  width: 34px;
  height: 34px;
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: 50%;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.15s;
}

.avatar-btn:hover { opacity: 0.85; }

/* ── Body ── */
.pos-body {
  display: grid;
  grid-template-columns: 1fr 300px;
  flex: 1;
  min-height: 0;
  height: calc(100dvh - 58px);
}

/* ── Panel produk ── */
.panel-products {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px 16px 18px 20px;
  overflow-y: auto;
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
  text-align: center;
  padding: 48px 0;
  color: #d1d5db;
  font-size: 14px;
}

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

/* ── Cart TransitionGroup ── */
.cart-enter-active { transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1); }
.cart-leave-active  { transition: all 0.18s ease-in; }
.cart-enter-from    { opacity: 0; transform: translateX(-12px) scale(0.97); }
.cart-leave-to      { opacity: 0; transform: translateX(12px) scale(0.97); }
.cart-move          { transition: transform 0.22s ease; }

/* ── Responsive ── */
@media (max-width: 960px) {
  .pos-body { grid-template-columns: 1fr 280px; }
}

@media (max-width: 768px) {
  .pos-body {
    grid-template-columns: 1fr;
    height: auto;
    overflow: visible;
  }

  .panel-order {
    border-left: none;
    border-top: 1px solid var(--border);
    max-height: none;
  }

  .nav-menu .nav-btn:not(.nav-btn--active) { display: none; }
}

@media (max-width: 480px) {
  .navbar { padding: 0 12px; gap: 10px; }
  .panel-products { padding: 14px 12px; }
  .grid { grid-template-columns: repeat(2, 1fr); }
  .search-wrap { max-width: 160px; }
  .brand-label { display: none; }
}
</style>
