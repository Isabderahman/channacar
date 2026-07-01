<script setup lang="ts">
import type { Car, Category, ResourceResponse } from '~/types/entities'
import { CAR_STATUS_OPTIONS, FUEL_OPTIONS, TRANSMISSION_OPTIONS } from '~/utils/constants'
import { formatCurrency } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const route = useRoute()
const { adminApi, publicApi } = useApi()

const pending = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const car = ref<Car | null>(null)
const categories = ref<Category[]>([])

const form = reactive({
  base_price_per_day: '',
  insurance_price_per_day: '',
  caution: '',
  brand: '',
  category_id: '',
  climatisation: false,
  doors: 0,
  fuel: 'petrol',
  gps: false,
  is_active: false,
  luggage: 0,
  name: '',
  registration_number: '',
  seats: 0,
  status: 'available',
  transmission: 'auto',
  year: new Date().getFullYear(),
})

const categoryOptions = computed(() =>
  categories.value.map((category) => ({
    label: category.name,
    value: category.id,
  })),
)

const summaryItems = computed(() => {
  if (!car.value) {
    return []
  }

  return [
    { label: 'Current status', value: car.value.status },
    { label: 'Daily rate', value: formatCurrency(car.value.base_price_per_day) },
    { label: 'Registration', value: car.value.registration_number },
    { label: 'Category', value: car.value.category?.name ?? 'Unknown' },
  ]
})

const fillForm = (value: Car) => {
  form.base_price_per_day = String(value.base_price_per_day)
  form.insurance_price_per_day =
    value.insurance_price_per_day == null ? '' : String(value.insurance_price_per_day)
  form.caution = value.caution == null ? '' : String(value.caution)
  form.brand = value.brand
  form.category_id = String(value.category_id)
  form.climatisation = value.climatisation
  form.doors = value.doors
  form.fuel = value.fuel
  form.gps = value.gps
  form.is_active = value.is_active
  form.luggage = value.luggage
  form.name = value.name
  form.registration_number = value.registration_number
  form.seats = value.seats
  form.status = value.status
  form.transmission = value.transmission
  form.year = value.year
}

const loadCar = async () => {
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const carId = Number(route.params.id)
    const [carResponse, categoryResponse] = await Promise.all([
      adminApi<ResourceResponse<Car>>(`/cars/${carId}`),
      publicApi<ResourceResponse<Category[]>>('/public/categories'),
    ])

    car.value = carResponse.data
    categories.value = categoryResponse.data
    fillForm(carResponse.data)
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'This car could not be loaded.'
  } finally {
    pending.value = false
  }
}

const save = async () => {
  if (!car.value) {
    return
  }

  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await adminApi<ResourceResponse<Car>>(`/cars/${car.value.id}`, {
      method: 'PUT',
      body: {
        ...form,
        base_price_per_day: Number(form.base_price_per_day),
        insurance_price_per_day:
          form.insurance_price_per_day === '' ? null : Number(form.insurance_price_per_day),
        caution: form.caution === '' ? null : Number(form.caution),
        category_id: Number(form.category_id),
        doors: Number(form.doors),
        luggage: Number(form.luggage),
        seats: Number(form.seats),
        year: Number(form.year),
      },
    })

    car.value = response.data
    fillForm(response.data)
    successMessage.value = 'Car updated successfully.'
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The car could not be saved.'
  } finally {
    saving.value = false
  }
}

const deleteCar = async () => {
  if (!car.value || !window.confirm('Delete this car?')) {
    return
  }

  saving.value = true
  errorMessage.value = ''

  try {
    await adminApi(`/cars/${car.value.id}`, {
      method: 'DELETE',
    })

    await navigateTo('/admin/cars')
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The car could not be deleted.'
  } finally {
    saving.value = false
  }
}

watch(
  () => route.params.id,
  () => {
    void loadCar()
  },
  { immediate: true },
)
</script>

<template>
  <div>
    <AdminPageIntro
      title="Car Detail"
      description="Edit the selected vehicle, keep pricing and merchandising current, and manage gallery assets from the same route."
      eyebrow="Fleet detail"
    >
      <div class="flex flex-wrap gap-3">
        <BaseButton to="/admin/cars" variant="secondary">
          <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
          <span>Back to cars</span>
        </BaseButton>
        <BaseButton type="button" variant="ghost" :disabled="saving" @click="deleteCar">
          <BaseIcon name="trash" :size="18" />
          <span>Delete</span>
        </BaseButton>
      </div>
    </AdminPageIntro>

    <StateNotice
      v-if="errorMessage"
      title="Car detail error"
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
      title="Loading car detail"
      message="Fetching the selected vehicle and its reference data."
    />

    <div v-else-if="car" class="space-y-6">
      <AdminSplitLayout>
        <template #primary>
          <AppPanel padding="lg">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">
                  {{ car.brand }} {{ car.name }}
                </h3>
                <p class="mt-2 text-[0.92rem] leading-6 text-[var(--text-muted)]">
                  Update core specs here, then use the image manager below for gallery changes.
                </p>
              </div>
              <StatusBadge :value="car.status" />
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="save">
              <div class="grid gap-4 md:grid-cols-2">
                <FormField v-model="form.brand" name="brand" label="Brand" />
                <FormField v-model="form.name" name="name" label="Model name" />
                <FormField v-model="form.year" name="year" label="Year" type="number" min="1900" />
                <FormField v-model="form.registration_number" name="registration_number" label="Registration" />
                <FormSelect v-model="form.category_id" name="category_id" label="Category" :options="categoryOptions" />
                <FormField v-model="form.base_price_per_day" name="base_price_per_day" label="Base price / day" type="number" min="0" />
                <FormField v-model="form.insurance_price_per_day" name="insurance_price_per_day" label="Insurance / day (optional)" type="number" min="0" />
                <FormField v-model="form.caution" name="caution" label="Caution / deposit (optional)" type="number" min="0" />
                <FormField v-model="form.doors" name="doors" label="Doors" type="number" min="1" />
                <FormField v-model="form.seats" name="seats" label="Seats" type="number" min="1" />
                <FormField v-model="form.luggage" name="luggage" label="Luggage" type="number" min="0" />
                <FormSelect v-model="form.transmission" name="transmission" label="Transmission" :options="[...TRANSMISSION_OPTIONS]" />
                <FormSelect v-model="form.fuel" name="fuel" label="Fuel" :options="[...FUEL_OPTIONS]" />
                <FormSelect v-model="form.status" name="status" label="Status" :options="[...CAR_STATUS_OPTIONS]" />
              </div>

              <div class="grid gap-4 md:grid-cols-3">
                <FormCheckbox v-model="form.climatisation" name="climatisation" label="Air conditioning" />
                <FormCheckbox v-model="form.gps" name="gps" label="GPS included" />
                <FormCheckbox v-model="form.is_active" name="is_active" label="Visible in listings" />
              </div>

              <BaseButton type="submit" :disabled="saving">
                <BaseIcon name="edit" :size="18" />
                <span>{{ saving ? 'Saving...' : 'Save changes' }}</span>
              </BaseButton>
            </form>
          </AppPanel>

          <ImageGalleryManager
            :car-id="car.id"
            :images="car.images ?? []"
            @changed="loadCar"
          />
        </template>

        <template #secondary>
          <AppPanel padding="lg">
            <KeyValueList :items="summaryItems" title="Vehicle summary" />
          </AppPanel>
        </template>
      </AdminSplitLayout>
    </div>
  </div>
</template>
