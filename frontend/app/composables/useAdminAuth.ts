import type { AdminUser, LoginResponse } from '~/types/entities'

const TOKEN_KEY = 'channacar.admin.token'
const USER_KEY = 'channacar.admin.user'

export const useAdminAuth = () => {
  const token = useState<string | null>('admin-token', () => null)
  const user = useState<AdminUser | null>('admin-user', () => null)
  const ready = useState<boolean>('admin-auth-ready', () => false)
  const runtimeConfig = useRuntimeConfig()
  const endpoint = (path: string) => {
    const normalizedBase = runtimeConfig.public.apiBase.replace(/\/$/, '')
    const normalizedPath = path.startsWith('/') ? path : `/${path}`

    return `${normalizedBase}${normalizedPath}`
  }

  const persist = () => {
    if (!process.client) {
      return
    }

    if (token.value) {
      localStorage.setItem(TOKEN_KEY, token.value)
    } else {
      localStorage.removeItem(TOKEN_KEY)
    }

    if (user.value) {
      localStorage.setItem(USER_KEY, JSON.stringify(user.value))
    } else {
      localStorage.removeItem(USER_KEY)
    }
  }

  const initFromStorage = () => {
    if (ready.value || !process.client) {
      ready.value = true
      return
    }

    token.value = localStorage.getItem(TOKEN_KEY)

    const storedUser = localStorage.getItem(USER_KEY)

    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser) as AdminUser
      } catch {
        user.value = null
      }
    }

    ready.value = true
  }

  const clearSession = () => {
    token.value = null
    user.value = null
    persist()
  }

  const login = async (email: string, password: string) => {
    const response = await $fetch<LoginResponse>(endpoint('/login'), {
      method: 'POST',
      body: { email, password },
    })

    token.value = response.token
    user.value = response.user
    persist()

    return response.user
  }

  const logout = async () => {
    try {
      if (token.value) {
        await $fetch(endpoint('/logout'), {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        })
      }
    } finally {
      clearSession()
    }
  }

  return {
    token,
    user,
    ready,
    isAuthenticated: computed(() => Boolean(token.value)),
    initFromStorage,
    clearSession,
    login,
    logout,
  }
}
