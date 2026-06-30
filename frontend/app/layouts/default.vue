<script setup lang="ts">
import SiteFooter from '~/components/common/SiteFooter.vue'
import SiteHeader from '~/components/common/SiteHeader.vue'
</script>

<template>
  <div class="page-shell">
    <SiteHeader />

    <main>
      <slot />
    </main>

    <SiteFooter />
  </div>
</template>
