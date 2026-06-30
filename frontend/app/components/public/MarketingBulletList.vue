<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'

interface Props {
  intro?: string
  items: string[]
  title: string
}

withDefaults(defineProps<Props>(), {
  intro: undefined,
})
</script>

<template>
  <div>
    <h2 class="ridex-title-2">
      {{ title }}
    </h2>
    <p v-if="intro" class="mt-3 max-w-[62ch] text-[0.96rem] leading-7 text-[var(--text-muted)]">
      {{ intro }}
    </p>

    <ul class="mt-6 grid gap-3 md:grid-cols-2">
      <li
        v-for="item in items"
        :key="item"
        class="flex items-start gap-3 rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-4"
      >
        <BaseIcon name="check-circle" :size="18" class="mt-0.5 text-[var(--primary)]" />
        <span class="text-[0.95rem] leading-6 text-[var(--text-muted)]">{{ item }}</span>
      </li>
    </ul>
  </div>
</template>
