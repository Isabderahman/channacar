<script setup lang="ts">
import AppPanel from '~/components/data/AppPanel.vue'
import StateNotice from '~/components/feedback/StateNotice.vue'
import FormField from '~/components/forms/FormField.vue'
import FormSelect from '~/components/forms/FormSelect.vue'
import MarketingHeroSection from '~/components/public/MarketingHeroSection.vue'
import PublicCarCard from '~/components/public/PublicCarCard.vue'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import type { Car, Category, PaginatedResponse, ResourceResponse } from '~/types/entities'
import { FUEL_OPTIONS, TRANSMISSION_OPTIONS } from '~/utils/constants'

const { publicApi } = useApi()

const pending = ref(false)
const errorMessage = ref('')
const cars = ref<Car[]>([])
const categories = ref<Category[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)

const filters = reactive({
  category_id: '',
  fuel: '',
  max_price: '',
  min_price: '',
  search: '',
  seats: '',
  transmission: '',
})

const categoryOptions = computed(() =>
  categories.value.map((category) => ({
    label: category.name,
    value: category.id,
  })),
)

const heroFeatures = computed(() => [
  {
    icon: 'calendar' as const,
    title: 'Réservation rapide',
    description: 'Filtrez, comparez, puis envoyez votre demande sans quitter la page.',
  },
  {
    icon: 'car' as const,
    title: `${categories.value.length || 0} catégories disponibles`,
    description: 'Citadines, familiales, SUV et modèles premium pour différents budgets.',
  },
])

const loadCategories = async () => {
  const response = await publicApi<ResourceResponse<Category[]>>('/public/categories')
  categories.value = response.data
}

const loadCars = async (page = 1) => {
  pending.value = true
  errorMessage.value = ''

  try {
    const response = await publicApi<PaginatedResponse<Car>>('/public/cars', {
      query: {
        page,
        per_page: 12,
        ...Object.fromEntries(
          Object.entries(filters).filter(([, value]) => String(value).trim().length > 0),
        ),
      },
    })

    cars.value = response.data
    currentPage.value = response.current_page
    lastPage.value = response.last_page
    total.value = response.total
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'La liste des véhicules est indisponible pour le moment.'
  } finally {
    pending.value = false
  }
}

const resetFilters = async () => {
  filters.category_id = ''
  filters.fuel = ''
  filters.max_price = ''
  filters.min_price = ''
  filters.search = ''
  filters.seats = ''
  filters.transmission = ''

  await loadCars(1)
}

onMounted(async () => {
  await loadCategories()
  await loadCars()
})

useSeoMeta({
  title: 'Réserver une voiture | ChanaaCar',
  description: 'Parcourez la flotte ChanaaCar avec filtres, pagination et réservation rapide.',
})
</script>

