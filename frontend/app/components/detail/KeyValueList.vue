<script setup lang="ts">
interface Item {
  label: string
  value: string | number
}

defineProps<{
  items: Item[]
  title?: string
}>()
</script>

<template>
  <div>
    <h3 v-if="title" class="text-[19px] font-medium tracking-[-0.02em]">
      {{ title }}
    </h3>

    <dl class="mt-4 grid border-t border-line sm:grid-cols-2 sm:gap-x-6">
      <div
        v-for="item in items"
        :key="item.label"
        class="flex items-baseline justify-between gap-4 border-b border-line py-3"
      >
        <dt class="font-plex text-[12px] text-muted">
          {{ item.label }}
        </dt>
        <dd class="text-end text-[15px]">
          {{ item.value }}
        </dd>
      </div>
    </dl>
  </div>
</template>
