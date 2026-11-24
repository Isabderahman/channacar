<script setup lang="ts">
import CarBookingForm from '~/components/car/CarBookingForm.vue'
import CarGallery from '~/components/car/CarGallery.vue'
import CarInfo from '~/components/car/CarInfo.vue'
import CarRelated from '~/components/car/CarRelated.vue'
import CarReviewsFaq from '~/components/car/CarReviewsFaq.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import SiteIcon from '~/components/site/SiteIcon.vue'
import type {
  Car,
  Extra,
  PaginatedResponse,
  PickupLocation,
  ResourceResponse,
  Testimonial,
} from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { carFullName, formatAmount } from '~/utils/car-display'
import { formatCurrency, pickCarImagePath, toNumber } from '~/utils/formatters'

const route = useRoute()
const localePath = useLocalePath()
const { mediaUrl, publicApi } = useApi()
const { t, te, locale } = useI18n()
// resolveApiErrorMessage only has French and English wording of its own.
const apiLocale = computed(() => (locale.value === 'fr' ? 'fr' : 'en'))
const toast = useToast({ locale: apiLocale.value, errorTitle: t('car.errors.title') })

const slug = String(route.params.slug)

// Server-rendered so the car's title, description and structured data are
// in the HTML. Only the car itself is required; the rest degrades to empty.
const { data, error } = await useAsyncData(`car-${slug}`, async () => {
  const car = (await publicApi<ResourceResponse<Car>>(`/public/cars/${slug}`)).data

  const [extras, locations, testimonials, sameCategory, others] = await Promise.allSettled([
    publicApi<ResourceResponse<Extra[]>>('/public/extras'),
    publicApi<ResourceResponse<PickupLocation[]>>('/public/locations'),
    publicApi<PaginatedResponse<Testimonial>>('/public/testimonials', { query: { per_page: 3 } }),
    publicApi<PaginatedResponse<Car>>('/public/cars', { query: { category_id: car.category_id, per_page: 4 } }),
    publicApi<PaginatedResponse<Car>>('/public/cars', { query: { per_page: 6 } }),
  ])

  const settled = <T,>(result: PromiseSettledResult<T>) =>
    result.status === 'fulfilled' ? result.value : null

  // Same category first, topped up with the rest of the fleet.
  const related = [...(settled(sameCategory)?.data ?? []), ...(settled(others)?.data ?? [])]
    .filter((entry, index, list) => entry.id !== car.id && list.findIndex((other) => other.id === entry.id) === index)
    .slice(0, 3)

  return {
    car,
    extras: settled(extras)?.data ?? [],
    locations: settled(locations)?.data ?? [],
    testimonials: settled(testimonials)?.data ?? [],
    related,
  }
})

const errorMessage = computed(() =>
  error.value
    ? resolveApiErrorMessage(error.value, {
        locale: apiLocale.value,
        fallback: t('car.errors.load'),
      })
    : '',
)

if (error.value && import.meta.server && error.value.statusCode === 404) {
  const event = useRequestEvent()
  if (event) {
    setResponseStatus(event, 404)
  }
}

onMounted(() => {
  if (errorMessage.value) {
    toast.error(errorMessage.value, t('car.errors.loadTitle'))
  }
})

const car = computed(() => data.value?.car ?? null)
const extras = computed(() => data.value?.extras ?? [])
const locations = computed(() => data.value?.locations ?? [])

// Make the floating WhatsApp button enquire about this specific car.
useCarWhatsappFloat(car)

const booking = data.value ? useCarBooking(car as Ref<Car>, extras, locations) : null

const photoPath = computed(() => pickCarImagePath(car.value))
const summaryPhoto = computed(() => (photoPath.value ? mediaUrl(photoPath.value) : null))
const heroImage = computed(() => mediaUrl(photoPath.value))

const contactLink = computed(() => ({
  path: localePath('/contact'),
  query: car.value ? { vehicule: `${carFullName(car.value)} (${car.value.year})` } : undefined,
}))

const fuelLabel = (fuel: string) => (te(`site.fleet.fuels.${fuel}`) ? t(`site.fleet.fuels.${fuel}`) : fuel)

