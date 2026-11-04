<script setup lang="ts">
import { socialLinkDefs } from '~/utils/home-content'

const { t } = useI18n()
const localePath = useLocalePath()
const route = useRoute()

// The car page uses the compact footer from the product mockup, and leaves
// room for its fixed mobile booking bar.
const isCarDetail = computed(() => String(route.name ?? '').startsWith('cars-slug'))

const year = new Date().getFullYear()

const groups = computed(() => [
  {
    title: t('site.footer.company'),
    links: [
      { label: t('site.footer.about'), to: localePath('/a-propos') },
      { label: t('site.footer.pricing'), to: localePath('/cars') },
      { label: t('site.footer.process'), to: { path: localePath('/'), hash: '#etapes' } },
      { label: t('site.footer.contact'), to: localePath('/contact') },
    ],
  },
  {
    title: t('site.footer.support'),
    links: [
      { label: t('site.footer.help'), to: localePath('/faq') },
      { label: t('site.footer.ask'), to: localePath('/contact') },
    ],
  },
])

const socials = computed(() =>
  socialLinkDefs
    .filter((link) => link.key !== 'mail')
    .map((link) => ({ ...link, label: t(`footer.social.${link.key}`) })),
)
</script>

<template>
  <footer
    class="border-t border-line"
    :class="isCarDetail ? 'mt-24 pb-[84px] lg:mt-36 lg:pb-0' : ''"
  >
    <div
      v-if="!isCarDetail"
      class="wrap grid gap-10 pb-8 pt-8 text-[14px] sm:grid-cols-2 lg:grid-cols-4 lg:pt-14"
    >
      <div class="max-w-[420px] sm:col-span-2">
        <p class="mb-3 text-[18px] font-semibold tracking-[-0.02em] lg:mb-4 lg:text-[20px]">
          Chanaa<span class="text-accent">Car</span>
        </p>
        <p class="text-muted">
          <span class="lg:hidden">{{ t('site.footer.descriptionShort') }}</span>
          <span class="hidden lg:inline">{{ t('site.footer.description') }}</span>
        </p>
      </div>

      <nav
        v-for="group in groups"
        :key="group.title"
        :aria-label="group.title"
        class="hidden flex-col gap-2.5 sm:flex"
      >
        <p class="mono-label mb-1.5">{{ group.title }}</p>
        <NuxtLink
          v-for="link in group.links"
          :key="link.label"
          :to="link.to"
          class="w-fit transition-colors hover:text-accent"
        >
          {{ link.label }}
        </NuxtLink>
      </nav>
    </div>

    <div
      class="wrap flex flex-wrap items-center justify-between gap-4 pb-8 font-plex text-[11px] text-muted lg:text-[12px]"
      :class="isCarDetail ? 'pt-8' : ''"
    >
      <span>© {{ year }} ChanaaCar. {{ t('site.footer.rights') }}</span>

      <span class="flex flex-wrap gap-x-6 gap-y-2" :aria-label="t('site.footer.social')">
        <a
          v-for="social in socials"
          :key="social.key"
          :href="social.href"
          target="_blank"
          rel="noopener noreferrer"
          class="transition-colors hover:text-ink"
        >
          {{ social.label }}
        </a>
      </span>
    </div>
  </footer>
</template>
