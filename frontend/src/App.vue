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

/* ── Lenis smooth-scroll base ── */
html.lenis, html.lenis body { height: auto; }
.lenis.lenis-smooth { scroll-behavior: auto !important; }
.lenis.lenis-smooth [data-lenis-prevent] { overscroll-behavior: contain; }
.lenis.lenis-stopped { overflow: hidden; }

/* ── DESIGN SYSTEM: Color Palette ── */
:root {
  /* Primary Colors */
  --accent:      #4f46e5;      /* Electric Indigo */
  --accent-dark: #4338ca;      /* Darker Indigo */
  --accent-light:#6366f1;      /* Lighter Indigo */
  --accent-bg:   #eef2ff;      /* Very Light Indigo */
  --accent-ring: #c7d2fe;      /* Ring/Border */
  --accent-rgb:  79, 70, 229;

  /* Secondary Colors */
  --success:     #10b981;      /* Emerald */
  --success-bg:  #d1fae5;
  --warning:     #f59e0b;      /* Amber */
  --warning-bg:  #fef3c7;
  --danger:      #ef4444;      /* Red */
  --danger-bg:   #fee2e2;
  --info:        #06b6d4;      /* Cyan */
  --info-bg:     #cffafe;

  /* Neutral/Grays */
  --ink:         #111827;      /* Almost Black */
  --ink-dark:    #0f172a;      /* Darker */
  --ink-light:   #1f2937;      /* Lighter */
  --muted:       #6b7280;      /* Medium Gray */
  --subtle:      #9ca3af;      /* Light Gray */
  --border:      #e5e7eb;      /* Border/Divider */
  --surface:     #f9fafb;      /* Background */
  --surface-alt: #f3f4f6;      /* Alt Background */
  --white:       #ffffff;      /* White */

  /* ── Typography ── */
  --font-sans:   'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
  --font-mono:   'Monaco', 'Courier New', monospace;
  
  /* Font Sizes */
  --text-xs:     11px;         /* 0.75rem */
  --text-sm:     12px;         /* 0.875rem */
  --text-base:   14px;         /* 1rem */
  --text-lg:     16px;         /* 1.125rem */
  --text-xl:     18px;         /* 1.25rem */
  --text-2xl:    20px;         /* 1.5rem */
  --text-3xl:    24px;         /* 1.75rem */
  --text-4xl:    32px;         /* 2rem */

  /* Font Weights */
  --font-normal: 400;
  --font-medium: 500;
  --font-semibold: 600;
  --font-bold:   700;
  --font-extrabold: 800;

  /* Line Heights */
  --leading-tight: 1.25;
  --leading-snug:  1.375;
  --leading-normal: 1.5;
  --leading-relaxed: 1.625;
  --leading-loose:  2;

  /* ── Spacing Scale ── */
  --space-0:     0;
  --space-1:     4px;
  --space-2:     8px;
  --space-3:     12px;
  --space-4:     16px;
  --space-5:     20px;
  --space-6:     24px;
  --space-8:     32px;
  --space-10:    40px;
  --space-12:    48px;
  --space-16:    64px;
  --space-20:    80px;

  /* ── Border Radius ── */
  --rounded-xs:   2px;
  --rounded-sm:   4px;
  --rounded-md:   8px;
  --rounded-lg:   12px;
  --rounded-xl:   16px;
  --rounded-2xl:  20px;
  --rounded-full: 999px;

  /* ── Shadows ── */
  --shadow-xs:  0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-sm:  0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md:  0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg:  0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-xl:  0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  --shadow-inner: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);

  /* ── Transitions ── */
  --transition-fast:   150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-base:   200ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow:   300ms cubic-bezier(0.4, 0, 0.2, 1);

  /* ── Z-Index Scale ── */
  --z-hide:      -1;
  --z-base:      0;
  --z-dropdown:  1000;
  --z-sticky:    1020;
  --z-fixed:     1030;
  --z-modal:     1040;
  --z-popover:   1050;
  --z-tooltip:   1070;
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

/* ── Modal Sukses — item list ── */
.success-icon {
  width: 56px; height: 56px; border-radius: 50%;
  background: #dcfce7; color: #16a34a;
  display: flex; align-items: center; justify-content: center;
  margin: 24px auto 10px;
}

