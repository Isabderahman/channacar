/**
 * Scroll animations for the public site. The effects themselves are CSS
 * (see "Motion" in main.css); these directives only say when to play them.
 *
 *   <div v-inview data-anim="rise">        fades up when it enters the viewport
 *   <ul v-inview data-anim="stagger">       its children rise one after another
 *   <div v-inview data-anim="rule" class="rule-b">   its hairline draws in
 *   <div v-inview data-anim="photo">        top-down curtain reveal (blocks only, not car photos)
 *
 * Hero text uses the CSS-only `.intro` class instead, so it animates on first
 * paint without waiting for JavaScript. Car photos are never animated.
 *
 * Elements are hidden before first paint only when this plugin is going to
 * reveal them: app.vue adds `html.anim` from an inline head script, and drops
 * it again if the app never boots (`window.__ccAnim` stays unset). Nothing is
 * hidden or animated under prefers-reduced-motion.
 */
export default defineNuxtPlugin((nuxtApp) => {
  let observer: IntersectionObserver | null = null

  if (import.meta.client) {
    ;(window as Window & { __ccAnim?: boolean }).__ccAnim = true
  }

  const reveal = (el: Element) => {
    ;(el as HTMLElement).dataset.inview = 'true'
  }

  const getObserver = () => {
    observer ??= new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            reveal(entry.target)
            observer?.unobserve(entry.target)
          }
        }
      },
      // Trigger a little before the element is fully on screen.
      { rootMargin: '0px 0px -10% 0px', threshold: 0.01 },
    )

    return observer
  }

  const indexChildren = (el: HTMLElement) => {
    if (!el.dataset.anim?.split(' ').includes('stagger')) {
      return
    }

    Array.from(el.children).forEach((child, index) => {
      // Cap the delay so long lists don't trail on forever.
      ;(child as HTMLElement).style.setProperty('--i', String(Math.min(index, 8)))
    })
  }

  nuxtApp.vueApp.directive('inview', {
    getSSRProps: () => ({}),

    mounted(el: HTMLElement) {
      if (!import.meta.client) {
        return
      }

      indexChildren(el)

      if (!('IntersectionObserver' in window)) {
        reveal(el)
        return
      }

      getObserver().observe(el)
    },

    updated(el: HTMLElement) {
      indexChildren(el)
    },

    unmounted(el: HTMLElement) {
      observer?.unobserve(el)
    },
  })
})
