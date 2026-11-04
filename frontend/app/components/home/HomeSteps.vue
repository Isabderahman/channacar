<script setup lang="ts">
// "Réservez en 4 étapes": four columns separated by hairlines (rows on mobile).
const { t } = useI18n()

const steps = computed(() =>
  (['place', 'car', 'confirm', 'road'] as const).map((key, index) => ({
    key,
    number: String(index + 1).padStart(2, '0'),
    title: t(`site.home.steps.${key}.title`),
    description: t(`site.home.steps.${key}.description`),
  })),
)
</script>

<template>
  <section id="etapes" class="wrap scroll-mt-6 pt-24 lg:pt-40">
    <div v-inview data-anim="stagger" class="mb-8 grid gap-10 lg:mb-14 lg:grid-cols-2">
      <h2 class="display text-[40px] lg:text-[clamp(40px,5vw,72px)]">
        {{ t('site.home.steps.titleLine1') }}<br>{{ t('site.home.steps.titleLine2') }}
      </h2>
      <p class="hidden max-w-[440px] self-end text-[17px] text-ink-2 md:block">{{ t('site.home.steps.lead') }}</p>
    </div>

    <ol v-inview data-anim="stagger" class="grid border-t border-line xl:grid-cols-4">
      <li
        v-for="step in steps"
        :key="step.key"
        class="flex gap-5 border-b border-line py-5 xl:block xl:border-b-0 xl:border-e xl:px-7 xl:pb-0 xl:pt-7 xl:first:ps-0 xl:last:border-e-0 xl:last:pe-0"
      >
        <p class="font-plex text-[12px] text-accent xl:mb-14 xl:text-[13px]">{{ step.number }}</p>
        <div>
          <h3 class="mb-1 text-[18px] font-medium xl:mb-2.5 xl:text-[21px]">{{ step.title }}</h3>
          <p class="text-[14px] text-muted xl:text-[15px]">{{ step.description }}</p>
        </div>
      </li>
    </ol>
  </section>
</template>
