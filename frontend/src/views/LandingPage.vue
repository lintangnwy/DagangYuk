<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { onMounted, onUnmounted, ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import ThemeSwitcher from '@/components/ThemeSwitcher.vue'

gsap.registerPlugin(ScrollTrigger)

const features = [
  {
    num: '01',
    title: 'Kasir yang cepat',
    desc: 'Cari produk, masukkan jumlah, selesai. Tidak perlu kalkulator terpisah.',
  },
  {
    num: '02',
    title: 'Stok otomatis',
    desc: 'Setiap transaksi langsung memperbarui stok. Tidak ada selisih lagi.',
  },
  {
    num: '03',
    title: 'Laporan real-time',
    desc: 'Buka aplikasi, lihat pemasukan hari ini. Sesederhana itu.',
  },
  {
    num: '04',
    title: 'Jalan tanpa internet',
    desc: 'Kasir tetap beroperasi. Data menyinkron otomatis saat online kembali.',
  },
]

// ── Rotating text ──────────────────────────────────────
const words        = ['lancar', 'cepat', 'mudah', 'rapi', 'aman']
const wordIndex    = ref(0)
const isAnim       = ref(false)
let   timer: ReturnType<typeof setInterval> | null = null

function startRotate() {
  timer = setInterval(() => {
    if (isAnim.value) return
    isAnim.value = true
    const el = document.querySelector('.rw') as HTMLElement | null
    if (!el) { isAnim.value = false; return }
    gsap.to(el, {
      y: -24, opacity: 0, duration: 0.25, ease: 'power2.in',
      onComplete() {
        wordIndex.value = (wordIndex.value + 1) % words.length
        gsap.fromTo(el,
          { y: 24, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.3, ease: 'power2.out',
            onComplete() { isAnim.value = false } },
        )
      },
    })
  }, 2000)
}

onMounted(() => {
  // Hero
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } })
  tl.from('.hero-eyebrow', { opacity: 0, y: 14, duration: 0.4 })
    .from('.hero-h1',      { opacity: 0, y: 22, duration: 0.5 }, '-=0.2')
    .from('.hero-sub',     { opacity: 0, y: 16, duration: 0.4 }, '-=0.2')
    .from('.hero-btns',    { opacity: 0, y: 12, duration: 0.4 }, '-=0.15')
    .from('.receipt',      { opacity: 0, x: 28, scale: 0.97, duration: 0.55 }, '-=0.35')

  // Stats
  gsap.from('.stat', {
    opacity: 0, y: 10, stagger: 0.1, duration: 0.4,
    scrollTrigger: { trigger: '.stats', start: 'top 88%' },
  })

  // Feature cards
  gsap.from('.f-card', {
    opacity: 0, y: 22, stagger: 0.09, duration: 0.45,
    scrollTrigger: { trigger: '.features-grid', start: 'top 85%' },
  })

  // Testimonial + CTA
  gsap.from('.testi-quote, .cta-content', {
    opacity: 0, y: 18, duration: 0.5,
    scrollTrigger: { trigger: '.testi', start: 'top 85%' },
  })
  gsap.from('.cta-content', {
    opacity: 0, y: 18, duration: 0.5,
    scrollTrigger: { trigger: '.cta', start: 'top 88%' },
  })

  setTimeout(startRotate, 1400)
})

onUnmounted(() => { if (timer) clearInterval(timer) })
</script>

