import type {
  MarketingCard,
  MarketingContactGroup,
  MarketingFaqItem,
} from '~/utils/marketing-content'

// Shape of one locale's copy for the six information pages (Marrakech,
// airport, long term, about, FAQ, contact). One module per locale lives next
// to this file; `useMarketingContent()` picks the one matching the current
// i18n locale. Links (`to` paths) stay in the pages — only text lives here.

export interface MarketingSeoCopy {
  description: string
  title: string
}

/** Labels of the hero buttons; their targets are set by each page. */
export interface MarketingHeroCopy {
  eyebrow: string
  primaryLabel: string
  secondaryLabel: string
}

export interface MarketingNarrativeCopy {
  eyebrow: string
  intro: string
  title: string
}

export interface MarketingListCopy {
  items: string[]
  title: string
}

/** Pages whose hero subtitle is the first narrative paragraph. */
export interface MarketingStoryPage {
  hero: MarketingHeroCopy
  narrative: MarketingNarrativeCopy
  paragraphs: string[]
  seo: MarketingSeoCopy
  title: string
}

export type ContactFormField =
  | 'nom_complet'
  | 'telephone'
  | 'email'
  | 'date_depart'
  | 'date_retour'
  | 'lieu_prise_en_charge'
  | 'lieu_retour'
  | 'message'

export interface MarketingContent {
  /** BCP 47 tag used to format dates in the WhatsApp request. */
  dateLocale: string
  /** Default numbered rows under the hero image. */
  heroFeatures: MarketingCard[]

  marrakech: MarketingStoryPage & { reasons: MarketingListCopy }
  airport: MarketingStoryPage & { advantages: MarketingListCopy }
  longTerm: MarketingStoryPage & { advantages: MarketingListCopy }
  about: MarketingStoryPage & { values: { cards: MarketingCard[], title: string } }

  faq: {
    hero: MarketingHeroCopy
    items: MarketingFaqItem[]
    seo: MarketingSeoCopy
    subtitle: string
    title: string
  }

  contact: {
    groups: MarketingContactGroup[]
    hero: MarketingHeroCopy
    seo: MarketingSeoCopy
    subtitle: string
    title: string
  }

  pageCta: {
    description: string
    primaryLabel: string
    secondaryLabel: string
    title: string
  }

  contactForm: {
    labels: Record<ContactFormField, string>
    /** Between a field label and its value in the WhatsApp message. */
    labelSeparator: string
    statusLink: string
    statusText: string
    statusTitle: string
    submit: string
    subtitle: string
    title: string
    /** Prefill when arriving with ?vehicule=…; `{vehicle}` is replaced. */
    vehiclePrefill: string
    /** First line of the WhatsApp message built from the form. */
    whatsappIntro: string
  }
}

// Locale-independent contact values shown on the contact page.
export const contactPhones = ['+212 6 66 62 36 45', '+212 6 61 23 29 02']
export const contactWhatsapp = ['+212 6 15 93 39 36']
export const contactEmails = ['contact@chanaacar.com']
