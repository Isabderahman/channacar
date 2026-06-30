<script setup lang="ts" generic="T extends Record<string, any>">
import { computed } from 'vue'
import AppPanel from '~/components/data/AppPanel.vue'

interface Column {
  align?: 'end' | 'start'
  key: string
  label: string
}

interface Props {
  columns: Column[]
  description?: string
  emptyMessage?: string
  emptyTitle?: string
  rows: T[]
  rowKey?: string
  title: string
}

const props = withDefaults(defineProps<Props>(), {
  description: undefined,
  emptyMessage: 'No records match the current state.',
  emptyTitle: 'Nothing to show',
  rowKey: 'id',
})

const emit = defineEmits<{
  rowClick: [row: T]
}>()

const hasRows = computed(() => props.rows.length > 0)

const resolveCell = (row: T, key: string) =>
  key.split('.').reduce<unknown>((result, segment) => {
    if (result && typeof result === 'object' && segment in result) {
      return (result as Record<string, unknown>)[segment]
    }

    return undefined
  }, row)
</script>

<template>
  <AppPanel padding="none">
    <div class="border-b border-[var(--surface-border)] px-5 py-5 md:px-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="font-heading text-[1.15rem] font-semibold text-[var(--text-strong)]">
            {{ title }}
          </h3>
          <p v-if="description" class="mt-2 max-w-[60ch] text-[0.92rem] leading-6 text-[var(--text-muted)]">
            {{ description }}
          </p>
        </div>

        <slot name="toolbar" />
      </div>
    </div>

    <div v-if="hasRows" class="overflow-x-auto">
      <table class="min-w-full border-separate border-spacing-0">
        <thead>
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="border-b border-[var(--surface-border)] px-5 py-3 text-left text-[0.75rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]"
              :class="column.align === 'end' ? 'text-right' : 'text-left'"
            >
              {{ column.label }}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="row in rows"
            :key="String(resolveCell(row, rowKey) ?? Math.random())"
            class="transition duration-300 hover:bg-white/4"
          >
            <td
              v-for="column in columns"
              :key="`${String(resolveCell(row, rowKey))}-${column.key}`"
              class="border-b border-[var(--surface-border)] px-5 py-4 align-top text-[0.95rem] text-[var(--text-muted)]"
              :class="column.align === 'end' ? 'text-right' : 'text-left'"
              @click="emit('rowClick', row)"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="resolveCell(row, column.key)"
              >
                {{ resolveCell(row, column.key) ?? '—' }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="px-5 py-8 md:px-6">
      <p class="font-heading text-[1.05rem] font-semibold text-[var(--text-strong)]">
        {{ emptyTitle }}
      </p>
      <p class="mt-2 max-w-[48ch] text-[0.92rem] leading-6 text-[var(--text-muted)]">
        {{ emptyMessage }}
      </p>
    </div>
  </AppPanel>
</template>
