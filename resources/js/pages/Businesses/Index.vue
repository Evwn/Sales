<template>
  <AppLayout title="Businesses">
    <template #header>
      <div class="flex justify-between items-center px-4 sm:px-6 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Businesses
        </h2>
        <Link
          :href="route('businesses.create')"
          class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
          >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Business
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div v-if="businesses.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No businesses</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new business.</p>
              <div class="mt-6">
                <Link
                  :href="route('businesses.create')"
                  class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Create Business
                </Link>
              </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="business in businesses"
                :key="business.id"
                class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg"
              >
                <div class="p-6">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <img
                        v-if="business.logo_path"
                        :src="business.logo_url"
                        :alt="business.name"
                        class="h-12 w-12 rounded-full object-cover"
                      />
                      <div
                        v-else
                        class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                      >
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ business.name }}
                      </h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ business.industry || 'No industry specified' }}
                      </p>
                    </div>
                  </div>

                  <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ business.description || 'No description provided' }}
                    </p>
                  </div>

                  <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ business.full_address }}
                    </p>
                  </div>

                  <div class="mt-6 flex justify-end space-x-3">
                    <Link
                      :href="route('businesses.edit', business)"
                      class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                      Edit
                    </Link>
                    <Link
                      :href="route('businesses.show', business)"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                      View Details
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  businesses: {
    type: Array,
    required: true
  }
});

defineOptions({
  name: 'Businesses'
});
</script> 