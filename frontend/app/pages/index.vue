<script setup lang="ts">
import SectionHeading from '~/components/common/SectionHeading.vue'
import SiteFooter from '~/components/common/SiteFooter.vue'
import SiteHeader from '~/components/common/SiteHeader.vue'
import WhatsAppFloat from '~/components/common/WhatsAppFloat.vue'
import PublicCarCard from '~/components/public/PublicCarCard.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import HeroSection from '~/components/sections/HeroSection.vue'
import StepCard from '~/components/steps/StepCard.vue'
import { useLandingContent } from '~/composables/useLandingContent'
import type { Car, PaginatedResponse } from '~/types/entities'

definePageMeta({
  layout: false,
})

const { t } = useI18n()
const { publicApi } = useApi()
const localePath = useLocalePath()

const {
  heroContent,
  heroSearchFields,
  labels,
  stepCards,
} = useLandingContent()

// Featured vehicles now come from the live fleet (admin/seeded cars) instead of
// a static list. Pulls the most recent active, visible cars from the public API.
const { data: featuredResponse } = await useAsyncData('home-featured-cars', () =>
  publicApi<PaginatedResponse<Car>>('/public/cars', { query: { per_page: 6 } }),
)

const featuredCars = computed(() => featuredResponse.value?.data ?? [])

useSeoMeta(() => ({
  title: t('seo.home.title'),
  description: t('seo.home.description'),
}))
</script>

<template>
  <div class="page-shell">
    <SiteHeader />

    <main>
      <HeroSection
        :title="heroContent.title"
        :description="heroContent.description"
        :image="heroContent.image"
        :action-label="heroContent.actionLabel"
        :fields="heroSearchFields"
      />

      <section id="featured-cars" class="pb-[50px]">
        <div class="section-shell">
          <div v-reveal>
            <SectionHeading
              :title="labels.featuredCarsTitle"
              :action-label="labels.featuredCarsAction"
              :action-href="localePath('/cars')"
            />
          </div>

          <div
            v-if="featuredCars.length"
            v-reveal="{ stagger: 0.12, y: 40 }"
            class="grid gap-[30px] md:grid-cols-2 xl:grid-cols-3"
          >
            <PublicCarCard
              v-for="car in featuredCars"
              :key="car.id"
              :car="car"
            />
          </div>

          <StateNotice
            v-else
            title="Flotte bientôt disponible"
            message="Nos véhicules seront affichés ici dès leur mise en ligne. Contactez-nous pour connaître les disponibilités."
          />
        </div>
      </section>

      <section id="steps" class="section-block">
        <div class="section-shell">
          <div v-reveal>
            <SectionHeading :title="labels.stepsTitle" />
          </div>

          <div v-reveal="{ stagger: 0.1, y: 36 }" class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <StepCard
              v-for="step in stepCards"
              :key="step.title"
              :step="step"
            />
          </div>
        </div>
      </section>
    </main>

    <SiteFooter />
    <WhatsAppFloat />
  </div>
</template>