<template>
  <div class="page">

    <!-- ── Navbar ── -->
    <header class="nav">
      <div class="wrap nav-inner">
        <RouterLink to="/" class="logo">
          <div class="logo-mark">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
          </div>
          <span>DagangYuk</span>
        </RouterLink>

        <div class="nav-right">
          <a href="#fitur" class="nav-link">Fitur</a>
          <ThemeSwitcher />
          <RouterLink to="/login" class="btn-nav">Masuk</RouterLink>
        </div>
      </div>
    </header>

    <!-- ── Hero ── -->
    <section class="hero">
      <div class="wrap hero-inner">
        <div class="hero-text">
          <p class="hero-eyebrow">Aplikasi kasir untuk UMKM</p>
          <h1 class="hero-h1">
            Jualan lebih
            <span class="rw-wrap"><span class="rw">{{ words[wordIndex] }}</span></span>,<br />
            catatan lebih rapi
          </h1>
          <p class="hero-sub">
            DagangYuk adalah kasir digital untuk pemilik toko — bukan untuk akuntan.
            Tidak perlu training, tidak perlu setup berhari-hari.
          </p>
          <div class="hero-btns">
            <RouterLink to="/login" class="btn-primary">Mulai gratis</RouterLink>
            <a href="#fitur" class="btn-ghost">Lihat fitur</a>
          </div>
        </div>

        <!-- Receipt mockup -->
        <div class="hero-visual" aria-hidden="true">
          <div class="receipt">
            <div class="r-top">
              <span class="r-store">Toko Makmur</span>
              <span class="r-date">08 Sep 2026</span>
            </div>
            <div class="r-line dashed"></div>
            <div class="r-items">
              <div class="r-item"><span>Indomie Goreng</span><span>Rp 3.500</span></div>
              <div class="r-item"><span>Teh Botol 500ml</span><span>Rp 5.000</span></div>
              <div class="r-item"><span>Roti Tawar</span><span>Rp 12.000</span></div>
            </div>
            <div class="r-line"></div>
            <div class="r-item r-total"><span>Total</span><strong>Rp 20.500</strong></div>
            <div class="r-pay">
              <div class="r-item small"><span>Bayar</span><span>Rp 25.000</span></div>
              <div class="r-item small green"><span>Kembali</span><strong>Rp 4.500</strong></div>
            </div>
            <p class="r-thanks">Terima kasih sudah berbelanja</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Stats ── -->
    <div class="stats">
      <div class="wrap stats-inner">
        <div class="stat"><strong>500+</strong><span>toko aktif</span></div>
        <div class="stat-sep"></div>
        <div class="stat"><strong>1 juta+</strong><span>transaksi</span></div>
        <div class="stat-sep"></div>
        <div class="stat"><strong>4.8/5</strong><span>kepuasan pengguna</span></div>
      </div>
    </div>

    <!-- ── Features ── -->
    <section class="features" id="fitur">
      <div class="wrap">
        <div class="section-head">
          <h2>Yang Anda butuhkan, sudah ada</h2>
          <p>Fitur yang benar-benar dipakai setiap hari, tidak lebih.</p>
        </div>
        <div class="features-grid">
          <div class="f-card" v-for="f in features" :key="f.num">
            <span class="f-num">{{ f.num }}</span>
            <h3>{{ f.title }}</h3>
            <p>{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Testimonial ── -->
    <section class="testi">
      <div class="wrap testi-inner">
        <blockquote class="testi-quote">
          <p>"Dulu stok saya catat di buku, sering selisih. Tiga bulan pakai DagangYuk tidak pernah selisih lagi."</p>
          <footer>
            <div class="t-av">S</div>
            <div>
              <strong>Sari Wulandari</strong>
              <span>Warung Kelontong Bu Sari, Semarang</span>
            </div>
          </footer>
        </blockquote>
      </div>
    </section>

    <!-- ── CTA ── -->
    <section class="cta">
      <div class="wrap cta-content">
        <div>
          <h2>Mulai hari ini, gratis</h2>
          <p>Tidak perlu kartu kredit. Tidak perlu instalasi.</p>
        </div>
        <RouterLink to="/login" class="btn-primary">Buat akun</RouterLink>
      </div>
    </section>

    <!-- ── Footer ── -->
    <footer class="footer">
      <div class="wrap footer-inner">
        <span class="logo-text">DagangYuk</span>
        <span class="footer-copy">© 2026 DagangYuk</span>
      </div>
    </footer>

  </div>
</template>

<style scoped>
/* ── Tokens — semua accent dari global :root ── */
.page {
  --r: 9px;
  min-height: 100vh;
  background: var(--white);
  color: var(--ink);
  font-size: 15px;
  line-height: 1.6;
}

.wrap {
  width: 100%;
  max-width: 1100px;
  margin-inline: auto;
  padding-inline: clamp(18px, 5vw, 48px);
}

/* ── Nav ── */
.nav {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255,255,255,0.92);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
}

