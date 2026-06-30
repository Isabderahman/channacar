<script setup lang="ts">
interface Props {
  help?: string
  label: string
  name: string
  placeholder?: string
  rows?: number
}

withDefaults(defineProps<Props>(), {
  help: undefined,
  placeholder: '',
  rows: 4,
})

const model = defineModel<string>({ default: '' })
</script>

<template>
  <label class="block">
    <span class="mb-2 block text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]">
      {{ label }}
    </span>

    <textarea
      v-model="model"
      :name="name"
      :placeholder="placeholder"
      :rows="rows"
      class="w-full rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3 text-[0.95rem] text-[var(--text-strong)] outline-none transition duration-300 placeholder:text-[var(--text-subtle)] focus:border-[var(--primary-border)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--primary)_28%,transparent)]"
    />

    <span v-if="help" class="mt-2 block text-[0.82rem] leading-5 text-[var(--text-subtle)]">
      {{ help }}
    </span>
  </label>
</template>
