<script setup lang="ts">
interface Props {
  eyebrow?: string
  intro?: string
  paragraphs: string[]
  title: string
}

withDefaults(defineProps<Props>(), {
  eyebrow: undefined,
  intro: undefined,
})
</script>

<template>
  <section class="wrap pt-24 lg:pt-40">
    <div class="grid gap-8 border-t border-ink pt-8 lg:grid-cols-2 lg:gap-10">
      <div>
        <p v-if="eyebrow" class="mono-label mb-4 lg:text-[12px]">
          {{ eyebrow }}
        </p>
        <h2 class="display max-w-[16ch] text-[32px] lg:text-[clamp(32px,3.6vw,52px)]">
          {{ title }}
        </h2>
        <p v-if="intro" class="mt-5 max-w-[42ch] text-[15px] text-muted lg:text-[16px]">
          {{ intro }}
        </p>
      </div>

      <div class="space-y-4 text-[16px] leading-[1.65] text-ink-2 lg:text-[17px]">
        <p v-for="paragraph in paragraphs" :key="paragraph">
          {{ paragraph }}
        </p>
      </div>
    </div>
  </section>
</template>
