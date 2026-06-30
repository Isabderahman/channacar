<script setup lang="ts">
import { computed } from 'vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { StepCardItem, StepTone } from '~/utils/home-content'

const props = defineProps<{
  step: StepCardItem
}>()

const toneClasses = computed<Record<StepTone, string>>(() => ({
  pink: 'bg-[var(--mimi-pink)] text-[var(--deep-cerise)]',
  blue: 'bg-[var(--columbia-blue)] text-[var(--carolina-blue)]',
  green: 'bg-[var(--honey-dew)] text-[var(--medium-sea-green)]',
  purple: 'bg-[var(--pale-purple-pantone)] text-[var(--slate-blue)]',
}))
</script>

<template>
  <article
    class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--alice-blue-2)] p-5 transition duration-500 ease-out hover:bg-[var(--alice-blue-3)] hover:shadow-[var(--shadow-1)]"
  >
    <div
      class="mb-5 grid size-[50px] place-items-center rounded-[14px]"
      :class="toneClasses[step.tone]"
    >
      <BaseIcon :name="step.icon" :size="24" />
    </div>

    <h3 class="mb-[15px] font-heading text-[1.375rem] font-normal text-[var(--space-cadet)]">
      {{ step.title }}
    </h3>

    <p class="text-[var(--independence)]">
      {{ step.description }}
    </p>

    <a
      v-if="step.linkHref && step.linkLabel"
      :href="step.linkHref"
      class="step-link relative mt-5 inline-block w-max text-[var(--carolina-blue)] after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-[var(--carolina-blue)] after:transition-all after:duration-500 hover:after:w-full focus-visible:after:w-full"
    >
      {{ step.linkLabel }}
    </a>
  </article>
</template>
