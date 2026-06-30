import type { Car, NumericValue } from '~/types/entities'

export const toNumber = (value: NumericValue | null | undefined) => {
  if (typeof value === 'number') {
    return value
  }

  if (typeof value === 'string') {
    const parsed = Number(value)

    return Number.isFinite(parsed) ? parsed : 0
  }

  return 0
}

export const formatCurrency = (
  value: NumericValue | null | undefined,
  currency = 'MAD',
  locale = 'en-US',
) =>
  new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
    maximumFractionDigits: 0,
  }).format(toNumber(value))

export const formatDate = (value: string | null | undefined, locale = 'en-US') => {
  if (!value) {
    return 'Not set'
  }

  return new Intl.DateTimeFormat(locale, {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

export const formatTime = (value: string | null | undefined) => {
  if (!value) {
    return '--:--'
  }

  return value.slice(0, 5)
}

export const formatDateTimeLine = (
  date: string | null | undefined,
  time: string | null | undefined,
  locale = 'en-US',
) => `${formatDate(date, locale)} at ${formatTime(time)}`

export const formatBoolean = (value: boolean, trueLabel = 'Yes', falseLabel = 'No') =>
  value ? trueLabel : falseLabel

export const formatEnumLabel = (value: string | null | undefined) => {
  if (!value) {
    return 'Unknown'
  }

  return value
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

export const pickCarImagePath = (car?: Pick<Car, 'images'> | null) =>
  car?.images?.find((image) => image.is_thumbnail)?.path ?? car?.images?.[0]?.path ?? null

export const initialsFromName = (value: string) =>
  value
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((chunk) => chunk[0]?.toUpperCase() ?? '')
    .join('')
