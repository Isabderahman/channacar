<script setup lang="ts">
import type { Extra, ExtraBillingType, PaginatedResponse, ResourceResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { formatCurrency, isExtraPerDay } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()
const toast = useToast()

const iconOptions = [
  { label: 'Personnes', value: 'people' },
  { label: 'Bouclier', value: 'shield' },
  { label: 'Bébé', value: 'baby' },
  { label: 'Flocon', value: 'snowflake' },
  { label: 'Téléphone', value: 'phone' },
  { label: 'Wifi', value: 'wifi' },
  { label: 'Voiture', value: 'car' },
  { label: 'Carburant', value: 'fuel' },
  { label: 'Étoile', value: 'star' },
  { label: 'Plus', value: 'plus' },
]

const billingTypeOptions = [
  { label: 'Per rental day', value: 'per_day' },
  { label: 'One-time charge', value: 'one_time' },
]

const safeIcon = (value?: string | null): any => value || 'plus'

const extras = ref<Extra[]>([])
const pending = ref(true)
const saving = ref(false)
const selectedId = ref<number | null>(null)
const errorMessage = ref('')

const createFormState = () => ({
  billing_type: 'per_day' as ExtraBillingType,
  icon: '',
  is_active: true,
  name: '',
  price_per_day: '',
})

const form = reactive(createFormState())
const isEditing = computed(() => selectedId.value !== null)

const rows = computed(() =>
  extras.value.map((extra) => ({
    id: extra.id,
    is_active: extra.is_active,
    name: extra.name,
    price: `${formatCurrency(extra.price_per_day)}${isExtraPerDay(extra) ? ' / day' : ' once'}`,
  })),
)

const fillForm = (extra: Extra) => {
  form.billing_type = extra.billing_type ?? 'per_day'
  form.icon = extra.icon ?? ''
  form.is_active = extra.is_active
  form.name = extra.name
  form.price_per_day = String(extra.price_per_day)
  selectedId.value = extra.id
}

const resetForm = () => {
  Object.assign(form, createFormState())
  selectedId.value = null
}

const loadExtras = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PaginatedResponse<Extra>>('/extras', {
      query: { per_page: 100 },
    })
    extras.value = response.data
  } catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, { fallback: 'Extras could not be loaded.' })
    toast.error(errorMessage.value, 'Loading failed')
  } finally {
    pending.value = false
  }
}

const save = async () => {
  saving.value = true
  errorMessage.value = ''

  try {
    const endpoint = isEditing.value ? `/extras/${selectedId.value}` : '/extras'
    const method = isEditing.value ? 'PUT' : 'POST'

    await adminApi<ResourceResponse<Extra>>(endpoint, {
      method,
      body: {
        ...form,
        icon: form.icon || null,
        price_per_day: Number(form.price_per_day),
      },
    })

    toast.success(
      isEditing.value ? 'Extra updated successfully.' : 'Extra created successfully.',
      'Saved',
    )
    resetForm()
    await loadExtras()
  } catch (error) {
    toast.apiError(error, 'The extra could not be saved.')
  } finally {
    saving.value = false
  }
}

const removeSelected = async () => {
  if (!selectedId.value || !window.confirm('Delete this extra?')) {
    return
  }

  saving.value = true

  try {
    await adminApi(`/extras/${selectedId.value}`, { method: 'DELETE' })
    toast.success('Extra deleted.', 'Deleted')
    resetForm()
    await loadExtras()
  } catch (error) {
    toast.apiError(error, 'The extra could not be deleted.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void loadExtras()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Extras"
      description="Manage optional add-ons that can be attached during reservation checkout and admin booking edits."
      eyebrow="Optional extras"
    />

    <StateNotice v-if="errorMessage" title="Loading failed" :message="errorMessage" tone="error" />

    <AdminSplitLayout>
      <template #primary>
        <TableCard
          title="Extra catalogue"
          description="Select an extra to update pricing or availability."
          :columns="[
            { key: 'name', label: 'Extra' },
            { key: 'price', label: 'Price' },
            { key: 'is_active', label: 'Live' },
          ]"
          :rows="rows"
          @row-click="(row) => fillForm(extras.find((extra) => extra.id === row.id)!)"
        >
          <template #cell-is_active="{ row }">
            <StatusBadge :value="row.is_active ? 'active' : 'inactive'" :label="row.is_active ? 'Active' : 'Inactive'" />
          </template>
        </TableCard>
      </template>

      <template #secondary>
        <AppPanel padding="lg">
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">
            {{ isEditing ? 'Edit extra' : 'Create extra' }}
          </h3>

          <form class="mt-6 space-y-4" @submit.prevent="save">
            <FormField v-model="form.name" name="name" label="Extra name" />
            <div class="flex items-end gap-3">
              <div class="flex-1">
                <FormSelect v-model="form.icon" name="icon" label="Icon" :options="iconOptions" />
              </div>
              <span class="mb-1 grid size-11 shrink-0 place-items-center rounded-[14px] bg-[var(--primary-soft)] text-[var(--primary)]">
                <BaseIcon :name="safeIcon(form.icon)" :size="20" />
              </span>
            </div>
            <FormField v-model="form.price_per_day" name="price_per_day" label="Price" type="number" min="0" />
            <FormSelect
              v-model="form.billing_type"
              name="billing_type"
              label="Billing"
              :options="billingTypeOptions"
              help="Per rental day multiplies the price by the number of days; one-time charges it once per reservation."
            />
            <FormCheckbox v-model="form.is_active" name="is_active" label="Available for reservations" />

            <div class="flex flex-wrap gap-3">
              <BaseButton type="submit" :disabled="saving">
                <BaseIcon :name="isEditing ? 'edit' : 'plus'" :size="18" />
                <span>{{ saving ? 'Saving...' : isEditing ? 'Save extra' : 'Create extra' }}</span>
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
