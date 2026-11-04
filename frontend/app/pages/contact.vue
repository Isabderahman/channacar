<script setup lang="ts">
import MarketingContactForm from '~/components/public/MarketingContactForm.vue'
import MarketingContactInfo from '~/components/public/MarketingContactInfo.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import MarketingPageCta from '~/components/public/MarketingPageCta.vue'
import { commonPageCta, contactContent } from '~/utils/marketing-content'

useSeoMeta({
  title: 'Contact | ChanaaCar',
  description:
    "Coordonnées, disponibilité et formulaire de demande pour contacter l'équipe ChanaaCar.",
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="Contact"
      :title="contactContent.title"
      :subtitle="contactContent.subtitle"
      :primary-action="{ label: 'Réserver maintenant', to: '/cars' }"
      :secondary-action="{ label: 'Questions fréquentes', to: '/faq' }"
    />

    <section class="wrap pt-24 lg:pt-40">
      <div class="grid gap-16 xl:grid-cols-2 xl:gap-16">
        <MarketingContactInfo
          :title="contactContent.title"
          :subtitle="contactContent.subtitle"
          :groups="contactContent.contacts"
        />
        <MarketingContactForm />
      </div>
    </section>

    <MarketingPageCta v-bind="commonPageCta" />
  </div>
</template>
