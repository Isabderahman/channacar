<script setup lang="ts">
import BrandLogo from '~/components/common/BrandLogo.vue'
import SiteIcon from '~/components/site/SiteIcon.vue'
import { contactDetails } from '~/utils/home-content'

const { t, locale } = useI18n()
const localePath = useLocalePath()
const switchLocalePath = useSwitchLocalePath()
const route = useRoute()

const isMenuOpen = ref(false)
const menuCloseButton = ref<HTMLButtonElement | null>(null)

// The car page has its own compact mobile header (back · wordmark · call),
// as in the product-mobile mockup.
const isCarDetail = computed(() => String(route.name ?? '').startsWith('cars-slug'))

const navLinks = computed(() =>
  [
    { key: 'cars', to: '/cars' },
    { key: 'marrakech', to: '/location-marrakech' },
    { key: 'airport', to: '/location-aeroport-marrakech-menara' },
    { key: 'longTerm', to: '/location-longue-duree' },
    { key: 'about', to: '/a-propos' },
    { key: 'faq', to: '/faq' },
  ].map((link) => {
    const to = localePath(link.to)

    return {
      label: t(`site.nav.${link.key}`),
      to,
      active: route.path === to || route.path.startsWith(`${to}/`),
    }
  }),
)

const languages = computed(() =>
  ['fr', 'en', 'es', 'ar'].map((code) => ({
    code,
    label: code.toUpperCase(),
    to: switchLocalePath(code as 'fr' | 'en' | 'es' | 'ar'),
    active: code === locale.value,
  })),
)

// "Réserver" jumps to the booking form on a car page, otherwise to the
// homepage booking strip.
const bookTarget = computed(() =>
  isCarDetail.value ? { hash: '#reserver' } : { path: localePath('/'), hash: '#reserver' },
)

const fleetPath = computed(() => localePath('/cars'))
const homePath = computed(() => localePath('/'))

const openMenu = async () => {
  isMenuOpen.value = true
  await nextTick()
  menuCloseButton.value?.focus()
}

const closeMenu = () => {
  isMenuOpen.value = false
}

const onKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && isMenuOpen.value) {
    closeMenu()
  }
}

watch(() => route.fullPath, closeMenu)

watch(isMenuOpen, (open) => {
  if (import.meta.client) {
    document.documentElement.style.overflow = open ? 'hidden' : ''
  }
})

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
  document.documentElement.style.overflow = ''
})
</script>

