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

const reviews = computed(() => props.testimonials.slice(0, 3))

const reviewDate = (value?: string) =>
  value ? new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' }).format(new Date(value)) : ''

const faq = computed(() => [
  {
    q: 'Quelles sont les conditions pour louer ?',
    a: 'Le conducteur doit avoir au moins 21 ans et un permis de conduire valide depuis au moins 1 an.',
  },
  {
    q: 'Quels documents dois-je présenter ?',
    a: 'Une pièce d’identité valide (CIN ou passeport), votre permis de conduire et un moyen de paiement.',
  },
  {
    q: 'L’assurance est-elle incluse ?',
    a: 'Oui, nos véhicules sont couverts selon les conditions du contrat de location. L’assurance complémentaire est une protection en plus, facultative.',
  },
  {
    q: 'Le kilométrage est-il limité ?',
    a: 'La plupart de nos locations incluent un kilométrage adapté à votre itinéraire. Le kilométrage illimité est disponible sur demande selon la catégorie et la durée.',
  },
  {
    q: 'Comment fonctionne la caution ?',
    a:
      props.caution > 0
        ? `Pour ce véhicule, la caution est de ${formatAmount(props.caution)} MAD. C’est une garantie remboursable, non incluse dans le total de la location.`
        : 'La caution est une garantie remboursable, non incluse dans le total de la location. Notre équipe vous en précise le montant à la confirmation.',
  },
  {
    q: 'Puis-je annuler ma réservation ?',
    a: 'Oui, selon les conditions d’annulation précisées lors de la réservation.',
  },
  {
    q: 'Que faire en cas de panne ou d’accident ?',
    a: 'Contactez immédiatement notre équipe d’assistance, disponible 24h/24 et 7j/7.',
  },
])

const open = ref(0)
const toggle = (index: number) => {
  open.value = open.value === index ? -1 : index
}
</script>

<template>
  <section class="wrap mt-8 flex flex-wrap items-start gap-12 lg:mt-40 lg:gap-16">
    <div v-if="reviews.length" id="avis" class="min-w-0 flex-[1_1_420px] scroll-mt-6">
      <p v-inview data-anim="rise" class="mono-label mb-3 text-[10px] lg:mb-8 lg:text-[12px]">Ils ont loué chez nous</p>
      <div v-inview data-anim="stagger" class="flex flex-col gap-2.5 lg:gap-3">
        <figure
          v-for="review in reviews"
          :key="review.id"
          class="flex flex-col gap-2.5 border border-line-strong p-4 lg:p-6"
        >
          <p class="tracking-[2px] text-accent" :aria-label="`Note du client : ${review.stars} sur 5`">
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
        <span class="lg:hidden">Bon à savoir</span>
        <span class="hidden lg:inline">Bon à savoir avant de réserver</span>
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

      <p class="mt-6 text-[15px] text-ink-2">
        Une autre question ?
        <a :href="whatsappLink" target="_blank" rel="noopener" class="text-link text-ink">Écrivez-nous sur WhatsApp</a>
        ou <NuxtLink :to="contactLink" class="text-link text-ink">par le formulaire de contact</NuxtLink>, nous répondons 24h/24.
      </p>
    </div>
  </section>
</template>
