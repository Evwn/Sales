<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Suppliers" :button="{ text: 'Add Supplier', link: '/suppliers/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search suppliers..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <div class="mt-6 bg-white shadow rounded-lg p-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="supplier in suppliers" :key="supplier.id" class="hover:bg-blue-50 transition">
              <td class="px-4 py-2">{{ supplier.name }}</td>
              <td class="px-4 py-2">{{ supplier.email }}</td>
              <td class="px-4 py-2">{{ supplier.phone }}</td>
              <td class="px-4 py-2">{{ supplier.address }}</td>
              <td class="px-4 py-2">
                <span :class="supplier.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded text-xs font-semibold">
                  {{ supplier.status ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-2">
                <button @click="goToEdit(supplier.id)" class="bg-gray-100 hover:bg-blue-100 text-blue-700 px-3 py-1 rounded transition">Edit</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!suppliers.length" class="text-center text-gray-400 py-8">No suppliers found.</div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/PageHeader.vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({ suppliers: Array });
const search = ref('');
const handleSearch = () => {
  // Implement search logic here
};
function goToCreate() {
  router.visit('/suppliers/create');
}
function goToEdit(id) {
  router.visit(`/suppliers/${id}/edit`);
}
</script> 