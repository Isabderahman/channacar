<script setup lang="ts">
import type {
  PlanningCar,
  PlanningReservation,
  PlanningResponse,
  ReservationStatus,
} from '~/types/entities'
import { formatCurrency, formatTime } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

type ViewMode = 'month' | 'fortnight' | 'week'

interface PlanningBar {
  reservation: PlanningReservation
  startIndex: number
  span: number
  lane: number
  clippedStart: boolean
  clippedEnd: boolean
}

interface PlanningRow {
  car: PlanningCar
  bars: PlanningBar[]
  lanes: number
  bookedDays: number
}

const LEFT_COLUMN_WIDTH = 260
const LANE_HEIGHT = 34
const BAR_HEIGHT = 28
// Tall enough for the three lines of car identity in the sticky left column, so
// a single-lane row never leaves the bar floating against a much taller label.
const ROW_MIN_HEIGHT = 78

const CELL_WIDTH: Record<ViewMode, number> = {
  month: 46,
  fortnight: 74,
  week: 128,
}

const VIEW_OPTIONS: Array<{ label: string; value: ViewMode }> = [
  { label: 'Month', value: 'month' },
  { label: '2 weeks', value: 'fortnight' },
  { label: 'Week', value: 'week' },
]

// Solid fills rather than the translucent StatusBadge tones: bars sit on top of
// the weekend/today tints, so they need their own opaque colour to stay legible.
const STATUS_STYLES: Record<ReservationStatus, { bar: string; dot: string; label: string }> = {
  pending: { bar: 'bg-amber-400 text-amber-950', dot: 'bg-amber-400', label: 'Pending' },
  confirmed: { bar: 'bg-emerald-500 text-white', dot: 'bg-emerald-500', label: 'Confirmed' },
  ongoing: { bar: 'bg-sky-500 text-white', dot: 'bg-sky-500', label: 'Ongoing' },
  completed: { bar: 'bg-zinc-500 text-white', dot: 'bg-zinc-500', label: 'Completed' },
  cancelled: { bar: 'bg-rose-500/70 text-white line-through', dot: 'bg-rose-500/70', label: 'Cancelled' },
}

const CAR_STATUS_LABELS: Record<string, string> = {
  available: 'Available',
  rented: 'Rented',
  maintenance: 'Maintenance',
}

const toIso = (date: Date) =>
  `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`

const parseIso = (value: string) => {
  const [year, month, day] = value.split('-').map(Number)

  return new Date(year ?? 1970, (month ?? 1) - 1, day ?? 1)
}

const addDays = (value: string, amount: number) => {
  const date = parseIso(value)
  date.setDate(date.getDate() + amount)

  return toIso(date)
}

const addMonths = (value: string, amount: number) => {
  const date = parseIso(value)
  date.setDate(1)
  date.setMonth(date.getMonth() + amount)

  return toIso(date)
}

// Round instead of floor: DST shifts make a "day" 23h or 25h long.
const diffInDays = (from: string, to: string) =>
  Math.round((parseIso(to).getTime() - parseIso(from).getTime()) / 86_400_000)

const startOfMonth = (value: string) => `${value.slice(0, 7)}-01`

const todayIso = toIso(new Date())

const { adminApi } = useApi()

const viewMode = ref<ViewMode>('month')
const anchor = ref(startOfMonth(todayIso))
const includeCancelled = ref(false)
const includeInactive = ref(false)
const search = ref('')
const categoryFilter = ref('')

const pending = ref(true)
const errorMessage = ref('')
const cars = ref<PlanningCar[]>([])
const reservations = ref<PlanningReservation[]>([])

const cellWidth = computed(() => CELL_WIDTH[viewMode.value])

const rangeStart = computed(() =>
  viewMode.value === 'month' ? startOfMonth(anchor.value) : anchor.value,
)

const rangeEnd = computed(() => {
  if (viewMode.value === 'week') {
    return addDays(rangeStart.value, 6)
  }

  if (viewMode.value === 'fortnight') {
    return addDays(rangeStart.value, 13)
  }

  return addDays(addMonths(rangeStart.value, 1), -1)
})

