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
        <p class="mono-label">
          {{ label }}
        </p>
        <p class="mt-4 text-[44px] font-medium leading-none tracking-[-0.04em]">
          {{ value }}
        </p>
        <p v-if="description" class="mt-3 max-w-[30ch] text-[14px] leading-6 text-ink-2">
          {{ description }}
        </p>
      </div>

      <span class="grid size-10 shrink-0 place-items-center border border-line text-accent">
        <BaseIcon :name="icon" :size="18" />
      </span>
    </div>
  </AppPanel>
</template>
