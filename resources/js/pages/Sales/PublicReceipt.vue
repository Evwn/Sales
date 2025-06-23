<template>
  <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center py-8">
    <div class="w-full max-w-md bg-white rounded shadow p-4">
      <SalesReceipt :sale="sale" />
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import SalesReceipt from '@/components/SalesReceipt.vue';

const props = defineProps({
  sale: Object
});
</script>

<style scoped>
.bg-gray-100 {
  background: #f7fafc;
}
@media print {
  .min-h-screen, .bg-gray-100, .max-w-md, .rounded, .shadow, .p-4 {
    background: white !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    width: 100vw !important;
    min-height: 0 !important;
  }
}
</style> 
