<template>
  <AppLayout>
    <Head title="Businesses" />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Businesses
        </h2>
        <Link
          href="/businesses/create"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Add Business
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th scope="col" class="w-1/3 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="w-1/3 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th scope="col" class="w-1/3 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="business in businesses" :key="business.id" class="hover:bg-gray-50">
                    <td class="w-1/3 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ business.name }}</td>
                    <td class="w-1/3 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ business.description }}</td>
                    <td class="w-1/3 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div class="flex space-x-3">
                        <Link
                          :href="`/businesses/${business.id}`"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          View
                        </Link>
                        <Link
                          :href="`/businesses/${business.id}/edit`"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Edit
                        </Link>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="businesses.length === 0" class="hover:bg-gray-50">
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                      No businesses found.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

interface Business {
  id: number;
  name: string;
  description: string | null;
  created_at: string;
  updated_at: string;
}

defineProps<{
  businesses: Business[];
}>();

defineOptions({
  name: 'Businesses'
});
</script> 