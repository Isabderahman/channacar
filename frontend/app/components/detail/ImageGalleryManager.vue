<script setup lang="ts">
import AppPanel from '~/components/data/AppPanel.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormCheckbox from '~/components/forms/FormCheckbox.vue'
import FormField from '~/components/forms/FormField.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { CarImage, ResourceResponse } from '~/types/entities'

const props = defineProps<{
  carId: number
  images: CarImage[]
}>()

const emit = defineEmits<{
  changed: []
}>()

const { adminApi, mediaUrl } = useApi()

const file = ref<File | null>(null)
const form = reactive({
  isThumbnail: false,
  sortOrder: '',
})
const errorMessage = ref('')
const pending = ref(false)

const onFileChange = (event: Event) => {
  const nextFile = (event.target as HTMLInputElement).files?.[0] ?? null
  file.value = nextFile
}

const resetForm = () => {
  file.value = null
  form.isThumbnail = false
  form.sortOrder = ''
}

const submit = async () => {
  if (!file.value) {
    errorMessage.value = 'Select an image before uploading.'
    return
  }

  pending.value = true
  errorMessage.value = ''

  try {
    const body = new FormData()
    body.append('image', file.value)
    body.append('is_thumbnail', form.isThumbnail ? '1' : '0')

    if (form.sortOrder !== '') {
      body.append('sort_order', form.sortOrder)
    }

    await adminApi<ResourceResponse<CarImage>>(`/cars/${props.carId}/images`, {
      method: 'POST',
      body,
    })

    resetForm()
    emit('changed')
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The image upload failed. Check the selected file.'
  } finally {
    pending.value = false
  }
}

const removeImage = async (imageId: number) => {
  pending.value = true
  errorMessage.value = ''

  try {
    await adminApi(`/images/${imageId}`, {
      method: 'DELETE',
    })

    emit('changed')
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'The image could not be deleted right now.'
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <AppPanel padding="lg">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">Image manager</h3>
        <p class="mt-2 max-w-[56ch] text-[0.92rem] leading-6 text-[var(--text-muted)]">
          Upload gallery images, optionally mark the next upload as the thumbnail, and keep sort
          order explicit.
        </p>
      </div>
      <BaseIcon name="image" :size="20" class="text-[var(--primary)]" />
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <figure
        v-for="image in images"
        :key="image.id"
        class="rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-3"
      >
        <img
          :src="mediaUrl(image.path)"
          alt="Car gallery asset"
          class="h-40 w-full rounded-[14px] object-cover"
        >
        <div class="mt-3 flex items-center justify-between gap-3">
          <div class="text-[0.82rem] text-[var(--text-subtle)]">
            <p>Sort: {{ image.sort_order }}</p>
            <p>{{ image.is_thumbnail ? 'Thumbnail' : 'Gallery image' }}</p>
          </div>
          <BaseButton variant="secondary" size="sm" @click="removeImage(image.id)">
            <BaseIcon name="trash" :size="16" />
            <span>Delete</span>
          </BaseButton>
        </div>
      </figure>
    </div>

    <form class="mt-6 space-y-4" @submit.prevent="submit">
      <div>
        <span class="mb-2 block text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]">
          Image file
        </span>
        <input
          type="file"
          accept="image/*"
          class="block w-full rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3 text-[0.95rem] text-[var(--text-strong)]"
          @change="onFileChange"
        >
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <FormField
          v-model="form.sortOrder"
          name="sort_order"
          label="Sort order"
          type="number"
          min="0"
          placeholder="Optional"
        />
        <FormCheckbox
          v-model="form.isThumbnail"
          name="is_thumbnail"
          label="Set as thumbnail"
          description="Uploading as thumbnail resets the previous thumbnail flag."
        />
      </div>

      <StateNotice
        v-if="errorMessage"
        title="Image action failed"
        :message="errorMessage"
        tone="error"
      />

      <BaseButton type="submit" :disabled="pending">
        <BaseIcon name="plus" :size="18" />
        <span>{{ pending ? 'Working...' : 'Upload image' }}</span>
      </BaseButton>
    </form>
  </AppPanel>
</template>
