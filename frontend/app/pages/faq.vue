<script setup lang="ts">
import MarketingFaqList from '~/components/public/MarketingFaqList.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'
import { commonPageCta, faqContent } from '~/utils/marketing-content'

useSeoMeta({
  title: 'FAQ | ChanaaCar',
  description:
    'Questions fréquentes sur les documents, la caution, l’assurance, les annulations et la récupération du véhicule.',
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="FAQ"
      :title="faqContent.title"
      subtitle="Les réponses essentielles pour comprendre rapidement les conditions de location chez ChanaaCar."
      :primary-action="{ label: 'Contacter ChanaaCar', to: '/contact' }"
      :secondary-action="{ label: 'Voir les voitures', to: '/cars' }"
    />

    <section class="section-shell pb-12">
      <MarketingFaqList :title="faqContent.title" :items="faqContent.items" />
    </section>

    <MarketingPageCta v-bind="commonPageCta" />
  </div>
</template>
