<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead>
        <tr>
          <th class="px-4 py-2 text-left">Date</th>
          <th class="px-4 py-2 text-left">Business</th>
          <th class="px-4 py-2 text-left">Branch</th>
          <th class="px-4 py-2 text-left">Seller</th>
          <th class="px-4 py-2 text-left">Products</th>
          <th class="px-4 py-2 text-left">Amount</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50">
          <td class="px-4 py-2">{{ formatDate(sale.created_at) }}</td>
          <td class="px-4 py-2">{{ sale.business?.name || '—' }}</td>
          <td class="px-4 py-2">{{ sale.branch?.name || '—' }}</td>
          <td class="px-4 py-2">{{ sale.seller?.name || '—' }}</td>
          <td class="px-4 py-2">
            <span v-if="sale.items && sale.items.length">
              {{ sale.items.map(i => i.product?.name + ' x' + i.quantity).join(', ') }}
            </span>
            <span v-else>—</span>
          </td>
          <td class="px-4 py-2">KES {{ Number(sale.amount).toFixed(2) }}</td>
          <td class="px-4 py-2">{{ sale.status }}</td>
          <td class="px-4 py-2 flex gap-2">
            <a :href="`/sales/${sale.id}`" class="text-blue-600 hover:underline">View</a>
            <a :href="`/sales/${sale.id}/receipt/pdf`" class="text-green-600 hover:underline" target="_blank">PDF</a>
            <button class="text-gray-500 hover:text-black" @click="$emit('expand', sale.id)">Expand</button>
          </td>
        </tr>
        <tr v-if="!sales || sales.length === 0">
          <td colspan="8" class="text-center py-4 text-gray-400">No sales found</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script setup>
const props = defineProps({
  sales: { type: Array, default: () => [] }
});
function formatDate(date) {
  return new Date(date).toLocaleDateString();
}
</script> 