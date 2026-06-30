<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import AppModal from '~/components/data/AppModal.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormCheckbox from '~/components/forms/FormCheckbox.vue'
import FormField from '~/components/forms/FormField.vue'
import FormSelect from '~/components/forms/FormSelect.vue'
import FormTextarea from '~/components/forms/FormTextarea.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Reservation, ResourceResponse } from '~/types/entities'
import { formatCurrency } from '~/utils/formatters'

const props = defineProps<{
  open: boolean
  reservation: Reservation | null
}>()

const emit = defineEmits<{
  close: []
  updated: [reservation: Reservation]
}>()

const { adminApi, mediaUrl } = useApi()

// Surface backend messages (validation / 500) when available, not just "[POST] 500".
const extractError = (error: unknown, fallback: string) => {
  const e = error as { data?: { message?: string }; message?: string }
  return e?.data?.message || e?.message || fallback
}

const saving = ref(false)
const generating = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Each document is recto (front) + verso (back).
const files = reactive<Record<string, File | null>>({
  driver_license: null,
  driver_license_verso: null,
  identity: null,
  identity_verso: null,
})

const day = (value?: string | null) => (value ? String(value).slice(0, 10) : '')

const createClient = () => ({
  full_name: '',
  birth_date: '',
  birth_place: '',
  address: '',
  phone: '',
  whatsapp: '',
  email: '',
  driver_license: '',
  license_issued_at: '',
  license_issued_place: '',
  passport_number: '',
  cin_number: '',
})

const createSecondDriver = () => ({
  full_name: '',
  birth_date: '',
  birth_place: '',
  address: '',
  phone: '',
  driver_license: '',
  license_issued_at: '',
  license_issued_place: '',
  passport_number: '',
  cin_number: '',
})

const createDetails = () => ({
  prolongation_date: '',
  prolongation_time: '',
  prolongation_location: '',
  km_depart: '',
  km_arrivee: '',
  fuel_depart: '',
  fuel_retour: '',
  condition_depart: '',
  condition_retour: '',
  personnes_transportees: '',
  suppression_franchise: false,
  divers_note: '',
})

const fuelOptions = [
  { label: '0', value: '0' },
  { label: '1/4', value: '1/4' },
  { label: '1/2', value: '1/2' },
  { label: '3/4', value: '3/4' },
  { label: 'Plein', value: 'Plein' },
]

const client = reactive(createClient())
const secondDriver = reactive(createSecondDriver())
const details = reactive(createDetails())
const paymentMethod = ref<'cash' | 'cheque'>('cash')

const hydrate = () => {
  const r = props.reservation
  errorMessage.value = ''
  successMessage.value = ''
  files.driver_license = null
  files.driver_license_verso = null
  files.identity = null
  files.identity_verso = null

  Object.assign(client, createClient())
  Object.assign(secondDriver, createSecondDriver())
  Object.assign(details, createDetails())
  paymentMethod.value = (r?.payment_method as 'cash' | 'cheque') ?? 'cash'

  if (r?.contract_details) {
    Object.assign(details, {
      ...createDetails(),
      ...r.contract_details,
      prolongation_date: day(r.contract_details.prolongation_date),
      suppression_franchise: Boolean(r.contract_details.suppression_franchise),
    })
  }

  if (r?.client) {
    const c = r.client
    Object.assign(client, {
      full_name: c.full_name ?? '',
      birth_date: day(c.birth_date),
      birth_place: c.birth_place ?? '',
      address: c.address ?? '',
      phone: c.phone ?? '',
      whatsapp: c.whatsapp ?? '',
      email: c.email ?? '',
      driver_license: c.driver_license ?? '',
      license_issued_at: day(c.license_issued_at),
      license_issued_place: c.license_issued_place ?? '',
      passport_number: c.passport_number ?? '',
      cin_number: c.cin_number ?? '',
    })
  }

  if (r?.second_driver) {
    Object.assign(secondDriver, { ...createSecondDriver(), ...r.second_driver, birth_date: day(r.second_driver.birth_date), license_issued_at: day(r.second_driver.license_issued_at) })
  }
}

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    hydrate()
  }
})

const priceRows = computed(() => {
  const r = props.reservation
  if (!r) {
    return []
  }
  return [
    { label: 'Assurance', value: formatCurrency(r.insurance_total ?? 0) },
    { label: 'Frais de livraison/reprise', value: formatCurrency(r.delivery_total ?? 0) },
    { label: 'Total général', value: formatCurrency(r.total_price) },
  ]
})

const onFile = (event: Event, field: string) => {
  files[field] = (event.target as HTMLInputElement).files?.[0] ?? null
}

