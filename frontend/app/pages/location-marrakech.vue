<script setup lang="ts">
import MarketingBulletList from '~/components/public/MarketingBulletList.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingNarrativeSection from '~/components/public/MarketingNarrativeSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'
import { commonPageCta, marrakechContent } from '~/utils/marketing-content'

useSeoMeta({
  title: 'Location de voiture à Marrakech | ChanaaCar',
  description:
    'Pourquoi louer une voiture à Marrakech et comment ChanaaCar facilite vos déplacements au Maroc.',
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="Location Marrakech"
      :title="marrakechContent.title"
      :subtitle="marrakechContent.paragraphs[0]"
      :primary-action="{ label: 'Voir les voitures', to: '/cars' }"
      :secondary-action="{ label: 'Nous contacter', to: '/contact' }"
    />

    <MarketingNarrativeSection
      eyebrow="Déplacements à Marrakech"
      title="Circuler librement dans la ville et autour."
      intro="Une voiture vous permet d’enchaîner vos visites, rendez-vous et excursions avec plus de souplesse."
      :paragraphs="marrakechContent.paragraphs"
    />

    <section class="wrap pt-24 lg:pt-40">
      <MarketingBulletList
        title="Pourquoi louer une voiture à Marrakech ?"
        :items="marrakechContent.reasons"
      />
    </section>

    <MarketingPageCta v-bind="commonPageCta" />
  </div>
</template>
