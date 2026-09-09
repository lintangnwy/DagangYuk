<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { gsap } from 'gsap'
import ThemeSwitcher from '@/components/ThemeSwitcher.vue'

const router = useRouter()
const auth   = useAuthStore()

const email        = ref('')
const password     = ref('')
const showPassword = ref(false)
const isLoading    = ref(false)
const errorMessage = ref('')

onMounted(() => {
  gsap.from('.panel-left',  { opacity: 0, x: -28, duration: 0.55, ease: 'power2.out' })
  gsap.from('.panel-right', { opacity: 0, x:  28, duration: 0.55, ease: 'power2.out' })
  gsap.from('.form-head, .field, .btn-submit, .foot-note', {
    opacity: 0, y: 12, duration: 0.38, stagger: 0.07,
    ease: 'power2.out', delay: 0.28,
  })
})

async function handleLogin() {
  if (!email.value || !password.value) {
    errorMessage.value = 'Email dan password harus diisi.'
    return
  }
  errorMessage.value = ''
  isLoading.value    = true
  try {
    await auth.login(email.value, password.value)
    router.push('/pos')
  } catch (err: unknown) {
    errorMessage.value = err instanceof Error ? err.message : 'Login gagal.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-page">

    <!-- Kiri — branding -->
    <aside class="panel-left">
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

      <div class="left-body">
        <!-- Dekorasi angka -->
        <div class="deco-nums" aria-hidden="true">
          <div class="deco-card">
            <span class="deco-label">Transaksi hari ini</span>
            <span class="deco-val">128</span>
          </div>
          <div class="deco-card deco-card--sm">
            <span class="deco-label">Stok produk</span>
            <span class="deco-val">54</span>
          </div>
        </div>

        <blockquote>
          <p>"Sekarang tutup toko tinggal lihat rekap di hp. Tidak perlu repot lagi."</p>
          <footer>
            <div class="t-av">R</div>
            <div class="t-info">
              <strong>Rina Marlina</strong>
              <span>Toko Sembako Rina, Bandung</span>
            </div>
          </footer>
        </blockquote>
      </div>
    </aside>

    <!-- Kanan — form -->
    <main class="panel-right">
      <RouterLink to="/" class="back-link">← Kembali</RouterLink>
      <div class="theme-corner"><ThemeSwitcher /></div>

      <div class="form-wrap">
        <div class="form-head">
          <h1>Selamat datang</h1>
          <p>Masuk untuk melanjutkan ke dasbor toko Anda</p>
        </div>

        <form @submit.prevent="handleLogin" novalidate>
          <Transition name="alert">
            <div v-if="errorMessage" class="alert" role="alert">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              {{ errorMessage }}
            </div>
          </Transition>

          <div class="field">
            <label for="email">Alamat email</label>
            <input
              id="email" v-model="email" type="email"
              placeholder="nama@email.com" autocomplete="email"
              :disabled="isLoading" />
          </div>

          <div class="field">
            <div class="field-top">
              <label for="password">Password</label>
              <a href="#" tabindex="-1" class="link-sm">Lupa password?</a>
            </div>
            <div class="pw-wrap">
              <input
                id="password" v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Masukkan password"
                autocomplete="current-password"
                :disabled="isLoading" />
              <button
                type="button" class="pw-toggle"
                @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Sembunyikan' : 'Tampilkan'">
                <!-- Eye -->
                <svg v-if="!showPassword" width="15" height="15" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <!-- Eye-off -->
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span v-if="isLoading" class="spinner" aria-hidden="true"></span>
            {{ isLoading ? 'Memproses…' : 'Masuk' }}
          </button>
        </form>

        <p class="foot-note">
          Belum terdaftar? <a href="#">Hubungi admin toko</a>
        </p>
      </div>
    </main>

  </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.login-page {
  min-height: 100dvh;
  display: grid;
  grid-template-columns: 1fr 1fr;
  font-size: 14px;
  color: var(--ink);
}

/* ── Panel kiri ── */
.panel-left {
  background: #0f172a;
  padding: clamp(28px, 4vw, 48px);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.panel-left::before,
.panel-left::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
}

.panel-left::before {
  width: 320px;
  height: 320px;
  background: radial-gradient(circle, rgba(var(--accent-rgb, 37,99,235), 0.15) 0%, transparent 70%);
  top: -80px;
  right: -80px;
}

.panel-left::after {
  width: 240px;
  height: 240px;
  background: radial-gradient(circle, rgba(var(--accent-rgb, 37,99,235), 0.08) 0%, transparent 70%);
  bottom: -60px;
  left: -60px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
  color: #fff;
  position: relative;
  z-index: 1;
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

.left-body {
  margin-top: auto;
  margin-bottom: clamp(32px, 6vh, 56px);
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.deco-nums { display: flex; gap: 10px; }

.deco-card {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  padding: 14px 16px;
  flex: 1;
  backdrop-filter: blur(6px);
}

.deco-card--sm { flex: 0.7; }

.deco-label {
  display: block;
  font-size: 11px;
  color: rgba(255,255,255,0.4);
  margin-bottom: 6px;
}

.deco-val {
  font-size: 28px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -1px;
}

blockquote { margin: 0; }

blockquote p {
  font-size: clamp(15px, 1.6vw, 18px);
  font-weight: 400;
  color: rgba(255,255,255,0.82);
  line-height: 1.65;
  margin-bottom: 20px;
  max-width: 340px;
}

blockquote footer { display: flex; align-items: center; gap: 12px; }

.t-av {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--accent-bg);
  color: var(--accent);
  font-size: 13px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 2px solid var(--accent-ring);
  transition: background 0.3s, color 0.3s, border-color 0.3s;
}

.t-info { display: flex; flex-direction: column; gap: 2px; }
.t-info strong { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.9); }
.t-info span   { font-size: 11.5px; color: rgba(255,255,255,0.35); }

/* ── Panel kanan ── */
.panel-right {
  background: var(--surface);
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  padding: clamp(40px, 7vw, 72px) clamp(28px, 6vw, 64px);
  position: relative;
}

.back-link {
  display: none;
  position: absolute;
  top: 20px;
  left: 24px;
  font-size: 13px;
  color: var(--muted);
  text-decoration: none;
  transition: color 0.15s;
}
.back-link:hover { color: var(--ink); }

.theme-corner {
  position: absolute;
  top: 16px;
  right: 20px;
}

.form-wrap {
  width: 100%;
  max-width: 360px;
}

.form-head { margin-bottom: 30px; }

.form-head h1 {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.5px;
  color: var(--ink);
  margin-bottom: 5px;
}

.form-head p { font-size: 14px; color: var(--muted); }

form { display: flex; flex-direction: column; gap: 16px; }

.alert {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 7px;
  line-height: 1.5;
}

.alert-enter-active { transition: all 0.25s ease-out; }
.alert-leave-active  { transition: all 0.2s ease-in; }
.alert-enter-from,
.alert-leave-to { opacity: 0; transform: translateY(-6px); }

.field { display: flex; flex-direction: column; gap: 5px; }

.field label { font-size: 12.5px; font-weight: 600; color: #374151; }

.field-top { display: flex; justify-content: space-between; align-items: center; }
.field-top label { font-size: 12.5px; font-weight: 600; color: #374151; }

.link-sm {
  font-size: 12px;
  color: var(--subtle);
  text-decoration: none;
  transition: color 0.15s;
}
.link-sm:hover { color: var(--accent); }

.field input,
.pw-wrap input {
  width: 100%;
  height: 42px;
  padding: 0 14px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-size: 14px;
  color: var(--ink);
  background: var(--white);
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  -webkit-appearance: none;
}

.field input:focus,
.pw-wrap input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-ring);
}

.field input::placeholder,
.pw-wrap input::placeholder { color: #d1d5db; }

.field input:disabled,
.pw-wrap input:disabled {
  background: var(--surface);
  cursor: not-allowed;
  color: var(--subtle);
}

.pw-wrap { position: relative; }
.pw-wrap input { padding-right: 42px; }

.pw-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--subtle);
  padding: 4px;
  display: flex;
  align-items: center;
  transition: color 0.15s;
}
.pw-toggle:hover { color: var(--ink); }

.btn-submit {
  width: 100%;
  height: 44px;
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 14.5px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
  margin-top: 4px;
}

.btn-submit:hover:not(:disabled) {
  background: var(--accent-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px color-mix(in srgb, var(--accent) 35%, transparent);
}

.btn-submit:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }

.foot-note {
  text-align: center;
  margin-top: 20px;
  font-size: 13px;
  color: var(--muted);
}

.foot-note a {
  color: var(--accent);
  font-weight: 600;
  text-decoration: none;
  transition: color 0.3s;
}
.foot-note a:hover { text-decoration: underline; }

/* ── Responsive ── */
@media (max-width: 860px) {
  .login-page { grid-template-columns: 360px 1fr; }
  .deco-nums { display: none; }
}

@media (max-width: 640px) {
  .login-page { grid-template-columns: 1fr; }
  .panel-left { display: none; }
  .panel-right { justify-content: flex-start; padding-top: 72px; }
  .back-link { display: inline-flex; }
  .form-wrap { max-width: 100%; }
}
</style>
