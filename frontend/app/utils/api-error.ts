/**
 * Turn any thrown value into a sentence a human can act on.
 *
 * ofetch (FetchError) builds its `message` from the request line and the HTTP
 * status — `[POST] "/_backend/api/cars": 422 Unprocessable Content` — so the
 * common `error instanceof Error ? error.message : fallback` pattern leaked raw
 * status codes into the UI. The real reason lives on the parsed JSON body
 * (`error.data`), which Laravel fills with `message` and, for validation
 * failures, an `errors` bag keyed by field.
 *
 * Resolution order: validation bag → backend message → status wording →
 * caller fallback. Status codes never reach the returned string.
 */

export type ApiErrorLocale = 'en' | 'fr'

interface ValidationBag {
  [field: string]: string[] | string | undefined
}

interface ApiErrorLike {
  data?: unknown
  message?: string
  name?: string
  status?: number
  statusCode?: number
  response?: { status?: number; _data?: unknown }
}

// `[POST] "/_backend/api/cars": 422 Unprocessable Content` and friends.
const OFETCH_MESSAGE = /^\[[a-z]+\]\s+"[^"]*":\s*\d{3}/i

// Laravel's generic 500 body carries no information the visitor can use.
const USELESS_MESSAGES = new Set([
  'server error',
  'internal server error',
  'error',
  'bad request',
  'unauthenticated.',
  'unauthorized',
  'forbidden',
  'not found',
  'unprocessable content',
  'unprocessable entity',
])

const NETWORK_HINTS = [
  'failed to fetch',
  'fetch failed',
  'networkerror',
  'network request failed',
  'load failed',
  'ecconnrefused',
  'econnrefused',
  'aborted',
  'timeout',
]

const STATUS_MESSAGES: Record<ApiErrorLocale, Record<number, string>> = {
  en: {
    0: 'The server could not be reached. Check your connection and try again.',
    400: 'The request was rejected. Review the submitted data and try again.',
    401: 'Your session has expired. Sign in again to continue.',
    403: 'You are not allowed to perform this action.',
    404: 'The requested item could not be found — it may have been deleted.',
    405: 'This action is not available on this resource.',
    409: 'This action conflicts with the current state of the data. Reload and try again.',
    413: 'The file is too large to be uploaded.',
    419: 'The session expired while the page was open. Reload the page and try again.',
    422: 'Some fields are invalid. Check the values and try again.',
    429: 'Too many attempts in a short time. Wait a moment before retrying.',
    500: 'The server hit an unexpected error. Try again in a moment.',
    502: 'The server is temporarily unavailable. Try again in a moment.',
    503: 'The service is temporarily unavailable. Try again in a moment.',
    504: 'The server took too long to respond. Try again in a moment.',
  },
  fr: {
    0: 'Le serveur est injoignable. Vérifiez votre connexion puis réessayez.',
    400: 'La demande a été refusée. Vérifiez les informations saisies puis réessayez.',
    401: 'Votre session a expiré. Reconnectez-vous pour continuer.',
    403: 'Vous n’êtes pas autorisé à effectuer cette action.',
    404: 'L’élément demandé est introuvable — il a peut-être été supprimé.',
    405: 'Cette action n’est pas disponible pour cette ressource.',
    409: 'Cette action entre en conflit avec les données actuelles. Rechargez puis réessayez.',
    413: 'Le fichier est trop volumineux pour être envoyé.',
    419: 'La session a expiré pendant que la page était ouverte. Rechargez la page puis réessayez.',
    422: 'Certains champs sont invalides. Vérifiez les valeurs saisies.',
    429: 'Trop de tentatives en peu de temps. Patientez un instant avant de réessayer.',
    500: 'Le serveur a rencontré une erreur inattendue. Réessayez dans un instant.',
    502: 'Le serveur est momentanément indisponible. Réessayez dans un instant.',
    503: 'Le service est momentanément indisponible. Réessayez dans un instant.',
    504: 'Le serveur a mis trop de temps à répondre. Réessayez dans un instant.',
  },
}

