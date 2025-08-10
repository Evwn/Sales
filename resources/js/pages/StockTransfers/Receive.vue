
<template>
  <app-layout title="Stock Transfer">
    <PageHeader title="Receive Stock Transfer" :button="{ text: 'Back', link: '/stock-transfers' }" />
 <div class="min-h-screen bg-gradient-to-br from-white/20 to-gray-900 flex items-center justify-center p-6">
    <div class="w-full max-w-2xl backdrop-blur-md bg-white/10 border border-purple-500 rounded-2xl shadow-xl p-8">
      <h1 class="text-3xl font-bold text-white/80 mb-6">Receive Stock Transfer</h1>

      <div class="mb-6 text-black">
        <p><span class="font-semibold">Reference:</span> {{ transfer.reference }}</p>
        <p><span class="font-semibold">From:</span> {{ transfer.from_store?.name?? 'N/A' }}</p>
        <p><span class="font-semibold">To:</span> {{ transfer.to_store?.name?? 'N/A' }}</p>
        <p><span class="font-semibold">Status:</span> {{ transfer.status }}</p>
      </div>

      <form @submit.prevent="submit">
        <div v-for="(item, idx) in items" :key="item.id" class="mb-4 flex flex-col sm:flex-row items-start sm:items-center gap-3 bg-white/10 p-4 rounded-xl border border-purple-300">
          <span class="text-white font-medium w-full sm:w-1/2">{{ items.stockItem.item?.name }}</span>
          <div class="flex items-center gap-2 w-full sm:w-1/2">
            <input
              v-model.number="item.received_quantity"
              :max="item.quantity"
              min="0"
              type="number"
              class="w-24 px-3 py-1 rounded bg-white text-gray-800 border border-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
            <span class="text-black">/ {{ item.quantity }}</span>
            <span v-if="item.received_quantity > item.remaining_quantity" class="text-red-500 text-xs">
              Cannot receive more than {{ item.remaining_quantity }}
            </span>

          </div>
        </div>

       <div class="mt-6 flex gap-4">
            <button
              type="submit"
              :disabled="hasInvalid"
              class="bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg font-semibold shadow-lg transition"
            >
              Mark as Received
            </button>
            <!-- or
            <button
              type="button"
              @click="returnRemaining"
              class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold shadow-lg transition"
            >
              Return the Items
            </button> -->
          </div>
      </form>
    </div>
  </div>
  </app-layout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed ,watch } from 'vue';

const props = defineProps({
  transfer: Object,
});

// Build items array with remaining_quantity for each
const items = ref(
  (props.transfer?.items ?? []).map(item => {
    const remaining_quantity = parseFloat(item.quantity);
    return {
      ...item,
      remaining_quantity,
      received_quantity: remaining_quantity, // Default to max remaining
    };
  })
);

// Validation: prevent negative or too large quantities
const hasInvalid = computed(() =>
  items.value.some(item =>
    item.received_quantity < 0 ||
    item.received_quantity > item.remaining_quantity
  )
);
items.value.forEach((item, index) => {
  watch(
    () => item.received_quantity,
    (newVal, oldVal) => {
      if (newVal > item.remaining_quantity) {
        item.received_quantity = item.remaining_quantity;
      }
      if (newVal < 0 || newVal === '') {
        item.received_quantity = 0;
      }
    }
  );
});

// Submit: mark as received
function submit() {
  if (hasInvalid.value) return;
  router.post(`/stock-transfers/${props.transfer.id}/receive`, {
    items: items.value.map(({ id, received_quantity }) => ({ id, received_quantity }))
  });
}

// Return remaining items
function returnRemaining() {
  router.post(`/stock-transfers/${props.transfer.id}/return-remaining`);
}
</script>
