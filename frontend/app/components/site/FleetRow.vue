<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { Car } from '~/types/entities'
import { carFullName, carPhotoPath, formatAmount, rowIndex } from '~/utils/car-display'

// One numbered row of the fleet "spec sheet" (homepage + /cars). The whole row
// is clickable through a stretched link on the car name.
const props = defineProps<{
  car: Car
  index: number
  to: RouteLocationRaw
}>()

const { t } = useI18n()
const { mediaUrl } = useApi()

const photo = computed(() => {
  const path = carPhotoPath(props.car)
  return path ? mediaUrl(path) : null
})

const name = computed(() => carFullName(props.car))
const number = computed(() => rowIndex(props.index))
const meta = computed(() =>
  [props.car.category?.name?.toUpperCase(), props.car.year].filter(Boolean).join(' · '),
)
const gearbox = computed(() => t(`site.fleet.transmission.${props.car.transmission}`))
const fuel = computed(() => t(`site.fleet.fuels.${props.car.fuel}`))
const price = computed(() => formatAmount(props.car.base_price_per_day))

const specs = computed(() => [
  { label: t('site.fleet.seats'), value: props.car.seats },
  { label: t('site.fleet.gearbox'), value: gearbox.value },
  { label: t('site.fleet.fuel'), value: fuel.value },
  { label: t('site.fleet.bags'), value: props.car.luggage },
])

const specLine = computed(() =>
  [
    t('site.fleet.seatsShort', { n: props.car.seats }),
    gearbox.value,
    fuel.value,
    t('site.fleet.bagsShort', { n: props.car.luggage }),
  ].join(' · '),
)
</script>

<template>
  <li
    class="group relative border-b border-line has-[a:focus-visible]:outline-2 has-[a:focus-visible]:outline-offset-2 has-[a:focus-visible]:outline-accent"
  >
    <!-- Mobile -->
    <div class="flex flex-col gap-3.5 py-5 md:hidden">
      <div class="flex items-center gap-3.5">
        <div class="photo-frame h-[72px] w-28 shrink-0 overflow-hidden">
          <img v-if="photo" :src="photo" alt="" loading="lazy" class="size-full object-cover">
        </div>
        <div class="min-w-0">
          <p class="mb-1 font-plex text-[11px] text-accent">{{ number }} · {{ meta }}</p>
          <h3 class="text-[20px] font-medium leading-[1.15] tracking-[-0.02em]">
            <NuxtLink :to="to" class="after:absolute after:inset-0 focus-visible:outline-none">
              {{ name }}
            </NuxtLink>
          </h3>
        </div>
      </div>
      <div class="flex items-center justify-between gap-3">
        <p class="font-plex text-[12px] text-muted">{{ specLine }}</p>
        <p class="whitespace-nowrap">
          <span class="text-[20px] font-medium">{{ price }}</span>
          <span class="font-plex text-[11px] text-muted"> {{ t('site.fleet.perDayShort') }}</span>
        </p>
      </div>
    </div>

    <!-- Tablet / desktop -->
    <div class="hidden flex-wrap items-center gap-x-6 gap-y-6 py-7 md:flex xl:gap-x-10">
      <span class="w-10 shrink-0 font-plex text-[13px] text-muted">{{ number }}</span>

      <div class="photo-frame h-24 w-40 shrink-0 overflow-hidden xl:h-[120px] xl:w-[200px]">
        <img
          v-if="photo"
          :src="photo"
          alt=""
          loading="lazy"
          class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
        >
      </div>

      <div class="min-w-0 flex-[1_1_220px] xl:flex-[1_1_260px]">
        <p class="mb-1.5 font-plex text-[12px] tracking-[0.08em] text-accent">{{ meta }}</p>
        <h3 class="text-[28px] font-medium leading-[1.1] tracking-[-0.02em]">
          <NuxtLink :to="to" class="after:absolute after:inset-0 focus-visible:outline-none">
            {{ name }}
          </NuxtLink>
        </h3>
      </div>

      <dl class="grid min-w-0 flex-[1_1_240px] grid-cols-4 gap-2 text-[15px] xl:flex-[1_1_300px]">
        <div v-for="spec in specs" :key="spec.label" class="min-w-0">
          <dt class="truncate font-plex text-[11px] uppercase tracking-[0.06em] text-muted">{{ spec.label }}</dt>
          <dd class="mt-1">{{ spec.value }}</dd>
        </div>
      </dl>

      <div class="ms-auto flex shrink-0 items-center gap-7">
        <p class="text-end">
          <span class="text-[28px] font-medium tracking-[-0.02em]">{{ price }}</span>
          <span class="block font-plex text-[11px] text-muted">{{ t('site.fleet.perDay') }}</span>
        </p>
        <span
          aria-hidden="true"
          class="flex size-12 items-center justify-center border border-line-strong transition-colors group-hover:border-accent group-hover:text-accent"
        >
          <SiteIcon name="arrow-up-right" />
        </span>
      </div>
    </div>
  </li>
</template>
