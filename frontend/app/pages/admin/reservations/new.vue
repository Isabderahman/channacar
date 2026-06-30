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

const { adminApi } = useApi()

const pending = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const cars = ref<Car[]>([])
const clients = ref<Client[]>([])
const extras = ref<Extra[]>([])
const locations = ref<PickupLocation[]>([])

const createFormState = () => ({
  car_id: '',
  client_id: '',
  deposit_amount: '0',
  dropoff_date: '',
  dropoff_location_id: '',
  dropoff_time: '10:00',
  extra_ids: [] as Array<number | string>,
  apply_insurance: false,
  notes: '',
  payment_status: 'unpaid',
  pickup_date: '',
  pickup_location_id: '',
  pickup_time: '10:00',
  source: 'phone',
  status: 'pending',
})

const form = reactive(createFormState())

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

const hasClients = computed(() => clients.value.length > 0)

const loadReferences = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const [carsResponse, clientsResponse, locationsResponse, extrasResponse] = await Promise.all([
      adminApi<PaginatedResponse<Car>>('/cars', { query: { per_page: 100 } }),
      adminApi<PaginatedResponse<Client>>('/clients', { query: { per_page: 100 } }),
      adminApi<PaginatedResponse<PickupLocation>>('/locations', { query: { per_page: 100 } }),
      adminApi<PaginatedResponse<Extra>>('/extras', { query: { per_page: 100 } }),
    ])

    cars.value = carsResponse.data
    clients.value = clientsResponse.data
    locations.value = locationsResponse.data
    extras.value = extrasResponse.data
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The reservation form could not be prepared.'
  } finally {
    pending.value = false
  }
}

const submit = async () => {
  saving.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<ResourceResponse<Reservation>>('/reservations', {
      method: 'POST',
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
    })

    await navigateTo(`/admin/reservations/${response.data.id}`)
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The reservation could not be created with the current data.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void loadReferences()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="New Reservation"
      description="Create a reservation on behalf of a client for phone, walk-in, or web bookings. Pricing is computed by the backend from the car, season, and selected extras."
      eyebrow="Booking operations"
    >
      <BaseButton to="/admin/reservations" variant="secondary">
        <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
        <span>Back to reservations</span>
      </BaseButton>
    </AdminPageIntro>

    <StateNotice
      v-if="errorMessage"
      title="Reservation could not be created"
      :message="errorMessage"
      tone="error"
    />

    <StateNotice
      v-if="pending"
      class="mt-6"
      title="Preparing form"
      message="Fetching the car, client, location, and extra options."
    />

    <AppPanel v-else-if="!hasClients" padding="lg" class="mt-6">
      <StateNotice
        title="No clients available"
        message="A reservation must be attached to an existing client record. Add a client first, then come back to create the booking."
        tone="warning"
      />
      <BaseButton to="/admin/clients" class="mt-5">
        <BaseIcon name="profile-add" :size="18" />
        <span>Create a client</span>
      </BaseButton>
    </AppPanel>

    <AppPanel v-else padding="lg" class="mt-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">Reservation details</h3>
          <p class="mt-2 text-[0.92rem] leading-6 text-[var(--text-muted)]">
            The same availability and pricing rules used by the public booking flow are enforced here.
          </p>
        </div>
        <BaseIcon name="plus" :size="18" class="text-[var(--primary)]" />
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
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
          help="Use multi-select to attach optional extras to the reservation."
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
          <BaseIcon name="plus" :size="18" />
          <span>{{ saving ? 'Creating...' : 'Create reservation' }}</span>
        </BaseButton>
      </form>
    </AppPanel>
  </div>
</template>
