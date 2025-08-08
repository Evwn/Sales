<template>
  <div>
    <Link href="/settings/locations" class="inline-flex items-center text-blue-600 hover:underline mb-4">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Back to Locations
    </Link>
    <form @submit.prevent="$emit('submit')" class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Location Name <span class="text-red-500">*</span></label>
        <input v-model="form.name" type="text" required autofocus class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Enter location name" />
        <div v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Location Type <span class="text-red-500">*</span></label>
        <div class="relative">
          <select v-model="selectedType" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            <option value="">Select location type</option>
            <option v-for="type in locationTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
            <option value="__add_new__">+ Add Location Type</option>
          </select>
        </div>
        <div v-if="showAddType" class="mt-3 flex items-center space-x-2">
          <input v-model="newTypeName" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="New location type name" />
          <button type="button" @click="saveType" :disabled="isSavingType" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
            <span v-if="isSavingType">Saving...</span>
            <span v-else>Save</span>
          </button>
        </div>
        <div v-if="errors.location_type_id" class="text-red-500 text-xs mt-1">{{ errors.location_type_id }}</div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Business <span class="text-red-500">*</span></label>
        <select v-model="form.business_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
          <option value="">Select business</option>
          <option v-for="business in businesses" :key="business.id" :value="business.id">{{ business.name }}</option>
        </select>
        <div v-if="errors.business_id" class="text-red-500 text-xs mt-1">{{ errors.business_id }}</div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Branch <span class="text-red-500">*</span></label>
        <select v-model="form.branch_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
          <option value="">Select branch</option>
          <option v-for="branch in filteredBranches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
        </select>
        <div v-if="errors.branch_id" class="text-red-500 text-xs mt-1">{{ errors.branch_id }}</div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Address <span class="text-red-500">*</span></label>
        <textarea v-model="form.address" required rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none" placeholder="Enter full address"></textarea>
        <div v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address }}</div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
        <input v-model="form.phone" type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Enter phone number" />
        <div v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <div class="flex items-center space-x-4">
          <label class="flex items-center">
            <input type="radio" v-model="form.status" :value="true" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
            <span class="ml-2 text-sm text-gray-700">Active</span>
          </label>
          <label class="flex items-center">
            <input type="radio" v-model="form.status" :value="false" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
            <span class="ml-2 text-sm text-gray-700">Inactive</span>
          </label>
        </div>
        <div v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</div>
      </div>
      <div class="flex items-center justify-end gap-4 mt-6">
        <button type="submit" :disabled="isSubmitting" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
          <span v-if="isSubmitting">Saving...</span>
          <span v-else>Save</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
const props = defineProps({
  form: Object,
  locationTypes: Array,
  businesses: Array,
  branches: Array,
  isSubmitting: Boolean,
  errors: Object,
});
const emit = defineEmits(['submit']);
const filteredBranches = computed(() => {
  if (!props.form.business_id) return props.branches;
  return props.branches.filter(branch => branch.business_id == props.form.business_id);
});
const selectedType = ref(props.form.location_type_id);
watch(selectedType, (val) => {
  if (val === '__add_new__') showAddType.value = true;
  else showAddType.value = false;
  props.form.location_type_id = val;
});
const showAddType = ref(false);
const newTypeName = ref('');
const isSavingType = ref(false);
const saveType = async () => {
  if (!newTypeName.value.trim()) return;
  isSavingType.value = true;
  try {
    const { data } = await axios.post('/settings/location-types', { name: newTypeName.value });
    props.locationTypes.push(data.locationType);
    selectedType.value = data.locationType.id;
    showAddType.value = false;
    newTypeName.value = '';
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Location type added!', showConfirmButton: false, timer: 2000 });
  } catch (e) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Error', text: e.response?.data?.message || 'Failed to add location type', showConfirmButton: false, timer: 3000 });
  } finally {
    isSavingType.value = false;
  }
};
</script> 