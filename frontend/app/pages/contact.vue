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

    <section class="section-shell section-block">
      <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
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
