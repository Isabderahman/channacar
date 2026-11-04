<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'
import { useWhatsappFloatMessage } from '~/composables/useWhatsappFloat'
import { whatsappGeneralMessage, whatsappHref } from '~/utils/whatsapp'

// Floating WhatsApp call-to-action, shown on every public page (default
// layout). Pages can override the prefilled message (car detail sets a
// car-specific one). Hidden on the mobile car page, whose fixed booking bar
// and summary already carry WhatsApp.
const { t } = useI18n()
const route = useRoute()
const floatMessage = useWhatsappFloatMessage()
// The layout renders this before an async page sets its message during SSR,
// so the page-specific message is only applied once mounted (no hydration
// mismatch).
const isMounted = ref(false)
onMounted(() => {
  isMounted.value = true
})
const href = computed(() =>
  whatsappHref((isMounted.value && floatMessage.value) || whatsappGeneralMessage),
)
const isCarDetail = computed(() => String(route.name ?? '').startsWith('cars-slug'))
</script>

<template>
  <a
    :href="href"
    target="_blank"
    rel="noopener"
    :aria-label="t('site.actions.whatsappFloat')"
    class="group fixed bottom-5 end-5 z-50 size-14 items-center justify-center bg-accent text-on-accent transition-colors hover:bg-[var(--accent-hover)] print:hidden"
    :class="isCarDetail ? 'hidden lg:flex' : 'flex'"
  >
    <SiteIcon name="whatsapp" :size="24" />

    <span
      class="pointer-events-none absolute end-full me-2 hidden whitespace-nowrap border border-line-strong bg-bg px-3 py-2 font-plex text-[12px] text-ink opacity-0 transition-opacity group-hover:opacity-100 lg:block"
    >
      {{ t('site.actions.whatsappChat') }}
    </span>
  </a>
</template>
