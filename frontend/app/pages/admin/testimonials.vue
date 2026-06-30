<script setup lang="ts">
import type { PaginatedResponse, ResourceResponse, Testimonial } from '~/types/entities'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()

const testimonials = ref<Testimonial[]>([])
const pending = ref(true)
const saving = ref(false)
const selectedId = ref<number | null>(null)
const errorMessage = ref('')
const successMessage = ref('')

const createFormState = () => ({
  content: '',
  full_name: '',
  is_visible: false,
  stars: 5,
})

const form = reactive(createFormState())
const isEditing = computed(() => selectedId.value !== null)

const rows = computed(() =>
  testimonials.value.map((testimonial) => ({
    full_name: testimonial.full_name,
    id: testimonial.id,
    is_visible: testimonial.is_visible,
    preview: testimonial.content.slice(0, 72),
    stars: testimonial.stars,
  })),
)

const fillForm = (testimonial: Testimonial) => {
  form.content = testimonial.content
  form.full_name = testimonial.full_name
  form.is_visible = testimonial.is_visible
  form.stars = testimonial.stars
  selectedId.value = testimonial.id
}

const resetForm = () => {
  Object.assign(form, createFormState())
  selectedId.value = null
  successMessage.value = ''
}

const loadTestimonials = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PaginatedResponse<Testimonial>>('/testimonials', {
      query: { per_page: 100 },
    })
    testimonials.value = response.data
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Testimonials could not be loaded.'
  } finally {
    pending.value = false
  }
}

const save = async () => {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const endpoint = isEditing.value ? `/testimonials/${selectedId.value}` : '/testimonials'
    const method = isEditing.value ? 'PUT' : 'POST'

    await adminApi<ResourceResponse<Testimonial>>(endpoint, {
      method,
      body: {
        ...form,
        stars: Number(form.stars),
      },
    })

    successMessage.value = isEditing.value
      ? 'Testimonial updated successfully.'
      : 'Testimonial created successfully.'
    resetForm()
    await loadTestimonials()
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The testimonial could not be saved.'
  } finally {
    saving.value = false
  }
}

const removeSelected = async () => {
  if (!selectedId.value || !window.confirm('Delete this testimonial?')) {
    return
  }

  saving.value = true

  try {
    await adminApi(`/testimonials/${selectedId.value}`, { method: 'DELETE' })
    resetForm()
    await loadTestimonials()
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The testimonial could not be deleted.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void loadTestimonials()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Testimonials"
      description="Moderate customer quotes before they appear on the storefront and keep the tone aligned with the product."
      eyebrow="Moderation panel"
    />

    <StateNotice v-if="errorMessage" title="Testimonial action failed" :message="errorMessage" tone="error" />
    <StateNotice v-else-if="successMessage" title="Saved" :message="successMessage" tone="success" />

    <AdminSplitLayout>
      <template #primary>
        <TableCard
          title="Moderation queue"
          description="Select a testimonial to update copy, rating, or visibility."
          :columns="[
            { key: 'full_name', label: 'Customer' },
            { key: 'stars', label: 'Stars' },
            { key: 'preview', label: 'Preview' },
            { key: 'is_visible', label: 'Visible' },
          ]"
          :rows="rows"
          @row-click="(row) => fillForm(testimonials.find((testimonial) => testimonial.id === row.id)!)"
        >
          <template #cell-stars="{ row }">
            <span class="font-heading text-[var(--text-strong)]">{{ row.stars }}/5</span>
          </template>
          <template #cell-is_visible="{ row }">
            <StatusBadge :value="row.is_visible ? 'visible' : 'hidden'" :label="row.is_visible ? 'Visible' : 'Hidden'" />
          </template>
        </TableCard>
      </template>

      <template #secondary>
        <AppPanel padding="lg">
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">
            {{ isEditing ? 'Edit testimonial' : 'Create testimonial' }}
          </h3>

          <form class="mt-6 space-y-4" @submit.prevent="save">
            <FormField v-model="form.full_name" name="full_name" label="Customer name" />
            <FormField v-model="form.stars" name="stars" label="Stars" type="number" min="1" max="5" />
            <FormTextarea v-model="form.content" name="content" label="Quote" rows="6" />
            <FormCheckbox v-model="form.is_visible" name="is_visible" label="Visible on storefront" />

            <div class="flex flex-wrap gap-3">
              <BaseButton type="submit" :disabled="saving">
                <BaseIcon :name="isEditing ? 'edit' : 'plus'" :size="18" />
                <span>{{ saving ? 'Saving...' : isEditing ? 'Save testimonial' : 'Create testimonial' }}</span>
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
