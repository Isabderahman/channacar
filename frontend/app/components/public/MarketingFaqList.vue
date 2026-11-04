<script setup lang="ts">
import type { MarketingFaqItem } from '~/utils/marketing-content'

defineProps<{
  items: MarketingFaqItem[]
  title: string
}>()

// Answers stay in the DOM when collapsed (v-show), so the FAQPage content
// remains readable to crawlers.
const open = ref(0)
const toggle = (index: number) => {
  open.value = open.value === index ? -1 : index
}
</script>

<template>
  <div>
    <h2 class="display border-b border-ink pb-5 text-[40px] md:pb-8 lg:text-[clamp(40px,5vw,72px)]">
      {{ title }}
    </h2>

    <div>
      <div v-for="(item, index) in items" :key="item.question" class="border-b border-line">
        <h3>
          <button
            :id="`faq-list-q-${index}`"
            type="button"
            class="flex min-h-14 w-full items-center justify-between gap-4 py-4 text-start text-[16px] transition-colors hover:text-accent lg:py-5 lg:text-[19px]"
            :aria-expanded="open === index ? 'true' : 'false'"
            :aria-controls="`faq-list-a-${index}`"
            @click="toggle(index)"
          >
            {{ item.question }}
            <span aria-hidden="true" class="font-plex text-accent">{{ open === index ? '−' : '+' }}</span>
          </button>
        </h3>
        <p
          v-show="open === index"
          :id="`faq-list-a-${index}`"
          role="region"
          :aria-labelledby="`faq-list-q-${index}`"
          class="mb-5 max-w-[680px] text-[15px] text-ink-2 lg:mb-6 lg:text-[16px]"
        >
          {{ item.answer }}
        </p>
      </div>
    </div>
  </div>
</template>
