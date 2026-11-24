<script setup lang="ts">
import StateNotice from '~/components/feedback/StateNotice.vue'
import ContactBlock from '~/components/site/ContactBlock.vue'
import FleetRow from '~/components/site/FleetRow.vue'
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { Car, Category, PaginatedResponse, ResourceResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'

const route = useRoute()
const localePath = useLocalePath()
const { publicApi } = useApi()
const { t, locale } = useI18n()
// resolveApiErrorMessage only has French and English wording of its own.
const apiLocale = computed(() => (locale.value === 'fr' ? 'fr' : 'en'))
const toast = useToast({ locale: apiLocale.value, errorTitle: t('fleetPage.toastTitle') })

// Place/dates chosen on the homepage travel on to the car pages.
const { hydrateFromQuery, toQuery } = useBookingDraft()
hydrateFromQuery(route.query)

const PER_PAGE = 12

const emptyFilters = () => ({
  category_id: '',
  fuel: '',
  max_price: '',
  min_price: '',
  search: '',
  seats: '',
  transmission: '',
})

// `filters` is what the visitor is typing; `applied` is what was last
// submitted and drives the (server-rendered) request.
const filters = reactive({
  ...emptyFilters(),
  category_id: typeof route.query.category_id === 'string' ? route.query.category_id : '',
})
const applied = reactive({ ...filters, page: 1 })

const { data: categoriesData } = await useAsyncData('fleet-categories', () =>
  publicApi<ResourceResponse<Category[]>>('/public/categories').catch(() => null),
)
const categories = computed(() => categoriesData.value?.data ?? [])

const {
  data: fleet,
  status,
  error,
} = await useAsyncData(
  'fleet',
  () =>
    publicApi<PaginatedResponse<Car>>('/public/cars', {
      query: {
        per_page: PER_PAGE,
        ...Object.fromEntries(
          Object.entries(applied).filter(([, value]) => String(value).trim().length > 0),
        ),
      },
    }),
  { watch: [applied] },
)

const cars = computed(() => fleet.value?.data ?? [])
const total = computed(() => fleet.value?.total ?? 0)
const currentPage = computed(() => fleet.value?.current_page ?? 1)
const lastPage = computed(() => fleet.value?.last_page ?? 1)
const pending = computed(() => status.value === 'pending')

const errorMessage = computed(() =>
  error.value
    ? resolveApiErrorMessage(error.value, {
        locale: apiLocale.value,
        fallback: t('fleetPage.error'),
      })
    : '',
)

const reportError = () => {
  if (errorMessage.value) {
    toast.error(errorMessage.value, t('fleetPage.loadTitle'))
  }
}
onMounted(reportError)
watch(errorMessage, reportError)

const listTop = ref<HTMLElement | null>(null)

const apply = (page = 1) => {
  Object.assign(applied, filters, { page })
}

const pickCategory = (id: string) => {
  filters.category_id = id
  apply(1)
}

const goToPage = (page: number) => {
  applied.page = page
  listTop.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const reset = () => {
  Object.assign(filters, emptyFilters())
  apply(1)
}

const carLink = (car: Car) => ({
  path: localePath(`/cars/${car.slug ?? car.id}`),
  query: toQuery(),
})

const transmissionOptions = computed(() =>
  ['auto', 'manual'].map((value) => ({ label: t(`car.info.gearbox.${value}`), value })),
)

const fuelOptions = computed(() =>
  ['diesel', 'petrol', 'hybrid', 'electric'].map((value) => ({ label: t(`site.fleet.fuels.${value}`), value })),
)

const cellClass =
  'flex min-w-0 flex-col gap-1.5 border-b border-line px-4 py-3.5 font-plex text-[10px] uppercase tracking-[0.08em] text-muted lg:gap-2 lg:px-5 lg:py-4 lg:text-[11px]'

// Per-field getters: Unhead ignores a getter passed as the whole input.
useSeoMeta({
  title: () => `${t('fleetPage.seo.title')} | ChanaaCar`,
  description: () => t('fleetPage.seo.description'),
})
</script>

<template>
  <div>
    <section class="wrap pt-8 lg:pt-14">
      <p class="intro mono-label mb-5 lg:mb-7 lg:text-[12px]">{{ t('fleetPage.eyebrow') }}</p>
      <div class="grid items-end gap-6 xl:grid-cols-[1.4fr_1fr] xl:gap-10">
        <h1 style="--d: 90" class="intro text-[48px] font-medium leading-[0.92] tracking-[-0.045em] md:text-[clamp(56px,7vw,104px)]">
          {{ t('fleetPage.title') }}
        </h1>
        <p style="--d: 200" class="intro max-w-[420px] text-[16px] text-ink-2 lg:text-[18px] lg:leading-[1.45] xl:pb-3">
          {{ t('fleetPage.lead') }}
        </p>
      </div>
    </section>

    <section ref="listTop" class="wrap scroll-mt-6 pt-16 lg:pt-24">
      <div v-inview data-anim="rule stagger" class="rule-b flex flex-col gap-5 pb-5 md:flex-row md:flex-wrap md:items-end md:justify-between md:gap-6 md:pb-8">
        <h2 class="display text-[44px] lg:text-[clamp(40px,5vw,72px)]">
          {{ t('site.fleet.title') }} <span class="font-plex text-[13px] tracking-normal text-muted lg:text-[15px]">{{ t('fleetPage.count', total) }}</span>
        </h2>

        <div
          v-if="categories.length"
          role="group"
          :aria-label="t('site.fleet.filterAria')"
          class="flex flex-wrap gap-1"
        >
          <button
            type="button"
            class="chip lg:text-[13px]"
            :aria-pressed="!applied.category_id ? 'true' : 'false'"
            @click="pickCategory('')"
          >
            {{ t('site.fleet.all') }}
          </button>
          <button
            v-for="category in categories"
            :key="category.id"
            type="button"
            class="chip lg:text-[13px]"
            :aria-pressed="applied.category_id === String(category.id) ? 'true' : 'false'"
            @click="pickCategory(String(category.id))"
          >
            {{ category.name }}
          </button>
        </div>
      </div>

      <form
        v-inview
        data-anim="rise"
        :aria-label="t('fleetPage.filtersAria')"
        class="mt-6 border border-b-0 border-line lg:mt-8"
        @submit.prevent="apply(1)"
      >
        <div class="grid grid-cols-2 lg:grid-cols-6">
          <label :class="cellClass" class="col-span-2 lg:border-e">
            {{ t('fleetPage.search') }}
            <input v-model="filters.search" type="search" :placeholder="t('fleetPage.searchPlaceholder')" class="field-bare text-[16px] placeholder:text-faint">
          </label>
          <label :class="cellClass" class="border-e">
            {{ t('site.fleet.gearbox') }}
            <select v-model="filters.transmission" class="field-bare text-[16px]">
              <option value="">{{ t('fleetPage.allFeminine') }}</option>
              <option v-for="option in transmissionOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
          </label>
          <label :class="cellClass" class="lg:border-e">
            {{ t('site.fleet.fuel') }}
            <select v-model="filters.fuel" class="field-bare text-[16px]">
              <option value="">{{ t('site.fleet.all') }}</option>
              <option v-for="option in fuelOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
          </label>
          <label :class="cellClass" class="border-e">
            {{ t('fleetPage.minPrice') }}
            <input v-model="filters.min_price" type="number" min="0" inputmode="numeric" :placeholder="t('car.currency')" class="field-bare text-[16px] placeholder:text-faint">
          </label>
          <label :class="cellClass">
            {{ t('fleetPage.maxPrice') }}
            <input v-model="filters.max_price" type="number" min="0" inputmode="numeric" :placeholder="t('car.currency')" class="field-bare text-[16px] placeholder:text-faint">
          </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line ps-4 lg:ps-5">
          <label class="flex items-center gap-3 py-2 font-plex text-[11px] uppercase tracking-[0.08em] text-muted">
            {{ t('fleetPage.minSeats') }}
            <input v-model="filters.seats" type="number" min="1" inputmode="numeric" class="field-bare w-16 text-[16px]">
          </label>
          <div class="flex items-center gap-5">
            <button type="button" class="text-link text-[14px]" @click="reset">{{ t('fleetPage.reset') }}</button>
            <button type="submit" class="btn btn-primary min-h-14">
              {{ t('fleetPage.apply') }}
              <SiteIcon name="arrow-right" />
            </button>
          </div>
        </div>
      </form>

      <StateNotice
        v-if="errorMessage"
        class="mt-8"
        :title="t('fleetPage.errorTitle')"
        :message="errorMessage"
        tone="error"
      />

      <ul v-else-if="pending" aria-busy="true" class="mt-4">
        <li v-for="n in 4" :key="n" class="flex animate-pulse items-center gap-10 border-b border-line py-7">
          <span class="hidden h-[120px] w-[200px] bg-surface md:block" />
          <span class="h-[72px] w-28 bg-surface md:hidden" />
          <span class="h-6 w-48 bg-surface" />
        </li>
      </ul>

      <ul v-else-if="cars.length" class="mt-4">
        <FleetRow
          v-for="(car, index) in cars"
          :key="car.id"
          :car="car"
          :index="(currentPage - 1) * PER_PAGE + index"
          :to="carLink(car)"
        />
      </ul>

      <div v-else class="border-b border-line py-12">
        <p class="text-[21px] font-medium">{{ t('fleetPage.emptyTitle') }}</p>
        <p class="mt-1 text-ink-2">{{ t('fleetPage.emptyText') }}</p>
      </div>

      <nav
        v-if="lastPage > 1"
        :aria-label="t('fleetPage.paginationAria')"
        class="mt-6 flex items-center justify-between gap-4"
      >
        <button
          type="button"
          class="btn btn-outline"
          :disabled="currentPage <= 1 || pending"
          @click="goToPage(currentPage - 1)"
        >
          <SiteIcon name="arrow-left" />
          {{ t('fleetPage.previous') }}
        </button>
        <span class="font-plex text-[12px] text-muted">{{ t('fleetPage.page', { current: currentPage, last: lastPage }) }}</span>
        <button
          type="button"
          class="btn btn-outline"
          :disabled="currentPage >= lastPage || pending"
          @click="goToPage(currentPage + 1)"
        >
          {{ t('fleetPage.next') }}
          <SiteIcon name="arrow-right" />
        </button>
      </nav>
    </section>

    <ContactBlock />
  </div>
</template>
