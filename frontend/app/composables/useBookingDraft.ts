export interface BookingDraft {
  pickupLocationId: string
  /** '' means "same as pickup". */
  dropoffLocationId: string
  /** `YYYY-MM-DDTHH:mm`, '' until the visitor picks one. */
  pickupAt: string
  dropoffAt: string
}

/**
 * The draft's rental length when both dates are set and in order, else null.
 * Days = ceil((return − departure) / 24h), minimum 1.
 */
export const draftDays = (draft: Pick<BookingDraft, 'pickupAt' | 'dropoffAt'>) => {
  if (!draft.pickupAt || !draft.dropoffAt) {
    return null
  }

  const ms = new Date(draft.dropoffAt).getTime() - new Date(draft.pickupAt).getTime()

  if (!Number.isFinite(ms) || ms <= 0) {
    return null
  }

  return Math.max(1, Math.ceil(ms / 86_400_000))
}

const QUERY_KEYS = {
  pickupLocationId: 'pickup',
  dropoffLocationId: 'dropoff',
  pickupAt: 'from',
  dropoffAt: 'to',
} as const satisfies Record<keyof BookingDraft, string>

/**
 * The visitor's place + dates, picked in the homepage booking strip and
 * carried to the fleet list and the car page so they don't re-enter them.
 * Shared state for client navigation; mirrored in the URL query (`pickup`,
 * `dropoff`, `from`, `to`) so it survives a reload or a shared link.
 */
export const useBookingDraft = () => {
  // Dates start empty: homepage cards show the daily price until the
  // visitor picks both, then switch to the total for the period.
  const draft = useState<BookingDraft>('booking-draft', () => ({
    pickupLocationId: '',
    dropoffLocationId: '',
    pickupAt: '',
    dropoffAt: '',
  }))

  // Only a draft the visitor actually edited is carried in links — default
  // dates would otherwise end up in every crawled car URL.
  const touched = useState('booking-draft-touched', () => false)

  /** Query params for links that should carry the draft. */
  const toQuery = (): Record<string, string> | undefined => {
    if (!touched.value) {
      return undefined
    }

    return Object.fromEntries(
      (Object.keys(QUERY_KEYS) as Array<keyof BookingDraft>)
        .filter((key) => draft.value[key])
        .map((key) => [QUERY_KEYS[key], draft.value[key]]),
    )
  }

  /** Pull any draft values present in the current route query. */
  const hydrateFromQuery = (query: Record<string, unknown>) => {
    for (const key of Object.keys(QUERY_KEYS) as Array<keyof BookingDraft>) {
      const value = query[QUERY_KEYS[key]]

      if (typeof value === 'string' && value) {
        draft.value[key] = value
        touched.value = true
      }
    }
  }

  return { draft, touched, toQuery, hydrateFromQuery }
}
