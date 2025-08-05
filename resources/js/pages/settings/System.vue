<template>
  <AppLayout>
      <PageHeader title="System Settings" />
    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="saveSettings" class="space-y-8">
              
              <!-- General Settings -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Business Name
                    </label>
                    <input 
                      v-model="form.businessName" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Time Zone
                    </label>
                    <select 
                      v-model="form.timezone" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="UTC">UTC</option>
                      <option value="America/New_York">Eastern Time</option>
                      <option value="America/Chicago">Central Time</option>
                      <option value="America/Denver">Mountain Time</option>
                      <option value="America/Los_Angeles">Pacific Time</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Currency
                    </label>
                    <select 
                      v-model="form.currency" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="USD">USD ($)</option>
                      <option value="EUR">EUR (€)</option>
                      <option value="GBP">GBP (£)</option>
                      <option value="KES">KES (KSh)</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Date Format
                    </label>
                    <select 
                      v-model="form.dateFormat" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="Y-m-d">YYYY-MM-DD</option>
                      <option value="m/d/Y">MM/DD/YYYY</option>
                      <option value="d/m/Y">DD/MM/YYYY</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Notifications -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notifications</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                      <p class="text-sm text-gray-500">Receive notifications via email</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="form.emailNotifications" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">SMS Notifications</h4>
                      <p class="text-sm text-gray-500">Receive notifications via SMS</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="form.smsNotifications" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Push Notifications</h4>
                      <p class="text-sm text-gray-500">Receive push notifications</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="form.pushNotifications" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Security -->
              <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Security</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Two-Factor Authentication</h4>
                      <p class="text-sm text-gray-500">Add an extra layer of security</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="form.twoFactorAuth" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Session Timeout</h4>
                      <p class="text-sm text-gray-500">Auto-logout after inactivity</p>
                    </div>
                    <select 
                      v-model="form.sessionTimeout" 
                      class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="15">15 minutes</option>
                      <option value="30">30 minutes</option>
                      <option value="60">1 hour</option>
                      <option value="120">2 hours</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Backup & Maintenance -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Backup & Maintenance</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Auto Backup</h4>
                      <p class="text-sm text-gray-500">Automatically backup data daily</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="form.autoBackup" type="checkbox" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Maintenance Mode</h4>
                      <p class="text-sm text-gray-500">Temporarily disable system access</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="form.maintenanceMode" type="checkbox" class="sr-only peer">
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
                  {{ saving ? 'Saving...' : 'Save Settings' }}
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
import PageHeader from '@/components/ui/PageHeader.vue';
import Swal from 'sweetalert2';

const saving = ref(false);

const form = ref({
  businessName: 'My Business',
  timezone: 'UTC',
  currency: 'USD',
  dateFormat: 'Y-m-d',
  emailNotifications: true,
  smsNotifications: false,
  pushNotifications: true,
  twoFactorAuth: false,
  sessionTimeout: '30',
  autoBackup: true,
  maintenanceMode: false,
});

const saveSettings = async () => {
  saving.value = true;
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    Swal.fire({
      icon: 'success',
      title: 'Settings Saved!',
      text: 'Your system settings have been updated successfully.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save settings. Please try again.',
    });
  } finally {
    saving.value = false;
  }
};
</script> 