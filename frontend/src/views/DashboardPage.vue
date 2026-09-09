<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const router = useRouter()

onMounted(() => auth.fetchUser())

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="dashboard">
    <header class="dash-header">
      <div class="dash-container">
        <span class="wordmark">DagangYuk</span>
        <div class="dash-right">
          <span class="dash-user">{{ auth.user?.name ?? auth.user?.email }}</span>
          <button class="btn-logout" @click="logout">Keluar</button>
        </div>
      </div>
    </header>

    <main class="dash-main">
      <div class="dash-container">
        <h1>Selamat datang, {{ auth.user?.name ?? 'Pengguna' }} 👋</h1>
        <p>Dashboard sedang dalam pengembangan.</p>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f9fafb;
}

.dash-header {
  background: #fff;
  border-bottom: 1px solid #e8e8e8;
  height: 58px;
  display: flex;
  align-items: center;
}

.dash-container {
  width: 100%;
  max-width: 1100px;
  margin-inline: auto;
  padding-inline: clamp(20px, 5vw, 48px);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.wordmark {
  font-size: 17px;
  font-weight: 700;
  color: #111;
  letter-spacing: -0.3px;
}

.dash-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.dash-user {
  font-size: 14px;
  color: #555;
}

.btn-logout {
  font-size: 13px;
  font-weight: 600;
  color: #111;
  background: none;
  border: 1.5px solid #e0e0e0;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  transition: border-color 0.15s;
}

.btn-logout:hover {
  border-color: #aaa;
}

.dash-main {
  padding: 64px 0;
}

.dash-main h1 {
  font-size: 24px;
  font-weight: 700;
  color: #111;
  margin-bottom: 8px;
}

.dash-main p {
  color: #777;
  font-size: 15px;
}
</style>
