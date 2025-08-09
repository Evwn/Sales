<template>
  <AppLayout>            
    <!-- Header -->
    <PageHeader title="Purchase Items" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            
            <!-- Search & Filters -->
            <div class="flex flex-wrap gap-4 mb-4 mt-4 bg-white/50 hover:bg-white/50 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 backdrop-blur sm:rounded-lg p-4">
              
              <!-- Search -->
              <div class="flex-1  hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                <Input
                  v-model="search"
                  placeholder="Search purchase items, ref, location ..."
                  class="w-full"
                />
              </div>

              <!-- Location Filter -->
              <div >
                <select v-model="locationFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All Locations</option>
                  <option 
                    v-for="loc in uniqueLocations" 
                    :key="loc" 
                    :value="loc"
                  >
                    {{ loc }}
                  </option>
                </select>
              </div>

              <!-- Status Filter -->
              <div>
                <select v-model="statusFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="received">Received</option>
                  <option value="partially_received">Partially Received</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>

            </div>

            <!-- Table -->
            <div v-if="filteredItems.length" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 overflow-hidden rounded-lg shadow">
                <thead class="bg-[#B76E79]/80 backdrop-blur-md">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Purchase Ref</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Item Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Qty Ordered</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Qty Received</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Purchase Cost (each)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Additional Cost</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="backdrop-blur-sm bg-white/60 divide-y divide-gray-200">
                  <tr 
                    v-for="(item, index) in filteredItems" 
                    :key="item.id" 
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.purchase?.reference }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.item?.name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.stock_item?.location?.name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.quantity_ordered }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.quantity_received }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Ksh {{ item.purchase_cost }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Ksh {{ item.proportional_additional_cost }}</td>
                    <td class="px-6 py-4 text-sm">
                      <span 
                        :class="{
                          'px-3 py-1 rounded-full text-xs font-semibold': true,
                          'bg-green-100 text-green-800': item.status === 'received',
                          'bg-yellow-100 text-yellow-800': item.status === 'pending',
                          'bg-red-100 text-red-800': item.status === 'cancelled',
                          'bg-yellow-100 text-green-700': item.status === 'partially_received',                        }"
                      >
                        {{ formatStatus(item.status) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-6 text-gray-500">
              No purchase items found.
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/PageHeader.vue';

const props = defineProps({
  items: { type: Array, default: () => [] }
});

const search = ref('');
const locationFilter = ref('');
const statusFilter = ref('');

// Get unique locations for filter dropdown
const uniqueLocations = computed(() => {
  const locations = props.items.map(i => i.stock_item?.location?.name).filter(Boolean);
  return [...new Set(locations)];
});

const filteredItems = computed(() => {
  const term = search.value.trim().toLowerCase();
  
  return props.items.filter(item => {
    const matchesSearch = !term || 
      (item.item?.name || '').toLowerCase().includes(term) ||
      (item.purchase?.reference || '').toLowerCase().includes(term) ||
      (item.stock_item?.location?.name || '').toLowerCase().includes(term) ||
      (item.status || '').toLowerCase().includes(term);

    const matchesLocation = !locationFilter.value || item.stock_item?.location?.name === locationFilter.value;
    const matchesStatus = !statusFilter.value || item.status === statusFilter.value;

    return matchesSearch && matchesLocation && matchesStatus;
  });
});

const formatStatus = (status) => {
  if (status === 'partially_received') return 'Partial';
  return status.charAt(0).toUpperCase() + status.slice(1);
};
</script>
