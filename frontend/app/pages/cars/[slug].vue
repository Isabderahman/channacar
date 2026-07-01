<script setup lang="ts">
import AppPanel from '~/components/data/AppPanel.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import CarDetailSkeleton from '~/components/public/CarDetailSkeleton.vue'
import ReservationForm from '~/components/public/ReservationForm.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Car, Extra, PickupLocation, ResourceResponse } from '~/types/entities'
import { formatCurrency, pickCarImagePath } from '~/utils/formatters'

const route = useRoute()
const { mediaUrl, publicApi } = useApi()

const pending = ref(true)
const errorMessage = ref('')
const car = ref<Car | null>(null)
const extras = ref<Extra[]>([])
const locations = ref<PickupLocation[]>([])
const activeImage = ref(0)

const loadCar = async () => {
  pending.value = true
  errorMessage.value = ''
  activeImage.value = 0

  try {
    const carSlug = String(route.params.slug)
    const [carResponse, extrasResponse, locationsResponse] = await Promise.all([
      publicApi<ResourceResponse<Car>>(`/public/cars/${carSlug}`),
      publicApi<ResourceResponse<Extra[]>>('/public/extras'),
      publicApi<ResourceResponse<PickupLocation[]>>('/public/locations'),
    ])

    car.value = carResponse.data
    extras.value = extrasResponse.data
    locations.value = locationsResponse.data
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Le véhicule demandé n’a pas pu être chargé.'
  } finally {
    pending.value = false
  }
}

const heroImage = computed(() => mediaUrl(pickCarImagePath(car.value)))
const gallery = computed(() => car.value?.images ?? [])
const mainImage = computed(() =>
  gallery.value.length ? mediaUrl(gallery.value[activeImage.value]?.path) : heroImage.value,
)

const stepImage = (delta: number) => {
  const count = gallery.value.length
  if (count > 1) {
    activeImage.value = (activeImage.value + delta + count) % count
  }
}

// BaseIcon needs an IconName; spec icons are plain strings.
const asIcon = (value: string): any => value

const fuelLabels: Record<string, string> = {
  diesel: 'Diesel',
  electric: 'Électrique',
  hybrid: 'Hybride',
  petrol: 'Essence',
}

const heroSpecs = computed(() => {
  const c = car.value
  if (!c) {
    return []
  }

  return [
    { icon: 'payment', label: 'Prix / jour', value: formatCurrency(c.base_price_per_day, 'MAD', 'fr-MA') },
    { icon: 'car', label: 'Catégorie', value: c.category?.name ?? 'N/A' },
    { icon: 'people', label: 'Places', value: `${c.seats}` },
    { icon: 'door', label: 'Portes', value: `${c.doors}` },
    { icon: 'luggage', label: 'Bagages', value: `${c.luggage}` },
    { icon: 'transmission', label: 'Boîte', value: c.transmission === 'auto' ? 'Auto' : 'Manuelle' },
    { icon: 'fuel', label: 'Carburant', value: fuelLabels[c.fuel] ?? c.fuel },
    { icon: 'snowflake', label: 'Climatisation', value: c.climatisation ? 'Oui' : 'Non' },
    { icon: 'map-pin', label: 'GPS', value: c.gps ? 'Oui' : 'Non' },
  ]
})

watch(
  () => route.params.slug,
  () => {
    void loadCar()
  },
  { immediate: true },
)

const carDescription = computed(() => {
  const c = car.value
  if (!c) {
    return 'Détail du véhicule, galerie, caractéristiques et formulaire de réservation.'
  }
  const transmission = c.transmission === 'auto' ? 'boîte automatique' : 'boîte manuelle'
  const fuel = (fuelLabels[c.fuel] ?? c.fuel).toLowerCase()
  return `Louez la ${c.brand} ${c.name} (${c.year}) à Marrakech : ${c.seats} places, ${c.doors} portes, ${transmission}, ${fuel}${c.climatisation ? ', climatisation' : ''}${c.gps ? ', GPS' : ''}. À partir de ${formatCurrency(c.base_price_per_day, 'MAD', 'fr-MA')} / jour. Réservation simple chez ChannaCar.`
})

