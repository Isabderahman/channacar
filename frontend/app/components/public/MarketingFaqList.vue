<script setup lang="ts">
import type { MarketingFaqItem } from '~/utils/marketing-content'

defineProps<{
  items: MarketingFaqItem[]
  title: string
}>()
</script>

<template>
  <div>
    <h2 class="ridex-title-2">
      {{ title }}
    </h2>

    <div class="mt-6 space-y-3">
      <details
        v-for="item in items"
        :key="item.question"
        class="group rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-5"
      >
        <summary class="cursor-pointer list-none font-heading text-[1.05rem] text-[var(--text-strong)]">
          {{ item.question }}
        </summary>
        <p class="mt-3 text-[0.94rem] leading-7 text-[var(--text-muted)]">
          {{ item.answer }}
        </p>
      </details>
    </div>
  </div>
</template>
