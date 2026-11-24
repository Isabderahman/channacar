import ar from '~/content/marketing/ar'
import en from '~/content/marketing/en'
import es from '~/content/marketing/es'
import fr from '~/content/marketing/fr'
import type { MarketingContent } from '~/content/marketing/shared'

const contentByLocale: Record<string, MarketingContent> = { ar, en, es, fr }

/**
 * Copy of the information pages (Marrakech, airport, long term, about, FAQ,
 * contact) in the current i18n locale, falling back to French. Long-form text
 * lives in `app/content/marketing/<locale>.ts` rather than the i18n JSON.
 */
export const useMarketingContent = () => {
  const { locale } = useI18n()
  return computed<MarketingContent>(() => contentByLocale[locale.value] ?? fr)
}
