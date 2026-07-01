<script setup lang="ts">
import { computed } from 'vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Car } from '~/types/entities'
import { carWhatsappMessage, whatsappHref } from '~/utils/whatsapp'

const props = withDefaults(
  defineProps<{
    car: Car
    label?: string
    size?: 'md' | 'lg' | 'icon'
    fullWidth?: boolean
  }>(),
  {
    label: undefined,
    size: 'md',
    fullWidth: false,
  },
)

const href = computed(() => whatsappHref(carWhatsappMessage(props.car)))

const sizeClass = computed(() => {
  switch (props.size) {
    case 'lg':
      return 'min-h-[50px] px-6'
    case 'icon':
      return 'size-10 p-0'
    default:
      return 'min-h-10 px-5'
  }
})
</script>

<template>
  <a
    :href="href"
    target="_blank"
    rel="noopener"
    :aria-label="`Contacter ChanaaCar sur WhatsApp à propos de ${car.brand} ${car.name}`"
    class="relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-[14px] bg-[var(--carolina-blue)] font-heading text-[0.875rem] font-semibold text-[var(--white)] transition duration-500 ease-out before:pointer-events-none before:absolute before:inset-0 before:bg-[linear-gradient(to_right,hsla(0,0%,100%,0.4),transparent)] before:opacity-0 before:transition before:duration-500 hover:shadow-[var(--shadow-2)] hover:before:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--carolina-blue)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--alice-blue-1)]"
    :class="[sizeClass, fullWidth ? 'w-full' : '']"
  >
    <BaseIcon name="whatsapp" :size="18" class="relative z-10" />
    <span v-if="label" class="relative z-10">{{ label }}</span>
  </a>
</template>
