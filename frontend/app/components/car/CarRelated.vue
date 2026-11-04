<script setup lang="ts">
import type { Car } from '~/types/entities'
import { carFullName, carPhotoPath, formatAmount } from '~/utils/car-display'

// "Vous aimerez aussi": up to 3 cars, same category first.
defineProps<{ cars: Car[] }>()

const localePath = useLocalePath()
const { mediaUrl } = useApi()
const { toQuery } = useBookingDraft()

const photo = (car: Car) => {
  const path = carPhotoPath(car)
  return path ? mediaUrl(path) : null
}
const meta = (car: Car) => [car.category?.name?.toUpperCase(), car.year].filter(Boolean).join(' · ')
const link = (car: Car) => ({ path: localePath(`/cars/${car.slug ?? car.id}`), query: toQuery() })
</script>

<template>
  <section v-if="cars.length" class="wrap mt-24 lg:mt-40">
    <div class="flex items-end justify-between gap-6 border-b border-ink pb-5 lg:pb-6">
      <h2 class="text-[28px] font-medium tracking-[-0.035em] lg:text-[40px]">Vous aimerez aussi</h2>
      <NuxtLink :to="localePath('/cars')" class="text-link shrink-0 text-[15px]">Toute la flotte</NuxtLink>
    </div>

    <ul class="grid sm:grid-cols-2 lg:grid-cols-3">
      <li v-for="car in cars" :key="car.id" class="border-b border-line">
        <NuxtLink :to="link(car)" class="group flex flex-col gap-4 py-6 sm:pe-6">
          <div class="photo-frame h-[200px] overflow-hidden">
            <img
              v-if="photo(car)"
              :src="photo(car)!"
              alt=""
              loading="lazy"
              class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
            >
          </div>
          <div class="flex items-baseline justify-between gap-3">
            <div class="min-w-0">
              <p class="mb-1 font-plex text-[11px] text-accent">{{ meta(car) }}</p>
              <p class="text-[21px] font-medium tracking-[-0.02em] transition-colors group-hover:text-accent">
                {{ carFullName(car) }}
              </p>
            </div>
            <p class="whitespace-nowrap">
              <span class="text-[21px] font-medium">{{ formatAmount(car.base_price_per_day) }}</span>
              <span class="font-plex text-[11px] text-muted"> MAD/j</span>
            </p>
          </div>
        </NuxtLink>
      </li>
    </ul>
  </section>
</template>