.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 58px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
  color: var(--ink);
}

.logo-mark {
  width: 30px;
  height: 30px;
  background: var(--accent);
  color: #fff;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background 0.3s;
}

.logo span {
  font-size: 16px;
  font-weight: 700;
  letter-spacing: -0.3px;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-link {
  font-size: 14px;
  color: var(--muted);
  text-decoration: none;
  transition: color 0.15s;
}
.nav-link:hover { color: var(--ink); }

.btn-nav {
  font-size: 13.5px;
  font-weight: 600;
  color: #fff;
  background: var(--accent);
  border: none;
  padding: 7px 18px;
  border-radius: 7px;
  text-decoration: none;
  transition: background 0.15s;
}
.btn-nav:hover { background: var(--accent-dark); }

/* ── Buttons ── */
.btn-primary {
  display: inline-flex;
  align-items: center;
  background: var(--accent);
  color: #fff;
  font-size: 14.5px;
  font-weight: 600;
  padding: 11px 26px;
  border-radius: var(--r);
  text-decoration: none;
  transition: background 0.15s, transform 0.15s;
  white-space: nowrap;
}
.btn-primary:hover { background: var(--accent-dark); transform: translateY(-1px); }

.btn-ghost {
  display: inline-flex;
  align-items: center;
  color: var(--muted);
  font-size: 14.5px;
  font-weight: 500;
  padding: 11px 26px;
  border-radius: var(--r);
  border: 1.5px solid var(--border);
  text-decoration: none;
  transition: border-color 0.15s, color 0.15s;
}
.btn-ghost:hover { border-color: var(--accent-ring); color: var(--accent); }

/* ── Hero ── */
.hero {
  padding-block: clamp(64px, 10vw, 108px);
  border-bottom: 1px solid var(--border);
}

.hero-inner {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: clamp(40px, 6vw, 80px);
  align-items: center;
}

.hero-eyebrow {
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  color: var(--accent);
  margin-bottom: 16px;
  background: var(--accent-bg);
  display: inline-block;
  padding: 3px 12px;
  border-radius: 999px;
  border: 1px solid var(--accent-ring);
  transition: color 0.3s, background 0.3s, border-color 0.3s;
}

.hero-h1 {
  font-size: clamp(30px, 4.5vw, 52px);
  font-weight: 800;
  line-height: 1.12;
  letter-spacing: -1.5px;
  color: var(--ink);
  margin-bottom: 20px;
}

/* Rotating */
.rw-wrap {
  display: inline-block;
  overflow: hidden;
  vertical-align: bottom;
  min-width: 100px;
  height: 1.12em;
  position: relative;
}

.rw {
  display: inline-block;
  color: var(--accent);
  font-style: italic;
  will-change: transform, opacity;
  transition: color 0.3s;
}

.hero-sub {
  font-size: clamp(15px, 2vw, 17px);
  color: var(--muted);
  line-height: 1.75;
  max-width: 460px;
  margin-bottom: 36px;
}

.hero-btns {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

/* Receipt */
.hero-visual { flex-shrink: 0; }

.receipt {
  width: 232px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 18px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.07);
  font-size: 12.5px;
  font-family: 'Courier New', monospace;
}

.r-top { display: flex; justify-content: space-between; margin-bottom: 10px; }
.r-store { font-weight: 700; font-size: 13px; color: var(--ink); }
.r-date  { color: var(--subtle); }

.r-line { border: none; border-top: 1px solid var(--border); margin-block: 10px; }
.r-line.dashed { border-top-style: dashed; }

.r-items { display: flex; flex-direction: column; gap: 6px; }

.r-item { display: flex; justify-content: space-between; color: var(--ink); }
.r-item span:last-child { color: var(--muted); }
.r-total { font-weight: 700; margin-top: 2px; }
.r-total strong { color: var(--ink); }

.r-pay { margin-top: 8px; display: flex; flex-direction: column; gap: 4px; }
.r-item.small { font-size: 11.5px; color: var(--subtle); }
.r-item.green strong { color: var(--accent); transition: color 0.3s; }

.r-thanks {
  margin-top: 12px;
  text-align: center;
  font-size: 11px;
  color: var(--subtle);
  border-top: 1px dashed var(--border);
  padding-top: 10px;
}

/* ── Stats ── */
.stats { border-bottom: 1px solid var(--border); padding-block: 18px; }

.stats-inner {
  display: flex;
  align-items: center;
  gap: clamp(16px, 4vw, 48px);
  flex-wrap: wrap;
}

.stat { display: flex; flex-direction: column; gap: 2px; }
.stat strong { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; color: var(--ink); }
.stat span   { font-size: 12px; color: var(--subtle); }

.stat-sep { width: 1px; height: 28px; background: var(--border); flex-shrink: 0; }

/* ── Features ── */
.features { padding-block: clamp(60px, 8vw, 96px); border-bottom: 1px solid var(--border); }

.section-head { margin-bottom: clamp(32px, 5vw, 52px); max-width: 520px; }
.section-head h2 { font-size: clamp(22px, 3vw, 30px); font-weight: 800; letter-spacing: -0.5px; color: var(--ink); margin-bottom: 8px; }
.section-head p  { font-size: 15px; color: var(--muted); }

.features-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  border-top: 1px solid var(--border);
  border-left: 1px solid var(--border);
}

