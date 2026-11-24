<script setup lang="ts">
import MarketingBulletList from '~/components/public/MarketingBulletList.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingNarrativeSection from '~/components/public/MarketingNarrativeSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'

const content = useMarketingContent()
const page = computed(() => content.value.longTerm)
const cta = computed(() => content.value.pageCta)

useSeoMeta({
  title: () => page.value.seo.title,
  description: () => page.value.seo.description,
})
</script>

<template>
  <div>
    <MarketingHeroSection
      :eyebrow="page.hero.eyebrow"
      :title="page.title"
      :subtitle="page.paragraphs[0]"
      :primary-action="{ label: page.hero.primaryLabel, to: '/contact' }"
      :secondary-action="{ label: page.hero.secondaryLabel, to: '/cars' }"
    />

    <MarketingNarrativeSection
      :eyebrow="page.narrative.eyebrow"
      :title="page.narrative.title"
      :intro="page.narrative.intro"
      :paragraphs="page.paragraphs"
    />

    <section class="wrap pt-24 lg:pt-40">
      <MarketingBulletList :title="page.advantages.title" :items="page.advantages.items" />
    </section>

    <MarketingPageCta
      :title="cta.title"
      :description="cta.description"
      :primary-action="{ label: cta.primaryLabel, to: '/cars' }"
      :secondary-action="{ label: cta.secondaryLabel, to: '/contact' }"
    />
  </div>
</template>
