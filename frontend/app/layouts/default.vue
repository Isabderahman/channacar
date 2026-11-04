<script setup lang="ts">
import SiteFooter from '~/components/common/SiteFooter.vue'
import SiteHeader from '~/components/common/SiteHeader.vue'
import WhatsAppFloat from '~/components/common/WhatsAppFloat.vue'

// Public site shell (tokens and fonts are global — see main.css / nuxt.config).
</script>

<template>
  <div class="site">
    <SiteHeader />

    <main>
      <slot />
    </main>

    <SiteFooter />
    <WhatsAppFloat />
  </div>
</template>
