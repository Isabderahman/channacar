import { isUnauthorizedError } from '~/utils/api-error'

export const useApi = () => {
  const runtimeConfig = useRuntimeConfig()
  const { clearSession, token } = useAdminAuth()
  const toast = useToast()

  const endpoint = (path: string) => {
    const normalizedBase = runtimeConfig.public.apiBase.replace(/\/$/, '')
    const normalizedPath = path.startsWith('/') ? path : `/${path}`

    return `${normalizedBase}${normalizedPath}`
  }

  // Force `Accept: application/json` so Laravel renders validation/auth errors
  // as JSON (422/401) instead of a 302 redirect. Without it, $fetch silently
  // follows the redirect and resolves with a non-JSON body, hiding the real
  // error (e.g. "car not available") behind an undefined response.
  const publicApi = <T>(path: string, options?: Parameters<typeof $fetch<T>>[1]) =>
    $fetch<T>(endpoint(path), {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(options?.headers ?? {}),
      },
    })

  const adminApi = async <T>(path: string, options?: Parameters<typeof $fetch<T>>[1]) => {
    if (!token.value) {
      throw new Error('Admin session is required. Sign in again to continue.')
    }

    try {
      return await $fetch<T>(endpoint(path), {
        ...options,
        headers: {
          Accept: 'application/json',
          ...(options?.headers ?? {}),
          Authorization: `Bearer ${token.value}`,
        },
      })
    } catch (error) {
      // A revoked or expired Sanctum token fails every subsequent call. Drop the
      // dead session once, here, so the auth gate takes over instead of each
      // page reporting its own unrelated-looking failure.
      if (isUnauthorizedError(error) && token.value) {
        clearSession()
        toast.warning('Your session has expired. Sign in again to continue.', 'Signed out')
      }

      throw error
    }
  }

  const mediaUrl = (path?: string | null) => {
    if (!path) {
      return '/images/hero-banner.jpg'
    }

    if (/^https?:\/\//.test(path)) {
      return path
    }

    const normalizedBase = runtimeConfig.public.storageBase.replace(/\/$/, '')
    const normalizedPath = String(path).replace(/^\//, '')

    return `${normalizedBase}/${normalizedPath}`
  }

  return {
    adminApi,
    mediaUrl,
    publicApi,
  }
}
