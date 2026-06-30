<script setup lang="ts">
import BrandLogo from '~/components/common/BrandLogo.vue'
import BaseIcon from '~/components/ui/BaseIcon.vue'
import { marketingFooterInfo, marketingNavLinks } from '~/utils/marketing-content'

const localePath = useLocalePath()

const quickLinks = computed(() =>
  marketingNavLinks.map((link) => ({
    ...link,
    to: localePath(link.to),
  })),
)
</script>

<template>
  <footer class="border-t border-[var(--surface-border)] bg-[var(--surface-1)]">
    <div class="section-shell py-10">
      <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div>
          <BrandLogo brand-name="ChanaaCar" tagline="location de voiture" :to="localePath('/')" />
          <p class="mt-4 max-w-[54ch] text-[0.95rem] leading-7 text-[var(--text-muted)]">
            Votre partenaire de confiance pour la location de voiture à Marrakech et partout au Maroc.
          </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <p class="font-heading text-[1rem] text-[var(--text-strong)]">Liens rapides</p>
            <ul class="mt-3 space-y-2 text-[0.92rem] text-[var(--text-muted)]">
              <li v-for="link in quickLinks" :key="link.to">
                <NuxtLink :to="link.to">{{ link.label }}</NuxtLink>
              </li>
            </ul>
          </div>

          <div>
            <p class="font-heading text-[1rem] text-[var(--text-strong)]">Informations</p>
            <ul class="mt-3 space-y-2 text-[0.92rem] text-[var(--text-muted)]">
              <li v-for="item in marketingFooterInfo" :key="item">{{ item }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="mt-8 flex flex-wrap items-center justify-between gap-4 rounded-[20px] border border-[var(--surface-border)] bg-[var(--surface-2)] px-5 py-4 text-[0.88rem] text-[var(--text-subtle)]">
        <p>© 2026 ChanaaCar. Tous droits réservés.</p>
        <div class="flex items-center gap-2 text-[var(--primary)]">
          <BaseIcon name="check-circle" :size="16" />
          <span>Assistance 24h/24 et réservation rapide.</span>
        </div>
      </div>
    </div>
  </footer>
</template>
