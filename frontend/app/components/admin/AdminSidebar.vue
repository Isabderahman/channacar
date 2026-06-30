<script setup lang="ts">
import { computed } from 'vue'
import BrandLogo from '~/components/common/BrandLogo.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
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
      class="fixed inset-0 z-40 bg-black/60 lg:hidden"
      @click="emit('close')"
    />

    <aside
      class="fixed inset-y-0 left-0 z-50 flex w-[18rem] flex-col border-r border-[var(--surface-border)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--surface-1)_96%,transparent),color-mix(in_srgb,var(--surface-2)_96%,transparent))] px-5 py-5 shadow-[var(--shadow-1)] transition duration-300"
      :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="flex items-center justify-between gap-3">
        <BrandLogo brand-name="ChannaCar" tagline="admin control" to="/admin/dashboard" />

        <button
          type="button"
          class="grid size-10 place-items-center rounded-full border border-[var(--surface-border)] text-[var(--text-muted)] lg:hidden"
          @click="emit('close')"
        >
          <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
        </button>
      </div>

      <div class="mt-8 flex items-center gap-3 rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
        <div class="grid size-12 place-items-center rounded-[16px] bg-[var(--primary-soft)] font-heading text-[0.95rem] font-semibold text-[var(--primary)]">
          {{ initials }}
        </div>
        <div>
          <p class="font-heading text-[1rem] text-[var(--text-strong)]">
            {{ user?.name ?? 'Admin user' }}
          </p>
          <p class="text-[0.82rem] text-[var(--text-subtle)]">
            {{ user?.email ?? 'No email loaded' }}
          </p>
        </div>
      </div>

      <nav class="mt-6 space-y-2">
        <NuxtLink
          v-for="item in ADMIN_NAV_ITEMS"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 rounded-[18px] px-4 py-3 text-[0.95rem] transition duration-300"
          :class="
            isCurrent(item.to)
              ? 'bg-[var(--primary)] text-white shadow-[var(--shadow-2)]'
              : 'text-[var(--text-muted)] hover:bg-[var(--surface-2)] hover:text-[var(--text-strong)]'
          "
        >
          <BaseIcon :name="item.icon" :size="18" />
          <span>{{ item.label }}</span>
        </NuxtLink>
      </nav>


    </aside>
  </div>
</template>
