<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'

interface Props {
  actionHref?: string
  actionLabel?: string
  title: string
}

withDefaults(defineProps<Props>(), {
  actionHref: undefined,
  actionLabel: undefined,
})
</script>

<template>
  <div class="mb-[30px] flex flex-wrap items-end justify-between gap-3">
    <h2 class="ridex-title-2">
      {{ title }}
    </h2>

    <a
      v-if="actionHref && actionLabel"
      :href="actionHref"
      class="ridex-inline-link"
    >
      <span>{{ actionLabel }}</span>
      <BaseIcon name="arrow-right" :size="16" />
    </a>
  </div>
</template>
