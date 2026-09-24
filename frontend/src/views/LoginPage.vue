<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ThemeSwitcher from '@/components/ThemeSwitcher.vue'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function handleLogin() {
  if (!email.value || !password.value) {
    errorMessage.value = 'Email dan password wajib diisi.'
    return
  }
  
  errorMessage.value = ''
  successMessage.value = ''
  isLoading.value = true

  try {
    await auth.login(email.value, password.value)
    successMessage.value = 'Login berhasil! Mengalihkan...'
    setTimeout(() => {
      router.push('/dashboard')
    }, 500)
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Email atau password salah.'
  } finally {
    isLoading.value = false
  }
}

function togglePassword() {
  showPassword.value = !showPassword.value
}
</script>

<template>
  <div class="login-wrapper">
    <div class="split-layout">
      <!-- Left: Branding & Visuals -->
      <div class="login-left">
        <div class="left-overlay"></div>
        <div class="left-content">
          <RouterLink to="/" class="brand slide-up" style="--delay: 0.1s">
            <div class="brand-icon">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <h1 class="brand-name">DagangYuk</h1>
          </RouterLink>

          <div class="hero-text slide-up" style="--delay: 0.2s">
            <h2>Kelola Bisnis Anda<br/>Lebih Cerdas & Efisien</h2>
            <p>Sistem Point of Sale multi-tenant yang dirancang untuk mempercepat transaksi dan mempermudah analisis penjualan toko Anda.</p>
          </div>

          <div class="feature-pills slide-up" style="--delay: 0.3s">
            <div class="pill"> Multi-Tenant</div>
            <div class="pill"> Real-time Analytics</div>
            <div class="pill"> Keamanan Tinggi</div>
          </div>
        </div>
      </div>

      <!-- Right: Login Form -->
      <div class="login-right">
        <div class="right-topbar">
          <RouterLink to="/" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
          </RouterLink>
          <ThemeSwitcher />
        </div>

        <div class="form-container">
          <div class="form-header slide-up" style="--delay: 0.1s">
            <h2>Masuk ke Akun</h2>
            <p>Selamat datang kembali! Silakan masukkan detail Anda.</p>
          </div>

          <div v-if="errorMessage" class="alert alert-error slide-up" style="--delay: 0.2s">
            {{ errorMessage }}
          </div>
          <div v-if="successMessage" class="alert alert-success slide-up" style="--delay: 0.2s">
            {{ successMessage }}
          </div>

          <form @submit.prevent="handleLogin" class="login-form slide-up" style="--delay: 0.3s">
            <div class="form-group">
              <label for="email">Email</label>
              <div class="input-box">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input id="email" v-model="email" type="email" placeholder="contoh@toko.com" :disabled="isLoading" required />
              </div>
            </div>

            <div class="form-group">
              <div class="label-row">
                <label for="password">Password</label>
                <a href="#" class="forgot-link">Lupa Password?</a>
              </div>
              <div class="input-box">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input id="password" v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" :disabled="isLoading" required />
                <button type="button" class="btn-toggle" @click="togglePassword" :disabled="isLoading">
                  <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a2 2 0 1 1-2.83-2.83"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>

            <button type="submit" class="btn-primary" :disabled="isLoading">
              <span v-if="isLoading" class="spinner"></span>
              <span v-else>Masuk Sekarang</span>
            </button>
          </form>

          <p class="form-footer slide-up" style="--delay: 0.4s">
            Belum punya akun? <a href="#">Hubungi Admin</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Reset & Base */
.login-wrapper {
  min-height: 100vh;
  background: var(--surface);
  display: flex;
  font-family: var(--font-sans);
}

.split-layout {
  display: flex;
  width: 100%;
  min-height: 100vh;
}

/* ── LEFT PANEL ── */
.login-left {
  flex: 1;
  position: relative;
  background: #0f172a;
  background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&q=80&w=1920');
  background-size: cover;
  background-position: center;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 60px;
  overflow: hidden;
}

