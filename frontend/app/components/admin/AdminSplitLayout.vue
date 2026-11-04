<template>
  <div class="grid grid-cols-[minmax(0,1fr)] gap-6 xl:grid-cols-[minmax(0,1.08fr)_minmax(0,0.92fr)]">
    <div class="min-w-0 space-y-6">
      <slot name="primary" />
    </div>

    <div class="min-w-0 space-y-6">
      <slot name="secondary" />
    </div>
  </div>
</template>
