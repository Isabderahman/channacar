<script setup lang="ts">
import MarketingFaqList from '~/components/public/MarketingFaqList.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'

const content = useMarketingContent()
const page = computed(() => content.value.faq)
const cta = computed(() => content.value.pageCta)

useSeoMeta({
  title: () => page.value.seo.title,
  description: () => page.value.seo.description,
})

// FAQPage structured data — eligible for FAQ rich results and heavily mined by
// AI answer engines (GEO). Sourced from the same localized items shown on the page.
useSchemaOrg(
  computed(() => [
    defineWebPage({ '@type': 'FAQPage' }),
    ...page.value.items.map((item) =>
      defineQuestion({ name: item.question, acceptedAnswer: item.answer }),
    ),
  ]),
)
</script>

<template>
  <div>
    <MarketingHeroSection
      :eyebrow="page.hero.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :primary-action="{ label: page.hero.primaryLabel, to: '/contact' }"
      :secondary-action="{ label: page.hero.secondaryLabel, to: '/cars' }"
    />

    <section class="wrap pt-24 lg:pt-40">
      <MarketingFaqList :title="page.title" :items="page.items" />
    </section>

    <MarketingPageCta
      :title="cta.title"
      :description="cta.description"
      :primary-action="{ label: cta.primaryLabel, to: '/cars' }"
      :secondary-action="{ label: cta.secondaryLabel, to: '/contact' }"
    />
  </div>
</template>
