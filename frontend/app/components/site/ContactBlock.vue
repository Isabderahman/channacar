<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'
import { contactDetails } from '~/utils/home-content'
import { whatsappGeneralMessage, whatsappHref } from '~/utils/whatsapp'

// Orange contact block: the one large accent surface on the page.
const { t } = useI18n()
const whatsappLink = whatsappHref(whatsappGeneralMessage)
</script>

<template>
  <section class="wrap pb-[72px] pt-24 lg:pb-[120px] lg:pt-40">
    <div class="flex flex-col gap-6 bg-accent px-5 py-7 text-on-accent lg:flex-row lg:flex-wrap lg:items-end lg:justify-between lg:gap-10 lg:p-14">
      <h2 class="display text-[36px] lg:flex-[1_1_520px] leading-[0.98] lg:text-[clamp(36px,4.5vw,64px)]">
        {{ t('site.home.contact.line1') }}<br>{{ t('site.home.contact.line2') }}
      </h2>

      <div class="flex flex-col gap-2 lg:min-w-[280px]">
        <a
          :href="whatsappLink"
          target="_blank"
          rel="noopener"
          class="btn min-h-[54px] bg-bg px-4 text-ink transition-colors hover:text-accent lg:min-h-14 lg:px-5"
        >
          {{ t('site.actions.whatsappChat') }}
          <SiteIcon name="arrow-right" />
        </a>
        <a
          :href="contactDetails.phoneHref"
          class="btn hidden min-h-14 border-on-accent text-on-accent hover:bg-bg hover:text-ink lg:flex"
          dir="ltr"
        >
          {{ contactDetails.phoneLabel }}
        </a>
      </div>
    </div>
  </section>
</template>
