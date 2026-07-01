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

const files = ref<File[]>([])
const form = reactive({
  isThumbnail: false,
  sortOrder: '',
})
const errorMessage = ref('')
const pending = ref(false)

const onFileChange = (event: Event) => {
  files.value = Array.from((event.target as HTMLInputElement).files ?? [])
}

const resetForm = () => {
  files.value = []
  form.isThumbnail = false
  form.sortOrder = ''
}

const submit = async () => {
  if (files.value.length === 0) {
    errorMessage.value = 'Select at least one image before uploading.'
    return
  }

  pending.value = true
  errorMessage.value = ''

  // The backend accepts one image per request, so upload the selection
  // sequentially. Only the first file honours the "set as thumbnail" flag, and
  // an explicit sort order is incremented per file so the batch keeps its order.
  const baseSortOrder = form.sortOrder !== '' ? Number(form.sortOrder) : null
  let uploaded = 0

  try {
    for (const [index, file] of files.value.entries()) {
      const body = new FormData()
      body.append('image', file)
      body.append('is_thumbnail', index === 0 && form.isThumbnail ? '1' : '0')

      if (baseSortOrder !== null) {
        body.append('sort_order', String(baseSortOrder + index))
      }

      await adminApi<ResourceResponse<CarImage>>(`/cars/${props.carId}/images`, {
        method: 'POST',
        body,
      })

      uploaded += 1
    }

    resetForm()
    emit('changed')
  } catch (error) {
    const detail =
      error instanceof Error ? error.message : 'Check the selected files and try again.'
    errorMessage.value =
      uploaded > 0
        ? `Uploaded ${uploaded} of ${files.value.length} images before failing. ${detail}`
        : `The image upload failed. ${detail}`

    if (uploaded > 0) {
      emit('changed')
    }
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
          Image files
        </span>
        <input
          type="file"
          accept="image/*"
          multiple
          class="block w-full rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3 text-[0.95rem] text-[var(--text-strong)]"
          @change="onFileChange"
        >
        <p class="mt-2 text-[0.82rem] text-[var(--text-subtle)]">
          {{ files.length > 0 ? `${files.length} image${files.length > 1 ? 's' : ''} selected. The thumbnail flag applies to the first one.` : 'Max 50 MB per image.' }}
        </p>
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
        <span>{{ pending ? 'Working...' : files.length > 1 ? `Upload ${files.length} images` : 'Upload image' }}</span>
      </BaseButton>
    </form>
  </AppPanel>
</template>