const days = computed(() => {
  const total = diffInDays(rangeStart.value, rangeEnd.value) + 1

  return Array.from({ length: total }, (_, index) => {
    const iso = addDays(rangeStart.value, index)
    const date = parseIso(iso)
    const weekday = date.getDay()

    return {
      index,
      iso,
      dayNumber: date.getDate(),
      weekdayLabel: date.toLocaleDateString('en-US', { weekday: 'short' }).slice(0, viewMode.value === 'month' ? 2 : 3),
      monthLabel: date.toLocaleDateString('en-US', { month: 'short' }),
      isWeekend: weekday === 0 || weekday === 6,
      isToday: iso === todayIso,
      isFirstOfMonth: date.getDate() === 1,
    }
  })
})

const rangeLabel = computed(() => {
  const start = parseIso(rangeStart.value)
  const end = parseIso(rangeEnd.value)

  if (viewMode.value === 'month') {
    return start.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  }

  const sameMonth = start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()
  const year = end.getFullYear()

  if (sameMonth) {
    const month = start.toLocaleDateString('en-US', { month: 'short' })

    return `${month} ${start.getDate()} – ${end.getDate()}, ${year}`
  }

  const startLabel = start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  const endLabel = end.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })

  return `${startLabel} – ${endLabel}, ${year}`
})

const categoryOptions = computed(() => {
  const names = new Set<string>()

  cars.value.forEach((car) => {
    if (car.category) {
      names.add(car.category)
    }
  })

  return [...names].sort().map((name) => ({ label: name, value: name }))
})

const filteredCars = computed(() => {
  const term = search.value.trim().toLowerCase()

  return cars.value.filter((car) => {
    if (categoryFilter.value && car.category !== categoryFilter.value) {
      return false
    }

    if (!term) {
      return true
    }

    return `${car.brand} ${car.name} ${car.registration_number}`.toLowerCase().includes(term)
  })
})

const rows = computed<PlanningRow[]>(() => {
  const totalDays = days.value.length
  const lastIndex = totalDays - 1

  return filteredCars.value.map((car) => {
    const bars: PlanningBar[] = []
    // Lane packing keeps overlapping bars (e.g. a completed rental next to a
    // cancelled one) on separate lines instead of stacking on top of each other.
    const laneEnds: number[] = []
    const bookedDays = new Set<number>()

    reservations.value
      .filter((reservation) => reservation.car_id === car.id)
      .sort((a, b) => a.pickup_date.localeCompare(b.pickup_date))
      .forEach((reservation) => {
        const rawStart = diffInDays(rangeStart.value, reservation.pickup_date)
        const rawEnd = diffInDays(rangeStart.value, reservation.dropoff_date)

        if (rawEnd < 0 || rawStart > lastIndex) {
          return
        }

        const startIndex = Math.max(0, rawStart)
        const endIndex = Math.min(lastIndex, rawEnd)
        const span = endIndex - startIndex + 1

        let lane = laneEnds.findIndex((end) => end < startIndex)

        if (lane === -1) {
          lane = laneEnds.length
        }

        laneEnds[lane] = endIndex

        if (reservation.status !== 'cancelled') {
          for (let day = startIndex; day <= endIndex; day += 1) {
            bookedDays.add(day)
          }
        }

        bars.push({
          reservation,
          startIndex,
          span,
          lane,
          clippedStart: rawStart < 0,
          clippedEnd: rawEnd > lastIndex,
        })
      })

    return {
      car,
      bars,
      lanes: Math.max(1, laneEnds.length),
      bookedDays: bookedDays.size,
    }
  })
})

const totalWidth = computed(() => LEFT_COLUMN_WIDTH + days.value.length * cellWidth.value)

const gridBackground = computed(
  () =>
    `repeating-linear-gradient(to right, var(--surface-border) 0 1px, transparent 1px ${cellWidth.value}px)`,
)

const highlightDays = computed(() => days.value.filter((day) => day.isWeekend || day.isToday))

const summary = computed(() => {
  const totalDays = days.value.length
  const capacity = rows.value.length * totalDays
  const booked = rows.value.reduce((total, row) => total + row.bookedDays, 0)
  const visibleCarIds = new Set(filteredCars.value.map((car) => car.id))
  const today = days.value.find((day) => day.isToday)

  const freeToday = today
    ? rows.value.filter(
        (row) =>
          !row.bars.some(
            (bar) =>
              bar.reservation.status !== 'cancelled' &&
              today.index >= bar.startIndex &&
              today.index < bar.startIndex + bar.span,
          ),
      ).length
    : null

  return {
    cars: rows.value.length,
    reservations: reservations.value.filter((reservation) => visibleCarIds.has(reservation.car_id)).length,
    occupancy: capacity > 0 ? Math.round((booked / capacity) * 100) : 0,
    // Only meaningful while the visible range actually contains today.
    freeToday: freeToday === null ? '—' : String(freeToday),
  }
})

