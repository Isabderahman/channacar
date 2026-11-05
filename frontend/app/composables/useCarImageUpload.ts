import type { CarImage, ResourceResponse } from '~/types/entities'
import { getErrorStatus, resolveApiErrorMessage } from '~/utils/api-error'

/**
 * Longest edge the backend keeps (CarImageController::MAX_DIMENSION). Anything
 * bigger is downscaled server-side anyway, so shrinking it in the browser first
 * loses nothing visible — and turns a 10–25 MB camera photo into ~1 MB, which
 * makes big batches faster and spares the server a 100+ MB GD bitmap per photo.
 */
const MAX_DIMENSION = 2560

/** Statuses worth retrying: rate limit, gateway hiccups, network failure (0). */
const RETRYABLE = new Set([0, 429, 502, 503, 504])
const MAX_RETRIES = 3

export interface CarImageUploadFailure {
  name: string
  message: string
}

export interface CarImageUploadResult {
  uploaded: number
  failed: CarImageUploadFailure[]
}

export interface CarImageUploadOptions {
  /** Flag the first file of the batch as the car's thumbnail. */
  thumbnailFirst?: boolean
  /** Sort order for the first file, incremented per file; null = append. */
  baseSortOrder?: number | null
  /** Called before each file (done = files finished so far). */
  onProgress?: (done: number, total: number) => void
}

const sleep = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms))

/** Seconds from a 429's Retry-After header, else a growing backoff. */
const retryDelay = (error: unknown, attempt: number) => {
  const header = (error as { response?: { headers?: Headers } })?.response?.headers?.get?.('retry-after')
  const seconds = Number(header)

  return Number.isFinite(seconds) && seconds > 0 ? Math.min(seconds, 60) * 1000 : 1500 * attempt
}

/**
 * Downscale a photo larger than MAX_DIMENSION in the browser. Smaller images,
 * and anything the browser can't decode (e.g. HEIC outside Safari), are sent
 * untouched so the server can still accept them or explain the problem.
 */
export const prepareCarImage = async (file: File): Promise<File> => {
  if (typeof createImageBitmap !== 'function' || typeof document === 'undefined') {
    return file
  }

  let bitmap: ImageBitmap

  try {
    // Applies the EXIF rotation, so the re-encoded pixels are already upright.
    bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' })
  } catch {
    return file
  }

  try {
    const longest = Math.max(bitmap.width, bitmap.height)

    if (longest <= MAX_DIMENSION) {
      return file
    }

    const ratio = MAX_DIMENSION / longest
    const canvas = document.createElement('canvas')
    canvas.width = Math.round(bitmap.width * ratio)
    canvas.height = Math.round(bitmap.height * ratio)

    const context = canvas.getContext('2d')
    if (!context) {
      return file
    }

    context.imageSmoothingQuality = 'high'
    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height)

    // PNG/WebP may be transparent cut-outs: keep the alpha channel (WebP).
    // Photos go out as high-quality JPEG; the server re-encodes to WebP.
    const keepsAlpha = file.type === 'image/png' || file.type === 'image/webp'
    const blob = await new Promise<Blob | null>((resolve) =>
      canvas.toBlob(resolve, keepsAlpha ? 'image/webp' : 'image/jpeg', 0.92),
    )

    if (!blob || !blob.type.startsWith('image/')) {
      return file
    }

    const extension = blob.type === 'image/jpeg' ? 'jpg' : blob.type.slice('image/'.length)
    const name = `${file.name.replace(/\.[^.]+$/, '')}.${extension}`

    return new File([blob], name, { type: blob.type, lastModified: file.lastModified })
  } finally {
    bitmap.close()
  }
}

/**
 * Uploads car photos one request at a time (the endpoint takes one image per
 * request). A failing photo no longer stops the batch: rate-limit and gateway
 * errors are retried, and the rest are reported per file at the end.
 */
export const useCarImageUpload = () => {
  const { adminApi } = useApi()

  const uploadImages = async (
    carId: number,
    files: File[],
    options: CarImageUploadOptions = {},
  ): Promise<CarImageUploadResult> => {
    const failed: CarImageUploadFailure[] = []
    let uploaded = 0

    for (const [index, original] of files.entries()) {
      options.onProgress?.(index, files.length)

      const file = await prepareCarImage(original)
      const body = new FormData()
      body.append('image', file)
      body.append('is_thumbnail', index === 0 && options.thumbnailFirst ? '1' : '0')

      if (options.baseSortOrder !== null && options.baseSortOrder !== undefined) {
        body.append('sort_order', String(options.baseSortOrder + index))
      }

      for (let attempt = 0; ; attempt += 1) {
        try {
          await adminApi<ResourceResponse<CarImage>>(`/cars/${carId}/images`, {
            method: 'POST',
            body,
          })
          uploaded += 1
          break
        } catch (error) {
          if (RETRYABLE.has(getErrorStatus(error)) && attempt < MAX_RETRIES) {
            await sleep(retryDelay(error, attempt + 1))
            continue
          }

          failed.push({
            name: original.name,
            message: resolveApiErrorMessage(error, {
              fallback: 'Check the file and try again.',
            }),
          })
          break
        }
      }
    }

    options.onProgress?.(files.length, files.length)

    return { uploaded, failed }
  }

  return { uploadImages }
}