const carDescription = computed(() => {
  const c = car.value
  if (!c) {
    return t('car.seo.fallbackDescription')
  }
  const features = [
    t(`car.seo.transmission.${c.transmission === 'auto' ? 'auto' : 'manual'}`),
    fuelLabel(c.fuel).toLowerCase(),
    c.climatisation ? t('car.seo.airCon') : null,
    c.gps ? 'GPS' : null,
  ]
    .filter(Boolean)
    .join(locale.value === 'ar' ? '، ' : ', ')
  // `price` keeps the French currency format ("1.800 MAD"); the other
  // languages use the bare `amount` and place the currency themselves.
  return t('car.seo.description', {
    name: carFullName(c),
    year: c.year,
    seats: c.seats,
    doors: c.doors,
    features,
    price: formatCurrency(c.base_price_per_day, 'MAD', 'fr-MA'),
    amount: formatAmount(c.base_price_per_day),
  })
})

// Per-field getters: Unhead ignores a getter passed as the whole input.
useSeoMeta({
  title: () =>
    car.value ? `${car.value.brand} ${car.value.name} | ChanaaCar` : `${t('car.seo.fallbackTitle')} | ChanaaCar`,
  description: () => carDescription.value,
  ogImage: () => heroImage.value,
})

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
          fuelType: fuelLabel(car.value.fuel),
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
            { name: t('car.breadcrumb.home'), item: localePath('/') },
            { name: t('site.nav.cars'), item: localePath('/cars') },
            {
              name: `${car.value.brand} ${car.value.name}`,
              item: localePath(`/cars/${car.value.slug ?? route.params.slug}`),
            },
          ],
        }),
      ]
    : [],
)
</script>

<template>
  <div>
    <div v-if="!car || !booking" class="wrap pb-16 pt-10">
      <NuxtLink :to="localePath('/cars')" class="btn btn-outline">
        <SiteIcon name="arrow-left" />
        {{ t('site.actions.back') }}
      </NuxtLink>

      <StateNotice
        class="mt-6"
        :title="t('car.errors.unavailable')"
        :message="errorMessage || t('car.errors.load')"
        tone="error"
      />
    </div>

    <template v-else>
      <nav
        :aria-label="t('car.breadcrumb.aria')"
        class="intro wrap mb-8 hidden pt-6 font-plex text-[12px] text-muted lg:block"
      >
        <NuxtLink :to="localePath('/cars')" class="transition-colors hover:text-ink">{{ t('site.nav.cars') }}</NuxtLink>
        <template v-if="car.category">
          <span aria-hidden="true"> / </span>
          <NuxtLink
            :to="{ path: localePath('/cars'), query: { category_id: String(car.category.id) } }"
            class="transition-colors hover:text-ink"
          >
            {{ car.category.name }}
          </NuxtLink>
        </template>
        <span aria-hidden="true"> / </span>
        <span class="text-ink" aria-current="page">{{ car.brand }} {{ car.name }}</span>
      </nav>

      <section class="flex flex-col lg:mx-auto lg:max-w-[1360px] lg:flex-row lg:flex-wrap lg:items-start lg:gap-10 lg:px-10 xl:gap-12">
        <CarGallery :car="car" class="min-w-0 lg:flex-[1_1_520px] xl:flex-[1_1_640px]" />
        <div class="min-w-0 px-5 pt-7 lg:flex-[1_1_360px] lg:px-0 lg:pt-0 xl:flex-[1_1_400px]">
          <CarInfo :car="car" :booking="booking" :has-reviews="(data?.testimonials.length ?? 0) > 0" />
        </div>
      </section>

      <CarBookingForm
        :car="car"
        :booking="booking"
        :extras="extras"
        :locations="locations"
        :photo="summaryPhoto"
      />

      <CarReviewsFaq
        :testimonials="data?.testimonials ?? []"
        :caution="toNumber(car.caution)"
        :whatsapp-link="booking.whatsappLink.value"
        :contact-link="contactLink"
      />

      <CarRelated :cars="data?.related ?? []" />
    </template>
  </div>
</template>
