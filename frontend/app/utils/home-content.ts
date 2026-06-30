export type CarSpecIcon = 'people' | 'energy' | 'efficiency' | 'transmission'
export type CarSpecKey = 'capacity' | 'fuel' | 'efficiency' | 'transmission'
export type HeroLocationOptionKey =
  | 'marrakechAirport'
  | 'marrakechCityCenter'
  | 'gueliz'
  | 'medina'
  | 'hotelDelivery'
export type HeroSearchFieldName =
  | 'pickupLocation'
  | 'dropoffLocation'
  | 'pickupDateTime'
  | 'dropoffDateTime'
export type HeroSearchFieldType = 'select' | 'datetime-local'
export type FeaturedCarId =
  | 'toyotaRav4'
  | 'bmw3Series'
  | 'volkswagenTCross'
  | 'cadillacEscalade'
  | 'bmw4SeriesGti'
  | 'bmw4Series'
export type StepIcon = 'profile-add' | 'car' | 'person' | 'payment'
export type StepId = 'createProfile' | 'chooseCar' | 'matchSeller' | 'makeDeal'
export type StepTone = 'pink' | 'blue' | 'green' | 'purple'
export type SocialIcon = 'facebook' | 'instagram' | 'linkedin' | 'mail' | 'twitter'
export type LocaleDirection = 'ltr' | 'rtl'

export interface HeroSearchOptionBase {
  key: HeroLocationOptionKey
  value: string
}

export interface HeroSearchOption extends HeroSearchOptionBase {
  label: string
}

export interface HeroSearchFieldBase {
  defaultValue?: string
  name: HeroSearchFieldName
  options?: HeroSearchOptionBase[]
  type: HeroSearchFieldType
}

export interface HeroSearchField extends HeroSearchFieldBase {
  label: string
  options?: HeroSearchOption[]
  placeholder?: string
}

export interface FeaturedCarSpecBase {
  icon: CarSpecIcon
  key: CarSpecKey
}

export interface FeaturedCarSpec extends FeaturedCarSpecBase {
  label: string
}

export interface FeaturedCarBase {
  id: FeaturedCarId
  year: number
  image: string
  pricePerMonth: number
  specs: FeaturedCarSpecBase[]
}

export interface FeaturedCar extends FeaturedCarBase {
  alt: string
  name: string
  specs: FeaturedCarSpec[]
}

export interface StepCardBase {
  icon: StepIcon
  id: StepId
  linkHref?: string
  tone: StepTone
}

export interface StepCardItem extends StepCardBase {
  description: string
  linkLabel?: string
  title: string
}

export interface NavItem {
  href: string
  label: string
}

export interface NavItemDef {
  href: string
  key: string
}

export interface FooterLinkGroup {
  links: NavItem[]
  title: string
  wide?: boolean
}

export interface FooterLinkGroupDef {
  key: string
  links: NavItemDef[]
  wide?: boolean
}

export interface SocialLinkBase {
  href: string
  icon: SocialIcon
  key: SocialIcon
}

export interface SocialLink extends SocialLinkBase {
  label: string
}

export interface ContactInfo {
  availability: string
  phoneHref: string
  phoneLabel: string
}

export interface BrandContent {
  brandName: string
  tagline: string
}

export interface LocaleOption {
  code: string
  dir: LocaleDirection
  name: string
}

export const contactDetails = {
  phoneHref: 'tel:+212666623645',
  phoneLabel: '+212 6 66 62 36 45',
} as const

export const heroImage = '/images/hero-banner.jpg'

export const headerLinkDefs = [
  { key: 'home', href: '#home' },
  { key: 'exploreCars', href: '#featured-cars' },
  { key: 'aboutUs', href: '#footer' },
  { key: 'howItWorks', href: '#steps' },
] satisfies NavItemDef[]

const heroLocationOptionDefs = [
  { key: 'marrakechAirport', value: 'marrakech-airport' },
  { key: 'marrakechCityCenter', value: 'marrakech-city-center' },
  { key: 'gueliz', value: 'gueliz' },
  { key: 'medina', value: 'medina' },
  { key: 'hotelDelivery', value: 'hotel-delivery' },
] satisfies HeroSearchOptionBase[]

export const heroSearchFieldDefs = [
  {
    name: 'pickupLocation',
    type: 'select',
    defaultValue: 'marrakech-airport',
    options: heroLocationOptionDefs,
  },
  {
    name: 'dropoffLocation',
    type: 'select',
    defaultValue: 'marrakech-airport',
    options: heroLocationOptionDefs,
  },
  { name: 'pickupDateTime', type: 'datetime-local' },
  { name: 'dropoffDateTime', type: 'datetime-local' },
] satisfies HeroSearchFieldBase[]

