<template>
  <div class="min-h-screen bg-gray-100 py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-800">Print Receipt</h2>
          <div class="space-x-4">
            <button @click="printReceipt" class="btn btn-primary print-button">
              Print Receipt
            </button>
            <Link href="/pos" class="btn btn-secondary">
              Back to POS
            </Link>
          </div>
        </div>
        <div class="receipt-preview">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Receipt Preview</h3>
          <div class="bg-white border rounded-lg shadow-sm">
            <SalesReceipt 
              :sale="sale" 
              :template="selectedTemplate"
              :show-back-side="showBackSide"
              ref="receiptComponent" 
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import SalesReceipt from '@/components/SalesReceipt.vue';

const props = defineProps({
  sale: {
    type: Object,
    required: true
  }
});
function printReceipt() {
  window.print();
}


const receiptComponent = ref(null);
const selectedTemplate = ref('default');
const showBackSide = ref(false);
</script>
