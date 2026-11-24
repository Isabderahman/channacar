// Page copy for the information pages lives in app/content/marketing/<locale>.ts
// (see useMarketingContent). The nav/footer lists below only feed legacy components.

export interface MarketingCard {
  description: string
  title: string
}

export interface MarketingFaqItem {
  answer: string
  question: string
}

export interface MarketingLink {
  label: string
  to: string
}

export type MarketingContactKind = 'phone' | 'whatsapp' | 'email' | 'address' | 'hours'

export interface MarketingContactGroup {
  /** Stable type, so links (tel:, mailto:, wa.me) don't depend on the translated label. */
  kind: MarketingContactKind
  label: string
  values: string[]
}

export const marketingNavLinks: MarketingLink[] = [
  { label: 'Accueil', to: '/' },
  { label: 'Nos Véhicules', to: '/cars' },
  { label: 'Location Marrakech', to: '/location-marrakech' },
  { label: 'Location Aéroport', to: '/location-aeroport-marrakech-menara' },
  { label: 'Longue Durée', to: '/location-longue-duree' },
  { label: 'À Propos', to: '/a-propos' },
  { label: 'FAQ', to: '/faq' },
  { label: 'Contact', to: '/contact' },
]

export const marketingFooterInfo = [
  'Assistance 24h/24',
  'Réservation rapide',
  'Tarifs transparents',
  'Véhicules récents',
]
