<script setup lang="ts">
interface Props {
  description?: string
  label: string
  name: string
}

withDefaults(defineProps<Props>(), {
  description: undefined,
})

const model = defineModel<boolean>({ default: false })
</script>

<template>
  <label class="flex cursor-pointer items-start gap-3 border border-line px-4 py-3 transition-colors hover:border-line-strong">
    <input
      v-model="model"
      :name="name"
      type="checkbox"
      class="mt-0.5 size-[18px] shrink-0 accent-accent"
    >

    <span>
      <span class="block text-[15px] font-medium text-ink">
        {{ label }}
      </span>
      <span v-if="description" class="mt-1 block text-[13px] leading-5 text-muted">
        {{ description }}
      </span>
    </span>
  </label>
</template>
