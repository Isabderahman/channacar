<script setup lang="ts">
import { computed } from 'vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

interface Props {
  message: string
  title: string
  tone?: 'error' | 'info' | 'success' | 'warning'
}

const props = withDefaults(defineProps<Props>(), {
  tone: 'info',
})

const classes = computed(() => {
  switch (props.tone) {
    case 'error':
      return 'border-rose-400/30 bg-rose-500/12 text-rose-100'
    case 'success':
      return 'border-emerald-400/30 bg-emerald-500/12 text-emerald-100'
    case 'warning':
      return 'border-amber-400/30 bg-amber-500/12 text-amber-100'
    default:
      return 'border-[var(--surface-border)] bg-[var(--surface-3)] text-[var(--text-muted)]'
  }
})

const icon = computed(() => {
  switch (props.tone) {
    case 'error':
      return 'filter'
    case 'success':
      return 'check-circle'
    case 'warning':
      return 'filter'
    default:
      return 'refresh'
  }
})
</script>

<template>
  <div class="rounded-[20px] border px-4 py-4" :class="classes">
    <div class="flex items-start gap-3">
      <span class="mt-0.5 rounded-full bg-black/10 p-2">
        <BaseIcon :name="icon" :size="16" />
      </span>

      <div>
        <p class="font-heading text-[1rem] font-semibold text-[var(--text-strong)]">
          {{ title }}
        </p>
        <p class="mt-1 text-[0.9rem] leading-6">
          {{ message }}
        </p>
      </div>
    </div>
  </div>
</template>
