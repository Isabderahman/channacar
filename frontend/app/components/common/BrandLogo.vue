<script setup lang="ts">
interface Props {
  brandName: string
  tagline?: string
  to?: string
}

withDefaults(defineProps<Props>(), {
  tagline: undefined,
  to: '/',
})
</script>

<template>
  <NuxtLink
    :to="to"
    class="inline-flex items-center gap-3 transition duration-500 ease-out hover:opacity-90 focus-visible:opacity-90 focus-visible:outline-none"
  >
    <img
      src="/images/chanaa-car-logo.png"
      :alt="`${brandName} — car rental service`"
      class="brand-logo h-12 w-auto md:h-14"
      width="1563"
      height="1563"
    >

    <span
      v-if="tagline"
      class="font-heading text-[0.62rem] font-semibold uppercase tracking-[0.22em] text-[var(--primary)]"
    >
      {{ tagline }}
    </span>
  </NuxtLink>
</template>
