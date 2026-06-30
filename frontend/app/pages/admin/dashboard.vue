<script setup lang="ts">
import type { DashboardStats, PaginatedResponse, Reservation } from '~/types/entities'
import { formatCurrency, formatDateTimeLine } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()

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
    errorMessage.value =
      error instanceof Error ? error.message : 'The dashboard could not be loaded.'
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

      <div class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
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
            <template #cell-status="{ row }">
              <StatusBadge :value="row.status" />
            </template>
          </TableCard>

          <AppPanel padding="lg">
            <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">Operations snapshot</h3>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
              <div class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
                <p class="text-[0.76rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]">Today</p>
                <p class="mt-3 text-[0.95rem] text-[var(--text-muted)]">
                  {{ stats.today.pickups }} pickups and {{ stats.today.dropoffs }} dropoffs are scheduled.
                </p>
              </div>
              <div class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
                <p class="text-[0.76rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]">Fleet balance</p>
                <p class="mt-3 text-[0.95rem] text-[var(--text-muted)]">
                  {{ stats.cars.available }} available, {{ stats.cars.rented }} rented, {{ stats.cars.maintenance }} in maintenance.
                </p>
              </div>
            </div>
          </AppPanel>
        </div>

        <AppPanel padding="lg">
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">Quick actions</h3>
          <p class="mt-2 text-[0.92rem] leading-6 text-[var(--text-muted)]">
            These routes cover the main operational loops you listed for the admin area.
          </p>

          <div class="mt-6 space-y-3">
            <NuxtLink
              to="/admin/cars"
              class="flex items-center justify-between rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-4 text-[var(--text-muted)] transition duration-300 hover:border-[var(--primary-border)] hover:text-[var(--text-strong)]"
            >
              <span class="flex items-center gap-3">
                <BaseIcon name="car" :size="18" class="text-[var(--primary)]" />
                <span>Create or edit cars</span>
              </span>
              <BaseIcon name="arrow-right" :size="18" />
            </NuxtLink>

            <NuxtLink
              to="/admin/reservations"
              class="flex items-center justify-between rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-4 text-[var(--text-muted)] transition duration-300 hover:border-[var(--primary-border)] hover:text-[var(--text-strong)]"
            >
              <span class="flex items-center gap-3">
                <BaseIcon name="calendar" :size="18" class="text-[var(--primary)]" />
                <span>Review reservations</span>
              </span>
              <BaseIcon name="arrow-right" :size="18" />
            </NuxtLink>

            <NuxtLink
              to="/admin/testimonials"
              class="flex items-center justify-between rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-4 text-[var(--text-muted)] transition duration-300 hover:border-[var(--primary-border)] hover:text-[var(--text-strong)]"
            >
              <span class="flex items-center gap-3">
                <BaseIcon name="star" :size="18" class="text-[var(--primary)]" />
                <span>Moderate testimonials</span>
              </span>
              <BaseIcon name="arrow-right" :size="18" />
            </NuxtLink>
          </div>

          <div class="mt-6 rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
            <p class="text-[0.76rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]">
              Deposits collected
            </p>
            <p class="mt-3 font-heading text-[1.8rem] text-[var(--text-strong)]">
              {{ formatCurrency(stats.revenue.deposits_collected) }}
            </p>
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
