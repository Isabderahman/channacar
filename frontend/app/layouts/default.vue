<script setup lang="ts">
import SiteFooter from '~/components/common/SiteFooter.vue'
import SiteHeader from '~/components/common/SiteHeader.vue'
import WhatsAppFloat from '~/components/common/WhatsAppFloat.vue'
</script>

<template>
  <div class="page-shell">
    <SiteHeader />

    <main>
      <slot />
    </main>

    <SiteFooter />
    <WhatsAppFloat />
  </div>
</template>
