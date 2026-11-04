<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { CarBooking } from '~/composables/useCarBooking'
import type { Car, Extra, PickupLocation } from '~/types/entities'
import { formatAmount } from '~/utils/car-display'
import { toNumber } from '~/utils/formatters'

// Booking request, ordered for conversion: 01 when/where, 02 options,
// 03 contact details. Summary panel is sticky on desktop; on mobile it sits
// after the form and a fixed bottom bar carries the total + submit.
const props = defineProps<{
  car: Car
  booking: CarBooking
  extras: Extra[]
  locations: PickupLocation[]
  photo: string | null
}>()

const VISIBLE_OPTIONS = 3
const showAllOptions = ref(false)

const { form } = props.booking

const visibleExtras = computed(() =>
  props.extras.filter(
    (extra, index) =>
      showAllOptions.value || index < VISIBLE_OPTIONS || props.booking.isExtraSelected(extra.id),
  ),
)
const hiddenCount = computed(() => props.extras.length - visibleExtras.value.length)

const extraPrice = (extra: Extra) =>
  `${formatAmount(extra.price_per_day)} MAD${extra.billing_type === 'one_time' ? ' · une fois' : ' / jour'}`

// "50 MAD par jour et par option." when every option shares one per-day price.
const optionsNote = computed(() => {
  const prices = new Set(props.extras.map((extra) => `${toNumber(extra.price_per_day)}-${extra.billing_type}`))
  const first = props.extras[0]

  if (prices.size === 1 && first && first.billing_type !== 'one_time') {
    return `${formatAmount(first.price_per_day)} MAD par jour et par option.`
  }

  return 'Prix indiqué pour chaque option, par jour sauf mention contraire.'
})

const nextSteps = [
  'Notre équipe vérifie la disponibilité.',
  'Vous recevez le tarif confirmé par téléphone ou WhatsApp.',
  'Le véhicule vous attend au lieu choisi.',
]
</script>

