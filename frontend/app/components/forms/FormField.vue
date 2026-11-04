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
// Square hairline control (design handoff); `hero` kept for API compatibility.
const inputClass = 'field min-h-11 text-[15px]'
</script>

<template>
  <label class="block">
    <span class="mono-label mb-2 block">
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

    <span v-if="help" class="mt-1.5 block text-[13px] leading-5 text-muted">
      {{ help }}
    </span>
  </label>
</template>
