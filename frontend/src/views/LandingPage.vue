<script setup lang="ts">
import { RouterLink } from 'vue-router'

const features = [
  {
    icon: '🧾',
    title: 'Transaksi Cepat',
    desc: 'Proses penjualan lebih cepat dengan antarmuka kasir yang simpel dan responsif.',
  },
  {
    icon: '📦',
    title: 'Manajemen Stok',
    desc: 'Pantau stok barang secara real-time, dapatkan notifikasi saat stok menipis.',
  },
  {
    icon: '📊',
    title: 'Laporan Penjualan',
    desc: 'Laporan harian, mingguan, dan bulanan lengkap untuk analisis bisnis Anda.',
  },
  {
    icon: '🖨️',
    title: 'Cetak Struk',
    desc: 'Cetak struk langsung dari aplikasi dengan berbagai format printer yang didukung.',
  },
  {
    icon: '👥',
    title: 'Multi Kasir',
    desc: 'Dukung beberapa kasir sekaligus dengan manajemen akses yang mudah.',
  },
  {
    icon: '☁️',
    title: 'Cloud & Offline',
    desc: 'Tetap beroperasi meski tanpa koneksi internet, data tersinkron otomatis.',
  },
]
</script>

<template>
  <div class="landing">
    <!-- Navbar -->
    <nav class="navbar">
      <div class="container nav-inner">
        <div class="brand">
          <span class="brand-icon">�</span>
          <span class="brand-name">DagangYuk</span>
        </div>
        <div class="nav-links">
          <a href="#fitur">Fitur</a>
          <a href="#tentang">Tentang</a>
          <RouterLink to="/login" class="btn-login">Masuk</RouterLink>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
      <div class="container hero-inner">
        <div class="hero-text">
          <span class="badge">✨ POS Modern untuk UMKM</span>
          <h1>Kelola Kasir Bisnis Anda <span class="highlight">Lebih Mudah</span></h1>
          <p>
            DagangYuk adalah aplikasi point of sale yang dirancang khusus untuk membantu UMKM
            mengelola penjualan, stok, dan laporan keuangan dalam satu platform yang simpel.
          </p>
          <div class="hero-actions">
            <RouterLink to="/login" class="btn-primary">Mulai Sekarang →</RouterLink>
            <a href="#fitur" class="btn-outline">Lihat Fitur</a>
          </div>
          <div class="hero-stats">
            <div class="stat">
              <strong>500+</strong>
              <span>Toko Aktif</span>
            </div>
            <div class="divider"></div>
            <div class="stat">
              <strong>1 Juta+</strong>
              <span>Transaksi</span>
            </div>
            <div class="divider"></div>
            <div class="stat">
              <strong>99.9%</strong>
              <span>Uptime</span>
            </div>
          </div>
        </div>
        <div class="hero-visual">
          <div class="pos-mockup">
            <div class="mockup-header">
              <span class="dot red"></span>
              <span class="dot yellow"></span>
              <span class="dot green"></span>
              <span class="mockup-title">Kasir — DagangYuk</span>
            </div>
            <div class="mockup-body">
              <div class="mockup-cart">
                <div class="cart-item" v-for="i in 3" :key="i">
                  <div class="item-icon">📦</div>
                  <div class="item-info">
                    <div class="item-name skeleton"></div>
                    <div class="item-price skeleton short"></div>
                  </div>
                  <div class="item-qty">x{{ i }}</div>
                </div>
              </div>
              <div class="mockup-total">
                <span>Total</span>
                <strong>Rp 125.000</strong>
              </div>
              <div class="mockup-btn">💳 Bayar Sekarang</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="fitur">
      <div class="container">
        <div class="section-head">
          <h2>Semua yang Anda Butuhkan</h2>
          <p>Fitur lengkap untuk mendukung operasional toko Anda setiap hari.</p>
        </div>
        <div class="features-grid">
          <div class="feature-card" v-for="f in features" :key="f.title">
            <div class="feature-icon">{{ f.icon }}</div>
            <h3>{{ f.title }}</h3>
            <p>{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="tentang">
      <div class="container cta-inner">
        <h2>Siap Tingkatkan Penjualan Anda?</h2>
        <p>Bergabung dengan ratusan toko yang sudah menggunakan DagangYuk.</p>
        <RouterLink to="/login" class="btn-primary large">Coba Gratis Sekarang</RouterLink>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container footer-inner">
        <div class="brand">
          <span class="brand-icon">�</span>
          <span class="brand-name">DagangYuk</span>
        </div>
        <p class="footer-copy">© 2026 DagangYuk. Semua hak dilindungi.</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ---- Base ---- */
.landing {
  min-height: 100vh;
  background: #fff;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

/* ---- Navbar ---- */
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid #f0f0f0;
}

.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 64px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
}

.brand-icon {
  font-size: 24px;
}

.brand-name {
  font-size: 20px;
  font-weight: 700;
  color: #1a1a2e;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 32px;
}

.nav-links a {
  text-decoration: none;
  color: #555;
  font-size: 15px;
  transition: color 0.2s;
}

.nav-links a:hover {
  color: #f97316;
}

