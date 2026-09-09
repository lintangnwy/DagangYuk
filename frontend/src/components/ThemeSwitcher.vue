<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useThemeStore, themes, type ThemeKey } from '@/stores/theme'

const theme  = useThemeStore()
const open   = ref(false)
const panelRef = ref<HTMLElement | null>(null)

const swatches: { key: ThemeKey; color: string }[] = [
  { key: 'blue',   color: '#2563eb' },
  { key: 'green',  color: '#16a34a' },
  { key: 'purple', color: '#7c3aed' },
  { key: 'orange', color: '#ea580c' },
]

function pick(key: ThemeKey) {
  theme.setTheme(key)
  open.value = false
}

function onOutside(e: MouseEvent) {
  if (panelRef.value && !panelRef.value.contains(e.target as Node)) {
    open.value = false
  }
}

onMounted(()  => document.addEventListener('mousedown', onOutside))
onUnmounted(() => document.removeEventListener('mousedown', onOutside))
</script>

<template>
  <div class="ts-wrap" ref="panelRef">
    <!-- Trigger -->
    <button
      class="ts-trigger"
      @click="open = !open"
      :aria-expanded="open"
      aria-label="Ganti tema warna"
      :title="'Tema: ' + themes[theme.current].label"
    >
      <span class="ts-dot" :style="{ background: swatches.find(s => s.key === theme.current)?.color }"></span>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
        :style="{ transform: open ? 'rotate(180deg)' : 'rotate(0)', transition: 'transform 0.2s' }">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </button>

    <!-- Dropdown -->
    <Transition name="ts-drop">
      <div v-if="open" class="ts-panel">
        <p class="ts-title">Tema Warna</p>
        <div class="ts-swatches">
          <button
            v-for="s in swatches" :key="s.key"
            class="ts-swatch"
            :class="{ active: theme.current === s.key }"
            :style="{ background: s.color }"
            :title="themes[s.key].label"
            @click="pick(s.key)"
            :aria-pressed="theme.current === s.key"
          >
            <svg v-if="theme.current === s.key" width="12" height="12" viewBox="0 0 24 24"
              fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </button>
        </div>
        <div class="ts-labels">
          <span
            v-for="s in swatches" :key="s.key"
            class="ts-label" :class="{ active: theme.current === s.key }"
          >{{ themes[s.key].label }}</span>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.ts-wrap {
  position: relative;
  flex-shrink: 0;
}

.ts-trigger {
  display: flex;
  align-items: center;
  gap: 6px;
  height: 34px;
  padding: 0 10px;
  background: none;
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 8px;
  cursor: pointer;
  color: var(--muted, #6b7280);
  transition: background 0.15s, border-color 0.15s;
}

.ts-trigger:hover {
  background: var(--surface, #f9fafb);
  border-color: var(--accent, #2563eb);
}

.ts-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Dropdown */
.ts-panel {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 160px;
  background: #fff;
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 10px;
  padding: 14px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  z-index: 200;
}

.ts-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--subtle, #9ca3af);
  margin-bottom: 10px;
}

.ts-swatches {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.ts-swatch {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s, box-shadow 0.15s;
  flex-shrink: 0;
}

.ts-swatch:hover {
  transform: scale(1.12);
}

.ts-swatch.active {
  box-shadow: 0 0 0 3px rgba(0,0,0,0.12);
  transform: scale(1.08);
}

.ts-labels {
  display: flex;
  gap: 8px;
}

.ts-label {
  font-size: 10px;
  color: var(--subtle, #9ca3af);
  width: 28px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ts-label.active {
  color: var(--accent, #2563eb);
  font-weight: 600;
}

/* Transition */
.ts-drop-enter-active { transition: all 0.18s cubic-bezier(0.34,1.56,0.64,1); }
.ts-drop-leave-active { transition: all 0.14s ease-in; }
.ts-drop-enter-from  { opacity: 0; transform: translateY(-8px) scale(0.96); }
.ts-drop-leave-to    { opacity: 0; transform: translateY(-4px) scale(0.97); }
</style>
