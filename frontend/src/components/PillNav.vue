<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue'
import { gsap } from 'gsap'

export interface PillNavItem {
  label: string
  key: string
}

const props = withDefaults(defineProps<{
  items: PillNavItem[]
  active: string
  baseColor?: string
  pillColor?: string
  pillTextColor?: string
  ease?: string
  initialLoadAnimation?: boolean
}>(), {
  baseColor: '#f3f4f6',
  pillColor: '#111827',
  pillTextColor: '#ffffff',
  ease: 'power3.out',
  initialLoadAnimation: false,
})

const emit = defineEmits<{
  (e: 'change', key: string): void
}>()

const navRef  = ref<HTMLElement | null>(null)
const pillRef = ref<HTMLElement | null>(null)

function getActiveEl(): HTMLElement | null {
  if (!navRef.value) return null
  return navRef.value.querySelector(`[data-key="${props.active}"]`) as HTMLElement | null
}

function movePill(target: HTMLElement, animate = true) {
  const pill = pillRef.value
  const nav  = navRef.value
  if (!pill || !nav) return

  const navRect    = nav.getBoundingClientRect()
  const targetRect = target.getBoundingClientRect()

  const x = targetRect.left - navRect.left
  const w = targetRect.width

  if (animate) {
    gsap.to(pill, {
      x, width: w,
      duration: 0.35,
      ease: props.ease,
    })
  } else {
    gsap.set(pill, { x, width: w })
  }
}

function select(item: PillNavItem, el: HTMLElement) {
  emit('change', item.key)
  movePill(el)
}

onMounted(async () => {
  await nextTick()
  const activeEl = getActiveEl()
  if (!activeEl) return

  if (props.initialLoadAnimation) {
    gsap.set(pillRef.value, { opacity: 0 })
    movePill(activeEl, false)
    gsap.to(pillRef.value, { opacity: 1, duration: 0.3, delay: 0.2, ease: 'power2.out' })

    gsap.from(navRef.value!.querySelectorAll('.pn-item'), {
      opacity: 0, y: -8, stagger: 0.06, duration: 0.35,
      ease: 'power2.out', delay: 0.1,
    })
  } else {
    movePill(activeEl, false)
  }
})

watch(() => props.active, async () => {
  await nextTick()
  const activeEl = getActiveEl()
  if (activeEl) movePill(activeEl)
})

// Re-position pill saat items berubah (misal setelah user info dimuat)
watch(() => props.items, async () => {
  await nextTick()
  const activeEl = getActiveEl()
  if (activeEl) movePill(activeEl, false)
}, { deep: true })
</script>

<template>
  <nav
    ref="navRef"
    class="pn-nav"
    :style="{ background: baseColor }"
    role="tablist"
  >
    <!-- Sliding pill -->
    <span
      ref="pillRef"
      class="pn-pill"
      :style="{ background: pillColor }"
      aria-hidden="true"
    ></span>

    <!-- Items -->
    <button
      v-for="item in items"
      :key="item.key"
      :data-key="item.key"
      class="pn-item"
      :class="{ 'pn-item--active': active === item.key }"
      :style="{
        color: active === item.key ? pillTextColor : undefined,
      }"
      role="tab"
      :aria-selected="active === item.key"
      @click="select(item, $event.currentTarget as HTMLElement)"
    >
      {{ item.label }}
    </button>
  </nav>
</template>

<style scoped>
.pn-nav {
  position: relative;
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 4px;
  gap: 0;
  user-select: none;
}

.pn-pill {
  position: absolute;
  top: 4px;
  left: 0;
  height: calc(100% - 8px);
  border-radius: 999px;
  pointer-events: none;
  will-change: transform, width;
  z-index: 0;
}

.pn-item {
  position: relative;
  z-index: 1;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px 16px;
  border-radius: 999px;
  font-size: 13.5px;
  font-weight: 500;
  color: #6b7280;
  white-space: nowrap;
  transition: color 0.2s;
  line-height: 1;
}

.pn-item:hover:not(.pn-item--active) {
  color: #111827;
}

.pn-item--active {
  font-weight: 600;
}
</style>
