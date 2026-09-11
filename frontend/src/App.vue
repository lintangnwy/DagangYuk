<script setup lang="ts">
import { useThemeStore } from '@/stores/theme'
// Apply theme on app load
useThemeStore()
</script>

<template>
  <RouterView />
</template>

<style>
/* ── Global reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html {
  font-size: 14px;
  -webkit-text-size-adjust: 100%;
}

body {
  font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
  font-size: 1rem;
  line-height: 1.6;
}

/* ── Global CSS tokens (default: blue) ── */
:root {
  --accent:      #2563eb;
  --accent-dark: #1d4ed8;
  --accent-bg:   #eff6ff;
  --accent-ring: #bfdbfe;

  --ink:     #111827;
  --muted:   #6b7280;
  --subtle:  #9ca3af;
  --border:  #e5e7eb;
  --surface: #f9fafb;
  --white:   #ffffff;
}

/* ── Global Modal (Teleport to body) ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 16px;
}

.modal-box {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 440px;
  max-height: 90dvh;
  overflow-y: auto;
  box-shadow: 0 32px 80px rgba(0, 0, 0, 0.2);
  position: relative;
}

.modal-box.pay-modal { max-width: 460px; }
.modal-box.success-modal { max-width: 380px; text-align: center; }

.modal-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 22px 14px;
  border-bottom: 1px solid var(--border);
}

.modal-hd h2 { font-size: 16px; font-weight: 800; color: var(--ink); }

.modal-x {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: var(--muted);
  width: 30px;
  height: 30px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
  line-height: 1;
}
.modal-x:hover { background: var(--surface); color: var(--ink); }

.modal-bd { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.modal-ft { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px; border-top: 1px solid var(--border); }

.modal-desc { font-size: 13.5px; color: var(--muted); line-height: 1.5; }
.modal-desc strong { color: var(--ink); font-weight: 700; }
.modal-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 13px; padding: 8px 12px; border-radius: 6px; }

.mfield { display: flex; flex-direction: column; gap: 6px; }
.mfield label { font-size: 12.5px; font-weight: 600; color: #374151; }
.mfield input {
  height: 44px;
  padding: 0 14px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  color: var(--ink);
  background: #fff;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.mfield input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }

.mbtn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 10px 22px; border-radius: 8px; font-size: 14px;
  font-weight: 700; cursor: pointer; transition: background 0.15s;
  display: flex; align-items: center; gap: 8px;
}
.mbtn-primary:hover:not(:disabled) { background: var(--accent-dark); }
.mbtn-primary:disabled { opacity: 0.55; cursor: not-allowed; }

.mbtn-ghost {
  background: none; border: 1.5px solid var(--border); color: var(--muted);
  padding: 10px 20px; border-radius: 8px; font-size: 14px;
  font-weight: 600; cursor: pointer; transition: border-color 0.15s;
}
.mbtn-ghost:hover { border-color: var(--accent-ring); }

.mspin {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: g-spin 0.55s linear infinite;
  flex-shrink: 0;
}
@keyframes g-spin { to { transform: rotate(360deg); } }

/* Modal transitions */
.modal-enter-active { transition: all 0.22s cubic-bezier(0.34,1.56,0.64,1); }
.modal-leave-active  { transition: all 0.16s ease-in; }
.modal-enter-from   { opacity: 0; transform: scale(0.95) translateY(10px); }
.modal-leave-to     { opacity: 0; transform: scale(0.97); }
</style>
