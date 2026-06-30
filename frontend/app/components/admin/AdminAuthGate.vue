<script setup lang="ts">
import AppPanel from '~/components/data/AppPanel.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormField from '~/components/forms/FormField.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

const credentials = reactive({
  email: '',
  password: '',
})

const pending = ref(false)
const errorMessage = ref('')

const { isAuthenticated, login, ready } = useAdminAuth()

const submit = async () => {
  errorMessage.value = ''
  pending.value = true

  try {
    await login(credentials.email, credentials.password)
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Unable to sign in with the current credentials.'
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <slot v-if="ready && isAuthenticated" />

  <div v-else class="mx-auto flex min-h-screen w-full max-w-[1200px] items-center px-4 py-10">
    <div class="grid w-full gap-6 lg:grid-cols-[1.2fr_0.8fr]">
      <AppPanel padding="lg">
        <p class="text-[0.78rem] uppercase tracking-[0.22em] text-[var(--text-subtle)]">
          Protected admin workspace
        </p>
        <h1 class="mt-4 font-heading text-[2.5rem] leading-tight text-[var(--text-strong)]">
          Operate the fleet, reservations, and customer records from one reusable interface.
        </h1>
        <p class="mt-4 max-w-[56ch] text-[1rem] leading-7 text-[var(--text-muted)]">
          Sign in with your admin API credentials to unlock the dashboard, CRUD flows, reservation
          follow-up, and moderation tools.
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          <div class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
            <BaseIcon name="dashboard" :size="18" class="text-[var(--primary)]" />
            <p class="mt-3 font-heading text-[1rem] text-[var(--text-strong)]">Fleet overview</p>
            <p class="mt-1 text-[0.88rem] leading-6 text-[var(--text-subtle)]">
              Track active, rented, and maintenance vehicles in one place.
            </p>
          </div>
          <div class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
            <BaseIcon name="calendar" :size="18" class="text-[var(--primary)]" />
            <p class="mt-3 font-heading text-[1rem] text-[var(--text-strong)]">Reservation flow</p>
            <p class="mt-1 text-[0.88rem] leading-6 text-[var(--text-subtle)]">
              Review filters, update status, and keep pickup/dropoff timing visible.
            </p>
          </div>
          <div class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
            <BaseIcon name="star" :size="18" class="text-[var(--primary)]" />
            <p class="mt-3 font-heading text-[1rem] text-[var(--text-strong)]">Brand moderation</p>
            <p class="mt-1 text-[0.88rem] leading-6 text-[var(--text-subtle)]">
              Keep testimonials and merchandising details consistent with the storefront.
            </p>
          </div>
        </div>
      </AppPanel>

      <AppPanel padding="lg">
        <h2 class="font-heading text-[1.6rem] text-[var(--text-strong)]">Admin sign in</h2>
        <p class="mt-2 text-[0.95rem] leading-6 text-[var(--text-muted)]">
          This uses the Laravel `/api/login` endpoint through the Nuxt proxy and stores the
          returned token locally in the browser.
        </p>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
          <FormField
            v-model="credentials.email"
            name="email"
            label="Email"
            type="email"
            placeholder="admin@channacar.com"
            autocomplete="email"
          />
          <FormField
            v-model="credentials.password"
            name="password"
            label="Password"
            type="password"
            placeholder="Enter your password"
            autocomplete="current-password"
          />

          <StateNotice
            v-if="errorMessage"
            title="Sign in failed"
            :message="errorMessage"
            tone="error"
          />

          <BaseButton type="submit" :disabled="pending" class="w-full">
            <BaseIcon name="check-circle" :size="18" />
            <span>{{ pending ? 'Signing in...' : 'Unlock admin' }}</span>
          </BaseButton>
        </form>
      </AppPanel>
    </div>
  </div>
</template>
