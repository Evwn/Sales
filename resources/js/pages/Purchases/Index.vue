<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Purchases" :button="{ text: 'Add Purchase', link: '/purchases/create' }" />
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search purchases..." class="w-full" @keyup.enter="handleSearch" />
        </div>
        <!-- Add more filters here if needed -->
      </div>
      <!-- Table -->
      <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">Order #</th>
              <th class="px-4 py-2 text-left">Supplier</th>
              <th class="px-4 py-2 text-left">Location</th>
              <th class="px-4 py-2 text-left">Status</th>
              <th class="px-4 py-2 text-left">Order Date</th>
              <th class="px-4 py-2 text-left">Expected Date</th>
              <th class="px-4 py-2 text-left">Total Cost</th>
              <th class="px-4 py-2 text-left">Received</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 font-semibold text-blue-700 cursor-pointer hover:underline" @click="showPurchase(purchase)">
                {{ purchase.reference || purchase.id }}
              </td>
              <td class="px-4 py-2">{{ purchase.supplier?.name }}</td>
              <td class="px-4 py-2">
                {{ purchase.location?.name }}
                <span v-if="purchase.location?.location_type?.name"> ({{ purchase.location.location_type.name }})</span>
              </td>
              <td class="px-4 py-2">
                <span :class="statusClass(purchase.status)">{{ purchase.status }}</span>
              </td>
              <td class="px-4 py-2">{{ formatDate(purchase.order_date) }}</td>
              <td class="px-4 py-2">{{ formatDate(purchase.expected_date) }}</td>
              <td class="px-4 py-2">{{ formatCurrency(purchase.total_cost) }}</td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                  <span class="text-xs">{{ purchase.total_received }}/{{ purchase.total_ordered }}</span>
                  <div class="w-24 h-2 bg-gray-200 rounded">
                    <div
                      class="h-2 rounded"
                      :class="{
                        'bg-green-500': purchase.status !== 'completed' && purchase.status !== 'received',
                        'bg-gray-400': purchase.status === 'completed' || purchase.status === 'received',
                      }"
                      :style="{ width: (purchase.total_ordered ? (purchase.total_received / purchase.total_ordered) * 100 : 0) + '%' }"
                    ></div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/PageHeader.vue';

const purchases = ref([]);
const filters = ref({ status: '' });
const search = ref('');
const handleSearch = () => {
  // Implement search logic here
};

const fetchPurchases = () => {
  router.get('/purchases', filters.value, {
    preserveState: true,
    onSuccess: (page) => {
      purchases.value = page.props.purchases;
    },
  });
};

onMounted(fetchPurchases);

const showPurchase = (purchase) => router.visit(`/purchases/${purchase.id}`);

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};
const formatCurrency = (amount) => {
  if (amount == null) return '-';
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount);
};
const statusClass = (status) => {
  if (status === 'cancelled') return 'text-red-600 font-bold';
  if (status === 'completed' || status === 'received') return 'text-green-600 font-bold';
  if (status === 'draft') return 'text-gray-400';
  return '';
};
</script>
<style scoped>
.btn { @apply px-2 py-1 rounded font-semibold shadow text-xs; }
.btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300; }
.btn-outline { @apply border border-gray-400 text-gray-700 hover:bg-gray-100; }
.btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
</style> 