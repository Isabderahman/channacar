<script setup lang="ts">
import AdminExtrasPicker from '~/components/admin/AdminExtrasPicker.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormField from '~/components/forms/FormField.vue'
import FormSelect from '~/components/forms/FormSelect.vue'
import FormTextarea from '~/components/forms/FormTextarea.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type {
  Car,
  Client,
  Extra,
  PaginatedResponse,
  PickupLocation,
  Reservation,
  ResourceResponse,
} from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import {
  PAYMENT_STATUS_OPTIONS,
  RESERVATION_SOURCE_OPTIONS,
  RESERVATION_STATUS_OPTIONS,
} from '~/utils/constants'
import { toNumber } from '~/utils/formatters'

/**
 * Reservation creation form shared by the dedicated page
 * (`pages/admin/reservations/new.vue`) and the planning board modal, so booking
 * a car from either surface runs the exact same payload and validation.
 */
const props = withDefaults(
  defineProps<{
    /** Preselected car — the planning row that was clicked. */
    presetCarId?: number | null
    /** Preselected pickup day — the planning cell that was clicked. */
    presetPickupDate?: string | null
    submitLabel?: string
    showCancel?: boolean
  }>(),
  {
    presetCarId: null,
    presetPickupDate: null,
    submitLabel: 'Create reservation',
    showCancel: false,
  },
)

const emit = defineEmits<{
  cancel: []
  created: [reservation: Reservation]
}>()

const { adminApi } = useApi()
const toast = useToast()

const pending = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const cars = ref<Car[]>([])
const clients = ref<Client[]>([])
const extras = ref<Extra[]>([])
const locations = ref<PickupLocation[]>([])

const addDays = (iso: string, amount: number) => {
  const [year, month, day] = iso.split('-').map(Number)
  const date = new Date(year ?? 1970, (month ?? 1) - 1, day ?? 1)
  date.setDate(date.getDate() + amount)

  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const createFormState = () => ({
  car_id: props.presetCarId ? String(props.presetCarId) : '',
  client_id: '',
  deposit_amount: '0',
  dropoff_date: props.presetPickupDate ? addDays(props.presetPickupDate, 1) : '',
  dropoff_location_id: '',
  dropoff_time: '10:00',
  extra_ids: [] as Array<number | string>,
  apply_insurance: false,
  notes: '',
  payment_status: 'unpaid',
  pickup_date: props.presetPickupDate ?? '',
  pickup_location_id: '',
  pickup_time: '10:00',
  source: 'phone',
  status: 'pending',
})

const form = reactive(createFormState())

const carOptions = computed(() =>
  cars.value.map((car) => ({
    label: `${car.brand} ${car.name} · ${car.registration_number}`,
    value: car.id,
  })),
)

const clientOptions = computed(() =>
  clients.value.map((client) => ({
    label: `${client.full_name} · ${client.phone}`,
    value: client.id,
  })),
)

const locationOptions = computed(() =>
  locations.value.map((location) => ({
    label: location.name,
    value: location.id,
  })),
)

const hasClients = computed(() => clients.value.length > 0)

const selectedCar = computed(
  () => cars.value.find((car) => String(car.id) === String(form.car_id)) ?? null,
)

const insurancePerDay = computed(() => toNumber(selectedCar.value?.insurance_price_per_day))

// Same rule as the public booking flow: a same-day return still bills one day.
const rentalDays = computed(() => {
  if (!form.pickup_date || !form.dropoff_date) {
    return 0
  }

  const ms = new Date(form.dropoff_date).getTime() - new Date(form.pickup_date).getTime()

  return ms <= 0 ? 1 : Math.max(1, Math.ceil(ms / 86_400_000))
})

// A car without an insurance fee hides the card, so a stale flag must not keep
// billing an insurance the operator can no longer see.
watch(insurancePerDay, (value) => {
  if (value <= 0) {
    form.apply_insurance = false
  }
})

// Dropoff defaults to the day after pickup, but never fights a manual edit.
watch(
  () => form.pickup_date,
  (value, previous) => {
    if (!value) {
      return
    }

    if (!form.dropoff_date || form.dropoff_date <= value || (previous && form.dropoff_date === addDays(previous, 1))) {
      form.dropoff_date = addDays(value, 1)
    }
  },
)

watch(
  () => form.pickup_location_id,
  (value) => {
    if (!form.dropoff_location_id && value) {
      form.dropoff_location_id = value
    }
  },
)

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
    errorMessage.value = resolveApiErrorMessage(error, {
      fallback: 'The reservation form could not be prepared.',
    })
    toast.error(errorMessage.value, 'Loading failed')
  } finally {
    pending.value = false
  }
}

/** Re-apply the presets — used when the modal reopens on another car or day. */
const reset = () => {
  Object.assign(form, createFormState())
}

const submit = async () => {
  saving.value = true

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

    toast.success(
      `Reservation ${response.data.reservation_number} created.`,
      'Reservation created',
    )
    emit('created', response.data)
  } catch (error) {
    toast.apiError(error, 'The reservation could not be created with the current data.')
  } finally {
    saving.value = false
  }
}

watch(
  () => [props.presetCarId, props.presetPickupDate],
  () => {
    reset()
  },
)

onMounted(() => {
  void loadReferences()
})

defineExpose({ reset })
</script>

<template>
  <div>
    <StateNotice v-if="errorMessage" title="Loading failed" :message="errorMessage" tone="error" />

    <StateNotice
      v-if="pending"
      title="Preparing form"
      message="Fetching the car, client, location, and extra options."
    />

    <div v-else-if="!hasClients">
      <StateNotice
        title="No clients available"
        message="A reservation must be attached to an existing client record. Add a client first, then come back to create the booking."
        tone="warning"
      />
      <BaseButton to="/admin/clients" class="mt-5">
        <BaseIcon name="profile-add" :size="18" />
        <span>Create a client</span>
      </BaseButton>
    </div>

    <form v-else class="space-y-4" @submit.prevent="submit">
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

      <AdminExtrasPicker
        v-model="form.extra_ids"
        v-model:insurance="form.apply_insurance"
        :extras="extras"
        :rental-days="rentalDays"
        :insurance-per-day="insurancePerDay"
      />

      <FormTextarea
        v-model="form.notes"
        name="notes"
        label="Notes"
        placeholder="Operational notes for pickup, dropoff, or customer handling."
      />

      <div class="flex flex-wrap items-center gap-3">
        <BaseButton type="submit" :disabled="saving">
          <BaseIcon name="plus" :size="18" />
          <span>{{ saving ? 'Creating...' : submitLabel }}</span>
        </BaseButton>

        <BaseButton v-if="showCancel" type="button" variant="secondary" @click="emit('cancel')">
          <span>Cancel</span>
        </BaseButton>
      </div>
    </form>
  </div>
</template>
