<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'

// The ChanaaCar logo (white lettering on transparent — made for dark
// backgrounds; in the admin light theme it sits on a dark plate, see
// `.logo-plate` in main.css).
interface Props {
  brandName?: string
  size?: 'sm' | 'md' | 'lg' | 'xl'
  tagline?: string
  to?: RouteLocationRaw
}

withDefaults(defineProps<Props>(), {
  brandName: 'ChanaaCar',
  size: 'md',
  tagline: undefined,
  to: '/',
})

// The artwork is a square badge with generous padding, so it needs a little
// more height than a text wordmark to stay legible.
const heights = {
  sm: 'h-12',
  md: 'h-14 xl:h-16',
  lg: 'h-20 lg:h-24',
  xl: 'h-24 md:h-28',
}
</script>

<template>
  <NuxtLink
    :to="to"
    class="inline-flex shrink-0 items-center gap-3 transition-opacity hover:opacity-85"
  >
    <span class="logo-plate">
      <img
        src="/images/chanaa-car-logo.png"
        :alt="`${brandName} — car rental service`"
        width="1563"
        height="1563"
        decoding="async"
        class="block w-auto"
        :class="heights[size]"
      >
    </span>

    <span v-if="tagline" class="mono-label">
      {{ tagline }}
    </span>
  </NuxtLink>
</template>
