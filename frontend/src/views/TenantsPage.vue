<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { useTenantStore, type CreateTenantPayload } from '@/stores/tenant'

const store = useTenantStore()

const showModal   = ref(false)
const formError   = ref('')
const formSuccess = ref('')

const form = ref<CreateTenantPayload>({
  name: '',
  address: '',
  phone: '',
  admin_name: '',
  admin_email: '',
  admin_password: '',
})

function resetForm() {
  form.value = {
    name: '', address: '', phone: '',
    admin_name: '', admin_email: '', admin_password: '',
  }
  formError.value = ''
  formSuccess.value = ''
}

function openModal() {
  resetForm()
  showModal.value = true
}

async function submitForm() {
  formError.value = ''
  formSuccess.value = ''

  // Validasi sederhana di frontend
  if (!form.value.name.trim()) { formError.value = 'Nama tenant wajib diisi.'; return }
  if (!form.value.admin_name.trim()) { formError.value = 'Nama admin wajib diisi.'; return }
  if (!form.value.admin_email.trim()) { formError.value = 'Email admin wajib diisi.'; return }
  if (form.value.admin_password.length < 6) { formError.value = 'Password minimal 6 karakter.'; return }

  try {
    await store.createTenantWithAdmin(form.value)
    formSuccess.value = `Tenant "${form.value.name}" berhasil dibuat!`
    setTimeout(() => {
      showModal.value = false
      formSuccess.value = ''
    }, 1200)
  } catch (e: any) {
    formError.value = e.message
  }
}

async function toggleStatus(tenant: any) {
  try {
    await store.toggleTenantStatus(tenant)
  } catch (e: any) {
    alert(e.message)
  }
}

function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(() => store.fetchTenants())
</script>

