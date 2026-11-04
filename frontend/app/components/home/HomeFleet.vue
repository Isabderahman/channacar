<script setup lang="ts">
import FleetCard from '~/components/home/FleetCard.vue'
import { draftDays } from '~/composables/useBookingDraft'
import type { Car } from '~/types/entities'

// "La flotte": card grid with category filters built from the live fleet
// (label + count per category present in the data). Once both hero dates are
// set, every card shows the total for the period.
const props = withDefaults(
  defineProps<{
    cars: Car[]
    limit?: number
  }>(),
  { limit: 6 },
)

const { t, locale } = useI18n()
const localePath = useLocalePath()
const { draft, toQuery } = useBookingDraft()

const selected = ref<number | 'all'>('all')

const filters = computed(() => {
  const counts = new Map<number, { name: string; count: number }>()

  for (const car of props.cars) {
    if (!car.category) {
      continue
    }

    const entry = counts.get(car.category.id)
    if (entry) {
      entry.count += 1
    } else {
      counts.set(car.category.id, { name: car.category.name, count: 1 })
    }
  }

  return [
    { key: 'all' as const, label: t('site.fleet.all'), count: props.cars.length },
    ...[...counts.entries()]
      .sort((a, b) => b[1].count - a[1].count)
      .map(([id, entry]) => ({ key: id, label: entry.name, count: entry.count })),
  ]
})

const visibleCars = computed(() =>
  (selected.value === 'all'
    ? props.cars
    : props.cars.filter((car) => car.category_id === selected.value)
  ).slice(0, props.limit),
)

// ── Period from the hero booking strip ───────────────────────────────────
const days = computed(() => draftDays(draft.value))
const hasBothDates = computed(() => Boolean(draft.value.pickupAt && draft.value.dropoffAt))

const dateLocales: Record<string, string> = { fr: 'fr-FR', en: 'en-GB', es: 'es-ES', ar: 'ar-MA' }
const shortDate = (value: string) =>
  new Intl.DateTimeFormat(dateLocales[locale.value] ?? 'fr-FR', { day: 'numeric', month: 'short' }).format(
    new Date(value),
  )

const dateNote = computed(() => {
  if (days.value) {
    return {
      tone: 'valid' as const,
      text: t('site.fleet.dateNoteValid', {
        days: t('site.fleet.days', days.value),
        from: shortDate(draft.value.pickupAt),
        to: shortDate(draft.value.dropoffAt),
      }),
    }
  }

  if (hasBothDates.value) {
    return { tone: 'error' as const, text: t('site.fleet.dateNoteInvalid') }
  }

  return { tone: 'prompt' as const, text: t('site.fleet.dateNotePrompt') }
})

const carLink = (car: Car) => ({
  path: localePath(`/cars/${car.slug ?? car.id}`),
  query: toQuery(),
})

const fleetLink = computed(() => ({
  path: localePath('/cars'),
  query: selected.value === 'all' ? undefined : { category_id: String(selected.value) },
}))
</script>

<template>
  <section id="flotte" class="wrap scroll-mt-6 pt-24 lg:pt-40">
    <div v-inview data-anim="rule stagger" class="rule-b flex flex-col gap-5 pb-5 md:flex-row md:flex-wrap md:items-end md:justify-between md:gap-6 md:pb-8">
      <h2 class="display text-[44px] lg:text-[clamp(40px,5vw,72px)]">{{ t('site.fleet.title') }}</h2>

      <div
        v-if="filters.length > 1"
        role="group"
        :aria-label="t('site.fleet.filterAria')"
        class="flex flex-wrap gap-1"
      >
        <button
          v-for="filter in filters"
          :key="filter.key"
          type="button"
          class="chip lg:text-[13px]"
          :aria-pressed="selected === filter.key ? 'true' : 'false'"
          @click="selected = filter.key"
        >
          {{ filter.label }} <span class="opacity-55">{{ filter.count }}</span>
        </button>
      </div>
    </div>

    <p
      v-inview
      data-anim="rise"
      aria-live="polite"
      class="mt-4 font-plex text-[12px] lg:mt-6 lg:text-[13px]"
      :class="{
        'text-accent': dateNote.tone === 'valid',
        'text-danger': dateNote.tone === 'error',
        'text-muted': dateNote.tone === 'prompt',
      }"
    >
      {{ dateNote.text }}
    </p>

    <ul
      v-if="visibleCars.length"
      :key="String(selected)"
      v-inview
      data-anim="stagger"
      class="mt-4 grid gap-4 md:grid-cols-2 lg:mt-6 lg:gap-6 xl:grid-cols-3">
      <FleetCard
        v-for="(car, index) in visibleCars"
        :key="car.id"
        :car="car"
        :index="index"
        :days="days"
        :to="carLink(car)"
      />
    </ul>

    <p v-else class="mt-6 border-y border-line py-10 text-ink-2">{{ t('site.fleet.empty') }}</p>

    <p class="mt-5 lg:mt-8 lg:text-end">
      <NuxtLink :to="fleetLink" class="text-link text-[15px]">{{ t('site.fleet.viewAll') }}</NuxtLink>
    </p>
  </section>
</template>
