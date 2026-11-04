<script setup lang="ts">
interface Props {
  intro?: string
  items: string[]
  title: string
}

withDefaults(defineProps<Props>(), {
  intro: undefined,
})
</script>

<template>
  <div>
    <div v-inview data-anim="rule stagger" class="rule-b flex flex-col gap-4 pb-5 md:flex-row md:flex-wrap md:items-end md:justify-between md:gap-6 md:pb-8">
      <h2 class="display text-[40px] lg:text-[clamp(40px,5vw,72px)]">
        {{ title }}
      </h2>
      <p v-if="intro" class="max-w-[440px] text-[16px] text-ink-2 lg:text-[17px]">
        {{ intro }}
      </p>
    </div>

    <ul v-inview data-anim="stagger" class="grid md:grid-cols-2 md:gap-x-10">
      <li
        v-for="item in items"
        :key="item"
        class="flex gap-3 border-b border-line py-4 text-[15px] text-ink-2 lg:py-5 lg:text-[16px]"
      >
        <span aria-hidden="true" class="text-accent">—</span>
        <span>{{ item }}</span>
      </li>
    </ul>
  </div>
</template>
