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
          Loyalty Program
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="saveLoyalty" class="space-y-8">
              
              <!-- Program Settings -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Program Settings</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Enable Loyalty Program</h4>
                      <p class="text-sm text-gray-500">Turn on customer loyalty features</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="loyalty.enabled" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  
                  <div v-if="loyalty.enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Program Name
                      </label>
                      <input 
                        v-model="loyalty.programName" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Rewards Program"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Points Currency Name
                      </label>
                      <input 
                        v-model="loyalty.pointsName" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Points"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Earning Rules -->
              <div v-if="loyalty.enabled" class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Earning Rules</h3>
                <div class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Points per Currency Unit
                      </label>
                      <input 
                        v-model="loyalty.pointsPerCurrency" 
                        type="number" 
                        step="0.01" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="1.0"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Minimum Purchase for Points
                      </label>
                      <input 
                        v-model="loyalty.minimumPurchase" 
                        type="number" 
                        step="0.01" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Points on Discounted Items</h4>
                      <p class="text-sm text-gray-500">Award points on items with discounts</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="loyalty.pointsOnDiscounted" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Redemption Rules -->
              <div v-if="loyalty.enabled" class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Redemption Rules</h3>
                <div class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Points Value (Currency)
                      </label>
                      <input 
                        v-model="loyalty.pointsValue" 
                        type="number" 
                        step="0.01" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.01"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Minimum Points for Redemption
                      </label>
                      <input 
                        v-model="loyalty.minimumRedemption" 
                        type="number" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="100"
                      />
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Allow Partial Redemption</h4>
                      <p class="text-sm text-gray-500">Let customers use partial points</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="loyalty.allowPartialRedemption" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Tiers -->
              <div v-if="loyalty.enabled" class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Tiers</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Enable Customer Tiers</h4>
                      <p class="text-sm text-gray-500">Different benefits for different customer levels</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="loyalty.enableTiers" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  
                  <div v-if="loyalty.enableTiers" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                          Bronze Tier (Points)
                        </label>
                        <input 
                          v-model="loyalty.tiers.bronze" 
                          type="number" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="0"
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                          Silver Tier (Points)
                        </label>
                        <input 
                          v-model="loyalty.tiers.silver" 
                          type="number" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="1000"
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                          Gold Tier (Points)
                        </label>
                        <input 
                          v-model="loyalty.tiers.gold" 
                          type="number" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="5000"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notifications -->
              <div v-if="loyalty.enabled" class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notifications</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Points Earned Notification</h4>
                      <p class="text-sm text-gray-500">Notify customers when they earn points</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="loyalty.notifications.pointsEarned" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Tier Upgrade Notification</h4>
                      <p class="text-sm text-gray-500">Notify customers when they upgrade tiers</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="loyalty.notifications.tierUpgrade" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Points Expiry Warning</h4>
                      <p class="text-sm text-gray-500">Warn customers before points expire</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="loyalty.notifications.pointsExpiry" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
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
                  {{ saving ? 'Saving...' : 'Save Loyalty Settings' }}
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

const loyalty = ref({
  enabled: false,
  programName: 'Rewards Program',
  pointsName: 'Points',
  pointsPerCurrency: 1.0,
  minimumPurchase: 0.00,
  pointsOnDiscounted: false,
  pointsValue: 0.01,
  minimumRedemption: 100,
  allowPartialRedemption: true,
  enableTiers: false,
  tiers: {
    bronze: 0,
    silver: 1000,
    gold: 5000,
  },
  notifications: {
    pointsEarned: true,
    tierUpgrade: true,
    pointsExpiry: true,
  },
});

const saveLoyalty = async () => {
  saving.value = true;
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    Swal.fire({
      icon: 'success',
      title: 'Loyalty Settings Saved!',
      text: 'Your loyalty program settings have been updated successfully.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save loyalty settings. Please try again.',
    });
  } finally {
    saving.value = false;
  }
};
</script> 