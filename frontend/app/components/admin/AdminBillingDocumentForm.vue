<script setup lang="ts">
import type {
  BillingDocument,
  BillingDocumentLine,
  BillingDocumentType,
  Car,
  PaginatedResponse,
  ResourceResponse,
} from '~/types/entities'
import { BILLING_TYPE_OPTIONS } from '~/utils/constants'
import {
  billingTotals,
  daysInPeriod,
  emptyBillingLine,
  formatDh,
  lineTotal,
  todayIso,
} from '~/utils/billing'
import { toNumber } from '~/utils/formatters'

// Create or edit a devis / facture. Prices are entered HT; the backend
// recomputes every total on save (the summary here is a live preview).
const props = withDefaults(
  defineProps<{
    /** Edit mode when set. Type and number are fixed once issued. */
    document?: BillingDocument | null
    initialType?: BillingDocumentType
  }>(),
  { document: null, initialType: 'quote' },
)

const emit = defineEmits<{
  saved: [document: BillingDocument]
}>()

const { adminApi } = useApi()
const toast = useToast()

const isEdit = computed(() => props.document !== null)
const saving = ref(false)
const cars = ref<Car[]>([])

type EditableLine = BillingDocumentLine & { description: string; key: number }

let lineKey = 0
const toEditable = (line: BillingDocumentLine): EditableLine => ({
  ...line,
  description: line.description ?? '',
  key: ++lineKey,
})

const createState = () => ({
  type: props.initialType as BillingDocumentType,
  issue_date: todayIso(),
  validity_days: 15 as number | string,
  client_name: '',
  client_details: '',
  tva_rate: 20 as number | string,
  notes: '',
  lines: [toEditable(emptyBillingLine('per_day'))],
})

const form = reactive(createState())

const fill = (document: BillingDocument) => {
  form.type = document.type
  form.issue_date = document.issue_date.slice(0, 10)
  form.validity_days = document.validity_days ?? 15
  form.client_name = document.client_name
  form.client_details = document.client_details ?? ''
  form.tva_rate = toNumber(document.tva_rate)
  form.notes = document.notes ?? ''
  form.lines = document.lines.map((line) =>
    toEditable({
      ...line,
      quantity: line.quantity == null ? null : toNumber(line.quantity),
      unit_price: line.unit_price == null ? null : toNumber(line.unit_price),
      total: line.total == null ? null : toNumber(line.total),
    }),
  )
}

watch(
  () => props.document,
  (document) => {
    if (document) {
      fill(document)
    }
  },
  { immediate: true },
)

const totals = computed(() => billingTotals(form.lines, form.tva_rate))

const carOptions = computed(() =>
  cars.value.map((car) => ({
    label: `${car.brand} ${car.name} · ${car.registration_number}`,
    value: car.id,
  })),
)

const addLine = (type: BillingDocumentLine['type']) => {
  form.lines.push(toEditable(emptyBillingLine(type)))
}

const removeLine = (index: number) => {
  form.lines.splice(index, 1)
}

const moveLine = (index: number, offset: -1 | 1) => {
  const target = index + offset

  if (target < 0 || target >= form.lines.length) {
    return
  }

  const [line] = form.lines.splice(index, 1)
  form.lines.splice(target, 0, line!)
}

const setLineType = (line: EditableLine, type: BillingDocumentLine['type']) => {
  if (line.type === type) {
    return
  }

  // Keep the amount when switching: per-day total → flat amount, and back.
  if (type === 'flat') {
    line.total = lineTotal(line)
  } else {
    line.quantity = daysInPeriod(line.period_start, line.period_end) ?? 1
    line.unit_price = toNumber(line.total)
  }

  line.type = type
}

/** Picking a car fills the designation and its daily rate (editable after). */
const applyCar = (line: EditableLine, event: Event) => {
  const select = event.target as HTMLSelectElement
  const car = cars.value.find((item) => item.id === Number(select.value))
  select.value = ''

  if (!car) {
    return
  }

  line.designation = `Location ${car.brand} ${car.name}`
  line.unit_price = toNumber(car.base_price_per_day)
}

