<template>
  <AppLayout title="Server Error">
    <div class="flex flex-col items-center justify-center min-h-[60vh] py-12">
      <div class="text-7xl font-extrabold text-gray-700 mb-4">500</div>
      <h1 class="text-3xl font-bold mb-2 text-gray-800 dark:text-gray-100">Server Error</h1>
      <p class="mb-6 text-lg text-gray-600 dark:text-gray-300">Something went wrong on our end. Please try again later or contact support if the problem persists.</p>
      <button class="btn btn-primary" @click="reload">Reload</button>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
function reload() {
  window.location.reload();
}
</script>
<style scoped>
.btn {
  @apply px-6 py-2 rounded font-semibold shadow transition-colors duration-150;
}
.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}
</style> 