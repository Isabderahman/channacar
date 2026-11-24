import type { Car } from '~/types/entities'
import { carWhatsappMessage } from '~/utils/whatsapp'

// Lets a page (e.g. the car detail page) override the floating WhatsApp
// button's prefilled message with a contextual one. Null means "use the
// generic enquiry".
export const useWhatsappFloatMessage = () =>
  useState<string | null>('whatsapp-float-message', () => null)

let lastOwner = 0

// Binds the floating button's message to a car for as long as the calling
// component is alive, then clears it on navigation away. Follows the current
// language.
//
// On page-to-page navigation (another car, or the same car in another
// language) the incoming page sets its message before the outgoing one is
// disposed, so each binding only updates/clears the message while it is
// still the latest one to have claimed it.
export const useCarWhatsappFloat = (car: Ref<Car | null>) => {
  const message = useWhatsappFloatMessage()
  const owner = useState<number | null>('whatsapp-float-owner', () => null)
  const { t, locale } = useI18n()
  const id = (lastOwner += 1)
  let claimed = false

  watch(
    [car, locale],
    ([value]) => {
      if (claimed && owner.value !== id) {
        return
      }
      claimed = true
      owner.value = id
      message.value = value ? carWhatsappMessage(value, t) : null
    },
    { immediate: true },
  )

  onScopeDispose(() => {
    if (owner.value === id) {
      owner.value = null
      message.value = null
    }
  })
}
