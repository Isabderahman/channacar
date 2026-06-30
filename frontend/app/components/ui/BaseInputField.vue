<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  autocomplete?: string
  id?: string
  label: string
  name: string
  placeholder: string
  required?: boolean
  type?: 'text' | 'search' | 'number'
}

const props = withDefaults(defineProps<Props>(), {
  autocomplete: 'off',
  id: undefined,
  required: false,
  type: 'text',
})

const model = defineModel<string>({ default: '' })
const inputId = computed(() => props.id ?? props.name)
</script>

<template>
  <div class="px-5 py-[15px]">
    <label
      :for="inputId"
      class="mb-2.5 block truncate text-start text-[0.875rem] text-[var(--manatee)]"
    >
      {{ label }}
    </label>

    <input
      :id="inputId"
      v-model="model"
      :name="name"
      :type="type"
      :autocomplete="autocomplete"
      :required="required"
      :placeholder="placeholder"
      class="block w-full rounded-[4px] bg-transparent text-start text-[var(--space-cadet)] outline-2 outline-transparent outline-offset-[5px] transition duration-500 ease-out placeholder:text-[var(--independence)] focus:outline-[var(--carolina-blue)]"
    >
  </div>
</template>
