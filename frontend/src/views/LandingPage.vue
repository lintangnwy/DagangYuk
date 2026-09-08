<script setup lang="ts">
import { RouterLink } from 'vue-router'

const features = [
  {
    title: 'Transaksi lebih cepat',
    desc: 'Cukup cari produk, masukkan jumlah, dan selesai. Tidak perlu kalkulator terpisah.',
  },
  {
    title: 'Stok otomatis tercatat',
    desc: 'Setiap penjualan langsung memperbarui stok. Tidak ada lagi selisih antara catatan dan rak.',
  },
  {
    title: 'Laporan setiap saat',
    desc: 'Buka aplikasi, lihat berapa pemasukan hari ini. Sesederhana itu.',
  },
  {
    title: 'Jalan tanpa internet',
    desc: 'Listrik mati, wifi down — kasir tetap beroperasi. Data menyinkron otomatis saat koneksi kembali.',
  },
]
</script>

<template>
  <div class="page">

    <!-- Navbar -->
    <header class="header">
      <div class="container header-inner">
        <RouterLink to="/" class="wordmark">DagangYuk</RouterLink>
        <nav class="header-nav" aria-label="Navigasi utama">
          <a href="#fitur">Fitur</a>
          <RouterLink to="/login" class="btn-nav">Masuk</RouterLink>
        </nav>
      </div>
    </header>

    <!-- Hero -->
    <section class="hero">
      <div class="container hero-inner">
        <div class="hero-content">
          <p class="eyebrow">Aplikasi kasir untuk UMKM</p>
          <h1>
            Jualan lebih lancar,<br class="break-md" />
            catatan lebih rapi
          </h1>
          <p class="lead">
            DagangYuk adalah kasir digital yang dirancang untuk pemilik
            toko — bukan untuk akuntan. Tidak perlu training, tidak perlu
            setup berhari-hari.
          </p>
          <div class="hero-cta">
            <RouterLink to="/login" class="btn-primary">Coba sekarang</RouterLink>
            <a href="#fitur" class="btn-text">Pelajari fitur →</a>
          </div>
        </div>

        <!-- Mockup sederhana -->
        <div class="hero-visual" aria-hidden="true">
          <div class="receipt">
            <div class="receipt-header">
              <span class="r-store">Toko Makmur</span>
              <span class="r-date">08 Sep 2026</span>
            </div>
            <div class="receipt-divider"></div>
            <div class="receipt-items">
              <div class="r-item">
                <span>Indomie Goreng</span>
                <span>Rp 3.500</span>
              </div>
              <div class="r-item">
                <span>Teh Botol 500ml</span>
                <span>Rp 5.000</span>
              </div>
              <div class="r-item">
                <span>Roti Tawar</span>
                <span>Rp 12.000</span>
              </div>
            </div>
            <div class="receipt-divider"></div>
            <div class="r-item r-total">
              <span>Total</span>
              <strong>Rp 20.500</strong>
            </div>
            <div class="receipt-pay">
              <div class="pay-row">
                <span>Bayar</span>
                <span>Rp 25.000</span>
              </div>
              <div class="pay-row pay-change">
                <span>Kembali</span>
                <strong>Rp 4.500</strong>
              </div>
            </div>
            <div class="receipt-footer">Terima kasih sudah berbelanja</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <div class="stats-bar">
      <div class="container stats-inner">
        <div class="stat-item">
          <span class="stat-num">500+</span>
          <span class="stat-label">toko aktif</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
          <span class="stat-num">1 juta+</span>
          <span class="stat-label">transaksi diproses</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat-item">
          <span class="stat-num">4.8/5</span>
          <span class="stat-label">kepuasan pengguna</span>
        </div>
      </div>
    </div>

    <!-- Features -->
    <section class="features" id="fitur">
      <div class="container">
        <div class="section-intro">
          <h2>Yang Anda butuhkan, sudah ada</h2>
          <p>Tidak lebih, tidak kurang — fitur yang benar-benar dipakai setiap hari.</p>
        </div>
        <div class="features-grid">
          <article class="f-card" v-for="(f, i) in features" :key="i">
            <span class="f-index">{{ String(i + 1).padStart(2, '0') }}</span>
            <h3>{{ f.title }}</h3>
            <p>{{ f.desc }}</p>
          </article>
        </div>
      </div>
    </section>

    <!-- Testimonial -->
    <section class="testi">
      <div class="container testi-inner">
        <blockquote>
          <p>
            "Dulu saya tulis stok di buku, sering salah hitung. Sekarang pakai
            DagangYuk sudah tiga bulan, tidak pernah ada selisih lagi."
          </p>
          <footer>
            <div class="testi-avatar">S</div>
            <div class="testi-who">
              <span>Sari Wulandari</span>
              <small>Warung Kelontong Bu Sari, Semarang</small>
            </div>
          </footer>
        </blockquote>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta">
      <div class="container cta-inner">
        <h2>Mulai hari ini, gratis</h2>
        <p>Tidak perlu kartu kredit. Tidak perlu instalasi. Langsung pakai.</p>
        <RouterLink to="/login" class="btn-primary">Buat akun</RouterLink>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container footer-inner">
        <span class="wordmark">DagangYuk</span>
        <span class="footer-note">© 2026 DagangYuk. All rights reserved.</span>
      </div>
    </footer>

  </div>
