<script setup lang="ts">
import { computed } from 'vue'
import BrandLogo from '~/components/common/BrandLogo.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import { useLandingContent } from '~/composables/useLandingContent'

const localePath = useLocalePath()

const { brand, footerCopyright, footerDescription, footerLinkGroups, isRtl, socialLinks } =
  useLandingContent()

const homePath = computed(() => localePath('/'))
const homeAnchorPath = computed(() => `${homePath.value}#home`)
const footerBarClass = computed(() => (isRtl.value ? 'md:flex-row' : 'md:flex-row-reverse'))
const resolvedFooterLinkGroups = computed(() =>
  footerLinkGroups.value.map((group) => ({
    ...group,
    links: group.links.map((link) => ({
      ...link,
      href: link.href.startsWith('#') ? `${homePath.value}${link.href}` : link.href,
    })),
  })),
)
</script>

<template>
  <footer id="footer" class="bg-[var(--alice-blue-4)] text-[var(--independence)]">
    <div class="section-shell">
      <div class="flex flex-wrap items-start justify-between gap-y-[50px] py-[60px]">
        <div class="w-full xl:w-1/3">
          <BrandLogo
            :brand-name="brand.brandName"
            :to="homePath"
            class="md:[&_img]:h-16"
          />

          <p class="mt-5 max-w-[35ch] text-[0.875rem] leading-8">
            {{ footerDescription }}
          </p>
        </div>

        <ul
          v-for="group in resolvedFooterLinkGroups"
          :key="group.title"
          class="font-heading"
          :class="group.wide ? 'w-full columns-2 md:w-full xl:w-1/3' : 'w-1/2 xl:w-1/6'"
        >
          <li class="mb-2 break-inside-avoid">
            <p class="font-semibold text-[var(--space-cadet)]">
              {{ group.title }}
            </p>
          </li>

          <li
            v-for="link in group.links"
            :key="`${group.title}-${link.href}`"
            class="break-inside-avoid"
          >
            <a
              :href="link.href"
              class="block py-1.5 text-[0.875rem] transition duration-500 ease-out hover:text-[var(--carolina-blue)] focus-visible:text-[var(--carolina-blue)]"
            >
              {{ link.label }}
            </a>
          </li>
        </ul>
      </div>

      <div
        class="rounded-t-[18px] border border-[var(--surface-border)] bg-[linear-gradient(to_top,var(--alice-blue-2),var(--alice-blue-3))] p-5 shadow-[var(--shadow-1)] md:flex md:items-center md:justify-between md:py-[30px]"
        :class="footerBarClass"
      >
        <ul class="mb-5 flex items-center gap-5 md:mb-0">
          <li
            v-for="social in socialLinks"
            :key="social.key"
          >
            <a
              :href="social.href"
              :aria-label="social.label"
              :target="social.href.startsWith('http') ? '_blank' : undefined"
              :rel="social.href.startsWith('http') ? 'noopener noreferrer' : undefined"
              class="text-[20px] transition duration-500 ease-out hover:text-[var(--carolina-blue)] focus-visible:text-[var(--carolina-blue)]"
            >
              <BaseIcon :name="social.icon" :size="20" />
            </a>
          </li>
        </ul>

        <p class="text-[0.875rem]">
          &copy; 2026
          <a
            :href="homeAnchorPath"
            class="inline-block transition duration-500 ease-out hover:text-[var(--carolina-blue)] focus-visible:text-[var(--carolina-blue)]"
          >
            {{ brand.brandName }}
          </a>.
          {{ footerCopyright }}
        </p>
      </div>
    </div>
  </footer>
</template>