const documentFields = [
  { field: 'driver_license', label: 'Permis — recto', existing: 'driver_license_path' },
  { field: 'driver_license_verso', label: 'Permis — verso', existing: 'driver_license_verso_path' },
  { field: 'identity', label: 'CIN / Passeport — recto', existing: 'identity_path' },
  { field: 'identity_verso', label: 'CIN / Passeport — verso', existing: 'identity_verso_path' },
]

const existingDoc = (key: string) =>
  (props.reservation as unknown as Record<string, string | null | undefined>)?.[key]

const persist = async (): Promise<Reservation | null> => {
  const r = props.reservation
  if (!r) {
    return null
  }

  // 1) Save corrected contract info.
  const secondDriverPayload = Object.values(secondDriver).some((v) => String(v).trim().length)
    ? { ...secondDriver }
    : null

  const response = await adminApi<ResourceResponse<Reservation>>(`/reservations/${r.id}/contract`, {
    method: 'PATCH',
    body: {
      payment_method: paymentMethod.value,
      client: { ...client, whatsapp: client.whatsapp || null, email: client.email || null },
      second_driver: secondDriverPayload,
      contract_details: { ...details },
    },
  })

  // 2) Upload documents (recto/verso) if any were chosen.
  const chosen = Object.entries(files).filter(([, file]) => file)
  if (chosen.length) {
    const body = new FormData()
    for (const [field, file] of chosen) {
      body.append(field, file as File)
    }
    const docs = await adminApi<ResourceResponse<Reservation>>(`/reservations/${r.id}/documents`, {
      method: 'POST',
      body,
    })
    return docs.data
  }

  return response.data
}

const save = async () => {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const updated = await persist()
    if (updated) {
      emit('updated', updated)
    }
    successMessage.value = 'Informations du contrat enregistrées.'
  } catch (error) {
    errorMessage.value = extractError(error, 'Échec de l’enregistrement.')
  } finally {
    saving.value = false
  }
}

const approveAndGenerate = async () => {
  const r = props.reservation
  if (!r) {
    return
  }

  generating.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    await persist()

    // Mark the reservation as completed.
    await adminApi(`/reservations/${r.id}/status`, {
      method: 'PATCH',
      body: { status: 'completed' },
    })

    // Download the generated contract PDF.
    const blob = await adminApi<Blob>(`/reservations/${r.id}/contract`, { responseType: 'blob' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `contrat-${r.contract_number ?? r.reservation_number ?? r.id}.pdf`
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)

    const refreshed = await adminApi<ResourceResponse<Reservation>>(`/reservations/${r.id}`)
    emit('updated', refreshed.data)
    emit('close')
  } catch (error) {
    errorMessage.value = extractError(error, 'Le contrat n’a pas pu être généré.')
  } finally {
    generating.value = false
  }
}
</script>

