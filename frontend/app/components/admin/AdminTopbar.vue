<script setup lang="ts">
import { computed } from 'vue'
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
  <header class="fixed inset-x-0 top-0 z-30 border-b border-line bg-bg lg:left-[18rem]">
    <div class="flex h-20 items-center justify-between gap-4 px-4 md:px-6 xl:px-10">
      <div class="flex items-center gap-4">
        <button
          type="button"
          class="grid size-11 place-items-center border border-line-strong text-ink transition-colors hover:border-ink lg:hidden"
          aria-label="Open navigation"
          @click="emit('toggleSidebar')"
        >
          <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 9h16M4 15h16" /></svg>
        </button>

        <div>
          <p class="mono-label">ChanaaCar admin</p>
          <h1 class="text-[22px] font-medium leading-tight tracking-[-0.02em]">
            {{ currentLabel }}
          </h1>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="grid size-11 place-items-center border border-line-strong text-ink-2 transition-colors hover:border-ink hover:text-ink"
          :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          @click="toggleTheme"
        >
          <BaseIcon :name="isDark ? 'sun' : 'moon'" :size="18" />
        </button>

        <NuxtLink
          to="/"
          class="hidden min-h-11 items-center gap-3 border border-line-strong px-4 text-[14px] font-medium transition-colors hover:border-ink sm:inline-flex"
        >
          View site
          <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 17L17 7M9 7h8v8" /></svg>
        </NuxtLink>
      </div>
    </div>
  </header>
</template>
