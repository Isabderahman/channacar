<script setup lang="ts">
import type {
  Car,
  Client,
  Extra,
  PaginatedResponse,
  PickupLocation,
  Reservation,
  ResourceResponse,
} from '~/types/entities'
import {
  PAYMENT_STATUS_OPTIONS,
  RESERVATION_SOURCE_OPTIONS,
  RESERVATION_STATUS_OPTIONS,
} from '~/utils/constants'
import { formatCurrency } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const route = useRoute()
const { adminApi } = useApi()

const pending = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const reservation = ref<Reservation | null>(null)
const showContractModal = ref(false)
const cars = ref<Car[]>([])
const clients = ref<Client[]>([])
const extras = ref<Extra[]>([])
const locations = ref<PickupLocation[]>([])

const form = reactive({
  car_id: '',
  client_id: '',
  deposit_amount: '',
  dropoff_date: '',
  dropoff_location_id: '',
  dropoff_time: '',
  extra_ids: [] as Array<number | string>,
  apply_insurance: false,
  notes: '',
  payment_status: 'unpaid',
  pickup_date: '',
  pickup_location_id: '',
  pickup_time: '',
  source: 'web',
  status: 'pending',
})

const carOptions = computed(() =>
  cars.value.map((car) => ({
    label: `${car.brand} ${car.name}`,
    value: car.id,
  })),
)

const clientOptions = computed(() =>
  clients.value.map((client) => ({
    label: client.full_name,
    value: client.id,
  })),
)

const locationOptions = computed(() =>
  locations.value.map((location) => ({
    label: location.name,
    value: location.id,
  })),
)

const extraOptions = computed(() =>
  extras.value.map((extra) => ({
    label: `${extra.name} (${formatCurrency(extra.price_per_day)}/day)`,
    value: extra.id,
  })),
)

const summaryItems = computed(() => {
  if (!reservation.value) {
    return []
  }

  return [
    { label: 'Reference', value: reservation.value.reservation_number },
    { label: 'Client', value: reservation.value.client?.full_name ?? 'Unknown' },
    { label: 'Car', value: reservation.value.car ? `${reservation.value.car.brand} ${reservation.value.car.name}` : 'Unknown' },
    { label: 'Total price', value: formatCurrency(reservation.value.total_price) },
  ]
})

const fillForm = (value: Reservation) => {
  form.car_id = String(value.car_id)
  form.client_id = String(value.client_id)
  form.deposit_amount = String(value.deposit_amount)
  form.dropoff_date = value.dropoff_date
  form.dropoff_location_id = String(value.dropoff_location_id)
  form.dropoff_time = value.dropoff_time.slice(0, 5)
  form.extra_ids = value.extras?.map((extra) => extra.id) ?? []
  form.apply_insurance = Number(value.insurance_total ?? 0) > 0
  form.notes = value.notes ?? ''
  form.payment_status = value.payment_status
  form.pickup_date = value.pickup_date
  form.pickup_location_id = String(value.pickup_location_id)
  form.pickup_time = value.pickup_time.slice(0, 5)
  form.source = value.source
  form.status = value.status
}

const loadReservation = async () => {
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const reservationId = Number(route.params.id)
    const [
      reservationResponse,
      carsResponse,
      clientsResponse,
      locationsResponse,
      extrasResponse,
    ] = await Promise.all([
      adminApi<ResourceResponse<Reservation>>(`/reservations/${reservationId}`),
      adminApi<PaginatedResponse<Car>>('/cars', { query: { per_page: 100 } }),
      adminApi<PaginatedResponse<Client>>('/clients', { query: { per_page: 100 } }),
      adminApi<PaginatedResponse<PickupLocation>>('/locations', { query: { per_page: 100 } }),
      adminApi<PaginatedResponse<Extra>>('/extras', { query: { per_page: 100 } }),
    ])

    reservation.value = reservationResponse.data
    cars.value = carsResponse.data
    clients.value = clientsResponse.data
    locations.value = locationsResponse.data
    extras.value = extrasResponse.data
    fillForm(reservationResponse.data)
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The reservation detail could not be loaded.'
  } finally {
    pending.value = false
  }
}

const save = async () => {
  if (!reservation.value) {
    return
  }

  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await adminApi<ResourceResponse<Reservation>>(
      `/reservations/${reservation.value.id}`,
      {
        method: 'PUT',
        body: {
          car_id: Number(form.car_id),
          client_id: Number(form.client_id),
          deposit_amount: Number(form.deposit_amount),
          dropoff_date: form.dropoff_date,
          dropoff_location_id: Number(form.dropoff_location_id),
          dropoff_time: form.dropoff_time,
          extra_ids: form.extra_ids.map((value) => Number(value)),
          apply_insurance: form.apply_insurance,
          notes: form.notes || null,
          payment_status: form.payment_status,
          pickup_date: form.pickup_date,
          pickup_location_id: Number(form.pickup_location_id),
          pickup_time: form.pickup_time,
          source: form.source,
          status: form.status,
        },
      },
    )

    reservation.value = response.data
    fillForm(response.data)
    successMessage.value = 'Reservation updated successfully.'
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The reservation could not be saved.'
  } finally {
    saving.value = false
  }
}

