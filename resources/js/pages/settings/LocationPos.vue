<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center space-x-4">
        <Link href="/settings" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Location & POS Settings</h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="space-y-8">
              <h3 class="text-lg font-medium text-gray-900">Location & POS Configuration</h3>
              <p class="text-gray-600">Location and POS settings will be configured here.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script> 