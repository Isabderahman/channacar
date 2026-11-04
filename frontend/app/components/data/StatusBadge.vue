<script setup lang="ts">
import { computed } from 'vue'
import { formatEnumLabel } from '~/utils/formatters'

interface Props {
  label?: string
  value: string
}

const props = withDefaults(defineProps<Props>(), {
  label: undefined,
})

// Square mono tag; the dot carries the status colour (tokens in main.css).
const toneClass = computed(() => {
  const value = props.value.toLowerCase()

  if (['available', 'confirmed', 'completed', 'paid', 'visible', 'active'].includes(value)) {
    return 'bg-ok'
  }

  if (['pending', 'deposit', 'maintenance'].includes(value)) {
    return 'bg-warn'
  }

  if (['rented', 'ongoing', 'web'].includes(value)) {
    return 'bg-info'
  }

  if (['cancelled', 'unpaid'].includes(value)) {
    return 'bg-danger'
  }

  return 'bg-muted'
})
</script>

<template>
  <span
    class="inline-flex items-center gap-2 whitespace-nowrap border border-line-strong px-2.5 py-1 font-plex text-[11px] uppercase tracking-[0.06em] text-ink"
  >
    <span class="size-1.5 shrink-0" :class="toneClass" aria-hidden="true" />
    {{ label ?? formatEnumLabel(value) }}
  </span>
</template>
