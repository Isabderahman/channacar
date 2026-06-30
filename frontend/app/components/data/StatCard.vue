<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import AppPanel from '~/components/data/AppPanel.vue'

interface Props {
  description?: string
  icon?: string
  label: string
  value: number | string
}

withDefaults(defineProps<Props>(), {
  description: undefined,
  icon: 'dashboard',
})
</script>

<template>
  <AppPanel>
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-[0.78rem] uppercase tracking-[0.2em] text-[var(--text-subtle)]">
          {{ label }}
        </p>
        <p class="mt-3 font-heading text-[2rem] leading-none text-[var(--text-strong)]">
          {{ value }}
        </p>
        <p v-if="description" class="mt-3 max-w-[30ch] text-[0.9rem] leading-6 text-[var(--text-muted)]">
          {{ description }}
        </p>
      </div>

      <span class="grid size-11 place-items-center rounded-[18px] bg-[var(--primary-soft)] text-[var(--primary)]">
        <BaseIcon :name="icon" :size="20" />
      </span>
    </div>
  </AppPanel>
</template>
