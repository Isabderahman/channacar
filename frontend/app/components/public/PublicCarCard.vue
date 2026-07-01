<script setup lang="ts">
import PublicCarWhatsAppButton from '~/components/public/PublicCarWhatsAppButton.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Car } from '~/types/entities'
import { formatCurrency, pickCarImagePath } from '~/utils/formatters'

const props = defineProps<{
  car: Car
}>()

const { mediaUrl } = useApi()
</script>

<template>
  <article class="ridex-surface group overflow-hidden p-2.5">
    <div class="aspect-[16/11] overflow-hidden rounded-[20px] bg-black/20">
      <img
        :src="mediaUrl(pickCarImagePath(car))"
        :alt="`${car.brand} ${car.name}`"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
      >
    </div>

    <div class="p-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-[0.78rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]">
            {{ car.category?.name ?? 'Fleet vehicle' }}
          </p>
          <h3 class="mt-2 font-heading text-[1.35rem] text-[var(--text-strong)]">
            {{ car.brand }} {{ car.name }}
          </h3>
        </div>
        <span class="rounded-full border border-[var(--primary-border)] px-3 py-1 text-[0.78rem] font-semibold text-[var(--primary)]">
          {{ car.year }}
        </span>
      </div>

      <div class="mt-4 grid gap-2 text-[0.9rem] text-[var(--text-muted)] sm:grid-cols-2">
        <div class="flex items-center gap-2">
          <BaseIcon name="people" :size="16" class="text-[var(--primary)]" />
          <span>{{ car.seats }} seats</span>
        </div>
        <div class="flex items-center gap-2">
          <BaseIcon name="transmission" :size="16" class="text-[var(--primary)]" />
          <span>{{ car.transmission }}</span>
        </div>
        <div class="flex items-center gap-2">
          <BaseIcon name="energy" :size="16" class="text-[var(--primary)]" />
          <span>{{ car.fuel }}</span>
        </div>
        <div class="flex items-center gap-2">
          <BaseIcon name="dashboard" :size="16" class="text-[var(--primary)]" />
          <span>{{ car.luggage }} luggage</span>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap items-center gap-3">
        <p class="mr-auto font-heading text-[0.95rem] text-[var(--text-strong)]">
          <strong class="text-[1.6rem] font-normal">
            {{ formatCurrency(car.base_price_per_day) }}
          </strong>
          / day
        </p>

        <PublicCarWhatsAppButton :car="car" size="icon" />

        <BaseButton :to="`/cars/${car.slug ?? car.id}`">
          <BaseIcon name="arrow-right" :size="18" />
          <span>View car</span>
        </BaseButton>
      </div>
    </div>
  </article>
</template>
