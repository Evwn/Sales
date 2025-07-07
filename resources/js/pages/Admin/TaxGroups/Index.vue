<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Tax Codes</h1>
    <div class="mb-4">
      <Link href="/admin/tax-groups/create" class="bg-blue-600 text-white px-4 py-2 rounded">Add Tax Group</Link>
    </div>
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
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
const props = defineProps({ taxGroups: Array });

function deleteTaxGroup(id) {
  if (confirm('Are you sure you want to delete this tax group?')) {
    router.delete(`/admin/tax-groups/${id}`);
  }
}
</script> 