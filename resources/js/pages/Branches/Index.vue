<template>
  <AppLayout>
    <Head title="Branches" />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Branches
        </h2>
        <div class="flex items-center space-x-4">
          <select
            v-model="selectedBusinessId"
            @change="handleBusinessChange"
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-2"
          >
            <option value="">All Businesses</option>
            <option v-for="b in businesses" :key="b.id" :value="b.id">
              {{ b.name }}
            </option>
          </select>
          <Link
            v-if="business"
            :href="`/businesses/${business.id}/branches/create`"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Add Branch
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <div v-if="!businesses.length" class="text-center">
              <p class="text-gray-500">No business found. Please create a business first.</p>
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
                    <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Name
                    </th>
                    <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Address
                    </th>
                    <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Phone
                    </th>
                    <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Business
                    </th>
                    <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="branch in branches" :key="branch.id" class="hover:bg-gray-50">
                    <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ branch.name }}</td>
                    <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <span :title="branch.address">
                        {{ branch.address.split(' ').slice(0, 3).join(' ') }}<span v-if="branch.address.split(' ').length > 3">...</span>
                      </span>
                    </td>
                    <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ branch.phone }}</td>
                    <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm">
                      <span v-if="branch.status === 'active'" class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Active</span>
                      <span v-else class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">Inactive</span>
                    </td>
                    <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ branch.business?.name || 'N/A' }}</td>
                    <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div class="flex space-x-3">
                        <template v-if="isOwner && branch.business && branch.business.id">
                          <Link
                            :href="`/businesses/${branch.business.id}/branches/${branch.id}`"
                            class="text-indigo-600 hover:text-indigo-900"
                          >
                            View
                          </Link>
                          <Link
                            :href="`/businesses/${branch.business.id}/branches/${branch.id}/edit`"
                            class="text-green-600 hover:text-green-900"
                          >
                            Edit
                          </Link>
                          <button
                            v-if="isAdmin"
                            @click="deleteBranch(branch)"
                            class="text-red-600 hover:text-red-900"
                            type="button"
                          >
                            Delete
                          </button>
                        </template>
                        <template v-else>
                          <span class="text-gray-400">No actions</span>
                        </template>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="branches.length === 0">
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                      No branches found. Click "Add Branch" to create one.
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
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  business: {
    type: Object,
    required: false,
    default: null
  },
  branches: {
    type: Array,
    required: true,
    default: () => []
  },
  businesses: {
    type: Array,
    required: true,
    default: () => []
  }
});

const page = usePage();
const isOwner = computed(() => {
  return page.props.auth?.user?.roles?.some(role => role.name === 'owner');
});

const isAdmin = computed(() => {
  return page.props.auth?.user?.roles?.some(role => role.name === 'admin');
});

const selectedBusinessId = ref(props.business?.id || '');

const handleBusinessChange = () => {
  if (selectedBusinessId.value) {
    router.visit(`/businesses/${selectedBusinessId.value}/branches`);
  } else {
    router.visit('/branches');
  }
};

const deleteBranch = (branch) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `Delete branch "${branch.name}"? This cannot be undone!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/businesses/${branch.business.id}/branches/${branch.id}`);
    }
  });
};
</script> 
