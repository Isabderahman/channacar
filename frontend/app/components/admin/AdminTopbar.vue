<script setup lang="ts">
import { computed } from 'vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import { ADMIN_NAV_ITEMS } from '~/utils/constants'

const emit = defineEmits<{
  toggleSidebar: []
}>()

const route = useRoute()

const { isDark, toggle: toggleTheme } = useTheme()

const currentLabel = computed(() => {
  const match = ADMIN_NAV_ITEMS.find(
    (item) => route.path === item.to || route.path.startsWith(`${item.to}/`),
  )

  return match?.label ?? 'Admin'
})
</script>

<template>
  <header class="fixed inset-x-0 top-0 z-30 border-b border-[var(--surface-border)] bg-[color-mix(in_srgb,var(--surface-1)_90%,transparent)] backdrop-blur lg:left-[18rem]">
    <div class="flex h-20 items-center justify-between gap-4 px-4 md:px-6 xl:px-8">
      <div class="flex items-center gap-3">
        <button
          type="button"
          class="grid size-11 place-items-center rounded-full border border-[var(--surface-border)] text-[var(--text-muted)] lg:hidden"
          @click="emit('toggleSidebar')"
        >
          <BaseIcon name="dashboard" :size="18" />
        </button>

        <div>
          <p class="text-[0.76rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]">
            ChannaCar admin
          </p>
          <h1 class="font-heading text-[1.35rem] text-[var(--text-strong)]">
            {{ currentLabel }}
          </h1>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          class="grid size-11 place-items-center rounded-full border border-[var(--surface-border)] text-[var(--text-muted)] transition duration-300 ease-out hover:border-[var(--primary-border)] hover:text-[var(--primary)]"
          :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          @click="toggleTheme"
        >
          <BaseIcon :name="isDark ? 'sun' : 'moon'" :size="18" />
        </button>

        <BaseButton to="/" variant="secondary">
          <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
          <span>View site</span>
        </BaseButton>
      </div>
    </div>
  </header>
</template>
