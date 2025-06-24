<template>
  <AppLayout title="Bad Request">
    <div class="flex flex-col items-center justify-center min-h-[60vh] py-12">
      <div class="text-7xl font-extrabold text-blue-500 mb-4">400</div>
      <h1 class="text-3xl font-bold mb-2 text-gray-800 dark:text-gray-100">Bad Request</h1>
      <p class="mb-6 text-lg text-gray-600 dark:text-gray-300">The request could not be understood by the server. Please check your input or try again.</p>
      <button class="btn btn-primary" @click="goBack">Go Back</button>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
function goBack() {
  window.history.back();
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