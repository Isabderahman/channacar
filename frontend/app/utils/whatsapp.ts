import type { Car } from '~/types/entities'
import { businessInfo } from '~/utils/business-info'
import { formatCurrency } from '~/utils/formatters'

// Single source for the WhatsApp number + link building, used by the floating
// button and the per-car buttons (card + detail page).
const digits = businessInfo.whatsapp.replace(/\D/g, '')

export const whatsappHref = (message?: string): string => {
  const base = `https://wa.me/${digits}`
  return message ? `${base}?text=${encodeURIComponent(message)}` : base
}

// Generic enquiry used by the floating button.
export const whatsappGeneralMessage =
  'Bonjour ChanaaCar, je souhaite des informations sur la location d’une voiture.'

// Car-specific enquiry that pre-fills the model, year and daily price.
export const carWhatsappMessage = (car: Car): string =>
  `Bonjour ChanaaCar, je suis intéressé(e) par la ${car.brand} ${car.name} (${car.year}) ` +
  `à ${formatCurrency(car.base_price_per_day, 'MAD', 'fr-MA')} / jour. ` +
  `Est-elle disponible pour mes dates ?`
