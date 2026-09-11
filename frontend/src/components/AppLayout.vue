<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ThemeSwitcher from '@/components/ThemeSwitcher.vue'

const auth   = useAuthStore()
const route  = useRoute()
const router = useRouter()

const props = withDefaults(defineProps<{
  fullHeight?: boolean
}>(), {
  fullHeight: false,
})

const sidebarOpen = ref(true)

const navGroups = computed(() => {
  const groups = [
    {
      label: 'Utama',
      items: [
        { label: 'Dashboard', icon: 'dashboard', to: '/dashboard' },
      ],
    }
  ]
  if (!auth.isSuperAdmin) {
    groups.push(
      {
        label: 'Transaksi',
        items: [
          { label: 'Kasir',           icon: 'pos',     to: '/pos' },
          { label: 'Riwayat Pesanan', icon: 'history', to: '/orders' },
        ],
      },
      {
        label: 'Inventaris',
        items: [
          { label: 'Produk',    icon: 'box',      to: '/products' },
          { label: 'Kategori',  icon: 'category', to: '/categories' },
          { label: 'Stok & Adj.', icon: 'stock',    to: '/stock' },
        ],
      }
    )
  }

  if (auth.isAdmin || auth.isSuperAdmin) {
    groups.push({
      label: 'Manajemen',
      items: [
        ...(auth.isSuperAdmin ? [{ label: 'Tenants', icon: 'users', to: '/tenants' }] : []),
        { label: 'Pengguna', icon: 'users', to: '/users' },
      ],
    })
  }

  return groups
})

async function logout() {
  await auth.logout()
  router.push('/login')
}

function initial(name: string) {
  return name?.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() || 'U'
}
</script>

<template>
  <div class="app-shell" :class="{ 'sidebar-collapsed': !sidebarOpen }">

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
      <!-- Brand -->
      <div class="sb-brand">
        <div class="brand-mark">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
        </div>
        <span class="brand-name">DagangYuk</span>
        <button class="toggle-btn" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Nav groups -->
      <nav class="sb-nav">
        <div v-for="group in navGroups" :key="group.label" class="nav-group">
          <span class="group-label">{{ group.label }}</span>
          <RouterLink
            v-for="item in group.items"
            :key="item.to"
            :to="item.to"
            class="nav-item"
            :class="{ active: route.path.startsWith(item.to) }"
          >
            <!-- Icons -->
            <span class="nav-icon">
              <svg v-if="item.icon === 'dashboard'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
              </svg>
              <svg v-else-if="item.icon === 'pos'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 001.99 1.61h9.72a2 2 0 001.99-1.61L23 6H6"/>
              </svg>
              <svg v-else-if="item.icon === 'history'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="12 8 12 12 14 14"/>
                <path d="M3.05 11a9 9 0 1 0 .5-4.5"/><polyline points="3 3 3 7 7 7"/>
              </svg>
              <svg v-else-if="item.icon === 'box'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
              </svg>
              <svg v-else-if="item.icon === 'category'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
              <svg v-else-if="item.icon === 'stock'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                <path d="M3.27 6.96L12 12.01l8.73-5.05"/>
                <path d="M12 22.08V12"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else-if="item.icon === 'users'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
              </svg>
            </span>
            <span class="nav-label">{{ item.label }}</span>
          </RouterLink>
        </div>
      </nav>

      <!-- User info -->
      <div class="sb-user">
        <div class="user-av">{{ initial(auth.user?.name ?? 'U') }}</div>
        <div class="user-info">
          <p class="user-name">{{ auth.user?.name }}</p>
          <p class="user-role">{{ auth.user?.role ?? 'Pengguna' }}</p>
        </div>
        <button class="logout-btn" @click="logout" title="Keluar">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
            <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </div>
    </aside>

    <!-- ── Main ── -->
    <div class="main-wrap">
      <!-- Topbar -->
      <header class="topbar">
        <h1 class="page-title">
          <slot name="title">DagangYuk</slot>
        </h1>
        <div class="topbar-right">
          <ThemeSwitcher />
          <button class="icon-btn" aria-label="Notifikasi">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
          </button>
        </div>
      </header>

      <!-- Content -->
      <main class="page-content" :class="{ 'page-content--full': fullHeight }">
        <slot />
      </main>
    </div>

  </div>
</template>

