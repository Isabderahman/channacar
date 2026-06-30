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
  <label class="flex items-start gap-3 rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3">
    <input
      v-model="model"
      :name="name"
      type="checkbox"
      class="mt-1 size-4 rounded border-[var(--surface-border)] accent-[var(--primary)]"
    >

    <span>
      <span class="block text-[0.95rem] font-semibold text-[var(--text-strong)]">
        {{ label }}
      </span>
      <span v-if="description" class="mt-1 block text-[0.82rem] leading-5 text-[var(--text-subtle)]">
        {{ description }}
      </span>
    </span>
  </label>
</template>
