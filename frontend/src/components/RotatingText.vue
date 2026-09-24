<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'

interface Props {
  texts: string[]
  transition?: {
    duration?: number
    stagger?: number
  }
  rotationInterval?: number
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  texts: () => ['Toko', 'Kafe', 'Resto', 'Retail', 'UMKM'],
  rotationInterval: 2400
})

const currentIndex = ref(0)
let timer: number | null = null

const currentWord = computed(() => props.texts[currentIndex.value] || '')
const currentLetters = computed(() => currentWord.value.split(''))

const next = () => {
  currentIndex.value = (currentIndex.value + 1) % props.texts.length
}

onMounted(() => {
  timer = window.setInterval(next, props.rotationInterval)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<template>
  <span class="rotating-text-wrapper inline-flex items-center align-baseline">
    <transition name="rotate-word" mode="out-in">
      <span :key="currentWord" class="rotating-word inline-flex overflow-hidden py-1">
        <span
          v-for="(char, idx) in currentLetters"
          :key="`${currentWord}-${idx}`"
          class="rotating-letter inline-block"
          :style="{
            animationDelay: `${idx * 35}ms`
          }"
        >
          <span v-if="char === ' '">&nbsp;</span>
          <span v-else>{{ char }}</span>
        </span>
      </span>
    </transition>
  </span>
</template>

<style scoped>
.rotating-text-wrapper {
  display: inline-flex;
  vertical-align: baseline;
  position: relative;
}

.rotating-word {
  display: inline-flex;
}

.rotating-letter {
  display: inline-block;
  animation: flipLetter 0.45s cubic-bezier(0.2, 0.8, 0.2, 1) both;
}

@keyframes flipLetter {
  0% {
    opacity: 0;
    transform: translateY(100%) rotateX(-90deg);
    filter: blur(4px);
  }
  100% {
    opacity: 1;
    transform: translateY(0) rotateX(0deg);
    filter: blur(0);
  }
}

.rotate-word-enter-active,
.rotate-word-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.rotate-word-leave-to {
  opacity: 0;
  transform: translateY(-50%) scale(0.95);
  filter: blur(4px);
}
</style>