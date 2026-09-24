<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import ThemeSwitcher from '@/components/ThemeSwitcher.vue'
import RotatingText from '@/components/RotatingText.vue'
import { useLenis } from '@/composables/useLenis'

gsap.registerPlugin(ScrollTrigger)

const { lenis } = useLenis()

const activeTab = ref('Semua')
const tabs = ['Semua', 'Minuman', 'Makanan', 'Pastry']
const rotatingWords = ['Cafe Modern', 'Warung Makan', 'Toko Retail', 'Kedai Kopi', 'Butik & Fashion']

function smoothTo(selector: string) {
  const target = document.querySelector(selector)
  if (target && lenis.value) lenis.value.scrollTo(target as HTMLElement)
}

onMounted(() => {
  const intro = gsap.timeline({ defaults: { ease: 'power3.out' } })
    .from('.hero-badge', { opacity: 0, scale: 0.9, duration: 0.4 }, '-=0.2')
    .from('.hero-title', { opacity: 0, y: 25, duration: 0.6 }, '-=0.2')
    .from('.hero-copy, .hero-actions', { opacity: 0, y: 15, duration: 0.5, stagger: 0.1 }, '-=0.3')
    .from('.hero-stage', { opacity: 0, y: 40, duration: 0.7 }, '-=0.4')

  gsap.utils.toArray<HTMLElement>('.reveal').forEach((element) => {
    gsap.from(element, { opacity: 0, y: 30, duration: 0.6, scrollTrigger: { trigger: element, start: 'top 85%' } })
  })
})
</script>

