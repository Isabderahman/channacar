<script setup lang="ts">
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

const route = useRoute()

const reservationNumber = computed(() => String(route.query.reservation ?? 'Référence en attente'))
const carName = computed(() => String(route.query.car ?? 'votre véhicule sélectionné'))

useSeoMeta({
  title: 'Réservation confirmée | ChanaaCar',
  description: 'Page de confirmation après une demande de réservation.',
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="Confirmation"
      :title="`Votre demande pour ${carName} est bien reçue.`"
      subtitle="Nous avons enregistré votre demande de réservation. L’équipe ChanaaCar vous recontacte pour confirmer la disponibilité et finaliser la remise du véhicule."
      :primary-action="{ label: 'Réserver un autre véhicule', to: '/cars' }"
      :secondary-action="{ label: 'Contacter l’équipe', to: '/contact' }"
    >
      <template #meta>
        <div class="grid gap-4 sm:grid-cols-3">
          <article class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--alice-blue-2)] p-5">
            <BaseIcon name="check-circle" :size="20" class="text-[var(--carolina-blue)]" />
            <p class="mt-3 text-[0.76rem] uppercase tracking-[0.2em] text-[var(--manatee)]">
              Statut
            </p>
            <p class="mt-2 text-[1rem] font-semibold text-[var(--space-cadet)]">
              Demande enregistrée
            </p>
          </article>

          <article class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--alice-blue-2)] p-5">
            <p class="text-[0.76rem] uppercase tracking-[0.2em] text-[var(--manatee)]">
              Référence
            </p>
            <p class="mt-3 text-[1rem] font-semibold text-[var(--space-cadet)]">
              {{ reservationNumber }}
            </p>
          </article>

          <article class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--alice-blue-2)] p-5">
            <p class="text-[0.76rem] uppercase tracking-[0.2em] text-[var(--manatee)]">
              Suite
            </p>
            <p class="mt-3 text-[1rem] font-semibold text-[var(--space-cadet)]">
              Retour rapide de l’équipe
            </p>
          </article>
        </div>
      </template>
    </MarketingHeroSection>
  </div>
</template>
