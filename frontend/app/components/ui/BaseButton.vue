<script setup lang="ts">
import { computed } from 'vue'

type ButtonVariant = 'primary' | 'secondary' | 'ghost'
type ButtonSize = 'sm' | 'md' | 'lg' | 'icon'

interface Props {
  ariaLabel?: string
  disabled?: boolean
  fullWidth?: boolean
  href?: string
  size?: ButtonSize
  to?: string
  type?: 'button' | 'submit' | 'reset'
  uppercase?: boolean
  variant?: ButtonVariant
}

const props = withDefaults(defineProps<Props>(), {
  ariaLabel: undefined,
  disabled: false,
  fullWidth: false,
  href: undefined,
  size: 'md',
  to: undefined,
  type: 'button',
  uppercase: false,
  variant: 'primary',
})

const commonClasses =
  'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-[14px] font-heading text-[0.875rem] font-semibold transition duration-500 ease-out before:pointer-events-none before:absolute before:inset-0 before:bg-[linear-gradient(to_right,hsla(0,0%,100%,0.4),transparent)] before:opacity-0 before:transition before:duration-500 hover:before:opacity-100 focus-visible:before:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--carolina-blue)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--alice-blue-1)] disabled:pointer-events-none disabled:opacity-60'

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'secondary':
      return 'border border-[var(--surface-border)] bg-[var(--beau-blue)] text-[var(--blue-jeans)] shadow-none hover:bg-[var(--lavender-blush)] hover:text-[var(--red-salsa)] hover:shadow-none'
    case 'ghost':
      return 'border border-[var(--surface-border)] bg-[var(--surface-3)] text-[var(--space-cadet)] shadow-[var(--shadow-1)] hover:bg-[var(--surface-2)] hover:text-[var(--carolina-blue)] hover:shadow-[var(--shadow-1)]'
    default:
      return 'bg-[var(--carolina-blue)] text-[var(--white)] hover:shadow-[var(--shadow-2)]'
  }
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'min-h-9 min-w-[112px] px-5'
    case 'lg':
      return 'min-h-[50px] min-w-[140px] px-6'
    case 'icon':
      return 'size-9 p-0'
    default:
      return 'min-h-10 min-w-10 px-5'
  }
})

const widthClasses = computed(() => (props.fullWidth ? 'w-full' : ''))
const textTransformClasses = computed(() => (props.uppercase ? 'uppercase' : ''))
</script>

<template>
  <NuxtLink
    v-if="to"
    :to="to"
    :aria-label="ariaLabel"
    :class="[commonClasses, variantClasses, sizeClasses, widthClasses, textTransformClasses]"
  >
    <span class="relative z-10 flex items-center justify-center gap-2">
      <slot />
    </span>
  </NuxtLink>

  <a
    v-else-if="href"
    :href="href"
    :aria-label="ariaLabel"
    :class="[commonClasses, variantClasses, sizeClasses, widthClasses, textTransformClasses]"
  >
    <span class="relative z-10 flex items-center justify-center gap-2">
      <slot />
    </span>
  </a>

  <button
    v-else
    :type="type"
    :disabled="disabled"
    :aria-label="ariaLabel"
    :class="[commonClasses, variantClasses, sizeClasses, widthClasses, textTransformClasses]"
  >
    <span class="relative z-10 flex items-center justify-center gap-2">
      <slot />
    </span>
  </button>
</template>
