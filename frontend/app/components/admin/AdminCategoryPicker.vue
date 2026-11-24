<script setup lang="ts">
import type { Category } from '~/types/entities'

// Pick one or more categories for a car (e.g. "SUV" + "Premium"). The order
// of picking is kept: the first one is the car's main category, used where
// only one fits (rental contract, breadcrumb).
const props = withDefaults(
  defineProps<{
    categories: Category[]
    label?: string
  }>(),
  { label: 'Categories' },
)

const model = defineModel<number[]>({ default: () => [] })

const isSelected = (id: number) => model.value.includes(id)
const isMain = (id: number) => model.value[0] === id

const toggle = (id: number) => {
  model.value = isSelected(id) ? model.value.filter((value) => value !== id) : [...model.value, id]
}

const mainName = computed(
  () => props.categories.find((category) => category.id === model.value[0])?.name ?? null,
)
</script>

<template>
  <fieldset>
    <legend class="mono-label mb-2">{{ label }}</legend>

    <div v-if="categories.length" role="group" :aria-label="label" class="flex flex-wrap gap-1.5">
      <button
        v-for="category in categories"
        :key="category.id"
        type="button"
        class="chip min-h-10 text-[12px]"
        :aria-pressed="isSelected(category.id) ? 'true' : 'false'"
        @click="toggle(category.id)"
      >
        {{ category.name }}
        <span v-if="isMain(category.id)" class="text-[10px] opacity-60">MAIN</span>
      </button>
    </div>
    <p v-else class="text-[14px] text-ink-2">Categories could not be loaded. Refresh the page.</p>

    <p class="mt-1.5 text-[13px] text-muted">
      <template v-if="model.length > 1">
        Listed under {{ model.length }} categories. Main: {{ mainName }} — the first one picked, used on the contract.
      </template>
      <template v-else>
        Pick one or more. The first one picked is the main category.
      </template>
    </p>
  </fieldset>
</template>
