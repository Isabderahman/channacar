<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Extra } from '~/types/entities'
import { extraLineTotal, formatCurrency, isExtraPerDay } from '~/utils/formatters'

/**
 * Extras selector for the admin reservation forms, mirroring the tile grid the
 * public booking page shows clients (`components/public/ReservationForm.vue`):
 * the operator sees the same icons, prices and billing rules the customer sees,
 * instead of a multi-select where nothing but the label is visible.
 *
 * The running totals are an on-screen estimate only — the backend recomputes the
 * authoritative price from the car, season and extras snapshot on save.
 */
const props = withDefaults(
  defineProps<{
    extras: Extra[]
    /** Rental length used for the per-day estimates; 0 hides the totals. */
    rentalDays?: number
    /** Car's optional insurance fee; 0 hides the card unless it is already on. */
    insurancePerDay?: number
  }>(),
  {
    rentalDays: 0,
    insurancePerDay: 0,
  },
)

const selectedIds = defineModel<Array<number | string>>({ default: () => [] })
const applyInsurance = defineModel<boolean>('insurance', { default: false })

// Extra.icon is a free-form string from the API; BaseIcon only renders known
// names and ignores the rest, so resolving to `any` keeps types happy.
const resolveIcon = (value?: string | null): any => value || 'plus'

const isSelected = (id: number) => selectedIds.value.some((value) => Number(value) === id)

const toggle = (id: number) => {
  selectedIds.value = isSelected(id)
    ? selectedIds.value.filter((value) => Number(value) !== id)
    : [...selectedIds.value, id]
}

const selectedExtras = computed(() => props.extras.filter((extra) => isSelected(extra.id)))

const billedDays = computed(() => Math.max(1, props.rentalDays))
const showTotals = computed(() => props.rentalDays > 0)

const extrasTotal = computed(() =>
  selectedExtras.value.reduce((sum, extra) => sum + extraLineTotal(extra, billedDays.value), 0),
)

const insuranceTotal = computed(() =>
  applyInsurance.value ? props.insurancePerDay * billedDays.value : 0,
)

const optionsTotal = computed(() => extrasTotal.value + insuranceTotal.value)

// Also shown when the car carries no insurance fee but the reservation already
// has the flag on — otherwise the operator could never switch it back off.
const showInsurance = computed(() => props.insurancePerDay > 0 || applyInsurance.value)

