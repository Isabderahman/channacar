/**
 * Meta (Facebook) Pixel helpers.
 *
 * The pixel is loaded by plugins/meta-pixel.client.ts when a pixel ID is
 * configured (NUXT_PUBLIC_META_PIXEL_ID). `track` is a no-op on the server,
 * without a pixel ID, and on admin pages.
 *
 * Bookings are also sent server-side through the Conversions API (Backend
 * MetaConversionsService). Both copies share one event ID so Meta counts the
 * booking once: pass `trackingContext()` to the API and its `event_id` to
 * `track`.
 */

type FbqFunction = ((...args: unknown[]) => void) & {
  callMethod?: (...args: unknown[]) => void
  queue: unknown[]
  push: FbqFunction
  loaded: boolean
  version: string
  disablePushState?: boolean
}

declare global {
  interface Window {
    fbq?: FbqFunction
    _fbq?: FbqFunction
  }
}

/** Browser context the backend needs to send the matching server event. */
export interface MetaTrackingContext {
  event_id: string
  source_url: string
  fbp: string | null
  fbc: string | null
}

const PIXEL_SCRIPT = 'https://connect.facebook.net/en_US/fbevents.js'

/** `/admin`, `/en/admin/...` — the back-office is never tracked. */
export const isAdminPath = (path: string) => /^(\/[a-z]{2})?\/admin(\/|$)/.test(path)

/**
 * Meta's base snippet: queue calls in a stub until fbevents.js has loaded,
 * then init the pixel. Safe to call more than once.
 */
export const loadMetaPixel = (pixelId: string) => {
  if (window.fbq) {
    return
  }

  const fbq = function (...args: unknown[]) {
    if (fbq.callMethod) {
      fbq.callMethod.apply(fbq, args)
    } else {
      fbq.queue.push(args)
    }
  } as FbqFunction
  fbq.push = fbq
  fbq.loaded = true
  fbq.version = '2.0'
  fbq.queue = []
  // We send PageView ourselves on route changes (skipping /admin); without
  // this the pixel would also fire one on every history.pushState.
  fbq.disablePushState = true

  window.fbq = fbq
  window._fbq ??= fbq

  const script = document.createElement('script')
  script.async = true
  script.src = PIXEL_SCRIPT
  document.head.appendChild(script)

  fbq('init', pixelId)
}

const readCookie = (name: string): string | null => {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`))
  return match?.[1] ? decodeURIComponent(match[1]) : null
}

const newEventId = () =>
  globalThis.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(36).slice(2, 12)}`

/**
 * Keep the ad click ID (`fbclid`) as the `_fbc` cookie Meta expects, so the
 * server event can be attributed to the ad even when an ad blocker stops the
 * pixel from doing it. Format: fb.1.<timestamp ms>.<fbclid>.
 */
export const rememberFbclid = () => {
  const fbclid = new URLSearchParams(window.location.search).get('fbclid')
  if (!fbclid || readCookie('_fbc')?.endsWith(`.${fbclid}`)) {
    return
  }

  const ninetyDays = 90 * 24 * 60 * 60
  document.cookie = `_fbc=fb.1.${Date.now()}.${encodeURIComponent(fbclid)}; path=/; max-age=${ninetyDays}; SameSite=Lax`
}

export const useMetaPixel = () => {
  const track = (event: string, params: Record<string, unknown> = {}, eventId?: string) => {
    if (import.meta.server || !window.fbq || isAdminPath(window.location.pathname)) {
      return
    }

    if (eventId) {
      window.fbq('track', event, params, { eventID: eventId })
    } else {
      window.fbq('track', event, params)
    }
  }

  const trackingContext = (): MetaTrackingContext => ({
    event_id: newEventId(),
    source_url: window.location.href,
    fbp: readCookie('_fbp'),
    fbc: readCookie('_fbc'),
  })

  return { track, trackingContext }
}
