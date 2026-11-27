import type { BillingDocumentLine, BillingDocumentType, NumericValue } from '~/types/entities'
import { toNumber } from '~/utils/formatters'

// Devis / facture helpers. The backend (BillingDocumentService) is the source
// of truth for totals; these mirror its rules for the live preview only.

export const BILLING_TYPE_LABELS: Record<BillingDocumentType, string> = {
  quote: 'Devis',
  invoice: 'Facture',
}

const round2 = (value: number) => Math.round(value * 100) / 100

/** "1 350,00 DH" — the format printed on the PDF. */
export const formatDh = (value: NumericValue | null | undefined) =>
  `${new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    .format(toNumber(value))
    .replace(/[  ]/g, ' ')} DH`

/** "25/11/2026" from "2026-11-25". */
export const formatDayFr = (value: string | null | undefined) => {
  if (!value) {
    return ''
  }

  const [year, month, day] = value.slice(0, 10).split('-')

  return `${day}/${month}/${year}`
}

/** Whole days between two `YYYY-MM-DD` dates (07/02 → 21/02 = 14), minimum 1; null if incomplete. */
export const daysInPeriod = (start?: string | null, end?: string | null) => {
  if (!start || !end) {
    return null
  }

  const ms = Date.parse(`${end}T00:00:00Z`) - Date.parse(`${start}T00:00:00Z`)

  if (!Number.isFinite(ms) || ms < 0) {
    return null
  }

  return Math.max(1, Math.round(ms / 86_400_000))
}

export const lineTotal = (line: BillingDocumentLine) =>
  line.type === 'flat'
    ? round2(toNumber(line.total))
    : round2(toNumber(line.quantity) * toNumber(line.unit_price))

export const billingTotals = (lines: BillingDocumentLine[], tvaRate: NumericValue) => {
  const ht = round2(lines.reduce((sum, line) => sum + lineTotal(line), 0))
  const tva = round2((ht * toNumber(tvaRate)) / 100)

  return { ht, tva, ttc: round2(ht + tva) }
}

export const emptyBillingLine = (type: BillingDocumentLine['type'] = 'per_day'): BillingDocumentLine => ({
  type,
  designation: type === 'flat' ? '' : 'Location ',
  description: '',
  period_start: null,
  period_end: null,
  quantity: type === 'flat' ? null : 1,
  unit_price: type === 'flat' ? null : 0,
  total: type === 'flat' ? 0 : null,
})

/** `YYYY-MM-DD` for today in the browser's timezone. */
export const todayIso = () => {
  const now = new Date()

  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}
