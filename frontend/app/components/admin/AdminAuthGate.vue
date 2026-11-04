<script setup lang="ts">
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormField from '~/components/forms/FormField.vue'
import { resolveApiErrorMessage } from '~/utils/api-error'

const credentials = reactive({
  email: '',
  password: '',
})

const pending = ref(false)
const errorMessage = ref('')

const { isAuthenticated, login, ready } = useAdminAuth()

const features = [
  { title: 'Fleet overview', description: 'Track active, rented, and maintenance vehicles in one place.' },
  { title: 'Reservation flow', description: 'Review filters, update status, and keep pickup/dropoff timing visible.' },
  { title: 'Brand moderation', description: 'Keep testimonials and merchandising details consistent with the storefront.' },
]
const toast = useToast()

const submit = async () => {
  errorMessage.value = ''
  pending.value = true

  try {
    await login(credentials.email, credentials.password)
    toast.success(`Signed in as ${credentials.email}.`, 'Welcome back')
  } catch (error) {
    // Kept inline as well: the reason belongs next to the form the user is
    // still looking at, after the toast has faded.
    errorMessage.value = resolveApiErrorMessage(error, {
      fallback: 'Unable to sign in with the current credentials.',
    })
    toast.error(errorMessage.value, 'Sign-in failed')
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <slot v-if="ready && isAuthenticated" />

  <div v-else class="mx-auto flex min-h-screen w-full max-w-[1360px] items-center px-5 py-10 lg:px-10">
    <div class="grid w-full gap-12 lg:grid-cols-[1.25fr_0.75fr] lg:items-end lg:gap-16">
      <div>
        <p class="text-[22px] font-semibold tracking-[-0.02em]">Chanaa<span class="text-accent">Car</span></p>
        <p class="mono-label mt-10">Protected admin workspace</p>
        <h1 class="mt-5 text-[40px] font-medium leading-[0.95] tracking-[-0.045em] md:text-[clamp(44px,5vw,72px)]">
          Operate the fleet, reservations, and customer records from one reusable interface.
        </h1>
        <p class="mt-6 max-w-[56ch] text-[16px] leading-7 text-ink-2">
          Sign in with your admin API credentials to unlock the dashboard, CRUD flows, reservation
          follow-up, and moderation tools.
        </p>

        <ol class="mt-10 grid border-t border-line md:grid-cols-3">
          <li
            v-for="(feature, index) in features"
            :key="feature.title"
            class="border-b border-line py-5 md:border-b-0 md:border-r md:px-6 md:pt-6 md:first:ps-0 md:last:border-r-0"
          >
            <p class="font-plex text-[12px] text-accent">{{ String(index + 1).padStart(2, '0') }}</p>
            <p class="mt-3 text-[17px] font-medium md:mt-8">{{ feature.title }}</p>
            <p class="mt-1.5 text-[14px] leading-6 text-muted">{{ feature.description }}</p>
          </li>
        </ol>
      </div>

      <div class="border border-line-strong p-6 md:p-8">
        <h2 class="text-[28px] font-medium tracking-[-0.03em]">Admin sign in</h2>
        <p class="mt-2 text-[14px] leading-6 text-ink-2">
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

          <button type="submit" class="btn btn-primary min-h-14 w-full" :disabled="pending">
            {{ pending ? 'Signing in...' : 'Unlock admin' }}
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 12h16M14 6l6 6-6 6" /></svg>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
