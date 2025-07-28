<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/PageHeader.vue';

const categories = computed(() => usePage().props.categories || []);
const search = ref('');

const handleSearch = () => {
  router.get('/categories', { search: search.value }, { preserveState: true, replace: true });
};

const editCategory = (category) => router.visit(`/categories/${category.id}/edit`);
const showCategory = (category) => router.visit(`/categories/${category.id}`);
const deleteCategory = (category) => {
  Swal.fire({
    title: 'Delete Category?',
    text: 'Are you sure you want to delete this category?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/categories/${category.id}`);
    }
  });
};
</script>

<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Categories" :button="{ text: 'Add Category', link: '/categories/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search categories..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Description</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-100">
              <td class="px-4 py-2">{{ category.name }}</td>
              <td class="px-4 py-2">{{ category.description }}</td>
              <td class="px-4 py-2 flex gap-2">
                <Button size="sm" variant="outline" @click="showCategory(category)">View</Button>
                <Button size="sm" variant="outline" @click="editCategory(category)">Edit</Button>
                <Button size="sm" variant="destructive" @click="deleteCategory(category)">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-end">
          <!-- <Pagination :links="categories.links" /> -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
table th, table td { white-space: nowrap; }
</style> 