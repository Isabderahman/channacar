<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { CarBooking } from '~/composables/useCarBooking'
import type { Car } from '~/types/entities'
import { formatAmount } from '~/utils/car-display'

// Info column: category · year, name, price/day, deposit, spec table, CTAs,
// live estimate, reassurance points and rental conditions.
const props = defineProps<{
  car: Car
  booking: CarBooking
  hasReviews: boolean
}>()

const fuelLabels: Record<string, string> = {
  diesel: 'Diesel',
  electric: 'Électrique',
  hybrid: 'Hybride',
  petrol: 'Essence',
}

const meta = computed(() =>
  [props.car.category?.name?.toUpperCase(), props.car.year].filter(Boolean).join(' · '),
)
const gearbox = computed(() => (props.car.transmission === 'auto' ? 'Automatique' : 'Manuelle'))
const fuel = computed(() => fuelLabels[props.car.fuel] ?? props.car.fuel)

const specs = computed(() => [
  { k: 'Places', v: props.car.seats },
  { k: 'Portes', v: props.car.doors },
  { k: 'Bagages', v: props.car.luggage },
  { k: 'Boîte', v: gearbox.value },
  { k: 'Carburant', v: fuel.value },
  { k: 'Climatisation', v: props.car.climatisation ? 'Oui' : 'Non' },
  { k: 'GPS', v: props.car.gps ? 'Oui' : 'Non' },
  { k: 'Année', v: props.car.year },
])

// Compact one-line spec summary used on mobile.
const specLine = computed(() =>
  [
    `${props.car.seats} places`,
    `${props.car.doors} portes`,
    `${props.car.luggage} bagages`,
    props.car.transmission === 'auto' ? 'Auto' : 'Manuelle',
    fuel.value,
    props.car.climatisation ? 'Clim' : null,
    props.car.gps ? 'GPS' : null,
  ]
    .filter(Boolean)
    .join(' · '),
)

const reassurance = [
  'Livraison aéroport Menara ou hôtel',
  'Assistance 24h/24, 7j/7',
  'Tarifs transparents',
  'Caution remboursable',
]

const conditions = [
  { k: 'Âge minimum', v: '21 ans' },
  { k: 'Permis', v: '1 an minimum' },
  { k: 'Documents', v: 'CIN ou passeport + permis' },
  { k: 'Assurance', v: 'Incluse selon contrat' },
]
</script>

<template>
  <div>
    <p class="mb-2 font-plex text-[11px] tracking-[0.08em] text-accent lg:mb-3 lg:text-[12px]">{{ meta }}</p>
    <h1 class="mb-5 text-[42px] font-medium leading-[0.95] tracking-[-0.045em] lg:mb-8 lg:text-[clamp(44px,5vw,72px)]">
      {{ car.brand }} {{ car.name }}
    </h1>

    <div class="flex items-baseline justify-between gap-4 border-b border-t border-b-line border-t-ink py-4 lg:py-5">
      <p>
        <span class="text-[34px] font-medium tracking-[-0.03em] lg:text-[44px]">{{ formatAmount(car.base_price_per_day) }}</span>
        <span class="font-plex text-[12px] text-muted lg:text-[13px]"> MAD / jour</span>
      </p>
      <p v-if="booking.hasCaution.value" class="text-end font-plex text-[11px] text-muted lg:text-[12px]">
        Caution<br>
        <span class="text-[13px] text-ink lg:text-[14px]">{{ booking.fmt(booking.caution.value) }} MAD</span>
      </p>
    </div>

    <!-- Mobile: one-line specs -->
    <p class="border-b border-line py-2.5 font-plex text-[12px] text-ink-2 md:hidden">{{ specLine }}</p>

    <!-- Desktop: spec table -->
    <dl class="hidden grid-cols-2 md:grid">
      <div
        v-for="spec in specs"
        :key="spec.k"
        class="me-5 flex justify-between gap-3 border-b border-line py-3.5"
      >
        <dt class="font-plex text-[12px] text-muted">{{ spec.k }}</dt>
        <dd class="text-[15px]">{{ spec.v }}</dd>
      </div>
    </dl>

    <div class="mt-8 hidden gap-2 lg:flex">
      <a href="#reserver" class="btn btn-primary min-h-14 flex-auto">
        Réserver ce véhicule
        <SiteIcon name="arrow-down" />
      </a>
      <a
        :href="booking.whatsappLink.value"
        target="_blank"
        rel="noopener"
        class="btn btn-outline min-h-14 flex-none"
      >
        WhatsApp
      </a>
    </div>

    <p class="mt-3.5 hidden text-[15px] text-ink-2 lg:block" aria-live="polite">
      Votre estimation : {{ booking.fmt(booking.total.value) }} MAD pour {{ booking.daysLabel.value }}.
    </p>

    <ul class="mt-0 grid grid-cols-2 gap-x-3.5 gap-y-2.5 pt-4 text-[13px] text-ink-2 lg:mt-6 lg:gap-x-5 lg:gap-y-3 lg:border-t lg:border-line lg:pt-5 lg:text-[14px]">
      <li v-for="point in reassurance" :key="point" class="flex gap-2.5">
        <span aria-hidden="true" class="text-accent">—</span>{{ point }}
      </li>
    </ul>

    <!-- Mobile: one-line conditions -->
    <p class="mt-4 bg-surface-2 px-3.5 py-3 text-[13px] text-ink-2 md:hidden">
      21 ans minimum · permis de plus d’1 an · CIN ou passeport · assurance incluse selon contrat
    </p>

    <dl class="mt-5 hidden grid-cols-2 gap-x-5 gap-y-3 bg-surface-2 p-4 text-[14px] md:grid">
      <div v-for="condition in conditions" :key="condition.k">
        <dt class="font-plex text-[11px] uppercase tracking-[0.06em] text-muted">{{ condition.k }}</dt>
        <dd class="mt-0.5">{{ condition.v }}</dd>
      </div>
    </dl>

    <a
      v-if="hasReviews"
      href="#avis"
      class="mt-3 flex min-h-12 items-center justify-between border border-line-strong px-3.5 text-[14px] transition-colors hover:border-ink lg:hidden"
    >
      <span><span class="text-accent">★</span> Ce qu’en disent nos clients</span>
      <SiteIcon name="arrow-right" :size="16" />
    </a>
  </div>
</template>
