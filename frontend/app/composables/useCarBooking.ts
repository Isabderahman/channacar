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
import { whatsappHref, type WhatsappTranslate } from '~/utils/whatsapp'

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
  const localePath = useLocalePath()
  const { publicApi } = useApi()
  const { t, locale } = useI18n()
  // resolveApiErrorMessage only has French and English wording of its own.
  const apiLocale = computed(() => (locale.value === 'fr' ? 'fr' : 'en'))
  const toast = useToast({
    locale: apiLocale.value,
    errorTitle: t('car.booking.toast.errorTitle'),
    successTitle: t('car.booking.toast.successTitle'),
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
  const daysLabel = computed(() => t('site.fleet.days', days.value))

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
      label: extra.billing_type === 'one_time' ? t('car.booking.lineOnce', { name: extra.name }) : extra.name,
      amount: extraLineTotal(extra, days.value),
    })),
    ...(insuranceTotal.value > 0
      ? [{ key: 'insurance', label: t('car.booking.insurance'), amount: insuranceTotal.value }]
      : []),
    ...(deliveryTotal.value > 0
      ? [{ key: 'delivery', label: t('car.booking.delivery'), amount: deliveryTotal.value }]
      : []),
  ])

  const total = computed(
    () => rentalTotal.value + lines.value.reduce((sum, line) => sum + line.amount, 0),
  )

  const fmt = (value: number) => formatAmount(value)

  // ── WhatsApp ───────────────────────────────────────────────────────────
  const carName = computed(() => carFullName(car.value))

  const whatsappLink = computed(() => {
    // French elides the article before a vowel ("l’Audi", "la Porsche");
    // the other languages use the same wording for both keys.
    const elided = /^[aeiouyhâàéèêîôû]/i.test(car.value.brand)
    const params = {
      car: t(elided ? 'whatsapp.carNameElided' : 'whatsapp.carName', { name: carName.value }),
      days: daysLabel.value,
    }

    return whatsappHref(
      form.pickupAt && form.dropoffAt
        ? t('whatsapp.bookingDates', {
            ...params,
            from: formatShortDate(form.pickupAt, t),
            to: formatShortDate(form.dropoffAt, t),
          })
        : t('whatsapp.booking', params),
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
        locale: apiLocale.value,
        fallback: t('car.booking.toast.error'),
      })
      toast.error(errorMessage.value, t('car.booking.toast.errorTitle'))
      pending.value = false
      return
    }

    // The reservation exists at this point. Anything failing below is only
    // about reaching the confirmation page — never report it as a booking
    // failure, or the visitor may retry and create a duplicate.
    toast.success(
      t('car.booking.toast.success', { number: reservation.reservation_number }),
      t('car.booking.toast.successTitle'),
    )

    // Stay in the visitor's language (`/en/reservation/success`, …).
    const target = {
      path: localePath('/reservation/success'),
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

/** "05/11 à 10:00" (the connecting word follows the language). */
const formatShortDate = (value: string, t: WhatsappTranslate) => {
  const [date = '', time = ''] = value.split('T')
  const [, month = '', day = ''] = date.split('-')
  return t('whatsapp.dateTime', { date: `${day}/${month}`, time: time.slice(0, 5) })
}
