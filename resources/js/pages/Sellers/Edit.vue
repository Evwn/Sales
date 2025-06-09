<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit User
        </h2>
        <Link
          href="/sellers"
          class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Back to Users
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <div class="mt-1">
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                  {{ form.errors.name }}
                </div>
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <div class="mt-1">
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>
                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                  {{ form.errors.email }}
                </div>
              </div>

              <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                  Password
                  <span class="text-sm text-gray-500">(leave blank to keep current password)</span>
                </label>
                <div class="mt-1">
                  <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>
                <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                  {{ form.errors.password }}
                </div>
              </div>

              <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <div class="mt-1">
                  <select
                    id="role"
                    v-model="form.role"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option value="seller">Seller</option>
                    <option value="admin">Business Admin</option>
                  </select>
                </div>
                <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                  {{ form.errors.role }}
                </div>
              </div>

              <div>
                <label for="business_id" class="block text-sm font-medium text-gray-700">Business</label>
                <div class="mt-1">
                  <select
                    id="business_id"
                    v-model="form.business_id"
                    @change="loadBranches"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option value="">Select a business</option>
                    <option v-for="business in businesses" :key="business.id" :value="business.id">
                      {{ business.name }}
                    </option>
                  </select>
                </div>
                <div v-if="form.errors.business_id" class="mt-1 text-sm text-red-600">
                  {{ form.errors.business_id }}
                </div>
              </div>

              <div v-if="form.role === 'seller'">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                <div class="mt-1">
                  <select
                    id="branch_id"
                    v-model="form.branch_id"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    :disabled="!form.business_id"
                  >
                    <option value="">Select a branch</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                      {{ branch.name }}
                    </option>
                  </select>
                </div>
                <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">
                  {{ form.errors.branch_id }}
                </div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  :disabled="form.processing"
                >
                  Update User
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
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  seller: {
    type: Object,
    required: true,
  },
  businesses: {
    type: Array,
    required: true,
  },
  branches: {
    type: Array,
    required: true,
  },
});

const branches = ref(props.branches);

const form = useForm({
  name: props.seller.name,
  email: props.seller.email,
  password: '',
  role: props.seller.role,
  business_id: props.seller.business_id,
  branch_id: props.seller.branch_id,
});

// Reset branch_id when role changes to admin
watch(() => form.role, (newRole) => {
  if (newRole === 'admin') {
    form.branch_id = '';
  }
});

const loadBranches = async () => {
  if (!form.business_id || form.role === 'admin') {
    branches.value = [];
    form.branch_id = '';
    return;
  }

  try {
    const response = await axios.get(`/sellers/businesses/${form.business_id}/branches`);
    branches.value = response.data.branches;
    form.branch_id = '';
  } catch (error) {
    console.error('Error loading branches:', error);
  }
};

const submit = () => {
  form.patch(`/sellers/${props.seller.id}`);
};
</script> 