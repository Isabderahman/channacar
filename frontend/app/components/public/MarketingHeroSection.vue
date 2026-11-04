<script setup lang="ts">
import { computed, useSlots } from 'vue'
import SiteIcon from '~/components/site/SiteIcon.vue'

interface ActionLink {
  label: string
  to: string
}

interface HeroFeature {
  description: string
  icon:
    | 'calendar'
    | 'car'
    | 'check-circle'
    | 'clock'
    | 'dashboard'
    | 'image'
    | 'mail'
    | 'map-pin'
    | 'people'
    | 'phone'
    | 'star'
    | 'transmission'
  title: string
}

interface Props {
  eyebrow?: string
  /** `icon` is kept for API compatibility; the redesign shows numbered rows instead. */
  features?: HeroFeature[]
  id?: string
  image?: string
  imageAlt?: string
  primaryAction?: ActionLink
  secondaryAction?: ActionLink
  subtitle?: string
  title: string
  titleClass?: string
  titleWrapClass?: string
}

const props = withDefaults(defineProps<Props>(), {
  eyebrow: 'ChanaaCar',
  features: () => [
    {
      icon: 'check-circle',
      title: 'Tarifs transparents',
      description: 'Une offre claire, des conditions visibles, et une réservation plus directe.',
    },
    {
      icon: 'map-pin',
      title: 'Marrakech et aéroport',
      description: 'Des parcours conçus pour les clients qui arrivent, repartent, ou circulent au Maroc.',
    },
  ],
  id: undefined,
  image: '/images/hero-banner.jpg',
  imageAlt: 'ChanaaCar',
  primaryAction: undefined,
  secondaryAction: undefined,
  subtitle: undefined,
  // `ch` resolves against the heading's own font size, so the measure lives here.
  titleClass: 'max-w-[18ch] text-[44px] md:text-[clamp(48px,6.2vw,96px)]',
  titleWrapClass: '',
})

const slots = useSlots()
const localePath = useLocalePath()

const hasActionRow = computed(() => Boolean(props.primaryAction || props.secondaryAction))
const hasMetaSlot = computed(() => Boolean(slots.meta))
</script>

<template>
  <section :id="id" class="wrap pt-8 lg:pt-14">
    <p class="intro mono-label mb-5 lg:mb-7 lg:text-[12px]">
      {{ eyebrow }}
    </p>

    <div :class="titleWrapClass">
      <h1 style="--d: 90" class="intro font-medium leading-[0.95] tracking-[-0.045em]" :class="titleClass">
        {{ title }}
      </h1>
    </div>

    <div
      v-if="subtitle || hasActionRow"
      style="--d: 220"
      class="intro mt-8 grid items-end gap-6 lg:mt-12 lg:grid-cols-2 lg:gap-10"
    >
      <p v-if="subtitle" class="max-w-[560px] text-[16px] text-ink-2 lg:text-[18px] lg:leading-[1.45]">
        {{ subtitle }}
      </p>

      <div
        v-if="hasActionRow"
        class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end"
        :class="subtitle ? '' : 'lg:col-start-2'"
      >
        <NuxtLink
          v-if="primaryAction"
          :to="localePath(primaryAction.to)"
          class="btn btn-primary min-h-14 sm:min-w-[240px]"
        >
          {{ primaryAction.label }}
          <SiteIcon name="arrow-right" />
        </NuxtLink>
        <NuxtLink
          v-if="secondaryAction"
          :to="localePath(secondaryAction.to)"
          class="btn btn-outline min-h-14 sm:min-w-[240px]"
        >
          {{ secondaryAction.label }}
          <SiteIcon name="arrow-right" />
        </NuxtLink>
      </div>
    </div>
  </section>

  <div v-if="image" class="mt-10 lg:mx-auto lg:mt-14 lg:max-w-[1360px] lg:px-10">
    <div class="photo-frame relative h-[220px] overflow-hidden md:h-[340px] lg:h-[440px]">
      <div class="intro-photo absolute inset-0" style="--d: 260">
        <img
          v-parallax="0.06"
          :src="image"
          :alt="imageAlt"
          fetchpriority="high"
          class="absolute inset-x-0 -top-[10%] h-[120%] w-full object-cover object-[center_65%]"
        >
      </div>
    </div>
  </div>

  <div v-if="hasMetaSlot || features.length" class="wrap">
    <slot name="meta">
      <ol v-inview data-anim="stagger" class="grid md:grid-cols-2 md:gap-x-10">
        <li
          v-for="(feature, index) in features"
          :key="feature.title"
          class="flex gap-5 border-b border-line py-6"
        >
          <span class="font-plex text-[12px] text-accent lg:text-[13px]">{{ String(index + 1).padStart(2, '0') }}</span>
          <div>
            <p class="mb-1 text-[18px] font-medium lg:text-[21px]">{{ feature.title }}</p>
            <p class="text-[14px] text-muted lg:text-[15px]">{{ feature.description }}</p>
          </div>
        </li>
      </ol>
    </slot>
  </div>
</template>
