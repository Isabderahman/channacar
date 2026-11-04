<script setup lang="ts">
import HomeFleet from '~/components/home/HomeFleet.vue'
import HomeHero from '~/components/home/HomeHero.vue'
import HomeSteps from '~/components/home/HomeSteps.vue'
import HomeZones from '~/components/home/HomeZones.vue'
import ContactBlock from '~/components/site/ContactBlock.vue'
import type { Car, PaginatedResponse, PickupLocation, ResourceResponse } from '~/types/entities'

const { t } = useI18n()
const { publicApi } = useApi()

// Live fleet (active, non-maintenance cars) and agency locations, rendered
// server-side. Either one failing leaves its section empty instead of
// breaking the page.
const { data } = await useAsyncData('home', async () => {
  const [cars, locations] = await Promise.allSettled([
    publicApi<PaginatedResponse<Car>>('/public/cars', { query: { per_page: 50 } }),
    publicApi<ResourceResponse<PickupLocation[]>>('/public/locations'),
  ])

  return {
    cars: cars.status === 'fulfilled' ? cars.value.data : [],
    locations: locations.status === 'fulfilled' ? locations.value.data : [],
  }
})

const cars = computed(() => data.value?.cars ?? [])
const locations = computed(() => data.value?.locations ?? [])

// Per-field getters: Unhead ignores a getter passed as the whole input.
useSeoMeta({
  title: () => t('seo.home.title'),
  description: () => t('seo.home.description'),
})
</script>

<template>
  <div>
    <HomeHero :locations="locations" />
    <HomeFleet :cars="cars" />
    <HomeSteps />
    <HomeZones />
    <ContactBlock />
  </div>
</template>
