<script setup lang="ts">
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

interface Props {
  featuredCount?: number
}

withDefaults(defineProps<Props>(), {
  featuredCount: 0,
})
</script>

<template>
  <section class="section-shell pb-10 pt-4 md:pb-14">
    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
      <div class="rounded-[32px] border border-[var(--surface-border)] bg-[radial-gradient(circle_at_top_left,color-mix(in_srgb,var(--primary)_24%,transparent),transparent_36%),linear-gradient(180deg,var(--surface-1),var(--surface-2))] p-7 shadow-[var(--shadow-1)] md:p-10">
        <p class="text-[0.8rem] uppercase tracking-[0.24em] text-[var(--text-subtle)]">
          Marrakech-ready booking flow
        </p>
        <h1 class="mt-4 font-heading text-[2.7rem] leading-[1.02] text-[var(--text-strong)] md:text-[4rem]">
          A stronger car rental frontend for both customers and operators.
        </h1>
        <p class="mt-5 max-w-[58ch] text-[1rem] leading-7 text-[var(--text-muted)]">
          Browse the fleet, reserve from polished detail pages, and manage the business from a
          protected admin workspace built from reusable components.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <BaseButton to="/cars">
            <BaseIcon name="car" :size="18" />
            <span>Browse cars</span>
          </BaseButton>
          <BaseButton to="/admin/dashboard" variant="ghost">
            <BaseIcon name="dashboard" :size="18" />
            <span>Open admin</span>
          </BaseButton>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
          <div class="rounded-[20px] border border-[var(--surface-border)] bg-black/10 p-4">
            <p class="text-[0.76rem] uppercase tracking-[0.2em] text-[var(--text-subtle)]">
              Featured cars
            </p>
            <p class="mt-2 font-heading text-[2rem] text-[var(--text-strong)]">
              {{ featuredCount }}
            </p>
          </div>
          <div class="rounded-[20px] border border-[var(--surface-border)] bg-black/10 p-4">
            <p class="text-[0.76rem] uppercase tracking-[0.2em] text-[var(--text-subtle)]">
              Admin sections
            </p>
            <p class="mt-2 font-heading text-[2rem] text-[var(--text-strong)]">9</p>
          </div>
          <div class="rounded-[20px] border border-[var(--surface-border)] bg-black/10 p-4">
            <p class="text-[0.76rem] uppercase tracking-[0.2em] text-[var(--text-subtle)]">
              Shared building blocks
            </p>
            <p class="mt-2 font-heading text-[2rem] text-[var(--text-strong)]">Reusable</p>
          </div>
        </div>
      </div>

      <div class="grid gap-6">
        <div class="rounded-[32px] border border-[var(--surface-border)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--surface-2)_96%,transparent),color-mix(in_srgb,var(--surface-3)_70%,transparent))] p-6 shadow-[var(--shadow-1)]">
          <img
            src="/images/hero-banner.jpg"
            alt="ChannaCar hero vehicle"
            class="h-[240px] w-full rounded-[24px] object-cover md:h-[300px]"
          >
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-[24px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-5">
            <BaseIcon name="calendar" :size="18" class="text-[var(--primary)]" />
            <p class="mt-3 font-heading text-[1.1rem] text-[var(--text-strong)]">
              Upcoming pickups
            </p>
            <p class="mt-2 text-[0.92rem] leading-6 text-[var(--text-subtle)]">
              The admin dashboard is structured to surface same-day pickup and dropoff pressure.
            </p>
          </div>
          <div class="rounded-[24px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-5">
            <BaseIcon name="image" :size="18" class="text-[var(--primary)]" />
            <p class="mt-3 font-heading text-[1.1rem] text-[var(--text-strong)]">Image control</p>
            <p class="mt-2 text-[0.92rem] leading-6 text-[var(--text-subtle)]">
              Car detail editing includes a reusable image manager for thumbnail and gallery flow.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