</template>

<style scoped>
/* ─── Tokens ─── */
:root {
  --c-ink: #0f0f0f;
  --c-muted: #6b6b6b;
  --c-subtle: #999;
  --c-line: #e8e8e8;
  --c-surface: #f7f7f7;
  --c-white: #fff;
  --r: 8px;
}

/* ─── Base ─── */
.page {
  min-height: 100vh;
  background: var(--c-white);
  color: var(--c-ink);
  font-size: 16px;
  line-height: 1.6;
}

.container {
  width: 100%;
  max-width: 1100px;
  margin-inline: auto;
  padding-inline: clamp(20px, 5vw, 48px);
}

/* ─── Header ─── */
.header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--c-line);
}

.header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 58px;
}

.wordmark {
  font-size: 17px;
  font-weight: 700;
  color: var(--c-ink);
  text-decoration: none;
  letter-spacing: -0.3px;
}

.header-nav {
  display: flex;
  align-items: center;
  gap: 24px;
}

.header-nav a {
  font-size: 14px;
  color: var(--c-muted);
  text-decoration: none;
  transition: color 0.15s;
}

.header-nav a:hover {
  color: var(--c-ink);
}

.btn-nav {
  font-size: 14px !important;
  font-weight: 600 !important;
  color: var(--c-ink) !important;
  border: 1.5px solid var(--c-ink) !important;
  padding: 6px 16px;
  border-radius: 6px;
  transition: background 0.15s, color 0.15s !important;
}

.btn-nav:hover {
  background: var(--c-ink) !important;
  color: var(--c-white) !important;
}

/* ─── Buttons ─── */
.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: var(--c-ink);
  color: var(--c-white);
  font-size: 15px;
  font-weight: 600;
  padding: 11px 24px;
  border-radius: var(--r);
  text-decoration: none;
  transition: opacity 0.15s, transform 0.15s;
  white-space: nowrap;
}

.btn-primary:hover {
  opacity: 0.82;
  transform: translateY(-1px);
}

.btn-text {
  display: inline-flex;
  align-items: center;
  font-size: 15px;
  color: var(--c-muted);
  text-decoration: none;
  transition: color 0.15s;
}

.btn-text:hover {
  color: var(--c-ink);
}

/* ─── Hero ─── */
.hero {
  padding-block: clamp(64px, 10vw, 112px);
  border-bottom: 1px solid var(--c-line);
}

.hero-inner {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: clamp(40px, 6vw, 80px);
  align-items: center;
}

.eyebrow {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--c-subtle);
  margin-bottom: 16px;
}

.hero-content h1 {
  font-size: clamp(32px, 4.5vw, 52px);
  font-weight: 800;
  line-height: 1.12;
  letter-spacing: -1.5px;
  color: var(--c-ink);
  margin-bottom: 20px;
}

.lead {
  font-size: clamp(15px, 2vw, 17px);
  color: var(--c-muted);
  line-height: 1.75;
  max-width: 460px;
  margin-bottom: 36px;
}

