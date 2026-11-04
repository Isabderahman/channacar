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
      return 'p-6 md:p-8'
    default:
      return 'p-5 md:p-6'
  }
})
</script>

<template>
  <component
    :is="tag"
    class="border border-line bg-bg"
    :class="paddingClass"
  >
    <slot />
  </component>
</template>
