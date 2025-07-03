<template>
  <AppLayout>
    <Head title="Sellers" />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Sellers
        </h2>
        <div class="flex items-center space-x-4">
          <!-- Branch selection -->
          <select
            v-model="selectedBranchId"
            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            @change="onBranchSelect"
          >
            <option value="all">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
              {{ branch.name }}
            </option>
          </select>
          <!-- Add Seller button -->
          <Link
            v-if="selectedBranchId !== 'all'"
            :href="`/businesses/${getBusinessIdForBranch(selectedBranchId)}/branches/${selectedBranchId}/sellers/create`"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Add Seller
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <div v-if="!branches || branches.length === 0" class="text-center py-4">
              <p class="text-gray-600">You need to create a business first.</p>
              <Link
                href="/businesses/create"
                class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Create Business
              </Link>
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Branch
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Business
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="seller in filteredSellers" :key="seller.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ seller.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ seller.email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ seller.branch?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ seller.branch?.business?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div class="flex space-x-3">
                        <button
                          v-if="seller.branch"
                          @click="showSellerDetails(seller)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          View
                        </button>
                        <Link
                          v-if="seller.branch"
                          :href="`/businesses/${seller.branch.business.id}/branches/${seller.branch.id}/sellers/${seller.id}/edit`"
                          class="text-green-600 hover:text-green-900"
                        >
                          Edit
                        </Link>
                        <button
                          v-if="isAdmin && seller.branch"
                          @click="deleteSeller(seller)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredSellers.length === 0">
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                      No sellers found. Click "Add Seller" to create one.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2'

const props = defineProps({
  business: {
    type: Object,
    required: false,
    default: null
  },
  branch: {
    type: Object,
    required: false,
    default: null
  },
  sellers: {
    type: Array,
    required: true,
    default: () => []
  },
  branches: {
    type: Array,
    required: false,
    default: () => []
  },
  userRole: {
    type: String,
    required: true
  },
  selectedBranch: {
    type: [String, Number],
    default: 'all'
  }
});

const selectedBranchId = ref(props.selectedBranch);

// Computed property to check if user is admin
const isAdmin = computed(() => props.userRole === 'admin');

// Computed property to filter sellers based on selected branch
const filteredSellers = computed(() => {
  if (selectedBranchId.value === 'all') {
    return props.sellers;
  }
  return props.sellers.filter(seller => seller.branch?.id == selectedBranchId.value);
});

const onBranchSelect = () => {
  selectedBranchId.value = selectedBranchId.value;
};

function getBusinessIdForBranch(branchId) {
  const branch = props.branches.find(b => b.id == branchId);
  return branch ? branch.business_id : null;
}

const deleteSeller = (seller) => {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/businesses/${seller.branch.business.id}/branches/${seller.branch.id}/sellers/${seller.id}`);
    }
  });
};

const showSellerDetails = (seller) => {
  Swal.fire({
    title: 'Seller Details',
    html: `
      <div style='text-align:left'>
        <p><strong>Name:</strong> ${seller.name}</p>
        <p><strong>Email:</strong> ${seller.email}</p>
        <p><strong>Branch:</strong> ${seller.branch?.name || 'N/A'}</p>
        <p><strong>Business:</strong> ${seller.branch?.business?.name || 'N/A'}</p>
      </div>
    `,
    confirmButtonText: 'Close'
  });
}
</script> 