.btn-login {
  background: #f97316;
  color: #fff !important;
  padding: 8px 20px;
  border-radius: 8px;
  font-weight: 600;
  transition: background 0.2s !important;
}

.btn-login:hover {
  background: #ea6c0a !important;
}

/* ---- Hero ---- */
.hero {
  background: linear-gradient(135deg, #fff7ed 0%, #fff 60%);
  padding: 80px 0 100px;
}

.hero-inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

.badge {
  display: inline-block;
  background: #fff7ed;
  color: #f97316;
  border: 1px solid #fed7aa;
  padding: 4px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 20px;
}

.hero-text h1 {
  font-size: 48px;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 20px;
  color: #1a1a2e;
}

.highlight {
  color: #f97316;
}

.hero-text p {
  font-size: 17px;
  color: #555;
  line-height: 1.7;
  margin-bottom: 36px;
  max-width: 480px;
}

.hero-actions {
  display: flex;
  gap: 16px;
  margin-bottom: 48px;
}

.btn-primary {
  background: #f97316;
  color: #fff;
  padding: 13px 28px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
  font-size: 16px;
  transition: background 0.2s, transform 0.2s;
  display: inline-block;
}

.btn-primary:hover {
  background: #ea6c0a;
  transform: translateY(-1px);
}

.btn-primary.large {
  padding: 16px 36px;
  font-size: 17px;
}

.btn-outline {
  border: 2px solid #f97316;
  color: #f97316;
  padding: 13px 28px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
  font-size: 16px;
  transition: background 0.2s;
}

.btn-outline:hover {
  background: #fff7ed;
}

.hero-stats {
  display: flex;
  gap: 24px;
  align-items: center;
}

.stat {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.stat strong {
  font-size: 22px;
  font-weight: 800;
  color: #1a1a2e;
}

.stat span {
  font-size: 13px;
  color: #888;
}

.divider {
  width: 1px;
  height: 40px;
  background: #e5e5e5;
}

/* ---- POS Mockup ---- */
.hero-visual {
  display: flex;
  justify-content: center;
}

.pos-mockup {
  width: 340px;
  background: #1a1a2e;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(249, 115, 22, 0.15), 0 8px 24px rgba(0, 0, 0, 0.2);
}

.mockup-header {
  background: #252540;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.dot.red { background: #ff5f57; }
.dot.yellow { background: #febc2e; }
.dot.green { background: #28c840; }

.mockup-title {
  color: #888;
  font-size: 12px;
  margin-left: 8px;
}

.mockup-body {
  padding: 20px;
}

.mockup-cart {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #252540;
  padding: 10px 12px;
  border-radius: 8px;
}

.item-icon {
  font-size: 20px;
}

.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.skeleton {
  height: 10px;
  background: #3a3a5c;
  border-radius: 4px;
}

.skeleton.short {
  width: 60%;
}

.item-qty {
  color: #f97316;
  font-size: 13px;
  font-weight: 600;
}

.mockup-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #ccc;
  font-size: 15px;
  border-top: 1px solid #3a3a5c;
  padding-top: 16px;
  margin-bottom: 16px;
}

.mockup-total strong {
  color: #fff;
  font-size: 18px;
}

.mockup-btn {
  background: #f97316;
  color: #fff;
  text-align: center;
  padding: 12px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
}

/* ---- Features ---- */
.features {
  padding: 100px 0;
  background: #fafafa;
}

.section-head {
  text-align: center;
  margin-bottom: 60px;
}

.section-head h2 {
  font-size: 36px;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 12px;
}

.section-head p {
  font-size: 17px;
  color: #666;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
}

.feature-card {
  background: #fff;
  border: 1px solid #f0f0f0;
  border-radius: 16px;
  padding: 32px 28px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.feature-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
}

.feature-icon {
  font-size: 36px;
  margin-bottom: 16px;
}

.feature-card h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 10px;
}

.feature-card p {
  font-size: 14px;
  color: #666;
  line-height: 1.6;
}

/* ---- CTA ---- */
.cta {
  padding: 100px 0;
  background: linear-gradient(135deg, #1a1a2e, #2d2d5e);
}

.cta-inner {
  text-align: center;
}

.cta h2 {
  font-size: 40px;
  font-weight: 800;
  color: #fff;
  margin-bottom: 16px;
}

.cta p {
  font-size: 18px;
  color: #aab;
  margin-bottom: 40px;
}

/* ---- Footer ---- */
.footer {
  background: #1a1a2e;
  border-top: 1px solid #2d2d5e;
  padding: 24px 0;
}

.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.footer .brand-name {
  color: #fff;
}

.footer-copy {
  font-size: 13px;
  color: #666;
}

/* ---- Responsive ---- */
@media (max-width: 900px) {
  .hero-inner {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .hero-text p {
    max-width: 100%;
  }

  .hero-actions {
    justify-content: center;
  }

  .hero-stats {
    justify-content: center;
  }

  .hero-visual {
    order: -1;
  }

  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .hero-text h1 {
    font-size: 32px;
  }

  .features-grid {
    grid-template-columns: 1fr;
  }

  .footer-inner {
    flex-direction: column;
    gap: 8px;
    text-align: center;
  }

  .nav-links a:not(.btn-login) {
    display: none;
  }
}
</style>
