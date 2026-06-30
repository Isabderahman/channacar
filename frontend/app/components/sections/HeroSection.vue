<script setup lang="ts">
import { computed } from 'vue'
import HeroSearchBar from '~/components/sections/HeroSearchBar.vue'
import type { HeroSearchField } from '~/utils/home-content'

interface Props {
  actionLabel?: string
  description: string
  fields: HeroSearchField[]
  id?: string
  image: string
  title: string
}

const props = withDefaults(defineProps<Props>(), {
  actionLabel: 'Search',
  id: 'home',
})

const emit = defineEmits<{
  search: [payload: Record<string, string>]
}>()

const { localeProperties } = useI18n()
const localePath = useLocalePath()

const isRtl = computed(() => localeProperties.value.dir === 'rtl')

const layoutOffsetClass = computed(() =>
  isRtl.value ? 'lg:pl-[18rem] xl:pl-[42rem]' : 'lg:pr-[18rem] xl:pr-[42rem]',
)

const imagePositionClass = computed(() =>
  isRtl.value ? 'left-0 bg-right' : 'right-0 bg-left',
)

const handleSearch = (payload: Record<string, string>) => {
  emit('search', payload)
  void navigateTo({
    path: localePath('/cars'),
    query: payload,
  })
}
</script>

<template>
  <section
    v-intro
    :id="id"
    class="relative overflow-hidden pb-[50px] pt-[120px] lg:min-h-[85vh] lg:pt-[140px] xl:min-h-screen"
  >
    <div class="section-shell">
      <div class="relative lg:min-h-[560px]">
        <div :class="layoutOffsetClass">
          <div class="max-w-[20ch]">
            <h1 data-intro class="ridex-title-1">
              {{ title }}
            </h1>
          </div>

          <p data-intro class="mb-[30px] mt-[15px] max-w-[48ch] text-[var(--independence)] leading-8">
            {{ description }}
          </p>
        </div>

        <div data-intro class="relative z-20">
          <HeroSearchBar
            :fields="fields"
            :action-label="actionLabel"
            @search="handleSearch"
          />
        </div>

        <div
          data-intro-image
          class="absolute bottom-[50px] top-[20px] hidden w-[min(41vw,630px)] rounded-[30px] bg-cover shadow-[var(--shadow-1)] lg:block"
          :class="imagePositionClass"
          :style="{ backgroundImage: `url(${image})` }"
        />
      </div>
    </div>
  </section>
</template>
