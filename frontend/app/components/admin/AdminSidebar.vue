<script setup lang="ts">
import { computed } from 'vue'
import { ADMIN_NAV_ITEMS } from '~/utils/constants'

interface Props {
  open: boolean
}

defineProps<Props>()

const emit = defineEmits<{
  close: []
}>()

const route = useRoute()
const { logout, user } = useAdminAuth()

const isCurrent = (to: string) => route.path === to || route.path.startsWith(`${to}/`)

const navItems = computed(() =>
  ADMIN_NAV_ITEMS.map((item, index) => ({
    ...item,
    number: String(index + 1).padStart(2, '0'),
  })),
)

const initials = computed(() =>
  user.value?.name
    ?.split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((chunk) => chunk[0]?.toUpperCase() ?? '')
    .join('') ?? 'AD',
)
</script>

<template>
  <div>
    <button
      v-if="open"
      type="button"
      class="fixed inset-0 z-40 bg-black/70 lg:hidden"
      aria-label="Close navigation"
      @click="emit('close')"
    />

    <aside
      class="fixed inset-y-0 left-0 z-50 flex w-[18rem] flex-col border-r border-line bg-bg transition-transform duration-300"
      :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="flex h-20 shrink-0 items-center justify-between gap-3 border-b border-line px-6">
        <NuxtLink to="/admin/dashboard" class="flex items-baseline gap-3">
          <span class="text-[20px] font-semibold tracking-[-0.02em]">Chanaa<span class="text-accent">Car</span></span>
          <span class="mono-label">Admin</span>
        </NuxtLink>

        <button
          type="button"
          class="grid size-10 place-items-center border border-line-strong text-ink-2 transition-colors hover:border-ink hover:text-ink lg:hidden"
          aria-label="Close navigation"
          @click="emit('close')"
        >
          <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 6l12 12M18 6L6 18" /></svg>
        </button>
      </div>

      <nav aria-label="Admin" class="flex-1 overflow-y-auto px-3 py-4">
        <NuxtLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          :aria-current="isCurrent(item.to) ? 'page' : undefined"
          class="flex min-h-11 items-center gap-4 border-l-2 px-3 text-[15px] transition-colors"
          :class="
            isCurrent(item.to)
              ? 'border-accent bg-surface-2 text-ink'
              : 'border-transparent text-ink-2 hover:bg-surface-2 hover:text-ink'
          "
          @click="emit('close')"
        >
          <span class="w-5 font-plex text-[11px]" :class="isCurrent(item.to) ? 'text-accent' : 'text-muted'">
            {{ item.number }}
          </span>
          {{ item.label }}
        </NuxtLink>
      </nav>

      <div class="shrink-0 border-t border-line px-6 py-4">
        <div class="flex items-center gap-3">
          <span class="grid size-10 shrink-0 place-items-center border border-line-strong font-plex text-[12px] text-ink">
            {{ initials }}
          </span>
          <div class="min-w-0">
            <p class="truncate text-[14px] font-medium">{{ user?.name ?? 'Admin user' }}</p>
            <p class="truncate font-plex text-[11px] text-muted">{{ user?.email ?? 'No email loaded' }}</p>
          </div>
        </div>
        <button
          type="button"
          class="mt-3 min-h-9 text-[13px] text-ink-2 underline underline-offset-4 transition-colors hover:text-accent"
          @click="logout"
        >
          Sign out
        </button>
      </div>
    </aside>
  </div>
</template>
