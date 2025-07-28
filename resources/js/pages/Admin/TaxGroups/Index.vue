<template>
<AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tax Codes
                </h2>
                <Link
                    href="/admin/tax-groups/create"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Add Tax Group
                </Link>
            </div>
        </template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full bg-white border">
          <thead>
            <tr>
              <th class="px-4 py-2 border">Code</th>
              <th class="px-4 py-2 border">Description</th>
              <th class="px-4 py-2 border">Rate (%)</th>
              <th class="px-4 py-2 border">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tax in taxGroups" :key="tax.id">
              <td class="px-4 py-2 border">{{ tax.code }}</td>
              <td class="px-4 py-2 border">{{ tax.description }}</td>
              <td class="px-4 py-2 border">{{ tax.rate }}</td>
              <td class="px-4 py-2 border">
                <Link :href="`/admin/tax-groups/${tax.id}/edit`" class="text-blue-600 hover:underline mr-2">Edit</Link>
                <button @click="deleteTaxGroup(tax.id)" class="text-red-600 hover:underline">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
  </div>
</AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
const props = defineProps({ taxGroups: Array });

function deleteTaxGroup(id) {
  if (confirm('Are you sure you want to delete this tax group?')) {
    router.delete(`/admin/tax-groups/${id}`);
  }
}
</script> 