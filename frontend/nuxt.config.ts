import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2026-06-23',
  css: ['~/assets/css/main.css'],
  components: [{ path: '~/components', pathPrefix: false }],
  modules: ['@nuxtjs/i18n'],
  runtimeConfig: {
    backendOrigin: process.env.NUXT_BACKEND_ORIGIN ?? 'http://127.0.0.1:8000',
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE ?? '/_backend/api',
      storageBase: process.env.NUXT_PUBLIC_STORAGE_BASE ?? '/_backend/storage',
    },
  },
  app: {
    head: {
      title: 'ChannaCar',
      meta: [
        {
          name: 'description',
          content:
            'Ridex-inspired car rental landing page built with reusable Nuxt 4 and Tailwind components.',
        },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
        { rel: 'icon', type: 'image/png', href: '/favicon-64.png' },
        { rel: 'apple-touch-icon', href: '/images/chanaa-car-logo.png' },
      ],
    },
  },
  i18n: {
    defaultLocale: 'fr',
    baseUrl: process.env.NUXT_PUBLIC_SITE_URL ?? 'https://chanaacar.com',
    // No locale prefix in the URL — the active language is persisted in a cookie.
    strategy: 'no_prefix',
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
