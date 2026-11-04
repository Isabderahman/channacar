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
    <div class="border-b border-line px-5 py-5 md:px-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="text-[20px] font-medium tracking-[-0.02em]">
            {{ title }}
          </h3>
          <p v-if="description" class="mt-1.5 max-w-[60ch] text-[14px] leading-6 text-ink-2">
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
              class="whitespace-nowrap border-b border-line px-5 py-3 text-left font-plex text-[11px] font-normal uppercase tracking-[0.08em] text-muted"
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
            class="transition-colors hover:bg-surface-2"
          >
            <td
              v-for="column in columns"
              :key="`${String(resolveCell(row, rowKey))}-${column.key}`"
              class="border-b border-line px-5 py-4 align-top text-[14px] text-ink-2"
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
      <p class="text-[17px] font-medium">
        {{ emptyTitle }}
      </p>
      <p class="mt-1.5 max-w-[48ch] text-[14px] leading-6 text-ink-2">
        {{ emptyMessage }}
      </p>
    </div>
  </AppPanel>
</template>
