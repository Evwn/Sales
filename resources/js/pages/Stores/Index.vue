<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Stores" :button="{ text: 'Add Store', link: '/stores/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search stores..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Business</th>
            <th>Branch</th>
            <th>Address</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="store in filteredStores" :key="store.id">
            <td>{{ store.name }}</td>
            <td>{{ store.business?.name || '-' }}</td>
            <td>{{ store.branch?.name || '-' }}</td>
            <td>{{ store.address }}</td>
            <td>{{ store.phone }}</td>
            <td>{{ store.status ? 'Active' : 'Inactive' }}</td>
            <td>
              <button @click="goToEdit(store.id)">Edit</button>
              <button @click="deleteStore(store.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/PageHeader.vue';

const props = defineProps({
  stores: Array,
  businesses: Array,
  branches: Array
});
const selectedBusiness = ref('');
const selectedBranch = ref('');
const search = ref('');
const handleSearch = () => {
  // Implement search logic here
};

const filteredBranches = computed(() => {
  if (!selectedBusiness.value) return props.branches;
  return props.branches.filter(b => b.business_id == selectedBusiness.value);
});
const filteredStores = computed(() => {
  let stores = props.stores;
  if (search.value) {
    stores = stores.filter(s =>
      s.name.toLowerCase().includes(search.value.toLowerCase()) ||
      s.address.toLowerCase().includes(search.value.toLowerCase()) ||
      s.phone.toLowerCase().includes(search.value.toLowerCase())
    );
  }
  if (selectedBusiness.value) stores = stores.filter(s => s.business_id == selectedBusiness.value);
  if (selectedBranch.value) stores = stores.filter(s => s.branch_id == selectedBranch.value);
  return stores;
});
function goToCreate() {
  router.visit('/stores/create');
}
function goToEdit(id) {
  router.visit(`/stores/${id}/edit`);
}
function deleteStore(id) {
  if (confirm('Delete this store?')) {
    router.delete(`/stores/${id}`);
  }
}
</script> 