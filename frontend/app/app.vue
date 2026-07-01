<script setup lang="ts">
import { businessInfo } from '~/utils/business-info'

// Site-wide structured data (JSON-LD). Lives in app.vue (not a layout) so it
// also covers the homepage, which opts out of the default layout. Establishes
// the business identity (AutoRental / LocalBusiness) used by rich results, the
// local pack, and AI answer engines (GEO). WebSite/WebPage nodes are added by
// the module automatically; per-page Product/FAQPage schema links back to this.
useSchemaOrg([
  defineLocalBusiness({
    '@type': 'AutoRental',
    name: businessInfo.name,
    alternateName: businessInfo.alternateName,
    legalName: businessInfo.legalName,
    description: businessInfo.description,
    logo: businessInfo.logo,
    image: businessInfo.image,
    email: businessInfo.email,
    telephone: businessInfo.telephone,
    currenciesAccepted: businessInfo.currenciesAccepted,
    priceRange: businessInfo.priceRange,
    address: {
      '@type': 'PostalAddress',
      streetAddress: businessInfo.address.streetAddress,
      addressLocality: businessInfo.address.addressLocality,
      addressRegion: businessInfo.address.addressRegion,
      postalCode: businessInfo.address.postalCode,
      addressCountry: businessInfo.address.addressCountry,
    },
    geo: {
      '@type': 'GeoCoordinates',
      latitude: businessInfo.geo.latitude,
      longitude: businessInfo.geo.longitude,
    },
    areaServed: [...businessInfo.areaServed],
    openingHoursSpecification: [
      {
        '@type': 'OpeningHoursSpecification',
        dayOfWeek: [
          'Monday',
          'Tuesday',
          'Wednesday',
          'Thursday',
          'Friday',
          'Saturday',
          'Sunday',
        ],
        opens: businessInfo.opens,
        closes: businessInfo.closes,
      },
    ],
    sameAs: [...businessInfo.sameAs],
  }),
])

// seo: true emits hreflang alternates + og:locale for the prefixed locales.
const localeHead = useLocaleHead({
  dir: true,
  lang: true,
  seo: true,
})

const { theme } = useTheme()

useHead(() => ({
  htmlAttrs: {
    ...localeHead.value.htmlAttrs,
    'data-theme': theme.value,
  },
  link: localeHead.value.link,
  meta: localeHead.value.meta,
  // Append the brand to page titles, but skip it when the page title already
  // contains the brand (several pages hardcode "… | ChanaaCar").
  titleTemplate: (title?: string) => {
    if (!title) return 'ChannaCar — Location de voiture à Marrakech'
    return /chann?aacar/i.test(title) ? title : `${title} · ChannaCar`
  },
}))
</script>

<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
