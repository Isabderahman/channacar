<script setup lang="ts">
// Shimmer placeholder block. Size/shape come from utility classes passed by the
// parent (e.g. <BaseSkeleton class="h-4 w-32 rounded-full" />). Respects
// prefers-reduced-motion and adapts to both light and dark themes via tokens.
</script>

<template>
  <div class="cc-skeleton" aria-hidden="true" />
</template>

<style scoped>
.cc-skeleton {
  position: relative;
  overflow: hidden;
  background: color-mix(in srgb, var(--text-strong) 9%, transparent);
}

.cc-skeleton::after {
  content: '';
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(
    90deg,
    transparent 0%,
    color-mix(in srgb, var(--text-strong) 13%, transparent) 50%,
    transparent 100%
  );
  animation: cc-skeleton-shimmer 1.4s ease-in-out infinite;
}

[dir='rtl'] .cc-skeleton::after {
  animation-name: cc-skeleton-shimmer-rtl;
}

@keyframes cc-skeleton-shimmer {
  100% {
    transform: translateX(100%);
  }
}

@keyframes cc-skeleton-shimmer-rtl {
  100% {
    transform: translateX(-200%);
  }
}

@media (prefers-reduced-motion: reduce) {
  .cc-skeleton::after {
    animation: none;
  }
}
</style>
