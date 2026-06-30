<script setup lang="ts">
import { computed, ref } from 'vue'
import BrandLogo from '~/components/common/BrandLogo.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import { marketingNavLinks } from '~/utils/marketing-content'

const { locale, locales, setLocale } = useI18n()
const localePath = useLocalePath()
const route = useRoute()

const isMenuOpen = ref(false)

const navItems = computed(() =>
  marketingNavLinks.map((item) => ({
    label: item.label,
    to: item.to.startsWith('/admin') ? item.to : localePath(item.to),
  })),
)

const localeOptions = computed(() =>
  locales.value.map((entry) => {
    if (typeof entry === 'string') {
      return { code: entry, name: entry.toUpperCase() }
    }

    return {
      code: String(entry.code),
      name: entry.name ? String(entry.name) : String(entry.code).toUpperCase(),
    }
  }),
)

const changeLocale = async (event: Event) => {
  const nextLocale = (event.target as HTMLSelectElement).value

  if (nextLocale !== locale.value) {
    await setLocale(nextLocale)
  }
}

const isActive = (to: string) => route.path === to
</script>

<template>
  <header class="fixed inset-x-0 top-0 z-40 border-b border-[var(--surface-border)] bg-[color-mix(in_srgb,var(--alice-blue-1)_88%,transparent)] backdrop-blur">
    <div class="section-shell flex h-24 items-center justify-between gap-4">
      <BrandLogo brand-name="ChanaaCar" tagline="location de voiture" :to="localePath('/')" />

      <button
        type="button"
        class="grid size-11 place-items-center rounded-full border border-[var(--surface-border)] text-[var(--text-muted)] lg:hidden"
        @click="isMenuOpen = !isMenuOpen"
      >
        <BaseIcon name="dashboard" :size="18" />
      </button>

      <div
        class="fixed inset-x-4 top-24 rounded-[24px] border border-[var(--surface-border)] bg-[var(--surface-1)] p-5 shadow-[var(--shadow-1)] lg:static lg:inset-auto lg:flex lg:items-center lg:gap-4 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none"
        :class="isMenuOpen ? 'block' : 'hidden lg:flex'"
      >
        <nav class="lg:mr-4">
          <ul class="grid gap-3 xl:flex xl:items-center xl:gap-1.5">
            <li v-for="item in navItems" :key="item.to">
              <NuxtLink
                :to="item.to"
                class="inline-flex rounded-full px-3 py-2 text-[0.84rem] transition duration-300"
                :class="
                  isActive(item.to)
                    ? 'bg-[var(--primary-soft)] text-[var(--primary)]'
                    : 'text-[var(--text-muted)] hover:bg-[var(--surface-2)] hover:text-[var(--text-strong)]'
                "
                @click="isMenuOpen = false"
              >
                {{ item.label }}
              </NuxtLink>
            </li>
          </ul>
        </nav>

        <div class="mt-4 flex flex-wrap items-center gap-3 lg:mt-0">
          <div class="relative min-w-[9rem]">
            <select
              :value="locale"
              class="w-full appearance-none rounded-full border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-2 pe-10 text-[0.9rem] text-[var(--text-strong)] outline-none"
              @change="changeLocale"
            >
              <option v-for="option in localeOptions" :key="option.code" :value="option.code">
                {{ option.name }}
              </option>
            </select>

            <BaseIcon
              name="arrow-right"
              :size="16"
              class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 rotate-90 text-[var(--text-subtle)]"
            />
          </div>

          <BaseButton :to="localePath('/cars')" variant="ghost">
            <BaseIcon name="car" :size="18" />
            <span>Réserver</span>
          </BaseButton>

          <BaseButton href="tel:+212666623645" variant="secondary">
            <BaseIcon name="phone" :size="18" />
            <span>+212 6 66 62 36 45</span>
          </BaseButton>
        </div>
      </div>
    </div>
  </header>
</template>
