import type { BillingDocument } from '~/types/entities'

/** Download a devis / facture PDF through the authenticated admin API. */
export const useBillingDocumentPdf = () => {
  const { adminApi } = useApi()
  const toast = useToast()
  const downloading = ref<number | null>(null)

  const download = async (document: Pick<BillingDocument, 'id' | 'number'>) => {
    downloading.value = document.id

    try {
      const blob = await adminApi<Blob>(`/billing-documents/${document.id}/pdf`, { responseType: 'blob' })
      const url = URL.createObjectURL(blob)
      const link = window.document.createElement('a')
      link.href = url
      link.download = `${document.number}.pdf`
      link.click()
      URL.revokeObjectURL(url)
    } catch (error) {
      toast.apiError(error, 'Le PDF n’a pas pu être généré.')
    } finally {
      downloading.value = null
    }
  }

  return { download, downloading }
}
