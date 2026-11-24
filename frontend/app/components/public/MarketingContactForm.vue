<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'
import type { ContactFormField } from '~/content/marketing/shared'
import { whatsappHref } from '~/utils/whatsapp'

const route = useRoute()
const marketing = useMarketingContent()
const copy = computed(() => marketing.value.contactForm)
const submitted = ref(false)
const whatsappUrl = ref('')

const form = reactive<Record<ContactFormField, string>>({
  date_depart: '',
  date_retour: '',
  email: '',
  lieu_retour: '',
  lieu_prise_en_charge: '',
  message: '',
  nom_complet: '',
  telephone: '',
})

// Order of the lines in the WhatsApp message.
const messageFields: ContactFormField[] = [
  'nom_complet',
  'telephone',
  'email',
  'date_depart',
  'date_retour',
  'lieu_prise_en_charge',
  'lieu_retour',
  'message',
]

// When the visitor arrives from a car detail page (?vehicule=...), pre-fill the
// message so their enquiry states which car it is about. The prefill follows a
// language switch as long as the visitor hasn't edited it.
const vehicule = computed(() => {
  const value = route.query.vehicule
  return (Array.isArray(value) ? value[0] : value)?.trim() || ''
})

let lastPrefill = ''
watch(
  [vehicule, () => copy.value.vehiclePrefill],
  ([value, template]) => {
    if (!value) return
    const prefill = template.replace('{vehicle}', value)
    if (!form.message || form.message === lastPrefill) {
      form.message = prefill
    }
    lastPrefill = prefill
  },
  { immediate: true },
)

// <input type="date"> yields YYYY-MM-DD; spell it out in the visitor's language.
const formatDate = (value: string) => {
  const date = new Date(`${value}T00:00:00`)
  if (Number.isNaN(date.getTime())) return value
  return new Intl.DateTimeFormat(marketing.value.dateLocale, { dateStyle: 'long' }).format(date)
}

// No backend endpoint for enquiries: the request is handed to WhatsApp, where
// the team answers, with every filled field on its own labelled line.
const buildMessage = () => {
  const { labels, labelSeparator, whatsappIntro } = copy.value
  const lines = messageFields
    .map((field) => {
      const value = form[field].trim()
      if (!value) return ''
      const shown = field.startsWith('date_') ? formatDate(value) : value
      return `${labels[field]}${labelSeparator}${shown}`
    })
    .filter(Boolean)

  return lines.length ? [whatsappIntro, '', ...lines].join('\n') : whatsappIntro
}

const submit = () => {
  whatsappUrl.value = whatsappHref(buildMessage())
  window.open(whatsappUrl.value, '_blank', 'noopener')
  submitted.value = true
}
</script>

<template>
  <div>
    <div v-inview data-anim="rule stagger" class="rule-b pb-5 md:pb-8">
      <h2 class="text-[32px] font-medium leading-none tracking-[-0.035em] lg:text-[40px]">
        {{ copy.title }}
      </h2>
      <p class="mt-4 max-w-[52ch] text-[15px] text-ink-2 lg:text-[16px]">
        {{ copy.subtitle }}
      </p>
    </div>

    <form v-inview data-anim="rise" class="mt-6 flex flex-col gap-4 lg:mt-8" @submit.prevent="submit">
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="field-label">
          {{ copy.labels.nom_complet }}
          <input v-model="form.nom_complet" class="field" name="nom_complet" type="text" autocomplete="name">
        </label>
        <label class="field-label">
          {{ copy.labels.telephone }}
          <input v-model="form.telephone" class="field" name="telephone" type="tel" autocomplete="tel">
        </label>
        <label class="field-label">
          {{ copy.labels.email }}
          <input v-model="form.email" class="field" name="email" type="email" autocomplete="email">
        </label>
        <label class="field-label">
          {{ copy.labels.date_depart }}
          <input v-model="form.date_depart" class="field" name="date_depart" type="date">
        </label>
        <label class="field-label">
          {{ copy.labels.date_retour }}
          <input v-model="form.date_retour" class="field" name="date_retour" type="date">
        </label>
        <label class="field-label">
          {{ copy.labels.lieu_prise_en_charge }}
          <input v-model="form.lieu_prise_en_charge" class="field" name="lieu_prise_en_charge" type="text">
        </label>
        <label class="field-label sm:col-span-2">
          {{ copy.labels.lieu_retour }}
          <input v-model="form.lieu_retour" class="field" name="lieu_retour" type="text">
        </label>
        <label class="field-label sm:col-span-2">
          {{ copy.labels.message }}
          <textarea v-model="form.message" class="field" name="message" rows="5" />
        </label>
      </div>

      <div
        v-if="submitted"
        role="status"
        class="border border-line-strong px-4 py-3.5"
      >
        <p class="font-medium">{{ copy.statusTitle }}</p>
        <p class="mt-1 text-[14px] text-ink-2">
          {{ copy.statusText }}
          <a
            :href="whatsappUrl"
            target="_blank"
            rel="noopener"
            class="underline transition-colors hover:text-accent"
          >{{ copy.statusLink }}</a>
        </p>
      </div>

      <button type="submit" class="btn btn-primary min-h-14 w-full sm:w-auto sm:self-start sm:min-w-[280px]">
        {{ copy.submit }}
        <SiteIcon name="arrow-right" />
      </button>
    </form>
  </div>
</template>
