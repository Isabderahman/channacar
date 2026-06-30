export default defineNuxtPlugin(() => {
  useAdminAuth().initFromStorage()
})