<template>
  <div class="landing-page">
    <header class="site-nav">
      <div class="shell nav-inner">
        <RouterLink to="/" class="brand" aria-label="DagangYuk beranda">
          <span class="brand-mark">D</span><span>DagangYuk</span>
        </RouterLink>
        <nav class="nav-links" aria-label="Navigasi utama">
          <a href="javascript:void(0)" @click.prevent="smoothTo('#fitur')">Fitur</a>
          <a href="javascript:void(0)" @click.prevent="smoothTo('#harga')">Harga</a>
          <a href="javascript:void(0)" @click.prevent="smoothTo('#testimoni')">Testimoni</a>
          <ThemeSwitcher />
          <RouterLink to="/login" class="nav-login">Masuk</RouterLink>
          <RouterLink to="/login" class="nav-cta">Coba Gratis</RouterLink>
        </nav>
      </div>
    </header>

    <main>
      <section class="hero shell">
        <div class="hero-copy-wrap">
          <div class="hero-badge">POS SaaS Paling Seru untuk UMKM</div>
          <h1 class="hero-title">
            Solusi Kasir Pintar untuk<br />
            <RotatingText :texts="rotatingWords" :rotation-interval="2200" class="rotating-accent" />
          </h1>
          <p class="hero-copy">Kelola transaksi kilat, pantau stok otomatis, dan lihat omzet toko Anda secara real-time langsung dari satu layar yang super intuitif.</p>
          <div class="hero-actions">
            <RouterLink to="/login" class="button button-primary">Mulai Gratis Sekarang</RouterLink>
            <a href="#fitur" class="button button-secondary">Pelajari Fitur</a>
          </div>
        </div>

        <div class="hero-stage" aria-label="Pratinjau kasir DagangYuk">
          <div class="pos-preview">
            <div class="preview-top">
              <span class="preview-dots"><i></i><i></i><i></i></span>
              <span class="branch-badge">Cabang Utama - Denpasar</span>
              <span class="preview-date">OMZET HARI INI: <b>Rp 8.350.000</b></span>
              <span class="live-pill">• LIVE</span>
            </div>
            <div class="preview-body">
              <div class="preview-products">
                <div class="preview-tabs">
                  <button 
                    v-for="t in tabs" 
                    :key="t" 
                    class="tab" 
                    :class="{ active: activeTab === t }"
                    @click="activeTab = t"
                  >
                    {{ t }}
                  </button>
                </div>
                <div class="preview-grid">
                  <div class="p-card">
                    <div class="p-cat">Minuman</div>
                    <strong>Kopi Susu Senja</strong>
                    <small>Stok: 45</small>
                    <div class="p-price">Rp 25.000</div>
                  </div>
                  <div class="p-card">
                    <div class="p-cat">Minuman</div>
                    <strong>Matcha Latte Colt</strong>
                    <small>Stok: 22</small>
                    <div class="p-price">Rp 28.000</div>
                  </div>
                  <div class="p-card">
                    <div class="p-cat">Pastry</div>
                    <strong>Almond Croissant</strong>
                    <small>Stok: 12</small>
                    <div class="p-price">Rp 32.000</div>
                  </div>
                  <div class="p-card">
                    <div class="p-cat">Makanan</div>
                    <strong>Nasi Goreng Senopati</strong>
                    <small>Stok: 18</small>
                    <div class="p-price">Rp 35.000</div>
                  </div>
                  <div class="p-card">
                    <div class="p-cat">Minuman</div>
                    <strong>Cold Brew Citrus</strong>
                    <small>Stok: 14</small>
                    <div class="p-price">Rp 26.000</div>
                  </div>
                  <div class="p-card">
                    <div class="p-cat">Pastry</div>
                    <strong>Pain au Chocolat</strong>
                    <small>Stok: 9</small>
                    <div class="p-price">Rp 30.000</div>
                  </div>
                </div>
              </div>

              <div class="preview-cart">
                <div class="cart-title">Keranjang Kasir <span class="badge-tag">Walk-in</span></div>
                <div class="cart-items">
                  <div class="ci-row">
                    <div><b>Kopi Susu Senja</b><small>Rp 25.000 × 2</small></div>
                    <span>Rp 50.000</span>
                  </div>
                  <div class="ci-row">
                    <div><b>Almond Croissant</b><small>Rp 32.000 × 2</small></div>
                    <span>Rp 64.000</span>
                  </div>
                  <div class="ci-row">
                    <div><b>Nasi Goreng</b><small>Rp 35.000 × 1</small></div>
                    <span>Rp 35.000</span>
                  </div>
                </div>
                <div class="cart-summary">
                  <div><span>Subtotal</span><span>Rp 149.000</span></div>
                  <div><span>PPN (11%)</span><span>Rp 16.390</span></div>
                  <div class="total-row"><span>Total Tagihan</span><strong>Rp 165.390</strong></div>
                </div>
                <button class="pay-btn">Bayar Sekarang</button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="proof-strip shell" aria-label="Brand partners">
        <p>TELAH DIAJAK BEKERJASAMA OLEH 12.000+ UMKM & CAFE DI INDONESIA</p>
        <div class="brands-grid">
          <span>Kopi Kenari</span>
          <span>Roti Nusantara</span>
          <span>Toko Sumber Makmur</span>
          <span>Teha Artisanal</span>
        </div>
      </section>

      <section id="fitur" class="feature-section shell reveal">
        <div class="section-badge">FITUR UNGGULAN</div>
        <h2 class="section-title">Semua yang Anda Butuhkan untuk Mengelola Toko</h2>
        <p class="section-subtitle">Dirancang agar kasir dan pemilik toko bisa bekerja lebih cepat tanpa drama.</p>

        <div class="feature-cards-grid">
          <div class="feature-card">
            <div class="card-icon">01</div>
            <h3>Kasir Super Cepat</h3>
            <p>Pencarian produk instan via barcode atau klik kategori. Transaksi selesai dalam hitungan detik.</p>
          </div>
          <div class="feature-card">
            <div class="card-icon">02</div>
            <h3>Stok Otomatis</h3>
            <p>Setiap penjualan langsung memotong stok di sistem. Tidak ada lagi selisih barang di gudang.</p>
          </div>
          <div class="feature-card">
            <div class="card-icon">03</div>
            <h3>Laporan Real-Time</h3>
            <p>Pantau omzet, laba kotor, dan produk terlaris langsung dari handphone atau laptop Anda.</p>
          </div>
        </div>
      </section>

      <section id="harga" class="pricing-section shell reveal">
        <div class="section-badge">PILIHAN HARGA</div>
        <h2 class="section-title">Investasi Terjangkau untuk Bisnis Anda</h2>
        <p class="section-subtitle">Mulai gratis dan upgrade kapan pun bisnis Anda berkembang.</p>

        <div class="pricing-grid">
          <div class="pricing-card">
            <div class="plan-header">
              <h3>Starter</h3>
              <span class="badge-sub">Untuk usaha rintisan</span>
            </div>
            <div class="price-val">Rp 0<span> /bulan</span></div>
            <ul class="plan-features">
              <li>1 Outlet / Cabang Toko</li>
              <li>3 Staf Kasir</li>
              <li>Laporan Penjualan Harian</li>
              <li>Pembayaran Tunai & QRIS</li>
            </ul>
            <RouterLink to="/login" class="button button-secondary btn-full">Mulai Gratis</RouterLink>
          </div>

          <div class="pricing-card featured">
            <div class="popular-badge">Paling Populer</div>
            <div class="plan-header">
              <h3>Pro</h3>
              <span class="badge-sub">Untuk bisnis berkembang pesat</span>
            </div>
            <div class="price-val">Rp 99.000<span> /bulan</span></div>
            <ul class="plan-features">
              <li>Multi-Cabang Tanpa Batas</li>
              <li>Manajemen Staf & Absensi</li>
              <li>Laporan Keuangan & Laba Rugi</li>
              <li>Manajemen Stok & Bahan Baku</li>
              <li>Prioritas Support 24/7</li>
            </ul>
            <RouterLink to="/login" class="button button-primary btn-full">Coba Pro Gratis</RouterLink>
          </div>
        </div>
      </section>

      <section id="testimoni" class="testimonial-section shell reveal">
        <div class="testimonial-label">Cerita pelanggan</div>
        <blockquote>"DagangYuk benar-benar mengubah cara warung kami beroperasi. Laporan harian akurat dan kasir tidak pernah salah hitung kembalian lagi!"</blockquote>
        <div class="testimonial-author">
          <div class="author-avatar">BP</div>
          <div>
            <strong>Bagus Pratama</strong>
            <small>Owner Kopi Kenari & Ritel Lokal</small>
          </div>
        </div>
      </section>

      <section class="final-cta shell reveal">
        <div class="cta-card-box">
          <div class="section-badge">SIAP BERTRANSAKSI?</div>
          <h2>Mulai Kelola Toko Lebih Pintar Hari Ini</h2>
          <p>Tanpa kartu kredit. Setup toko Anda dalam waktu kurang dari 2 menit.</p>
          <RouterLink to="/login" class="button button-primary">Daftar Sekarang Juga</RouterLink>
        </div>
      </section>
    </main>

    <footer class="site-footer shell">
      <RouterLink to="/" class="brand"><span class="brand-mark">D</span><span>DagangYuk</span></RouterLink>
      <div class="footer-links">
        <a href="#">Privasi</a>
        <a href="#">Syarat</a>
        <a href="#">Bantuan</a>
      </div>
      <span>© 2026 DagangYuk. All rights reserved.</span>
    </footer>
  </div>