const rowHeight = (row: PlanningRow) => Math.max(ROW_MIN_HEIGHT, row.lanes * LANE_HEIGHT + 12)

const barStyle = (bar: PlanningBar, row: PlanningRow) => {
  const stackOffset = (rowHeight(row) - row.lanes * LANE_HEIGHT) / 2

  return {
    left: `${bar.startIndex * cellWidth.value + 3}px`,
    width: `${bar.span * cellWidth.value - 6}px`,
    top: `${stackOffset + bar.lane * LANE_HEIGHT + (LANE_HEIGHT - BAR_HEIGHT) / 2}px`,
    height: `${BAR_HEIGHT}px`,
  }
}

const barTooltip = (bar: PlanningBar) => {
  const reservation = bar.reservation

  return [
    `${reservation.reservation_number} — ${STATUS_STYLES[reservation.status].label}`,
    `Client: ${reservation.client_name ?? 'Unknown'}${reservation.client_phone ? ` (${reservation.client_phone})` : ''}`,
    `Pickup: ${reservation.pickup_date} at ${formatTime(reservation.pickup_time)}`,
    `Dropoff: ${reservation.dropoff_date} at ${formatTime(reservation.dropoff_time)}`,
    `Total: ${formatCurrency(reservation.total_price)}`,
  ].join('\n')
}

const barLabel = (bar: PlanningBar) => {
  const width = bar.span * cellWidth.value

  if (width < 70) {
    return ''
  }

  if (width < 150) {
    return bar.reservation.client_name?.split(' ')[0] ?? bar.reservation.reservation_number
  }

  return `${bar.reservation.client_name ?? 'Unknown client'} · ${bar.reservation.reservation_number}`
}

const loadPlanning = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PlanningResponse>('/planning', {
      query: {
        from: rangeStart.value,
        to: rangeEnd.value,
        include_cancelled: includeCancelled.value ? 1 : 0,
        include_inactive: includeInactive.value ? 1 : 0,
      },
    })

    cars.value = response.cars
    reservations.value = response.reservations
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Planning could not be loaded.'
  } finally {
    pending.value = false
  }
}

const shift = (direction: -1 | 1) => {
  if (viewMode.value === 'month') {
    anchor.value = addMonths(rangeStart.value, direction)

    return
  }

  anchor.value = addDays(rangeStart.value, direction * days.value.length)
}

const goToToday = () => {
  anchor.value = viewMode.value === 'month' ? startOfMonth(todayIso) : todayIso
}

const setViewMode = (mode: ViewMode) => {
  const previousStart = rangeStart.value
  const previousEnd = rangeEnd.value

  viewMode.value = mode

  if (mode === 'month') {
    anchor.value = startOfMonth(previousStart)

    return
  }

  // Zooming in from a range that contains today should land on today rather
  // than on the first of the month, which is usually already in the past.
  anchor.value = todayIso >= previousStart && todayIso <= previousEnd ? todayIso : previousStart
}

watch([rangeStart, rangeEnd, includeCancelled, includeInactive], () => {
  void loadPlanning()
})

