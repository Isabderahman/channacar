<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { PickupLocation } from '~/types/entities'
import { heroImage } from '~/utils/home-content'

// Headline + full-width photo + booking strip joined to the photo's bottom
// edge. The strip writes into the shared booking draft: once both dates are
// set, the fleet cards below show the total for the period, and the place and
// dates follow the visitor to the car page.
const props = defineProps<{
  locations: PickupLocation[]
}>()

const { t } = useI18n()
const { draft, touched } = useBookingDraft()

// Default the pickup to the first agency location once they're loaded.
watch(
  () => props.locations,
  (locations) => {
    if (!draft.value.pickupLocationId && locations.length) {
      draft.value.pickupLocationId = String(locations[0]!.id)
    }
  },
  { immediate: true },
)

const markTouched = () => {
  touched.value = true
}

// "Voir les véhicules" → the fleet grid on this page, now priced for the dates.
const submit = () => {
  touched.value = true
  document.getElementById('flotte')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
</script>

<template>
  <section id="top" class="wrap pt-8 lg:pt-14">
    <div class="grid items-end gap-5 xl:grid-cols-2 xl:gap-10">
      <div>
        <p class="mono-label mb-5 lg:mb-7 lg:text-[12px]">
          <span class="lg:hidden">{{ t('site.home.hero.eyebrowShort') }}</span>
          <span class="hidden lg:inline">{{ t('site.home.hero.eyebrow') }}</span>
        </p>
        <h1 class="text-[54px] font-medium leading-[0.92] tracking-[-0.045em] md:text-[clamp(56px,8vw,128px)]">
          {{ t('site.home.hero.line1') }}<br>
          <span class="text-muted">{{ t('site.home.hero.line2Muted') }}</span>
          {{ t('site.home.hero.line2') }}
        </h1>
      </div>

      <div class="flex max-w-[420px] flex-col gap-6 xl:pb-3">
        <p class="text-[16px] text-ink-2 lg:text-[18px] lg:leading-[1.45]">{{ t('site.home.hero.lead') }}</p>
        <div class="hidden flex-wrap gap-x-8 gap-y-2 font-plex text-[12px] text-muted lg:flex">
          <span>{{ t('site.home.hero.delivery') }}</span>
          <span>{{ t('site.home.hero.pricing') }}</span>
          <span>{{ t('site.home.hero.support') }}</span>
        </div>
      </div>
    </div>
  </section>

  <div class="mt-8 lg:mx-auto lg:mt-14 lg:max-w-[1360px] lg:px-10">
    <div class="photo-frame h-[280px] overflow-hidden md:h-[420px] lg:h-[600px]">
      <img
        :src="heroImage"
        :alt="t('site.home.hero.photoAlt')"
        width="1537"
        height="1023"
        fetchpriority="high"
        class="size-full object-cover object-[center_65%]"
      >
    </div>

    <form
      id="reserver"
      :aria-label="t('site.home.search.aria')"
      class="mx-5 grid scroll-mt-6 grid-cols-2 border border-t-0 border-line bg-bg lg:mx-0 xl:grid-cols-[repeat(4,minmax(0,1fr))_minmax(220px,1fr)]"
      @submit.prevent="submit"
    >
      <label class="col-span-2 flex flex-col gap-1.5 border-b border-line px-4 py-3.5 font-plex text-[10px] uppercase tracking-[0.08em] text-muted md:col-span-1 md:border-e md:px-6 xl:gap-2.5 xl:border-b-0 xl:py-5 xl:text-[11px]">
        {{ t('site.home.search.pickup') }}
        <select v-model="draft.pickupLocationId" class="field-bare text-[16px] xl:text-[17px]" @change="markTouched">
          <option v-for="location in locations" :key="location.id" :value="String(location.id)">
            {{ location.name }}
          </option>
        </select>
      </label>

      <label class="col-span-2 flex flex-col gap-1.5 border-b border-line px-4 py-3.5 font-plex text-[10px] uppercase tracking-[0.08em] text-muted md:col-span-1 md:px-6 xl:gap-2.5 xl:border-b-0 xl:border-e xl:py-5 xl:text-[11px]">
        {{ t('site.home.search.dropoff') }}
        <select v-model="draft.dropoffLocationId" class="field-bare text-[16px] xl:text-[17px]" @change="markTouched">
          <option value="">{{ t('site.home.search.sameLocation') }}</option>
          <option v-for="location in locations" :key="location.id" :value="String(location.id)">
            {{ location.name }}
          </option>
        </select>
      </label>

      <label class="flex min-w-0 flex-col gap-1.5 border-e border-line px-4 py-3.5 font-plex text-[10px] uppercase tracking-[0.08em] text-muted md:px-6 xl:gap-2.5 xl:py-5 xl:text-[11px]">
        {{ t('site.home.search.from') }}
        <input
          v-model="draft.pickupAt"
          type="datetime-local"
          class="field-bare text-[13px] md:text-[16px] xl:text-[17px]"
          @change="markTouched"
        >
      </label>

      <label class="flex min-w-0 flex-col gap-1.5 px-4 py-3.5 font-plex text-[10px] uppercase tracking-[0.08em] text-muted md:px-6 xl:gap-2.5 xl:border-e xl:border-line xl:py-5 xl:text-[11px]">
        {{ t('site.home.search.to') }}
        <input
          v-model="draft.dropoffAt"
          type="datetime-local"
          :min="draft.pickupAt"
          class="field-bare text-[13px] md:text-[16px] xl:text-[17px]"
          @change="markTouched"
        >
      </label>

      <button
        type="submit"
        class="btn btn-primary col-span-2 min-h-[60px] px-4 text-[16px] md:min-h-16 md:px-6 xl:col-span-1 xl:min-h-24 xl:text-[17px]"
      >
        {{ t('site.home.search.submit') }}
        <SiteIcon name="arrow-right" :size="20" />
      </button>
    </form>
  </div>
</template>