.left-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 58, 138, 0.85) 100%);
  z-index: 1;
}

.left-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: white;
  margin-bottom: auto;
}
.brand-icon {
  width: 42px; height: 42px;
  background: var(--accent);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
}
.brand-icon svg { width: 24px; height: 24px; color: white; }
.brand-name { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }

.hero-text { margin-bottom: 40px; }
.hero-text h2 {
  font-size: 42px;
  font-weight: 800;
  line-height: 1.15;
  margin-bottom: 20px;
  letter-spacing: -1px;
}
.hero-text p {
  font-size: 16px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.8);
  max-width: 480px;
}

.feature-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}
.pill {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 8px 16px;
  border-radius: 99px;
  font-size: 14px;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

/* ── RIGHT PANEL ── */
.login-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--surface);
  position: relative;
  max-width: 650px;
  width: 100%;
}

.right-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30px 40px;
}
.back-link {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 600;
  color: var(--muted);
  text-decoration: none;
  transition: color 0.2s;
}
.back-link:hover { color: var(--ink); }

.form-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 0 80px;
  max-width: 520px;
  margin: 0 auto;
  width: 100%;
  padding-bottom: 80px;
}

.form-header { margin-bottom: 32px; }
.form-header h2 { font-size: 32px; font-weight: 800; color: var(--ink); margin-bottom: 8px; letter-spacing: -0.5px; }
.form-header p { font-size: 15px; color: var(--muted); }

.alert { padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; margin-bottom: 24px; }
.alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

.login-form { display: flex; flex-direction: column; gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 14px; font-weight: 600; color: var(--ink); }

.label-row { display: flex; justify-content: space-between; align-items: center; }
.forgot-link { font-size: 13px; font-weight: 600; color: var(--accent); text-decoration: none; }
.forgot-link:hover { text-decoration: underline; }

.input-box {
  position: relative;
  display: flex;
  align-items: center;
}
.input-box .icon {
  position: absolute; left: 16px;
  width: 18px; height: 18px;
  color: var(--muted);
  pointer-events: none;
}
.input-box input {
  width: 100%;
  height: 48px;
  padding: 0 44px;
  border: 1.5px solid var(--border);
  border-radius: 12px;
  font-size: 15px;
  color: var(--ink);
  background: var(--white);
  transition: all 0.2s;
  font-family: inherit;
}
.input-box input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); outline: none; }

.btn-toggle {
  position: absolute; right: 12px;
  background: none; border: none;
  color: var(--muted); cursor: pointer;
  padding: 4px; display: flex; align-items: center;
}
.btn-toggle:hover { color: var(--ink); }
.btn-toggle svg { width: 18px; height: 18px; }

.btn-primary {
  height: 48px;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  margin-top: 10px;
  transition: all 0.2s;
  display: flex; justify-content: center; align-items: center;
}
.btn-primary:hover:not(:disabled) {
  background: var(--accent-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(var(--accent-rgb, 59, 130, 246), 0.3);
}
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner {
  width: 20px; height: 20px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.form-footer {
  margin-top: 32px;
  text-align: center;
  font-size: 14px;
  color: var(--muted);
}
.form-footer a { color: var(--ink); font-weight: 600; text-decoration: none; }
.form-footer a:hover { color: var(--accent); text-decoration: underline; }

/* ── ANIMATIONS ── */
.slide-up {
  opacity: 0;
  transform: translateY(20px);
  animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  animation-delay: var(--delay, 0s);
}
@keyframes slideUpFade {
  to { opacity: 1; transform: translateY(0); }
}

/* ── RESPONSIVE ── */
@media (max-width: 992px) {
  .login-left { display: none; }
  .login-right { max-width: 100%; }
}
@media (max-width: 576px) {
  .form-container { padding: 0 24px; padding-bottom: 40px; }
  .right-topbar { padding: 20px 24px; }
  .form-header h2 { font-size: 28px; }
}
</style>
