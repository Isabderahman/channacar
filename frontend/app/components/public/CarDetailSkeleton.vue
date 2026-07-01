<script setup lang="ts">
import BaseSkeleton from '~/components/ui/BaseSkeleton.vue'

// Placeholder that mirrors the car detail hero (info + gallery) and the
// reservation panel while the vehicle, gallery and references load.
</script>

<template>
  <div>
    <div class="grid gap-8 lg:grid-cols-[1fr_1.08fr] lg:items-start">
      <!-- Info + specs (after the image on mobile, matching the real layout) -->
      <div class="order-2 lg:order-1">
        <BaseSkeleton class="h-3 w-28 rounded-full" />
        <BaseSkeleton class="mt-3 h-9 w-3/4 rounded-[10px] sm:h-11" />

        <div class="mt-4 space-y-2.5">
          <BaseSkeleton class="h-4 w-full max-w-[46ch] rounded-full" />
          <BaseSkeleton class="h-4 w-4/5 max-w-[40ch] rounded-full" />
        </div>

        <div class="mt-6 grid grid-cols-2 gap-2.5 sm:grid-cols-3">
          <BaseSkeleton
            v-for="n in 9"
            :key="n"
            class="h-[54px] w-full rounded-[14px]"
          />
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
          <BaseSkeleton class="h-12 w-36 rounded-[14px]" />
          <BaseSkeleton class="h-12 w-48 rounded-[14px]" />
        </div>
      </div>

      <!-- Gallery -->
      <div class="order-1 lg:order-2">
        <BaseSkeleton class="aspect-[4/3] w-full rounded-[24px] sm:aspect-[16/10]" />

        <div class="-mx-1 mt-3 flex gap-3 px-1">
          <BaseSkeleton
            v-for="n in 4"
            :key="n"
            class="h-16 w-24 shrink-0 rounded-[16px] sm:h-20 sm:w-28"
          />
        </div>
      </div>
    </div>

    <!-- Reservation panel -->
    <div class="ridex-surface mt-10 p-6 sm:p-8">
      <BaseSkeleton class="h-7 w-64 rounded-[8px]" />
      <BaseSkeleton class="mt-2 h-4 w-80 max-w-full rounded-full" />

      <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <BaseSkeleton v-for="n in 6" :key="n" class="h-12 w-full rounded-[12px]" />
      </div>

      <BaseSkeleton class="mt-6 h-12 w-full rounded-[12px] sm:w-48" />
    </div>
  </div>
</template>
