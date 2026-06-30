<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Testimonial } from '~/types/entities'

defineProps<{
  items: Array<Pick<Testimonial, 'content' | 'full_name' | 'id' | 'stars'>>
}>()
</script>

<template>
  <section class="section-shell section-block">
    <div class="mb-6">
      <p class="text-[0.78rem] uppercase tracking-[0.22em] text-[var(--text-subtle)]">
        Social proof
      </p>
      <h2 class="mt-3 font-heading text-[2rem] text-[var(--text-strong)]">
        Testimonials that keep the storefront feeling alive
      </h2>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
      <article
        v-for="item in items"
        :key="item.id"
        class="rounded-[24px] border border-[var(--surface-border)] bg-[var(--surface-1)] p-5 shadow-[var(--shadow-1)]"
      >
        <div class="flex gap-1 text-[var(--primary)]">
          <BaseIcon
            v-for="index in item.stars"
            :key="`${item.id}-${index}`"
            name="star"
            :size="16"
          />
        </div>
        <p class="mt-4 text-[0.95rem] leading-7 text-[var(--text-muted)]">
          {{ item.content }}
        </p>
        <p class="mt-5 font-heading text-[1rem] text-[var(--text-strong)]">
          {{ item.full_name }}
        </p>
      </article>
    </div>
  </section>
</template>
