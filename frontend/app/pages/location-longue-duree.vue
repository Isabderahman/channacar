<script setup lang="ts">
import MarketingBulletList from '~/components/public/MarketingBulletList.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingNarrativeSection from '~/components/public/MarketingNarrativeSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'
import { commonPageCta, longTermContent } from '~/utils/marketing-content'

useSeoMeta({
  title: 'Location longue durée au Maroc | ChanaaCar',
  description:
    'Solutions de location longue durée pour particuliers, professionnels, expatriés et entreprises.',
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="Longue durée"
      :title="longTermContent.title"
      :subtitle="longTermContent.paragraphs[0]"
      :primary-action="{ label: 'Demander une offre', to: '/contact' }"
      :secondary-action="{ label: 'Découvrir la flotte', to: '/cars' }"
    />

    <MarketingNarrativeSection
      eyebrow="Offres longue durée"
      title="Une solution plus stable pour les séjours longs et les besoins pro."
      intro="Pour plusieurs semaines ou plusieurs mois, l’objectif est de garder une solution fiable avec un cadre clair."
      :paragraphs="longTermContent.paragraphs"
    />

    <section class="section-shell pb-12">
      <MarketingBulletList title="Avantages" :items="longTermContent.advantages" />
    </section>

    <MarketingPageCta v-bind="commonPageCta" />
  </div>
</template>
