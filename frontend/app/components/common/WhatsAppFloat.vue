<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import { useWhatsappFloatMessage } from '~/composables/useWhatsappFloat'
import { whatsappGeneralMessage, whatsappHref } from '~/utils/whatsapp'

// Floating WhatsApp call-to-action, shown on every public page (see the default
// layout + homepage). Uses the brand colours, not WhatsApp green. Pages can
// override the prefilled message (car detail sets a car-specific one).
const floatMessage = useWhatsappFloatMessage()
const href = computed(() => whatsappHref(floatMessage.value || whatsappGeneralMessage))
</script>

<template>
  <a
    :href="href"
    target="_blank"
    rel="noopener"
    aria-label="Contacter ChanaaCar sur WhatsApp"
    class="group fixed bottom-5 end-5 z-50 grid size-14 place-items-center rounded-full bg-[var(--carolina-blue)] text-[var(--white)] shadow-[var(--shadow-2)] outline-none transition duration-300 ease-out hover:scale-105 focus-visible:ring-4 focus-visible:ring-[color-mix(in_srgb,var(--primary)_35%,transparent)] print:hidden sm:size-16"
  >
    <span
      class="pointer-events-none absolute inset-0 rounded-full bg-[var(--carolina-blue)] opacity-50 motion-safe:animate-ping"
    />
    <BaseIcon name="whatsapp" :size="30" class="relative z-10" />

    <span
      class="pointer-events-none absolute end-full me-3 hidden whitespace-nowrap rounded-full bg-[var(--surface-2)] px-3 py-1.5 text-[0.82rem] font-semibold text-[var(--text-strong)] shadow-[var(--shadow-1)] opacity-0 transition duration-300 group-hover:opacity-100 lg:block"
    >
      Discuter sur WhatsApp
    </span>
  </a>
</template>
