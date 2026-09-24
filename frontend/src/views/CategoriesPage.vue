<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

interface Category {
  id: number
  name: string
  description?: string
  created_at: string
}

const categories = ref<Category[]>([])
const loading = ref(false)
const error = ref('')

const showFormModal = ref(false)
const editingCategory = ref<Category | null>(null)
const categoryName = ref('')
const categoryDesc = ref('')
const submitting = ref(false)
const formError = ref('')

onMounted(async () => {
  await fetchCategories()
})

async function fetchCategories() {
  loading.value = true
  try {
    const res = await api.get('/categories')
    categories.value = res.data
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat kategori.'
  } finally {
    loading.value = false
  }
}

function openNewCategory() {
  editingCategory.value = null
  categoryName.value = ''
  categoryDesc.value = ''
  formError.value = ''
  showFormModal.value = true
}

function openEditCategory(cat: Category) {
  editingCategory.value = cat
  categoryName.value = cat.name
  categoryDesc.value = cat.description || ''
  formError.value = ''
  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
}

async function saveCategory() {
  if (!categoryName.value.trim()) {
    formError.value = 'Nama kategori wajib diisi.'
    return
  }
  
  submitting.value = true
  formError.value = ''
  try {
    if (editingCategory.value) {
      await api.put(`/categories/${editingCategory.value.id}`, {
        name: categoryName.value,
        description: categoryDesc.value,
      })
    } else {
      await api.post('/categories', {
        name: categoryName.value,
        description: categoryDesc.value,
      })
    }
    await fetchCategories()
    closeFormModal()
  } catch (e: any) {
    formError.value = e.response?.data?.message || 'Gagal menyimpan kategori.'
  } finally {
    submitting.value = false
  }
}

async function deleteCategory(id: number) {
  if (!confirm('Hapus kategori ini?')) return
  
  try {
    await api.delete(`/categories/${id}`)
    await fetchCategories()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menghapus kategori.')
  }
}

function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
  <AppLayout>
    <template #title>Kategori Produk</template>
    
    <div class="page-head">
      <div>
        <h1>Kategori Produk</h1>
        <p>Kelola pengelompokan produk toko Anda.</p>
      </div>
      <button class="btn-primary" @click="openNewCategory">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Kategori
      </button>
    </div>

    <div v-if="error" class="alert-err">{{ error }}</div>

    <div v-if="loading" class="state-center">
      <span class="spin-ring"></span> Memuat kategori...
    </div>
    
    <div v-else-if="categories.length === 0" class="empty-state tcard">
      <p>Belum ada kategori. Klik "Tambah Kategori" untuk membuat.</p>
    </div>

    <div v-else class="card-list">
      <div v-for="cat in categories" :key="cat.id" class="card">
        <div class="card-hd">
          <div class="cat-info">
            <div class="cat-icon">{{ cat.name.charAt(0) }}</div>
            <div class="cat-title">
              <strong>{{ cat.name }}</strong>
              <small>Dibuat pada {{ fmtDate(cat.created_at) }}</small>
            </div>
          </div>
        </div>
        <p class="cat-desc" v-if="cat.description">{{ cat.description }}</p>
        <p class="cat-desc empty-desc" v-else>Tidak ada deskripsi.</p>
        <div class="card-ft">
          <button class="btn-sm btn-edit" @click="openEditCategory(cat)">Edit</button>
          <button class="btn-sm btn-danger" @click="deleteCategory(cat.id)">Hapus</button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showFormModal" class="modal-backdrop" @click.self="closeFormModal">
          <div class="modal-box">
            <div class="modal-header">
              <div class="modal-title">
                {{ editingCategory ? 'Edit Kategori' : 'Tambah Kategori' }}
              </div>
              <button class="modal-close" @click="closeFormModal">✕</button>
            </div>
            
            <div class="modal-body">
              <div class="field">
                <label>Nama Kategori <span class="req">*</span></label>
                <input v-model="categoryName" type="text" placeholder="Contoh: Minuman Dingin" autofocus />
              </div>
              
              <div class="field">
                <label>Deskripsi (Opsional)</label>
                <textarea v-model="categoryDesc" rows="3" placeholder="Deskripsi singkat..."></textarea>
              </div>

              <div v-if="formError" class="alert-err-sm">{{ formError }}</div>

              <div class="modal-actions">
                <button class="btn-cancel" @click="closeFormModal" :disabled="submitting">Batal</button>
                <button class="btn-save" @click="saveCategory" :disabled="submitting">
                  {{ submitting ? 'Menyimpan...' : 'Simpan' }}
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
.page-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
.page-head h1 { font-size: 20px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13.5px; color: var(--muted); margin-top: 4px; }

