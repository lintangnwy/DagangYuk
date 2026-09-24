<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

interface Warehouse {
  id: number
  name: string
  is_active: boolean
}

interface Branch {
  id: number
  name: string
  address: string | null
  phone: string | null
  is_active: boolean
  warehouses: Warehouse[]
}

const branches = ref<Branch[]>([])
const loading  = ref(true)
const error    = ref('')

// Modal
const modal    = ref(false)
const editing  = ref<Branch | null>(null)
const saving   = ref(false)
const saveErr  = ref('')

const form = ref({ name: '', address: '', phone: '', is_active: true })

async function load() {
  loading.value = true; error.value = ''
  try {
    const res = await api.get('/branches')
    branches.value = res.data
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Gagal memuat data cabang.'
  } finally { loading.value = false }
}

function openCreate() {
  editing.value = null
  form.value = { name: '', address: '', phone: '', is_active: true }
  saveErr.value = ''
  modal.value = true
}

function openEdit(b: Branch) {
  editing.value = b
  form.value = { name: b.name, address: b.address ?? '', phone: b.phone ?? '', is_active: b.is_active }
  saveErr.value = ''
  modal.value = true
}

function closeModal() { modal.value = false }

async function save() {
  if (!form.value.name.trim()) { saveErr.value = 'Nama cabang wajib diisi.'; return }
  saving.value = true; saveErr.value = ''
  try {
    if (editing.value) {
      await api.put(`/branches/${editing.value.id}`, form.value)
    } else {
      await api.post('/branches', form.value)
    }
    closeModal()
    await load()
  } catch (e: any) {
    saveErr.value = e.response?.data?.message ?? 'Gagal menyimpan data.'
  } finally { saving.value = false }
}

async function toggleActive(b: Branch) {
  try {
    await api.put(`/branches/${b.id}`, { is_active: !b.is_active })
    b.is_active = !b.is_active
  } catch {}
}

async function destroy(b: Branch) {
  if (!confirm(`Hapus cabang "${b.name}"? Semua data terkait akan terlepas.`)) return
  try {
    await api.delete(`/branches/${b.id}`)
    branches.value = branches.value.filter(x => x.id !== b.id)
  } catch (e: any) {
    alert(e.response?.data?.message ?? 'Gagal menghapus.')
  }
}

onMounted(load)
</script>

