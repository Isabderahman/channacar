<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import { businessInfo } from '~/utils/business-info'

// Floating WhatsApp call-to-action, shown on every public page (see app.vue).
// Number lives in business-info.ts so it stays consistent everywhere.
const phoneDigits = businessInfo.whatsapp.replace(/\D/g, '')

const message = 'Bonjour ChanaaCar, je souhaite des informations sur la location d’une voiture.'

const href = `https://wa.me/${phoneDigits}?text=${encodeURIComponent(message)}`
</script>

<template>
  <a
    :href="href"
    target="_blank"
    rel="noopener"
    aria-label="Contacter ChanaaCar sur WhatsApp"
    class="group fixed bottom-5 end-5 z-50 grid size-14 place-items-center rounded-full bg-[#25D366] text-white shadow-[0_10px_30px_rgba(37,211,102,0.45)] outline-none transition duration-300 ease-out hover:scale-105 hover:bg-[#20bd5a] focus-visible:ring-4 focus-visible:ring-[rgba(37,211,102,0.35)] print:hidden sm:size-16"
  >
    <span
      class="pointer-events-none absolute inset-0 rounded-full bg-[#25D366] opacity-60 motion-safe:animate-ping"
    />
    <BaseIcon name="whatsapp" :size="30" class="relative z-10" />

    <span
      class="pointer-events-none absolute end-full me-3 hidden whitespace-nowrap rounded-full bg-[var(--surface-2)] px-3 py-1.5 text-[0.82rem] font-semibold text-[var(--text-strong)] shadow-[var(--shadow-1)] opacity-0 transition duration-300 group-hover:opacity-100 lg:block"
    >
      Discuter sur WhatsApp
    </span>
  </a>
</template>
