<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import { HOME_FEATURES } from '~/utils/constants'
</script>

<template>
  <section class="section-shell section-block">
    <div class="grid gap-4 md:grid-cols-3">
      <article
        v-for="feature in HOME_FEATURES"
        :key="feature.title"
        class="rounded-[24px] border border-[var(--surface-border)] bg-[var(--surface-1)] p-5 shadow-[var(--shadow-1)]"
      >
        <BaseIcon :name="feature.icon" :size="20" class="text-[var(--primary)]" />
        <h2 class="mt-4 font-heading text-[1.2rem] text-[var(--text-strong)]">
          {{ feature.title }}
        </h2>
        <p class="mt-2 text-[0.94rem] leading-7 text-[var(--text-muted)]">
          {{ feature.description }}
        </p>
      </article>
    </div>
  </section>
</template>