watch(
  () => route.params.id,
  () => {
    void loadReservation()
  },
  { immediate: true },
)
</script>

<template>
  <div>
    <AdminPageIntro
      title="Reservation Detail"
      description="Adjust reservation timing, customer assignment, pricing fields, extras, and status without leaving the shared admin shell."
      eyebrow="Booking detail"
    >
      <div class="flex flex-wrap gap-3">
        <BaseButton v-if="reservation" type="button" @click="showContractModal = true">
          <BaseIcon name="edit" :size="18" />
          <span>Établir le contrat</span>
        </BaseButton>
        <BaseButton to="/admin/reservations" variant="secondary">
          <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
          <span>Back to reservations</span>
        </BaseButton>
      </div>
    </AdminPageIntro>

    <AdminContractModal
      :open="showContractModal"
      :reservation="reservation"
      @close="showContractModal = false"
      @updated="(updated) => { reservation = updated }"
    />

    <StateNotice
      v-if="errorMessage"
      title="Reservation error"
      :message="errorMessage"
      tone="error"
    />
    <StateNotice
      v-else-if="successMessage"
      title="Saved"
      :message="successMessage"
      tone="success"
    />

    <StateNotice
      v-if="pending"
      class="mt-6"
      title="Loading reservation"
      message="Fetching reservation detail and the related car, client, location, and extra options."
    />

    <div v-else-if="reservation" class="space-y-6">
      <AdminSplitLayout>
        <template #primary>
          <AppPanel padding="lg">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">
                  Reservation {{ reservation.reservation_number }}
                </h3>
                <p class="mt-2 text-[0.92rem] leading-6 text-[var(--text-muted)]">
                  Keep operational fields aligned with the pricing and availability rules enforced by the backend.
                </p>
              </div>
              <StatusBadge :value="reservation.status" />
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="save">
              <div class="grid gap-4 md:grid-cols-2">
                <FormSelect v-model="form.client_id" name="client_id" label="Client" :options="clientOptions" />
                <FormSelect v-model="form.car_id" name="car_id" label="Car" :options="carOptions" />
                <FormSelect
                  v-model="form.pickup_location_id"
                  name="pickup_location_id"
                  label="Pickup location"
                  :options="locationOptions"
                />
                <FormSelect
                  v-model="form.dropoff_location_id"
                  name="dropoff_location_id"
                  label="Dropoff location"
                  :options="locationOptions"
                />
                <FormField v-model="form.pickup_date" name="pickup_date" label="Pickup date" type="date" />
                <FormField v-model="form.pickup_time" name="pickup_time" label="Pickup time" type="time" />
                <FormField v-model="form.dropoff_date" name="dropoff_date" label="Dropoff date" type="date" />
                <FormField v-model="form.dropoff_time" name="dropoff_time" label="Dropoff time" type="time" />
                <FormSelect v-model="form.status" name="status" label="Status" :options="[...RESERVATION_STATUS_OPTIONS]" />
                <FormSelect
                  v-model="form.payment_status"
                  name="payment_status"
                  label="Payment status"
                  :options="[...PAYMENT_STATUS_OPTIONS]"
                />
                <FormSelect v-model="form.source" name="source" label="Source" :options="[...RESERVATION_SOURCE_OPTIONS]" />
                <FormField
                  v-model="form.deposit_amount"
                  name="deposit_amount"
                  label="Deposit amount"
                  type="number"
                  min="0"
                />
              </div>

              <FormSelect
                v-model="form.extra_ids"
                name="extra_ids"
                label="Extras"
                :options="extraOptions"
                multiple
                help="Use multi-select to keep optional extras attached to the reservation."
              />

              <FormCheckbox
                v-model="form.apply_insurance"
                name="apply_insurance"
                label="Assurance complémentaire"
                description="Bills the car's insurance fee per rental day when enabled."
              />

              <FormTextarea
                v-model="form.notes"
                name="notes"
                label="Notes"
                placeholder="Operational notes for pickup, dropoff, or customer handling."
              />

              <BaseButton type="submit" :disabled="saving">
                <BaseIcon name="edit" :size="18" />
                <span>{{ saving ? 'Saving...' : 'Save reservation' }}</span>
              </BaseButton>
            </form>
          </AppPanel>
        </template>

        <template #secondary>
          <AppPanel padding="lg">
            <KeyValueList :items="summaryItems" title="Reservation summary" />
          </AppPanel>
        </template>
      </AdminSplitLayout>
    </div>
  </div>
</template>
