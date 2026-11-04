<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'

interface ActionLink {
  label: string
  to: string
}

defineProps<{
  description: string
  primaryAction: ActionLink
  secondaryAction: ActionLink
  title: string
}>()

const localePath = useLocalePath()
</script>

<template>
  <section class="wrap pb-[72px] pt-24 lg:pb-[120px] lg:pt-40">
    <div v-inview data-anim="photo" class="flex flex-col gap-6 bg-accent px-5 py-7 text-on-accent lg:flex-row lg:flex-wrap lg:items-end lg:justify-between lg:gap-10 lg:p-14">
      <div class="lg:flex-[1_1_520px]">
        <h2 class="display text-[36px] leading-[0.98] lg:text-[clamp(36px,4.5vw,64px)]">
          {{ title }}
        </h2>
        <p class="mt-4 max-w-[52ch] text-[15px] lg:mt-6 lg:text-[17px]">
          {{ description }}
        </p>
      </div>

      <div class="flex flex-col gap-2 lg:min-w-[280px]">
        <NuxtLink
          :to="localePath(primaryAction.to)"
          class="btn min-h-[54px] bg-bg px-4 text-ink transition-colors hover:text-accent lg:min-h-14 lg:px-5"
        >
          {{ primaryAction.label }}
          <SiteIcon name="arrow-right" />
        </NuxtLink>
        <NuxtLink
          :to="localePath(secondaryAction.to)"
          class="btn min-h-[54px] border-on-accent px-4 text-on-accent hover:bg-bg hover:text-ink lg:min-h-14 lg:px-5"
        >
          {{ secondaryAction.label }}
          <SiteIcon name="arrow-right" />
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
