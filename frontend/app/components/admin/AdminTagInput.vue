<script setup lang="ts">
// Free-form tags for a car ("Nouveau", "Promo", "Familiale"). Type and press
// Enter (or a comma) to add; click a suggestion to add it; × removes. Limits
// mirror Car::MAX_TAGS / Car::MAX_TAG_LENGTH on the backend.
const MAX_TAGS = 8
const MAX_LENGTH = 30

/** Offered even before the fleet has any tags of its own. */
const DEFAULT_SUGGESTIONS = ['Nouveau', 'Promo', 'Populaire', 'Économique', 'Familiale', 'Luxe', '4x4', 'Automatique']

const props = withDefaults(
  defineProps<{
    label?: string
    name?: string
    /** Tags already used on other cars. */
    suggestions?: string[]
  }>(),
  { label: 'Tags', name: 'tags', suggestions: () => [] },
)

const model = defineModel<string[]>({ default: () => [] })
const draft = ref('')

const normalize = (value: string) => value.replace(/\s+/g, ' ').trim().slice(0, MAX_LENGTH)
const has = (tag: string) => model.value.some((item) => item.toLowerCase() === tag.toLowerCase())
const isFull = computed(() => model.value.length >= MAX_TAGS)

const add = (raw: string) => {
  const tag = normalize(raw)

  if (!tag || has(tag) || isFull.value) {
    return
  }

  model.value = [...model.value, tag]
}

const remove = (tag: string) => {
  model.value = model.value.filter((item) => item !== tag)
}

const commitDraft = () => {
  draft.value.split(',').forEach(add)
  draft.value = ''
}

const onKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter' || event.key === ',') {
    event.preventDefault()
    commitDraft()
  } else if (event.key === 'Backspace' && draft.value === '' && model.value.length > 0) {
    model.value = model.value.slice(0, -1)
  }
}

const available = computed(() => {
  const query = draft.value.trim().toLowerCase()
  const seen = new Set<string>()

  return [...props.suggestions, ...DEFAULT_SUGGESTIONS]
    .filter((tag) => {
      const key = tag.toLowerCase()

      if (seen.has(key) || has(tag)) {
        return false
      }

      seen.add(key)

      return !query || key.includes(query)
    })
    .slice(0, 12)
})
</script>

<template>
  <div>
    <label :for="`field-${name}`" class="mono-label mb-2 block">{{ label }}</label>

    <div class="field flex min-h-11 flex-wrap items-center gap-1.5 py-1.5 focus-within:outline-2 focus-within:-outline-offset-1 focus-within:outline-accent">
      <span
        v-for="tag in model"
        :key="tag"
        class="inline-flex items-center gap-1 bg-surface-2 py-1 ps-2 pe-1 font-plex text-[12px] text-ink"
      >
        {{ tag }}
        <button
          type="button"
          class="grid size-5 place-items-center text-muted transition-colors hover:text-ink"
          :aria-label="`Remove tag ${tag}`"
          @click="remove(tag)"
        >
          <svg aria-hidden="true" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 6l12 12M18 6L6 18" /></svg>
        </button>
      </span>

      <input
        :id="`field-${name}`"
        v-model="draft"
        type="text"
        :name="name"
        :maxlength="MAX_LENGTH"
        :disabled="isFull"
        :placeholder="isFull ? '' : model.length ? 'Add another…' : 'Type a tag, press Enter'"
        autocomplete="off"
        class="min-w-[9rem] flex-1 bg-transparent py-1 text-[15px] outline-none placeholder:text-faint"
        @keydown="onKeydown"
        @blur="commitDraft"
      >
    </div>

    <div v-if="available.length && !isFull" class="mt-2 flex flex-wrap gap-1.5">
      <button
        v-for="tag in available"
        :key="tag"
        type="button"
        class="border border-dashed border-line-strong px-2 py-1 font-plex text-[11px] text-ink-2 transition-colors hover:border-ink hover:text-ink"
        @mousedown.prevent
        @click="add(tag)"
      >
        + {{ tag }}
      </button>
    </div>

    <p class="mt-1.5 text-[13px] text-muted">
      Shown as labels on the public fleet list and car page. Up to {{ MAX_TAGS }} tags.
    </p>
  </div>
</template>
