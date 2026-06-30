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
    <h3 v-if="title" class="font-heading text-[1.1rem] text-[var(--text-strong)]">
      {{ title }}
    </h3>

    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
      <div
        v-for="item in items"
        :key="item.label"
        class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4"
      >
        <dt class="text-[0.75rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]">
          {{ item.label }}
        </dt>
        <dd class="mt-2 font-heading text-[1rem] text-[var(--text-strong)]">
          {{ item.value }}
        </dd>
      </div>
    </dl>
  </div>
</template>
