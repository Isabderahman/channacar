import { computed } from 'vue'

export type ThemeMode = 'dark' | 'light'

/**
 * Theme state persisted in the `channacar.theme` cookie so the server renders
 * the correct theme (no flash of the wrong mode on load). Dark is the default.
 */
export const useTheme = () => {
  const cookie = useCookie<ThemeMode>('channacar.theme', {
    default: () => 'dark',
    maxAge: 60 * 60 * 24 * 365,
    sameSite: 'lax',
    path: '/',
  })

  const theme = useState<ThemeMode>('channacar.theme', () => cookie.value ?? 'dark')

  const setTheme = (mode: ThemeMode) => {
    theme.value = mode
    cookie.value = mode
  }

  const toggle = () => setTheme(theme.value === 'dark' ? 'light' : 'dark')

  const isDark = computed(() => theme.value === 'dark')

  return { theme, isDark, setTheme, toggle }
}
