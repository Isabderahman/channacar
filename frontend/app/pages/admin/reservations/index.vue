<script setup lang="ts">
import type { PaginatedResponse, Reservation } from '~/types/entities'
import {
  PAYMENT_STATUS_OPTIONS,
  RESERVATION_SOURCE_OPTIONS,
  RESERVATION_STATUS_OPTIONS,
} from '~/utils/constants'
import { formatDateTimeLine } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()

const reservations = ref<Reservation[]>([])
const pending = ref(true)
const errorMessage = ref('')
const currentPage = ref(1)
const lastPage = ref(1)

const filters = reactive({
  payment_status: '',
  search: '',
  source: '',
  status: '',
})

const rows = computed(() =>
  reservations.value.map((reservation) => ({
    car: reservation.car ? `${reservation.car.brand} ${reservation.car.name}` : 'Unknown',
    client: reservation.client?.full_name ?? 'Unknown client',
    id: reservation.id,
    payment_status: reservation.payment_status,
    pickup: formatDateTimeLine(reservation.pickup_date, reservation.pickup_time),
    reservation_number: reservation.reservation_number,
    status: reservation.status,
  })),
)

const loadReservations = async (page = 1) => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PaginatedResponse<Reservation>>('/reservations', {
      query: {
        page,
        per_page: 15,
        ...Object.fromEntries(
          Object.entries(filters).filter(([, value]) => String(value).trim().length > 0),
        ),
      },
    })

    reservations.value = response.data
    currentPage.value = response.current_page
    lastPage.value = response.last_page
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Reservations could not be loaded.'
  } finally {
    pending.value = false
  }
}

const resetFilters = async () => {
  filters.payment_status = ''
  filters.search = ''
  filters.source = ''
  filters.status = ''
  await loadReservations(1)
}

onMounted(() => {
  void loadReservations()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Reservations"
      description="Filter and review reservations before moving into the dedicated detail editor."
      eyebrow="Booking operations"
    >
      <BaseButton to="/admin/reservations/new">
        <BaseIcon name="plus" :size="18" />
        <span>New reservation</span>
      </BaseButton>
    </AdminPageIntro>

    <AppPanel padding="lg">
      <div class="mb-5 flex items-center gap-3">
        <BaseIcon name="filter" :size="18" class="text-[var(--primary)]" />
        <h2 class="font-heading text-[1.15rem] text-[var(--text-strong)]">Reservation filters</h2>
      </div>

      <form class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="loadReservations(1)">
        <FormField v-model="filters.search" name="search" label="Search" placeholder="Reference, client, phone" />
        <FormSelect v-model="filters.status" name="status" label="Status" :options="[...RESERVATION_STATUS_OPTIONS]" />
        <FormSelect
          v-model="filters.payment_status"
          name="payment_status"
          label="Payment status"
          :options="[...PAYMENT_STATUS_OPTIONS]"
        />
        <FormSelect v-model="filters.source" name="source" label="Source" :options="[...RESERVATION_SOURCE_OPTIONS]" />

        <div class="flex items-end gap-3 xl:col-span-4">
          <BaseButton type="submit">
            <BaseIcon name="search" :size="18" />
            <span>Apply filters</span>
          </BaseButton>
          <BaseButton type="button" variant="secondary" @click="resetFilters">
            <BaseIcon name="refresh" :size="18" />
            <span>Reset</span>
          </BaseButton>
        </div>
      </form>
    </AppPanel>

    <StateNotice
      v-if="errorMessage"
      class="mt-6"
      title="Reservations unavailable"
      :message="errorMessage"
      tone="error"
    />

    <TableCard
      v-else
      class="mt-6"
      title="Reservation list"
      description="Open a reservation to edit dates, locations, extras, and status from the detail screen."
      :columns="[
        { key: 'reservation_number', label: 'Reference' },
        { key: 'client', label: 'Client' },
        { key: 'car', label: 'Car' },
        { key: 'pickup', label: 'Pickup' },
        { key: 'status', label: 'Status' },
        { key: 'payment_status', label: 'Payment' },
      ]"
      :rows="rows"
      @row-click="(row) => navigateTo(`/admin/reservations/${row.id}`)"
    >
      <template #toolbar>
        <span class="text-[0.88rem] text-[var(--text-subtle)]">
          Page {{ currentPage }} / {{ lastPage }}
        </span>
      </template>
      <template #cell-status="{ row }">
        <StatusBadge :value="row.status" />
      </template>
      <template #cell-payment_status="{ row }">
        <StatusBadge :value="row.payment_status" />
      </template>
    </TableCard>

    <div class="mt-4 flex justify-end gap-3">
      <BaseButton
        type="button"
        variant="secondary"
        :disabled="currentPage <= 1 || pending"
        @click="loadReservations(currentPage - 1)"
      >
        <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
        <span>Previous</span>
      </BaseButton>
      <BaseButton
        type="button"
        variant="secondary"
        :disabled="currentPage >= lastPage || pending"
        @click="loadReservations(currentPage + 1)"
      >
        <span>Next</span>
        <BaseIcon name="arrow-right" :size="18" />
      </BaseButton>
    </div>
  </div>
</template>
