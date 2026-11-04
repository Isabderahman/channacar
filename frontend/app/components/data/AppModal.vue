<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

interface Props {
  open: boolean
  title?: string
  description?: string
  size?: 'md' | 'lg' | 'xl'
}

const props = withDefaults(defineProps<Props>(), {
  description: undefined,
  size: 'lg',
  title: undefined,
})

const emit = defineEmits<{
  close: []
}>()

const maxWidth = computed(() => {
  switch (props.size) {
    case 'md':
      return 'max-w-[34rem]'
    case 'xl':
      return 'max-w-[64rem]'
    default:
      return 'max-w-[48rem]'
  }
})

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    emit('close')
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (typeof document === 'undefined') {
      return
    }

    if (isOpen) {
      document.body.style.overflow = 'hidden'
      document.addEventListener('keydown', handleKeydown)
    } else {
      document.body.style.overflow = ''
      document.removeEventListener('keydown', handleKeydown)
    }
  },
)

onBeforeUnmount(() => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', handleKeydown)
  }
})
</script>

<template>
  <ClientOnly>
    <Teleport to="body">
      <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-black/70 px-4 py-6 md:py-12"
        role="dialog"
        aria-modal="true"
        @click.self="emit('close')"
      >
        <div
          class="w-full border border-line-strong bg-bg text-ink"
          :class="maxWidth"
        >
          <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5">
            <div>
              <h3 v-if="title" class="text-[24px] font-medium leading-tight tracking-[-0.02em]">
                {{ title }}
              </h3>
              <p v-if="description" class="mt-1.5 text-[14px] leading-6 text-ink-2">
                {{ description }}
              </p>
            </div>

            <button
              type="button"
              class="grid size-10 shrink-0 place-items-center border border-line-strong text-ink-2 transition-colors hover:border-ink hover:text-ink"
              aria-label="Close dialog"
              @click="emit('close')"
            >
              <BaseIcon name="plus" :size="18" class="rotate-45" />
            </button>
          </div>

          <div class="px-6 py-6">
            <slot />
          </div>

          <div v-if="$slots.footer" class="border-t border-line px-6 py-4">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
    </Teleport>
  </ClientOnly>
</template>
