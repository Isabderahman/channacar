<script setup lang="ts">
const localeHead = useLocaleHead({
  dir: true,
  lang: true,
  seo: false,
})

const { theme } = useTheme()

useHead(() => ({
  htmlAttrs: {
    ...localeHead.value.htmlAttrs,
    'data-theme': theme.value,
  },
}))
</script>

<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
