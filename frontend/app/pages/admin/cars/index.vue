<script setup lang="ts">
import type { Car, Category, PaginatedResponse, ResourceResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { CAR_STATUS_OPTIONS, CATEGORY_OPTIONS, FUEL_OPTIONS, TRANSMISSION_OPTIONS } from '~/utils/constants'
import { formatCurrency } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const { adminApi, publicApi } = useApi()
const toast = useToast()

const pending = ref(true)
const formPending = ref(false)
const errorMessage = ref('')
const cars = ref<Car[]>([])
const categories = ref<Category[]>([])

const createFormState = () => ({
  base_price_per_day: '',
  insurance_price_per_day: '',
  caution: '',
  brand: '',
  category_id: '',
  climatisation: true,
  doors: 5,
  fuel: 'petrol',
  gps: true,
  is_active: true,
  luggage: 2,
  name: '',
  registration_number: '',
  seats: 5,
  status: 'available',
  transmission: 'auto',
  year: new Date().getFullYear(),
})

const form = reactive(createFormState())
const showCreateModal = ref(false)

interface PendingImage {
  file: File
  preview: string
}

const pendingImages = ref<PendingImage[]>([])

const clearPendingImages = () => {
  pendingImages.value.forEach((item) => URL.revokeObjectURL(item.preview))
  pendingImages.value = []
}

const onImagesSelected = (event: Event) => {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files ?? [])

  files.forEach((file) => {
    pendingImages.value.push({ file, preview: URL.createObjectURL(file) })
  })

  // Allow re-selecting the same file again later.
  input.value = ''
}

const removePendingImage = (index: number) => {
  const [removed] = pendingImages.value.splice(index, 1)
  if (removed) {
    URL.revokeObjectURL(removed.preview)
  }
}

const openCreateModal = () => {
  resetForm()
  clearPendingImages()
  showCreateModal.value = true
}

const categoryOptions = computed(() => {
  const dynamicOptions = categories.value.map((category) => ({
    label: category.name,
    value: category.id,
  }))

  return dynamicOptions.length
    ? dynamicOptions
    : CATEGORY_OPTIONS.map((category) => ({ label: category, value: category }))
})

const tableRows = computed(() =>
  cars.value.map((car) => ({
    active: car.is_active,
    category_name: car.category?.name ?? 'Unknown',
    id: car.id,
    price: formatCurrency(car.base_price_per_day),
    status: car.status,
    vehicle: `${car.brand} ${car.name}`,
    year: car.year,
  })),
)

const loadCars = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const [carsResponse, categoryResponse] = await Promise.all([
      adminApi<PaginatedResponse<Car>>('/cars', {
        query: {
          per_page: 20,
        },
      }),
      publicApi<ResourceResponse<Category[]>>('/public/categories'),
    ])

    cars.value = carsResponse.data
    categories.value = categoryResponse.data
  } catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, {
      fallback: 'The car list could not be loaded.',
    })
    toast.error(errorMessage.value, 'Loading failed')
  } finally {
    pending.value = false
  }
}

const resetForm = () => {
  Object.assign(form, createFormState())
}

const submit = async () => {
  formPending.value = true
  errorMessage.value = ''

  try {
    const payload = {
      ...form,
      base_price_per_day: Number(form.base_price_per_day),
      insurance_price_per_day:
        form.insurance_price_per_day === '' ? null : Number(form.insurance_price_per_day),
      caution: form.caution === '' ? null : Number(form.caution),
      category_id: typeof form.category_id === 'number' ? form.category_id : Number(form.category_id),
      doors: Number(form.doors),
      luggage: Number(form.luggage),
      seats: Number(form.seats),
      year: Number(form.year),
    }

    const response = await adminApi<ResourceResponse<Car>>('/cars', {
      method: 'POST',
      body: payload,
    })

    const newCarId = response.data.id

    // The image endpoint needs an existing car, so upload the picked files now
    // that the car exists. The first image is marked as the thumbnail.
    if (pendingImages.value.length > 0) {
      for (const [index, item] of pendingImages.value.entries()) {
        const imageBody = new FormData()
        imageBody.append('image', item.file)
        imageBody.append('is_thumbnail', index === 0 ? '1' : '0')
        imageBody.append('sort_order', String(index))

        await adminApi(`/cars/${newCarId}/images`, {
          method: 'POST',
          body: imageBody,
        })
      }
    }

    clearPendingImages()
    resetForm()
    await loadCars()
    await navigateTo(`/admin/cars/${newCarId}`)
  } catch (error) {
    toast.apiError(error, 'The car could not be created with the current data.')
  } finally {
    formPending.value = false
  }
}