<template>
  <div>
    <MarketingHeroSection
      eyebrow="Flotte disponible"
      title="Trouvez la voiture adaptée à votre séjour."
      subtitle="Parcourez les véhicules disponibles, filtrez selon votre budget et réservez plus vite depuis une seule interface."
      :primary-action="{ label: 'Contacter ChanaaCar', to: '/contact' }"
      :features="heroFeatures"
    >
      <template #meta>
        <div class="grid gap-3 sm:grid-cols-3">
          <div class="group flex items-center gap-3 rounded-[20px] border border-[var(--surface-border)] bg-[linear-gradient(160deg,var(--surface-2),var(--surface-1))] px-4 py-4 transition duration-500 ease-out hover:-translate-y-1 hover:border-[var(--primary-border)] hover:shadow-[var(--shadow-2)]">
            <span class="grid size-10 shrink-0 place-items-center rounded-[12px] bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)] transition duration-500 ease-out group-hover:scale-105">
              <BaseIcon name="car" :size="18" />
            </span>
            <div class="min-w-0">
              <p class="text-[0.62rem] uppercase tracking-[0.08em] leading-tight text-[var(--text-subtle)]">
                Véhicules trouvés
              </p>
              <p class="mt-1.5 font-heading text-[1.45rem] leading-none text-[var(--text-strong)]">
                {{ total }}
              </p>
            </div>
          </div>
          <div class="group flex items-center gap-3 rounded-[20px] border border-[var(--surface-border)] bg-[linear-gradient(160deg,var(--surface-2),var(--surface-1))] px-4 py-4 transition duration-500 ease-out hover:-translate-y-1 hover:border-[var(--primary-border)] hover:shadow-[var(--shadow-2)]">
            <span class="grid size-10 shrink-0 place-items-center rounded-[12px] bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)] transition duration-500 ease-out group-hover:scale-105">
              <BaseIcon name="filter" :size="18" />
            </span>
            <div class="min-w-0">
              <p class="text-[0.62rem] uppercase tracking-[0.08em] leading-tight text-[var(--text-subtle)]">
                Page actuelle
              </p>
              <p class="mt-1.5 font-heading text-[1.45rem] leading-none text-[var(--text-strong)]">
                {{ currentPage }} / {{ lastPage }}
              </p>
            </div>
          </div>
          <div class="group flex items-center gap-3 rounded-[20px] border border-[var(--surface-border)] bg-[linear-gradient(160deg,var(--surface-2),var(--surface-1))] px-4 py-4 transition duration-500 ease-out hover:-translate-y-1 hover:border-[var(--primary-border)] hover:shadow-[var(--shadow-2)]">
            <span class="grid size-10 shrink-0 place-items-center rounded-[12px] bg-[var(--primary-soft)] text-[var(--primary)] shadow-[inset_0_0_0_1px_var(--primary-border)] transition duration-500 ease-out group-hover:scale-105">
              <BaseIcon name="clock" :size="18" />
            </span>
            <div class="min-w-0">
              <p class="text-[0.62rem] uppercase tracking-[0.08em] leading-tight text-[var(--text-subtle)]">
                Assistance
              </p>
              <p class="mt-1.5 font-heading text-[1.45rem] leading-none text-[var(--text-strong)]">
                7j/7
              </p>
            </div>
          </div>
        </div>
      </template>
    </MarketingHeroSection>

    <section class="section-shell pb-12">
      <AppPanel v-reveal padding="lg">
        <div class="mb-5 flex items-center gap-3">
          <BaseIcon name="filter" :size="18" class="text-[var(--primary)]" />
          <h2 class="font-heading text-[1.2rem] text-[var(--text-strong)]">Filtres</h2>
        </div>

        <form class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="loadCars(1)">
          <FormField
            v-model="filters.search"
            name="search"
            label="Recherche"
            placeholder="Marque ou modèle"
            variant="hero"
          />
          <FormSelect
            v-model="filters.category_id"
            name="category"
            label="Catégorie"
            :options="categoryOptions"
            variant="hero"
          />
          <FormSelect
            v-model="filters.transmission"
            name="transmission"
            label="Transmission"
            :options="[...TRANSMISSION_OPTIONS]"
            variant="hero"
          />
          <FormSelect
            v-model="filters.fuel"
            name="fuel"
            label="Carburant"
            :options="[...FUEL_OPTIONS]"
            variant="hero"
          />
          <FormField
            v-model="filters.seats"
            name="seats"
            label="Places min"
            type="number"
            min="1"
            variant="hero"
          />
          <FormField
            v-model="filters.min_price"
            name="min_price"
            label="Prix min / jour"
            type="number"
            min="0"
            variant="hero"
          />
          <FormField
            v-model="filters.max_price"
            name="max_price"
            label="Prix max / jour"
            type="number"
            min="0"
            variant="hero"
          />

          <div class="flex items-end gap-3 xl:col-span-1">
            <BaseButton type="submit">
              <BaseIcon name="search" :size="18" />
              <span>Appliquer</span>
            </BaseButton>
            <BaseButton type="button" variant="secondary" @click="resetFilters">
              <BaseIcon name="refresh" :size="18" />
              <span>Réinitialiser</span>
            </BaseButton>
          </div>
        </form>
      </AppPanel>

      <div v-reveal class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-[0.92rem] text-[var(--text-muted)]">
          {{ cars.length }} véhicule(s) affiché(s) sur {{ total }} disponible(s).
        </p>

        <div class="flex items-center gap-3">
          <BaseButton
            type="button"
            variant="secondary"
            :disabled="currentPage <= 1 || pending"
            @click="loadCars(currentPage - 1)"
          >
            <BaseIcon name="arrow-right" :size="18" class="rotate-180" />
            <span>Précédent</span>
          </BaseButton>
          <span class="text-[0.88rem] text-[var(--text-subtle)]">
            Page {{ currentPage }} / {{ lastPage }}
          </span>
          <BaseButton
            type="button"
            variant="secondary"
            :disabled="currentPage >= lastPage || pending"
            @click="loadCars(currentPage + 1)"
          >
            <span>Suivant</span>
            <BaseIcon name="arrow-right" :size="18" />
          </BaseButton>
        </div>
      </div>

      <StateNotice
        v-if="errorMessage"
        class="mt-6"
        title="Liste indisponible"
        :message="errorMessage"
        tone="error"
      />

      <div v-else-if="pending" class="mt-6">
        <StateNotice
          title="Chargement des véhicules"
          message="Récupération de la flotte et des résultats filtrés."
        />
      </div>

      <div
        v-else-if="cars.length"
        :key="`cars-page-${currentPage}`"
        v-reveal="{ stagger: 0.08, y: 32, start: 'top 92%' }"
        class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3"
      >
        <PublicCarCard
          v-for="car in cars"
          :key="car.id"
          :car="car"
        />
      </div>

      <div v-else class="mt-6">
        <StateNotice
          title="Aucun véhicule trouvé"
          message="Ajustez les filtres ou réinitialisez la recherche pour revoir toute la flotte."
        />
      </div>
    </section>
  </div>
</template>
