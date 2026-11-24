<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'
import type { Testimonial } from '~/types/entities'
import { formatAmount } from '~/utils/car-display'

// Reviews (published testimonials from the back-office) next to the
// "Bon à savoir" FAQ accordion. The reviews column is dropped when there
// are none, rather than showing placeholders.
const props = defineProps<{
  testimonials: Testimonial[]
  caution: number
  whatsappLink: string
  contactLink: RouteLocationRaw
}>()

const { t, locale } = useI18n()

const reviews = computed(() => props.testimonials.slice(0, 3))

// Month names follow the language ("novembre 2026", "November 2026", …).
const DATE_LOCALES: Record<string, string> = { fr: 'fr-FR', en: 'en-GB', es: 'es-ES', ar: 'ar-MA' }

const reviewDate = (value?: string) =>
  value
    ? new Intl.DateTimeFormat(DATE_LOCALES[locale.value] ?? 'fr-FR', { month: 'long', year: 'numeric' }).format(
        new Date(value),
      )
    : ''

const FAQ_KEYS = ['conditions', 'documents', 'insurance', 'mileage', 'deposit', 'cancel', 'breakdown'] as const

const faq = computed(() =>
  FAQ_KEYS.map((key) => ({
    q: t(`car.faq.items.${key}.q`),
    a:
      key === 'deposit' && props.caution > 0
        ? t('car.faq.items.deposit.aAmount', { amount: formatAmount(props.caution) })
        : t(`car.faq.items.${key}.a`),
  })),
)

const open = ref(0)
const toggle = (index: number) => {
  open.value = open.value === index ? -1 : index
}
</script>

<template>
  <section class="wrap mt-8 flex flex-wrap items-start gap-12 lg:mt-40 lg:gap-16">
    <div v-if="reviews.length" id="avis" class="min-w-0 flex-[1_1_420px] scroll-mt-6">
      <p v-inview data-anim="rise" class="mono-label mb-3 text-[10px] lg:mb-8 lg:text-[12px]">{{ t('car.faq.reviewsTitle') }}</p>
      <div v-inview data-anim="stagger" class="flex flex-col gap-2.5 lg:gap-3">
        <figure
          v-for="review in reviews"
          :key="review.id"
          class="flex flex-col gap-2.5 border border-line-strong p-4 lg:p-6"
        >
          <p class="tracking-[2px] text-accent" :aria-label="t('car.faq.rating', { stars: review.stars })">
            {{ '★'.repeat(review.stars) }}<span class="text-faint">{{ '★'.repeat(5 - review.stars) }}</span>
          </p>
          <blockquote class="text-[14px] text-ink-2 lg:text-[16px]">{{ review.content }}</blockquote>
          <figcaption class="font-plex text-[11px] text-muted">
            {{ review.full_name }}<template v-if="review.created_at"> · {{ reviewDate(review.created_at) }}</template>
          </figcaption>
        </figure>
      </div>
    </div>

    <div v-inview data-anim="rise" class="min-w-0 flex-[1.3_1_520px]">
      <h2 class="mb-5 text-[28px] font-medium leading-none tracking-[-0.035em] lg:mb-8 lg:text-[40px]">
        <span class="lg:hidden">{{ t('car.faq.titleShort') }}</span>
        <span class="hidden lg:inline">{{ t('car.faq.title') }}</span>
      </h2>

      <div class="border-t border-line">
        <div v-for="(item, index) in faq" :key="item.q" class="border-b border-line">
          <h3>
            <button
              :id="`faq-q-${index}`"
              type="button"
              class="flex min-h-[52px] w-full items-center justify-between gap-4 py-3 text-start text-[15px] transition-colors hover:text-accent lg:min-h-14 lg:py-3.5 lg:text-[17px]"
              :aria-expanded="open === index ? 'true' : 'false'"
              :aria-controls="`faq-a-${index}`"
              @click="toggle(index)"
            >
              {{ item.q }}
              <span aria-hidden="true" class="font-plex text-accent">{{ open === index ? '−' : '+' }}</span>
            </button>
          </h3>
          <p
            v-show="open === index"
            :id="`faq-a-${index}`"
            role="region"
            :aria-labelledby="`faq-q-${index}`"
            class="mb-4 max-w-[560px] text-[14px] text-ink-2 lg:mb-5 lg:text-[15px]"
          >
            {{ item.a }}
          </p>
        </div>
      </div>

      <i18n-t keypath="car.faq.more" tag="p" scope="global" class="mt-6 text-[15px] text-ink-2">
        <template #whatsapp>
          <a :href="whatsappLink" target="_blank" rel="noopener" class="text-link text-ink">{{ t('car.faq.moreWhatsapp') }}</a>
        </template>
        <template #form>
          <NuxtLink :to="contactLink" class="text-link text-ink">{{ t('car.faq.moreForm') }}</NuxtLink>
        </template>
      </i18n-t>
    </div>
  </section>
</template>
