
<template>
  <app-layout>
    <PageHeader title="Receive Stock Transfer" :button="{ text: 'Back', link: '/stock-transfers' }" />
 <div class="min-h-screen bg-gradient-to-br from-purple-900 to-gray-900 flex items-center justify-center p-6">
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
          <span class="text-white font-medium w-full sm:w-1/2">{{ item.product?.name }}</span>
          <div class="flex items-center gap-2 w-full sm:w-1/2">
            <input
              v-model.number="item.received_quantity"
              :max="item.quantity"
              min="0"
              type="number"
              class="w-24 px-3 py-1 rounded bg-white text-gray-800 border border-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
            <span class="text-black">/ {{ item.quantity }}</span>
          </div>
        </div>

        <div class="mt-6">
          <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow-lg transition">
            Mark as Received
          </button>
        </div>
      </form>
    </div>
  </div>
  </app-layout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PageHeader from '@/components/ui/PageHeader.vue';

const props = defineProps({
  transfer: Object,
})

const items = ref((props.transfer?.items ?? []).map(item => ({
  ...item,
  received_quantity: item.quantity,
})))

const form = useForm({
  items: items.value.map(({ id, received_quantity }) => ({ id, received_quantity })),
})
function submit() {
  router.post(`/stock-transfers/${props.transfer.id}/receive`, {
    items: items.value.map(({ id, received_quantity }) => ({ id, received_quantity }))
  });
}
</script> 