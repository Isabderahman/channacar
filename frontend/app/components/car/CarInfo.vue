<script setup lang="ts">
import CarTags from '~/components/site/CarTags.vue'
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { CarBooking } from '~/composables/useCarBooking'
import type { Car } from '~/types/entities'
import { carMetaLine, formatAmount } from '~/utils/car-display'

// Info column: category · year, name, price/day, deposit, spec table, CTAs,
// live estimate, reassurance points and rental conditions.
const props = defineProps<{
  car: Car
  booking: CarBooking
  hasReviews: boolean
}>()

const { t, te } = useI18n()

const meta = computed(() =>
  carMetaLine(props.car),
)
const transmission = computed(() => (props.car.transmission === 'auto' ? 'auto' : 'manual'))
const gearbox = computed(() => t(`car.info.gearbox.${transmission.value}`))
const fuel = computed(() =>
  te(`site.fleet.fuels.${props.car.fuel}`) ? t(`site.fleet.fuels.${props.car.fuel}`) : props.car.fuel,
)
const yesNo = (value: boolean) => (value ? t('car.info.yes') : t('car.info.no'))

const specs = computed(() => [
  { k: t('site.fleet.seats'), v: props.car.seats },
  { k: t('car.info.doors'), v: props.car.doors },
  { k: t('site.fleet.bags'), v: props.car.luggage },
  { k: t('site.fleet.gearbox'), v: gearbox.value },
  { k: t('site.fleet.fuel'), v: fuel.value },
  { k: t('car.info.airCon'), v: yesNo(props.car.climatisation) },
  { k: t('car.info.gps'), v: yesNo(props.car.gps) },
  { k: t('car.info.year'), v: props.car.year },
])

// Compact one-line spec summary used on mobile.
const specLine = computed(() =>
  [
    t('car.info.seatsCount', { n: props.car.seats }),
    t('car.info.doorsCount', { n: props.car.doors }),
    t('car.info.bagsCount', { n: props.car.luggage }),
    t(`site.fleet.transmission.${transmission.value}`),
    fuel.value,
    props.car.climatisation ? t('car.info.airConShort') : null,
    props.car.gps ? t('car.info.gps') : null,
  ]
    .filter(Boolean)
    .join(' · '),
)

const reassurance = computed(() => [
  t('car.info.reassurance.delivery'),
  t('car.info.reassurance.support'),
  t('car.info.reassurance.pricing'),
  t('car.info.reassurance.deposit'),
])

const conditions = computed(() => [
  { k: t('car.info.conditions.ageLabel'), v: t('car.info.conditions.age') },
  { k: t('car.info.conditions.licenceLabel'), v: t('car.info.conditions.licence') },
  { k: t('car.info.conditions.documentsLabel'), v: t('car.info.conditions.documents') },
  { k: t('car.info.conditions.insuranceLabel'), v: t('car.info.conditions.insurance') },
])
</script>

<template>
  <div>
    <p class="intro mb-2 font-plex text-[11px] tracking-[0.08em] text-accent lg:mb-3 lg:text-[12px]">{{ meta }}</p>
    <h1 style="--d: 80" class="intro mb-5 text-[42px] font-medium leading-[0.95] tracking-[-0.045em] lg:mb-8 lg:text-[clamp(44px,5vw,72px)]">
      {{ car.brand }} {{ car.name }}
    </h1>
    <CarTags v-if="car.tags?.length" :tags="car.tags" style="--d: 120" class="intro -mt-2 mb-5 lg:-mt-4 lg:mb-7" />

    <div style="--d: 160" class="intro flex items-baseline justify-between gap-4 border-b border-t border-b-line border-t-ink py-4 lg:py-5">
      <p>
        <span class="text-[34px] font-medium tracking-[-0.03em] lg:text-[44px]">{{ formatAmount(car.base_price_per_day) }}</span>
        <span class="font-plex text-[12px] text-muted lg:text-[13px]">{{ ` ${t('site.fleet.perDay')}` }}</span>
      </p>
      <p v-if="booking.hasCaution.value" class="text-end font-plex text-[11px] text-muted lg:text-[12px]">
        {{ t('car.info.deposit') }}<br>
        <span class="text-[13px] text-ink lg:text-[14px]">{{ t('car.amount', { amount: booking.fmt(booking.caution.value) }) }}</span>
      </p>
    </div>

    <!-- Mobile: one-line specs -->
    <p style="--d: 220" class="intro border-b border-line py-2.5 font-plex text-[12px] text-ink-2 md:hidden">{{ specLine }}</p>

    <!-- Desktop: spec table -->
    <dl style="--d: 220" class="intro hidden grid-cols-2 md:grid">
      <div
        v-for="spec in specs"
        :key="spec.k"
        class="me-5 flex justify-between gap-3 border-b border-line py-3.5"
      >
        <dt class="font-plex text-[12px] text-muted">{{ spec.k }}</dt>
        <dd class="text-[15px]">{{ spec.v }}</dd>
      </div>
    </dl>

    <div style="--d: 300" class="intro mt-8 hidden gap-2 lg:flex">
      <a href="#reserver" class="btn btn-primary min-h-14 flex-auto">
        {{ t('car.info.book') }}
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

    <p style="--d: 340" class="intro mt-3.5 hidden text-[15px] text-ink-2 lg:block" aria-live="polite">
      {{ t('car.info.estimate', { amount: booking.fmt(booking.total.value), days: booking.daysLabel.value }) }}
    </p>

    <ul style="--d: 380" class="intro mt-0 grid grid-cols-2 gap-x-3.5 gap-y-2.5 pt-4 text-[13px] text-ink-2 lg:mt-6 lg:gap-x-5 lg:gap-y-3 lg:border-t lg:border-line lg:pt-5 lg:text-[14px]">
      <li v-for="point in reassurance" :key="point" class="flex gap-2.5">
        <span aria-hidden="true" class="text-accent">—</span>{{ point }}
      </li>
    </ul>

    <!-- Mobile: one-line conditions -->
    <p style="--d: 420" class="intro mt-4 bg-surface-2 px-3.5 py-3 text-[13px] text-ink-2 md:hidden">
      {{ t('car.info.conditions.summary') }}
    </p>

    <dl style="--d: 420" class="intro mt-5 hidden grid-cols-2 gap-x-5 gap-y-3 bg-surface-2 p-4 text-[14px] md:grid">
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
      <span><span class="text-accent">★</span> {{ t('car.info.reviews') }}</span>
      <SiteIcon name="arrow-right" :size="16" />
    </a>
  </div>
</template>