</template>

<style>
.landing-page { --ink: #0f172a; --muted: #64748b; --line: #e2e8f0; --paper: #f8fafc; background: var(--paper); color: var(--ink); font-family: Inter, ui-sans-serif, system-ui, sans-serif; overflow-x: hidden; }
.shell { width: min(1200px, calc(100% - 48px)); margin: 0 auto; }
.site-nav { height: 76px; border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, 0.85); position: sticky; top: 0; z-index: 50; backdrop-filter: blur(12px); }
.nav-inner, .nav-links, .brand { display: flex; align-items: center; }
.nav-inner { height: 100%; justify-content: space-between; }
.brand { gap: 10px; color: var(--ink); font-weight: 800; font-size: 19px; letter-spacing: -.03em; text-decoration: none; }
.brand-mark { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px; background: var(--accent); color: #fff; font-size: 16px; font-weight: 900; box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.3); }
.nav-links { gap: 28px; font-size: 14px; font-weight: 500; color: var(--muted); }
.nav-links a { color: inherit; text-decoration: none; transition: color .2s; }
.nav-links a:hover { color: var(--accent); }
.nav-login { font-weight: 600; color: var(--ink) !important; }
.nav-cta { background: var(--accent); color: #fff !important; padding: 10px 20px; border-radius: 10px; font-weight: 650; box-shadow: 0 4px 14px rgba(var(--accent-rgb), 0.35); transition: all .2s ease; }
.nav-cta:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(var(--accent-rgb), 0.45); }
.hero { padding-block: 70px 90px; text-align: center; display: flex; flex-direction: column; align-items: center; }
.hero-copy-wrap { max-width: 740px; margin-bottom: 50px; }
.hero-badge { display: inline-block; background: var(--accent-bg); color: var(--accent); font-size: 13px; font-weight: 700; padding: 6px 14px; border-radius: 20px; margin-bottom: 20px; border: 1px solid var(--accent-ring); }
.hero-title { font-size: clamp(40px, 6vw, 68px); line-height: 1.1; letter-spacing: -.035em; font-weight: 850; margin: 0 0 20px; color: var(--ink); }
.hero-title span, .rotating-accent { color: var(--accent); }
.hero-copy { font-size: 18px; line-height: 1.6; color: var(--muted); max-width: 620px; margin: 0 auto 32px; }
.hero-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.button { display: inline-flex; align-items: center; gap: 10px; padding: 14px 26px; border-radius: 12px; font-size: 15px; font-weight: 700; text-decoration: none; cursor: pointer; transition: all .2s cubic-bezier(0.16, 1, 0.3, 1); }
.button-primary { background: var(--accent); color: #fff; box-shadow: 0 8px 24px rgba(var(--accent-rgb), 0.35); }
.button-primary:hover { background: var(--accent-dark); transform: translateY(-2px); box-shadow: 0 12px 30px rgba(var(--accent-rgb), 0.45); }
.button-secondary { background: #fff; border: 1px solid var(--line); color: var(--ink); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
.button-secondary:hover { background: #f8fafc; border-color: var(--accent-ring); transform: translateY(-2px); }
.hero-stage { width: 100%; max-width: 1100px; background: #fff; border: 1px solid var(--line); border-radius: 20px; box-shadow: 0 30px 90px rgba(15, 23, 42, 0.08); overflow: hidden; text-align: left; }
.preview-top { height: 56px; background: #f8fafc; border-bottom: 1px solid var(--line); display: flex; align-items: center; padding: 0 24px; gap: 16px; font-size: 12px; }
.preview-dots { display: flex; gap: 6px; }
.preview-dots i { width: 10px; height: 10px; border-radius: 50%; background: #cbd5e1; display: inline-block; }
.branch-badge { font-weight: 700; color: var(--ink); background: #fff; padding: 5px 12px; border-radius: 8px; border: 1px solid var(--line); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.preview-date { margin-left: auto; color: var(--muted); font-size: 12px; }
.preview-date b { color: var(--accent); }
.live-pill { color: #10b981; font-weight: 750; font-size: 11px; letter-spacing: .05em; background: #dcfce7; padding: 3px 8px; border-radius: 6px; }
.preview-body { display: grid; grid-template-columns: 1.35fr 1fr; min-height: 440px; }
.preview-products { padding: 24px; border-right: 1px solid var(--line); background: #fafafa; }
.preview-tabs { display: flex; gap: 8px; margin-bottom: 20px; }
.tab { padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 650; color: var(--muted); cursor: pointer; background: #f1f5f9; border: none; transition: all .2s; }
.tab.active { background: var(--accent); color: #fff; box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.25); }
.preview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.p-card { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 6px; cursor: pointer; transition: all .2s; box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
.p-card:hover { border-color: var(--accent); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(var(--accent-rgb), 0.08); }
.p-cat { font-size: 9px; font-weight: 800; text-transform: uppercase; color: var(--accent); letter-spacing: .05em; }
.p-card strong { font-size: 13px; line-height: 1.3; color: var(--ink); }
.p-card small { font-size: 11px; color: var(--muted); }
.p-price { margin-top: auto; font-size: 13px; font-weight: 800; color: var(--ink); }
.preview-cart { padding: 24px; background: #fff; display: flex; flex-direction: column; }
.cart-title { font-size: 14px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; }
.badge-tag { background: var(--accent-bg); color: var(--accent); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
.cart-items { display: flex; flex-direction: column; gap: 12px; max-height: 200px; overflow-y: auto; margin-bottom: 20px; padding-right: 4px; }
.ci-row { display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding-bottom: 10px; border-bottom: 1px dashed var(--line); }
.ci-row b { display: block; font-size: 12px; color: var(--ink); }
.ci-row small { color: var(--muted); font-size: 11px; }
.ci-row span { font-weight: 750; color: var(--ink); }
.cart-summary { border-top: 1px solid var(--line); padding-top: 14px; display: flex; flex-direction: column; gap: 8px; font-size: 12px; color: var(--muted); }
.cart-summary div { display: flex; justify-content: space-between; }
.total-row { font-size: 15px; font-weight: 850; color: var(--ink); margin-top: 6px; padding-top: 8px; border-top: 1px solid var(--line); }
.pay-btn { width: 100%; background: var(--accent); color: #fff; border: none; border-radius: 10px; padding: 14px; font-weight: 800; font-size: 13px; cursor: pointer; margin-top: 18px; box-shadow: 0 4px 16px rgba(var(--accent-rgb), 0.3); transition: all .2s; }
.pay-btn:hover { background: var(--accent-dark); }
.proof-strip { padding-block: 60px; text-align: center; border-bottom: 1px solid var(--line); }
.proof-strip p { font-size: 12px; font-weight: 800; letter-spacing: .12em; color: var(--muted); margin-bottom: 24px; }
.brands-grid { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; font-size: 16px; font-weight: 750; color: #334155; opacity: .9; }
.feature-section { padding-block: 110px; text-align: center; }
.section-badge { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .15em; color: var(--accent); text-transform: uppercase; margin-bottom: 14px; background: var(--accent-bg); padding: 6px 14px; border-radius: 8px; border: 1px solid var(--accent-ring); }
.section-title { font-size: clamp(34px, 4.5vw, 50px); font-weight: 850; letter-spacing: -.035em; margin: 0 0 16px; color: var(--ink); }
.section-subtitle { font-size: 17px; color: var(--muted); max-width: 600px; margin: 0 auto 60px; line-height: 1.6; }
.feature-cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; text-align: left; }
.feature-card { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 36px; display: flex; flex-direction: column; transition: all .3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
.feature-card:hover { transform: translateY(-6px); border-color: var(--accent-ring); box-shadow: 0 20px 40px rgba(var(--accent-rgb), 0.08); }
.card-icon { width: 52px; height: 52px; border-radius: 14px; display: grid; place-items: center; font-size: 18px; font-weight: 800; margin-bottom: 24px; background: var(--accent-bg); color: var(--accent); }
.feature-card h3 { font-size: 19px; font-weight: 800; letter-spacing: -.02em; margin: 0 0 12px; color: var(--ink); }
.feature-card p { font-size: 15px; color: var(--muted); line-height: 1.65; margin: 0; }
.pricing-section { padding-bottom: 120px; text-align: center; }
.pricing-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; max-width: 860px; margin: 0 auto; text-align: left; }
.pricing-card { background: #fff; border: 1px solid var(--line); border-radius: 24px; padding: 44px; display: flex; flex-direction: column; position: relative; box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
.pricing-card.featured { border: 2px solid var(--accent); box-shadow: 0 20px 60px rgba(var(--accent-rgb), 0.12); }
.popular-badge { position: absolute; top: -14px; right: 32px; background: var(--accent); color: #fff; font-size: 12px; font-weight: 800; padding: 6px 16px; border-radius: 20px; box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.3); }
.plan-header h3 { font-size: 24px; font-weight: 850; margin: 0 0 6px; color: var(--ink); }
.badge-sub { font-size: 14px; color: var(--muted); }
.price-val { font-size: 46px; font-weight: 900; letter-spacing: -.03em; margin: 28px 0; color: var(--ink); }
.price-val span { font-size: 15px; font-weight: 500; color: var(--muted); }
.plan-features { list-style: none; padding: 0; margin: 0 0 36px; display: flex; flex-direction: column; gap: 14px; font-size: 15px; color: #334155; font-weight: 500; }
.plan-features li { position: relative; padding-left: 18px; }
.plan-features li::before { content: ''; position: absolute; left: 0; top: .55em; width: 6px; height: 6px; border-radius: 50%; background: var(--accent); }
.btn-full { width: 100%; justify-content: center; margin-top: auto; }
.testimonial-section { padding: 90px 0; text-align: center; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); background: #fff; }
.testimonial-label { color: var(--accent); font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; margin-bottom: 24px; }
.testimonial-section blockquote { max-width: 820px; margin: 0 auto 36px; font-size: clamp(22px, 3vw, 32px); font-weight: 800; line-height: 1.4; letter-spacing: -.02em; color: var(--ink); }
.testimonial-author { display: flex; align-items: center; justify-content: center; gap: 16px; }
.author-avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--accent-bg); color: var(--accent); font-weight: 850; display: grid; place-items: center; font-size: 16px; border: 1px solid var(--accent-ring); }
.testimonial-author strong { display: block; font-size: 15px; color: var(--ink); }
.testimonial-author small { color: var(--muted); font-size: 13px; }
.final-cta { padding-block: 110px; text-align: center; }
.cta-card-box { background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%); color: #fff; border-radius: 28px; padding: 70px 40px; box-shadow: 0 30px 80px rgba(var(--accent-rgb), 0.3); max-width: 900px; margin: 0 auto; }
.cta-card-box .section-badge { background: rgba(255, 255, 255, 0.15); color: #fff; border: 1px solid rgba(255, 255, 255, 0.2); }
.cta-card-box h2 { font-size: clamp(34px, 4.5vw, 48px); font-weight: 850; letter-spacing: -.03em; margin: 14px 0 18px; color: #fff; }
.cta-card-box p { color: rgba(255, 255, 255, 0.85); font-size: 17px; max-width: 500px; margin: 0 auto 32px; line-height: 1.6; }
.cta-card-box .button-primary { background: #fff; color: var(--accent); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
.cta-card-box .button-primary:hover { background: #f8fafc; transform: translateY(-2px); }
.site-footer { padding-block: 44px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--muted); }
.footer-links { display: flex; gap: 28px; }
.footer-links a { color: inherit; text-decoration: none; transition: color .2s; }
.footer-links a:hover { color: var(--accent); }
@media (max-width: 900px) { .preview-body { grid-template-columns: 1fr; } .preview-cart { border-top: 1px solid var(--line); } .feature-cards-grid { grid-template-columns: 1fr; } .pricing-grid { grid-template-columns: 1fr; } .final-cta { padding-block: 70px; } .cta-card-box { padding: 40px 24px; } .site-footer { flex-direction: column; gap: 20px; text-align: center; } .footer-links { justify-content: center; } }
</style>