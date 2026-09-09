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

body {
  font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
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
</style>
