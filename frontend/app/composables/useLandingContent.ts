import { computed } from 'vue'
import {
  contactDetails,
  featuredCarCatalog,
  footerLinkGroupDefs,
  headerLinkDefs,
  heroImage,
  heroSearchFieldDefs,
  socialLinkDefs,
  stepCardCatalog,
  type BrandContent,
  type CarSpecKey,
  type ContactInfo,
  type FeaturedCar,
  type FooterLinkGroup,
  type HeroSearchField,
  type LocaleOption,
  type NavItem,
  type SocialLink,
  type StepCardItem,
} from '~/utils/home-content'

interface HeroFieldCopy {
  label: string
  placeholder?: string
}

interface TranslatedCarMessage {
  alt: string
  name: string
  specs: Record<CarSpecKey, string>
}

interface TranslatedStepMessage {
  description: string
  linkLabel?: string
  title: string
}

interface LandingLabels {
  exploreCars: string
  featuredCarsAction: string
  featuredCarsTitle: string
  languageLabel: string
  priceSuffix: string
  profileAria: string
  rentNow: string
  stepsTitle: string
  toggleMenuAria: string
}

export const useLandingContent = () => {
  const { locale, localeProperties, locales, rt, t, tm } = useI18n()

  const brand = computed<BrandContent>(() => ({
    brandName: t('common.brand.name'),
    tagline: t('common.brand.tagline'),
  }))

  const currentLocale = computed(() => String(locale.value))

  const contactInfo = computed<ContactInfo>(() => ({
    ...contactDetails,
    availability: t('header.contact.availability'),
  }))

  const footerDescription = computed(() => t('footer.description'))
  const footerCopyright = computed(() => t('footer.copyright'))

  const headerLinks = computed<NavItem[]>(() =>
    headerLinkDefs.map((link) => ({
      href: link.href,
      label: t(`header.nav.${link.key}`),
    })),
  )

  const heroContent = computed(() => ({
    actionLabel: t('common.actions.viewVehicles'),
    description: t('hero.description'),
    image: heroImage,
    title: t('hero.title'),
  }))

  const heroSearchFields = computed<HeroSearchField[]>(() =>
    heroSearchFieldDefs.map((field) => {
      const copy = tm(`hero.fields.${field.name}`) as HeroFieldCopy

      return {
        ...field,
        label: rt(copy.label),
        options: field.options?.map((option) => ({
          ...option,
          label: t(`hero.options.${option.key}`),
        })),
        placeholder: copy.placeholder ? rt(copy.placeholder) : undefined,
      }
    }),
  )

  const featuredCars = computed<FeaturedCar[]>(() =>
    featuredCarCatalog.map((car) => {
      const copy = tm(`cars.items.${car.id}`) as TranslatedCarMessage

      return {
        ...car,
        alt: rt(copy.alt),
        name: rt(copy.name),
        specs: car.specs.map((spec) => ({
          ...spec,
          label: rt(copy.specs[spec.key]),
        })),
      }
    }),
  )

  const stepCards = computed<StepCardItem[]>(() =>
    stepCardCatalog.map((step) => {
      const copy = tm(`steps.cards.${step.id}`) as TranslatedStepMessage

      return {
        ...step,
        description: rt(copy.description),
        linkLabel: copy.linkLabel ? rt(copy.linkLabel) : undefined,
        title: rt(copy.title),
      }
    }),
  )

  const footerLinkGroups = computed<FooterLinkGroup[]>(() =>
    footerLinkGroupDefs.map((group) => ({
      links: group.links.map((link) => ({
        href: link.href,
        label: t(`footer.groups.${group.key}.links.${link.key}`),
      })),
      title: t(`footer.groups.${group.key}.title`),
      wide: group.wide,
    })),
  )

  const socialLinks = computed<SocialLink[]>(() =>
    socialLinkDefs.map((link) => ({
      ...link,
      label: t(`footer.social.${link.key}`),
    })),
  )

  const localeOptions = computed<LocaleOption[]>(() =>
    locales.value.map((entry) => {
      if (typeof entry === 'string') {
        return {
          code: entry,
          dir: 'ltr',
          name: entry.toUpperCase(),
        }
      }

      return {
        code: String(entry.code),
        dir: entry.dir === 'rtl' ? 'rtl' : 'ltr',
        name: entry.name ? String(entry.name) : String(entry.code).toUpperCase(),
      }
    }),
  )

  const isRtl = computed(() => localeProperties.value.dir === 'rtl')

  const labels = computed<LandingLabels>(() => ({
    exploreCars: t('common.actions.exploreCars'),
    featuredCarsAction: t('sections.featuredCars.actionLabel'),
    featuredCarsTitle: t('sections.featuredCars.title'),
    languageLabel: t('common.languageSwitcher.label'),
    priceSuffix: t('cars.priceSuffix'),
    profileAria: t('header.profileAria'),
    rentNow: t('common.actions.rentNow'),
    stepsTitle: t('sections.steps.title'),
    toggleMenuAria: t('header.toggleMenuAria'),
  }))

  const getFavoriteAriaLabel = (name: string) => t('cars.favoriteAria', { name })

  return {
    brand,
    contactInfo,
    currentLocale,
    featuredCars,
    footerCopyright,
    footerDescription,
    footerLinkGroups,
    getFavoriteAriaLabel,
    headerLinks,
    heroContent,
    heroSearchFields,
    isRtl,
    labels,
    localeOptions,
    socialLinks,
    stepCards,
  }
}
