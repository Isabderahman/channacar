<template>
  <div class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
    <div class="space-y-6">
      <slot name="primary" />
    </div>

    <div class="space-y-6">
      <slot name="secondary" />
    </div>
  </div>
</template>
