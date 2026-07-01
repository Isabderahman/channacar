<script setup lang="ts">
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormField from '~/components/forms/FormField.vue'
import FormSelect from '~/components/forms/FormSelect.vue'
import FormTextarea from '~/components/forms/FormTextarea.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Car, Extra, PickupLocation, Reservation, ResourceResponse } from '~/types/entities'
import { formatCurrency, toNumber } from '~/utils/formatters'

const props = defineProps<{
  car: Car
  extras: Extra[]
  locations: PickupLocation[]
}>()

const route = useRoute()
const router = useRouter()
const { publicApi } = useApi()

const pending = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const carName = computed(() => `${props.car.brand} ${props.car.name}`)

const form = reactive({
  client: {
    driver_license: '',
    email: '',
    full_name: '',
    phone: '',
    whatsapp: '',
  },
  dropoff_date: '',
  dropoff_location_id: '',
  dropoff_time: '10:00',
  extra_ids: [] as Array<number>,
  apply_insurance: false,
  notes: '',
  pickup_date: '',
  pickup_location_id: '',
  pickup_time: '10:00',
})

const locationOptions = computed(() =>
  props.locations.map((location) => ({
    label: location.name,
    value: location.id,
  })),
)

watch(
  () => form.pickup_location_id,
  (value) => {
    if (!form.dropoff_location_id && value) {
      form.dropoff_location_id = value
    }
  },
)

// --- Pricing (client-side estimate; the backend applies seasonal rates) ---
const fmt = (value: number) => formatCurrency(value, 'MAD', 'fr-MA')

const basePerDay = computed(() => toNumber(props.car.base_price_per_day))
const insurancePerDay = computed(() => toNumber(props.car.insurance_price_per_day))
const hasInsurance = computed(() => insurancePerDay.value > 0)
const caution = computed(() => toNumber(props.car.caution))
const hasCaution = computed(() => caution.value > 0)

const rentalDays = computed(() => {
  if (!form.pickup_date || !form.dropoff_date) {
    return 1
  }

  const start = new Date(`${form.pickup_date}T${form.pickup_time || '10:00'}`)
  const end = new Date(`${form.dropoff_date}T${form.dropoff_time || '10:00'}`)
  const ms = end.getTime() - start.getTime()

  if (!Number.isFinite(ms) || ms <= 0) {
    return 1
  }

  return Math.max(1, Math.ceil(ms / 86_400_000))
})

// Extra.icon is a free-form string from the API; BaseIcon only renders known
// names and ignores the rest, so resolving to `any` keeps types happy.
const resolveIcon = (value?: string | null): any => value || 'plus'

const isExtraSelected = (id: number) => form.extra_ids.includes(id)

const toggleExtra = (id: number) => {
  const index = form.extra_ids.indexOf(id)
  if (index >= 0) {
    form.extra_ids.splice(index, 1)
  } else {
    form.extra_ids.push(id)
  }
}

const selectedExtras = computed(() =>
  props.extras.filter((extra) => form.extra_ids.includes(extra.id)),
)

const baseTotal = computed(() => basePerDay.value * rentalDays.value)
const extrasTotal = computed(
  () => selectedExtras.value.reduce((sum, extra) => sum + toNumber(extra.price_per_day), 0) * rentalDays.value,
)
const insuranceTotal = computed(() =>
  form.apply_insurance && hasInsurance.value ? insurancePerDay.value * rentalDays.value : 0,
)

// Frais de Livraison/Reprise: pickup (livraison) fee + dropoff (reprise) fee,
// each based on its city (Marrakech = 0). Both apply, even for the same city.
const locationFee = (id: number | string) =>
  toNumber(props.locations.find((entry) => String(entry.id) === String(id))?.delivery_fee)

const deliveryTotal = computed(
  () => locationFee(form.pickup_location_id) + locationFee(form.dropoff_location_id),
)

const grandTotal = computed(
  () => baseTotal.value + extrasTotal.value + insuranceTotal.value + deliveryTotal.value,
)

