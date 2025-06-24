<template>
  <AppLayout title="Unauthorized">
    <div class="flex flex-col items-center justify-center min-h-[60vh] py-12">
      <div class="text-7xl font-extrabold text-yellow-500 mb-4">401</div>
      <h1 class="text-3xl font-bold mb-2 text-gray-800 dark:text-gray-100">Unauthorized</h1>
      <p class="mb-6 text-lg text-gray-600 dark:text-gray-300">Your session has expired or you are not authorized. Please log in again.</p>
      <Link href="/login" class="btn btn-primary">Login</Link>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
onMounted(() => {
  setTimeout(() => {
    window.location.href = '/login';
  }, 3000);
});
</script>
<style scoped>
.btn {
  @apply px-6 py-2 rounded font-semibold shadow transition-colors duration-150;
}
.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}
</style> 