<template>
  <AppModal
    :open="open"
    title="Établir le contrat"
    description="Vérifiez et corrigez les informations, ajoutez les documents, puis générez le contrat."
    size="xl"
    @close="emit('close')"
  >
    <div v-if="reservation" class="space-y-6">
      <StateNotice v-if="errorMessage" title="Erreur" :message="errorMessage" tone="error" />
      <StateNotice v-else-if="successMessage" title="Enregistré" :message="successMessage" tone="success" />

      <!-- 1er conducteur -->
      <section>
        <h4 class="mb-3 font-heading text-[1rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">1er conducteur</h4>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          <FormField v-model="client.full_name" name="c_name" label="Nom & prénom" />
          <FormField v-model="client.birth_date" name="c_birth" label="Né le" type="date" />
          <FormField v-model="client.birth_place" name="c_birthplace" label="À (lieu de naissance)" />
          <FormField v-model="client.address" name="c_addr" label="Adresse" />
          <FormField v-model="client.phone" name="c_phone" label="Téléphone" type="tel" />
          <FormField v-model="client.email" name="c_email" label="Email" type="email" />
          <FormField v-model="client.driver_license" name="c_dl" label="Permis n°" />
          <FormField v-model="client.license_issued_at" name="c_dldate" label="Permis délivré le" type="date" />
          <FormField v-model="client.license_issued_place" name="c_dlplace" label="Permis délivré à" />
          <FormField v-model="client.cin_number" name="c_cin" label="CIN n°" />
          <FormField v-model="client.passport_number" name="c_pass" label="Passeport n°" />
        </div>
      </section>

      <!-- 2ème conducteur -->
      <section>
        <h4 class="mb-3 font-heading text-[1rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">2ème conducteur (optionnel)</h4>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          <FormField v-model="secondDriver.full_name" name="s_name" label="Nom & prénom" />
          <FormField v-model="secondDriver.birth_date" name="s_birth" label="Né le" type="date" />
          <FormField v-model="secondDriver.birth_place" name="s_birthplace" label="À (lieu de naissance)" />
          <FormField v-model="secondDriver.phone" name="s_phone" label="Téléphone" type="tel" />
          <FormField v-model="secondDriver.driver_license" name="s_dl" label="Permis n°" />
          <FormField v-model="secondDriver.cin_number" name="s_cin" label="CIN n°" />
        </div>
      </section>

      <!-- Documents -->
      <section>
        <h4 class="mb-3 font-heading text-[1rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">Documents (recto / verso)</h4>
        <div class="grid gap-4 sm:grid-cols-2">
          <div v-for="doc in documentFields" :key="doc.field">
            <span class="mb-2 block text-[0.78rem] font-semibold uppercase tracking-[0.18em] text-[var(--text-subtle)]">{{ doc.label }}</span>
            <img
              v-if="existingDoc(doc.existing)"
              :src="mediaUrl(existingDoc(doc.existing))"
              :alt="doc.label"
              class="mb-2 h-28 w-full rounded-[14px] border border-[var(--surface-border)] object-cover"
            >
            <input type="file" accept="image/*" class="block w-full rounded-[16px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-4 py-3 text-[0.9rem] text-[var(--text-strong)] file:mr-3 file:rounded-[10px] file:border-0 file:bg-[var(--primary-soft)] file:px-3 file:py-1.5 file:text-[var(--primary)]" @change="(e) => onFile(e, doc.field)">
          </div>
        </div>
      </section>

      <!-- Détails du contrat -->
      <section>
        <h4 class="mb-3 font-heading text-[1rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">Détails du contrat</h4>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          <FormField v-model="details.prolongation_date" name="d_prol_date" label="Prolongation — date" type="date" />
          <FormField v-model="details.prolongation_time" name="d_prol_time" label="Prolongation — heure" type="time" />
          <FormField v-model="details.prolongation_location" name="d_prol_loc" label="Prolongation — lieu" />
          <FormField v-model="details.km_depart" name="d_km_dep" label="Km départ" type="number" min="0" />
          <FormField v-model="details.km_arrivee" name="d_km_arr" label="Km arrivée" type="number" min="0" />
          <FormField v-model="details.personnes_transportees" name="d_pers" label="Personnes transportées" type="number" min="0" />
          <FormSelect v-model="details.fuel_depart" name="d_fuel_dep" label="Carburant départ" :options="fuelOptions" />
          <FormSelect v-model="details.fuel_retour" name="d_fuel_ret" label="Carburant retour" :options="fuelOptions" />
        </div>
        <div class="mt-3 grid gap-3 md:grid-cols-2">
          <FormTextarea v-model="details.condition_depart" name="d_cond_dep" label="État du véhicule — départ" rows="2" />
          <FormTextarea v-model="details.condition_retour" name="d_cond_ret" label="État du véhicule — retour" rows="2" />
        </div>
        <div class="mt-3 grid gap-3 md:grid-cols-2">
          <FormField v-model="details.divers_note" name="d_divers" label="Divers (note)" />
          <FormCheckbox v-model="details.suppression_franchise" name="d_franchise" label="Le client accepte la suppression de la franchise" />
        </div>
      </section>

      <!-- Paiement + récapitulatif -->
      <section class="grid gap-4 md:grid-cols-2">
        <div>
          <h4 class="mb-3 font-heading text-[1rem] uppercase tracking-[0.12em] text-[var(--text-strong)]">Mode de règlement</h4>
          <FormSelect
            v-model="paymentMethod"
            name="payment_method"
            label="Paiement"
            :options="[{ label: 'Cash', value: 'cash' }, { label: 'Chèque', value: 'cheque' }]"
          />
        </div>
        <div class="rounded-[18px] border border-[var(--surface-border)] bg-[var(--surface-2)] p-4">
          <div v-for="row in priceRows" :key="row.label" class="flex items-center justify-between py-1 text-[0.9rem]">
            <span class="text-[var(--text-muted)]">{{ row.label }}</span>
            <span class="font-semibold text-[var(--text-strong)]">{{ row.value }}</span>
          </div>
          <p v-if="reservation.contract_number" class="mt-2 text-[0.8rem] text-[var(--text-subtle)]">
            Contrat N° {{ reservation.contract_number }}
          </p>
        </div>
      </section>
    </div>

    <template #footer>
      <div class="flex flex-wrap items-center justify-end gap-3">
        <BaseButton type="button" variant="secondary" :disabled="saving || generating" @click="save">
          <BaseIcon name="check-circle" :size="18" />
          <span>{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</span>
        </BaseButton>
        <BaseButton type="button" :disabled="saving || generating" @click="approveAndGenerate">
          <BaseIcon name="payment" :size="18" />
          <span>{{ generating ? 'Génération...' : 'Approuver & générer le contrat' }}</span>
        </BaseButton>
      </div>
    </template>
  </AppModal>
</template>
