<script setup lang="ts">
import AppPanel from '~/components/data/AppPanel.vue'
import FormCheckbox from '~/components/forms/FormCheckbox.vue'
import FormField from '~/components/forms/FormField.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { CarImage, ResourceResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { MAX_IMAGE_UPLOAD_MB } from '~/utils/constants'

const props = defineProps<{
  carId: number
  images: CarImage[]
}>()

const emit = defineEmits<{
  changed: []
}>()

const { adminApi, mediaUrl } = useApi()
const toast = useToast()

const files = ref<File[]>([])
const form = reactive({
  isThumbnail: false,
  sortOrder: '',
})
const pending = ref(false)

const onFileChange = (event: Event) => {
  const selected = Array.from((event.target as HTMLInputElement).files ?? [])
  // Catch oversized files here: past the server limit PHP discards the upload
  // before Laravel runs, and the only clue is a bare 422.
  const tooLarge = selected.filter((file) => file.size > MAX_IMAGE_UPLOAD_MB * 1024 * 1024)

  if (tooLarge.length > 0) {
    toast.warning(
      `${tooLarge.map((file) => file.name).join(', ')} exceed${tooLarge.length > 1 ? '' : 's'} the ${MAX_IMAGE_UPLOAD_MB} MB limit. Resize before uploading.`,
      'File too large',
    )
  }

  files.value = selected.filter((file) => !tooLarge.includes(file))
}

const resetForm = () => {
  files.value = []
  form.isThumbnail = false
  form.sortOrder = ''
}

const submit = async () => {
  if (files.value.length === 0) {
    toast.warning('Select at least one image before uploading.', 'Nothing to upload')
    return
  }

  pending.value = true

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

    toast.success(
      files.value.length > 1 ? `${files.value.length} images uploaded.` : 'Image uploaded.',
      'Upload complete',
    )
    resetForm()
    emit('changed')
  } catch (error) {
    const detail = resolveApiErrorMessage(error, {
      fallback: 'Check the selected files and try again.',
    })

    toast.error(
      uploaded > 0
        ? `Uploaded ${uploaded} of ${files.value.length} images before failing. ${detail}`
        : `The image upload failed. ${detail}`,
      'Upload failed',
    )

    if (uploaded > 0) {
      emit('changed')
    }
  } finally {
    pending.value = false
  }
}

const removeImage = async (imageId: number) => {
  pending.value = true

  try {
    await adminApi(`/images/${imageId}`, {
      method: 'DELETE',
    })

    toast.success('Image deleted.', 'Deleted')
    emit('changed')
  } catch (error) {
    toast.apiError(error, 'The image could not be deleted right now.')
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
          {{ files.length > 0 ? `${files.length} image${files.length > 1 ? 's' : ''} selected. The thumbnail flag applies to the first one.` : `Max ${MAX_IMAGE_UPLOAD_MB} MB per image — JPEG, PNG or WebP.` }}
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

      <BaseButton type="submit" :disabled="pending">
        <BaseIcon name="plus" :size="18" />
        <span>{{ pending ? 'Working...' : files.length > 1 ? `Upload ${files.length} images` : 'Upload image' }}</span>
      </BaseButton>
    </form>
  </AppPanel>
</template>
