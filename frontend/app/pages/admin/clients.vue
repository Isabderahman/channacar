<script setup lang="ts">
import type { Client, PaginatedResponse, ResourceResponse } from '~/types/entities'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()

const clients = ref<Client[]>([])
const pending = ref(true)
const saving = ref(false)
const selectedId = ref<number | null>(null)
const errorMessage = ref('')
const successMessage = ref('')

const createFormState = () => ({
  driver_license: '',
  email: '',
  full_name: '',
  phone: '',
  whatsapp: '',
})

const form = reactive(createFormState())
const isEditing = computed(() => selectedId.value !== null)

const rows = computed(() =>
  clients.value.map((client) => ({
    driver_license: client.driver_license,
    email: client.email || '—',
    full_name: client.full_name,
    id: client.id,
    phone: client.phone,
  })),
)

const fillForm = (client: Client) => {
  form.driver_license = client.driver_license
  form.email = client.email ?? ''
  form.full_name = client.full_name
  form.phone = client.phone
  form.whatsapp = client.whatsapp ?? ''
  selectedId.value = client.id
}

const resetForm = () => {
  Object.assign(form, createFormState())
  selectedId.value = null
  successMessage.value = ''
}

const loadClients = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PaginatedResponse<Client>>('/clients', {
      query: {
        per_page: 100,
      },
    })

    clients.value = response.data
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Clients could not be loaded.'
  } finally {
    pending.value = false
  }
}

const save = async () => {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const endpoint = isEditing.value ? `/clients/${selectedId.value}` : '/clients'
    const method = isEditing.value ? 'PUT' : 'POST'

    await adminApi<ResourceResponse<Client>>(endpoint, {
      method,
      body: {
        ...form,
        email: form.email || null,
        whatsapp: form.whatsapp || null,
      },
    })

    successMessage.value = isEditing.value ? 'Client updated successfully.' : 'Client created successfully.'
    resetForm()
    await loadClients()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'The client could not be saved.'
  } finally {
    saving.value = false
  }
}

const removeSelected = async () => {
  if (!selectedId.value || !window.confirm('Delete this client?')) {
    return
  }

  saving.value = true
  errorMessage.value = ''

  try {
    await adminApi(`/clients/${selectedId.value}`, {
      method: 'DELETE',
    })

    resetForm()
    await loadClients()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'The client could not be deleted.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void loadClients()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Clients"
      description="Maintain the customer directory and keep driver-licence records tied to reservation history."
      eyebrow="Client directory"
    />

    <StateNotice v-if="errorMessage" title="Client action failed" :message="errorMessage" tone="error" />
    <StateNotice v-else-if="successMessage" title="Saved" :message="successMessage" tone="success" />

    <AdminSplitLayout>
      <template #primary>
        <TableCard
          title="Client records"
          description="Select a client to edit the contact details used across reservations."
          :columns="[
            { key: 'full_name', label: 'Name' },
            { key: 'phone', label: 'Phone' },
            { key: 'email', label: 'Email' },
            { key: 'driver_license', label: 'Driver licence' },
          ]"
          :rows="rows"
          @row-click="(row) => fillForm(clients.find((client) => client.id === row.id)!)"
        />
      </template>

      <template #secondary>
        <AppPanel padding="lg">
          <h3 class="font-heading text-[1.2rem] text-[var(--text-strong)]">
            {{ isEditing ? 'Edit client' : 'Create client' }}
          </h3>

          <form class="mt-6 space-y-4" @submit.prevent="save">
            <FormField v-model="form.full_name" name="full_name" label="Full name" />
            <FormField v-model="form.phone" name="phone" label="Phone" type="tel" />
            <FormField v-model="form.whatsapp" name="whatsapp" label="WhatsApp" type="tel" />
            <FormField v-model="form.email" name="email" label="Email" type="email" />
            <FormField v-model="form.driver_license" name="driver_license" label="Driver licence" />

            <div class="flex flex-wrap gap-3">
              <BaseButton type="submit" :disabled="saving">
                <BaseIcon :name="isEditing ? 'edit' : 'plus'" :size="18" />
                <span>{{ saving ? 'Saving...' : isEditing ? 'Save client' : 'Create client' }}</span>
              </BaseButton>
              <BaseButton type="button" variant="secondary" @click="resetForm">
                <BaseIcon name="refresh" :size="18" />
                <span>Reset</span>
              </BaseButton>
              <BaseButton v-if="isEditing" type="button" variant="ghost" @click="removeSelected">
                <BaseIcon name="trash" :size="18" />
                <span>Delete</span>
              </BaseButton>
            </div>
          </form>
        </AppPanel>
      </template>
    </AdminSplitLayout>

    <StateNotice v-if="pending" class="mt-6" title="Loading clients" message="Fetching the customer directory." />
  </div>
</template>
