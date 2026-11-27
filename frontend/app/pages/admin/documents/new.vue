<script setup lang="ts">
import AdminBillingDocumentForm from '~/components/admin/AdminBillingDocumentForm.vue'
import type { BillingDocument, BillingDocumentType } from '~/types/entities'

definePageMeta({
  layout: 'admin',
})

const route = useRoute()

// ?type=quote|invoice from the list page buttons.
const initialType = computed<BillingDocumentType>(() => (route.query.type === 'invoice' ? 'invoice' : 'quote'))

const onSaved = async (document: BillingDocument) => {
  await navigateTo(`/admin/documents/${document.id}`)
}
</script>

<template>
  <div>
    <AdminPageIntro
      :title="initialType === 'invoice' ? 'Nouvelle facture' : 'Nouveau devis'"
      description="Saisissez le client et les lignes (prix HT). Le numéro est attribué à l’enregistrement ; le PDF reprend le format Chanâa Car."
      eyebrow="Facturation"
    >
      <BaseButton to="/admin/documents" variant="secondary">
        <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
        <span>Retour</span>
      </BaseButton>
    </AdminPageIntro>

    <AdminBillingDocumentForm :key="initialType" :initial-type="initialType" @saved="onSaved" />
  </div>
</template>
