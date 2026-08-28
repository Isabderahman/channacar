<script setup lang="ts">
import type { ToastTone } from '~/composables/useToast'

/**
 * Renders the app-wide toast stack. Mounted once in `app.vue`, so both the
 * public site and the admin back-office share it. Sits above modals (z-200) and
 * uses logical inset utilities so the stack flips with the Arabic RTL locale.
 */
const { toasts, dismiss } = useToast()

const toneClasses: Record<ToastTone, string> = {
  error: 'border-rose-400/40 bg-[color-mix(in_srgb,#f43f5e_16%,var(--surface-1))] text-rose-100',
  success:
    'border-emerald-400/40 bg-[color-mix(in_srgb,#10b981_16%,var(--surface-1))] text-emerald-100',
  warning: 'border-amber-400/40 bg-[color-mix(in_srgb,#f59e0b_16%,var(--surface-1))] text-amber-100',
  info: 'border-[var(--primary-border)] bg-[var(--surface-2)] text-[var(--text-muted)]',
}

const iconClasses: Record<ToastTone, string> = {
  error: 'text-rose-300',
  success: 'text-emerald-300',
  warning: 'text-amber-300',
  info: 'text-[var(--primary)]',
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
            class="pointer-events-auto w-full max-w-[26rem] rounded-[18px] border px-4 py-3 shadow-[var(--shadow-1)] backdrop-blur"
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
                <p class="font-heading text-[0.95rem] font-semibold text-[var(--text-strong)]">
                  {{ toast.title }}
                </p>
                <p class="mt-1 break-words text-[0.875rem] leading-6">
                  {{ toast.message }}
                </p>
              </div>

              <button
                type="button"
                class="-me-1 -mt-1 shrink-0 rounded-full p-1.5 text-[var(--text-subtle)] transition hover:bg-black/10 hover:text-[var(--text-strong)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]"
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
