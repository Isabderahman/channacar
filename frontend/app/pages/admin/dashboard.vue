<script setup lang="ts">
import type { DashboardStats, PaginatedResponse, Reservation } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { formatCurrency, formatDateTimeLine } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()
const toast = useToast()

const pending = ref(true)
const errorMessage = ref('')
const stats = ref<DashboardStats | null>(null)
const reservations = ref<Reservation[]>([])

const upcomingRows = computed(() =>
  reservations.value.map((reservation) => ({
    car: reservation.car ? `${reservation.car.brand} ${reservation.car.name}` : 'Unknown car',
    client: reservation.client?.full_name ?? 'Unknown client',
    id: reservation.id,
    pickup: formatDateTimeLine(reservation.pickup_date, reservation.pickup_time),
    reservation_number: reservation.reservation_number,
    status: reservation.status,
  })),
)

const loadDashboard = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const [statsResponse, reservationsResponse] = await Promise.all([
      adminApi<DashboardStats>('/dashboard/stats'),
      adminApi<PaginatedResponse<Reservation>>('/reservations', {
        query: {
          per_page: 5,
        },
      }),
    ])

    stats.value = statsResponse
    reservations.value = reservationsResponse.data
  } catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, {
      fallback: 'The dashboard could not be loaded.',
    })
    toast.error(errorMessage.value, 'Loading failed')
  } finally {
    pending.value = false
  }
}

onMounted(() => {
  void loadDashboard()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Dashboard"
      description="A protected command surface for the fleet, bookings, clients, and reputation signals."
      eyebrow="Admin overview"
    >
      <BaseButton type="button" variant="secondary" @click="loadDashboard">
        <BaseIcon name="refresh" :size="18" />
        <span>Refresh</span>
      </BaseButton>
    </AdminPageIntro>

    <StateNotice
      v-if="errorMessage"
      title="Dashboard unavailable"
      :message="errorMessage"
      tone="error"
    />

    <template v-else-if="stats">
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Cars online" :value="stats.cars.active" icon="car" description="Active vehicles currently listed in the system." />
        <StatCard label="Pending bookings" :value="stats.reservations.pending" icon="calendar" description="Reservations that still need confirmation or review." />
        <StatCard label="Clients" :value="stats.clients" icon="people" description="Unique client records stored in the directory." />
        <StatCard label="Paid revenue" :value="formatCurrency(stats.revenue.paid_total)" icon="payment" description="Fully paid reservation revenue tracked by the backend." />
      </div>

      <div class="mt-6">
        <TableCard
          title="Upcoming reservations"
          description="Use this list to jump directly into active reservations and keep pickup timing visible."
          :columns="[
            { key: 'reservation_number', label: 'Reference' },
            { key: 'client', label: 'Client' },
            { key: 'car', label: 'Car' },
            { key: 'pickup', label: 'Pickup' },
            { key: 'status', label: 'Status' },
          ]"
          :rows="upcomingRows"
          @row-click="(row) => navigateTo(`/admin/reservations/${row.id}`)"
        >
          <template #cell-reservation_number="{ row }">
            <span class="cursor-pointer whitespace-nowrap font-plex text-[13px] text-ink">{{ row.reservation_number }}</span>
          </template>
          <template #cell-client="{ row }">
            <span class="cursor-pointer whitespace-nowrap text-ink">{{ row.client }}</span>
          </template>
          <template #cell-car="{ row }">
            <span class="cursor-pointer whitespace-nowrap">{{ row.car }}</span>
          </template>
          <template #cell-pickup="{ row }">
            <span class="cursor-pointer whitespace-nowrap">{{ row.pickup }}</span>
          </template>
          <template #cell-status="{ row }">
            <StatusBadge :value="row.status" />
          </template>
        </TableCard>
      </div>

      <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <AppPanel padding="lg">
          <h3 class="text-[20px] font-medium tracking-[-0.02em]">Operations snapshot</h3>
          <dl class="mt-5 border-t border-line">
            <div class="grid gap-1 border-b border-line py-4 sm:grid-cols-[10rem_1fr] sm:gap-6">
              <dt class="mono-label pt-0.5">Today</dt>
              <dd class="text-[15px] text-ink-2">
                {{ stats.today.pickups }} pickups and {{ stats.today.dropoffs }} dropoffs are scheduled.
              </dd>
            </div>
            <div class="grid gap-1 border-b border-line py-4 sm:grid-cols-[10rem_1fr] sm:gap-6">
              <dt class="mono-label pt-0.5">Fleet balance</dt>
              <dd class="text-[15px] text-ink-2">
                {{ stats.cars.available }} available, {{ stats.cars.rented }} rented, {{ stats.cars.maintenance }} in maintenance.
              </dd>
            </div>
            <div class="grid gap-1 border-b border-line py-4 sm:grid-cols-[10rem_1fr] sm:gap-6">
              <dt class="mono-label pt-2">Deposits collected</dt>
              <dd class="text-[32px] font-medium leading-none tracking-[-0.03em]">
                {{ formatCurrency(stats.revenue.deposits_collected) }}
              </dd>
            </div>
          </dl>
        </AppPanel>

        <AppPanel padding="lg">
          <h3 class="text-[20px] font-medium tracking-[-0.02em]">Quick actions</h3>
          <p class="mt-1.5 text-[14px] leading-6 text-ink-2">
            These routes cover the main operational loops you listed for the admin area.
          </p>

          <div class="mt-5 border-t border-line">
            <NuxtLink
              to="/admin/cars"
              class="group flex min-h-14 items-center justify-between gap-4 border-b border-line text-[15px] text-ink-2 transition-colors hover:text-ink"
            >
              <span class="flex items-center gap-3">
                <BaseIcon name="car" :size="18" class="text-accent" />
                <span>Create or edit cars</span>
              </span>
              <BaseIcon name="arrow-right" :size="18" class="text-muted transition-colors group-hover:text-accent" />
            </NuxtLink>

            <NuxtLink
              to="/admin/reservations"
              class="group flex min-h-14 items-center justify-between gap-4 border-b border-line text-[15px] text-ink-2 transition-colors hover:text-ink"
            >
              <span class="flex items-center gap-3">
                <BaseIcon name="calendar" :size="18" class="text-accent" />
                <span>Review reservations</span>
              </span>
              <BaseIcon name="arrow-right" :size="18" class="text-muted transition-colors group-hover:text-accent" />
            </NuxtLink>

            <NuxtLink
              to="/admin/testimonials"
              class="group flex min-h-14 items-center justify-between gap-4 border-b border-line text-[15px] text-ink-2 transition-colors hover:text-ink"
            >
              <span class="flex items-center gap-3">
                <BaseIcon name="star" :size="18" class="text-accent" />
                <span>Moderate testimonials</span>
              </span>
              <BaseIcon name="arrow-right" :size="18" class="text-muted transition-colors group-hover:text-accent" />
            </NuxtLink>
          </div>
        </AppPanel>
      </div>
    </template>

    <StateNotice
      v-else-if="pending"
      title="Loading dashboard"
      message="Fetching admin KPIs and the latest reservation activity."
    />
  </div>
</template>
