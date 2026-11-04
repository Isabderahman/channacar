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
    <span class="mono-label mb-2 block">
      {{ label }}
    </span>

    <textarea
      v-model="model"
      :name="name"
      :placeholder="placeholder"
      :rows="rows"
      class="field text-[15px]"
    />

    <span v-if="help" class="mt-1.5 block text-[13px] leading-5 text-muted">
      {{ help }}
    </span>
  </label>
</template>
