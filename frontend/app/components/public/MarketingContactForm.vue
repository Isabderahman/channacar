<script setup lang="ts">
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormField from '~/components/forms/FormField.vue'
import FormTextarea from '~/components/forms/FormTextarea.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'

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
  <div class="ridex-surface p-6 md:p-7">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h2 class="ridex-title-2">
          Envoyer ma demande
        </h2>
        <p class="mt-3 max-w-[58ch] text-[0.96rem] leading-7 text-[var(--text-muted)]">
          Formulaire statique prêt pour un futur branchement API, email ou CRM.
        </p>
      </div>
      <BaseIcon name="mail" :size="20" class="text-[var(--primary)]" />
    </div>

    <form class="mt-6 space-y-4" @submit.prevent="submit">
      <div class="grid gap-4 md:grid-cols-2">
        <FormField v-model="form.nom_complet" name="nom_complet" label="Nom complet" />
        <FormField v-model="form.telephone" name="telephone" label="Téléphone" type="tel" />
        <FormField v-model="form.email" name="email" label="Email" type="email" />
        <FormField v-model="form.date_depart" name="date_depart" label="Date de départ" type="date" />
        <FormField v-model="form.date_retour" name="date_retour" label="Date de retour" type="date" />
        <FormField
          v-model="form.lieu_prise_en_charge"
          name="lieu_prise_en_charge"
          label="Lieu de prise en charge"
        />
        <FormField v-model="form.lieu_retour" name="lieu_retour" label="Lieu de retour" />
      </div>

      <FormTextarea
        v-model="form.message"
        name="message"
        label="Message"
        rows="5"
      />

      <StateNotice
        v-if="submitted"
        title="Formulaire prêt"
        message="La mise en page statique est en place. Il reste à relier ce formulaire à un vrai endpoint si vous voulez une soumission réelle."
        tone="success"
      />

      <BaseButton type="submit">
        <BaseIcon name="mail" :size="18" />
        <span>Envoyer ma demande</span>
      </BaseButton>
    </form>
  </div>
</template>