onMounted(() => {
  void loadCars()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Cars"
      description="Manage the fleet list, create new vehicles, and branch into dedicated edit and image-management screens."
      eyebrow="Fleet manager"
    >
      <div class="flex flex-wrap gap-3">
        <BaseButton type="button" @click="openCreateModal">
          <BaseIcon name="plus" :size="18" />
          <span>Add car</span>
        </BaseButton>
        <BaseButton type="button" variant="secondary" @click="loadCars">
          <BaseIcon name="refresh" :size="18" />
          <span>Refresh</span>
        </BaseButton>
      </div>
    </AdminPageIntro>

    <StateNotice
      v-if="errorMessage"
      title="Loading failed"
      :message="errorMessage"
      tone="error"
    />

    <TableCard
      title="Fleet list"
      description="Select a vehicle to open its full detail page and image manager."
      :columns="[
        { key: 'vehicle', label: 'Vehicle' },
        { key: 'category_name', label: 'Category' },
        { key: 'year', label: 'Year' },
        { key: 'price', label: 'Daily rate' },
        { key: 'status', label: 'Status' },
        { key: 'active', label: 'Live' },
      ]"
      :rows="tableRows"
      @row-click="(row) => navigateTo(`/admin/cars/${row.id}`)"
    >
      <template #cell-vehicle="{ row }">
        <span class="cursor-pointer whitespace-nowrap text-[15px] font-medium text-ink">{{ row.vehicle }}</span>
      </template>
      <template #cell-year="{ row }">
        <span class="cursor-pointer font-plex text-[13px]">{{ row.year }}</span>
      </template>
      <template #cell-price="{ row }">
        <span class="cursor-pointer whitespace-nowrap text-ink">{{ row.price }}</span>
      </template>
      <template #cell-status="{ row }">
        <StatusBadge :value="row.status" />
      </template>
      <template #cell-active="{ row }">
        <StatusBadge :value="row.active ? 'active' : 'inactive'" :label="row.active ? 'Live' : 'Hidden'" />
      </template>
    </TableCard>

    <StateNotice
      v-if="pending"
      class="mt-6"
      title="Loading fleet"
      message="Fetching cars and category reference data."
    />

    <AppModal
      :open="showCreateModal"
      title="Add a car"
      description="Create a new vehicle and optionally attach gallery images. You can manage images later on its detail page."
      size="xl"
      @close="showCreateModal = false"
    >
      <form class="space-y-4" @submit.prevent="submit">
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

        <div>
          <span class="mono-label mb-2 block">
            Images
          </span>
          <input
            type="file"
            accept="image/*"
            multiple
            class="block w-full cursor-pointer border border-line-strong px-3 py-2.5 text-[14px] text-ink-2 file:me-4 file:cursor-pointer file:border-0 file:bg-surface file:px-4 file:py-2 file:font-plex file:text-[12px] file:uppercase file:tracking-[0.06em] file:text-ink hover:file:bg-surface-3"
            @change="onImagesSelected"
          >
          <p class="mt-1.5 text-[13px] leading-5 text-muted">
            Optional. The first image becomes the thumbnail. Uploaded after the car is created (max 50&nbsp;MB each).
          </p>

          <div v-if="pendingImages.length" class="mt-4 grid gap-2 sm:grid-cols-3 md:grid-cols-4">
            <figure
              v-for="(item, index) in pendingImages"
              :key="item.preview"
              class="relative overflow-hidden border border-line bg-surface"
            >
              <img :src="item.preview" alt="Selected car image" class="h-24 w-full object-cover">
              <span
                v-if="index === 0"
                class="absolute left-2 top-2 bg-accent px-1.5 py-0.5 font-plex text-[10px] uppercase tracking-[0.06em] text-on-accent"
              >
                Thumbnail
              </span>
              <button
                type="button"
                class="absolute right-2 top-2 grid size-7 place-items-center bg-black/70 text-white transition-colors hover:bg-black"
                aria-label="Remove image"
                @click="removePendingImage(index)"
              >
                <BaseIcon name="plus" :size="14" class="rotate-45" />
              </button>
            </figure>
          </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
          <BaseButton type="submit" :disabled="formPending">
            <BaseIcon name="plus" :size="18" />
            <span>{{ formPending ? 'Creating...' : 'Create car' }}</span>
          </BaseButton>
          <BaseButton type="button" variant="secondary" @click="showCreateModal = false">
            <span>Cancel</span>
          </BaseButton>
        </div>
      </form>
    </AppModal>
  </div>
</template>
