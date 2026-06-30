<script setup lang="ts">
definePageMeta({
  layout: 'admin',
})

const runtimeConfig = useRuntimeConfig()
const { clearSession, token, user } = useAdminAuth()

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
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">Session tools</h3>
          <p class="mt-2 text-[0.92rem] leading-6 text-[var(--text-muted)]">
            Use this page to verify which API base is active and to clear the stored personal access token.
          </p>

          <div class="mt-6 rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
            <p class="text-[0.76rem] uppercase tracking-[0.18em] text-[var(--text-subtle)]">Token present</p>
            <p class="mt-3 text-[0.95rem] text-[var(--text-muted)]">
              {{ token ? 'Yes, a token is stored in local browser storage.' : 'No active admin token found.' }}
            </p>
          </div>

          <div class="mt-6 flex flex-wrap gap-3">
            <BaseButton type="button" variant="ghost" @click="clearSession">
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
