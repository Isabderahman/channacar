<script setup lang="ts">
import { watch } from 'vue'
import AdminAuthGate from '~/components/admin/AdminAuthGate.vue'
import AdminSidebar from '~/components/admin/AdminSidebar.vue'
import AdminTopbar from '~/components/admin/AdminTopbar.vue'

const route = useRoute()
const sidebarOpen = ref(false)

watch(
  () => route.fullPath,
  () => {
    sidebarOpen.value = false
  },
)
</script>

<template>
  <div class="min-h-screen bg-[var(--background-canvas)]">
    <AdminAuthGate>
      <div class="min-h-screen lg:flex">
        <AdminSidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <div class="min-h-screen flex-1 lg:pl-[18rem]">
          <AdminTopbar @toggle-sidebar="sidebarOpen = true" />

          <main class="px-4 pb-8 pt-24 md:px-6 xl:px-8">
            <slot />
          </main>
        </div>
      </div>
    </AdminAuthGate>
  </div>
</template>
