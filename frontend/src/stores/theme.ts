import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export type ThemeKey = 'blue' | 'green' | 'purple' | 'orange'

interface ThemeTokens {
  label: string
  accent:     string
  accentDark: string
  accentBg:   string
  accentRing: string
}

export const themes: Record<ThemeKey, ThemeTokens> = {
  blue: {
    label:      'Biru',
    accent:     '#2563eb',
    accentDark: '#1d4ed8',
    accentBg:   '#eff6ff',
    accentRing: '#bfdbfe',
  },
  green: {
    label:      'Hijau',
    accent:     '#16a34a',
    accentDark: '#15803d',
    accentBg:   '#f0fdf4',
    accentRing: '#bbf7d0',
  },
  purple: {
    label:      'Ungu',
    accent:     '#7c3aed',
    accentDark: '#6d28d9',
    accentBg:   '#f5f3ff',
    accentRing: '#ddd6fe',
  },
  orange: {
    label:      'Oranye',
    accent:     '#ea580c',
    accentDark: '#c2410c',
    accentBg:   '#fff7ed',
    accentRing: '#fed7aa',
  },
}

export const useThemeStore = defineStore('theme', () => {
  const saved = (localStorage.getItem('theme') as ThemeKey) ?? 'blue'
  const current = ref<ThemeKey>(saved)

  function apply(key: ThemeKey) {
    const t = themes[key]
    const root = document.documentElement
    root.style.setProperty('--accent',      t.accent)
    root.style.setProperty('--accent-dark', t.accentDark)
    root.style.setProperty('--accent-bg',   t.accentBg)
    root.style.setProperty('--accent-ring', t.accentRing)
  }

  function setTheme(key: ThemeKey) {
    current.value = key
    localStorage.setItem('theme', key)
    apply(key)
  }

  // Apply on init
  apply(current.value)

  watch(current, (k) => apply(k))

  return { current, setTheme, themes }
})
