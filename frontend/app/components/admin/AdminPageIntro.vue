<script setup lang="ts">
interface Props {
  description: string
  eyebrow?: string
  title: string
}

withDefaults(defineProps<Props>(), {
  eyebrow: 'Workspace',
})
</script>

<template>
  <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-[0.78rem] uppercase tracking-[0.22em] text-[var(--text-subtle)]">
        {{ eyebrow }}
      </p>
      <h2 class="mt-3 font-heading text-[2rem] leading-tight text-[var(--text-strong)] md:text-[2.4rem]">
        {{ title }}
      </h2>
      <p class="mt-3 max-w-[62ch] text-[0.98rem] leading-7 text-[var(--text-muted)]">
        {{ description }}
      </p>
    </div>

    <slot />
  </div>
</template>
