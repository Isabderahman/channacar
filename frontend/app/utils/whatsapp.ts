import type { Car } from '~/types/entities'
import { businessInfo } from '~/utils/business-info'
import { formatAmount } from '~/utils/car-display'
import { formatCurrency } from '~/utils/formatters'

// Single source for the WhatsApp number + link building, used by the floating
// button and the per-car buttons (card + detail page).
const digits = businessInfo.whatsapp.replace(/\D/g, '')

/** vue-i18n's `t` (from `useI18n()`), narrowed to what the messages need. */
export type WhatsappTranslate = (key: string, named: Record<string, unknown>) => string

export const whatsappHref = (message?: string): string => {
  const base = `https://wa.me/${digits}`
  return message ? `${base}?text=${encodeURIComponent(message)}` : base
}

// Generic enquiry used by the floating button, in the current language.
export const whatsappGeneralMessageFor = (t: WhatsappTranslate): string => t('whatsapp.general', {})

// Car-specific enquiry that pre-fills the model, year and daily price.
// `price` is the French currency format ("1.800 MAD"), `amount` the bare
// number for the other languages, which place the currency themselves.
export const carWhatsappMessage = (car: Car, t: WhatsappTranslate): string =>
  t('whatsapp.car', {
    name: `${car.brand} ${car.name}`,
    year: car.year,
    price: formatCurrency(car.base_price_per_day, 'MAD', 'fr-MA'),
    amount: formatAmount(car.base_price_per_day),
  })
