<script setup lang="ts">
import type { MarketingContactGroup } from '~/utils/marketing-content'

defineProps<{
  groups: MarketingContactGroup[]
  subtitle?: string
  title: string
}>()
</script>

<template>
  <div>
    <div class="border-b border-ink pb-5 md:pb-8">
      <h2 class="text-[32px] font-medium leading-none tracking-[-0.035em] lg:text-[40px]">
        {{ title }}
      </h2>
      <p v-if="subtitle" class="mt-4 max-w-[52ch] text-[15px] text-ink-2 lg:text-[16px]">
        {{ subtitle }}
      </p>
    </div>

    <dl>
      <div
        v-for="group in groups"
        :key="group.label"
        class="grid gap-2 border-b border-line py-5 sm:grid-cols-[140px_1fr] sm:gap-6"
      >
        <dt class="mono-label pt-1">
          {{ group.label }}
        </dt>
        <dd class="flex flex-col gap-1 text-[17px] lg:text-[19px]">
          <template v-for="value in group.values" :key="value">
            <a
              v-if="group.label === 'Email'"
              :href="`mailto:${value}`"
              class="w-fit transition-colors hover:text-accent"
            >
              {{ value }}
            </a>
            <a
              v-else-if="group.label === 'WhatsApp'"
              :href="`https://wa.me/${value.replace(/\D/g, '')}`"
              target="_blank"
              rel="noopener"
              class="w-fit transition-colors hover:text-accent"
              dir="ltr"
            >
              {{ value }}
            </a>
            <a
              v-else-if="group.label === 'Téléphone'"
              :href="`tel:${value.replace(/\s+/g, '')}`"
              class="w-fit transition-colors hover:text-accent"
              dir="ltr"
            >
              {{ value }}
            </a>
            <span v-else>{{ value }}</span>
          </template>
        </dd>
      </div>
    </dl>
  </div>
</template>