<template>
  <section id="reserver" class="wrap scroll-mt-6 pt-12 lg:mt-36 lg:pt-0">
    <div v-inview data-anim="rule" class="lg:pt-8 lg:rule-t">
      <div v-inview data-anim="stagger" class="mb-11 flex flex-wrap items-end justify-between gap-6 lg:mb-14">
        <h2 class="display text-[30px] leading-none tracking-[-0.035em] lg:text-[clamp(40px,5vw,64px)] lg:leading-[0.95] lg:tracking-[-0.04em]">
          <span class="lg:hidden">Réservez en 2 minutes.</span>
          <span class="hidden lg:inline">Réservez ce véhicule.</span>
        </h2>
        <p class="hidden max-w-[380px] text-ink-2 lg:block">
          Trois étapes, environ deux minutes. Il s’agit d’une demande : notre équipe confirme la disponibilité et le tarif.
        </p>
      </div>

      <div class="flex flex-wrap items-start gap-11 lg:gap-10 xl:gap-16">
        <form
          id="booking-form"
          v-inview
          data-anim="stagger"
          aria-label="Demande de réservation"
          class="flex min-w-0 flex-[1_1_520px] flex-col gap-11 lg:gap-16 xl:flex-[1_1_640px]"
          @submit.prevent="booking.submit"
        >
          <!-- 01 -->
          <fieldset>
            <legend class="mb-4 flex items-baseline gap-3 text-[19px] font-medium lg:mb-6 lg:gap-5 lg:text-[22px]">
              <span class="font-plex text-[11px] text-accent lg:text-[12px]">01</span>Quand et où ?
            </legend>

            <div class="grid gap-3 sm:grid-cols-2 lg:gap-4">
              <label class="field-label">
                Prise en charge
                <select v-model="form.pickupLocationId" class="field" required>
                  <option v-for="location in locations" :key="location.id" :value="String(location.id)">
                    {{ location.name }}
                  </option>
                </select>
              </label>
              <label class="field-label">
                Restitution
                <select v-model="form.dropoffLocationId" class="field">
                  <option value="">Même lieu que la prise en charge</option>
                  <option v-for="location in locations" :key="location.id" :value="String(location.id)">
                    {{ location.name }}
                  </option>
                </select>
              </label>
              <label class="field-label">
                Départ
                <input v-model="form.pickupAt" class="field" type="datetime-local" required>
              </label>
              <label class="field-label">
                Retour
                <input v-model="form.dropoffAt" class="field" type="datetime-local" :min="form.pickupAt" required>
              </label>
            </div>

            <div class="mt-3 flex items-center justify-between gap-4 border border-line-strong py-1 ps-3 lg:mt-4 lg:py-3 lg:ps-4">
              <span class="mono-label text-[10px] lg:text-[12px]">Durée</span>
              <div class="flex items-center">
                <button
                  type="button"
                  class="flex size-11 items-center justify-center text-[20px] transition-colors hover:text-accent disabled:opacity-40"
                  aria-label="Retirer un jour"
                  :disabled="booking.days.value <= 1"
                  @click="booking.decrementDays"
                >
                  −
                </button>
                <span class="min-w-[72px] text-center lg:min-w-[88px] lg:text-[17px]" aria-live="polite">
                  {{ booking.daysLabel.value }}
                </span>
                <button
                  type="button"
                  class="flex size-11 items-center justify-center text-[20px] transition-colors hover:text-accent"
                  aria-label="Ajouter un jour"
                  @click="booking.incrementDays"
                >
                  +
                </button>
              </div>
            </div>
          </fieldset>

          <!-- 02 -->
          <fieldset v-if="extras.length || booking.hasInsurance.value">
            <legend class="mb-1 flex flex-wrap items-baseline gap-x-3 text-[19px] font-medium lg:mb-2 lg:gap-x-5 lg:text-[22px]">
              <span class="font-plex text-[11px] text-accent lg:text-[12px]">02</span>Options
              <span class="text-[13px] font-normal text-muted lg:text-[14px]">facultatif</span>
            </legend>

            <template v-if="extras.length">
              <p class="mb-3 text-[13px] text-muted lg:mb-5 lg:text-[14px]">{{ optionsNote }}</p>

              <ul class="border-t border-line">
                <li
                  v-for="extra in visibleExtras"
                  :key="extra.id"
                  class="flex items-center justify-between gap-3 border-b border-line py-1.5 lg:gap-4 lg:py-2.5"
                >
                  <span class="min-w-0">
                    <span class="block lg:text-[16px]">{{ extra.name }}</span>
                    <span class="block font-plex text-[11px] text-muted">{{ extraPrice(extra) }}</span>
                  </span>
                  <button
                    type="button"
                    class="chip min-w-[92px] shrink-0 px-3 text-[11px] lg:min-w-[104px] lg:px-3.5 lg:text-[12px]"
                    :aria-pressed="booking.isExtraSelected(extra.id) ? 'true' : 'false'"
                    :aria-label="`${booking.isExtraSelected(extra.id) ? 'Retirer' : 'Ajouter'} : ${extra.name}`"
                    @click="booking.toggleExtra(extra.id)"
                  >
                    {{ booking.isExtraSelected(extra.id) ? '✓ Ajouté' : 'Ajouter' }}
                  </button>
                </li>
              </ul>

              <button
                v-if="extras.length > VISIBLE_OPTIONS"
                type="button"
                class="mt-1 min-h-11 text-[14px] underline underline-offset-4 transition-colors hover:text-accent lg:mt-2 lg:text-[15px]"
                :aria-expanded="showAllOptions ? 'true' : 'false'"
                @click="showAllOptions = !showAllOptions"
              >
                {{ showAllOptions ? 'Afficher moins d’options' : `Voir ${hiddenCount} autre${hiddenCount > 1 ? 's' : ''} option${hiddenCount > 1 ? 's' : ''}` }}
              </button>
            </template>

            <div
              v-if="booking.hasInsurance.value"
              class="mt-3 flex flex-wrap items-center justify-between gap-3 border border-line-strong p-3.5 lg:mt-6 lg:gap-5 lg:px-6 lg:py-5"
            >
              <div>
                <p class="font-medium lg:mb-1 lg:text-[17px]">Assurance complémentaire</p>
                <p class="text-[13px] text-muted lg:text-[14px]">
                  Protection optionnelle, {{ booking.fmt(booking.insurancePerDay.value) }} MAD par jour.
                </p>
              </div>
              <button
                type="button"
                class="chip min-w-[92px] px-3 text-[11px] lg:min-w-[104px] lg:px-3.5 lg:text-[12px]"
                :aria-pressed="form.insurance ? 'true' : 'false'"
                aria-label="Assurance complémentaire"
                @click="form.insurance = !form.insurance"
              >
                {{ form.insurance ? '✓ Ajoutée' : 'Ajouter' }}
              </button>
            </div>
          </fieldset>

          <!-- 03 -->
          <fieldset>
            <legend class="mb-1 flex items-baseline gap-3 text-[19px] font-medium lg:mb-2 lg:gap-5 lg:text-[22px]">
              <span class="font-plex text-[11px] text-accent lg:text-[12px]">{{ extras.length || booking.hasInsurance.value ? '03' : '02' }}</span>Vos coordonnées
            </legend>
            <p class="mb-4 text-[13px] text-muted lg:mb-5 lg:text-[14px]">Pour vous envoyer la confirmation.</p>

            <div class="grid gap-3 sm:grid-cols-2 lg:gap-4">
              <label class="field-label">
                Nom complet
                <input v-model="form.fullName" class="field" type="text" autocomplete="name" required>
              </label>
              <label class="field-label">
                Téléphone
                <input v-model="form.phone" class="field" type="tel" autocomplete="tel" placeholder="+212 …" required>
              </label>
              <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[14px] sm:col-span-2 lg:text-[15px]">
                <input v-model="form.whatsappReachable" type="checkbox" class="m-0 size-5 shrink-0 accent-accent">
                Ce numéro est joignable sur WhatsApp
              </label>
              <label class="field-label">
                <span>E-mail <span class="normal-case tracking-normal">(facultatif)</span></span>
                <input v-model="form.email" class="field" type="email" autocomplete="email">
              </label>
              <label class="field-label">
                N° de permis de conduire
                <input v-model="form.driverLicense" class="field" type="text" required>
              </label>
              <label class="field-label sm:col-span-2">
                <span>Note <span class="normal-case tracking-normal">(facultatif) — numéro de vol, demande particulière</span></span>
                <textarea v-model="form.notes" class="field" rows="3" />
              </label>
            </div>
          </fieldset>
        </form>

        <!-- Summary -->
        <aside
          v-inview
          data-anim="rise"
          aria-label="Récapitulatif"
          class="w-full min-w-0 border border-line lg:sticky lg:top-6 lg:w-auto lg:min-w-[300px] lg:flex-[0_1_340px] lg:border-line-strong xl:flex-[0_1_400px]"
        >
          <div class="flex items-center gap-4 border-b border-line p-4 lg:p-6">
            <div class="photo-frame hidden h-16 w-24 shrink-0 overflow-hidden lg:block">
              <img v-if="photo" :src="photo" alt="" class="size-full object-cover">
            </div>
            <div class="min-w-0">
              <p class="mono-label mb-0.5 text-[10px] lg:text-[11px]">Récapitulatif</p>
              <p class="text-[16px] font-medium lg:text-[18px]">{{ booking.carName.value }}</p>
            </div>
          </div>

          <div class="flex flex-col gap-2 px-4 py-4 text-[14px] lg:gap-3 lg:px-6 lg:py-5 lg:text-[15px]">
            <div class="flex justify-between gap-3">
              <span class="text-ink-2">Location · {{ booking.daysLabel.value }}</span>
              <span class="whitespace-nowrap">{{ booking.fmt(booking.rentalTotal.value) }} MAD</span>
            </div>
            <div v-for="line in booking.lines.value" :key="line.key" class="flex justify-between gap-3">
              <span class="text-ink-2">{{ line.label }}</span>
              <span class="whitespace-nowrap">{{ booking.fmt(line.amount) }} MAD</span>
            </div>
          </div>

          <div class="flex items-baseline justify-between border-t border-ink px-4 py-4 lg:px-6 lg:py-5">
            <span class="text-[15px]">Total estimé</span>
            <span>
              <span class="text-[28px] font-medium tracking-[-0.03em] lg:text-[36px]">{{ booking.fmt(booking.total.value) }}</span>
              <span class="font-plex text-[12px] text-muted"> MAD</span>
            </span>
          </div>

          <div class="flex flex-col gap-1.5 px-4 pb-4 font-plex text-[11px] text-muted lg:px-6 lg:pb-5 lg:text-[12px]">
            <div v-if="booking.hasCaution.value" class="flex justify-between gap-3">
              <span>Caution remboursable, non incluse</span>
              <span class="whitespace-nowrap">{{ booking.fmt(booking.caution.value) }} MAD</span>
            </div>
            <p>Tarif indicatif, ajusté selon la saison et confirmé par notre équipe.</p>
          </div>

          <p
            v-if="booking.errorMessage.value"
            role="alert"
            class="mx-4 mb-4 border border-[#E5484D] px-3.5 py-3 text-[14px] text-ink lg:mx-6 lg:mb-5"
          >
            {{ booking.errorMessage.value }}
          </p>

          <button
            type="submit"
            form="booking-form"
            class="btn btn-primary hidden min-h-16 w-full px-6 text-[17px] lg:flex"
            :disabled="booking.pending.value"
          >
            {{ booking.pending.value ? 'Envoi…' : 'Envoyer ma demande' }}
            <SiteIcon name="arrow-right" :size="20" />
          </button>

          <a
            :href="booking.whatsappLink.value"
            target="_blank"
            rel="noopener"
            class="flex min-h-[52px] items-center justify-center gap-2.5 border-t border-line text-[15px] font-medium transition-colors hover:text-accent lg:min-h-14"
          >
            <SiteIcon name="whatsapp" />
            <span class="lg:hidden">Réserver sur WhatsApp</span>
            <span class="hidden lg:inline">Ou réserver directement sur WhatsApp</span>
          </a>

          <div class="border-t border-line px-4 py-4 lg:px-6 lg:py-5">
            <p class="mono-label mb-3">Et ensuite ?</p>
            <ol class="flex flex-col gap-2 text-[14px] text-ink-2">
              <li v-for="(step, index) in nextSteps" :key="step" class="flex gap-2.5">
                <span class="font-plex text-[12px] leading-[1.75] text-accent">{{ index + 1 }}</span>{{ step }}
              </li>
            </ol>
          </div>
        </aside>
      </div>
    </div>

    <!-- Mobile: fixed total + submit -->
    <div
      class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-between gap-3 border-t border-line-strong bg-bg px-5 pb-[calc(16px+env(safe-area-inset-bottom))] pt-3 lg:hidden"
    >
      <div>
        <p class="mono-label text-[10px]">{{ booking.daysLabel.value }} · estimé</p>
        <p aria-live="polite">
          <span class="text-[24px] font-medium tracking-[-0.02em]">{{ booking.fmt(booking.total.value) }}</span>
          <span class="font-plex text-[11px] text-muted"> MAD</span>
        </p>
      </div>
      <button
        type="submit"
        form="booking-form"
        class="btn btn-primary min-h-[52px] shrink-0 justify-center px-5 text-[16px]"
        :disabled="booking.pending.value"
      >
        {{ booking.pending.value ? 'Envoi…' : 'Envoyer ma demande' }}
      </button>
    </div>
  </section>
</template>
