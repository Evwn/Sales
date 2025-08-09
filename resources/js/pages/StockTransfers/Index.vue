<template>
  <AppLayout>
    <PageHeader title="Stock Transfers" :button="{ text: 'Add Stock Transfer', link: '/stock-transfers/create' }" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">

            <!-- Search & Filters -->
            <div class="flex flex-wrap gap-4 mb-4 mt-4 bg-white/50 hover:bg-white/50 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 backdrop-blur sm:rounded-lg p-4">

              <!-- Search -->
              <div class="flex-1 hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                <Input
                  v-model="search"
                  placeholder="Search stock transfers, ref, from store, to store..."
                  class="w-full"
                />
              </div>

              <!-- From Store Filter -->
              <div>
                <select v-model="fromStoreFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All From Stores</option>
                  <option
                    v-for="store in props.stores"
                    :key="store.id"
                    :value="store.id"
                  >
                    {{ store.name }}
                  </option>
                </select>
              </div>

              <!-- To Store Filter -->
              <div>
                <select v-model="toStoreFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All To Stores</option>
                  <option
                    v-for="store in props.stores"
                    :key="store.id"
                    :value="store.id"
                  >
                    {{ store.name }}
                  </option>
                </select>
              </div>

              <!-- Status Filter -->
              <div>
                <select v-model="statusFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="partially_received">Partially Received</option>
                  <option value="completed">Received</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>

            </div>

            <!-- Table -->
            <div v-if="filteredTransfers.length" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 overflow-hidden rounded-lg shadow">
                <thead class="bg-[#B76E79]/80 backdrop-blur-md">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">From Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">To Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="backdrop-blur-sm bg-white/60 divide-y divide-gray-200">
                  <tr
                    v-for="transfer in filteredTransfers"
                    :key="transfer.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.reference }}</td> 
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.from_store?.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.to_store?.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <span
                        :class="{
                          'px-3 py-1 rounded-full text-xs font-semibold': true,
                          'bg-green-100 text-green-800': transfer.status === 'completed',
                          'bg-yellow-100 text-yellow-800': transfer.status === 'pending',
                          'bg-red-100 text-red-800': transfer.status === 'cancelled',
                          'bg-yellow-100 text-green-700': transfer.status === 'partially_received',
                        }"
                      >
                        {{ transfer.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transfer.notes }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <button @click="goToReceive(transfer.id)" v-if="transfer.status === 'pending' || transfer.status === 'partially_received'" class="text-blue-600 hover:text-blue-900">Receive</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-6 text-gray-500">
              No stock transfers found.
            </div>

          </div>
        </div>
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
  stores: Array
});

const search = ref('');
const fromStoreFilter = ref('');
const toStoreFilter = ref('');
const statusFilter = ref('');

const filteredTransfers = computed(() => {
  const term = search.value.trim().toLowerCase();
  return props.transfers.filter(t => {
    const matchesSearch = !term ||
      (t.reference || '').toLowerCase().includes(term) ||
      (t.from_store?.name || '').toLowerCase().includes(term) ||
      (t.to_store?.name || '').toLowerCase().includes(term);
    const matchesFromStore = !fromStoreFilter.value || t.from_store?.id == fromStoreFilter.value;
    const matchesToStore = !toStoreFilter.value || t.to_store?.id == toStoreFilter.value;
    const matchesStatus = !statusFilter.value || t.status === statusFilter.value;
    return matchesSearch && matchesFromStore && matchesToStore && matchesStatus;
  });
});

function goToCreate() {
  router.visit('/stock-transfers/create');
}

function goToReceive(id) {
  router.visit(`/stock-transfers/${id}/receive`);
}
</script>