useSeoMeta(() => ({
  title: car.value ? `${car.value.brand} ${car.value.name} | ChanaaCar` : 'Détail véhicule | ChanaaCar',
  description: carDescription.value,
  ogImage: heroImage.value,
}))

// Per-car structured data: a Car/Product with a per-day rental Offer, plus a
// breadcrumb trail. Powers product rich results and gives AI engines (GEO)
// concrete, citeable facts (model, year, specs, price).
useSchemaOrg(() =>
  car.value
    ? [
        defineProduct({
          '@type': ['Product', 'Car'],
          name: `${car.value.brand} ${car.value.name}`,
          description: carDescription.value,
          image: heroImage.value,
          brand: { '@type': 'Brand', name: car.value.brand },
          model: car.value.name,
          vehicleModelDate: String(car.value.year),
          vehicleTransmission:
            car.value.transmission === 'auto' ? 'AutomaticTransmission' : 'ManualTransmission',
          fuelType: fuelLabels[car.value.fuel] ?? car.value.fuel,
          vehicleSeatingCapacity: car.value.seats,
          numberOfDoors: car.value.doors,
          offers: {
            '@type': 'Offer',
            priceCurrency: 'MAD',
            price: Number(car.value.base_price_per_day),
            availability:
              car.value.status === 'available'
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            priceSpecification: {
              '@type': 'UnitPriceSpecification',
              priceCurrency: 'MAD',
              price: Number(car.value.base_price_per_day),
              unitCode: 'DAY',
              referenceQuantity: {
                '@type': 'QuantitativeValue',
                value: 1,
                unitCode: 'DAY',
              },
            },
          },
        }),
        defineBreadcrumb({
          itemListElement: [
            { name: 'Accueil', item: '/' },
            { name: 'Nos véhicules', item: '/cars' },
            {
              name: `${car.value.brand} ${car.value.name}`,
              item: `/cars/${car.value.slug ?? route.params.slug}`,
            },
          ],
        }),
      ]
    : [],
)
</script>

