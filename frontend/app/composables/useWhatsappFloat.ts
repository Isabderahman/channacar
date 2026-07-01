import type { Car } from '~/types/entities'
import { carWhatsappMessage } from '~/utils/whatsapp'

// Lets a page (e.g. the car detail page) override the floating WhatsApp
// button's prefilled message with a contextual one. Null means "use the
// generic enquiry".
export const useWhatsappFloatMessage = () =>
  useState<string | null>('whatsapp-float-message', () => null)

// Binds the floating button's message to a car for as long as the calling
// component is alive, then clears it on navigation away.
export const useCarWhatsappFloat = (car: Ref<Car | null>) => {
  const message = useWhatsappFloatMessage()

  watch(
    car,
    (value) => {
      message.value = value ? carWhatsappMessage(value) : null
    },
    { immediate: true },
  )

  onScopeDispose(() => {
    message.value = null
  })
}