const GENERIC_MESSAGE: Record<ApiErrorLocale, string> = {
  en: 'Something went wrong. Try again in a moment.',
  fr: 'Une erreur est survenue. Réessayez dans un instant.',
}

const asRecord = (value: unknown): Record<string, unknown> | null =>
  value && typeof value === 'object' ? (value as Record<string, unknown>) : null

/** HTTP status of a failed request, `0` when the request never got a response. */
export const getErrorStatus = (error: unknown): number => {
  const candidate = asRecord(error) as ApiErrorLike | null

  if (!candidate) {
    return 0
  }

  return Number(candidate.statusCode ?? candidate.status ?? candidate.response?.status ?? 0) || 0
}

export const isUnauthorizedError = (error: unknown) => getErrorStatus(error) === 401

const isNetworkError = (error: unknown) => {
  if (getErrorStatus(error) !== 0) {
    return false
  }

  const message = String((asRecord(error) as ApiErrorLike | null)?.message ?? '').toLowerCase()

  return NETWORK_HINTS.some((hint) => message.includes(hint))
}

/** Usable free text: not empty, not an ofetch status line, not filler wording. */
const usableMessage = (value: unknown): string | null => {
  if (typeof value !== 'string') {
    return null
  }

  const trimmed = value.trim()

  if (!trimmed || trimmed.length > 320 || trimmed.startsWith('<') || OFETCH_MESSAGE.test(trimmed)) {
    return null
  }

  return USELESS_MESSAGES.has(trimmed.toLowerCase().replace(/\s+/g, ' ')) ? null : trimmed
}

/** Laravel validation bag → "Field message. Other message." (max 3 fields). */
const fromValidationBag = (bag: unknown): string | null => {
  const record = asRecord(bag) as ValidationBag | null

  if (!record) {
    return null
  }

  const messages: string[] = []

  for (const value of Object.values(record)) {
    const first = Array.isArray(value) ? value[0] : value
    const usable = usableMessage(first)

    if (usable && !messages.includes(usable)) {
      messages.push(usable)
    }

    if (messages.length === 3) {
      break
    }
  }

  return messages.length ? messages.join(' ') : null
}

export interface ResolveApiErrorOptions {
  /** Shown when neither the backend nor the status carries anything better. */
  fallback?: string
  /** Language of the built-in status wording. Admin screens are English. */
  locale?: ApiErrorLocale
}

export const resolveApiErrorMessage = (
  error: unknown,
  options: ResolveApiErrorOptions = {},
): string => {
  const locale = options.locale ?? 'en'
  const fallback = options.fallback

  const direct = usableMessage(error)
  if (direct) {
    return direct
  }

  const candidate = asRecord(error) as ApiErrorLike | null

  if (!candidate) {
    return fallback || GENERIC_MESSAGE[locale]
  }

  // `responseType: 'blob'` (contract PDF) hands back a Blob body we cannot read
  // synchronously — fall through to the status wording in that case.
  const body = asRecord(candidate.data) ?? asRecord(candidate.response?._data)

  if (body) {
    const validation = fromValidationBag(body.errors)
    if (validation) {
      return validation
    }

    const backendMessage = usableMessage(body.message) ?? usableMessage(body.error)
    if (backendMessage) {
      return backendMessage
    }
  }

  if (isNetworkError(error)) {
    return STATUS_MESSAGES[locale][0]!
  }

  // Status `0` means "no response"; only the network branch above may claim it,
  // otherwise a plain client-side Error would be mislabelled as unreachable.
  const status = getErrorStatus(error)
  const statusMessage = status ? STATUS_MESSAGES[locale][status] : undefined
  if (statusMessage) {
    return statusMessage
  }

  // A plain `new Error('Admin session is required.')` is already readable; the
  // ofetch status line is not, and `usableMessage` filters it out.
  const ownMessage = usableMessage(candidate.message)
  if (ownMessage) {
    return ownMessage
  }

  if (fallback) {
    return fallback
  }

  if (status >= 500) {
    return STATUS_MESSAGES[locale][500]!
  }

  return GENERIC_MESSAGE[locale]
}
