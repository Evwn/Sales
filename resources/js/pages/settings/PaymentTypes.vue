<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center space-x-4">
        <Link href="/settings" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Payment Types
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="savePaymentTypes" class="space-y-8">
              
              <!-- Cash Payment -->
              <div class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-green-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Cash</h3>
                      <p class="text-sm text-gray-500">Accept cash payments</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="paymentTypes.cash.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                <div v-if="paymentTypes.cash.enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Display Name
                    </label>
                    <input 
                      v-model="paymentTypes.cash.displayName" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Cash"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Sort Order
                    </label>
                    <input 
                      v-model="paymentTypes.cash.sortOrder" 
                      type="number" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="1"
                    />
                  </div>
                </div>
              </div>

              <!-- Card Payment -->
              <div class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Card Payment</h3>
                      <p class="text-sm text-gray-500">Accept credit and debit cards</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="paymentTypes.card.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                <div v-if="paymentTypes.card.enabled" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Display Name
                      </label>
                      <input 
                        v-model="paymentTypes.card.displayName" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Card"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sort Order
                      </label>
                      <input 
                        v-model="paymentTypes.card.sortOrder" 
                        type="number" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="2"
                      />
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Gateway
                      </label>
                      <select 
                        v-model="paymentTypes.card.gateway" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Select Gateway</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                        <option value="square">Square</option>
                        <option value="flutterwave">Flutterwave</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Transaction Fee (%)
                      </label>
                      <input 
                        v-model="paymentTypes.card.transactionFee" 
                        type="number" 
                        step="0.01" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="2.9"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Mobile Money -->
              <div class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Mobile Money</h3>
                      <p class="text-sm text-gray-500">Accept mobile money payments</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="paymentTypes.mobileMoney.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                <div v-if="paymentTypes.mobileMoney.enabled" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Display Name
                      </label>
                      <input 
                        v-model="paymentTypes.mobileMoney.displayName" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Mobile Money"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sort Order
                      </label>
                      <input 
                        v-model="paymentTypes.mobileMoney.sortOrder" 
                        type="number" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="3"
                      />
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Provider
                      </label>
                      <select 
                        v-model="paymentTypes.mobileMoney.provider" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Select Provider</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="airtel">Airtel Money</option>
                        <option value="orange">Orange Money</option>
                        <option value="mtn">MTN Mobile Money</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Transaction Fee (%)
                      </label>
                      <input 
                        v-model="paymentTypes.mobileMoney.transactionFee" 
                        type="number" 
                        step="0.01" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="1.0"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bank Transfer -->
              <div class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-indigo-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Bank Transfer</h3>
                      <p class="text-sm text-gray-500">Accept bank transfers</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="paymentTypes.bankTransfer.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                <div v-if="paymentTypes.bankTransfer.enabled" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Display Name
                      </label>
                      <input 
                        v-model="paymentTypes.bankTransfer.displayName" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Bank Transfer"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sort Order
                      </label>
                      <input 
                        v-model="paymentTypes.bankTransfer.sortOrder" 
                        type="number" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="4"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Bank Account Details
                    </label>
                    <textarea 
                      v-model="paymentTypes.bankTransfer.accountDetails" 
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Enter bank account details for customers..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Save Button -->
              <div class="flex justify-end pt-6">
                <button 
                  type="submit" 
                  class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :disabled="saving"
                >
                  {{ saving ? 'Saving...' : 'Save Payment Types' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

const saving = ref(false);

const paymentTypes = ref({
  cash: {
    enabled: true,
    displayName: 'Cash',
    sortOrder: 1,
  },
  card: {
    enabled: true,
    displayName: 'Card',
    sortOrder: 2,
    gateway: 'stripe',
    transactionFee: 2.9,
  },
  mobileMoney: {
    enabled: false,
    displayName: 'Mobile Money',
    sortOrder: 3,
    provider: 'mpesa',
    transactionFee: 1.0,
  },
  bankTransfer: {
    enabled: false,
    displayName: 'Bank Transfer',
    sortOrder: 4,
    accountDetails: '',
  },
});

const savePaymentTypes = async () => {
  saving.value = true;
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    Swal.fire({
      icon: 'success',
      title: 'Payment Types Updated!',
      text: 'Your payment type settings have been saved successfully.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save payment types. Please try again.',
    });
  } finally {
    saving.value = false;
  }
};
</script> 