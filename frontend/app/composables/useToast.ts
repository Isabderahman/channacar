import type { ApiErrorLocale, ResolveApiErrorOptions } from '~/utils/api-error'
import { resolveApiErrorMessage } from '~/utils/api-error'

export type ToastTone = 'error' | 'info' | 'success' | 'warning'

export interface ToastItem {
  id: number
  tone: ToastTone
  title: string
  message: string
  /** Milliseconds before auto-dismiss; `0` keeps the toast until dismissed. */
  duration: number
}

export interface ToastInput {
  tone?: ToastTone
  title?: string
  message: string
  duration?: number
}

/** Per-component defaults so call sites stay short (see `useToast(defaults)`). */
export interface ToastDefaults {
  locale?: ApiErrorLocale
  errorTitle?: string
  infoTitle?: string
  successTitle?: string
  warningTitle?: string
}

const MAX_VISIBLE = 4

// Errors get longer on screen: they usually ask the user to fix something.
const DEFAULT_DURATION: Record<ToastTone, number> = {
  error: 8000,
  warning: 7000,
  info: 5000,
  success: 4000,
}

const DEFAULT_TITLE: Record<ApiErrorLocale, Record<ToastTone, string>> = {
  en: {
    error: 'Something went wrong',
    warning: 'Heads up',
    info: 'Information',
    success: 'Done',
  },
  fr: {
    error: 'Une erreur est survenue',
    warning: 'Attention',
    info: 'Information',
    success: 'C’est fait',
  },
}

// Timers live outside the reactive state: they are client-only and must survive
// re-renders, but must never be serialised into the SSR payload.
const timers = new Map<number, ReturnType<typeof setTimeout>>()
let nextId = 0

export const useToast = (defaults: ToastDefaults = {}) => {
  const toasts = useState<ToastItem[]>('app-toasts', () => [])
  const locale = defaults.locale ?? 'en'

  const titleFor = (tone: ToastTone) => {
    const override = {
      error: defaults.errorTitle,
      info: defaults.infoTitle,
      success: defaults.successTitle,
      warning: defaults.warningTitle,
    }[tone]

    return override ?? DEFAULT_TITLE[locale][tone]
  }

  const clearTimer = (id: number) => {
    const timer = timers.get(id)

    if (timer) {
      clearTimeout(timer)
      timers.delete(id)
    }
  }

  const dismiss = (id: number) => {
    clearTimer(id)
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  const clear = () => {
    toasts.value.forEach((toast) => clearTimer(toast.id))
    toasts.value = []
  }

  const scheduleDismiss = (toast: ToastItem) => {
    if (!import.meta.client || toast.duration <= 0) {
      return
    }

    clearTimer(toast.id)
    timers.set(
      toast.id,
      setTimeout(() => dismiss(toast.id), toast.duration),
    )
  }

  const push = (input: ToastInput): number => {
    const tone = input.tone ?? 'info'
    const message = input.message.trim()

    // Client-only: a toast queued during SSR would hydrate with no timer behind
    // it and sit on screen forever. Pages that must survive a failed SSR fetch
    // keep their inline StateNotice for that.
    if (!message || !import.meta.client) {
      return -1
    }

    const title = input.title ?? titleFor(tone)

    // A retried action that keeps failing should refresh the toast already on
    // screen instead of stacking identical copies.
    const existing = toasts.value.find(
      (toast) => toast.tone === tone && toast.title === title && toast.message === message,
    )

    if (existing) {
      scheduleDismiss(existing)

      return existing.id
    }

    const toast: ToastItem = {
      id: (nextId += 1),
      tone,
      title,
      message,
      duration: input.duration ?? DEFAULT_DURATION[tone],
    }

    toasts.value = [...toasts.value, toast].slice(-MAX_VISIBLE)
    // Anything trimmed off the top must not keep a pending timer around.
    timers.forEach((_, id) => {
      if (!toasts.value.some((item) => item.id === id)) {
        clearTimer(id)
      }
    })

    scheduleDismiss(toast)

    return toast.id
  }

  const withTone = (tone: ToastTone) => (message: string, title?: string, duration?: number) =>
    push({ tone, message, title, duration })

  /**
   * Report a failed request. Never surfaces a status code — see
   * `resolveApiErrorMessage`. `fallback` is the wording used when the backend
   * sends nothing usable.
   */
  const apiError = (
    error: unknown,
    fallback?: string,
    options: { title?: string } & ResolveApiErrorOptions = {},
  ) => {
    if (import.meta.dev) {
      console.error('[api]', error)
    }

    return push({
      tone: 'error',
      title: options.title,
      message: resolveApiErrorMessage(error, {
        fallback: options.fallback ?? fallback,
        locale: options.locale ?? locale,
      }),
    })
  }

  return {
    toasts,
    apiError,
    clear,
    dismiss,
    error: withTone('error'),
    info: withTone('info'),
    push,
    success: withTone('success'),
    warning: withTone('warning'),
  }
}