.btn-primary {
  display: flex; align-items: center; gap: 6px;
  background: var(--accent); color: #fff; border: none;
  padding: 10px 18px; border-radius: 9px; font-size: 14px;
  font-weight: 600; cursor: pointer; transition: background .15s;
}
.btn-primary:hover { background: var(--accent-dark); }

.alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 13px; padding: 10px 14px; border-radius: 7px; margin-bottom: 16px; }

.state-center { display: flex; align-items: center; gap: 10px; justify-content: center; padding: 64px; color: var(--muted); }
.spin-ring { display: inline-block; width: 20px; height: 20px; border: 2.5px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.tcard { background: var(--white); border: 1px solid var(--border); border-radius: 12px; }
.empty-state { text-align: center; padding: 64px 20px; color: var(--muted); font-size: 14px; }

.card-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.card { background: var(--white); border: 1px solid var(--border); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; transition: box-shadow .15s; }
.card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }

.card-hd { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
.cat-info { display: flex; align-items: center; gap: 12px; }
.cat-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--accent-bg); color: var(--accent); font-size: 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cat-title { display: flex; flex-direction: column; gap: 2px; }
.cat-title strong { font-size: 15px; font-weight: 700; color: var(--ink); }
.cat-title small { font-size: 11.5px; color: var(--muted); }

.cat-desc { font-size: 13.5px; color: var(--ink); margin-bottom: 16px; flex: 1; }
.empty-desc { color: var(--muted); font-style: italic; }

.card-ft { display: flex; gap: 8px; border-top: 1px solid var(--border); padding-top: 14px; }
.btn-sm { flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; }
.btn-edit { background: var(--surface); color: var(--ink); border: 1px solid var(--border); }
.btn-edit:hover { border-color: var(--accent); color: var(--accent); }
.btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.btn-danger:hover { background: #fee2e2; }

/* Modal */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 16px; }
.modal-box { background: var(--white); border-radius: 14px; width: 100%; max-width: 440px; box-shadow: 0 24px 60px rgba(0,0,0,.18); overflow: hidden; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border); }
.modal-title { font-size: 15px; font-weight: 700; color: var(--ink); }
.modal-close { background: none; border: none; color: var(--muted); font-size: 16px; cursor: pointer; padding: 4px 8px; border-radius: 6px; }
.modal-close:hover { background: var(--surface); }

.modal-body { padding: 22px; display: flex; flex-direction: column; gap: 14px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13.5px; font-weight: 600; color: var(--ink); }
.req { color: #dc2626; }
.field input, .field textarea {
  width: 100%; padding: 10px 12px; border: 1px solid var(--border);
  border-radius: 8px; font-size: 13.5px; color: var(--ink);
  background: var(--white); outline: none; transition: border-color .15s;
  font-family: inherit; box-sizing: border-box;
}
.field textarea { resize: vertical; }
.field input:focus, .field textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }

.alert-err-sm { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 12.5px; padding: 8px 12px; border-radius: 7px; }

.modal-actions { display: flex; gap: 10px; margin-top: 8px; }
.btn-cancel { flex: 1; height: 40px; background: none; border: 1.5px solid var(--border); color: var(--muted); border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-cancel:hover { border-color: var(--ink); color: var(--ink); }
.btn-save { flex: 2; height: 40px; background: var(--accent); border: none; color: white; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-save:hover:not(:disabled) { background: var(--accent-dark); }
.btn-save:disabled { opacity: .6; cursor: not-allowed; }

.modal-fade-enter-active { transition: opacity .2s ease; }
.modal-fade-leave-active { transition: opacity .15s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>