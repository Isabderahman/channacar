<script setup lang="ts">
import type { ToastTone } from '~/composables/useToast'

/**
 * Renders the app-wide toast stack. Mounted once in `app.vue`, so both the
 * public site and the admin back-office share it. Sits above modals (z-200) and
 * uses logical inset utilities so the stack flips with the Arabic RTL locale.
 */
const { toasts, dismiss } = useToast()

// Square, flat toasts (design handoff); the tone shows on the border + icon.
const toneClasses: Record<ToastTone, string> = {
  error: 'border-danger',
  success: 'border-ok',
  warning: 'border-warn',
  info: 'border-line-strong',
}

const iconClasses: Record<ToastTone, string> = {
  error: 'text-danger',
  success: 'text-ok',
  warning: 'text-warn',
  info: 'text-accent',
}
</script>

<template>
  <ClientOnly>
    <Teleport to="body">
      <div
        class="pointer-events-none fixed inset-x-0 top-4 z-[200] flex flex-col items-center gap-3 px-4 sm:inset-x-auto sm:end-4 sm:items-end sm:px-0"
        role="region"
        aria-label="Notifications"
      >
        <TransitionGroup
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="translate-y-[-8px] opacity-0 sm:translate-x-4 sm:translate-y-0"
          leave-active-class="transition duration-200 ease-in absolute"
          leave-to-class="opacity-0 scale-95"
          move-class="transition duration-200 ease-out"
        >
          <div
            v-for="toast in toasts"
            :key="toast.id"
            class="pointer-events-auto w-full max-w-[26rem] border bg-bg px-4 py-3 font-grotesk text-ink-2"
            :class="toneClasses[toast.tone]"
            :role="toast.tone === 'error' ? 'alert' : 'status'"
            :aria-live="toast.tone === 'error' ? 'assertive' : 'polite'"
          >
            <div class="flex items-start gap-3">
              <span class="mt-0.5 shrink-0" :class="iconClasses[toast.tone]">
                <svg
                  v-if="toast.tone === 'success'"
                  width="18"
                  height="18"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                >
                  <circle cx="12" cy="12" r="9" />
                  <path d="m8.5 12.5 2.5 2.5 4.5-5" />
                </svg>

                <svg
                  v-else-if="toast.tone === 'info'"
                  width="18"
                  height="18"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                >
                  <circle cx="12" cy="12" r="9" />
                  <path d="M12 11v5" />
                  <path d="M12 8h.01" />
                </svg>

                <svg
                  v-else
                  width="18"
                  height="18"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                >
                  <path d="M12 3.5 2.8 19.5h18.4L12 3.5Z" />
                  <path d="M12 10v4" />
                  <path d="M12 17h.01" />
                </svg>
              </span>

              <div class="min-w-0 flex-1">
                <p class="text-[15px] font-medium text-ink">
                  {{ toast.title }}
                </p>
                <p class="mt-1 break-words text-[14px] leading-6">
                  {{ toast.message }}
                </p>
              </div>

              <button
                type="button"
                class="-me-1 -mt-1 shrink-0 p-1.5 text-muted transition-colors hover:text-ink"
                aria-label="Fermer la notification"
                @click="dismiss(toast.id)"
              >
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  aria-hidden="true"
                >
                  <path d="m6 6 12 12M18 6 6 18" />
                </svg>
              </button>
            </div>
          </div>
        </TransitionGroup>
      </div>
    </Teleport>
  </ClientOnly>
</template>
