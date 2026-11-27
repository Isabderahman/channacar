<script setup lang="ts">
import type { BillingDocument, BillingDocumentType, PaginatedResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { BILLING_TYPE_LABELS, formatDayFr, formatDh } from '~/utils/billing'

definePageMeta({
  layout: 'admin',
})

const { adminApi } = useApi()
const toast = useToast()
const { download, downloading } = useBillingDocumentPdf()

const documents = ref<BillingDocument[]>([])
const pending = ref(true)
const errorMessage = ref('')
const currentPage = ref(1)
const lastPage = ref(1)

const filters = reactive({
  search: '',
  type: '' as '' | BillingDocumentType,
})

const typeFilters = [
  { label: 'Tous', value: '' },
  { label: 'Devis', value: 'quote' },
  { label: 'Factures', value: 'invoice' },
] as const

const rows = computed(() =>
  documents.value.map((document) => ({
    client: document.client_name,
    date: formatDayFr(document.issue_date),
    id: document.id,
    link: document.invoice
      ? `→ ${document.invoice.number}`
      : document.source
        ? `← ${document.source.number}`
        : '',
    number: document.number,
    total: formatDh(document.total_ttc),
    type: document.type,
  })),
)

const loadDocuments = async (page = 1) => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<PaginatedResponse<BillingDocument>>('/billing-documents', {
      query: {
        page,
        per_page: 20,
        ...(filters.type ? { type: filters.type } : {}),
        ...(filters.search.trim() ? { search: filters.search.trim() } : {}),
      },
    })

    documents.value = response.data
    currentPage.value = response.current_page
    lastPage.value = response.last_page
  } catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, {
      fallback: 'Les devis et factures n’ont pas pu être chargés.',
    })
    toast.error(errorMessage.value, 'Chargement impossible')
  } finally {
    pending.value = false
  }
}

const setType = (value: '' | BillingDocumentType) => {
  filters.type = value
  void loadDocuments(1)
}

const downloadRow = (id: number) => {
  const document = documents.value.find((item) => item.id === id)

  if (document) {
    void download(document)
  }
}

onMounted(() => {
  void loadDocuments()
})
</script>

<template>
  <div>
    <AdminPageIntro
      title="Devis & factures"
      description="Créez des devis et des factures au format Chanâa Car, téléchargez-les en PDF, et convertissez un devis accepté en facture."
      eyebrow="Facturation"
    >
      <div class="flex flex-wrap gap-3">
        <BaseButton to="/admin/documents/new?type=quote">
          <BaseIcon name="plus" :size="18" />
          <span>Nouveau devis</span>
        </BaseButton>
        <BaseButton to="/admin/documents/new?type=invoice" variant="secondary">
          <BaseIcon name="plus" :size="18" />
          <span>Nouvelle facture</span>
        </BaseButton>
      </div>
    </AdminPageIntro>

    <AppPanel padding="lg">
      <form class="flex flex-wrap items-end gap-4" @submit.prevent="loadDocuments(1)">
        <div role="group" aria-label="Type" class="flex gap-1.5">
          <button
            v-for="option in typeFilters"
            :key="option.value"
            type="button"
            class="chip min-h-11 text-[13px]"
            :aria-pressed="filters.type === option.value ? 'true' : 'false'"
            @click="setType(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
        <FormField
          v-model="filters.search"
          name="search"
          label="Recherche"
          type="search"
          placeholder="N° ou client"
          class="min-w-[14rem] flex-1"
        />
        <BaseButton type="submit">
          <BaseIcon name="search" :size="18" />
          <span>Rechercher</span>
        </BaseButton>
      </form>
    </AppPanel>

    <StateNotice
      v-if="errorMessage"
      class="mt-6"
      title="Chargement impossible"
      :message="errorMessage"
      tone="error"
    />

    <TableCard
      v-else
      class="mt-6"
      title="Documents"
      description="Ouvrez un document pour le modifier, le télécharger ou le convertir."
      empty-title="Aucun document"
      empty-message="Créez un premier devis ou une première facture avec les boutons ci-dessus."
      :columns="[
        { key: 'number', label: 'N°' },
        { key: 'type', label: 'Type' },
        { key: 'client', label: 'Client' },
        { key: 'date', label: 'Date' },
        { key: 'total', label: 'Total TTC', align: 'end' },
        { key: 'link', label: 'Lien' },
        { key: 'pdf', label: 'PDF' },
      ]"
      :rows="rows"
      @row-click="(row) => navigateTo(`/admin/documents/${row.id}`)"
    >
      <template #toolbar>
        <span class="whitespace-nowrap font-plex text-[12px] text-muted">
          Page {{ currentPage }} / {{ lastPage }}
        </span>
      </template>
      <template #cell-number="{ row }">
        <span class="cursor-pointer whitespace-nowrap font-plex text-[13px] text-ink">{{ row.number }}</span>
      </template>
      <template #cell-type="{ row }">
        <StatusBadge :value="row.type === 'invoice' ? 'active' : 'inactive'" :label="BILLING_TYPE_LABELS[row.type as BillingDocumentType]" />
      </template>
      <template #cell-client="{ row }">
        <span class="cursor-pointer text-ink">{{ row.client }}</span>
      </template>
      <template #cell-total="{ row }">
        <span class="whitespace-nowrap text-ink">{{ row.total }}</span>
      </template>
      <template #cell-link="{ row }">
        <span class="whitespace-nowrap font-plex text-[12px] text-muted">{{ row.link || '—' }}</span>
      </template>
      <template #cell-pdf="{ row }">
        <BaseButton
          size="sm"
          variant="secondary"
          :disabled="downloading === row.id"
          @click.stop="downloadRow(row.id)"
        >
          <span>{{ downloading === row.id ? '…' : 'PDF' }}</span>
        </BaseButton>
      </template>
    </TableCard>

    <div class="mt-4 flex justify-end gap-3">
      <BaseButton
        type="button"
        variant="secondary"
        :disabled="currentPage <= 1 || pending"
        @click="loadDocuments(currentPage - 1)"
      >
        <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
        <span>Précédent</span>
      </BaseButton>
      <BaseButton
        type="button"
        variant="secondary"
        :disabled="currentPage >= lastPage || pending"
        @click="loadDocuments(currentPage + 1)"
      >
        <span>Suivant</span>
        <BaseIcon name="arrow-right" :size="18" />
      </BaseButton>
    </div>
  </div>
</template>
