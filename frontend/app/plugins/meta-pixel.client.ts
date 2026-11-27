import { isAdminPath, loadMetaPixel, rememberFbclid } from '~/composables/useMetaPixel'

/**
 * Meta (Facebook) Pixel for the public site — off unless
 * NUXT_PUBLIC_META_PIXEL_ID is set. The admin back-office is never tracked.
 *
 * Fires here: PageView on each public page, Contact on WhatsApp/phone links.
 * Elsewhere: ViewContent (pages/cars/[slug].vue), Purchase on booking
 * (composables/useCarBooking.ts, mirrored server-side by the Conversions API).
 */
export default defineNuxtPlugin(() => {
  const configured: unknown = useRuntimeConfig().public.metaPixelId

  // Nuxt turns a bare numeric env value into a number; past 2^53 the last
  // digits are already wrong, and a wrong ID would silently feed someone
  // else's pixel. Set it quoted instead: NUXT_PUBLIC_META_PIXEL_ID='"1234…"'.
  if (typeof configured === 'number' && !Number.isSafeInteger(configured)) {
    console.warn('[meta-pixel] Pixel ID lost precision; wrap NUXT_PUBLIC_META_PIXEL_ID in double quotes. Pixel disabled.')
    return
  }

  const pixelId = String(configured ?? '').trim()
  if (!pixelId) {
    return
  }

  rememberFbclid()

  let lastPath = ''
  const trackPageView = (path: string) => {
    // Query-only changes (catalogue filters, …) are not new pages.
    if (isAdminPath(path) || path === lastPath) {
      return
    }

    lastPath = path
    loadMetaPixel(pixelId)
    window.fbq?.('track', 'PageView')
  }

  // Load right away (not on mount) so page components can track from their
  // own onMounted; an admin-only session never loads the script at all.
  trackPageView(window.location.pathname)

  useRouter().afterEach((to, _from, failure) => {
    if (!failure) {
      trackPageView(to.path)
    }
  })

  // WhatsApp and phone are the main ways customers reach the agency.
  document.addEventListener(
    'click',
    (event) => {
      const link = (event.target as Element | null)?.closest?.('a[href*="wa.me/"], a[href^="tel:"]')
      if (!link || !window.fbq || isAdminPath(window.location.pathname)) {
        return
      }

      const channel = link.getAttribute('href')?.startsWith('tel:') ? 'phone' : 'whatsapp'
      window.fbq('track', 'Contact', { content_category: channel })
    },
    { capture: true },
  )
})
