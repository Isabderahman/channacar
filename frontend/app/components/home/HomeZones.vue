<script setup lang="ts">
// Service zones as a large typographic list.
const { t } = useI18n()
</script>

<template>
  <section class="wrap pt-24 lg:pt-40">
    <div class="grid gap-4 lg:grid-cols-2 lg:gap-10 lg:border-t lg:border-ink lg:pt-8">
      <p class="mono-label lg:text-[12px]">{{ t('site.home.zones.label') }}</p>
      <ul class="text-[28px] font-medium leading-[1.2] tracking-[-0.03em] lg:text-[clamp(28px,3.4vw,48px)] lg:leading-[1.15]">
        <li>{{ t('site.home.zones.airport') }}</li>
        <li>
          {{ t('site.home.zones.gueliz') }} <span class="text-faint" aria-hidden="true">/</span>
          {{ t('site.home.zones.medina') }}
        </li>
        <li>
          {{ t('site.home.zones.palmeraie') }} <span class="text-faint" aria-hidden="true">/</span>
          {{ t('site.home.zones.hivernage') }}
        </li>
        <li class="text-muted">{{ t('site.home.zones.cities') }}</li>
      </ul>
    </div>
  </section>
</template>
