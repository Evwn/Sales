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
          Features
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="saveFeatures" class="space-y-8">
              
              <!-- Core Features -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Core Features</h3>
                <div class="space-y-6">
                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Inventory Management</h4>
                          <p class="text-sm text-gray-500">Track stock levels, manage items and variants</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.inventoryManagement" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Point of Sale (POS)</h4>
                          <p class="text-sm text-gray-500">Process sales transactions and manage orders</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.pos" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Customer Management</h4>
                          <p class="text-sm text-gray-500">Manage customer profiles and loyalty programs</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.customerManagement" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Advanced Features -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Advanced Features</h3>
                <div class="space-y-6">
                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Multi-location Support</h4>
                          <p class="text-sm text-gray-500">Manage multiple business locations</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.multiLocation" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Kitchen Display System</h4>
                          <p class="text-sm text-gray-500">Digital order management for kitchen staff</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.kitchenDisplay" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Online Ordering</h4>
                          <p class="text-sm text-gray-500">Accept orders through website and mobile app</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.onlineOrdering" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Delivery Management</h4>
                          <p class="text-sm text-gray-500">Track and manage delivery orders</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.deliveryManagement" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Analytics & Reporting -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Analytics & Reporting</h3>
                <div class="space-y-6">
                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Sales Analytics</h4>
                          <p class="text-sm text-gray-500">Detailed sales reports and insights</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.salesAnalytics" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Inventory Reports</h4>
                          <p class="text-sm text-gray-500">Stock level reports and alerts</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.inventoryReports" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Employee Performance</h4>
                          <p class="text-sm text-gray-500">Track employee sales and performance</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.employeePerformance" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Integrations -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Integrations</h3>
                <div class="space-y-6">
                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Payment Gateway Integration</h4>
                          <p class="text-sm text-gray-500">Connect with payment processors</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.paymentIntegration" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Accounting Software</h4>
                          <p class="text-sm text-gray-500">Sync with QuickBooks, Xero, etc.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.accountingIntegration" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-start space-x-4">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">Third-party Delivery</h4>
                          <p class="text-sm text-gray-500">Integrate with Uber Eats, DoorDash, etc.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input v-model="features.deliveryIntegration" type="checkbox" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
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
                  {{ saving ? 'Saving...' : 'Save Features' }}
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

const features = ref({
  // Core Features
  inventoryManagement: true,
  pos: true,
  customerManagement: true,
  
  // Advanced Features
  multiLocation: false,
  kitchenDisplay: false,
  onlineOrdering: false,
  deliveryManagement: false,
  
  // Analytics & Reporting
  salesAnalytics: true,
  inventoryReports: true,
  employeePerformance: false,
  
  // Integrations
  paymentIntegration: true,
  accountingIntegration: false,
  deliveryIntegration: false,
});

const saveFeatures = async () => {
  saving.value = true;
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    Swal.fire({
      icon: 'success',
      title: 'Features Updated!',
      text: 'Your feature settings have been saved successfully.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save features. Please try again.',
    });
  } finally {
    saving.value = false;
  }
};
</script> 