<template>
  <AppLayout>
    <template #title>Manajemen Cabang</template>

    <div class="page-head">
      <div>
        <h1>Cabang Toko</h1>
        <p>Kelola lokasi fisik toko Anda</p>
      </div>
      <button class="btn-primary" @click="openCreate">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Cabang
      </button>
    </div>

    <div v-if="error" class="alert-err">{{ error }}</div>

    <div v-if="loading" class="empty-state">Memuat data cabang...</div>

    <div v-else-if="!branches.length" class="empty-card">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#d1d5db"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <p>Belum ada cabang.<br>Tambahkan cabang pertama Anda.</p>
    </div>

    <div v-else class="branch-grid">
      <div v-for="branch in branches" :key="branch.id" class="branch-card" :class="{ inactive: !branch.is_active }">
        <div class="branch-card-header">
          <div class="branch-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </div>
          <div class="branch-status-toggle" @click="toggleActive(branch)" :title="branch.is_active ? 'Nonaktifkan' : 'Aktifkan'">
            <div class="toggle" :class="{ on: branch.is_active }">
              <div class="toggle-thumb"></div>
            </div>
            <span>{{ branch.is_active ? 'Aktif' : 'Nonaktif' }}</span>
          </div>
        </div>

        <h3 class="branch-name">{{ branch.name }}</h3>
        <p v-if="branch.address" class="branch-detail">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          {{ branch.address }}
        </p>
        <p v-if="branch.phone" class="branch-detail">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.69A2 2 0 012 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14h0v2.92z"/></svg>
          {{ branch.phone }}
        </p>

        <div class="warehouse-chips">
          <div v-if="!branch.warehouses?.length" class="chip chip-empty">Belum ada gudang</div>
          <div v-for="wh in branch.warehouses" :key="wh.id" class="chip">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            {{ wh.name }}
          </div>
        </div>

        <div class="branch-actions">
          <button class="btn-edit" @click="openEdit(branch)">Edit</button>
          <button class="btn-del" @click="destroy(branch)">Hapus</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="modal" class="modal-backdrop" @click.self="closeModal">
          <div class="modal-box">
            <div class="modal-header">
              <div class="modal-title">{{ editing ? 'Edit Cabang' : 'Tambah Cabang Baru' }}</div>
              <button class="modal-close" @click="closeModal">✕</button>
            </div>
            <div class="modal-body">
              <div class="field">
                <label>Nama Cabang <span class="req">*</span></label>
                <input v-model="form.name" placeholder="Contoh: Cabang Senen" />
              </div>
              <div class="field">
                <label>Alamat</label>
                <textarea v-model="form.address" placeholder="Jl. Contoh No. 123, Jakarta" rows="2" />
              </div>
              <div class="field">
                <label>Nomor Telepon</label>
                <input v-model="form.phone" placeholder="081234567890" />
              </div>

              <div v-if="saveErr" class="alert-err-sm">{{ saveErr }}</div>

              <div class="modal-actions">
                <button class="btn-cancel" @click="closeModal" :disabled="saving">Batal</button>
                <button class="btn-save" @click="save" :disabled="saving">
                  {{ saving ? 'Menyimpan...' : (editing ? 'Simpan Perubahan' : 'Tambah Cabang') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </AppLayout>
</template>

<style scoped>
.page-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.page-head h1 { font-size:20px; font-weight:800; letter-spacing:-.5px; color:var(--ink); }
.page-head p  { font-size:13px; color:var(--muted); margin-top:2px; }
.btn-primary { display:flex; align-items:center; gap:6px; background:var(--accent); color:#fff; border:none; padding:10px 18px; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer; transition:background .15s; }
.btn-primary:hover { background:var(--accent-dark); }
.alert-err { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; font-size:13px; padding:10px 14px; border-radius:7px; margin-bottom:16px; }
.empty-state { text-align:center; padding:52px; color:#d1d5db; font-size:14px; }
.empty-card { text-align:center; padding:60px 24px; color:#9ca3af; }
.empty-card p { margin-top:16px; font-size:14px; line-height:1.6; }

.branch-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px; }

.branch-card {
  background:var(--white); border:1px solid var(--border);
  border-radius:12px; padding:20px; transition:box-shadow .15s;
}
.branch-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
.branch-card.inactive { opacity:.6; }

.branch-card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.branch-icon { width:40px; height:40px; background:var(--accent-bg); border-radius:10px; display:flex; align-items:center; justify-content:center; color:var(--accent); }

.branch-status-toggle { display:flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; color:var(--muted); }
.toggle { width:32px; height:18px; border-radius:999px; background:#e5e7eb; position:relative; transition:background .2s; }
.toggle.on { background:var(--accent); }
.toggle-thumb { position:absolute; top:2px; left:2px; width:14px; height:14px; border-radius:50%; background:#fff; transition:transform .2s; }
.toggle.on .toggle-thumb { transform:translateX(14px); }

.branch-name { font-size:16px; font-weight:700; color:var(--ink); margin-bottom:8px; }
.branch-detail { display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--muted); margin-bottom:4px; }

.warehouse-chips { display:flex; flex-wrap:wrap; gap:6px; margin:12px 0; }
.chip { display:flex; align-items:center; gap:4px; background:var(--surface); border:1px solid var(--border); color:var(--muted); font-size:11.5px; padding:3px 8px; border-radius:6px; }
.chip-empty { font-style:italic; }

.branch-actions { display:flex; gap:8px; margin-top:14px; padding-top:14px; border-top:1px solid var(--border); }
.btn-edit { flex:1; height:34px; background:var(--accent-bg); color:var(--accent); border:1px solid var(--accent-ring,#c7d2fe); border-radius:7px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }
.btn-edit:hover { background:var(--accent); color:#fff; }
.btn-del  { height:34px; padding:0 14px; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }
.btn-del:hover { background:#dc2626; color:#fff; }

/* Modal */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center; z-index:9999; padding:16px; }
.modal-box { background:var(--white); border-radius:14px; width:100%; max-width:460px; box-shadow:0 24px 60px rgba(0,0,0,.18); overflow:hidden; }
.modal-header { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid var(--border); }
.modal-title { font-size:15px; font-weight:700; color:var(--ink); }
.modal-close { background:none; border:none; color:var(--muted); font-size:16px; cursor:pointer; padding:4px 8px; border-radius:6px; }
.modal-close:hover { background:var(--surface); }
.modal-body { padding:22px; display:flex; flex-direction:column; gap:14px; }
.field { display:flex; flex-direction:column; gap:6px; }
.field label { font-size:13.5px; font-weight:600; color:var(--ink); }
.req { color:#dc2626; }
.field input, .field textarea {
  width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px;
  font-size:13.5px; color:var(--ink); background:var(--white); outline:none;
  font-family:inherit; transition:border-color .15s; box-sizing:border-box;
}
.field input:focus, .field textarea:focus { border-color:var(--accent); }
.field textarea { resize:vertical; }
.alert-err-sm { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; font-size:12.5px; padding:8px 12px; border-radius:7px; }
.modal-actions { display:flex; gap:10px; }
.btn-cancel { flex:1; height:40px; background:none; border:1.5px solid var(--border); color:var(--muted); border-radius:8px; font-size:14px; cursor:pointer; }
.btn-cancel:hover { border-color:var(--ink); color:var(--ink); }
.btn-save { flex:2; height:40px; background:var(--accent); border:none; color:#fff; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-save:hover { background:var(--accent-dark); }
.btn-save:disabled { opacity:.6; cursor:not-allowed; }

.modal-fade-enter-active { transition:opacity .2s ease; }
.modal-fade-leave-active  { transition:opacity .15s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity:0; }
</style>