/** The period drives the number of days (still editable afterwards). */
const setPeriod = (line: EditableLine, field: 'period_start' | 'period_end', value: string | number | null) => {
  line[field] = value ? String(value) : null

  const days = daysInPeriod(line.period_start, line.period_end)

  if (days !== null) {
    line.quantity = days
  }
}

const submitLabel = computed(() => {
  if (saving.value) {
    return 'Enregistrement…'
  }

  if (isEdit.value) {
    return 'Enregistrer les modifications'
  }

  return form.type === 'quote' ? 'Créer le devis' : 'Créer la facture'
})

const periodError = (line: EditableLine) =>
  Boolean(line.period_start && line.period_end && line.period_end < line.period_start)

const payload = () => ({
  ...(isEdit.value ? {} : { type: form.type }),
  issue_date: form.issue_date,
  validity_days: form.type === 'quote' ? Number(form.validity_days) || null : null,
  client_name: form.client_name,
  client_details: form.client_details || null,
  tva_rate: Number(form.tva_rate),
  notes: form.notes || null,
  lines: form.lines.map((line) => ({
    type: line.type,
    designation: line.designation,
    description: line.description || null,
    period_start: line.type === 'per_day' ? line.period_start || null : null,
    period_end: line.type === 'per_day' ? line.period_end || null : null,
    quantity: line.type === 'per_day' ? Number(line.quantity) : null,
    unit_price: line.type === 'per_day' ? Number(line.unit_price) : null,
    total: line.type === 'flat' ? Number(line.total) : null,
  })),
})

const submit = async () => {
  if (form.lines.length === 0) {
    toast.warning('Ajoutez au moins une ligne.', 'Document vide')
    return
  }

  if (form.lines.some(periodError)) {
    toast.warning('La date de fin d’une période est avant sa date de début.', 'Période invalide')
    return
  }

  saving.value = true

  try {
    const response = await adminApi<ResourceResponse<BillingDocument>>(
      isEdit.value ? `/billing-documents/${props.document!.id}` : '/billing-documents',
      { method: isEdit.value ? 'PUT' : 'POST', body: payload() },
    )

    toast.success(`${response.data.number} enregistré.`, 'Enregistré')
    emit('saved', response.data)
  } catch (error) {
    toast.apiError(error, 'Le document n’a pas pu être enregistré.')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
    const response = await adminApi<PaginatedResponse<Car>>('/cars', { query: { per_page: 100 } })
    cars.value = response.data
  } catch {
    // Quick-fill only: lines can still be typed by hand.
  }
})
</script>

