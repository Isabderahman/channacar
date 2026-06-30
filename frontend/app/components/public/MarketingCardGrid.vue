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
    <h2 v-reveal class="ridex-title-2">
      {{ title }}
    </h2>
    <p v-if="intro" v-reveal="{ delay: 0.05 }" class="mt-3 max-w-[62ch] text-[0.96rem] leading-7 text-[var(--text-muted)]">
      {{ intro }}
    </p>

    <div v-reveal="{ stagger: 0.1, y: 32 }" class="mt-6 grid gap-4 md:grid-cols-2">
      <article
        v-for="card in cards"
        :key="card.title"
        class="rounded-[22px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-5"
      >
        <h3 class="font-heading text-[1.15rem] text-[var(--text-strong)]">
          {{ card.title }}
        </h3>
        <p class="mt-3 text-[0.94rem] leading-7 text-[var(--text-muted)]">
          {{ card.description }}
        </p>
      </article>
    </div>
  </div>
</template>
