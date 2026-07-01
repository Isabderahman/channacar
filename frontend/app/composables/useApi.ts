export const useApi = () => {
  const runtimeConfig = useRuntimeConfig()
  const { token } = useAdminAuth()

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

  const adminApi = <T>(path: string, options?: Parameters<typeof $fetch<T>>[1]) => {
    if (!token.value) {
      throw new Error('Admin session is required.')
    }

    return $fetch<T>(endpoint(path), {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(options?.headers ?? {}),
        Authorization: `Bearer ${token.value}`,
      },
    })
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
