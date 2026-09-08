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
    errorMessage.value = 'Email dan password harus diisi.'
    return
  }
  errorMessage.value = ''
  isLoading.value = true
  try {
    // TODO: POST /api/login
    await new Promise((r) => setTimeout(r, 1000))
  } catch {
    errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="page">

    <!-- Panel kiri — branding -->
    <aside class="panel-left">
      <RouterLink to="/" class="wordmark">DagangYuk</RouterLink>

      <div class="panel-body">
        <blockquote>
          <p>"Sekarang tutup toko tinggal lihat rekap di hp. Tidak perlu repot lagi."</p>
          <footer>
            <div class="av">R</div>
            <div class="av-info">
              <span>Rina Marlina</span>
              <small>Toko Sembako Rina, Bandung</small>
            </div>
          </footer>
        </blockquote>
      </div>
    </aside>

    <!-- Panel kanan — form -->
    <main class="panel-right">

      <!-- Back link di mobile -->
      <RouterLink to="/" class="back-link">← Kembali</RouterLink>

      <div class="form-container">
        <div class="form-header">
          <h1>Selamat datang</h1>
          <p>Masuk untuk melanjutkan ke dasbor toko</p>
        </div>

        <form @submit.prevent="handleLogin" novalidate>

          <div v-if="errorMessage" class="alert" role="alert">
            {{ errorMessage }}
          </div>

          <div class="field">
            <label for="email">Alamat email</label>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="nama@email.com"
              autocomplete="email"
              :disabled="isLoading"
            />
          </div>

          <div class="field">
            <div class="field-top">
              <label for="password">Password</label>
              <a href="#" tabindex="-1" class="link-forgot">Lupa password?</a>
            </div>
            <div class="input-pw">
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
                <!-- Eye icon -->
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <!-- Eye-off icon -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span v-if="isLoading" class="spinner" aria-hidden="true"></span>
            <span>{{ isLoading ? 'Memproses...' : 'Masuk' }}</span>
          </button>

        </form>

        <p class="note">
          Belum terdaftar? <a href="#">Hubungi admin toko</a>
        </p>
      </div>

    </main>
  </div>
</template>

<style scoped>
/* ─── Layout utama ─── */
.page {
  min-height: 100dvh;
  display: grid;
  grid-template-columns: 1fr 1fr;
}

/* ─── Panel kiri ─── */
.panel-left {
  background: #111;
  padding: clamp(28px, 4vw, 48px);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

/* Subtle texture */
.panel-left::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    radial-gradient(circle at 80% 20%, rgba(255,255,255,0.03) 0%, transparent 50%),
    radial-gradient(circle at 20% 80%, rgba(255,255,255,0.02) 0%, transparent 50%);
  pointer-events: none;
}

.wordmark {
  font-size: 17px;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  letter-spacing: -0.3px;
  position: relative;
  z-index: 1;
}

.panel-body {
  margin-top: auto;
  margin-bottom: clamp(32px, 6vh, 64px);
  position: relative;
  z-index: 1;
}

blockquote {
  margin: 0;
}

blockquote p {
  font-size: clamp(16px, 1.6vw, 20px);
  font-weight: 400;
  color: #e0e0e0;
  line-height: 1.65;
  margin-bottom: 24px;
  max-width: 320px;
}

blockquote footer {
  display: flex;
  align-items: center;
  gap: 12px;
}

.av {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #2a2a2a;
  color: #ccc;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid #3a3a3a;
}

.av-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.av-info span {
  font-size: 13px;
  font-weight: 600;
  color: #d0d0d0;
}

.av-info small {
  font-size: 11px;
  color: #666;
}

/* ─── Panel kanan ─── */
.panel-right {
  background: #fafafa;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  padding: clamp(32px, 6vw, 64px) clamp(24px, 5vw, 48px);
  padding-left: clamp(48px, 7vw, 96px);
  position: relative;
}

.back-link {
  display: none;
  position: absolute;
  top: 24px;
  left: 24px;
  font-size: 13px;
  color: #888;
  text-decoration: none;
  transition: color 0.15s;
}

.back-link:hover {
  color: #111;
}

.form-container {
  width: 100%;
  max-width: 360px;
}

/* ─── Form header ─── */
.form-header {
  margin-bottom: 32px;
}

.form-header h1 {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.5px;
  color: #111;
  margin-bottom: 4px;
}

.form-header p {
  font-size: 14px;
  color: #888;
}

/* ─── Form fields ─── */
form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.alert {
  background: #fef3f3;
  border: 1px solid #f5c6c6;
  color: #b91c1c;
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 6px;
  line-height: 1.5;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field label {
  font-size: 13px;
  font-weight: 600;
  color: #222;
}

.field-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.field-top label {
  font-size: 13px;
  font-weight: 600;
  color: #222;
}

.link-forgot {
  font-size: 12px;
  color: #999;
  text-decoration: none;
  transition: color 0.15s;
}

.link-forgot:hover {
  color: #111;
}

.field input,
.input-pw input {
  width: 100%;
  height: 42px;
  padding: 0 14px;
  border: 1.5px solid #e0e0e0;
  border-radius: 7px;
  font-size: 14px;
  color: #111;
  background: #fff;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  -webkit-appearance: none;
}

.field input:focus,
.input-pw input:focus {
  border-color: #111;
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.06);
}

.field input::placeholder,
.input-pw input::placeholder {
  color: #c8c8c8;
}

.field input:disabled,
.input-pw input:disabled {
  background: #f3f3f3;
  cursor: not-allowed;
  color: #aaa;
}

.input-pw {
  position: relative;
}

.input-pw input {
  padding-right: 42px;
}

.toggle-pw {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #bbb;
  padding: 4px;
  display: flex;
  align-items: center;
  line-height: 1;
  transition: color 0.15s;
}

.toggle-pw:hover {
  color: #555;
}

/* ─── Submit ─── */
.btn-submit {
  width: 100%;
  height: 42px;
  background: #111;
  color: #fff;
  border: none;
  border-radius: 7px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: opacity 0.15s;
  margin-top: 4px;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.82;
}

.btn-submit:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.spinner {
  width: 15px;
  height: 15px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  flex-shrink: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ─── Note ─── */
.note {
  text-align: center;
  margin-top: 20px;
  font-size: 13px;
  color: #999;
}

.note a {
  color: #111;
  font-weight: 600;
  text-decoration: none;
}

.note a:hover {
  text-decoration: underline;
}

/* ─── Responsive ─── */

/* Tablet */
@media (max-width: 860px) {
  .page {
    grid-template-columns: 1fr 1fr;
  }
}

/* Mobile — sembunyikan panel kiri, tampilkan form saja */
@media (max-width: 640px) {
  .page {
    grid-template-columns: 1fr;
    min-height: 100dvh;
  }

  .panel-left {
    display: none;
  }

  .panel-right {
    justify-content: flex-start;
    padding-top: 72px;
  }

  .back-link {
    display: inline-flex;
  }

  .form-container {
    max-width: 100%;
  }
}
</style>
