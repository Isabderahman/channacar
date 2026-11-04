<script setup lang="ts">
// Line icons for the redesigned public site (1.6 stroke, as in the mockups).
// Directional arrows mirror automatically in RTL (Arabic).
export type SiteIconName =
  | 'arrow-right'
  | 'arrow-left'
  | 'arrow-up-right'
  | 'arrow-down'
  | 'menu'
  | 'close'
  | 'phone'
  | 'whatsapp'

const props = withDefaults(
  defineProps<{
    name: SiteIconName
    size?: number
  }>(),
  { size: 18 },
)

const paths: Record<SiteIconName, string> = {
  'arrow-right': 'M4 12h16M14 6l6 6-6 6',
  'arrow-left': 'M20 12H4M10 6l-6 6 6 6',
  'arrow-up-right': 'M7 17L17 7M9 7h8v8',
  'arrow-down': 'M12 4v16M6 14l6 6 6-6',
  menu: 'M4 9h16M4 15h16',
  close: 'M6 6l12 12M18 6L6 18',
  phone: 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z',
  whatsapp: 'M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.6-5.2A8.5 8.5 0 1 1 21 11.5z',
}

const mirrored = new Set<SiteIconName>(['arrow-right', 'arrow-left', 'arrow-up-right'])
</script>

<template>
  <svg
    aria-hidden="true"
    :width="props.size"
    :height="props.size"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linejoin="round"
    class="shrink-0"
    :class="mirrored.has(props.name) ? 'rtl:-scale-x-100' : ''"
  >
    <path :d="paths[props.name]" />
  </svg>
</template>