export const featuredCarCatalog = [
  {
    id: 'toyotaRav4',
    year: 2021,
    image: '/images/car-1.jpg',
    pricePerMonth: 440,
    specs: [
      { icon: 'people', key: 'capacity' },
      { icon: 'energy', key: 'fuel' },
      { icon: 'efficiency', key: 'efficiency' },
      { icon: 'transmission', key: 'transmission' },
    ],
  },
  {
    id: 'bmw3Series',
    year: 2019,
    image: '/images/car-2.jpg',
    pricePerMonth: 350,
    specs: [
      { icon: 'people', key: 'capacity' },
      { icon: 'energy', key: 'fuel' },
      { icon: 'efficiency', key: 'efficiency' },
      { icon: 'transmission', key: 'transmission' },
    ],
  },
  {
    id: 'volkswagenTCross',
    year: 2020,
    image: '/images/car-3.jpg',
    pricePerMonth: 400,
    specs: [
      { icon: 'people', key: 'capacity' },
      { icon: 'energy', key: 'fuel' },
      { icon: 'efficiency', key: 'efficiency' },
      { icon: 'transmission', key: 'transmission' },
    ],
  },
  {
    id: 'cadillacEscalade',
    year: 2020,
    image: '/images/car-4.jpg',
    pricePerMonth: 620,
    specs: [
      { icon: 'people', key: 'capacity' },
      { icon: 'energy', key: 'fuel' },
      { icon: 'efficiency', key: 'efficiency' },
      { icon: 'transmission', key: 'transmission' },
    ],
  },
  {
    id: 'bmw4SeriesGti',
    year: 2021,
    image: '/images/car-5.jpg',
    pricePerMonth: 530,
    specs: [
      { icon: 'people', key: 'capacity' },
      { icon: 'energy', key: 'fuel' },
      { icon: 'efficiency', key: 'efficiency' },
      { icon: 'transmission', key: 'transmission' },
    ],
  },
  {
    id: 'bmw4Series',
    year: 2019,
    image: '/images/car-6.jpg',
    pricePerMonth: 490,
    specs: [
      { icon: 'people', key: 'capacity' },
      { icon: 'energy', key: 'fuel' },
      { icon: 'efficiency', key: 'efficiency' },
      { icon: 'transmission', key: 'transmission' },
    ],
  },
] satisfies FeaturedCarBase[]

export const stepCardCatalog = [
  {
    id: 'createProfile',
    icon: 'profile-add',
    tone: 'pink',
    linkHref: '#featured-cars',
  },
  {
    id: 'chooseCar',
    icon: 'car',
    tone: 'blue',
  },
  {
    id: 'matchSeller',
    icon: 'person',
    tone: 'green',
  },
  {
    id: 'makeDeal',
    icon: 'payment',
    tone: 'purple',
  },
] satisfies StepCardBase[]

export const footerLinkGroupDefs = [
  {
    key: 'company',
    links: [
      { key: 'aboutUs', href: '#footer' },
      { key: 'pricingPlans', href: '#featured-cars' },
      { key: 'ourProcess', href: '#steps' },
      { key: 'contacts', href: '#footer' },
    ],
  },
  {
    key: 'support',
    links: [
      { key: 'helpCenter', href: '#footer' },
      { key: 'askQuestion', href: '#footer' },
      { key: 'privacyPolicy', href: '#footer' },
      { key: 'termsAndConditions', href: '#footer' },
    ],
  },
  {
    key: 'locations',
    wide: true,
    links: [
      { key: 'airportMenara', href: '#featured-cars' },
      { key: 'gueliz', href: '#featured-cars' },
      { key: 'medina', href: '#featured-cars' },
      { key: 'palmeraie', href: '#featured-cars' },
      { key: 'hivernage', href: '#featured-cars' },
      { key: 'casablanca', href: '#featured-cars' },
      { key: 'agadir', href: '#featured-cars' },
      { key: 'essaouira', href: '#featured-cars' },
    ],
  },
] satisfies FooterLinkGroupDef[]

export const socialLinkDefs = [
  { key: 'facebook', href: 'https://www.facebook.com/profile.php?id=61578329224592', icon: 'facebook' },
  { key: 'instagram', href: 'https://www.instagram.com/chanaa_car/', icon: 'instagram' },
  { key: 'linkedin', href: 'https://www.linkedin.com/company/chan%C3%A2a-car/', icon: 'linkedin' },
  { key: 'mail', href: 'mailto:contact@chanaacar.com', icon: 'mail' },
] satisfies SocialLinkBase[]