const submit = async () => {
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  let reservation: Reservation
  try {
    const response = await publicApi<ResourceResponse<Reservation>>('/public/reservations', {
      method: 'POST',
      body: {
        car_id: props.car.id,
        pickup_location_id: Number(form.pickup_location_id),
        dropoff_location_id: Number(form.dropoff_location_id),
        pickup_date: form.pickup_date,
        pickup_time: form.pickup_time,
        dropoff_date: form.dropoff_date,
        dropoff_time: form.dropoff_time,
        notes: form.notes || null,
        extra_ids: form.extra_ids,
        apply_insurance: form.apply_insurance,
        client: {
          full_name: form.client.full_name,
          phone: form.client.phone,
          whatsapp: form.client.whatsapp || null,
          email: form.client.email || null,
          driver_license: form.client.driver_license,
        },
      },
    })
    reservation = response.data
  } catch (error) {
    // ofetch (FetchError) exposes the parsed JSON body on `error.data`; Laravel
    // validation errors carry the human-readable reason in `message`.
    const apiMessage = (error as { data?: { message?: string } })?.data?.message
    errorMessage.value =
      apiMessage ||
      'La réservation n’a pas pu être envoyée. Vérifiez les dates et les options sélectionnées.'
    pending.value = false
    return
  }

  // The reservation is created at this point. Any failure below is only about
  // reaching the confirmation page — never surface it as a booking failure,
  // or the user may retry and create a duplicate reservation.
  successMessage.value = `Réservation ${reservation.reservation_number} créée avec succès.`

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
    // Client-side navigation needs the success page's JS chunk, which may be
    // missing after a new deploy ("Failed to fetch dynamically imported
    // module"). Fall back to a full page load so fresh assets are fetched.
    window.location.assign(router.resolve(target).href)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <form class="grid gap-6 lg:grid-cols-[1.5fr_1fr] lg:items-start" @submit.prevent="submit">
    <!-- Left: form + options -->
    <div class="space-y-8">
      <section>
        <h3 class="font-heading text-[1.05rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">
          Vos informations
        </h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <FormField v-model="form.client.full_name" name="full_name" label="Nom complet" />
          <FormField v-model="form.client.phone" name="phone" label="Téléphone" type="tel" />
          <FormField v-model="form.client.whatsapp" name="whatsapp" label="WhatsApp" type="tel" />
          <FormField v-model="form.client.email" name="email" label="E-mail" type="email" />
          <FormField
            v-model="form.client.driver_license"
            name="driver_license"
            label="Permis de conduire"
          />
        </div>
      </section>

      <section>
        <h3 class="font-heading text-[1.05rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">
          Dates &amp; lieux
        </h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <FormSelect
            v-model="form.pickup_location_id"
            name="pickup_location_id"
            label="Lieu de prise en charge"
            :options="locationOptions"
          />
          <FormSelect
            v-model="form.dropoff_location_id"
            name="dropoff_location_id"
            label="Lieu de restitution"
            :options="locationOptions"
          />
          <FormField v-model="form.pickup_date" name="pickup_date" label="Date de départ" type="date" />
          <FormField v-model="form.pickup_time" name="pickup_time" label="Heure de départ" type="time" />
          <FormField v-model="form.dropoff_date" name="dropoff_date" label="Date de retour" type="date" />
          <FormField v-model="form.dropoff_time" name="dropoff_time" label="Heure de retour" type="time" />
        </div>
      </section>

      <section v-if="extras.length">
        <h3 class="font-heading text-[1.05rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">
          Vos options
        </h3>
        <p class="mt-1 text-[0.86rem] text-[var(--text-subtle)]">Facturées par jour de location.</p>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
          <button
            v-for="extra in extras"
            :key="extra.id"
            type="button"
            class="group flex flex-col items-center rounded-[18px] border-2 p-3 text-center transition duration-300 ease-out"
            :class="isExtraSelected(extra.id)
              ? 'border-[var(--primary)] bg-[var(--primary-soft)]'
              : 'border-[var(--surface-border)] bg-[var(--surface-2)] hover:border-[var(--primary-border)]'"
            :aria-pressed="isExtraSelected(extra.id)"
            @click="toggleExtra(extra.id)"
          >
            <span
              class="grid size-11 place-items-center rounded-full bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)]"
            >
              <BaseIcon :name="resolveIcon(extra.icon)" :size="20" />
            </span>
            <span class="mt-2 line-clamp-2 text-[0.82rem] font-medium text-[var(--text-strong)]">
              {{ extra.name }}
            </span>
            <span class="mt-0.5 text-[0.82rem] font-bold text-[var(--text-strong)]">
              {{ fmt(toNumber(extra.price_per_day)) }}/j
            </span>
            <span
              class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[0.7rem] font-semibold"
              :class="isExtraSelected(extra.id)
                ? 'bg-[var(--primary)] text-white'
                : 'bg-[var(--surface-3)] text-[var(--text-muted)]'"
            >
              <BaseIcon :name="isExtraSelected(extra.id) ? 'check-circle' : 'plus'" :size="13" />
              {{ isExtraSelected(extra.id) ? 'AJOUTÉ' : 'AJOUTER' }}
            </span>
          </button>
        </div>
      </section>

      <section v-if="hasInsurance">
        <h3 class="font-heading text-[1.05rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">
          Protection
        </h3>
        <button
          type="button"
          class="mt-4 flex w-full items-center gap-4 rounded-[18px] border-2 p-4 text-left transition duration-300 ease-out"
          :class="form.apply_insurance
            ? 'border-[var(--primary)] bg-[var(--primary-soft)]'
            : 'border-[var(--surface-border)] bg-[var(--surface-2)] hover:border-[var(--primary-border)]'"
          :aria-pressed="form.apply_insurance"
          @click="form.apply_insurance = !form.apply_insurance"
        >
          <span
            class="grid size-12 shrink-0 place-items-center rounded-full bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)]"
          >
            <BaseIcon name="shield" :size="22" />
          </span>
          <div class="min-w-0 flex-1">
            <p class="font-heading text-[1rem] text-[var(--text-strong)]">Assurance complémentaire</p>
            <p class="mt-0.5 text-[0.84rem] leading-5 text-[var(--text-muted)]">
              Protection optionnelle, facturée {{ fmt(insurancePerDay) }} par jour.
            </p>
          </div>
          <span
            class="inline-flex shrink-0 items-center gap-1 rounded-full px-3 py-1.5 text-[0.74rem] font-semibold"
            :class="form.apply_insurance ? 'bg-[var(--primary)] text-white' : 'bg-[var(--surface-3)] text-[var(--text-muted)]'"
          >
            <BaseIcon :name="form.apply_insurance ? 'check-circle' : 'plus'" :size="14" />
            {{ form.apply_insurance ? 'AJOUTÉE' : 'AJOUTER' }}
          </span>
        </button>
      </section>

      <FormTextarea
        v-model="form.notes"
        name="notes"
        label="Notes (optionnel)"
        placeholder="Détails de vol, instructions de prise en charge, ou toute information utile."
      />
    </div>

    <!-- Right: live summary -->
    <aside class="lg:sticky lg:top-28">
      <div class="rounded-[24px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-5 shadow-[var(--shadow-1)]">
        <p class="text-[0.74rem] uppercase tracking-[0.2em] text-[var(--text-subtle)]">Récapitulatif</p>
        <h3 class="mt-1 font-heading text-[1.3rem] text-[var(--text-strong)]">{{ carName }}</h3>
        <p class="mt-1 text-[0.86rem] text-[var(--text-muted)]">
          {{ rentalDays }} jour{{ rentalDays > 1 ? 's' : '' }} de location
        </p>

        <div class="mt-4 space-y-2 border-t border-[var(--surface-border)] pt-4 text-[0.9rem]">
          <div class="flex items-center justify-between">
            <span class="text-[var(--text-muted)]">Location ({{ fmt(basePerDay) }}/j)</span>
            <span class="font-semibold text-[var(--text-strong)]">{{ fmt(baseTotal) }}</span>
          </div>

          <div
            v-for="extra in selectedExtras"
            :key="extra.id"
            class="flex items-center justify-between text-[var(--text-muted)]"
          >
            <span class="truncate pr-2">{{ extra.name }}</span>
            <span>{{ fmt(toNumber(extra.price_per_day) * rentalDays) }}</span>
          </div>

          <div v-if="insuranceTotal > 0" class="flex items-center justify-between text-[var(--text-muted)]">
            <span>Assurance</span>
            <span>{{ fmt(insuranceTotal) }}</span>
          </div>

          <div v-if="deliveryTotal > 0" class="flex items-center justify-between text-[var(--text-muted)]">
            <span>Frais de livraison</span>
            <span>{{ fmt(deliveryTotal) }}</span>
          </div>
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-[var(--surface-border)] pt-4">
          <span class="font-heading text-[1.05rem] text-[var(--text-strong)]">Total</span>
          <span class="font-heading text-[1.5rem] text-[var(--primary)]">{{ fmt(grandTotal) }}</span>
        </div>

        <div
          v-if="hasCaution"
          class="mt-3 flex items-center justify-between rounded-[12px] border border-[var(--surface-border)] bg-[var(--surface-3)] px-3 py-2.5 text-[0.86rem]"
        >
          <span class="flex items-center gap-1.5 text-[var(--text-muted)]">
            <BaseIcon name="shield" :size="15" />
            Caution (remboursable)
          </span>
          <span class="font-semibold text-[var(--text-strong)]">{{ fmt(caution) }}</span>
        </div>

        <p class="mt-2 text-[0.74rem] leading-5 text-[var(--text-subtle)]">
          Tarif indicatif — ajusté selon la saison et confirmé par notre équipe.<template v-if="hasCaution"> La caution est une garantie remboursable, non incluse dans le total.</template>
        </p>

        <StateNotice
          v-if="errorMessage"
          class="mt-4"
          title="Réservation échouée"
          :message="errorMessage"
          tone="error"
        />

        <BaseButton type="submit" full-width class="mt-4" :disabled="pending">
          <BaseIcon name="check-circle" :size="18" />
          <span>{{ pending ? 'Envoi...' : 'Réserver maintenant' }}</span>
        </BaseButton>
      </div>
    </aside>
  </form>
</template>
