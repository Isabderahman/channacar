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
        class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-black/60 px-4 py-6 backdrop-blur-sm md:py-12"
        role="dialog"
        aria-modal="true"
        @click.self="emit('close')"
      >
        <div
          class="w-full rounded-[24px] border border-[var(--surface-border)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--surface-1)_94%,transparent),color-mix(in_srgb,var(--surface-2)_96%,transparent))] shadow-[var(--shadow-2)]"
          :class="maxWidth"
        >
          <div class="flex items-start justify-between gap-4 border-b border-[var(--surface-border)] px-6 py-5">
            <div>
              <h3 v-if="title" class="font-heading text-[1.3rem] text-[var(--text-strong)]">
                {{ title }}
              </h3>
              <p v-if="description" class="mt-1 text-[0.9rem] leading-6 text-[var(--text-muted)]">
                {{ description }}
              </p>
            </div>

            <button
              type="button"
              class="grid size-10 shrink-0 place-items-center rounded-full border border-[var(--surface-border)] text-[var(--text-muted)] transition duration-200 hover:bg-[var(--surface-2)] hover:text-[var(--text-strong)]"
              aria-label="Close dialog"
              @click="emit('close')"
            >
              <BaseIcon name="plus" :size="18" class="rotate-45" />
            </button>
          </div>

          <div class="px-6 py-6">
            <slot />
          </div>

          <div v-if="$slots.footer" class="border-t border-[var(--surface-border)] px-6 py-4">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
    </Teleport>
  </ClientOnly>
</template>
