<script setup lang="ts">
import AdminReservationForm from '~/components/admin/AdminReservationForm.vue'
import type { Reservation } from '~/types/entities'

definePageMeta({
  layout: 'admin',
})

const route = useRoute()

// The planning board can deep-link here with a car and a day already chosen
// (?car=12&date=2026-08-27).
const presetCarId = computed(() => {
  const value = Number(route.query.car)

  return Number.isFinite(value) && value > 0 ? value : null
})

const presetPickupDate = computed(() => {
  const value = route.query.date

  return typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value) ? value : null
})

const onCreated = async (reservation: Reservation) => {
  await navigateTo(`/admin/reservations/${reservation.id}`)
}
</script>

<template>
  <div>
    <AdminPageIntro
      title="New Reservation"
      description="Create a reservation on behalf of a client for phone, walk-in, or web bookings. Pricing is computed by the backend from the car, season, and selected extras."
      eyebrow="Booking operations"
    >
      <BaseButton to="/admin/reservations" variant="secondary">
        <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
        <span>Back to reservations</span>
      </BaseButton>
    </AdminPageIntro>

    <AppPanel padding="lg">
      <div class="flex items-start justify-between gap-4 border-b border-line pb-5">
        <div>
          <h3 class="text-[22px] font-medium tracking-[-0.02em]">Reservation details</h3>
          <p class="mt-1.5 text-[14px] leading-6 text-ink-2">
            The same pricing rules used by the public booking flow are applied here. A car can hold several reservations, even on overlapping dates.
          </p>
        </div>
        <BaseIcon name="plus" :size="18" class="text-accent" />
      </div>

      <AdminReservationForm
        class="mt-6"
        :preset-car-id="presetCarId"
        :preset-pickup-date="presetPickupDate"
        @created="onCreated"
      />
    </AppPanel>
  </div>
</template>