<template>
  <!-- Utility bar -->
  <div
    class="border-b border-line"
    :class="isCarDetail ? 'hidden lg:block' : ''"
  >
    <div class="wrap flex flex-wrap items-center justify-between gap-3 py-2 font-plex text-[11px] text-muted lg:py-2.5 lg:text-[12px]">
      <span>
        <span class="hidden sm:inline">{{ t('site.topbar.availability') }}</span>
        <span class="sm:hidden">{{ t('site.topbar.availabilityShort') }}</span>
      </span>

      <span class="flex items-center gap-6">
        <a :href="contactDetails.phoneHref" class="text-ink transition-colors hover:text-accent" dir="ltr">
          {{ contactDetails.phoneLabel }}
        </a>

        <span class="hidden items-center gap-1.5 sm:flex" :aria-label="t('site.language')">
          <template v-for="(lang, index) in languages" :key="lang.code">
            <span v-if="index > 0" aria-hidden="true">·</span>
            <span v-if="lang.active" class="text-ink" aria-current="true">{{ lang.label }}</span>
            <NuxtLink
              v-else
              :to="lang.to"
              :hreflang="lang.code"
              class="transition-colors hover:text-accent"
            >
              {{ lang.label }}
            </NuxtLink>
          </template>
        </span>
      </span>
    </div>
  </div>

  <!-- Compact car-page header (mobile only) -->
  <header
    v-if="isCarDetail"
    class="flex items-center justify-between border-b border-line px-5 py-2 lg:hidden"
  >
    <NuxtLink
      :to="fleetPath"
      :aria-label="t('site.actions.back')"
      class="-ms-3 flex size-11 items-center justify-center transition-colors hover:text-accent"
    >
      <SiteIcon name="arrow-left" :size="20" />
    </NuxtLink>

    <BrandLogo :to="homePath" size="sm" />

    <a
      :href="contactDetails.phoneHref"
      :aria-label="t('site.actions.call')"
      class="-me-3 flex size-11 items-center justify-center transition-colors hover:text-accent"
    >
      <SiteIcon name="phone" :size="20" />
    </a>
  </header>

  <!-- Main header -->
  <header
    class="wrap flex items-center justify-between gap-6 py-2.5 xl:py-4"
    :class="isCarDetail ? 'hidden lg:flex' : ''"
  >
    <BrandLogo :to="homePath" />

    <nav :aria-label="t('site.nav.aria')" class="hidden flex-wrap gap-8 text-[15px] xl:flex">
      <NuxtLink
        v-for="link in navLinks"
        :key="link.to"
        :to="link.to"
        :aria-current="link.active ? 'page' : undefined"
        class="transition-colors hover:text-accent"
        :class="link.active ? 'text-accent' : ''"
      >
        {{ link.label }}
      </NuxtLink>
    </nav>

    <div class="flex items-center gap-3">
      <NuxtLink
        :to="bookTarget"
        class="hidden min-h-11 items-center border border-ink px-5 text-[15px] font-medium transition-colors hover:border-accent hover:text-accent sm:inline-flex"
      >
        {{ t('site.actions.book') }}
      </NuxtLink>

      <button
        type="button"
        class="flex size-11 items-center justify-center border border-line-strong transition-colors hover:border-ink xl:hidden"
        :aria-label="t('site.actions.menuOpen')"
        :aria-expanded="isMenuOpen ? 'true' : 'false'"
        aria-controls="site-menu"
        @click="openMenu"
      >
        <SiteIcon name="menu" />
      </button>
    </div>
  </header>

  <!-- Mobile / tablet menu -->
  <div
    v-if="isMenuOpen"
    id="site-menu"
    role="dialog"
    aria-modal="true"
    :aria-label="t('site.nav.aria')"
    class="fixed inset-0 z-[60] flex flex-col overflow-y-auto bg-bg xl:hidden"
  >
    <div class="wrap flex items-center justify-between py-3.5">
      <BrandLogo :to="homePath" />

      <button
        ref="menuCloseButton"
        type="button"
        class="flex size-11 items-center justify-center border border-line-strong transition-colors hover:border-ink"
        :aria-label="t('site.actions.menuClose')"
        @click="closeMenu"
      >
        <SiteIcon name="close" />
      </button>
    </div>

    <nav :aria-label="t('site.nav.aria')" class="wrap mt-6 flex-1">
      <ul class="border-t border-line">
        <li v-for="link in navLinks" :key="`menu-${link.to}`" class="border-b border-line">
          <NuxtLink
            :to="link.to"
            :aria-current="link.active ? 'page' : undefined"
            class="flex min-h-14 items-center justify-between gap-4 py-3 text-[26px] font-medium tracking-[-0.03em] transition-colors hover:text-accent"
            :class="link.active ? 'text-accent' : ''"
          >
            {{ link.label }}
            <SiteIcon name="arrow-right" />
          </NuxtLink>
        </li>
        <li class="border-b border-line">
          <NuxtLink
            :to="localePath('/contact')"
            class="flex min-h-14 items-center justify-between gap-4 py-3 text-[26px] font-medium tracking-[-0.03em] transition-colors hover:text-accent"
          >
            {{ t('site.nav.contact') }}
            <SiteIcon name="arrow-right" />
          </NuxtLink>
        </li>
      </ul>
    </nav>

    <div class="wrap flex flex-col gap-2 pb-8 pt-10">
      <NuxtLink :to="bookTarget" class="btn btn-primary min-h-14" @click="closeMenu">
        {{ t('site.actions.book') }}
        <SiteIcon name="arrow-right" />
      </NuxtLink>
      <a :href="contactDetails.phoneHref" class="btn btn-outline min-h-14" dir="ltr">
        {{ contactDetails.phoneLabel }}
        <SiteIcon name="phone" />
      </a>

      <div class="mt-6 flex items-center gap-2 font-plex text-[13px] text-muted" :aria-label="t('site.language')">
        <template v-for="lang in languages" :key="`menu-${lang.code}`">
          <span
            v-if="lang.active"
            class="flex min-h-11 min-w-11 items-center justify-center border border-ink text-ink"
            aria-current="true"
          >
            {{ lang.label }}
          </span>
          <NuxtLink
            v-else
            :to="lang.to"
            :hreflang="lang.code"
            class="flex min-h-11 min-w-11 items-center justify-center border border-line-strong transition-colors hover:border-ink hover:text-ink"
          >
            {{ lang.label }}
          </NuxtLink>
        </template>
      </div>
    </div>
  </div>
</template>