.hero-cta {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

/* ─── Receipt mockup ─── */
.hero-visual {
  flex-shrink: 0;
}

.receipt {
  width: 240px;
  background: #fff;
  border: 1px solid var(--c-line);
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  font-size: 13px;
  font-family: 'Courier New', monospace;
}

.receipt-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.r-store {
  font-weight: 700;
  font-size: 14px;
  color: var(--c-ink);
  font-family: inherit;
}

.r-date {
  color: var(--c-subtle);
  font-family: inherit;
}

.receipt-divider {
  border: none;
  border-top: 1px dashed var(--c-line);
  margin-block: 10px;
}

.receipt-items {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.r-item {
  display: flex;
  justify-content: space-between;
  color: var(--c-ink);
}

.r-item span:last-child {
  color: var(--c-muted);
}

.r-total {
  font-weight: 600;
  margin-top: 4px;
}

.r-total strong {
  color: var(--c-ink);
}

.receipt-pay {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.pay-row {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: var(--c-subtle);
}

.pay-change {
  color: var(--c-ink);
  font-weight: 600;
}

.pay-change strong {
  color: #1a7a4a;
}

.receipt-footer {
  margin-top: 14px;
  text-align: center;
  font-size: 11px;
  color: var(--c-subtle);
  border-top: 1px dashed var(--c-line);
  padding-top: 10px;
}

/* ─── Stats ─── */
.stats-bar {
  border-bottom: 1px solid var(--c-line);
  padding-block: 20px;
}

.stats-inner {
  display: flex;
  align-items: center;
  gap: clamp(20px, 4vw, 48px);
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.stat-num {
  font-size: 18px;
  font-weight: 800;
  letter-spacing: -0.5px;
  color: var(--c-ink);
}

.stat-label {
  font-size: 12px;
  color: var(--c-subtle);
}

.stat-sep {
  width: 1px;
  height: 28px;
  background: var(--c-line);
  flex-shrink: 0;
}

/* ─── Features ─── */
.features {
  padding-block: clamp(64px, 8vw, 96px);
  border-bottom: 1px solid var(--c-line);
}

.section-intro {
  max-width: 520px;
  margin-bottom: clamp(36px, 5vw, 56px);
}

.section-intro h2 {
  font-size: clamp(22px, 3vw, 30px);
  font-weight: 800;
  letter-spacing: -0.5px;
  color: var(--c-ink);
  margin-bottom: 8px;
}

.section-intro p {
  font-size: 15px;
  color: var(--c-muted);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  border-top: 1px solid var(--c-line);
  border-left: 1px solid var(--c-line);
}

.f-card {
  padding: clamp(24px, 3vw, 40px);
  border-right: 1px solid var(--c-line);
  border-bottom: 1px solid var(--c-line);
  transition: background 0.15s;
}

.f-card:hover {
  background: var(--c-surface);
}

.f-index {
  display: block;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: var(--c-line);
  margin-bottom: 14px;
}

.f-card h3 {
  font-size: 16px;
  font-weight: 700;
  color: var(--c-ink);
  margin-bottom: 8px;
  letter-spacing: -0.2px;
}

.f-card p {
  font-size: 14px;
  color: var(--c-muted);
  line-height: 1.65;
}

/* ─── Testimonial ─── */
.testi {
  padding-block: clamp(64px, 8vw, 96px);
  border-bottom: 1px solid var(--c-line);
  background: var(--c-surface);
}

.testi-inner {
  max-width: 680px;
}

blockquote {
  margin: 0;
}

blockquote p {
  font-size: clamp(17px, 2.5vw, 22px);
  font-weight: 500;
  line-height: 1.6;
  color: var(--c-ink);
  letter-spacing: -0.2px;
  margin-bottom: 28px;
}

blockquote footer {
  display: flex;
  align-items: center;
  gap: 14px;
}

.testi-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #d0d0d0;
  color: var(--c-ink);
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.testi-who {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.testi-who span {
  font-size: 14px;
  font-weight: 600;
  color: var(--c-ink);
}

.testi-who small {
  font-size: 12px;
  color: var(--c-subtle);
}

/* ─── CTA ─── */
.cta {
  padding-block: clamp(64px, 8vw, 96px);
  border-bottom: 1px solid var(--c-line);
}

.cta-inner {
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-width: 480px;
}

.cta h2 {
  font-size: clamp(22px, 3vw, 30px);
  font-weight: 800;
  letter-spacing: -0.5px;
  color: var(--c-ink);
}

.cta p {
  font-size: 15px;
  color: var(--c-muted);
  margin-bottom: 4px;
}

/* ─── Footer ─── */
.footer {
  padding-block: 24px;
}

.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
}

.footer-note {
  font-size: 13px;
  color: var(--c-subtle);
}

/* ─── Responsive ─── */
@media (max-width: 800px) {
  .hero-inner {
    grid-template-columns: 1fr;
  }

  .hero-visual {
    display: none;
  }

  .break-md {
    display: none;
  }
}

@media (max-width: 560px) {
  .features-grid {
    grid-template-columns: 1fr;
  }

  .stat-sep {
    display: none;
  }

  .stats-inner {
    gap: 16px;
  }

  .footer-inner {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
