<template>
  <div class="min-h-screen bg-gray-100 py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-800">Print Receipt</h2>
          <div class="space-x-4">
            <button @click="window.print()" class="btn btn-primary print-button">
              Print Receipt
            </button>
            <Link href="/sales" class="btn btn-secondary">
              Back to Sale
            </Link>
          </div>
        </div>

        <!-- Receipt Options -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Receipt Options</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Template</label>
              <select v-model="selectedTemplate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="default">Default Template</option>
                <option value="compact">Compact Template</option>
                <option value="detailed">Detailed Template</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Print Options</label>
              <div class="mt-2 space-y-2">
                <label class="inline-flex items-center">
                  <input type="checkbox" v-model="showBackSide" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <span class="ml-2">Print Back Side</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Receipt Preview -->
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

const receiptComponent = ref(null);
const selectedTemplate = ref('default');
const showBackSide = ref(false);
</script>

<style scoped>
.receipt-preview {
  max-width: 80mm;
  margin: 0 auto;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 0.5rem;
}

@media print {
  body * {
    visibility: hidden;
  }
  .receipt, .receipt * {
    visibility: visible;
  }
  .receipt {
    position: absolute;
    left: 0;
    top: 0;
    width: 80mm !important;
  }
  .print-button {
    display: none !important;
  }
}
</style> 
