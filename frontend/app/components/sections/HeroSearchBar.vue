<script setup lang="ts">
import { computed, reactive } from 'vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { HeroSearchField } from '~/utils/home-content'

interface Props {
  actionLabel: string
  fields: HeroSearchField[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  search: [payload: Record<string, string>]
}>()

const { localeProperties } = useI18n()

const isRtl = computed(() => localeProperties.value.dir === 'rtl')

const formatDateTimeLocal = (value: Date) => {
  const date = new Date(value)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')

  return `${year}-${month}-${day}T${hours}:${minutes}`
}

const createDefaultDateTime = (offsetDays = 0) => {
  const next = new Date()

  next.setHours(10, 0, 0, 0)
  next.setDate(next.getDate() + offsetDays)

  return formatDateTimeLocal(next)
}

const pickupDateTimeDefault = useState('hero-search-pickup-default', () =>
  createDefaultDateTime(0),
)
const dropoffDateTimeDefault = useState('hero-search-dropoff-default', () =>
  createDefaultDateTime(3),
)

const resolveFieldValue = (field: HeroSearchField) => {
  if (field.defaultValue) {
    return field.defaultValue
  }

  if (field.type === 'select') {
    return field.options?.[0]?.value ?? ''
  }

  return field.name === 'dropoffDateTime'
    ? dropoffDateTimeDefault.value
    : pickupDateTimeDefault.value
}

const form = reactive<Record<string, string>>(
  Object.fromEntries(props.fields.map((field) => [field.name, resolveFieldValue(field)])),
)

const getFieldColumnClass = (field: HeroSearchField) =>
  field.type === 'select' ? 'lg:col-span-3' : 'lg:col-span-2'

const getDateIconName = (field: HeroSearchField) =>
  field.name === 'dropoffDateTime' ? 'calendar' : 'calendar'

const handleSubmit = () => {
  emit('search', { ...form })
}
</script>

<template>
  <form class="ridex-surface mt-8 w-full lg:mt-10" @submit.prevent="handleSubmit">
    <div class="p-2 sm:p-3">
      <div class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-12">
        <div
          v-for="field in fields"
          :key="field.name"
          :class="getFieldColumnClass(field)"
        >
          <label :for="field.name" class="sr-only">
            {{ field.label }}
          </label>

          <div class="relative">
            <BaseIcon
              v-if="field.type === 'select'"
              name="map-pin"
              :size="16"
              class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-black/50"
            />

            <BaseIcon
              v-else
              :name="getDateIconName(field)"
              :size="16"
              class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-black/50"
            />

            <select
              v-if="field.type === 'select'"
              :id="field.name"
              v-model="form[field.name]"
              :name="field.name"
              class="h-11 w-full appearance-none rounded-[10px] border border-black/10 bg-[var(--white)] pe-10 ps-10 text-[0.875rem] text-[#241c22] outline-none transition duration-300 focus:border-[var(--primary)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--primary)_18%,transparent)]"
            >
              <option
                v-for="option in field.options"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>

            <input
              v-else
              :id="field.name"
              v-model="form[field.name]"
              :name="field.name"
              :type="field.type"
              class="h-11 w-full rounded-[10px] border border-black/10 bg-[var(--white)] pe-4 ps-10 text-[0.875rem] text-[#241c22] outline-none transition duration-300 focus:border-[var(--primary)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--primary)_18%,transparent)]"
            >

            <BaseIcon
              v-if="field.type === 'select'"
              name="arrow-right"
              :size="16"
              class="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-black/50 opacity-60"
              :class="isRtl ? '-rotate-90' : 'rotate-90'"
            />
          </div>
        </div>

        <div class="lg:col-span-2">
          <BaseButton type="submit" full-width class="h-11 w-full rounded-[14px]">
            <span>{{ actionLabel }}</span>
          </BaseButton>
        </div>
      </div>
    </div>
  </form>
</template>
