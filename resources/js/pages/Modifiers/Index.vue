<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/PageHeader.vue';

const modifiers = computed(() => usePage().props.modifiers || []);
const search = ref('');

const handleSearch = () => {
  router.get('/modifiers', { search: search.value }, { preserveState: true, replace: true });
};

const editModifier = (modifier) => router.visit(`/modifiers/${modifier.id}/edit`);
const showModifier = (modifier) => router.visit(`/modifiers/${modifier.id}`);
const deleteModifier = (modifier) => {
  Swal.fire({
    title: 'Delete Modifier?',
    text: 'Are you sure you want to delete this modifier?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/modifiers/${modifier.id}`);
    }
  });
};
</script>

<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Modifiers" :button="{ text: 'Add Modifier', link: '/modifiers/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search modifiers..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Type</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="modifier in modifiers" :key="modifier.id" class="hover:bg-gray-100">
              <td class="px-4 py-2">{{ modifier.name }}</td>
              <td class="px-4 py-2">{{ modifier.type }}</td>
              <td class="px-4 py-2 flex gap-2">
                <Button size="sm" variant="outline" @click="showModifier(modifier)">View</Button>
                <Button size="sm" variant="outline" @click="editModifier(modifier)">Edit</Button>
                <Button size="sm" variant="destructive" @click="deleteModifier(modifier)">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-end">
          <!-- <Pagination :links="modifiers.links" /> -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
table th, table td { white-space: nowrap; }
</style>
