<template>
  <AppLayout title="Page Not Found">
    <div class="flex flex-col items-center justify-center min-h-[60vh] py-12">
      <div class="text-7xl font-extrabold text-red-500 mb-4">404</div>
      <h1 class="text-3xl font-bold mb-2 text-gray-800 dark:text-gray-100">Page Not Found</h1>
      <p class="mb-6 text-lg text-gray-600 dark:text-gray-300">Sorry, the page you are looking for does not exist or has been moved.</p>
      <div class="flex gap-4">
        <Link href="/dashboard" class="btn btn-primary">Dashboard</Link>
        <Link href="/" class="btn btn-secondary">Home</Link>
        <Link href="/login" class="btn btn-outline">Login</Link>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>
<style scoped>
.btn {
  @apply px-6 py-2 rounded font-semibold shadow transition-colors duration-150;
}
.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}
.btn-secondary {
  @apply bg-gray-200 text-gray-800 hover:bg-gray-300;
}
.btn-outline {
  @apply border border-blue-600 text-blue-600 hover:bg-blue-50;
}
</style> 