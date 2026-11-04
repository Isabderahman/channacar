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

// Square, flat buttons (design handoff): orange fill for the primary action,
// 1px outline for secondary, text-only ghost.
const commonClasses =
  'inline-flex shrink-0 items-center justify-center border font-medium transition-colors duration-200 disabled:pointer-events-none disabled:opacity-50'

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'secondary':
      return 'border-line-strong bg-transparent text-ink hover:border-ink'
    case 'ghost':
      return 'border-transparent bg-transparent text-ink-2 hover:bg-surface-2 hover:text-ink'
    default:
      return 'border-transparent bg-accent font-semibold text-on-accent hover:bg-[var(--accent-hover)]'
  }
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'min-h-9 px-3.5 text-[13px]'
    case 'lg':
      return 'min-h-12 px-5 text-[15px]'
    case 'icon':
      return 'size-10 p-0'
    default:
      return 'min-h-11 px-4 text-[14px]'
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
    <span class="flex items-center justify-center gap-2">
      <slot />
    </span>
  </NuxtLink>

  <a
    v-else-if="href"
    :href="href"
    :aria-label="ariaLabel"
    :class="[commonClasses, variantClasses, sizeClasses, widthClasses, textTransformClasses]"
  >
    <span class="flex items-center justify-center gap-2">
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
    <span class="flex items-center justify-center gap-2">
      <slot />
    </span>
  </button>
</template>
