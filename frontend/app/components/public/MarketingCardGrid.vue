<script setup lang="ts">
import type { MarketingCard } from '~/utils/marketing-content'

defineProps<{
  cards: MarketingCard[]
  intro?: string
  title: string
}>()
</script>

<template>
  <div>
    <div class="flex flex-col gap-4 border-b border-ink pb-5 md:flex-row md:flex-wrap md:items-end md:justify-between md:gap-6 md:pb-8">
      <h2 class="display text-[40px] lg:text-[clamp(40px,5vw,72px)]">
        {{ title }}
      </h2>
      <p v-if="intro" class="max-w-[440px] text-[16px] text-ink-2 lg:text-[17px]">
        {{ intro }}
      </p>
    </div>

    <ol class="grid md:grid-cols-2 md:gap-x-10 xl:grid-cols-4">
      <li
        v-for="(card, index) in cards"
        :key="card.title"
        class="flex gap-5 border-b border-line py-6 xl:block xl:border-b-0 xl:pb-0 xl:pt-7"
      >
        <p class="font-plex text-[12px] text-accent xl:mb-14 xl:text-[13px]">
          {{ String(index + 1).padStart(2, '0') }}
        </p>
        <div>
          <h3 class="mb-1 text-[18px] font-medium xl:mb-2.5 xl:text-[21px]">
            {{ card.title }}
          </h3>
          <p class="text-[14px] text-muted xl:text-[15px]">
            {{ card.description }}
          </p>
        </div>
      </li>
    </ol>
  </div>
</template>
