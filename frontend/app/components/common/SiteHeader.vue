<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import BrandLogo from '~/components/common/BrandLogo.vue'
import { useLandingContent } from '~/composables/useLandingContent'
import { marketingNavLinks } from '~/utils/marketing-content'

const { setLocale } = useI18n()
const localePath = useLocalePath()

const { brand, contactInfo, currentLocale, isRtl, labels, localeOptions } =
  useLandingContent()

const { isDark, toggle: toggleTheme } = useTheme()

const isMenuOpen = ref(false)
const isScrolled = ref(false)

const closeMenu = () => {
  isMenuOpen.value = false
}

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const handleScroll = () => {
  isScrolled.value = window.scrollY > 8
}

const homePath = computed(() => localePath('/'))
const customerHeaderLinks = computed(() =>
  marketingNavLinks.map((link) => ({
    label: link.label,
    to: localePath(link.to),
  })),
)

const menuSideClass = computed(() => (isRtl.value ? 'left-0' : 'right-0'))

const menuStateClass = computed(() => {
  if (isMenuOpen.value) {
    return 'visible translate-x-0'
  }

  return isRtl.value
    ? 'invisible -translate-x-full xl:visible xl:translate-x-0'
    : 'invisible translate-x-full xl:visible xl:translate-x-0'
})

const handleLocaleChange = (event: Event) => {
  const nextLocale = (event.target as HTMLSelectElement).value

  if (nextLocale !== currentLocale.value) {
    closeMenu()
    void setLocale(nextLocale)
  }
}

