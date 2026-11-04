<script setup lang="ts">
import SiteIcon from '~/components/site/SiteIcon.vue'

const route = useRoute()
const submitted = ref(false)

const form = reactive({
  date_depart: '',
  date_retour: '',
  email: '',
  lieu_retour: '',
  lieu_prise_en_charge: '',
  message: '',
  nom_complet: '',
  telephone: '',
})

// When the visitor arrives from a car detail page (?vehicule=...), pre-fill the
// message so their enquiry states which car it is about.
const vehicule = computed(() => {
  const value = route.query.vehicule
  return (Array.isArray(value) ? value[0] : value)?.trim() || ''
})

watch(
  vehicule,
  (value) => {
    if (value && !form.message) {
      form.message = `Bonjour ChanaaCar, je souhaite des informations sur la ${value}. Est-elle disponible pour mes dates ?`
    }
  },
  { immediate: true },
)

const submit = () => {
  submitted.value = true
}
</script>

<template>
  <div>
    <div class="border-b border-ink pb-5 md:pb-8">
      <h2 class="text-[32px] font-medium leading-none tracking-[-0.035em] lg:text-[40px]">
        Envoyer ma demande
      </h2>
      <p class="mt-4 max-w-[52ch] text-[15px] text-ink-2 lg:text-[16px]">
        Formulaire statique prêt pour un futur branchement API, email ou CRM.
      </p>
    </div>

    <form class="mt-6 flex flex-col gap-4 lg:mt-8" @submit.prevent="submit">
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="field-label">
          Nom complet
          <input v-model="form.nom_complet" class="field" name="nom_complet" type="text" autocomplete="name">
        </label>
        <label class="field-label">
          Téléphone
          <input v-model="form.telephone" class="field" name="telephone" type="tel" autocomplete="tel">
        </label>
        <label class="field-label">
          Email
          <input v-model="form.email" class="field" name="email" type="email" autocomplete="email">
        </label>
        <label class="field-label">
          Date de départ
          <input v-model="form.date_depart" class="field" name="date_depart" type="date">
        </label>
        <label class="field-label">
          Date de retour
          <input v-model="form.date_retour" class="field" name="date_retour" type="date">
        </label>
        <label class="field-label">
          Lieu de prise en charge
          <input v-model="form.lieu_prise_en_charge" class="field" name="lieu_prise_en_charge" type="text">
        </label>
        <label class="field-label sm:col-span-2">
          Lieu de retour
          <input v-model="form.lieu_retour" class="field" name="lieu_retour" type="text">
        </label>
        <label class="field-label sm:col-span-2">
          Message
          <textarea v-model="form.message" class="field" name="message" rows="5" />
        </label>
      </div>

      <div
        v-if="submitted"
        role="status"
        class="border border-line-strong px-4 py-3.5"
      >
        <p class="font-medium">Formulaire prêt</p>
        <p class="mt-1 text-[14px] text-ink-2">
          La mise en page statique est en place. Il reste à relier ce formulaire à un vrai endpoint si vous voulez une soumission réelle.
        </p>
      </div>

      <button type="submit" class="btn btn-primary min-h-14 w-full sm:w-auto sm:self-start sm:min-w-[280px]">
        Envoyer ma demande
        <SiteIcon name="arrow-right" />
      </button>
    </form>
  </div>
</template>
