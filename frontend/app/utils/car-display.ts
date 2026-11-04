import type { Car, NumericValue } from '~/types/entities'
import { pickCarImagePath, toNumber } from '~/utils/formatters'

// Display helpers shared by the redesigned public pages (fleet list, car page).

/** "1 000" — French grouping with a non-breaking space, no currency symbol. */
export const formatAmount = (value: NumericValue | null | undefined) =>
  new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 })
    .format(toNumber(value))
    .replace(/ /g, ' ')

export const carFullName = (car: Pick<Car, 'brand' | 'name'>) => `${car.brand} ${car.name}`

/** Stored image path for the car's main photo, or null when none was uploaded. */
export const carPhotoPath = (car?: Pick<Car, 'images'> | null) => pickCarImagePath(car)

/** "01", "02", … */
export const rowIndex = (index: number) => String(index + 1).padStart(2, '0')

/** Days between two `YYYY-MM-DDTHH:mm` values, rounded up, minimum 1. */
export const daysBetween = (from: string, to: string) => {
  if (!from || !to) {
    return 1
  }

  const ms = new Date(to).getTime() - new Date(from).getTime()

  if (!Number.isFinite(ms) || ms <= 0) {
    return 1
  }

  return Math.max(1, Math.ceil(ms / 86_400_000))
}

const pad = (value: number) => String(value).padStart(2, '0')

/** Local `YYYY-MM-DDTHH:mm`, the format of `<input type="datetime-local">`. */
export const toDateTimeLocal = (date: Date) =>
  `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`

/** Default rental window: tomorrow 10:00 → `days` later. */
export const defaultRentalWindow = (days = 3) => {
  const start = new Date()
  start.setDate(start.getDate() + 1)
  start.setHours(10, 0, 0, 0)

  const end = new Date(start)
  end.setDate(end.getDate() + days)

  return { from: toDateTimeLocal(start), to: toDateTimeLocal(end) }
}

/** Shift a `YYYY-MM-DDTHH:mm` value by whole days. */
export const addDays = (value: string, days: number) => {
  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
  }

  date.setDate(date.getDate() + days)

  return toDateTimeLocal(date)
}
