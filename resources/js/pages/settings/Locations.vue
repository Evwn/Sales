<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link href="/settings" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Locations</h2>
        </div>
        <Link 
          href="/settings/locations/create"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          <span>Add Location</span>
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Search and Filters -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
              <div class="flex-1 max-w-sm">
                <div class="relative">
                  <input 
                    v-model="search" 
                    type="text" 
                    placeholder="Search locations..." 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <select 
                  v-model="statusFilter" 
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">All Status</option>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
                <select 
                  v-model="typeFilter" 
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">All Types</option>
                  <option v-for="type in locationTypes" :key="type.id" :value="type.id">
                    {{ type.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Locations Table -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Location
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Business
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Address
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Phone
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="location in filteredLocations" :key="location.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ location.name }}</div>

                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ location.location_type?.name || 'N/A' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ location.business?.name || 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ location.address || 'No address' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ location.phone || 'No phone' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          location.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]"
                      >
                        {{ location.status ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex items-center space-x-2">
                        <Link 
                          :href="`/settings/locations/${location.id}/edit`"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          @click="deleteLocation(location)"
                          class="text-red-600 hover:text-red-900"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredLocations.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No locations found</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by creating a new location.</p>
              <div class="mt-6">
                <Link 
                  href="/settings/locations/create"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Add Location
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

// Get props from Inertia
const page = usePage();
const props = page.props;

// Reactive data
const locations = ref(props.locations || []);
const locationTypes = ref(props.locationTypes || []);
const businesses = ref(props.businesses || []);
const branches = ref(props.branches || []);
const search = ref('');
const statusFilter = ref('');
const typeFilter = ref('');

const isSubmitting = ref(false);

const form = ref({
  name: '',
  location_type_id: '',
  address: '',
  phone: '',
  status: true,
  business_id: '',
  branch_id: '',
});

// Computed properties
const filteredLocations = computed(() => {
  let filtered = locations.value;
  
  if (search.value) {
    filtered = filtered.filter(location => 
      location.name.toLowerCase().includes(search.value.toLowerCase()) ||
      location.address?.toLowerCase().includes(search.value.toLowerCase()) ||
      location.phone?.includes(search.value)
    );
  }
  
  if (statusFilter.value !== '') {
    filtered = filtered.filter(location => location.status == statusFilter.value);
  }
  
  if (typeFilter.value) {
    filtered = filtered.filter(location => location.location_type_id == typeFilter.value);
  }
  
  return filtered;
});

const filteredBranches = computed(() => {
  if (!form.value.business_id) return branches.value;
  return branches.value.filter(branch => branch.business_id == form.value.business_id);
});

// Methods
const loadLocations = async () => {
  try {
    const response = await axios.get('/settings/locations/api/list');
    locations.value = response.data;
  } catch (error) {
    console.error('Error loading locations:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load locations. Please refresh the page.',
    });
  }
};

const deleteLocation = async (location) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: `Do you want to delete "${location.name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/settings/locations/${location.id}`);
      
      locations.value = locations.value.filter(l => l.id !== location.id);
      
      Swal.fire(
        'Deleted!',
        'Location has been deleted.',
        'success'
      );
    } catch (error) {
      console.error('Error deleting location:', error);
      Swal.fire(
        'Error!',
        error.response?.data?.message || 'Failed to delete location.',
        'error'
      );
    }
  }
};

// Lifecycle
onMounted(() => {
  // Data is loaded from props via Inertia
  // loadLocations() is available for manual refresh if needed
});
</script> 