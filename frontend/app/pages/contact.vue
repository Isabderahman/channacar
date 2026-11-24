<script setup lang="ts">
import MarketingContactForm from '~/components/public/MarketingContactForm.vue'
import MarketingContactInfo from '~/components/public/MarketingContactInfo.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'

const content = useMarketingContent()
const page = computed(() => content.value.contact)
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
      :subtitle="page.subtitle"
      :primary-action="{ label: page.hero.primaryLabel, to: '/cars' }"
      :secondary-action="{ label: page.hero.secondaryLabel, to: '/faq' }"
    />

    <section class="wrap pt-24 lg:pt-40">
      <div class="grid gap-16 xl:grid-cols-2 xl:gap-16">
        <MarketingContactInfo
          :title="page.title"
          :subtitle="page.subtitle"
          :groups="page.groups"
        />
        <MarketingContactForm />
      </div>
    </section>

    <MarketingPageCta
      :title="cta.title"
      :description="cta.description"
      :primary-action="{ label: cta.primaryLabel, to: '/cars' }"
      :secondary-action="{ label: cta.secondaryLabel, to: '/contact' }"
    />
  </div>
</template>
