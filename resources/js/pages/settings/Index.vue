<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Settings
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Settings Navigation -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              
              <!-- System Settings -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/system')">
                <div class="flex items-center space-x-3">
                  <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">System Settings</h3>
                    <p class="text-sm text-gray-600">General system configuration</p>
                  </div>
                </div>
              </div>

              <!-- Features -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/features')">
                <div class="flex items-center space-x-3">
                  <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Features</h3>
                    <p class="text-sm text-gray-600">Enable/disable features</p>
                  </div>
                </div>
              </div>

              <!-- Billing & Subscriptions -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/billing')">
                <div class="flex items-center space-x-3">
                  <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Billing & Subscriptions</h3>
                    <p class="text-sm text-gray-600">Manage billing and plans</p>
                  </div>
                </div>
              </div>

              <!-- Payment Types -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/payment-types')">
                <div class="flex items-center space-x-3">
                  <div class="bg-yellow-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Payment Types</h3>
                    <p class="text-sm text-gray-600">Configure payment methods</p>
                  </div>
                </div>
              </div>

              <!-- Loyalty -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/loyalty')">
                <div class="flex items-center space-x-3">
                  <div class="bg-red-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Loyalty</h3>
                    <p class="text-sm text-gray-600">Customer loyalty programs</p>
                  </div>
                </div>
              </div>

              <!-- Taxes -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/taxes')">
                <div class="flex items-center space-x-3">
                  <div class="bg-indigo-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Taxes</h3>
                    <p class="text-sm text-gray-600">Tax configuration</p>
                  </div>
                </div>
              </div>

              <!-- Receipt -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/receipt')">
                <div class="flex items-center space-x-3">
                  <div class="bg-teal-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Receipt</h3>
                    <p class="text-sm text-gray-600">Receipt customization</p>
                  </div>
                </div>
              </div>

              <!-- Open Tickets -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/tickets')">
                <div class="flex items-center space-x-3">
                  <div class="bg-orange-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Open Tickets</h3>
                    <p class="text-sm text-gray-600">Support tickets</p>
                  </div>
                </div>
              </div>

              <!-- Kitchen Printers -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/kitchen-printers')">
                <div class="flex items-center space-x-3">
                  <div class="bg-pink-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Kitchen Printers</h3>
                    <p class="text-sm text-gray-600">Kitchen printer setup</p>
                  </div>
                </div>
              </div>

              <!-- Dining Options -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/dining-options')">
                <div class="flex items-center space-x-3">
                  <div class="bg-cyan-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Dining Options</h3>
                    <p class="text-sm text-gray-600">Dining preferences</p>
                  </div>
                </div>
              </div>

              <!-- Locations -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/locations')">
                <div class="flex items-center space-x-3">
                  <div class="bg-emerald-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Locations</h3>
                    <p class="text-sm text-gray-600">Manage locations</p>
                  </div>
                </div>
              </div>

              <!-- Location & POS Settings -->
              <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors cursor-pointer" @click="navigateTo('/settings/location-pos')">
                <div class="flex items-center space-x-3">
                  <div class="bg-violet-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Location & POS Settings</h3>
                    <p class="text-sm text-gray-600">POS configuration</p>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const navigateTo = (url) => {
  router.visit(url);
};
</script>

<style scoped>
/* Add any additional styling here */
</style> 