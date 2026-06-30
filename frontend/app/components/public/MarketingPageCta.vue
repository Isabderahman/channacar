<script setup lang="ts">
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

interface ActionLink {
  label: string
  to: string
}

defineProps<{
  description: string
  primaryAction: ActionLink
  secondaryAction: ActionLink
  title: string
}>()

const localePath = useLocalePath()
</script>

<template>
  <section class="section-shell pb-12">
    <div class="rounded-[32px] border border-[var(--surface-border)] bg-[linear-gradient(135deg,color-mix(in_srgb,var(--primary)_16%,transparent),transparent_55%),linear-gradient(180deg,var(--surface-1),var(--surface-2))] p-7 shadow-[var(--shadow-1)] md:p-10">
      <h2 class="ridex-title-2">
        {{ title }}
      </h2>
      <p class="mt-4 max-w-[58ch] text-[0.98rem] leading-7 text-[var(--text-muted)]">
        {{ description }}
      </p>
      <div class="mt-7 flex flex-wrap gap-3">
        <BaseButton :to="localePath(primaryAction.to)">
          <BaseIcon name="arrow-right" :size="18" />
          <span>{{ primaryAction.label }}</span>
        </BaseButton>
        <BaseButton :to="localePath(secondaryAction.to)" variant="ghost">
          <BaseIcon name="mail" :size="18" />
          <span>{{ secondaryAction.label }}</span>
        </BaseButton>
      </div>
    </div>
  </section>
</template>