<template>
  <form class="space-y-6" @submit.prevent="submit">
    <AppPanel padding="lg">
      <h3 class="text-[20px] font-medium tracking-[-0.02em]">Document</h3>

      <div class="mt-5 space-y-5 border-t border-line pt-5">
        <fieldset v-if="!isEdit">
          <legend class="mono-label mb-2">Type</legend>
          <div class="flex flex-wrap gap-1.5">
            <button
              v-for="option in BILLING_TYPE_OPTIONS"
              :key="option.value"
              type="button"
              class="chip min-h-10 text-[13px]"
              :aria-pressed="form.type === option.value ? 'true' : 'false'"
              @click="form.type = option.value"
            >
              {{ option.label }}
            </button>
          </div>
        </fieldset>

        <div class="grid gap-4 md:grid-cols-3">
          <FormField v-model="form.issue_date" name="issue_date" label="Date" type="date" required />
          <FormField
            v-if="form.type === 'quote'"
            v-model="form.validity_days"
            name="validity_days"
            label="Validité (jours)"
            type="number"
            min="1"
            max="365"
          />
          <FormField
            v-model="form.tva_rate"
            name="tva_rate"
            label="TVA (%)"
            type="number"
            min="0"
            max="100"
            step="0.01"
            required
          />
        </div>
      </div>
    </AppPanel>

    <AppPanel padding="lg">
      <h3 class="text-[20px] font-medium tracking-[-0.02em]">Client</h3>

      <div class="mt-5 grid gap-4 border-t border-line pt-5">
        <FormField
          v-model="form.client_name"
          name="client_name"
          label="Nom / raison sociale"
          placeholder="Financia Business School"
          required
        />
        <FormTextarea
          v-model="form.client_details"
          name="client_details"
          label="Détails (optionnel)"
          placeholder="Adresse, ICE, téléphone… — une information par ligne"
          :rows="3"
        />
      </div>
    </AppPanel>

    <AppPanel padding="lg">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h3 class="text-[20px] font-medium tracking-[-0.02em]">Lignes</h3>
          <p class="mt-1.5 text-[14px] leading-6 text-ink-2">
            Prix saisis HT. « Par jour » affiche la durée et le prix/jour ; « Forfait » un montant fixe (montant négatif = remise).
          </p>
        </div>
      </div>

      <ol class="mt-5 space-y-3 border-t border-line pt-5">
        <li
          v-for="(line, index) in form.lines"
          :key="line.key"
          class="border border-line p-4"
          :class="index % 2 === 0 ? 'bg-surface-2' : ''"
        >
          <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
              <span class="font-plex text-[12px] text-muted">{{ String(index + 1).padStart(2, '0') }}</span>
              <div class="flex gap-1">
                <button
                  type="button"
                  class="chip min-h-8 px-3 text-[12px]"
                  :aria-pressed="line.type === 'per_day' ? 'true' : 'false'"
                  @click="setLineType(line, 'per_day')"
                >
                  Par jour
                </button>
                <button
                  type="button"
                  class="chip min-h-8 px-3 text-[12px]"
                  :aria-pressed="line.type === 'flat' ? 'true' : 'false'"
                  @click="setLineType(line, 'flat')"
                >
                  Forfait
                </button>
              </div>
            </div>

            <div class="flex items-center gap-1">
              <span class="me-3 whitespace-nowrap text-[15px] font-medium">{{ formatDh(lineTotal(line)) }}</span>
              <button
                type="button"
                class="grid size-8 place-items-center border border-line-strong text-ink-2 transition-colors hover:border-ink hover:text-ink disabled:opacity-40"
                :disabled="index === 0"
                aria-label="Monter la ligne"
                @click="moveLine(index, -1)"
              >
                <BaseIcon name="arrow-right" :size="14" class="-rotate-90" />
              </button>
              <button
                type="button"
                class="grid size-8 place-items-center border border-line-strong text-ink-2 transition-colors hover:border-ink hover:text-ink disabled:opacity-40"
                :disabled="index === form.lines.length - 1"
                aria-label="Descendre la ligne"
                @click="moveLine(index, 1)"
              >
                <BaseIcon name="arrow-right" :size="14" class="rotate-90" />
              </button>
              <button
                type="button"
                class="grid size-8 place-items-center border border-line-strong text-ink-2 transition-colors hover:border-danger hover:text-danger disabled:opacity-40"
                :disabled="form.lines.length === 1"
                aria-label="Supprimer la ligne"
                @click="removeLine(index)"
              >
                <BaseIcon name="trash" :size="14" />
              </button>
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <label v-if="line.type === 'per_day' && carOptions.length" class="block md:col-span-2">
              <span class="mono-label mb-2 block">Remplir depuis un véhicule (optionnel)</span>
              <select class="field min-h-11 w-full text-[15px]" :name="`line-${line.key}-car`" @change="applyCar(line, $event)">
                <option value="">Choisir un véhicule…</option>
                <option v-for="option in carOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
              </select>
            </label>

            <FormField
              v-model="line.designation"
              :name="`line-${line.key}-designation`"
              label="Désignation"
              :placeholder="line.type === 'flat' ? 'Frais de livraison et récupération' : 'Location Volkswagen Touareg Executive'"
              required
              class="md:col-span-2"
            />

            <template v-if="line.type === 'per_day'">
              <FormField
                :model-value="line.period_start ?? ''"
                :name="`line-${line.key}-start`"
                label="Du (optionnel)"
                type="date"
                @update:model-value="setPeriod(line, 'period_start', $event)"
              />
              <FormField
                :model-value="line.period_end ?? ''"
                :name="`line-${line.key}-end`"
                label="Au (optionnel)"
                type="date"
                :min="line.period_start ?? undefined"
                @update:model-value="setPeriod(line, 'period_end', $event)"
              />
              <FormField
                v-model="line.quantity"
                :name="`line-${line.key}-quantity`"
                label="Durée (jours)"
                type="number"
                min="0.5"
                step="0.5"
                required
                :help="line.period_start && line.period_end ? 'Calculée depuis la période — modifiable.' : undefined"
              />
              <FormField
                v-model="line.unit_price"
                :name="`line-${line.key}-unit-price`"
                label="Prix/jour HT (DH)"
                type="number"
                min="0"
                step="0.01"
                required
              />
            </template>

            <FormField
              v-else
              v-model="line.total"
              :name="`line-${line.key}-total`"
              label="Montant HT (DH)"
              type="number"
              step="0.01"
              required
              class="md:col-span-2"
            />

            <FormTextarea
              v-model="line.description"
              :name="`line-${line.key}-description`"
              label="Détail sous la désignation (optionnel)"
              :placeholder="line.type === 'flat' ? 'Livraison aéroport Essaouira (700 DH) + récupération Rabat (1 100 DH)' : 'Kilométrage illimité, assurance tous risques…'"
              :rows="2"
              class="md:col-span-2"
            />
          </div>

          <p v-if="periodError(line)" class="mt-2 text-[13px] text-danger">La date de fin est avant la date de début.</p>
        </li>
      </ol>

      <div class="mt-4 flex flex-wrap gap-3">
        <BaseButton type="button" variant="secondary" @click="addLine('per_day')">
          <BaseIcon name="car" :size="18" />
          <span>Ligne par jour</span>
        </BaseButton>
        <BaseButton type="button" variant="secondary" @click="addLine('flat')">
          <BaseIcon name="plus" :size="18" />
          <span>Ligne forfait</span>
        </BaseButton>
      </div>
    </AppPanel>

    <AppPanel padding="lg">
      <div class="grid gap-6 md:grid-cols-2">
        <FormTextarea
          v-model="form.notes"
          name="notes"
          label="Remarques (optionnel, imprimées sous le total)"
          placeholder="Conditions de paiement, RIB…"
          :rows="4"
        />

        <dl class="self-end">
          <div class="flex justify-between gap-4 py-2 text-[15px]">
            <dt>Total HT</dt>
            <dd class="whitespace-nowrap">{{ formatDh(totals.ht) }}</dd>
          </div>
          <div class="flex justify-between gap-4 py-2 text-[15px]">
            <dt>TVA ({{ toNumber(form.tva_rate) }}%)</dt>
            <dd class="whitespace-nowrap">{{ formatDh(totals.tva) }}</dd>
          </div>
          <div class="mt-1 flex justify-between gap-4 bg-accent px-3 py-3 text-[17px] font-medium text-on-accent">
            <dt>TOTAL TTC</dt>
            <dd class="whitespace-nowrap">{{ formatDh(totals.ttc) }}</dd>
          </div>
        </dl>
      </div>

      <div class="mt-6 flex flex-wrap gap-3 border-t border-line pt-6">
        <BaseButton type="submit" :disabled="saving">
          <BaseIcon :name="isEdit ? 'edit' : 'plus'" :size="18" />
          <span>{{ submitLabel }}</span>
        </BaseButton>
        <slot name="actions" />
      </div>
    </AppPanel>
  </form>
</template>
