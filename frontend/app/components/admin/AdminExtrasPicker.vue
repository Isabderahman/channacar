<script setup lang="ts">
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Extra } from '~/types/entities'
import { extraLineTotal, formatCurrency, isExtraPerDay } from '~/utils/formatters'

/**
 * Extras selector for the admin reservation forms. Like the public booking form
 * (`components/car/CarBookingForm.vue`), it shows each option's price and
 * billing rule (per day / one time) instead of a multi-select where nothing but
 * the label is visible.
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
      <span class="mono-label">
        Extras
      </span>
      <span
        v-if="selectedExtras.length"
        class="font-plex text-[11px] text-accent"
      >
        {{ selectedExtras.length }} selected
      </span>
    </div>

    <p v-if="!extras.length" class="mt-3 text-[14px] text-muted">
      No extra is configured yet. Add one from the Extras page to offer it here.
    </p>

    <div v-else class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4">
      <button
        v-for="extra in extras"
        :key="extra.id"
        type="button"
        class="flex min-h-[124px] flex-col items-start border p-3 text-start transition-colors duration-200"
        :class="
          isSelected(extra.id)
            ? 'border-ink bg-ink text-bg'
            : 'border-line-strong text-ink hover:border-ink'
        "
        :aria-pressed="isSelected(extra.id)"
        @click="toggle(extra.id)"
      >
        <span class="flex w-full items-start justify-between gap-2">
          <BaseIcon
            :name="resolveIcon(extra.icon)"
            :size="18"
            :class="isSelected(extra.id) ? '' : 'text-accent'"
          />
          <span class="inline-flex items-center gap-1 font-plex text-[10px] tracking-[0.06em]">
            <BaseIcon :name="isSelected(extra.id) ? 'check-circle' : 'plus'" :size="12" />
            {{ isSelected(extra.id) ? 'ADDED' : 'ADD' }}
          </span>
        </span>

        <span class="mt-3 line-clamp-2 w-full text-[14px] font-medium [overflow-wrap:anywhere]">
          {{ extra.name }}
        </span>

        <span class="mt-0.5 font-plex text-[11px] opacity-70">
          {{ unitLabel(extra) }}
        </span>

        <span
          v-if="!extra.is_active"
          class="mt-1.5 border border-current px-1.5 py-0.5 font-plex text-[10px] uppercase tracking-[0.06em] opacity-70"
        >
          Inactive
        </span>
      </button>
    </div>

    <button
      v-if="showInsurance"
      type="button"
      class="mt-2 flex w-full items-center gap-4 border p-4 text-start transition-colors duration-200"
      :class="applyInsurance ? 'border-ink' : 'border-line-strong hover:border-ink'"
      :aria-pressed="applyInsurance"
      @click="applyInsurance = !applyInsurance"
    >
      <BaseIcon name="shield" :size="20" class="shrink-0 text-accent" />

      <div class="min-w-0 flex-1">
        <p class="text-[15px] font-medium">Assurance complémentaire</p>
        <p class="mt-0.5 text-[13px] leading-5 text-muted">
          <template v-if="insurancePerDay > 0">
            Optional protection, billed {{ formatCurrency(insurancePerDay) }} per rental day.
          </template>
          <template v-else>
            This car has no insurance fee configured, so nothing is billed for it.
          </template>
        </p>
      </div>

      <span
        class="inline-flex min-h-9 shrink-0 items-center gap-1.5 border px-3 font-plex text-[11px] tracking-[0.06em]"
        :class="applyInsurance ? 'border-ink bg-ink text-bg' : 'border-line-strong text-ink'"
      >
        <BaseIcon :name="applyInsurance ? 'check-circle' : 'plus'" :size="13" />
        {{ applyInsurance ? 'ADDED' : 'ADD' }}
      </span>
    </button>

    <div
      v-if="showTotals && optionsTotal > 0"
      class="mt-4 space-y-2 border border-line px-4 py-3 text-[14px]"
    >
      <div
        v-for="extra in selectedExtras"
        :key="`line-${extra.id}`"
        class="flex items-center justify-between gap-3 text-ink-2"
      >
        <!-- w-0 flex-1: the name truncates instead of widening the whole form on phones. -->
        <span class="w-0 flex-1 truncate">
          {{ extra.name }}
          <span class="font-plex text-[11px] text-muted">
            ({{ isExtraPerDay(extra) ? `${billedDays} × ${formatCurrency(extra.price_per_day)}` : 'one-time' }})
          </span>
        </span>
        <span>{{ formatCurrency(extraLineTotal(extra, billedDays)) }}</span>
      </div>

      <div v-if="insuranceTotal > 0" class="flex items-center justify-between text-ink-2">
        <span>Assurance complémentaire</span>
        <span>{{ formatCurrency(insuranceTotal) }}</span>
      </div>

      <div
        class="flex items-center justify-between border-t border-line pt-2 font-medium text-ink"
      >
        <span>Options estimate · {{ billedDays }} day{{ billedDays > 1 ? 's' : '' }}</span>
        <span>{{ formatCurrency(optionsTotal) }}</span>
      </div>

      <p class="font-plex text-[11px] leading-5 text-muted">
        Estimate only — the backend applies the seasonal rate and returns the final total.
      </p>
    </div>
  </div>
</template>
