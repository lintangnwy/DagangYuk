<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  if (!email.value || !password.value) {
    errorMessage.value = 'Email dan password wajib diisi.'
    return
  }

  errorMessage.value = ''
  isLoading.value = true

  try {
    // TODO: Hubungkan ke Laravel API
    // const response = await fetch('/api/login', { ... })
    await new Promise((resolve) => setTimeout(resolve, 1200)) // simulasi request
    console.log('Login dengan:', email.value)
  } catch {
    errorMessage.value = 'Terjadi kesalahan. Coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <!-- Sisi Kiri — Branding -->
    <div class="left-panel">
      <RouterLink to="/" class="brand">
        <span class="brand-icon">🛒</span>
        <span class="brand-name">DagangYuk</span>
      </RouterLink>

      <div class="left-content">
        <div class="illustration">
          <div class="ill-circle outer"></div>
          <div class="ill-circle inner"></div>
          <div class="ill-icon">🛒</div>
        </div>
        <h2>Kelola Toko Anda dengan Mudah</h2>
        <p>
          Masuk ke dasbor DagangYuk dan mulai kelola penjualan, stok, dan laporan bisnis Anda
          dari mana saja.
        </p>
        <div class="feature-list">
          <div class="feature-item">
            <span class="check">✓</span>
            <span>Transaksi cepat & akurat</span>
          </div>
          <div class="feature-item">
            <span class="check">✓</span>
            <span>Laporan real-time</span>
          </div>
          <div class="feature-item">
            <span class="check">✓</span>
            <span>Manajemen stok otomatis</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sisi Kanan — Form Login -->
    <div class="right-panel">
      <div class="login-box">
        <div class="login-header">
          <h1>Selamat Datang 👋</h1>
          <p>Masuk ke akun DagangYuk Anda</p>
        </div>

        <form class="login-form" @submit.prevent="handleLogin" novalidate>
          <!-- Error Alert -->
          <div v-if="errorMessage" class="alert-error" role="alert">
            <span>⚠️</span> {{ errorMessage }}
          </div>

          <!-- Email -->
          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-wrapper">
              <span class="input-icon">✉️</span>
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="contoh@email.com"
                autocomplete="email"
                :disabled="isLoading"
              />
            </div>
          </div>

          <!-- Password -->
          <div class="form-group">
            <div class="label-row">
              <label for="password">Password</label>
              <a href="#" class="forgot-link">Lupa password?</a>
            </div>
            <div class="input-wrapper">
              <span class="input-icon">🔒</span>
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Masukkan password"
                autocomplete="current-password"
                :disabled="isLoading"
              />
              <button
                type="button"
                class="toggle-pw"
                @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
              >
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <!-- Remember Me -->
          <label class="remember">
            <input type="checkbox" />
            <span>Ingat saya</span>
          </label>

          <!-- Submit -->
          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            <span>{{ isLoading ? 'Memproses...' : 'Masuk' }}</span>
          </button>
        </form>

        <p class="register-hint">
          Belum punya akun?
          <a href="#">Hubungi admin</a>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
}

/* ---- Left Panel ---- */
.left-panel {
  background: linear-gradient(145deg, #1a1a2e 0%, #2d2d5e 100%);
  padding: 40px 56px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.left-panel::before {
  content: '';
  position: absolute;
  top: -120px;
  right: -120px;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: rgba(249, 115, 22, 0.08);
  pointer-events: none;
}

.left-panel::after {
  content: '';
  position: absolute;
  bottom: -80px;
  left: -80px;
  width: 300px;
  height: 300px;
  border-radius: 50%;
  background: rgba(249, 115, 22, 0.05);
  pointer-events: none;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  margin-bottom: auto;
}

.brand-icon {
  font-size: 28px;
}

.brand-name {
  font-size: 22px;
  font-weight: 700;
  color: #fff;
}

.left-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 40px 0 60px;
  position: relative;
  z-index: 1;
}

/* Illustration */
.illustration {
  position: relative;
  width: 120px;
  height: 120px;
  margin-bottom: 36px;
}

.ill-circle {
  position: absolute;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.ill-circle.outer {
  width: 120px;
  height: 120px;
  background: rgba(249, 115, 22, 0.12);
}

.ill-circle.inner {
  width: 80px;
  height: 80px;
  background: rgba(249, 115, 22, 0.18);
}

.ill-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 40px;
  line-height: 1;
}

.left-content h2 {
  font-size: 28px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 14px;
  line-height: 1.3;
}

.left-content p {
  font-size: 15px;
  color: #9999bb;
  line-height: 1.7;
  margin-bottom: 32px;
  max-width: 360px;
}

.feature-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #ccc;
  font-size: 15px;
}

.check {
  width: 22px;
  height: 22px;
  background: #f97316;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  flex-shrink: 0;
}

/* ---- Right Panel ---- */
.right-panel {
  background: #f9fafb;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 24px;
}

.login-box {
  width: 100%;
  max-width: 420px;
}

.login-header {
  margin-bottom: 36px;
}

.login-header h1 {
  font-size: 30px;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 8px;
}

.login-header p {
  font-size: 15px;
  color: #666;
}

/* Form */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.forgot-link {
  font-size: 13px;
  color: #f97316;
  text-decoration: none;
}

.forgot-link:hover {
  text-decoration: underline;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  font-size: 16px;
  pointer-events: none;
}

.input-wrapper input {
  width: 100%;
  padding: 12px 44px 12px 44px;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  font-size: 15px;
  background: #fff;
  color: #1a1a2e;
  transition: border-color 0.2s, box-shadow 0.2s;
  outline: none;
}

.input-wrapper input:focus {
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}

.input-wrapper input::placeholder {
  color: #bbb;
}

.input-wrapper input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.toggle-pw {
  position: absolute;
  right: 14px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  padding: 0;
  line-height: 1;
}

/* Remember */
.remember {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #555;
  cursor: pointer;
}

.remember input[type='checkbox'] {
  width: 16px;
  height: 16px;
  accent-color: #f97316;
}

/* Submit Button */
.btn-submit {
  background: #f97316;
  color: #fff;
  border: none;
  padding: 14px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-submit:hover:not(:disabled) {
  background: #ea6c0a;
  transform: translateY(-1px);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Spinner */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Register hint */
.register-hint {
  text-align: center;
  margin-top: 24px;
  font-size: 14px;
  color: #666;
}

.register-hint a {
  color: #f97316;
  font-weight: 600;
  text-decoration: none;
}

.register-hint a:hover {
  text-decoration: underline;
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
  .login-page {
    grid-template-columns: 1fr;
  }

  .left-panel {
    padding: 32px 24px;
    min-height: auto;
  }

  .left-content {
    padding: 24px 0 0;
  }

  .left-content h2 {
    font-size: 22px;
  }

  .left-content p {
    display: none;
  }

  .illustration {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
  }

  .ill-circle.outer { width: 80px; height: 80px; }
  .ill-circle.inner { width: 52px; height: 52px; }
  .ill-icon { font-size: 28px; }
}
</style>
