<script setup lang="ts">
import { computed } from 'vue'

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
      return 'border-danger'
    case 'success':
      return 'border-ok'
    case 'warning':
      return 'border-warn'
    default:
      return 'border-line-strong'
  }
})

const markerClass = computed(() => {
  switch (props.tone) {
    case 'error':
      return 'bg-danger'
    case 'success':
      return 'bg-ok'
    case 'warning':
      return 'bg-warn'
    default:
      return 'bg-muted'
  }
})
</script>

<template>
  <div class="border bg-bg px-4 py-4" :class="classes">
    <p class="flex items-center gap-2.5 text-[15px] font-medium text-ink">
      <span class="size-2 shrink-0" :class="markerClass" aria-hidden="true" />
      {{ title }}
    </p>
    <p class="mt-1.5 text-[14px] leading-6 text-ink-2">
      {{ message }}
    </p>
  </div>
</template>
