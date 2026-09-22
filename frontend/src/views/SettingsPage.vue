<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

const auth = useAuthStore()

interface TenantSettings {
  name: string
  address: string
  phone: string
  receipt_footer: string
}

const form = ref<TenantSettings>({
  name: '',
  address: '',
  phone: '',
  receipt_footer: '',
})

const loading = ref(false)
const saving  = ref(false)
const error   = ref('')
const success = ref('')

async function loadSettings() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/settings')
    form.value = {
      name: res.data.name || '',
      address: res.data.address || '',
      phone: res.data.phone || '',
      receipt_footer: res.data.receipt_footer || '',
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat pengaturan.'
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  error.value = ''
  success.value = ''
  try {
    const res = await api.put('/settings', form.value)
    success.value = res.data.message || 'Pengaturan berhasil disimpan.'
    setTimeout(() => { success.value = '' }, 3000)
    
    // Update nama tenant di user store jika auth store mendukungnya (opsional)
    await auth.fetchUser()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal menyimpan pengaturan.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<template>
  <AppLayout>
    <template #title>Pengaturan Toko</template>

    <div class="page-head">
      <div>
        <h1>Pengaturan Toko</h1>
        <p>Sesuaikan informasi toko yang akan ditampilkan di struk dan aplikasi.</p>
      </div>
    </div>

    <div v-if="loading" class="state-center">
      <span class="spin-ring"></span> Memuat pengaturan...
    </div>
    
    <div v-else class="settings-card">
      <div v-if="error" class="alert error">{{ error }}</div>
      <div v-if="success" class="alert success">{{ success }}</div>

      <form @submit.prevent="saveSettings" class="settings-form">
        <div class="field">
          <label for="name">Nama Toko *</label>
          <input id="name" v-model="form.name" type="text" required placeholder="Contoh: Kopi Senja" />
        </div>

        <div class="field">
          <label for="phone">Nomor Telepon</label>
          <input id="phone" v-model="form.phone" type="tel" placeholder="Contoh: 08123456789" />
        </div>

        <div class="field">
          <label for="address">Alamat Toko</label>
          <textarea id="address" v-model="form.address" rows="3" placeholder="Alamat lengkap toko"></textarea>
        </div>

        <div class="field">
          <label for="footer">Pesan Struk (Footer)</label>
          <input id="footer" v-model="form.receipt_footer" type="text" placeholder="Contoh: Terima kasih atas kunjungan Anda!" />
          <span class="field-hint">Teks ini akan muncul di bagian bawah struk yang dicetak.</span>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="saving">
            <span v-if="saving" class="spin-ring btn-spin"></span>
            {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-head {
  margin-bottom: 24px;
}
.page-head h1 { font-size: 22px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13.5px; color: var(--muted); margin-top: 4px; }

.settings-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px;
  max-width: 600px;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.field label { font-size: 13px; font-weight: 600; color: var(--ink); }
.field input, .field textarea {
  width: 100%; padding: 10px 14px;
  border: 1px solid var(--border); border-radius: 8px;
  font-size: 14px; color: var(--ink); background: var(--surface);
  outline: none; transition: border-color 0.15s;
  font-family: inherit;
}
.field input:focus, .field textarea:focus {
  border-color: var(--accent); background: var(--white);
}
.field-hint { font-size: 12px; color: var(--muted); }

.form-actions {
  margin-top: 10px;
  padding-top: 16px;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
}

.btn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 10px 20px; border-radius: 8px; font-size: 14px;
  font-weight: 600; cursor: pointer; transition: background 0.15s;
  display: flex; align-items: center; gap: 8px;
}
.btn-primary:hover:not(:disabled) { background: var(--accent-dark); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.alert {
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 500;
  margin-bottom: 16px;
}
.alert.error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
.alert.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }

.state-center { display: flex; align-items: center; gap: 10px; padding: 48px; color: var(--muted); }
.spin-ring {
  display: inline-block; width: 20px; height: 20px;
  border: 2.5px solid var(--border); border-top-color: var(--accent);
  border-radius: 50%; animation: spin .7s linear infinite;
}
.btn-spin { width: 16px; height: 16px; border-width: 2px; border-color: rgba(255,255,255,0.3); border-top-color: #fff; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
