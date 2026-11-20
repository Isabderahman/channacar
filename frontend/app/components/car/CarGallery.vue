<script setup lang="ts">
import type { Car } from '~/types/entities'
import { carFullName } from '~/utils/car-display'

// Main photo + thumbnails (a grid on desktop, a scrolling strip on mobile).
const props = defineProps<{ car: Car }>()

const { mediaUrl } = useApi()

const images = computed(() => props.car.images ?? [])
const active = ref(0)
const name = computed(() => carFullName(props.car))
const current = computed(() => images.value[active.value])
const counter = computed(
  () => `${String(active.value + 1).padStart(2, '0')} / ${String(images.value.length).padStart(2, '0')}`,
)
</script>

<template>
  <div>
    <div class="photo-frame relative h-[260px] overflow-hidden sm:h-[400px] lg:h-[460px] xl:h-[560px]">
      <img
        v-if="current"
        :src="mediaUrl(current.path)"
        :alt="`${name} — photo ${active + 1} sur ${images.length}`"
        fetchpriority="high"
        class="size-full object-cover"
      >
      <span v-else class="absolute bottom-4 start-5 font-plex text-[11px] text-muted lg:bottom-6 lg:start-6 lg:text-[12px]">
        {{ name }}
      </span>

      <span
        v-if="images.length > 1"
        class="absolute bottom-3.5 end-5 bg-bg/80 px-2 py-1 font-plex text-[11px] text-ink lg:bottom-6 lg:end-6 lg:text-[12px]"
      >
        {{ counter }}
      </span>
    </div>

    <div
      v-if="images.length > 1"
      role="group"
      aria-label="Choisir une photo"
      class="mt-1.5 flex gap-1.5 overflow-x-auto px-5 [scrollbar-width:none] lg:mt-2 lg:grid lg:grid-cols-4 lg:gap-2 lg:overflow-visible lg:px-0"
    >
      <button
        v-for="(image, index) in images"
        :key="image.id"
        type="button"
        class="photo-frame h-14 w-[82px] shrink-0 overflow-hidden border transition-colors lg:h-[88px] lg:w-auto"
        :class="index === active ? 'border-ink' : 'border-transparent opacity-70 hover:opacity-100'"
        :aria-pressed="index === active ? 'true' : 'false'"
        :aria-label="`Photo ${index + 1} sur ${images.length}`"
        @click="active = index"
      >
        <img :src="mediaUrl(image.path)" alt="" loading="lazy" class="size-full object-cover">
      </button>
    </div>
  </div>
</template>