onMounted(() => {
  handleScroll()
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <header
    class="fixed inset-x-0 top-0 z-50 bg-[var(--alice-blue-1)] transition duration-500 ease-out"
    :class="isScrolled ? 'shadow-[var(--shadow-1)]' : ''"
  >
    <div class="mx-auto flex h-[70px] w-full max-w-[1480px] items-center gap-4 px-4 md:px-5 xl:gap-6">
      <div class="shrink-0">
        <BrandLogo
          :brand-name="brand.brandName"
          :to="homePath"
        />
      </div>

      <div
        v-if="isMenuOpen"
        class="fixed inset-x-0 bottom-0 top-[70px] bg-black/60 xl:hidden"
        @click="closeMenu"
      />

      <nav
        id="site-nav"
        class="fixed bottom-0 top-[70px] z-10 w-full max-w-[260px] border border-[var(--surface-border)] bg-[var(--alice-blue-1)] px-[25px] py-5 shadow-[var(--shadow-1)] transition duration-500 ease-out xl:static xl:min-w-0 xl:flex-1 xl:max-w-none xl:border-0 xl:bg-transparent xl:p-0 xl:shadow-none"
        :class="[menuSideClass, menuStateClass]"
      >
        <div class="mb-5 border-b border-[var(--surface-border)] pb-5 sm:hidden">
          <label
            for="locale-select-mobile"
            class="mb-2 block text-[0.75rem] uppercase tracking-[0.18em] text-[var(--manatee)]"
          >
            {{ labels.languageLabel }}
          </label>

          <select
            id="locale-select-mobile"
            :value="currentLocale"
            class="w-full appearance-none rounded-[16px] border border-[var(--surface-border)] bg-[var(--alice-blue-2)] px-4 py-3 text-[0.875rem] text-[var(--space-cadet)] outline-2 outline-transparent transition duration-500 ease-out focus:border-[var(--carolina-blue)] focus:outline-[var(--carolina-blue)]"
            @change="handleLocaleChange"
          >
            <option
              v-for="option in localeOptions"
              :key="`mobile-${option.code}`"
              :value="option.code"
            >
              {{ option.name }}
            </option>
          </select>
        </div>

        <ul class="xl:flex xl:items-center xl:justify-center xl:gap-4 2xl:gap-6">
          <li
            v-for="link in customerHeaderLinks"
            :key="link.to"
          >
            <NuxtLink
              :to="link.to"
              class="mb-[15px] block py-[5px] font-heading text-[0.875rem] text-[var(--independence)] transition duration-500 ease-out hover:text-[var(--carolina-blue)] focus-visible:text-[var(--carolina-blue)] xl:mb-0 xl:whitespace-nowrap xl:text-[0.82rem] xl:font-semibold 2xl:text-[0.875rem]"
              @click="closeMenu"
            >
              {{ link.label }}
            </NuxtLink>
          </li>
        </ul>
      </nav>

      <div class="ml-auto flex shrink-0 items-center gap-3 md:gap-4 xl:gap-5">
        <div class="hidden shrink-0 text-right 2xl:block">
          <a
            :href="contactInfo.phoneHref"
            class="whitespace-nowrap font-heading text-[0.95rem] leading-none text-[var(--space-cadet)] transition duration-500 ease-out hover:text-[var(--carolina-blue)] focus-visible:text-[var(--carolina-blue)]"
          >
            {{ contactInfo.phoneLabel }}
          </a>
          <span class="mt-1 block whitespace-nowrap text-[0.625rem] text-[var(--independence)]">
            {{ contactInfo.availability }}
          </span>
        </div>

        <div class="relative hidden shrink-0 sm:block">
          <label
            for="locale-select"
            class="sr-only"
          >
            {{ labels.languageLabel }}
          </label>

          <select
            id="locale-select"
            :value="currentLocale"
            class="appearance-none rounded-full border border-[var(--surface-border)] bg-[var(--alice-blue-2)] px-4 py-2 pe-10 text-[0.875rem] text-[var(--space-cadet)] outline-2 outline-transparent transition duration-500 ease-out focus:border-[var(--carolina-blue)] focus:outline-[var(--carolina-blue)]"
            @change="handleLocaleChange"
          >
            <option
              v-for="option in localeOptions"
              :key="option.code"
              :value="option.code"
            >
              {{ option.name }}
            </option>
          </select>

          <BaseIcon
            name="arrow-right"
            :size="16"
            class="pointer-events-none absolute end-4 top-1/2 -translate-y-1/2 rotate-90 text-[var(--independence)]"
          />
        </div>

        <button
          type="button"
          class="grid size-10 shrink-0 place-items-center rounded-full border border-[var(--surface-border)] text-[var(--text-muted)] transition duration-300 ease-out hover:border-[var(--primary-border)] hover:text-[var(--primary)]"
          :aria-label="isDark ? 'Activer le mode clair' : 'Activer le mode sombre'"
          @click="toggleTheme"
        >
          <BaseIcon :name="isDark ? 'sun' : 'moon'" :size="18" />
        </button>

        <BaseButton
          :to="localePath('/cars')"
          :aria-label="labels.exploreCars"
          class="shrink-0"
        >
          <BaseIcon name="car" :size="18" class="sm:hidden" />
          <span class="hidden font-normal sm:inline">{{ labels.exploreCars }}</span>
        </BaseButton>

        <BaseButton
          :to="localePath('/contact')"
          variant="ghost"
          size="icon"
          class="shrink-0"
          aria-label="Contact"
        >
          <BaseIcon name="mail" :size="18" />
        </BaseButton>

        <button
          type="button"
          class="grid h-10 w-10 place-items-center xl:hidden"
          :aria-expanded="isMenuOpen ? 'true' : 'false'"
          :aria-label="labels.toggleMenuAria"
          aria-controls="site-nav"
          @click="toggleMenu"
        >
          <span class="sr-only">{{ labels.toggleMenuAria }}</span>
          <span class="flex flex-col items-end">
            <span
              class="mb-2 block h-0.5 w-[22px] rounded-full bg-[var(--independence)] transition duration-500 ease-out"
              :class="isMenuOpen ? 'w-[22px] bg-[var(--carolina-blue)]' : ''"
            />
            <span
              class="mb-2 block h-0.5 w-[15.4px] rounded-full bg-[var(--independence)] transition duration-500 ease-out"
              :class="isMenuOpen ? 'w-[22px] bg-[var(--carolina-blue)]' : ''"
            />
            <span
              class="block h-0.5 w-[8.8px] rounded-full bg-[var(--independence)] transition duration-500 ease-out"
              :class="isMenuOpen ? 'w-[22px] bg-[var(--carolina-blue)]' : ''"
            />
          </span>
        </button>
      </div>
    </div>
  </header>
</template>
