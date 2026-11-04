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
  <div class="mb-8 flex flex-wrap items-end justify-between gap-6 border-b border-ink pb-6">
    <div>
      <p class="mono-label">
        {{ eyebrow }}
      </p>
      <h2 class="mt-3 text-[34px] font-medium leading-[0.95] tracking-[-0.04em] md:text-[44px]">
        {{ title }}
      </h2>
      <p class="mt-3 max-w-[62ch] text-[15px] leading-6 text-ink-2">
        {{ description }}
      </p>
    </div>

    <slot />
  </div>
</template>
