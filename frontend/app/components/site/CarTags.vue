<script setup lang="ts">
// Admin-set labels on a car ("Nouveau", "Promo") as small orange-outlined chips.
defineProps<{ tags?: string[] | null }>()
</script>

<template>
  <ul v-if="tags?.length" class="flex flex-wrap gap-1.5">
    <li
      v-for="tag in tags"
      :key="tag"
      class="border border-accent px-1.5 py-0.5 font-plex text-[10px] uppercase leading-[1.4] tracking-[0.06em] text-accent md:text-[11px]"
    >
      {{ tag }}
    </li>
  </ul>
</template>
