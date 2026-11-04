import type { Ref } from 'vue'
import type { Car, Extra, PickupLocation, Reservation, ResourceResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import {
  addDays,
  carFullName,
  daysBetween,
  defaultRentalWindow,
  formatAmount,
  toDateTimeLocal,
} from '~/utils/car-display'
import { extraLineTotal, toNumber } from '~/utils/formatters'
import { whatsappHref } from '~/utils/whatsapp'

/**
 * State + pricing for the car page booking flow. One instance is shared by the
 * info column (live estimate), the form, the summary panel and the mobile
 * bottom bar, so they always show the same numbers.
 *
 * The total is a client-side estimate: days × (daily price + per-day options
 * + insurance) + one-time options + delivery fees. The backend
 * (ReservationService) applies seasonal rates and stores the real price.
 */
export const useCarBooking = (
  car: Ref<Car>,
  extras: Ref<Extra[]>,
  locations: Ref<PickupLocation[]>,
) => {
  const route = useRoute()
  const router = useRouter()
  const { publicApi } = useApi()
  const toast = useToast({
    locale: 'fr',
    errorTitle: 'Réservation échouée',
    successTitle: 'Réservation confirmée',
  })

  // Start from the place/dates picked on the homepage (or in the URL).
  const { draft, hydrateFromQuery } = useBookingDraft()
  hydrateFromQuery(route.query)

  // No dates picked yet → tomorrow 10:00 for 3 days, so the estimate is
  // meaningful from the start.
  const fallbackWindow = defaultRentalWindow()
  const initialPickup = draft.value.pickupAt || fallbackWindow.from
  const initialDropoff =
    draft.value.dropoffAt && draft.value.dropoffAt > initialPickup
      ? draft.value.dropoffAt
      : addDays(initialPickup, 3)

  const form = reactive({
    pickupLocationId: draft.value.pickupLocationId || String(locations.value[0]?.id ?? ''),
    /** '' = same as pickup */
    dropoffLocationId: draft.value.dropoffLocationId,
    pickupAt: initialPickup,
    dropoffAt: initialDropoff,
    extraIds: [] as number[],
    insurance: false,
    fullName: '',
    phone: '',
    whatsappReachable: true,
    email: '',
    driverLicense: '',
    notes: '',
  })

  // ── Duration ───────────────────────────────────────────────────────────
  const days = computed(() => daysBetween(form.pickupAt, form.dropoffAt))
  const daysLabel = computed(() => `${days.value} jour${days.value > 1 ? 's' : ''}`)

  // Moving the pickup keeps the rental length.
  watch(
    () => form.pickupAt,
    (next, previous) => {
      if (!next || !previous) {
        return
      }

      const span = new Date(form.dropoffAt).getTime() - new Date(previous).getTime()
      const shifted = new Date(new Date(next).getTime() + (span > 0 ? span : 3 * 86_400_000))

      if (!Number.isNaN(shifted.getTime())) {
        form.dropoffAt = toDateTimeLocal(shifted)
      }
    },
  )

  const setDays = (count: number) => {
    if (form.pickupAt) {
      form.dropoffAt = addDays(form.pickupAt, Math.max(1, count))
    }
  }

  const incrementDays = () => setDays(days.value + 1)
  const decrementDays = () => setDays(days.value - 1)

  // ── Options ────────────────────────────────────────────────────────────
  const isExtraSelected = (id: number) => form.extraIds.includes(id)

  const toggleExtra = (id: number) => {
    const index = form.extraIds.indexOf(id)
    if (index >= 0) {
      form.extraIds.splice(index, 1)
    } else {
      form.extraIds.push(id)
    }
  }

  const selectedExtras = computed(() => extras.value.filter((extra) => form.extraIds.includes(extra.id)))

  // ── Pricing ────────────────────────────────────────────────────────────
  const basePerDay = computed(() => toNumber(car.value.base_price_per_day))
  const insurancePerDay = computed(() => toNumber(car.value.insurance_price_per_day))
  const hasInsurance = computed(() => insurancePerDay.value > 0)
  const caution = computed(() => toNumber(car.value.caution))
  const hasCaution = computed(() => caution.value > 0)

  const rentalTotal = computed(() => basePerDay.value * days.value)
  const insuranceTotal = computed(() =>
    form.insurance && hasInsurance.value ? insurancePerDay.value * days.value : 0,
  )

  // Delivery (pickup) + return (dropoff) fees, each based on its city
  // (Marrakech = 0). Both apply, even when returning to the same place.
  const locationFee = (id: string) =>
    toNumber(locations.value.find((entry) => String(entry.id) === String(id))?.delivery_fee)

  const effectiveDropoffId = computed(() => form.dropoffLocationId || form.pickupLocationId)
  const deliveryTotal = computed(
    () => locationFee(form.pickupLocationId) + locationFee(effectiveDropoffId.value),
  )

  /** Summary lines after the rental line: each option, insurance, delivery. */
  const lines = computed(() => [
    ...selectedExtras.value.map((extra) => ({
      key: `extra-${extra.id}`,
      label: extra.billing_type === 'one_time' ? `${extra.name} (une fois)` : extra.name,
      amount: extraLineTotal(extra, days.value),
    })),
    ...(insuranceTotal.value > 0
      ? [{ key: 'insurance', label: 'Assurance complémentaire', amount: insuranceTotal.value }]
      : []),
    ...(deliveryTotal.value > 0
      ? [{ key: 'delivery', label: 'Frais de livraison et reprise', amount: deliveryTotal.value }]
      : []),
  ])

  const total = computed(
    () => rentalTotal.value + lines.value.reduce((sum, line) => sum + line.amount, 0),
  )

  const fmt = (value: number) => formatAmount(value)

  // ── WhatsApp ───────────────────────────────────────────────────────────
  const carName = computed(() => carFullName(car.value))

  const whatsappLink = computed(() => {
    const article = /^[aeiouyhâàéèêîôû]/i.test(car.value.brand) ? 'l’' : 'la '
    const dates =
      form.pickupAt && form.dropoffAt
        ? ` (du ${formatShortDate(form.pickupAt)} au ${formatShortDate(form.dropoffAt)})`
        : ''

    return whatsappHref(
      `Bonjour ChanaaCar, je souhaite réserver ${article}${carName.value} pour ${daysLabel.value}${dates}.`,
    )
  })

  // ── Submit ─────────────────────────────────────────────────────────────
  const pending = ref(false)
  const errorMessage = ref('')

  const submit = async () => {
    if (pending.value) {
      return
    }

    pending.value = true
    errorMessage.value = ''

    let reservation: Reservation
    try {
      const response = await publicApi<ResourceResponse<Reservation>>('/public/reservations', {
        method: 'POST',
        body: {
          car_id: car.value.id,
          pickup_location_id: Number(form.pickupLocationId),
          dropoff_location_id: Number(effectiveDropoffId.value),
          pickup_date: form.pickupAt.slice(0, 10),
          pickup_time: form.pickupAt.slice(11, 16),
          dropoff_date: form.dropoffAt.slice(0, 10),
          dropoff_time: form.dropoffAt.slice(11, 16),
          notes: form.notes || null,
          extra_ids: form.extraIds,
          apply_insurance: form.insurance,
          client: {
            full_name: form.fullName,
            phone: form.phone,
            whatsapp: form.whatsappReachable ? form.phone : null,
            email: form.email || null,
            driver_license: form.driverLicense,
          },
        },
      })
      reservation = response.data
    } catch (error) {
      // Kept inline next to the summary as well: the visitor has a long form
      // to correct, and the toast fades.
      errorMessage.value = resolveApiErrorMessage(error, {
        locale: 'fr',
        fallback:
          'La réservation n’a pas pu être envoyée. Vérifiez les dates et les options sélectionnées.',
      })
      toast.error(errorMessage.value)
      pending.value = false
      return
    }

    // The reservation exists at this point. Anything failing below is only
    // about reaching the confirmation page — never report it as a booking
    // failure, or the visitor may retry and create a duplicate.
    toast.success(`Réservation ${reservation.reservation_number} créée avec succès.`)

    const target = {
      path: '/reservation/success',
      query: {
        car: carName.value,
        reservation: reservation.reservation_number,
        from: route.fullPath,
      },
    }

    try {
      await navigateTo(target)
    } catch {
      // The success page chunk can be missing right after a deploy; a full
      // page load fetches fresh assets.
      window.location.assign(router.resolve(target).href)
    } finally {
      pending.value = false
    }
  }

  return {
    form,
    days,
    daysLabel,
    incrementDays,
    decrementDays,
    isExtraSelected,
    toggleExtra,
    selectedExtras,
    basePerDay,
    insurancePerDay,
    hasInsurance,
    caution,
    hasCaution,
    rentalTotal,
    lines,
    total,
    fmt,
    carName,
    whatsappLink,
    pending,
    errorMessage,
    submit,
  }
}

export type CarBooking = ReturnType<typeof useCarBooking>

/** "05/11 à 10:00" */
const formatShortDate = (value: string) => {
  const [date = '', time = ''] = value.split('T')
  const [, month = '', day = ''] = date.split('-')
  return `${day}/${month} à ${time.slice(0, 5)}`
}
