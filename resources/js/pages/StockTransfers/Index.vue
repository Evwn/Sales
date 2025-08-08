<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Stock Transfers" :button="{ text: 'Add Stock Transfer', link: '/stock-transfers/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search stock transfers..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <div class="overflow-x-auto ">
      <table class="min-w-full divide-y divide-gray-200 overflow-hidden rounded-lg shadow ">
        <thead class="bg-[#B76E79]/80 backdrop-blur-md">
          <tr>
           <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
            Reference</th>
            <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
              From Store</th>
            <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
              To Store</th>
            <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
              Status</th>
            <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
              Notes</th>
            <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
              Actions</th>
          </tr>
        </thead>
        <tbody class="backdrop-blur-sm bg-white/60 divide-y divide-gray-200">
          <tr v-for="transfer in filteredTransfers" :key="transfer.id" class="hover:bg-gray-50">
            <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.reference }}</td> 
            <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.from_store?.name }}</td>
            <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.to_store?.name }}</td>
            <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.status }}</td>
            <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.notes }}</td>
            <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <button @click="goToReceive(transfer.id)" v-if="transfer.status === 'pending'">Receive</button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
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
  transfers: Array,
  businesses: Array,
  branches: Array,
  stores: Array
});
const selectedBusiness = ref('');
const selectedBranch = ref('');
const selectedStore = ref('');
const search = ref('');
const handleSearch = () => {/* ... */};
const filteredBranches = computed(() => {
  if (!selectedBusiness.value) return props.branches;
  return props.branches.filter(b => b.business_id == selectedBusiness.value);
});
const filteredStores = computed(() => {
  let stores = props.stores;
  if (selectedBusiness.value) stores = stores.filter(s => s.business_id == selectedBusiness.value);
  if (selectedBranch.value) stores = stores.filter(s => s.branch_id == selectedBranch.value);
  return stores;
});
const filteredTransfers = computed(() => {
  let transfers = props.transfers;
  if (selectedBusiness.value) transfers = transfers.filter(t => t.from_store?.business_id == selectedBusiness.value || t.to_store?.business_id == selectedBusiness.value);
  if (selectedBranch.value) transfers = transfers.filter(t => t.from_store?.branch_id == selectedBranch.value || t.to_store?.branch_id == selectedBranch.value);
  if (selectedStore.value) transfers = transfers.filter(t => t.from_store?.id == selectedStore.value || t.to_store?.id == selectedStore.value);
  return transfers;
});
function goToCreate() {
  router.visit('/stock-transfers/create');
}
function goToReceive(id) {
  router.visit(`/stock-transfers/${id}/receive`);
}
</script> 