<template>
  <div>
    <div v-if="errorMessage || pending" class="section-shell pb-12 pt-[118px] md:pt-[138px]">
      <BaseButton to="/cars" variant="secondary">
        <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
        <span>Retour à la flotte</span>
      </BaseButton>

      <StateNotice
        v-if="errorMessage"
        class="mt-6"
        title="Détail indisponible"
        :message="errorMessage"
        tone="error"
      />

      <CarDetailSkeleton v-else class="mt-6" />
    </div>

    <template v-else-if="car">
      <section v-intro class="relative overflow-hidden pb-10 pt-[104px] lg:pb-14 lg:pt-[136px]">
        <div class="section-shell">
          <BaseButton to="/cars" variant="secondary" class="mb-6">
            <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
            <span>Retour à la flotte</span>
          </BaseButton>

          <div class="grid gap-8 lg:grid-cols-[1fr_1.08fr] lg:items-start">
            <!-- Info + specs (after the image on mobile) -->
            <div class="order-2 lg:order-1">
              <p data-intro class="text-[0.8rem] uppercase tracking-[0.24em] text-[var(--text-subtle)]">
                Détail véhicule
              </p>
              <h1 data-intro class="ridex-title-1 mt-3 text-[2.1rem] leading-[1.05] sm:text-[2.5rem] xl:text-[3rem]">
                {{ car.brand }} {{ car.name }}
              </h1>
              <p data-intro class="mt-4 max-w-[48ch] text-[var(--independence)] leading-7">
                Consultez les caractéristiques, basculez entre les photos, puis envoyez votre demande de réservation.
              </p>

              <div data-intro class="mt-6 grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                <div
                  v-for="spec in heroSpecs"
                  :key="spec.label"
                  class="flex items-center gap-2.5 rounded-[14px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-3 py-2.5"
                >
                  <span class="grid size-9 shrink-0 place-items-center rounded-[10px] bg-[var(--primary-soft)] text-[var(--primary)]">
                    <BaseIcon :name="asIcon(spec.icon)" :size="16" />
                  </span>
                  <div class="min-w-0">
                    <p class="text-[0.6rem] uppercase leading-tight tracking-[0.1em] text-[var(--text-subtle)]">
                      {{ spec.label }}
                    </p>
                    <p class="truncate text-[0.85rem] font-semibold leading-tight text-[var(--text-strong)]">
                      {{ spec.value }}
                    </p>
                  </div>
                </div>
              </div>

              <div data-intro class="mt-6 flex flex-wrap gap-3">
                <BaseButton href="#reservation-form" size="lg">
                  <BaseIcon name="check-circle" :size="18" />
                  <span>Réserver</span>
                </BaseButton>
                <BaseButton to="/contact" variant="ghost" size="lg">
                  <BaseIcon name="mail" :size="18" />
                  <span>Contacter ChanaaCar</span>
                </BaseButton>
              </div>
            </div>

            <!-- Image gallery: shown first on mobile, switchable -->
            <div class="order-1 lg:order-2">
              <div
                data-intro-image
                class="relative overflow-hidden rounded-[24px] border border-[var(--surface-border)] bg-[var(--surface-2)] shadow-[var(--shadow-1)]"
              >
                <img
                  :src="mainImage"
                  :alt="`${car.brand} ${car.name}`"
                  class="aspect-[4/3] w-full object-cover sm:aspect-[16/10]"
                >

                <template v-if="gallery.length > 1">
                  <button
                    type="button"
                    class="absolute left-3 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-black/55 text-white backdrop-blur transition hover:bg-black/75"
                    aria-label="Image précédente"
                    @click="stepImage(-1)"
                  >
                    <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
                  </button>
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-black/55 text-white backdrop-blur transition hover:bg-black/75"
                    aria-label="Image suivante"
                    @click="stepImage(1)"
                  >
                    <BaseIcon name="arrow-right" :size="18" />
                  </button>
                  <span class="absolute bottom-3 right-3 rounded-full bg-black/55 px-2.5 py-1 text-[0.72rem] font-semibold text-white backdrop-blur">
                    {{ activeImage + 1 }} / {{ gallery.length }}
                  </span>
                </template>
              </div>

              <div
                v-if="gallery.length > 1"
                class="-mx-1 mt-3 flex snap-x gap-3 overflow-x-auto px-1 pb-1 [scrollbar-width:thin]"
              >
                <button
                  v-for="(image, index) in gallery"
                  :key="image.id"
                  type="button"
                  class="shrink-0 snap-start overflow-hidden rounded-[16px] border-2 transition duration-300 ease-out"
                  :class="index === activeImage ? 'border-[var(--primary)]' : 'border-[var(--surface-border)] hover:border-[var(--primary-border)]'"
                  :aria-label="`Image ${index + 1}`"
                  @click="activeImage = index"
                >
                  <img :src="mediaUrl(image.path)" alt="" class="h-16 w-24 object-cover sm:h-20 sm:w-28">
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section-shell pb-12">
        <AppPanel id="reservation-form" padding="lg">
          <div class="mb-6">
            <h2 class="font-heading text-[1.6rem] text-[var(--text-strong)]">Réservez ce véhicule</h2>
            <p class="mt-1 text-[0.94rem] text-[var(--text-muted)]">
              Renseignez vos informations, ajoutez vos options, et obtenez une estimation instantanée.
            </p>
          </div>

          <ReservationForm
            :car="car"
            :extras="extras"
            :locations="locations"
          />
        </AppPanel>
      </section>
    </template>
  </div>
</template>
