<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  padding?: 'none' | 'md' | 'lg'
  tag?: string
}

const props = withDefaults(defineProps<Props>(), {
  padding: 'md',
  tag: 'section',
})

const paddingClass = computed(() => {
  switch (props.padding) {
    case 'none':
      return ''
    case 'lg':
      return 'p-6 md:p-7'
    default:
      return 'p-5 md:p-6'
  }
})
</script>

<template>
  <component
    :is="tag"
    class="rounded-[24px] border border-[var(--surface-border)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--surface-1)_88%,transparent),color-mix(in_srgb,var(--surface-2)_94%,transparent))] shadow-[var(--shadow-1)]"
    :class="paddingClass"
  >
    <slot />
  </component>
</template>
