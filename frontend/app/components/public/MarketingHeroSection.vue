<script setup lang="ts">
import { computed, useSlots } from 'vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

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
  titleClass: 'text-[2.2rem] leading-[1.02] sm:text-[2.6rem] lg:text-[2.85rem] xl:text-[3.2rem]',
  titleWrapClass: 'max-w-[18rem] sm:max-w-[22rem] xl:max-w-[28rem]',
})

const slots = useSlots()
const { localeProperties } = useI18n()
const localePath = useLocalePath()

const isRtl = computed(() => localeProperties.value.dir === 'rtl')
const layoutOffsetClass = computed(() =>
  isRtl.value ? 'lg:pl-[18rem] xl:pl-[42rem]' : 'lg:pr-[18rem] xl:pr-[42rem]',
)
const imagePositionClass = computed(() =>
  isRtl.value ? 'left-0 bg-right' : 'right-0 bg-left',
)
const surfacePositionClass = computed(() =>
  isRtl.value
    ? 'lg:absolute lg:bottom-10 lg:right-0 lg:z-20 lg:mt-0 lg:w-[70%] lg:max-w-none'
    : 'lg:absolute lg:bottom-10 lg:left-0 lg:z-20 lg:mt-0 lg:w-[70%] lg:max-w-none',
)
const hasActionRow = computed(() => Boolean(props.primaryAction || props.secondaryAction))
const hasMetaSlot = computed(() => Boolean(slots.meta))
const hasSurface = computed(() => hasActionRow.value || hasMetaSlot.value || props.features.length > 0)
const surfaceGridClass = computed(() => {
  if (hasActionRow.value && (hasMetaSlot.value || props.features.length > 0)) {
    return 'lg:grid lg:grid-cols-[0.78fr_1.22fr] lg:items-center'
  }

  return ''
})
const featureGridClass = computed(() => {
  if (props.features.length === 1) {
    return 'sm:grid-cols-1'
  }

  return 'sm:grid-cols-2'
})
</script>

<template>
  <section
    v-intro
    :id="id"
    class="relative overflow-hidden pb-[50px] pt-[120px] lg:min-h-[85vh] lg:pt-[140px] xl:min-h-screen"
  >
    <div class="section-shell">
      <div class="relative lg:min-h-[640px]">
        <div :class="layoutOffsetClass">
          <div :class="titleWrapClass">
            <p data-intro class="text-[0.8rem] uppercase tracking-[0.24em] text-[var(--text-subtle)]">
              {{ eyebrow }}
            </p>

            <h1 data-intro class="ridex-title-1 mt-4" :class="titleClass">
              {{ title }}
            </h1>
          </div>

          <p
            v-if="subtitle"
            data-intro
            class="mb-[30px] mt-[15px] max-w-[48ch] text-[var(--independence)] leading-8"
          >
            {{ subtitle }}
          </p>
        </div>

        <div>
          <div
            data-intro-image
            class="absolute bottom-[50px] top-[20px] hidden w-[min(41vw,630px)] rounded-[30px] bg-cover shadow-[var(--shadow-1)] lg:block lg:z-0"
            :class="imagePositionClass"
            :style="{ backgroundImage: `url(${image})` }"
            :aria-label="imageAlt"
            role="img"
          />
        </div>

        <div
          v-if="hasSurface"
          data-intro
          class="ridex-surface relative isolate mt-8 overflow-hidden p-5 font-heading lg:p-6"
          :class="[surfaceGridClass, surfacePositionClass]"
        >
          <div
            class="pointer-events-none absolute -right-20 -top-24 -z-10 size-56 rounded-full bg-[var(--primary)] opacity-[0.12] blur-[70px]"
          />
          <div
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-px bg-[linear-gradient(to_right,transparent,color-mix(in_srgb,var(--primary)_60%,transparent),transparent)]"
          />
          <div
            v-if="hasActionRow"
            class="flex flex-wrap items-center gap-3"
          >
            <BaseButton v-if="primaryAction" :to="localePath(primaryAction.to)" size="lg">
              <BaseIcon name="arrow-right" :size="18" />
              <span>{{ primaryAction.label }}</span>
            </BaseButton>

            <BaseButton
              v-if="secondaryAction"
              :to="localePath(secondaryAction.to)"
              variant="ghost"
              size="lg"
            >
              <BaseIcon name="arrow-right" :size="18" />
              <span>{{ secondaryAction.label }}</span>
            </BaseButton>
          </div>

          <div
            v-if="hasMetaSlot || features.length"
            :class="hasActionRow ? 'mt-5 lg:mt-0' : ''"
          >
            <slot name="meta">
              <div class="grid gap-4" :class="featureGridClass">
                <article
                  v-for="feature in features"
                  :key="feature.title"
                  class="group relative overflow-hidden rounded-[20px] border border-[var(--surface-border)] bg-[linear-gradient(160deg,color-mix(in_srgb,var(--surface-2)_94%,var(--primary)),var(--surface-1))] p-5 transition duration-500 ease-out hover:-translate-y-1 hover:border-[var(--primary-border)] hover:shadow-[var(--shadow-2)]"
                >
                  <span
                    class="grid size-11 place-items-center rounded-[14px] bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)] transition duration-500 ease-out group-hover:scale-105"
                  >
                    <BaseIcon :name="feature.icon" :size="20" />
                  </span>
                  <p class="mt-4 text-[1rem] font-semibold text-[var(--space-cadet)]">
                    {{ feature.title }}
                  </p>
                  <p class="mt-2 text-[0.875rem] leading-6 text-[var(--independence)]">
                    {{ feature.description }}
                  </p>
                </article>
              </div>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
