<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Items" :button="{ text: 'Add Item', link: '/items/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search items..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Options</th>
              <th class="px-4 py-2 text-left">Category</th>
              <th class="px-4 py-2 text-left">SKU</th>
              <th class="px-4 py-2 text-left">Price</th>
              <th class="px-4 py-2 text-left">Cost</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-100">
              <td class="px-4 py-2">{{ item.name }}</td>
              <td class="px-4 py-2">{{ item.optionsString || '-' }}</td>
              <td class="px-4 py-2">{{ item.category?.name || '-' }}</td>
              <td class="px-4 py-2">{{ item.sku }}</td>
              <td class="px-4 py-2">{{ item.price ?? '-' }}</td>
              <td class="px-4 py-2">{{ item.cost ?? '-' }}</td>
              <td class="px-4 py-2 flex gap-2">
                <Button size="sm" variant="outline" @click="showItem(item)">View</Button>
                <Button size="sm" variant="outline" @click="editItem(item)">Edit</Button>
                <Button size="sm" variant="destructive" @click="deleteItem(item)">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-end">
          <!-- <Pagination :links="items.links" /> -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/PageHeader.vue';

const items = computed(() => usePage().props.items || []);
const search = ref('');

const handleSearch = () => {
  router.get('/items', { search: search.value }, { preserveState: true, replace: true });
};

const editItem = (item) => router.visit(`/items/${item.id}/edit`);
const showItem = (item) => router.visit(`/items/${item.id}`);
const deleteItem = (item) => {
  Swal.fire({
    title: 'Delete Item?',
    text: 'Are you sure you want to delete this item?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/items/${item.id}`);
    }
  });
};
</script>
<style scoped>
table th, table td { white-space: nowrap; }
</style> 