.success-title {
  font-size: 20px; font-weight: 800;
  color: var(--ink, #111827); margin: 0;
  text-align: center;
}

.success-inv {
  font-size: 12.5px; color: var(--muted, #6b7280);
  text-align: center; margin-top: 4px;
}

.success-details {
  width: 100%; margin-top: 14px;
  border-top: 1px dashed var(--border, #e5e7eb);
  padding-top: 14px;
  display: flex; flex-direction: column; gap: 6px;
}

.s-items-label {
  font-size: 10.5px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.8px;
  color: var(--muted, #6b7280);
  margin-bottom: 4px;
}

.s-item {
  display: flex; justify-content: space-between;
  align-items: flex-start; gap: 8px;
}

.s-item-left {
  display: flex; flex-direction: column; gap: 1px; flex: 1; min-width: 0;
}

.s-item-name {
  font-size: 13px; font-weight: 600;
  color: var(--ink, #111827);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.s-item-qty {
  font-size: 11.5px; color: var(--muted, #6b7280);
}

.s-item-sub {
  font-size: 13px; font-weight: 700;
  color: var(--ink, #111827); white-space: nowrap;
}

.srow {
  display: flex; justify-content: space-between; align-items: center;
  font-size: 13.5px; color: var(--muted, #6b7280); gap: 8px;
}
.srow span:first-child { white-space: nowrap; flex-shrink: 0; }
.srow strong { color: var(--ink, #111827); font-weight: 700; }
.srow.change {
  background: #f0fdf4; border-radius: 8px;
  padding: 10px 14px; color: #15803d;
}
.srow.change strong { color: #15803d; font-size: 15px; font-weight: 800; }

.success-actions {
  display: flex; flex-direction: column; gap: 10px;
  padding: 0 24px 24px; margin-top: 8px;
}

.success-btn-outline {
  width: 100%; height: 44px; background: none;
  border: 1.5px solid var(--border, #e5e7eb);
  color: var(--ink, #111827); border-radius: 10px;
  font-size: 14px; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: border-color 0.15s;
}
.success-btn-outline:hover { border-color: var(--accent, #2563eb); }

.success-btn-primary {
  width: 100%; height: 44px; background: var(--accent, #2563eb); color: #fff;
  border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
  cursor: pointer; display: flex; align-items: center;
  justify-content: center; gap: 8px; transition: background 0.15s;
}
.success-btn-primary:hover { background: var(--accent-dark, #1d4ed8); }

.abs-x {
  position: absolute; top: 14px; right: 14px;
  background: none; border: none; font-size: 18px;
  cursor: pointer; color: var(--muted, #6b7280);
  width: 30px; height: 30px; border-radius: 6px;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s; line-height: 1;
}
.abs-x:hover { background: var(--surface, #f9fafb); }

/* ════════════════════════════════════════════════════════════ */
/* ── UTILITY CLASSES FOR RAPID DEVELOPMENT ── */
/* ════════════════════════════════════════════════════════════ */

/* Text Utilities */
.text-xs { font-size: var(--text-xs); }
.text-sm { font-size: var(--text-sm); }
.text-base { font-size: var(--text-base); }
.text-lg { font-size: var(--text-lg); }
.text-xl { font-size: var(--text-xl); }
.text-2xl { font-size: var(--text-2xl); }
.text-3xl { font-size: var(--text-3xl); }
.text-4xl { font-size: var(--text-4xl); }

.font-normal { font-weight: var(--font-normal); }
.font-medium { font-weight: var(--font-medium); }
.font-semibold { font-weight: var(--font-semibold); }
.font-bold { font-weight: var(--font-bold); }
.font-extrabold { font-weight: var(--font-extrabold); }

.leading-tight { line-height: var(--leading-tight); }
.leading-snug { line-height: var(--leading-snug); }
.leading-normal { line-height: var(--leading-normal); }
.leading-relaxed { line-height: var(--leading-relaxed); }
.leading-loose { line-height: var(--leading-loose); }

/* Color Utilities */
.text-ink { color: var(--ink); }
.text-muted { color: var(--muted); }
.text-subtle { color: var(--subtle); }
.text-white { color: var(--white); }
.text-accent { color: var(--accent); }
.text-success { color: var(--success); }
.text-warning { color: var(--warning); }
.text-danger { color: var(--danger); }

.bg-surface { background: var(--surface); }
.bg-surface-alt { background: var(--surface-alt); }
.bg-white { background: var(--white); }
.bg-accent-bg { background: var(--accent-bg); }
.bg-success-bg { background: var(--success-bg); }
.bg-warning-bg { background: var(--warning-bg); }
.bg-danger-bg { background: var(--danger-bg); }

.border-border { border-color: var(--border); }

/* Spacing Utilities */
.p-1 { padding: var(--space-1); }
.p-2 { padding: var(--space-2); }
.p-3 { padding: var(--space-3); }
.p-4 { padding: var(--space-4); }
.p-6 { padding: var(--space-6); }
.p-8 { padding: var(--space-8); }

.px-2 { padding-left: var(--space-2); padding-right: var(--space-2); }
.px-3 { padding-left: var(--space-3); padding-right: var(--space-3); }
.px-4 { padding-left: var(--space-4); padding-right: var(--space-4); }

.py-2 { padding-top: var(--space-2); padding-bottom: var(--space-2); }
.py-3 { padding-top: var(--space-3); padding-bottom: var(--space-3); }
.py-4 { padding-top: var(--space-4); padding-bottom: var(--space-4); }

.m-1 { margin: var(--space-1); }
.m-2 { margin: var(--space-2); }
.m-3 { margin: var(--space-3); }
.m-4 { margin: var(--space-4); }

.mx-auto { margin-left: auto; margin-right: auto; }
.mt-2 { margin-top: var(--space-2); }
.mt-4 { margin-top: var(--space-4); }
.mb-2 { margin-bottom: var(--space-2); }
.mb-4 { margin-bottom: var(--space-4); }

/* Flexbox Utilities */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.flex-row { flex-direction: row; }
.flex-wrap { flex-wrap: wrap; }
.flex-nowrap { flex-wrap: nowrap; }
.items-start { align-items: flex-start; }
.items-center { align-items: center; }
.items-end { align-items: flex-end; }
.items-stretch { align-items: stretch; }
.justify-start { justify-content: flex-start; }
.justify-center { justify-content: center; }
.justify-between { justify-content: space-between; }
.justify-around { justify-content: space-around; }
.gap-1 { gap: var(--space-1); }
.gap-2 { gap: var(--space-2); }
.gap-3 { gap: var(--space-3); }
.gap-4 { gap: var(--space-4); }
.gap-6 { gap: var(--space-6); }

/* Grid Utilities */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: 1fr; }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

/* Border Radius */
.rounded-xs { border-radius: var(--rounded-xs); }
.rounded-sm { border-radius: var(--rounded-sm); }
.rounded-md { border-radius: var(--rounded-md); }
.rounded-lg { border-radius: var(--rounded-lg); }
.rounded-xl { border-radius: var(--rounded-xl); }
.rounded-full { border-radius: var(--rounded-full); }

/* Shadows */
.shadow-xs { box-shadow: var(--shadow-xs); }
.shadow-sm { box-shadow: var(--shadow-sm); }
.shadow-md { box-shadow: var(--shadow-md); }
.shadow-lg { box-shadow: var(--shadow-lg); }
.shadow-xl { box-shadow: var(--shadow-xl); }
.shadow-2xl { box-shadow: var(--shadow-2xl); }

/* Display & Position */
.block { display: block; }
.inline-block { display: inline-block; }
.inline { display: inline; }
.hidden { display: none; }
.absolute { position: absolute; }
.relative { position: relative; }
.fixed { position: fixed; }
.sticky { position: sticky; }

/* Width & Height */
.w-full { width: 100%; }
.w-auto { width: auto; }
.h-full { height: 100%; }
.h-auto { height: auto; }
.min-w-0 { min-width: 0; }
.max-w-full { max-width: 100%; }

/* Overflow & Text */
.overflow-hidden { overflow: hidden; }
.overflow-auto { overflow: auto; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.whitespace-nowrap { white-space: nowrap; }

/* Opacity & Cursor */
.opacity-50 { opacity: 0.5; }
.opacity-75 { opacity: 0.75; }
.cursor-pointer { cursor: pointer; }
.cursor-not-allowed { cursor: not-allowed; }

/* ════════════════════════════════════════════════════════════ */
/* ── RESPONSIVE BREAKPOINTS ── */
/* ════════════════════════════════════════════════════════════ */

/* Mobile First Approach */
/* Base: Mobile (320px - 480px) */

@media (min-width: 481px) {
  /* Tablet (481px - 768px) */
  .sm\:block { display: block; }
  .sm\:hidden { display: none; }
  .sm\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
  .sm\:p-4 { padding: var(--space-4); }
  .sm\:gap-4 { gap: var(--space-4); }
}

@media (min-width: 769px) {
  /* Desktop (769px - 1024px) */
  .md\:block { display: block; }
  .md\:hidden { display: none; }
  .md\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
  .md\:p-6 { padding: var(--space-6); }
  .md\:gap-6 { gap: var(--space-6); }
}

@media (min-width: 1025px) {
  /* Wide (1025px+) */
  .lg\:block { display: block; }
  .lg\:hidden { display: none; }
  .lg\:grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
  .lg\:p-8 { padding: var(--space-8); }
  .lg\:gap-8 { gap: var(--space-8); }
}

/* ════════════════════════════════════════════════════════════ */
/* ── ANIMATION KEYFRAMES ── */
/* ════════════════════════════════════════════════════════════ */

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideInUp {
  from { transform: translateY(16px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes slideInDown {
  from { transform: translateY(-16px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-fade { animation: fadeIn var(--transition-base); }
.animate-slide-up { animation: slideInUp var(--transition-base); }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

</style>
