<script setup lang="ts">
import type { PaginatedResponse, ResourceResponse, Season } from '~/types/entities'
import { SEASON_PRICE_TYPE_OPTIONS } from '~/utils/constants'
import { formatCurrency, formatDate } from '~/utils/formatters'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()

const seasons = ref<Season[]>([])
const pending = ref(true)
const saving = ref(false)
const selectedId = ref<number | null>(null)
const errorMessage = ref('')
const successMessage = ref('')

const createFormState = () => ({
  date_from: '',
  date_to: '',
  name: '',
  price_type: 'multiplier',
  price_value: '',
})

const form = reactive(createFormState())
const isEditing = computed(() => selectedId.value !== null)

const rows = computed(() =>
  seasons.value.map((season) => ({
    id: season.id,
    name: season.name,
    price_type: season.price_type,
    price_value:
      season.price_type === 'fixed' ? formatCurrency(season.price_value) : `${season.price_value}x`,
    range: `${formatDate(season.date_from)} - ${formatDate(season.date_to)}`,
  })),
)

const fillForm = (season: Season) => {
  form.date_from = season.date_from
  form.date_to = season.date_to
  form.name = season.name
  form.price_type = season.price_type
  form.price_value = String(season.price_value)
  selectedId.value = season.id
}

const resetForm = () => {
  Object.assign(form, createFormState())
  selectedId.value = null
  successMessage.value = ''
}

const loadSeasons = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PaginatedResponse<Season>>('/seasons', {
      query: { per_page: 100 },
    })
    seasons.value = response.data
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Seasons could not be loaded.'
  } finally {
    pending.value = false
  }
}

const save = async () => {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const endpoint = isEditing.value ? `/seasons/${selectedId.value}` : '/seasons'
    const method = isEditing.value ? 'PUT' : 'POST'

    await adminApi<ResourceResponse<Season>>(endpoint, {
      method,
      body: {
        ...form,
        price_value: Number(form.price_value),
      },
    })

    successMessage.value = isEditing.value ? 'Season updated successfully.' : 'Season created successfully.'
    resetForm()
    await loadSeasons()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'The season could not be saved.'
  } finally {
    saving.value = false
  }
}

const removeSelected = async () => {
  if (!selectedId.value || !window.confirm('Delete this season?')) {
    return
  }

  saving.value = true

  try {
    await adminApi(`/seasons/${selectedId.value}`, { method: 'DELETE' })
    resetForm()
    await loadSeasons()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'The season could not be deleted.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void loadSeasons()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Seasons"
      description="Manage seasonal pricing windows and keep the car-pricing rules aligned with your operating calendar."
      eyebrow="Season manager"
    />

    <StateNotice v-if="errorMessage" title="Season action failed" :message="errorMessage" tone="error" />
    <StateNotice v-else-if="successMessage" title="Saved" :message="successMessage" tone="success" />

    <AdminSplitLayout>
      <template #primary>
        <TableCard
          title="Season windows"
          description="Click a season to edit the date range or pricing strategy."
          :columns="[
            { key: 'name', label: 'Season' },
            { key: 'range', label: 'Date range' },
            { key: 'price_type', label: 'Type' },
            { key: 'price_value', label: 'Value' },
          ]"
          :rows="rows"
          @row-click="(row) => fillForm(seasons.find((season) => season.id === row.id)!)"
        >
          <template #cell-price_type="{ row }">
            <StatusBadge :value="row.price_type" />
          </template>
        </TableCard>
      </template>

      <template #secondary>
        <AppPanel padding="lg">
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">
            {{ isEditing ? 'Edit season' : 'Create season' }}
          </h3>

          <form class="mt-6 space-y-4" @submit.prevent="save">
            <FormField v-model="form.name" name="name" label="Season name" />
            <FormField v-model="form.date_from" name="date_from" label="Start date" type="date" />
            <FormField v-model="form.date_to" name="date_to" label="End date" type="date" />
            <FormSelect v-model="form.price_type" name="price_type" label="Pricing type" :options="[...SEASON_PRICE_TYPE_OPTIONS]" />
            <FormField
              v-model="form.price_value"
              name="price_value"
              label="Price value"
              type="number"
              min="0"
              step="0.01"
            />

            <div class="flex flex-wrap gap-3">
              <BaseButton type="submit" :disabled="saving">
                <BaseIcon :name="isEditing ? 'edit' : 'plus'" :size="18" />
                <span>{{ saving ? 'Saving...' : isEditing ? 'Save season' : 'Create season' }}</span>
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
