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

const toneClass = computed(() => {
  const value = props.value.toLowerCase()

  if (['available', 'confirmed', 'completed', 'paid', 'visible', 'active'].includes(value)) {
    return 'border-emerald-400/20 bg-emerald-500/15 text-emerald-100'
  }

  if (['pending', 'deposit', 'maintenance'].includes(value)) {
    return 'border-amber-400/20 bg-amber-500/15 text-amber-100'
  }

  if (['rented', 'ongoing', 'web'].includes(value)) {
    return 'border-sky-400/20 bg-sky-500/15 text-sky-100'
  }

  if (['cancelled', 'unpaid'].includes(value)) {
    return 'border-rose-400/20 bg-rose-500/15 text-rose-100'
  }

  return 'border-[var(--surface-border)] bg-[var(--surface-3)] text-[var(--text-muted)]'
})
</script>

<template>
  <span
    class="inline-flex items-center rounded-full border px-3 py-1 text-[0.72rem] font-semibold uppercase tracking-[0.18em]"
    :class="toneClass"
  >
    {{ label ?? formatEnumLabel(value) }}
  </span>
</template>