const unitLabel = (extra: Extra) =>
  `${formatCurrency(extra.price_per_day)}${isExtraPerDay(extra) ? ' / day' : ' once'}`
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-2">
      <span class="text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]">
        Extras
      </span>
      <span
        v-if="selectedExtras.length"
        class="rounded-full bg-[var(--primary-soft)] px-3 py-1 text-[0.72rem] font-semibold text-[var(--primary)]"
      >
        {{ selectedExtras.length }} selected
      </span>
    </div>

    <p v-if="!extras.length" class="mt-3 text-[0.88rem] text-[var(--text-subtle)]">
      No extra is configured yet. Add one from the Extras page to offer it here.
    </p>

    <div v-else class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
      <button
        v-for="extra in extras"
        :key="extra.id"
        type="button"
        class="group flex flex-col items-center rounded-[18px] border-2 p-3 text-center transition duration-300 ease-out"
        :class="
          isSelected(extra.id)
            ? 'border-[var(--primary)] bg-[var(--primary-soft)]'
            : 'border-[var(--surface-border)] bg-[var(--surface-2)] hover:border-[var(--primary-border)]'
        "
        :aria-pressed="isSelected(extra.id)"
        @click="toggle(extra.id)"
      >
        <span
          class="grid size-11 place-items-center rounded-full bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)]"
        >
          <BaseIcon :name="resolveIcon(extra.icon)" :size="20" />
        </span>

        <span class="mt-2 line-clamp-2 text-[0.82rem] font-medium text-[var(--text-strong)]">
          {{ extra.name }}
        </span>

        <span class="mt-0.5 text-[0.82rem] font-bold text-[var(--text-strong)]">
          {{ unitLabel(extra) }}
        </span>

        <span
          v-if="!extra.is_active"
          class="mt-1 rounded-full bg-[var(--surface-3)] px-2 py-0.5 text-[0.66rem] uppercase tracking-[0.1em] text-[var(--text-subtle)]"
        >
          Inactive
        </span>

        <span
          class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[0.7rem] font-semibold"
          :class="
            isSelected(extra.id)
              ? 'bg-[var(--primary)] text-[var(--white)]'
              : 'bg-[var(--surface-3)] text-[var(--text-muted)]'
          "
        >
          <BaseIcon :name="isSelected(extra.id) ? 'check-circle' : 'plus'" :size="13" />
          {{ isSelected(extra.id) ? 'ADDED' : 'ADD' }}
        </span>
      </button>
    </div>

    <button
      v-if="showInsurance"
      type="button"
      class="mt-3 flex w-full items-center gap-4 rounded-[18px] border-2 p-4 text-left transition duration-300 ease-out"
      :class="
        applyInsurance
          ? 'border-[var(--primary)] bg-[var(--primary-soft)]'
          : 'border-[var(--surface-border)] bg-[var(--surface-2)] hover:border-[var(--primary-border)]'
      "
      :aria-pressed="applyInsurance"
      @click="applyInsurance = !applyInsurance"
    >
      <span
        class="grid size-12 shrink-0 place-items-center rounded-full bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)]"
      >
        <BaseIcon name="shield" :size="22" />
      </span>

      <div class="min-w-0 flex-1">
        <p class="font-heading text-[1rem] text-[var(--text-strong)]">Assurance complémentaire</p>
        <p class="mt-0.5 text-[0.84rem] leading-5 text-[var(--text-muted)]">
          <template v-if="insurancePerDay > 0">
            Optional protection, billed {{ formatCurrency(insurancePerDay) }} per rental day.
          </template>
          <template v-else>
            This car has no insurance fee configured, so nothing is billed for it.
          </template>
        </p>
      </div>

      <span
        class="inline-flex shrink-0 items-center gap-1 rounded-full px-3 py-1.5 text-[0.74rem] font-semibold"
        :class="
          applyInsurance
            ? 'bg-[var(--primary)] text-[var(--white)]'
            : 'bg-[var(--surface-3)] text-[var(--text-muted)]'
        "
      >
        <BaseIcon :name="applyInsurance ? 'check-circle' : 'plus'" :size="14" />
        {{ applyInsurance ? 'ADDED' : 'ADD' }}
      </span>
    </button>

    <div
      v-if="showTotals && optionsTotal > 0"
      class="mt-4 space-y-2 rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3 text-[0.88rem]"
    >
      <div
        v-for="extra in selectedExtras"
        :key="`line-${extra.id}`"
        class="flex items-center justify-between gap-3 text-[var(--text-muted)]"
      >
        <span class="truncate">
          {{ extra.name }}
          <span class="text-[0.75rem] text-[var(--text-subtle)]">
            ({{ isExtraPerDay(extra) ? `${billedDays} × ${formatCurrency(extra.price_per_day)}` : 'one-time' }})
          </span>
        </span>
        <span>{{ formatCurrency(extraLineTotal(extra, billedDays)) }}</span>
      </div>

      <div v-if="insuranceTotal > 0" class="flex items-center justify-between text-[var(--text-muted)]">
        <span>Assurance complémentaire</span>
        <span>{{ formatCurrency(insuranceTotal) }}</span>
      </div>

      <div
        class="flex items-center justify-between border-t border-[var(--surface-border)] pt-2 font-semibold text-[var(--text-strong)]"
      >
        <span>Options estimate · {{ billedDays }} day{{ billedDays > 1 ? 's' : '' }}</span>
        <span>{{ formatCurrency(optionsTotal) }}</span>
      </div>

      <p class="text-[0.75rem] leading-5 text-[var(--text-subtle)]">
        Estimate only — the backend applies the seasonal rate and returns the final total.
      </p>
    </div>
  </div>
</template>
