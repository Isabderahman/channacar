import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2026-06-23',
  // Chunk errors after a redeploy are handled by app/plugins/chunk-reload.client.ts,
  // which reloads into the intended route with fresh assets.
  experimental: {
    emitRouteChunkError: 'manual',
  },
  css: ['~/assets/css/main.css'],
  components: [{ path: '~/components', pathPrefix: false }],
  modules: ['@nuxtjs/i18n', '@nuxtjs/seo'],
  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL ?? 'https://chanaacar.com',
    name: 'ChannaCar',
    defaultLocale: 'fr',
  },
  sitemap: {
    // Dynamic car detail pages are supplied by the server route below;
    // static routes are auto-discovered. The admin SPA is excluded.
    sources: ['/api/__sitemap__/urls'],
    exclude: ['/admin/**', '/reservation/success'],
  },
  robots: {
    disallow: ['/admin', '/admin/**', '/reservation/success'],
  },
  runtimeConfig: {
    backendOrigin: process.env.NUXT_BACKEND_ORIGIN ?? 'http://127.0.0.1:8000',
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE ?? '/_backend/api',
      storageBase: process.env.NUXT_PUBLIC_STORAGE_BASE ?? '/_backend/storage',
    },
  },
  app: {
    head: {
      meta: [
        {
          name: 'description',
          content:
            'ChannaCar — agence de location de voiture à Marrakech : aéroport Ménara, longue durée, large flotte. Réservation simple et tarifs transparents.',
        },
        // Social share defaults; per-page useSeoMeta() overrides these.
        { property: 'og:type', content: 'website' },
        { property: 'og:image', content: '/images/hero-banner.jpg' },
        { name: 'twitter:card', content: 'summary_large_image' },
        { name: 'twitter:image', content: '/images/hero-banner.jpg' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'icon', type: 'image/png', sizes: '32x32', href: '/favicon-32.png' },
        { rel: 'icon', type: 'image/png', sizes: '64x64', href: '/favicon-64.png' },
        { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' },
      ],
    },
  },
  i18n: {
    defaultLocale: 'fr',
    baseUrl: process.env.NUXT_PUBLIC_SITE_URL ?? 'https://chanaacar.com',
    // Default locale (fr) served at `/`; other locales prefixed (`/en/`, `/es/`, `/ar/`).
    // This exposes each language as a distinct URL so search engines can index them,
    // and lets @nuxtjs/i18n emit hreflang alternates automatically.
    strategy: 'prefix_except_default',
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'channacar.locale',
      cookieCrossOrigin: false,
      redirectOn: 'root',
      alwaysRedirect: false,
    },
    langDir: 'locales',
    locales: [
      { code: 'en', name: 'English', language: 'en-US', file: 'en.json', dir: 'ltr' },
      { code: 'fr', name: 'Français', language: 'fr-FR', file: 'fr.json', dir: 'ltr' },
      { code: 'es', name: 'Español', language: 'es-ES', file: 'es.json', dir: 'ltr' },
      { code: 'ar', name: 'العربية', language: 'ar', file: 'ar.json', dir: 'rtl' },
    ],
  },
  vite: {
    plugins: [tailwindcss()],
  },
})
