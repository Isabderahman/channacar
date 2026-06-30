<script setup lang="ts">
import MarketingCardGrid from '~/components/public/MarketingCardGrid.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingNarrativeSection from '~/components/public/MarketingNarrativeSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'
import { aboutContent, commonPageCta } from '~/utils/marketing-content'

useSeoMeta({
  title: 'À propos de ChanaaCar',
  description:
    "Découvrez la mission, l'expérience et les valeurs de l'agence ChanaaCar.",
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="À propos"
      :title="aboutContent.title"
      :subtitle="aboutContent.paragraphs[0]"
      :primary-action="{ label: 'Voir la flotte', to: '/cars' }"
      :secondary-action="{ label: 'Parler à l’équipe', to: '/contact' }"
    />

    <MarketingNarrativeSection
      eyebrow="L’agence"
      title="Une équipe locale qui mise sur la clarté et la disponibilité."
      intro="Nous accompagnons les voyageurs, familles et professionnels avec un service simple, rapide et lisible."
      :paragraphs="aboutContent.paragraphs"
    />

    <section class="section-shell pb-12">
      <MarketingCardGrid title="Nos valeurs" :cards="aboutContent.values" />
    </section>

    <MarketingPageCta v-bind="commonPageCta" />
  </div>
</template>