<template>
  <AppLayout>
    <template #title>Manajemen Tenant</template>

    <!-- Header -->
    <div class="page-head">
      <div>
        <h1>Manajemen Tenant</h1>
        <p>Kelola semua tenant yang terdaftar di sistem DagangYuk.</p>
      </div>
      <button class="btn-primary" @click="openModal">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Tenant
      </button>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-pill">
        <span class="stat-num">{{ store.tenants.length }}</span>
        <span class="stat-label">Total Tenant</span>
      </div>
      <div class="stat-pill active">
        <span class="stat-num">{{ store.tenants.filter(t => t.is_active).length }}</span>
        <span class="stat-label">Aktif</span>
      </div>
      <div class="stat-pill inactive">
        <span class="stat-num">{{ store.tenants.filter(t => !t.is_active).length }}</span>
        <span class="stat-label">Nonaktif</span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="state-center">
      <span class="spin-ring"></span> Memuat data tenant...
    </div>
    <div v-else-if="store.error" class="state-center err">{{ store.error }}</div>

    <!-- Table -->
    <div v-else class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Tenant</th>
              <th>Alamat</th>
              <th>Telepon</th>
              <th>Status</th>
              <th>Terdaftar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="store.tenants.length === 0">
              <td colspan="7" class="empty-row">
                Belum ada tenant. Klik "Tambah Tenant" untuk membuat yang pertama.
              </td>
            </tr>
            <tr v-for="(tenant, idx) in store.tenants" :key="tenant.id">
              <td class="mono">{{ idx + 1 }}</td>
              <td class="fw">{{ tenant.name }}</td>
              <td>{{ tenant.address || '—' }}</td>
              <td>{{ tenant.phone || '—' }}</td>
              <td>
                <span class="badge" :class="tenant.is_active ? 'badge-active' : 'badge-inactive'">
                  {{ tenant.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="muted">{{ fmtDate(tenant.created_at) }}</td>
              <td>
                <button
                  class="btn-sm"
                  :class="tenant.is_active ? 'btn-sm-danger' : 'btn-sm-success'"
                  @click="toggleStatus(tenant)"
                >
                  {{ tenant.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Overlay -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
          <div class="modal-card">
            <div class="modal-header">
              <h2>Tambah Tenant Baru</h2>
              <button class="modal-close" @click="showModal = false">&times;</button>
            </div>

            <form @submit.prevent="submitForm" class="modal-body">
              <!-- Tenant Section -->
              <div class="form-section">
                <h3 class="form-section-title">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                  </svg>
                  Info Tenant
                </h3>
                <div class="form-grid">
                  <div class="form-group full">
                    <label>Nama Tenant <span class="req">*</span></label>
                    <input v-model="form.name" type="text" placeholder="Cth: Warung Sejahtera" />
                  </div>
                  <div class="form-group">
                    <label>Alamat</label>
                    <input v-model="form.address" type="text" placeholder="Jl. Merdeka No. 10" />
                  </div>
                  <div class="form-group">
                    <label>No. Telepon</label>
                    <input v-model="form.phone" type="text" placeholder="081234567890" />
                  </div>
                </div>
              </div>

              <!-- Admin Section -->
              <div class="form-section">
                <h3 class="form-section-title">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                  Admin Tenant
                </h3>
                <p class="form-hint">Admin akan menjadi pengelola utama tenant ini.</p>
                <div class="form-grid">
                  <div class="form-group full">
                    <label>Nama Admin <span class="req">*</span></label>
                    <input v-model="form.admin_name" type="text" placeholder="Cth: Budi Santoso" />
                  </div>
                  <div class="form-group">
                    <label>Email <span class="req">*</span></label>
                    <input v-model="form.admin_email" type="email" placeholder="admin@tenant.com" />
                  </div>
                  <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <input v-model="form.admin_password" type="password" placeholder="Min. 6 karakter" />
                  </div>
                </div>
              </div>

              <!-- Alerts -->
              <div v-if="formError" class="alert alert-error">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/>
                  <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                {{ formError }}
              </div>
              <div v-if="formSuccess" class="alert alert-success">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                  <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                {{ formSuccess }}
              </div>

              <!-- Actions -->
              <div class="modal-actions">
                <button type="button" class="btn-outline" @click="showModal = false">Batal</button>
                <button type="submit" class="btn-primary" :disabled="store.saving">
                  <span v-if="store.saving" class="spin-ring sm"></span>
                  {{ store.saving ? 'Menyimpan...' : 'Buat Tenant & Admin' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

  </AppLayout>
</template>

<style scoped>
/* ── Page Head ── */
.page-head {
  display: flex; align-items: flex-start;
  justify-content: space-between; flex-wrap: wrap;
  gap: 12px; margin-bottom: 20px;
}
.page-head h1 { font-size: 22px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13px; color: var(--muted); margin-top: 3px; }

/* ── Stats ── */
.stats-row { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.stat-pill {
  display: flex; flex-direction: column; align-items: center;
  background: var(--white); border: 1px solid var(--border);
  border-radius: 12px; padding: 14px 28px; min-width: 120px;
  transition: box-shadow .15s;
}
.stat-pill:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
.stat-pill.active  { border-color: #86efac; background: #f0fdf4; }
.stat-pill.inactive { border-color: #fca5a5; background: #fef2f2; }
.stat-num   { font-size: 28px; font-weight: 800; color: var(--ink); letter-spacing: -.5px; }
.stat-label { font-size: 11.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }
.stat-pill.active .stat-num   { color: #16a34a; }
.stat-pill.inactive .stat-num { color: #ef4444; }

/* ── Buttons ── */
.btn-primary {
  display: flex; align-items: center; gap: 6px;
  background: var(--accent); color: #fff; border: none;
  padding: 9px 20px; border-radius: 8px; font-size: 13.5px;
  font-weight: 600; cursor: pointer; transition: background .15s;
}
.btn-primary:hover { background: var(--accent-dark); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }

.btn-outline {
  display: flex; align-items: center; gap: 6px;
  background: none; border: 1.5px solid var(--border);
  color: var(--muted); padding: 9px 20px; border-radius: 8px;
  font-size: 13px; cursor: pointer; transition: border-color .15s, color .15s;
}
.btn-outline:hover { border-color: var(--accent); color: var(--accent); }

.btn-sm {
  padding: 4px 12px; border-radius: 6px; font-size: 12px;
  font-weight: 600; border: none; cursor: pointer; transition: background .15s;
}
.btn-sm-danger  { background: #fef2f2; color: #ef4444; }
.btn-sm-danger:hover { background: #fee2e2; }
.btn-sm-success { background: #f0fdf4; color: #16a34a; }
.btn-sm-success:hover { background: #dcfce7; }

/* ── State ── */
.state-center {
  display: flex; align-items: center; gap: 10px;
  justify-content: center; padding: 64px; color: var(--muted);
}
.state-center.err { color: #ef4444; }
.spin-ring {
  display: inline-block; width: 20px; height: 20px;
  border: 2.5px solid var(--border); border-top-color: var(--accent);
  border-radius: 50%; animation: spin .7s linear infinite;
}
.spin-ring.sm { width: 14px; height: 14px; border-width: 2px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Card & Table ── */
.card {
  background: var(--white); border: 1px solid var(--border);
  border-radius: 12px; overflow: hidden;
}
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--surface); }
th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); padding: 10px 20px; border-bottom: 1px solid var(--border); }
td { padding: 12px 20px; border-bottom: 1px solid var(--border); font-size: 13.5px; color: var(--ink); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface); }
.mono  { font-family: monospace; font-size: 12px; color: var(--muted); }
.fw    { font-weight: 700; }
.muted { color: var(--muted); font-size: 12px; }
.empty-row { text-align: center; color: var(--muted); padding: 40px 20px !important; font-size: 13px; }

.badge {
  display: inline-block; padding: 3px 12px; border-radius: 999px;
  font-size: 12px; font-weight: 600;
}
.badge-active   { background: #dcfce7; color: #16a34a; }
.badge-inactive { background: #fee2e2; color: #ef4444; }

/* ── Modal ── */
.modal-overlay {
  position: fixed; inset: 0; z-index: 999;
  background: rgba(0,0,0,.45); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
}
.modal-card {
  background: var(--white); border-radius: 16px;
  width: 100%; max-width: 560px; max-height: 90vh;
  overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 24px; border-bottom: 1px solid var(--border);
}
.modal-header h2 { font-size: 17px; font-weight: 700; color: var(--ink); }
.modal-close {
  background: none; border: none; font-size: 22px;
  color: var(--muted); cursor: pointer; padding: 0 4px;
  transition: color .15s;
}
.modal-close:hover { color: var(--ink); }

.modal-body { padding: 24px; }

.form-section { margin-bottom: 20px; }
.form-section-title {
  display: flex; align-items: center; gap: 8px;
  font-size: 14px; font-weight: 700; color: var(--ink);
  margin-bottom: 12px; padding-bottom: 8px;
  border-bottom: 1px solid var(--border);
}
.form-hint { font-size: 12px; color: var(--muted); margin-bottom: 12px; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label {
  font-size: 12px; font-weight: 600; color: var(--muted);
  text-transform: uppercase; letter-spacing: .3px;
}
.req { color: #ef4444; }
.form-group input {
  padding: 9px 12px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 13.5px; color: var(--ink);
  background: var(--white); transition: border-color .15s;
  outline: none;
}
.form-group input::placeholder { color: #d1d5db; }
.form-group input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }

/* Alerts */
.alert {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 14px; border-radius: 8px;
  font-size: 13px; font-weight: 500; margin-bottom: 16px;
}
.alert-error   { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

.modal-actions {
  display: flex; justify-content: flex-end; gap: 10px;
  padding-top: 8px; border-top: 1px solid var(--border); margin-top: 8px;
}

/* Transition */
.modal-enter-active, .modal-leave-active { transition: opacity .2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-card { animation: modal-slide .25s ease; }
@keyframes modal-slide {
  from { transform: translateY(20px); opacity: 0; }
  to   { transform: translateY(0); opacity: 1; }
}

/* Responsive */
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
  .stats-row { flex-direction: column; }
  th:nth-child(3), td:nth-child(3),
  th:nth-child(4), td:nth-child(4) { display: none; }
}
</style>
