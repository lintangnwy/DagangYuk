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
  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
  tl.from('.split-left',  { x: '-100%', duration: 0.8 })
    .from('.split-right', { opacity: 0, duration: 0.8 }, '-=0.4')
    .from('.form-stagger', { opacity: 0, y: 15, stagger: 0.1, duration: 0.5 }, '-=0.4')
})

async function handleLogin() {
  if (!email.value || !password.value) {
    errorMessage.value = 'Email dan password wajib diisi.'
    return
  }
  errorMessage.value = ''
  isLoading.value    = true
  try {
    await auth.login(email.value, password.value)
    router.push('/dashboard')
  } catch (err: unknown) {
    errorMessage.value = err instanceof Error ? err.message : 'Kredensial tidak valid.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="auth-layout">
    <!-- Left Visual Panel -->
    <div class="split-left">
      <div class="brand-area">
        <RouterLink to="/" class="brand-link">
          <div class="brand-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
          </div>
          <span class="brand-text">DagangYuk</span>
        </RouterLink>
      </div>

      <div class="testimonial">
        <div class="quote-icon">"</div>
        <p class="quote-text">Sistem kasir paling intuitif yang pernah kami gunakan. Training kasir baru kini hanya butuh waktu 5 menit.</p>
        <div class="author">
          <div class="author-avatar">AM</div>
          <div class="author-info">
            <div class="author-name">Ahmad Maulana</div>
            <div class="author-role">Pemilik Kopi Senja</div>
          </div>
        </div>
      </div>

      <!-- Abstract Pattern Overlay -->
      <div class="pattern-overlay"></div>
    </div>

    <!-- Right Form Panel -->
    <div class="split-right">
      <div class="top-nav">
        <RouterLink to="/" class="back-link">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          Kembali
        </RouterLink>
        <ThemeSwitcher />
      </div>

      <div class="form-container">
        <div class="form-header form-stagger">
          <h1 class="form-title">Masuk ke Akun</h1>
          <p class="form-subtitle">Selamat datang kembali! Silakan masukkan detail Anda.</p>
        </div>

        <form @submit.prevent="handleLogin" novalidate class="login-form">
          <Transition name="fade-slide">
            <div v-if="errorMessage" class="error-alert form-stagger">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              {{ errorMessage }}
            </div>
          </Transition>

          <div class="input-group form-stagger">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="nama@perusahaan.com"
              autocomplete="email"
              :disabled="isLoading"
              class="form-input"
            />
          </div>

          <div class="input-group form-stagger">
            <div class="label-row">
              <label for="password">Password</label>
              <a href="#" class="forgot-link">Lupa password?</a>
            </div>
            <div class="password-wrapper">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                autocomplete="current-password"
                :disabled="isLoading"
                class="form-input"
              />
              <button
                type="button"
                class="btn-toggle-pw"
                @click="showPassword = !showPassword"
                title="Toggle password visibility"
              >
                <svg v-if="!showPassword" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg v-else viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a2 2 0 1 1-2.83-2.83"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-submit form-stagger" :disabled="isLoading">
            <svg v-if="isLoading" class="spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="2" x2="12" y2="6"></line>
              <line x1="12" y1="18" x2="12" y2="22"></line>
              <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
              <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
              <line x1="2" y1="12" x2="6" y2="12"></line>
              <line x1="18" y1="12" x2="22" y2="12"></line>
              <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
              <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
            </svg>
            <span v-else>Masuk</span>
          </button>
        </form>

        <div class="form-footer form-stagger">
          Tidak punya akun? <a href="#">Hubungi Admin</a>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Base Variables & Reset */
.auth-layout {
  display: flex;
  min-height: 100vh;
  background-color: var(--surface, #f9fafb);
  font-family: system-ui, -apple-system, sans-serif;
}

/* ── Left Split (Visual) ── */
.split-left {
  flex: 1;
  background: linear-gradient(135deg, var(--ink, #111827) 0%, #1e293b 100%);
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 48px;
  position: relative;
  overflow: hidden;
}

.brand-link {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: white;
  position: relative;
  z-index: 10;
}
.brand-logo {
  width: 36px; height: 36px;
  background: var(--accent, #2563eb);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
}
.brand-logo svg { width: 20px; height: 20px; }
.brand-text { font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }

.testimonial {
  position: relative;
  z-index: 10;
  max-width: 480px;
}
.quote-icon {
  font-family: serif;
  font-size: 80px;
  line-height: 1;
  color: rgba(255,255,255,0.2);
  margin-bottom: -20px;
}
.quote-text {
  font-size: 24px;
  font-weight: 500;
  line-height: 1.5;
  margin-bottom: 32px;
}
.author {
  display: flex;
  align-items: center;
  gap: 16px;
}
.author-avatar {
  width: 48px; height: 48px;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center;
  font-weight: 600; font-size: 16px;
  border: 1px solid rgba(255,255,255,0.2);
}
.author-name { font-weight: 600; font-size: 16px; }
.author-role { font-size: 14px; color: rgba(255,255,255,0.6); }

.pattern-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
  background-size: 32px 32px;
  opacity: 0.5;
  mask-image: linear-gradient(to bottom right, black, transparent);
  -webkit-mask-image: linear-gradient(to bottom right, black, transparent);
}

/* ── Right Split (Form) ── */
.split-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--white, #ffffff);
  position: relative;
}

.top-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 48px;
}
.back-link {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  color: var(--muted, #6b7280);
  text-decoration: none;
  transition: color 0.2s;
}
.back-link:hover { color: var(--ink, #111827); }

.form-container {
  max-width: 420px;
  width: 100%;
  margin: auto;
  padding: 0 32px;
}

.form-header { margin-bottom: 40px; }
.form-title {
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -1px;
  color: var(--ink);
  margin-bottom: 8px;
}
.form-subtitle {
  font-size: 15px;
  color: var(--muted);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.error-alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 14px;
  font-weight: 600;
  color: var(--ink);
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.forgot-link {
  font-size: 13px;
  font-weight: 500;
  color: var(--accent, #2563eb);
  text-decoration: none;
}
.forgot-link:hover { text-decoration: underline; }

.form-input {
  width: 100%;
  height: 48px;
  padding: 0 16px;
  background: var(--white);
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 10px;
  font-size: 15px;
  color: var(--ink);
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.form-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 4px rgba(var(--accent-rgb, 37, 99, 235), 0.1);
}
.form-input::placeholder { color: #9ca3af; }
.form-input:disabled { background: var(--surface); color: #9ca3af; cursor: not-allowed; }

.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.password-wrapper .form-input { padding-right: 48px; }
.btn-toggle-pw {
  position: absolute;
  right: 12px;
  background: none; border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  display: flex; align-items: center; justify-content: center;
  transition: color 0.2s;
}
.btn-toggle-pw:hover { color: var(--ink); }

.btn-submit {
  width: 100%;
  height: 48px;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  margin-top: 8px;
}
.btn-submit:hover:not(:disabled) {
  background: var(--accent-dark, #1d4ed8);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(var(--accent-rgb, 37, 99, 235), 0.2);
}
.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  width: 20px; height: 20px;
  animation: spin 1s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

.form-footer {
  margin-top: 32px;
  text-align: center;
  font-size: 14px;
  color: var(--muted);
}
.form-footer a {
  color: var(--ink);
  font-weight: 600;
  text-decoration: none;
}
.form-footer a:hover { text-decoration: underline; }

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

/* Responsive */
@media (max-width: 992px) {
  .split-left { display: none; }
  .top-nav { padding: 24px; }
  .form-container { padding: 0 24px; }
}
</style>
