<script setup lang="ts">
import MarketingBulletList from '~/components/public/MarketingBulletList.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingNarrativeSection from '~/components/public/MarketingNarrativeSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'
import { airportContent, commonPageCta } from '~/utils/marketing-content'

useSeoMeta({
  title: 'Location voiture aéroport Marrakech Menara | ChanaaCar',
  description:
    "Service de location et de remise rapide de véhicule à l'aéroport Marrakech Menara.",
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="Aéroport Marrakech Menara"
      :title="airportContent.title"
      :subtitle="airportContent.paragraphs[0]"
      :primary-action="{ label: 'Réserver un véhicule', to: '/cars' }"
      :secondary-action="{ label: 'Appeler maintenant', to: '/contact' }"
      title-wrap-class="max-w-[20rem] sm:max-w-[28rem] lg:max-w-[34rem] xl:max-w-[38rem]"
      title-class="text-[1.95rem] leading-[1.04] sm:text-[2.35rem] lg:text-[2.6rem] xl:text-[2.9rem]"
    />

    <MarketingNarrativeSection
      eyebrow="Arrivée à Marrakech"
      title="Un retrait plus simple dès la sortie de l’aéroport."
      intro="Dès l’atterrissage, vous évitez l’attente et démarrez votre séjour avec un véhicule prêt à partir."
      :paragraphs="airportContent.paragraphs"
    />

    <section class="section-shell pb-12">
      <MarketingBulletList title="Avantages" :items="airportContent.advantages" />
    </section>

    <MarketingPageCta v-bind="commonPageCta" />
  </div>
</template>
