import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export type ThemeKey = 'blue' | 'green' | 'purple' | 'orange'

interface ThemeTokens {
  label: string
  accent:     string
  accentDark: string
  accentBg:   string
  accentRing: string
  accentRgb:  string
}

export const themes: Record<ThemeKey, ThemeTokens> = {
  blue: {
    label:      'Indigo Blue',
    accent:     '#4f46e5',
    accentDark: '#4338ca',
    accentBg:   '#eef2ff',
    accentRing: '#c7d2fe',
    accentRgb:  '79, 70, 229',
  },
  green: {
    label:      'Hijau',
    accent:     '#16a34a',
    accentDark: '#15803d',
    accentBg:   '#f0fdf4',
    accentRing: '#bbf7d0',
    accentRgb:  '22, 163, 74',
  },
  purple: {
    label:      'Ungu',
    accent:     '#7c3aed',
    accentDark: '#6d28d9',
    accentBg:   '#f5f3ff',
    accentRing: '#ddd6fe',
    accentRgb:  '124, 58, 237',
  },
  orange: {
    label:      'Oranye',
    accent:     '#ea580c',
    accentDark: '#c2410c',
    accentBg:   '#fff7ed',
    accentRing: '#fed7aa',
    accentRgb:  '234, 88, 12',
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
    root.style.setProperty('--accent-rgb',  t.accentRgb)
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
