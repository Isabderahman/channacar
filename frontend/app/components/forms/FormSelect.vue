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
// Square hairline control (design handoff); `hero` kept for API compatibility.
const selectClass = 'field min-h-11 text-[15px]'
</script>

<template>
  <label class="block">
    <span class="mono-label mb-2 block">
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

    <span v-if="help" class="mt-1.5 block text-[13px] leading-5 text-muted">
      {{ help }}
    </span>
  </label>
</template>
