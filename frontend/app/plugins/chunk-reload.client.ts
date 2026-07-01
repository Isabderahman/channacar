/**
 * Recover from stale-deploy chunk errors.
 *
 * When the frontend is rebuilt, Vite emits new hashed filenames and removes the
 * old ones. A visitor who loaded the site before a deploy still references the
 * old chunks; the next client-side navigation then fails with "Failed to fetch
 * dynamically imported module". Instead of leaving the user on a broken page, we
 * force a full page load of the intended route so fresh assets are fetched.
 *
 * A short-lived sessionStorage guard prevents reload loops if the asset is
 * genuinely unreachable (e.g. a bad deploy) rather than merely stale.
 */
export default defineNuxtPlugin((nuxtApp) => {
  const RELOAD_GUARD_KEY = 'channacar.chunkReloadedAt'
  const RELOAD_WINDOW_MS = 10_000

  const reloadOnce = (path: string) => {
    const now = Date.now()
    const last = Number(sessionStorage.getItem(RELOAD_GUARD_KEY) || 0)
    if (now - last < RELOAD_WINDOW_MS) {
      return
    }
    sessionStorage.setItem(RELOAD_GUARD_KEY, String(now))
    window.location.assign(path)
  }

  // `app:chunkError` fires mid-navigation, when currentRoute is still the
  // source route. Track the intended destination so we reload straight into it.
  const router = useRouter()
  let pendingTarget = router.currentRoute.value.fullPath
  router.beforeEach((to) => {
    pendingTarget = to.fullPath
  })

  nuxtApp.hook('app:chunkError', ({ error }) => {
    console.warn('[chunk-reload] reloading after chunk error:', error?.message)
    reloadOnce(pendingTarget)
  })
})
