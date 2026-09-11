<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { useUserStore, type User, type CreateUserPayload } from '@/stores/users'
import { useAuthStore } from '@/stores/auth'

const store = useUserStore()
const authStore = useAuthStore()

const showModal   = ref(false)
const formError   = ref('')
const formSuccess = ref('')
const isEditing   = ref(false)
const editId      = ref<number | null>(null)

const form = ref<CreateUserPayload>({
  name: '',
  email: '',
  password: '',
  role_id: 3, // Default to user (kasir)
})

function resetForm() {
  form.value = {
    name: '', email: '', password: '', role_id: 3,
  }
  formError.value = ''
  formSuccess.value = ''
  isEditing.value = false
  editId.value = null
}

function openModal(user?: User) {
  resetForm()
  if (user) {
    isEditing.value = true
    editId.value = user.id
    form.value.name = user.name
    form.value.email = user.email
    form.value.role_id = user.role_id
    // password left empty
  }
  showModal.value = true
}

async function submitForm() {
  formError.value = ''
  formSuccess.value = ''

  if (!form.value.name.trim()) { formError.value = 'Nama wajib diisi.'; return }
  if (!form.value.email.trim()) { formError.value = 'Email wajib diisi.'; return }
  if (!isEditing.value && (!form.value.password || form.value.password.length < 6)) { 
    formError.value = 'Password minimal 6 karakter.'; return 
  }

  // Inject tenant_id if created by admin
  const payload: any = { ...form.value }
  if (!authStore.isSuperAdmin) {
     payload.tenant_id = authStore.user?.tenant_id
  }

  try {
    if (isEditing.value && editId.value) {
       await store.updateUser(editId.value, payload)
       formSuccess.value = `Pengguna "${form.value.name}" berhasil diubah!`
    } else {
       await store.createUser(payload)
       formSuccess.value = `Pengguna "${form.value.name}" berhasil dibuat!`
    }
    
    setTimeout(() => {
      showModal.value = false
      formSuccess.value = ''
    }, 1200)
  } catch (e: any) {
    formError.value = e.message
  }
}

async function confirmDelete(user: User) {
  if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${user.name}"?`)) {
    try {
      await store.deleteUser(user.id)
    } catch (e: any) {
      alert(e.message)
    }
  }
}

function roleLabel(role: any) {
  if (!role) return '—'
  const name = typeof role === 'string' ? role : role.name
  return name === 'super_admin' ? 'Super Admin' : (name === 'admin' ? 'Admin' : 'Kasir')
}

onMounted(() => store.fetchUsers())
</script>

<template>
  <AppLayout>
    <template #title>Manajemen Pengguna</template>

    <div class="page-head">
      <div>
        <h1>Manajemen Pengguna</h1>
        <p>Kelola staf dan kasir untuk toko Anda.</p>
      </div>
      <button class="btn-primary" @click="openModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Pengguna
      </button>
    </div>

    <div v-if="store.loading" class="state-center">
      <span class="spin-ring"></span> Memuat data pengguna...
    </div>
    <div v-else-if="store.error" class="state-center err">{{ store.error }}</div>

    <div v-else class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="store.users.length === 0">
              <td colspan="4" class="empty-row">Belum ada pengguna lain.</td>
            </tr>
            <tr v-for="user in store.users" :key="user.id">
              <td class="fw">{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>
                <span class="badge" :class="{'badge-admin': user.role?.name === 'admin', 'badge-kasir': user.role?.name === 'user'}">
                  {{ roleLabel(user.role) }}
                </span>
              </td>
              <td>
                <div class="actions">
                  <button class="btn-link" @click="openModal(user)">Edit</button>
                  <button class="btn-link text-red" @click="confirmDelete(user)" v-if="user.id !== authStore.user?.id">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
          <div class="modal-card">
            <div class="modal-header">
              <h2>{{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h2>
              <button class="modal-close" @click="showModal = false">&times;</button>
            </div>
            <form @submit.prevent="submitForm" class="modal-body">
              <div class="form-grid">
                <div class="form-group full">
                  <label>Nama <span class="req">*</span></label>
                  <input v-model="form.name" type="text" placeholder="Nama Lengkap" />
                </div>
                <div class="form-group full">
                  <label>Email <span class="req">*</span></label>
                  <input v-model="form.email" type="email" placeholder="email@contoh.com" />
                </div>
                <div class="form-group">
                  <label>Role <span class="req">*</span></label>
                  <select v-model="form.role_id" class="form-select">
                    <option v-if="authStore.isSuperAdmin" :value="1">Super Admin</option>
                    <option v-if="authStore.isSuperAdmin || authStore.isAdmin" :value="2">Admin</option>
                    <option :value="3">Kasir</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Password <span v-if="!isEditing" class="req">*</span></label>
                  <input v-model="form.password" type="password" :placeholder="isEditing ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter'" />
                </div>
              </div>

              <div v-if="formError" class="alert alert-error mt-3">
                {{ formError }}
              </div>
              <div v-if="formSuccess" class="alert alert-success mt-3">
                {{ formSuccess }}
              </div>

              <div class="modal-actions">
                <button type="button" class="btn-outline" @click="showModal = false">Batal</button>
                <button type="submit" class="btn-primary" :disabled="store.saving">
                  <span v-if="store.saving" class="spin-ring sm"></span>
                  {{ store.saving ? 'Menyimpan...' : 'Simpan' }}
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
.page-head {
  display: flex; align-items: flex-start;
  justify-content: space-between; flex-wrap: wrap;
  gap: 12px; margin-bottom: 20px;
}
.page-head h1 { font-size: 22px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13px; color: var(--muted); margin-top: 3px; }

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
.fw    { font-weight: 700; }
.empty-row { text-align: center; color: var(--muted); padding: 40px 20px !important; font-size: 13px; }

.badge {
  display: inline-block; padding: 3px 12px; border-radius: 999px;
  font-size: 12px; font-weight: 600;
}
.badge-admin { background: #e0e7ff; color: #4338ca; }
.badge-kasir { background: #fef3c7; color: #d97706; }

.actions { display: flex; gap: 12px; }
.btn-link { background: none; border: none; font-size: 13px; color: var(--accent); cursor: pointer; font-weight: 500; }
.btn-link:hover { text-decoration: underline; }
.text-red { color: #ef4444; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; z-index: 999;
  background: rgba(0,0,0,.45); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
}
.modal-card {
  background: var(--white); border-radius: 16px;
  width: 100%; max-width: 500px; max-height: 90vh;
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
}
.modal-body { padding: 24px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label {
  font-size: 12px; font-weight: 600; color: var(--muted);
  text-transform: uppercase; letter-spacing: .3px;
}
.req { color: #ef4444; }
.form-group input, .form-select {
  padding: 9px 12px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 13.5px; color: var(--ink);
  background: var(--white); outline: none; width: 100%;
}
.form-group input:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }
.mt-3 { margin-top: 16px; }
.alert {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 14px; border-radius: 8px;
  font-size: 13px; font-weight: 500;
}
.alert-error   { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.modal-actions {
  display: flex; justify-content: flex-end; gap: 10px;
  padding-top: 16px; border-top: 1px solid var(--border); margin-top: 16px;
}

.modal-enter-active, .modal-leave-active { transition: opacity .2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-card { animation: modal-slide .25s ease; }
@keyframes modal-slide {
  from { transform: translateY(20px); opacity: 0; }
  to   { transform: translateY(0); opacity: 1; }
}
</style>
