<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/utils/axios'

interface ActivityLog {
  id: number
  user_id: number | null
  action: string
  description: string
  ip_address: string | null
  created_at: string
  user?: {
    id: number
    name: string
    email: string
  }
}

const logs = ref<ActivityLog[]>([])
const loading = ref(false)
const error = ref('')

async function fetchLogs() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/activity-logs')
    logs.value = res.data.data ?? res.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'Gagal memuat log aktivitas.'
  } finally {
    loading.value = false
  }
}

function fmtDate(d: string) {
  return new Date(d).toLocaleString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

onMounted(() => {
  fetchLogs()
})
</script>

<template>
  <AppLayout>
    <template #title>Log Aktivitas</template>

    <div class="page-head">
      <div>
        <h1>Log Aktivitas Sistem</h1>
        <p>Pantau seluruh aktivitas penting dalam ekosistem platform.</p>
      </div>
      <button class="btn-refresh" @click="fetchLogs" :disabled="loading">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :style="loading ? 'animation:spin .7s linear infinite' : ''">
          <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
          <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
        </svg>
        Muat Ulang
      </button>
    </div>

    <div v-if="error" class="alert-err">{{ error }}</div>

    <div class="tcard">
      <div v-if="loading" class="empty-state">Memuat data...</div>
      <div v-else-if="logs.length === 0" class="empty-state">Belum ada log aktivitas tercatat.</div>
      <div class="table-wrap" v-else>
        <table>
          <thead>
            <tr>
              <th>Waktu</th>
              <th>User</th>
              <th>Aksi</th>
              <th>Deskripsi</th>
              <th>IP Address</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id">
              <td class="muted">{{ fmtDate(log.created_at) }}</td>
              <td>
                <div class="user-cell">
                  <span class="fw">{{ log.user?.name ?? 'System' }}</span>
                  <span class="muted-sm">{{ log.user?.email ?? '—' }}</span>
                </div>
              </td>
              <td>
                <span class="action-badge" :class="log.action.toLowerCase()">{{ log.action }}</span>
              </td>
              <td>{{ log.description }}</td>
              <td class="mono">{{ log.ip_address ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
.page-head h1 { font-size: 20px; font-weight: 800; letter-spacing: -.5px; color: var(--ink); }
.page-head p  { font-size: 13.5px; color: var(--muted); margin-top: 4px; }

.btn-refresh {
  display: flex; align-items: center; gap: 6px;
  background: var(--white); border: 1.5px solid var(--border); color: var(--ink);
  padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all .15s;
}
.btn-refresh:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
.btn-refresh:disabled { opacity: .5; cursor: not-allowed; }

.alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 13px; padding: 10px 14px; border-radius: 7px; margin-bottom: 16px; }

.tcard { background: var(--white); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.empty-state { text-align: center; padding: 52px; color: #d1d5db; font-size: 14px; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--surface); }
th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); padding: 12px 18px; border-bottom: 1px solid var(--border); }
td { padding: 14px 18px; border-bottom: 1px solid var(--border); font-size: 13.5px; color: var(--ink); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface); }

.user-cell { display: flex; flex-direction: column; gap: 2px; }
.fw { font-weight: 600; }
.muted { color: var(--muted); font-size: 12.5px; }
.muted-sm { color: var(--muted); font-size: 12px; }
.mono { font-family: monospace; font-size: 12.5px; color: var(--muted); }

.action-badge {
  display: inline-block; padding: 3px 10px; border-radius: 6px;
  font-size: 11.5px; font-weight: 700; background: var(--surface); color: var(--ink);
  border: 1px solid var(--border);
}
.action-badge[class*="delete"] { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
.action-badge[class*="create"] { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
.action-badge[class*="update"] { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
.action-badge[class*="login"]  { background: #fef3c7; border-color: #fde68a; color: #d97706; }

@keyframes spin { to { transform: rotate(360deg); } }
</style>
