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
          Billing & Subscriptions
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="space-y-8">
              
              <!-- Current Plan -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Plan</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-lg font-semibold text-blue-900">Professional Plan</h4>
                      <p class="text-sm text-blue-700">$29.99/month</p>
                      <p class="text-xs text-blue-600 mt-1">Next billing: January 15, 2024</p>
                    </div>
                    <div class="text-right">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Billing Information -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Billing Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Company Name
                    </label>
                    <input 
                      v-model="billing.companyName" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Tax ID
                    </label>
                    <input 
                      v-model="billing.taxId" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Billing Email
                    </label>
                    <input 
                      v-model="billing.email" 
                      type="email" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Phone Number
                    </label>
                    <input 
                      v-model="billing.phone" 
                      type="tel" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Payment Method -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Method</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                      <div class="bg-blue-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-900">Visa ending in 4242</p>
                        <p class="text-xs text-gray-500">Expires 12/25</p>
                      </div>
                    </div>
                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                      Update
                    </button>
                  </div>
                </div>
              </div>

              <!-- Billing History -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Billing History</h3>
                <div class="space-y-3">
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="text-sm font-medium text-gray-900">December 15, 2023</p>
                      <p class="text-xs text-gray-500">Professional Plan</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm font-medium text-gray-900">$29.99</p>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                        Paid
                      </span>
                    </div>
                  </div>
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="text-sm font-medium text-gray-900">November 15, 2023</p>
                      <p class="text-xs text-gray-500">Professional Plan</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm font-medium text-gray-900">$29.99</p>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                        Paid
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Plan Management -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Plan Management</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-gray-900">Starter</h4>
                    <p class="text-2xl font-bold text-gray-900">$9.99<span class="text-sm font-normal text-gray-500">/month</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                      <li>• Up to 5 locations</li>
                      <li>• Basic reporting</li>
                      <li>• Email support</li>
                    </ul>
                    <button class="w-full mt-4 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                      Current Plan
                    </button>
                  </div>
                  <div class="border-2 border-blue-500 rounded-lg p-4 bg-blue-50">
                    <h4 class="text-lg font-semibold text-gray-900">Professional</h4>
                    <p class="text-2xl font-bold text-gray-900">$29.99<span class="text-sm font-normal text-gray-500">/month</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                      <li>• Up to 20 locations</li>
                      <li>• Advanced reporting</li>
                      <li>• Priority support</li>
                      <li>• API access</li>
                    </ul>
                    <button class="w-full mt-4 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                      Current Plan
                    </button>
                  </div>
                  <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-gray-900">Enterprise</h4>
                    <p class="text-2xl font-bold text-gray-900">$99.99<span class="text-sm font-normal text-gray-500">/month</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                      <li>• Unlimited locations</li>
                      <li>• Custom reporting</li>
                      <li>• 24/7 support</li>
                      <li>• White-label options</li>
                    </ul>
                    <button class="w-full mt-4 px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-medium hover:bg-gray-700">
                      Upgrade
                    </button>
                  </div>
                </div>
              </div>

              <!-- Save Button -->
              <div class="flex justify-end pt-6">
                <button 
                  @click="saveBilling" 
                  class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :disabled="saving"
                >
                  {{ saving ? 'Saving...' : 'Save Billing Info' }}
                </button>
              </div>
            </div>
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

const billing = ref({
  companyName: 'My Business Inc.',
  taxId: '12-3456789',
  email: 'billing@mybusiness.com',
  phone: '+1 (555) 123-4567',
});

const saveBilling = async () => {
  saving.value = true;
  
  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    Swal.fire({
      icon: 'success',
      title: 'Billing Updated!',
      text: 'Your billing information has been saved successfully.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save billing information. Please try again.',
    });
  } finally {
    saving.value = false;
  }
};
</script> 