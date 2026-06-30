<script setup lang="ts">
import { computed } from 'vue'

interface SelectOption {
  label: string
  value: number | string
}

interface Props {
  help?: string
  label: string
  multiple?: boolean
  name: string
  options: SelectOption[]
  required?: boolean
  variant?: 'default' | 'hero'
}

const props = withDefaults(defineProps<Props>(), {
  help: undefined,
  multiple: false,
  required: false,
  variant: 'default',
})

const model = defineModel<number | string | Array<number | string> | null>({
  default: '',
})

const inputId = computed(() => `field-${props.name}`)
const selectClass = computed(() =>
  props.variant === 'hero'
    ? 'h-11 rounded-[10px] border border-black/10 bg-[var(--white)] px-4 py-2 text-[0.875rem] text-[#241c22] outline-none transition duration-300 focus:border-[var(--primary)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--primary)_18%,transparent)]'
    : 'rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3 text-[0.95rem] text-[var(--text-strong)] outline-none transition duration-300 focus:border-[var(--primary-border)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--primary)_28%,transparent)]',
)
</script>

<template>
  <label class="block">
    <span class="mb-2 block text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]">
      {{ label }}
    </span>

    <select
      :id="inputId"
      v-model="model"
      :multiple="multiple"
      :name="name"
      :required="required"
      class="w-full"
      :class="[selectClass, multiple ? 'min-h-[9rem]' : '']"
    >
      <option v-if="!multiple" value="">
        Select an option
      </option>

      <option
        v-for="option in options"
        :key="String(option.value)"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>

    <span v-if="help" class="mt-2 block text-[0.82rem] leading-5 text-[var(--text-subtle)]">
      {{ help }}
    </span>
  </label>
</template>
