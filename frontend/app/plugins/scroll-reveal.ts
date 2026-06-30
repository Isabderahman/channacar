/**
 * GSAP ScrollTrigger reveal directive.
 *
 * Usage:
 *   <div v-reveal />                          fade + slide up on scroll-in
 *   <div v-reveal="{ y: 40, delay: 0.1 }" />  tweak the motion
 *   <div v-reveal="{ stagger: 0.12 }" />      stagger the element's direct children
 *
 * GSAP is dynamically imported on the client only, so it never touches the SSR
 * bundle. Elements are hidden synchronously to avoid a flash before GSAP loads,
 * and animations are skipped entirely when the user prefers reduced motion.
 */
interface RevealOptions {
  y?: number
  x?: number
  opacity?: number
  duration?: number
  delay?: number
  ease?: string
  start?: string
  once?: boolean
  stagger?: number | boolean
}

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive('reveal', {
    getSSRProps: () => ({}),

    mounted(el: HTMLElement, binding: { value?: RevealOptions }) {
      if (!import.meta.client) {
        return
      }

      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return
      }

      const {
        y = 28,
        x = 0,
        opacity = 0,
        duration = 0.85,
        delay = 0,
        ease = 'power3.out',
        start = 'top 85%',
        once = true,
        stagger,
      } = binding.value ?? {}

      const targets = stagger
        ? (Array.from(el.children) as HTMLElement[])
        : [el]

      if (!targets.length) {
        return
      }

      // Hide synchronously so nothing flashes before GSAP finishes loading.
      for (const target of targets) {
        target.style.opacity = String(opacity)
        target.style.transform = `translate(${x}px, ${y}px)`
        target.style.willChange = 'transform, opacity'
      }

      const store = el as HTMLElement & { _reveal?: { killed: boolean; tween?: gsap.core.Tween } }
      store._reveal = { killed: false }

      Promise.all([import('gsap'), import('gsap/ScrollTrigger')]).then(
        ([{ gsap }, { ScrollTrigger }]) => {
          if (store._reveal?.killed) {
            return
          }

          gsap.registerPlugin(ScrollTrigger)

          store._reveal!.tween = gsap.to(targets, {
            opacity: 1,
            x: 0,
            y: 0,
            duration,
            delay,
            ease,
            stagger: stagger ? (typeof stagger === 'number' ? stagger : 0.12) : 0,
            scrollTrigger: {
              trigger: el,
              start,
              once,
              toggleActions: 'play none none none',
            },
            onComplete: () => {
              for (const target of targets) {
                target.style.willChange = ''
              }
            },
          })
        },
      )
    },

    unmounted(el: HTMLElement) {
      const store = el as HTMLElement & { _reveal?: { killed: boolean; tween?: gsap.core.Tween } }
      if (store._reveal) {
        store._reveal.killed = true
        store._reveal.tween?.scrollTrigger?.kill()
        store._reveal.tween?.kill()
      }
    },
  })

  /**
   * Hero intro timeline — plays on load (not on scroll, since heroes are above
   * the fold). Animates every child marked `data-intro` in DOM order, gives the
   * `data-intro-image` element a zoom-in + gentle scroll parallax.
   *
   *   <section v-intro> ... <h1 data-intro> ... <div data-intro-image> </section>
   */
  nuxtApp.vueApp.directive('intro', {
    getSSRProps: () => ({}),

    mounted(el: HTMLElement, binding: { value?: { y?: number; duration?: number; delay?: number; stagger?: number } }) {
      if (!import.meta.client) {
        return
      }

      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return
      }

      const { y = 26, duration = 0.8, delay = 0.1, stagger = 0.12 } = binding.value ?? {}

      const items = Array.from(el.querySelectorAll('[data-intro]')) as HTMLElement[]
      const image = el.querySelector('[data-intro-image]') as HTMLElement | null

      if (!items.length && !image) {
        return
      }

      // Hide synchronously to avoid a flash before GSAP loads.
      for (const item of items) {
        item.style.opacity = '0'
        item.style.transform = `translateY(${y}px)`
        item.style.willChange = 'transform, opacity'
      }
      if (image) {
        image.style.opacity = '0'
        image.style.willChange = 'transform, opacity'
      }

      const store = el as HTMLElement & {
        _intro?: { killed: boolean; timeline?: gsap.core.Timeline; parallax?: gsap.core.Tween }
      }
      store._intro = { killed: false }

      Promise.all([import('gsap'), import('gsap/ScrollTrigger')]).then(
        ([{ gsap }, { ScrollTrigger }]) => {
          if (store._intro?.killed) {
            return
          }

          gsap.registerPlugin(ScrollTrigger)

          const timeline = gsap.timeline({ defaults: { ease: 'power3.out' }, delay })

          if (image) {
            timeline.fromTo(
              image,
              { opacity: 0, scale: 1.08 },
              { opacity: 1, scale: 1, duration: duration + 0.35 },
              0,
            )
          }

          if (items.length) {
            timeline.to(
              items,
              { opacity: 1, y: 0, duration, stagger },
              image ? 0.18 : 0,
            )
          }

          timeline.eventCallback('onComplete', () => {
            for (const item of items) {
              item.style.willChange = ''
            }
            if (image) {
              image.style.willChange = ''
            }
          })

          // Gentle parallax: drift the hero image as the section scrolls past.
          if (image) {
            store._intro!.parallax = gsap.to(image, {
              yPercent: 10,
              ease: 'none',
              scrollTrigger: {
                trigger: el,
                start: 'top top',
                end: 'bottom top',
                scrub: true,
              },
            })
          }

          store._intro!.timeline = timeline
        },
      )
    },

    unmounted(el: HTMLElement) {
      const store = el as HTMLElement & {
        _intro?: { killed: boolean; timeline?: gsap.core.Timeline; parallax?: gsap.core.Tween }
      }
      if (store._intro) {
        store._intro.killed = true
        store._intro.parallax?.scrollTrigger?.kill()
        store._intro.parallax?.kill()
        store._intro.timeline?.kill()
      }
    },
  })
})
