<script setup lang="ts">
import type { PaginatedResponse, PickupLocation, ResourceResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { formatCurrency } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()
const toast = useToast()

const locations = ref<PickupLocation[]>([])
const pending = ref(true)
const saving = ref(false)
const selectedId = ref<number | null>(null)
const errorMessage = ref('')

const createFormState = () => ({
  address: '',
  delivery_fee: '0',
  is_active: true,
  name: '',
})

const form = reactive(createFormState())
const isEditing = computed(() => selectedId.value !== null)

const rows = computed(() =>
  locations.value.map((location) => ({
    address: location.address,
    delivery: Number(location.delivery_fee ?? 0) > 0 ? formatCurrency(location.delivery_fee) : '—',
    id: location.id,
    is_active: location.is_active,
    name: location.name,
  })),
)

const fillForm = (location: PickupLocation) => {
  form.address = location.address
  form.delivery_fee = String(location.delivery_fee ?? 0)
  form.is_active = location.is_active
  form.name = location.name
  selectedId.value = location.id
}

const resetForm = () => {
  Object.assign(form, createFormState())
  selectedId.value = null
}

const loadLocations = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PaginatedResponse<PickupLocation>>('/locations', {
      query: { per_page: 100 },
    })
    locations.value = response.data
  } catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, {
      fallback: 'Locations could not be loaded.',
    })
    toast.error(errorMessage.value, 'Loading failed')
  } finally {
    pending.value = false
  }
}

const save = async () => {
  saving.value = true
  errorMessage.value = ''

  try {
    const endpoint = isEditing.value ? `/locations/${selectedId.value}` : '/locations'
    const method = isEditing.value ? 'PUT' : 'POST'

    await adminApi<ResourceResponse<PickupLocation>>(endpoint, {
      method,
      body: { ...form, delivery_fee: Number(form.delivery_fee) || 0 },
    })

    toast.success(
      isEditing.value ? 'Location updated successfully.' : 'Location created successfully.',
      'Saved',
    )
    resetForm()
    await loadLocations()
  } catch (error) {
    toast.apiError(error, 'The location could not be saved.')
  } finally {
    saving.value = false
  }
}

const removeSelected = async () => {
  if (!selectedId.value || !window.confirm('Delete this location?')) {
    return
  }

  saving.value = true

  try {
    await adminApi(`/locations/${selectedId.value}`, { method: 'DELETE' })
    toast.success('Location deleted.', 'Deleted')
    resetForm()
    await loadLocations()
  } catch (error) {
    toast.apiError(error, 'The location could not be deleted.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void loadLocations()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Locations"
      description="Keep pickup and dropoff destinations active, accurate, and ready for the public booking flow."
      eyebrow="Pickup locations"
    />

    <StateNotice v-if="errorMessage" title="Loading failed" :message="errorMessage" tone="error" />

    <AdminSplitLayout>
      <template #primary>
        <TableCard
          title="Pickup locations"
          description="Edit the destinations used in both public and admin reservation forms."
          :columns="[
            { key: 'name', label: 'Name' },
            { key: 'delivery', label: 'Delivery' },
            { key: 'is_active', label: 'Live' },
          ]"
          :rows="rows"
          @row-click="(row) => fillForm(locations.find((location) => location.id === row.id)!)"
        >
          <template #cell-is_active="{ row }">
            <StatusBadge :value="row.is_active ? 'active' : 'inactive'" :label="row.is_active ? 'Active' : 'Inactive'" />
          </template>
        </TableCard>
      </template>

      <template #secondary>
        <AppPanel padding="lg">
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">
            {{ isEditing ? 'Edit location' : 'Create location' }}
          </h3>

          <form class="mt-6 space-y-4" @submit.prevent="save">
            <FormField v-model="form.name" name="name" label="Location name" />
            <FormField
              v-model="form.delivery_fee"
              name="delivery_fee"
              label="Delivery fee (MAD, 0 for Marrakech)"
              type="number"
              min="0"
            />
            <FormTextarea v-model="form.address" name="address" label="Address" rows="5" />
            <FormCheckbox v-model="form.is_active" name="is_active" label="Active for bookings" />

            <div class="flex flex-wrap gap-3">
              <BaseButton type="submit" :disabled="saving">
                <BaseIcon :name="isEditing ? 'edit' : 'plus'" :size="18" />
                <span>{{ saving ? 'Saving...' : isEditing ? 'Save location' : 'Create location' }}</span>
              </BaseButton>
              <BaseButton type="button" variant="secondary" @click="resetForm">
                <BaseIcon name="refresh" :size="18" />
                <span>Reset</span>
              </BaseButton>
              <BaseButton v-if="isEditing" type="button" variant="ghost" @click="removeSelected">
                <BaseIcon name="trash" :size="18" />
                <span>Delete</span>
              </BaseButton>
            </div>
          </form>
        </AppPanel>
      </template>
    </AdminSplitLayout>
  </div>
</template>
