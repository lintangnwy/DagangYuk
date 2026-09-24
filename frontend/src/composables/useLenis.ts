import Lenis from 'lenis'
import { onMounted, onUnmounted, ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

/**
 * Reusable smooth-scroll composable powered by Lenis.
 *
 * Syncs Lenis with GSAP's ticker so ScrollTrigger animations stay in
 * lock-step with the smoothed scroll position. Intended for full-window
 * scroll pages (landing / marketing). Do NOT mount on app-shell pages that
 * rely on internal overflow-y containers (e.g. POS, cart), because Lenis
 * hijacks the window scroll and those inner panes would desync.
 */
export function useLenis() {
  gsap.registerPlugin(ScrollTrigger)

  const lenis = ref<Lenis | null>(null)
  let rafFn: (time: number) => void

  onMounted(() => {
    const instance = new Lenis({
      duration: 1.2,
      easing: (t: number) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      smoothWheel: true,
      touchMultiplier: 1.8,
    })
    lenis.value = instance

    rafFn = (time: number) => instance.raf(time * 1000)
    gsap.ticker.add(rafFn)
    gsap.ticker.lagSmoothing(0)

    // Keep ScrollTrigger accurate when Lenis scrolls.
    instance.on('scroll', ScrollTrigger.update)
  })

  const stop = () => {
    if (rafFn) gsap.ticker.remove(rafFn)
    lenis.value?.destroy()
    lenis.value = null
  }

  onUnmounted(stop)

  return { lenis, stop }
}
