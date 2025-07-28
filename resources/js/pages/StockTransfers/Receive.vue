<template>
  <div>
    <h1>Receive Stock Transfer</h1>
    <form @submit.prevent="submit">
      <div v-for="(item, idx) in items" :key="item.id">
        <span>{{ item.product?.name }}</span>
        <input v-model.number="item.received_quantity" type="number" :max="item.quantity" min="0" />
        <span>/ {{ item.quantity }}</span>
      </div>
      <button type="submit">Mark as Received</button>
    </form>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({
  transfer: Object,
  items: Array
});
function submit() {
  router.post(`/stock-transfers/${props.transfer.id}/receive`, { items: props.items });
}
</script> 