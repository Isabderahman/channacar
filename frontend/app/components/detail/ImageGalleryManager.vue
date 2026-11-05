<script setup lang="ts">
import AppPanel from '~/components/data/AppPanel.vue'
import FormCheckbox from '~/components/forms/FormCheckbox.vue'
import FormField from '~/components/forms/FormField.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { CarImage } from '~/types/entities'
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

const fileInput = ref<HTMLInputElement | null>(null)

const resetForm = () => {
  files.value = []
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  form.isThumbnail = false
  form.sortOrder = ''
}

// Upload progress for the button label ("Uploading 3 / 12…").
const progress = ref<{ done: number; total: number } | null>(null)
const { uploadImages } = useCarImageUpload()

const submit = async () => {
  if (files.value.length === 0) {
    toast.warning('Select at least one image before uploading.', 'Nothing to upload')
    return
  }

  pending.value = true

  // One request per image (see useCarImageUpload): large photos are resized in
  // the browser first, rate-limit errors are retried, and a failing file no
  // longer stops the rest. Only the first file honours "set as thumbnail", and
  // an explicit sort order is incremented per file so the batch keeps its order.
  const total = files.value.length

  try {
    const { uploaded, failed } = await uploadImages(props.carId, files.value, {
      thumbnailFirst: form.isThumbnail,
      baseSortOrder: form.sortOrder !== '' ? Number(form.sortOrder) : null,
      onProgress: (done, count) => {
        progress.value = { done, total: count }
      },
    })

    if (failed.length === 0) {
      toast.success(total > 1 ? `${total} images uploaded.` : 'Image uploaded.', 'Upload complete')
      resetForm()
    } else {
      const details = failed.map((failure) => `${failure.name}: ${failure.message}`).join(' · ')
      toast.error(
        uploaded > 0
          ? `${uploaded} of ${total} images uploaded. Not uploaded — ${details}`
          : `The image upload failed. ${details}`,
        'Upload failed',
      )
      // Keep only the files that failed selected, so they can be retried.
      const failedNames = new Set(failed.map((failure) => failure.name))
      files.value = files.value.filter((file) => failedNames.has(file.name))
    }

    if (uploaded > 0) {
      emit('changed')
    }
  } finally {
    pending.value = false
    progress.value = null
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
        <h3 class="text-[22px] font-medium leading-tight tracking-[-0.02em]">Image manager</h3>
        <p class="mt-1.5 max-w-[56ch] text-[14px] leading-6 text-ink-2">
          Upload gallery images, optionally mark the next upload as the thumbnail, and keep sort
          order explicit.
        </p>
      </div>
      <BaseIcon name="image" :size="20" class="shrink-0 text-accent" />
    </div>

    <div class="mt-6 grid gap-2 sm:grid-cols-2">
      <figure
        v-for="image in images"
        :key="image.id"
        class="border border-line"
      >
        <img
          :src="mediaUrl(image.path)"
          alt="Car gallery asset"
          class="h-40 w-full bg-surface object-cover"
        >
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line px-3 py-2.5">
          <div class="font-plex text-[11px] uppercase tracking-[0.06em] text-muted">
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

    <form class="mt-6 space-y-4 border-t border-line pt-6" @submit.prevent="submit">
      <div>
        <span class="mono-label mb-2 block">
          Image files
        </span>
        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          multiple
          class="block w-full cursor-pointer border border-line-strong px-3 py-2.5 text-[14px] text-ink-2 file:me-4 file:cursor-pointer file:border-0 file:bg-surface file:px-4 file:py-2 file:font-plex file:text-[12px] file:uppercase file:tracking-[0.06em] file:text-ink hover:file:bg-surface-3"
          @change="onFileChange"
        >
        <p class="mt-1.5 text-[13px] text-muted">
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
        <span>{{ progress ? `Uploading ${Math.min(progress.done + 1, progress.total)} / ${progress.total}…` : pending ? 'Working...' : files.length > 1 ? `Upload ${files.length} images` : 'Upload image' }}</span>
      </BaseButton>
    </form>
  </AppPanel>
</template>
