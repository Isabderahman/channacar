<script setup lang="ts">
definePageMeta({
  layout: 'admin',
})

const runtimeConfig = useRuntimeConfig()
const { clearSession, token, user } = useAdminAuth()
const toast = useToast()

const clearLocalSession = () => {
  clearSession()
  toast.success('The stored admin token was removed from this browser.', 'Session cleared')
}

const sessionItems = computed(() => [
  { label: 'Name', value: user.value?.name ?? 'Unknown' },
  { label: 'Email', value: user.value?.email ?? 'Unknown' },
  { label: 'Role', value: user.value?.role ?? 'Not provided by API' },
  { label: 'API base', value: runtimeConfig.public.apiBase },
])
</script>

<template>
  <div>
    <AdminPageIntro
      title="Settings"
      description="The backend does not expose an admin-profile update endpoint yet, so this route currently acts as a session and environment overview."
      eyebrow="Admin profile"
    />

    <AdminSplitLayout>
      <template #primary>
        <AppPanel padding="lg">
          <KeyValueList :items="sessionItems" title="Current admin session" />
        </AppPanel>
      </template>

      <template #secondary>
        <AppPanel padding="lg">
          <h3 class="text-[22px] font-medium leading-tight tracking-[-0.02em]">Session tools</h3>
          <p class="mt-1.5 text-[14px] leading-6 text-ink-2">
            Use this page to verify which API base is active and to clear the stored personal access token.
          </p>

          <div class="mt-6 grid gap-1 border-y border-line py-4 sm:grid-cols-[9rem_1fr] sm:gap-6">
            <p class="mono-label pt-0.5">Token present</p>
            <p class="text-[15px] text-ink-2">
              {{ token ? 'Yes, a token is stored in local browser storage.' : 'No active admin token found.' }}
            </p>
          </div>

          <div class="mt-6 flex flex-wrap gap-3">
            <BaseButton type="button" variant="ghost" @click="clearLocalSession">
              <BaseIcon name="logout" :size="18" />
              <span>Clear local session</span>
            </BaseButton>
            <BaseButton to="/admin/dashboard" variant="secondary">
              <BaseIcon name="dashboard" :size="18" />
              <span>Back to dashboard</span>
            </BaseButton>
          </div>
        </AppPanel>
      </template>
    </AdminSplitLayout>
  </div>
</template>
