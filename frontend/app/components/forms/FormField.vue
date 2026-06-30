<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  autocomplete?: string
  help?: string
  label: string
  max?: number | string
  min?: number | string
  name: string
  placeholder?: string
  required?: boolean
  step?: number | string
  type?: 'date' | 'email' | 'number' | 'password' | 'search' | 'tel' | 'text' | 'time'
  variant?: 'default' | 'hero'
}

const props = withDefaults(defineProps<Props>(), {
  autocomplete: 'off',
  help: undefined,
  max: undefined,
  min: undefined,
  placeholder: '',
  required: false,
  step: undefined,
  type: 'text',
  variant: 'default',
})

const model = defineModel<string | number | null>({ default: '' })
const inputId = computed(() => `field-${props.name}`)
const inputClass = computed(() =>
  props.variant === 'hero'
    ? 'h-11 rounded-[10px] border border-black/10 bg-[var(--white)] px-4 py-2 text-[0.875rem] text-[#241c22] outline-none transition duration-300 placeholder:text-black/40 focus:border-[var(--primary)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--primary)_18%,transparent)]'
    : 'rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3 text-[0.95rem] text-[var(--text-strong)] outline-none transition duration-300 placeholder:text-[var(--text-subtle)] focus:border-[var(--primary-border)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--primary)_28%,transparent)]',
)
</script>

<template>
  <label class="block">
    <span class="mb-2 block text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]">
      {{ label }}
    </span>

    <input
      :id="inputId"
      v-model="model"
      :autocomplete="autocomplete"
      :max="max"
      :min="min"
      :name="name"
      :placeholder="placeholder"
      :required="required"
      :step="step"
      :type="type"
      class="w-full"
      :class="inputClass"
    >

    <span v-if="help" class="mt-2 block text-[0.82rem] leading-5 text-[var(--text-subtle)]">
      {{ help }}
    </span>
  </label>
</template>
