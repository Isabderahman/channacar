<script setup lang="ts">
import { computed } from 'vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { FeaturedCar } from '~/utils/home-content'

const props = withDefaults(
  defineProps<{
    car: FeaturedCar
    currency?: string
    favoriteAriaLabel: string
    locale?: string
    priceSuffix?: string
    rentLabel?: string
  }>(),
  {
    currency: 'USD',
    locale: 'en',
    priceSuffix: '/ month',
    rentLabel: 'Rent now',
  },
)

const formattedPrice = computed(() =>
  new Intl.NumberFormat(props.locale, {
    style: 'currency',
    currency: props.currency,
    maximumFractionDigits: 0,
  }).format(props.car.pricePerMonth),
)
</script>

<template>
  <article class="ridex-surface group p-2.5">
    <figure class="aspect-[3/2] overflow-hidden rounded-[18px] bg-black/20">
      <img
        :src="car.image"
        :alt="car.alt"
        class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.03]"
      >
    </figure>

    <div class="px-2.5 pb-2.5 pt-5">
      <div class="mb-[15px] flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h3 class="ridex-title-3 truncate">
            {{ car.name }}
          </h3>
        </div>

        <span
          class="shrink-0 rounded-[14px] border-2 border-dashed border-[var(--primary-border)] px-3 py-1 font-heading text-[0.875rem] font-semibold text-[var(--space-cadet)]"
        >
          {{ car.year }}
        </span>
      </div>

      <ul class="mb-[15px] grid gap-[15px] border-b border-[var(--surface-border)] pb-[15px] sm:grid-cols-2">
        <li
          v-for="spec in car.specs"
          :key="`${car.name}-${spec.label}`"
          class="flex items-center gap-2"
        >
          <BaseIcon
            :name="spec.icon"
            :size="20"
            class="text-[var(--carolina-blue)]"
          />
          <span class="text-[0.875rem] text-[var(--independence)]">
            {{ spec.label }}
          </span>
        </li>
      </ul>

      <div class="flex flex-wrap items-center gap-3">
        <p class="mr-auto font-heading text-[0.875rem] text-[var(--space-cadet)]">
          <strong class="text-[1.5rem] font-normal">
            {{ formattedPrice }}
          </strong>
          {{ priceSuffix }}
        </p>

        <BaseButton
          variant="secondary"
          size="icon"
          :aria-label="favoriteAriaLabel"
        >
          <BaseIcon name="heart" :size="18" />
        </BaseButton>

        <BaseButton size="sm" class="w-full sm:w-auto">
          {{ rentLabel }}
        </BaseButton>
      </div>
    </div>
  </article>
</template>