.f-card {
  padding: clamp(22px, 3vw, 38px);
  border-right: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  transition: background 0.15s;
}
.f-card:hover { background: var(--surface); }

.f-num {
  display: block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: var(--accent-ring);
  margin-bottom: 14px;
  transition: color 0.3s;
}

.f-card h3 { font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 8px; letter-spacing: -0.2px; }
.f-card p  { font-size: 14px; color: var(--muted); line-height: 1.65; }

/* ── Testimonial ── */
.testi { padding-block: clamp(60px, 8vw, 96px); background: var(--surface); border-bottom: 1px solid var(--border); }
.testi-inner { max-width: 680px; }
.testi-quote { margin: 0; }

.testi-quote p {
  font-size: clamp(17px, 2.4vw, 22px);
  font-weight: 500;
  line-height: 1.6;
  color: var(--ink);
  letter-spacing: -0.2px;
  margin-bottom: 24px;
}

.testi-quote footer { display: flex; align-items: center; gap: 12px; }

.t-av {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--accent-bg);
  color: var(--accent);
  font-size: 14px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 2px solid var(--accent-ring);
  transition: background 0.3s, color 0.3s, border-color 0.3s;
}

.testi-quote footer strong { display: block; font-size: 13.5px; color: var(--ink); }
.testi-quote footer span   { font-size: 12px; color: var(--subtle); }

/* ── CTA ── */
.cta { padding-block: clamp(56px, 7vw, 88px); border-bottom: 1px solid var(--border); }

.cta-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  flex-wrap: wrap;
}

.cta-content h2 { font-size: clamp(20px, 3vw, 28px); font-weight: 800; letter-spacing: -0.5px; color: var(--ink); margin-bottom: 6px; }
.cta-content p  { font-size: 14.5px; color: var(--muted); }

/* ── Footer ── */
.footer { padding-block: 24px; }

.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
}

.logo-text { font-size: 15px; font-weight: 700; letter-spacing: -0.3px; color: var(--ink); }
.footer-copy { font-size: 13px; color: var(--subtle); }

/* ── Responsive ── */
@media (max-width: 800px) {
  .hero-inner { grid-template-columns: 1fr; }
  .hero-visual { display: none; }
}

@media (max-width: 560px) {
  .features-grid { grid-template-columns: 1fr; }
  .stat-sep { display: none; }
  .cta-content { flex-direction: column; align-items: flex-start; }
  .footer-inner { flex-direction: column; align-items: flex-start; }
}
</style>
