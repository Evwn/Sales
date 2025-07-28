<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/PageHeader.vue';

const discounts = computed(() => usePage().props.discounts || []);
const search = ref('');

const handleSearch = () => {
  router.get('/discounts', { search: search.value }, { preserveState: true, replace: true });
};

const editDiscount = (discount) => router.visit(`/discounts/${discount.id}/edit`);
const showDiscount = (discount) => router.visit(`/discounts/${discount.id}`);
const deleteDiscount = (discount) => {
  Swal.fire({
    title: 'Delete Discount?',
    text: 'Are you sure you want to delete this discount?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/discounts/${discount.id}`);
    }
  });
};
</script>

<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Discounts" :button="{ text: 'Add Discount', link: '/discounts/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search discounts..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Type</th>
              <th class="px-4 py-2 text-left">Value</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="discount in discounts" :key="discount.id" class="hover:bg-gray-100">
              <td class="px-4 py-2">{{ discount.name }}</td>
              <td class="px-4 py-2">{{ discount.type }}</td>
              <td class="px-4 py-2">{{ discount.value }}</td>
              <td class="px-4 py-2 flex gap-2">
                <Button size="sm" variant="outline" @click="showDiscount(discount)">View</Button>
                <Button size="sm" variant="outline" @click="editDiscount(discount)">Edit</Button>
                <Button size="sm" variant="destructive" @click="deleteDiscount(discount)">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-end">
          <!-- <Pagination :links="discounts.links" /> -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
table th, table td { white-space: nowrap; }
</style> 
