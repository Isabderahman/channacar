<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { MarketingContactGroup } from '~/utils/marketing-content'

defineProps<{
  groups: MarketingContactGroup[]
  subtitle?: string
  title: string
}>()
</script>

<template>
  <div>
    <h2 class="ridex-title-2">
      {{ title }}
    </h2>
    <p v-if="subtitle" class="mt-3 max-w-[58ch] text-[0.96rem] leading-7 text-[var(--text-muted)]">
      {{ subtitle }}
    </p>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
      <article
        v-for="group in groups"
        :key="group.label"
        class="rounded-[22px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-5"
      >
        <div class="flex items-center gap-3">
          <BaseIcon
            :name="
              group.label === 'Téléphone'
                ? 'phone'
                : group.label === 'WhatsApp'
                  ? 'whatsapp'
                  : group.label === 'Email'
                    ? 'mail'
                    : group.label === 'Adresse'
                      ? 'map-pin'
                      : 'clock'
            "
            :size="18"
            class="text-[var(--primary)]"
          />
          <h3 class="font-heading text-[1.05rem] text-[var(--text-strong)]">
            {{ group.label }}
          </h3>
        </div>
        <ul class="mt-4 space-y-2 text-[0.95rem] leading-6 text-[var(--text-muted)]">
          <li v-for="value in group.values" :key="value">
            <a
              v-if="group.label === 'Email'"
              :href="`mailto:${value}`"
              class="transition duration-300 hover:text-[var(--primary)]"
            >
              {{ value }}
            </a>
            <a
              v-else-if="group.label === 'WhatsApp'"
              :href="`https://wa.me/${value.replace(/\D/g, '')}`"
              target="_blank"
              rel="noopener"
              class="transition duration-300 hover:text-[var(--primary)]"
            >
              {{ value }}
            </a>
            <a
              v-else-if="group.label === 'Téléphone'"
              :href="`tel:${value.replace(/\s+/g, '')}`"
              class="transition duration-300 hover:text-[var(--primary)]"
            >
              {{ value }}
            </a>
            <span v-else>{{ value }}</span>
          </li>
        </ul>
      </article>
    </div>
  </div>
</template>
