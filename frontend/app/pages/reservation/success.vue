<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'

const route = useRoute()
const localePath = useLocalePath()

const reservationNumber = computed(() => String(route.query.reservation ?? 'Référence en attente'))
const carName = computed(() => String(route.query.car ?? 'votre véhicule sélectionné'))

useSeoMeta({
  title: 'Réservation confirmée | ChanaaCar',
  description: 'Page de confirmation après une demande de réservation.',
})
</script>

<template>
  <section class="wrap pb-[72px] pt-8 lg:pb-[120px] lg:pt-14">
    <p class="intro mono-label mb-5 lg:mb-7 lg:text-[12px]">Confirmation</p>

    <h1 style="--d: 90" class="intro max-w-[18ch] text-[44px] font-medium leading-[0.95] tracking-[-0.045em] md:text-[clamp(48px,6.2vw,96px)]">
      Votre demande pour {{ carName }} est bien reçue.
    </h1>

    <p style="--d: 200" class="intro mt-8 max-w-[560px] text-[16px] text-ink-2 lg:mt-12 lg:text-[18px] lg:leading-[1.45]">
      Nous avons enregistré votre demande de réservation. L’équipe ChanaaCar vous recontacte pour confirmer la disponibilité et finaliser la remise du véhicule.
    </p>

    <dl style="--d: 300" class="intro mt-12 grid border-t border-line md:grid-cols-3 lg:mt-16">
      <div class="border-b border-line py-5 md:border-e md:pe-6">
        <dt class="mono-label">Statut</dt>
        <dd class="mt-2 text-[18px] lg:text-[21px]">Demande enregistrée</dd>
      </div>
      <div class="border-b border-line py-5 md:border-e md:px-6">
        <dt class="mono-label">Référence</dt>
        <dd class="mt-2 font-plex text-[18px] text-accent lg:text-[21px]">{{ reservationNumber }}</dd>
      </div>
      <div class="border-b border-line py-5 md:ps-6">
        <dt class="mono-label">Suite</dt>
        <dd class="mt-2 text-[18px] lg:text-[21px]">Retour rapide de l’équipe</dd>
      </div>
    </dl>

    <div style="--d: 400" class="intro mt-10 flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:mt-14">
      <NuxtLink :to="localePath('/cars')" class="btn btn-primary min-h-14 sm:min-w-[280px]">
        Réserver un autre véhicule
        <SiteIcon name="arrow-right" />
      </NuxtLink>
      <NuxtLink :to="localePath('/contact')" class="btn btn-outline min-h-14 sm:min-w-[280px]">
        Contacter l’équipe
        <SiteIcon name="arrow-right" />
      </NuxtLink>
    </div>
  </section>
</template>
