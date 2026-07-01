<script setup lang="ts">
import BaseSkeleton from '~/components/ui/BaseSkeleton.vue'

// Placeholder that mirrors PublicCarCard's layout while the fleet loads.
</script>

<template>
  <article class="ridex-surface overflow-hidden p-2.5">
    <BaseSkeleton class="aspect-[16/11] w-full rounded-[20px]" />

    <div class="p-4">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-2.5">
          <BaseSkeleton class="h-3 w-24 rounded-full" />
          <BaseSkeleton class="h-6 w-44 rounded-[8px]" />
        </div>
        <BaseSkeleton class="h-7 w-14 shrink-0 rounded-full" />
      </div>

      <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
        <BaseSkeleton v-for="n in 4" :key="n" class="h-4 w-28 rounded-full" />
      </div>

      <div class="mt-5 flex items-center gap-3">
        <BaseSkeleton class="h-8 w-24 rounded-[8px]" />
        <BaseSkeleton class="ms-auto h-10 w-28 rounded-[12px]" />
      </div>
    </div>
  </article>
</template>
