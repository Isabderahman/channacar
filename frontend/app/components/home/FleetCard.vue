<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { Car } from '~/types/entities'
import { carFullName, carPhotoPath, formatAmount, rowIndex } from '~/utils/car-display'
import { toNumber } from '~/utils/formatters'

// Homepage fleet card (handoff): 4:3 photo with index + category · year
// overlaid, 4-column specs, refundable deposit, and the price — per day, or
// the total for the period once the visitor has picked both dates.
const props = defineProps<{
  car: Car
  index: number
  to: RouteLocationRaw
  /** Rental length from the hero dates; null until both are valid. */
  days: number | null
}>()

const { t } = useI18n()
const { mediaUrl } = useApi()

const photo = computed(() => {
  const path = carPhotoPath(props.car)
  return path ? mediaUrl(path) : null
})

const name = computed(() => carFullName(props.car))
const meta = computed(() =>
  [props.car.category?.name?.toUpperCase(), props.car.year].filter(Boolean).join(' · '),
)
const gearbox = computed(() => t(`site.fleet.transmission.${props.car.transmission}`))
const fuel = computed(() => t(`site.fleet.fuels.${props.car.fuel}`))

const specs = computed(() => [
  { label: t('site.fleet.seats'), value: props.car.seats },
  { label: t('site.fleet.gearbox'), value: gearbox.value },
  { label: t('site.fleet.fuel'), value: fuel.value },
  { label: t('site.fleet.bags'), value: props.car.luggage },
])

const specLine = computed(() =>
  t('site.fleet.specLine', {
    seats: props.car.seats,
    gearbox: gearbox.value,
    fuel: fuel.value,
    bags: props.car.luggage,
  }),
)

const deposit = computed(() => {
  const amount = toNumber(props.car.caution)
  return amount > 0 ? `${formatAmount(amount)} MAD` : t('site.fleet.onRequest')
})

const daily = computed(() => toNumber(props.car.base_price_per_day))
const daysLabel = computed(() => (props.days ? t('site.fleet.days', props.days) : ''))

const price = computed(() =>
  props.days
    ? {
        main: formatAmount(daily.value * props.days),
        unit: t('site.fleet.unitTotal', { days: daysLabel.value }),
        sub: t('site.fleet.sub', { price: formatAmount(daily.value), days: daysLabel.value }),
      }
    : {
        main: formatAmount(daily.value),
        unit: t('site.fleet.perDay'),
        sub: t('site.fleet.subPrompt'),
      },
)
</script>

<template>
  <li class="group relative flex flex-col border border-line bg-bg transition-colors hover:border-line-strong">
    <div class="photo-frame relative h-[200px] overflow-hidden md:aspect-[4/3] md:h-auto">
      <img
        v-if="photo"
        :src="photo"
        alt=""
        loading="lazy"
        class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
      >
      <span class="absolute start-3 top-3 bg-bg/80 px-1.5 py-0.5 font-plex text-[10px] text-muted md:start-4 md:top-4 md:text-[11px]">
        {{ rowIndex(index) }}
      </span>
      <span class="absolute end-3 top-3 bg-bg/80 px-1.5 py-0.5 font-plex text-[10px] tracking-[0.08em] text-accent md:end-4 md:top-4 md:text-[11px]">
        {{ meta }}
      </span>
    </div>

    <div class="flex flex-1 flex-col gap-3 p-4 md:gap-5 md:p-6">
      <h3 class="text-[22px] font-medium leading-[1.15] tracking-[-0.02em] md:text-[26px] md:leading-[1.1]">{{ name }}</h3>

      <!-- Mobile: one-line specs -->
      <p class="font-plex text-[12px] text-muted md:hidden">{{ specLine }}</p>

      <!-- Tablet / desktop: 4-column mini table -->
      <dl class="hidden grid-cols-4 gap-2 text-[15px] md:grid">
        <div v-for="spec in specs" :key="spec.label" class="min-w-0">
          <dt class="truncate font-plex text-[10px] uppercase tracking-[0.06em] text-muted">{{ spec.label }}</dt>
          <dd class="mt-1">{{ spec.value }}</dd>
        </div>
      </dl>

      <div class="flex items-center justify-between gap-3 bg-surface-2 px-3 py-2.5 text-[14px] md:px-3.5 md:py-3 md:text-[15px]">
        <span class="font-plex text-[10px] uppercase tracking-[0.06em] text-muted">{{ t('site.fleet.deposit') }}</span>
        <span class="whitespace-nowrap">{{ deposit }}</span>
      </div>

      <div class="mt-auto flex items-center justify-between gap-4 border-t border-line pt-3 md:pt-5">
        <div class="min-w-0">
          <p class="flex flex-wrap items-baseline gap-x-1.5">
            <span class="text-[22px] font-medium tracking-[-0.02em] md:text-[28px]">{{ price.main }}</span>
            <span class="font-plex text-[11px] text-muted">{{ price.unit }}</span>
          </p>
          <p class="mt-0.5 font-plex text-[10px] text-muted md:text-[11px]">{{ price.sub }}</p>
        </div>

        <NuxtLink
          :to="to"
          :aria-label="t('site.fleet.viewCar', { name })"
          class="inline-flex min-h-11 shrink-0 items-center gap-2.5 border border-line-strong px-3.5 text-[14px] font-medium transition-colors after:absolute after:inset-0 hover:border-ink group-hover:border-ink md:px-4"
        >
          {{ t('site.fleet.view') }}
          <SiteIcon name="arrow-up-right" :size="16" />
        </NuxtLink>
      </div>
    </div>
  </li>
</template>