onMounted(() => {
  void loadPlanning()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Fleet planning"
      description="One row per car, one column per day. Each coloured bar is a reservation, so gaps are the days the car is free to rent."
      eyebrow="Availability board"
    >
      <BaseButton to="/admin/reservations/new">
        <BaseIcon name="plus" :size="18" />
        <span>New reservation</span>
      </BaseButton>
    </AdminPageIntro>

    <AppPanel padding="lg">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <BaseButton variant="secondary" size="icon" aria-label="Previous period" @click="shift(-1)">
            <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
          </BaseButton>

          <p class="min-w-[13rem] text-center font-heading text-[1.15rem] text-[var(--text-strong)]">
            {{ rangeLabel }}
          </p>

          <BaseButton variant="secondary" size="icon" aria-label="Next period" @click="shift(1)">
            <BaseIcon name="arrow-right" :size="18" />
          </BaseButton>

          <BaseButton variant="ghost" size="sm" @click="goToToday">
            <BaseIcon name="calendar" :size="16" />
            <span class="ml-2">Today</span>
          </BaseButton>
        </div>

        <div class="flex items-center gap-1 rounded-[16px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-1">
          <button
            v-for="option in VIEW_OPTIONS"
            :key="option.value"
            type="button"
            class="rounded-[12px] px-4 py-2 text-[0.85rem] font-semibold transition duration-300"
            :class="
              viewMode === option.value
                ? 'bg-[var(--primary)] text-[var(--white)]'
                : 'text-[var(--text-muted)] hover:text-[var(--text-strong)]'
            "
            @click="setViewMode(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <FormField v-model="search" name="planning_search" label="Search car" placeholder="Brand, model, plate" />
        <FormSelect
          v-model="categoryFilter"
          name="planning_category"
          label="Category"
          :options="categoryOptions"
        />
        <FormCheckbox
          v-model="includeCancelled"
          name="planning_cancelled"
          label="Show cancelled"
          description="Include cancelled reservations on the board."
        />
        <FormCheckbox
          v-model="includeInactive"
          name="planning_inactive"
          label="Show inactive cars"
          description="Include cars hidden from the public site."
        />
      </div>

      <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-[var(--surface-border)] pt-5">
        <span class="text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]">
          Legend
        </span>

        <span
          v-for="(style, status) in STATUS_STYLES"
          :key="status"
          class="flex items-center gap-2 text-[0.88rem] text-[var(--text-muted)]"
        >
          <span class="size-3 rounded-[4px]" :class="style.dot" />
          {{ style.label }}
        </span>

        <span class="flex items-center gap-2 text-[0.88rem] text-[var(--text-muted)]">
          <span class="size-3 rounded-[4px] bg-[color-mix(in_srgb,var(--primary)_30%,transparent)] ring-1 ring-[var(--primary)]" />
          Today
        </span>
        <span class="flex items-center gap-2 text-[0.88rem] text-[var(--text-muted)]">
          <span class="size-3 rounded-[4px] bg-[color-mix(in_srgb,var(--secondary)_18%,transparent)]" />
          Weekend
        </span>
      </div>
    </AppPanel>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Cars on board" :value="String(summary.cars)" icon="car" />
      <StatCard label="Reservations in range" :value="String(summary.reservations)" icon="calendar" />
      <StatCard label="Occupancy" :value="`${summary.occupancy}%`" icon="dashboard" />
      <StatCard label="Free today" :value="summary.freeToday" icon="check-circle" />
    </div>

    <StateNotice
      v-if="errorMessage"
      class="mt-6"
      title="Planning unavailable"
      :message="errorMessage"
      tone="error"
    />

    <AppPanel v-else class="mt-6" padding="none">
      <div class="flex items-center justify-between gap-4 border-b border-[var(--surface-border)] px-5 py-4 md:px-6">
        <div>
          <h2 class="font-heading text-[1.15rem] text-[var(--text-strong)]">Availability board</h2>
          <p class="mt-1 text-[0.88rem] text-[var(--text-subtle)]">
            Click any bar to open the reservation. Hover to see client, times, and total.
          </p>
        </div>
        <span v-if="pending" class="text-[0.85rem] text-[var(--text-subtle)]">Loading…</span>
      </div>

      <div class="max-h-[72vh] overflow-auto rounded-b-[24px]">
        <div class="relative" :style="{ minWidth: `${totalWidth}px` }">
          <div class="sticky top-0 z-30 flex border-b border-[var(--surface-border)] bg-[var(--surface-2)]">
            <div
              class="sticky left-0 z-40 flex shrink-0 items-center bg-[var(--surface-2)] px-4 py-3 text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]"
              :style="{ width: `${LEFT_COLUMN_WIDTH}px` }"
            >
              Vehicle
            </div>

            <div
              v-for="day in days"
              :key="day.iso"
              class="shrink-0 border-l border-[var(--surface-border)] py-2 text-center"
              :class="[
                day.isToday ? 'bg-[color-mix(in_srgb,var(--primary)_20%,transparent)]' : day.isWeekend ? 'bg-[color-mix(in_srgb,var(--secondary)_7%,transparent)]' : '',
              ]"
              :style="{ width: `${cellWidth}px` }"
            >
              <p
                class="text-[0.68rem] uppercase tracking-[0.12em]"
                :class="day.isToday ? 'text-[var(--primary)]' : 'text-[var(--text-subtle)]'"
              >
                {{ day.weekdayLabel }}
              </p>
              <p
                class="text-[0.95rem] font-semibold"
                :class="day.isToday ? 'text-[var(--primary)]' : 'text-[var(--text-strong)]'"
              >
                {{ day.dayNumber }}
              </p>
              <p v-if="day.isFirstOfMonth" class="text-[0.62rem] uppercase text-[var(--text-subtle)]">
                {{ day.monthLabel }}
              </p>
            </div>
          </div>

          <div class="relative">
            <div
              class="pointer-events-none absolute inset-y-0 z-0"
              :style="{ left: `${LEFT_COLUMN_WIDTH}px`, width: `${days.length * cellWidth}px` }"
            >
              <div
                v-for="day in highlightDays"
                :key="`tint-${day.iso}`"
                class="absolute inset-y-0"
                :class="day.isToday ? 'bg-[color-mix(in_srgb,var(--primary)_14%,transparent)] ring-1 ring-inset ring-[color-mix(in_srgb,var(--primary)_45%,transparent)]' : 'bg-[color-mix(in_srgb,var(--secondary)_6%,transparent)]'"
                :style="{ left: `${day.index * cellWidth}px`, width: `${cellWidth}px` }"
              />
            </div>

            <div
              v-for="row in rows"
              :key="row.car.id"
              class="relative z-10 flex border-b border-[var(--surface-border)] last:border-b-0"
            >
              <div
                class="sticky left-0 z-20 flex shrink-0 flex-col justify-center gap-1 border-r border-[var(--surface-border)] bg-[var(--surface-1)] px-4 py-2"
                :style="{ width: `${LEFT_COLUMN_WIDTH}px`, minHeight: `${rowHeight(row)}px` }"
              >
                <NuxtLink
                  :to="`/admin/cars/${row.car.id}`"
                  class="font-heading text-[0.98rem] leading-tight text-[var(--text-strong)] hover:text-[var(--primary)]"
                >
                  {{ row.car.brand }} {{ row.car.name }}
                </NuxtLink>
                <p class="flex flex-wrap items-center gap-2 text-[0.78rem] text-[var(--text-subtle)]">
                  <span class="rounded-[6px] bg-[var(--surface-3)] px-2 py-0.5 font-mono">
                    {{ row.car.registration_number }}
                  </span>
                  <span>{{ CAR_STATUS_LABELS[row.car.status] ?? row.car.status }}</span>
                </p>
                <p class="text-[0.75rem] text-[var(--text-subtle)]">
                  {{ row.bookedDays }} / {{ days.length }} days booked
                </p>
              </div>

              <div
                class="relative shrink-0"
                :style="{
                  width: `${days.length * cellWidth}px`,
                  height: `${rowHeight(row)}px`,
                  backgroundImage: gridBackground,
                }"
              >
                <button
                  v-for="bar in row.bars"
                  :key="bar.reservation.id"
                  type="button"
                  class="absolute flex items-center overflow-hidden px-2 text-[0.8rem] font-semibold shadow-[0_6px_16px_rgb(0_0_0/0.25)] transition duration-200 hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--white)]"
                  :class="[
                    STATUS_STYLES[bar.reservation.status].bar,
                    bar.clippedStart ? 'rounded-l-none' : 'rounded-l-[10px]',
                    bar.clippedEnd ? 'rounded-r-none' : 'rounded-r-[10px]',
                  ]"
                  :style="barStyle(bar, row)"
                  :title="barTooltip(bar)"
                  @click="navigateTo(`/admin/reservations/${bar.reservation.id}`)"
                >
                  <span class="truncate">{{ barLabel(bar) }}</span>
                </button>
              </div>
            </div>

            <p
              v-if="!rows.length && !pending"
              class="px-6 py-10 text-center text-[0.95rem] text-[var(--text-subtle)]"
            >
              No car matches the current filters.
            </p>
          </div>
        </div>
      </div>
    </AppPanel>
  </div>
</template>
