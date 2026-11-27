<script setup lang="ts">
import AdminBillingDocumentForm from '~/components/admin/AdminBillingDocumentForm.vue'
import type { BillingDocument, ResourceResponse } from '~/types/entities'
import { resolveApiErrorMessage } from '~/utils/api-error'
import { BILLING_TYPE_LABELS } from '~/utils/billing'

definePageMeta({
  layout: 'admin',
})

const route = useRoute()
const { adminApi } = useApi()
const toast = useToast()
const { download, downloading } = useBillingDocumentPdf()

const document = ref<BillingDocument | null>(null)
const pending = ref(true)
const busy = ref(false)
const errorMessage = ref('')

const loadDocument = async () => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await adminApi<ResourceResponse<BillingDocument>>(`/billing-documents/${Number(route.params.id)}`)
    document.value = response.data
  } catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, {
      fallback: 'Ce document n’a pas pu être chargé.',
    })
    toast.error(errorMessage.value, 'Chargement impossible')
  } finally {
    pending.value = false
  }
}

const convertToInvoice = async () => {
  if (!document.value || !window.confirm(`Créer une facture datée d’aujourd’hui à partir du devis ${document.value.number} ?`)) {
    return
  }

  busy.value = true

  try {
    const response = await adminApi<ResourceResponse<BillingDocument>>(
      `/billing-documents/${document.value.id}/invoice`,
      { method: 'POST' },
    )

    toast.success(`Facture ${response.data.number} créée.`, 'Converti')
    await navigateTo(`/admin/documents/${response.data.id}`)
  } catch (error) {
    toast.apiError(error, 'Le devis n’a pas pu être converti.')
  } finally {
    busy.value = false
  }
}

const deleteDocument = async () => {
  if (!document.value || !window.confirm(`Supprimer définitivement ${document.value.number} ?`)) {
    return
  }

  busy.value = true

  try {
    await adminApi(`/billing-documents/${document.value.id}`, { method: 'DELETE' })
    toast.success(`${document.value.number} supprimé.`, 'Supprimé')
    await navigateTo('/admin/documents')
  } catch (error) {
    toast.apiError(error, 'Le document n’a pas pu être supprimé.')
  } finally {
    busy.value = false
  }
}

watch(
  () => route.params.id,
  () => {
    void loadDocument()
  },
  { immediate: true },
)
</script>

<template>
  <div>
    <AdminPageIntro
      :title="document ? `${BILLING_TYPE_LABELS[document.type]} ${document.number}` : 'Document'"
      description="Modifiez le document puis téléchargez le PDF. Le type et le numéro ne changent pas."
      eyebrow="Facturation"
    >
      <div class="flex flex-wrap gap-3">
        <BaseButton to="/admin/documents" variant="secondary">
          <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
          <span>Retour</span>
        </BaseButton>
        <BaseButton v-if="document" :disabled="downloading === document.id" @click="download(document)">
          <span>{{ downloading === document.id ? 'Génération…' : 'Télécharger le PDF' }}</span>
        </BaseButton>
        <BaseButton
          v-if="document?.type === 'quote' && !document.invoice"
          variant="secondary"
          :disabled="busy"
          @click="convertToInvoice"
        >
          <BaseIcon name="refresh" :size="18" />
          <span>Convertir en facture</span>
        </BaseButton>
        <BaseButton v-if="document" variant="ghost" :disabled="busy" @click="deleteDocument">
          <BaseIcon name="trash" :size="18" />
          <span>Supprimer</span>
        </BaseButton>
      </div>
    </AdminPageIntro>

    <StateNotice
      v-if="errorMessage"
      title="Chargement impossible"
      :message="errorMessage"
      tone="error"
    />

    <StateNotice
      v-if="pending && !document"
      title="Chargement"
      message="Récupération du document."
    />

    <template v-if="document">
      <p v-if="document.invoice" class="mb-6 border-s-2 border-ok ps-3 text-[14px] text-ink-2">
        Ce devis a été converti en facture
        <NuxtLink :to="`/admin/documents/${document.invoice.id}`" class="text-ink underline underline-offset-4">{{ document.invoice.number }}</NuxtLink>.
      </p>
      <p v-else-if="document.source" class="mb-6 border-s-2 border-line-strong ps-3 text-[14px] text-ink-2">
        Facture issue du devis
        <NuxtLink :to="`/admin/documents/${document.source.id}`" class="text-ink underline underline-offset-4">{{ document.source.number }}</NuxtLink>.
      </p>

      <AdminBillingDocumentForm :document="document" @saved="(saved) => (document = saved)" />
    </template>
  </div>
</template>