<style scoped>
/* ── Shell ── */
.app-shell {
  display: grid;
  grid-template-columns: 220px 1fr;
  min-height: 100dvh;
  background: var(--surface, #f9fafb);
  transition: grid-template-columns 0.25s ease;
}

.app-shell.sidebar-collapsed {
  grid-template-columns: 60px 1fr;
}

/* ── Sidebar ── */
.sidebar {
  background: var(--white, #fff);
  border-right: 1px solid var(--border, #e5e7eb);
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100dvh;
  overflow: hidden;
  transition: width 0.25s ease;
}

/* Brand */
.sb-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 14px;
  border-bottom: 1px solid var(--border, #e5e7eb);
  min-height: 58px;
}

.brand-mark {
  width: 32px;
  height: 32px;
  background: var(--accent, #2563eb);
  color: #fff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background 0.3s;
}

.brand-name {
  font-size: 15px;
  font-weight: 800;
  letter-spacing: -0.3px;
  color: var(--ink, #111827);
  white-space: nowrap;
  overflow: hidden;
  flex: 1;
}

.toggle-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--muted, #6b7280);
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  flex-shrink: 0;
  transition: background 0.15s;
}
.toggle-btn:hover { background: var(--surface, #f9fafb); }

/* Nav */
.sb-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.nav-group { display: flex; flex-direction: column; gap: 2px; }

.group-label {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--subtle, #9ca3af);
  padding: 0 8px;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 8px;
  text-decoration: none;
  color: var(--muted, #6b7280);
  font-size: 13.5px;
  font-weight: 500;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
  overflow: hidden;
}

.nav-item:hover { background: var(--surface, #f9fafb); color: var(--ink, #111827); }

.nav-item.active {
  background: var(--accent-bg, #eff6ff);
  color: var(--accent, #2563eb);
  font-weight: 700;
}

.nav-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  width: 17px;
}

.nav-label { overflow: hidden; }

/* User footer */
.sb-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-top: 1px solid var(--border, #e5e7eb);
  overflow: hidden;
}

.user-av {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--accent, #2563eb);
  color: #fff;
  font-size: 12px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background 0.3s;
}

.user-info { flex: 1; min-width: 0; overflow: hidden; }
.user-name { font-size: 13px; font-weight: 600; color: var(--ink, #111827); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: 11px; color: var(--muted, #6b7280); text-transform: capitalize; }

.logout-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--muted, #6b7280);
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  flex-shrink: 0;
  transition: color 0.15s, background 0.15s;
}
.logout-btn:hover { color: #ef4444; background: #fef2f2; }

/* Collapsed sidebar */
.sidebar-collapsed .brand-name,
.sidebar-collapsed .nav-label,
.sidebar-collapsed .group-label,
.sidebar-collapsed .user-info {
  display: none;
}

.sidebar-collapsed .sb-nav { padding: 12px 4px; }
.sidebar-collapsed .nav-item { justify-content: center; padding: 8px; }
.sidebar-collapsed .sb-user { padding: 12px 8px; justify-content: center; }
.sidebar-collapsed .logout-btn { display: none; }

/* ── Main ── */
.main-wrap {
  display: flex;
  flex-direction: column;
  min-height: 100dvh;
  min-width: 0;
  overflow: hidden;
}

/* Topbar */
.topbar {
  height: 58px;
  background: var(--white, #fff);
  border-bottom: 1px solid var(--border, #e5e7eb);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  position: sticky;
  top: 0;
  z-index: 30;
}

.page-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--ink, #111827);
  letter-spacing: -0.3px;
}

.topbar-right { display: flex; align-items: center; gap: 8px; }

.icon-btn {
  width: 36px;
  height: 36px;
  background: none;
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--muted, #6b7280);
  transition: background 0.15s;
}
.icon-btn:hover { background: var(--surface, #f9fafb); }

/* Content */
.page-content {
  flex: 1;
  padding: clamp(20px, 3vw, 32px) clamp(16px, 4vw, 40px);
}

.page-content--full {
  padding: 0;
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ── Responsive ── */
@media (max-width: 768px) {
  .app-shell { grid-template-columns: 0 1fr; }
  .sidebar { position: fixed; z-index: 100; left: -220px; transition: left 0.25s ease; width: 220px; }
  .app-shell:not(.sidebar-collapsed) .sidebar { left: 0; box-shadow: 4px 0 24px rgba(0,0,0,0.12); }
}
</style>
