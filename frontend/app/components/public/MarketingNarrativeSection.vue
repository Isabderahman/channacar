<script setup lang="ts">
interface Props {
  eyebrow?: string
  intro?: string
  paragraphs: string[]
  title: string
}

withDefaults(defineProps<Props>(), {
  eyebrow: undefined,
  intro: undefined,
})
</script>

<template>
  <section class="section-shell section-block">
    <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
      <div v-reveal>
        <p
          v-if="eyebrow"
          class="text-[0.78rem] uppercase tracking-[0.22em] text-[var(--text-subtle)]"
        >
          {{ eyebrow }}
        </p>
        <h2 class="ridex-title-2 mt-3 leading-tight">
          {{ title }}
        </h2>
        <p
          v-if="intro"
          class="mt-5 max-w-[34ch] text-[0.96rem] leading-7 text-[var(--text-subtle)]"
        >
          {{ intro }}
        </p>
      </div>

      <div v-reveal="{ delay: 0.1 }" class="space-y-4 text-[0.98rem] leading-8 text-[var(--text-muted)]">
        <p v-for="paragraph in paragraphs" :key="paragraph">
          {{ paragraph }}
        </p>
      </div>
    </div>
  </section>
</template>
