<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ business.name }}
        </h2>
        <div class="flex space-x-4">
          <Link
            :href="`/businesses/${business.id}/branches/create`"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Add Branch
          </Link>
          <Link
            :href="`/businesses/${business.id}/edit`"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Edit Business
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <!-- Business Details -->
            <div class="mb-8">
              <h3 class="text-lg font-medium text-gray-900">Business Details</h3>
              <div class="mt-4 space-y-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Name</label>
                  <p class="mt-1 text-sm text-gray-900">{{ business.name }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Description</label>
                  <p class="mt-1 text-sm text-gray-900">{{ business.description }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Owner</label>
                  <p class="mt-1 text-sm text-gray-900">{{ business.owner.name }}</p>
                </div>
              </div>
            </div>

            <!-- Branches -->
            <div>
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Branches</h3>
                <button
                  @click="showAddBranchModal = true"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Add Branch
                </button>
              </div>
              <div class="mt-4">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Address
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Phone
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="branch in business.branches" :key="branch.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {{ branch.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ branch.address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ branch.phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          <div class="flex space-x-3">
                            <Link
                              :href="`/businesses/${business.id}/branches/${branch.id}`"
                              class="text-indigo-600 hover:text-indigo-900"
                            >
                              View
                            </Link>
                            <Link
                              :href="`/businesses/${business.id}/branches/${branch.id}/edit`"
                              class="text-green-600 hover:text-green-900"
                            >
                              Edit
                            </Link>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="business.branches.length === 0">
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                          No branches found. Click "Add Branch" to create one.
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Admins -->
            <div class="mt-8">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Admins</h3>
                <button
                  @click="showAddAdminModal = true"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Add Admin
                </button>
              </div>
              <div class="overflow-x-auto">
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
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="admin in business.admins" :key="admin.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ admin.name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ admin.email }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <button
                          v-if="admin.id !== business.owner_id"
                          @click="removeAdmin(admin)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Remove
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Branch Modal -->
    <Modal :show="showAddBranchModal" @close="closeAddBranchModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          Add Branch
        </h2>

        <form @submit.prevent="addBranch" class="mt-6">
          <div>
            <InputLabel for="branch_name" value="Name" />
            <TextInput
              id="branch_name"
              v-model="addBranchForm.name"
              type="text"
              class="mt-1 block w-full"
              required
            />
            <InputError :message="addBranchForm.errors.name" class="mt-2" />
          </div>

          <div class="mt-4">
            <InputLabel for="address" value="Address" />
            <TextInput
              id="address"
              v-model="addBranchForm.address"
              type="text"
              class="mt-1 block w-full"
              required
            />
            <InputError :message="addBranchForm.errors.address" class="mt-2" />
          </div>

          <div class="mt-4">
            <InputLabel for="phone" value="Phone" />
            <TextInput
              id="phone"
              v-model="addBranchForm.phone"
              type="text"
              class="mt-1 block w-full"
              required
            />
            <InputError :message="addBranchForm.errors.phone" class="mt-2" />
          </div>

          <div class="mt-6 flex justify-end">
            <SecondaryButton @click="closeAddBranchModal">
              Cancel
            </SecondaryButton>

            <PrimaryButton
              class="ml-3"
              :class="{ 'opacity-25': addBranchForm.processing }"
              :disabled="addBranchForm.processing"
            >
              Add Branch
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Add Admin Modal -->
    <Modal :show="showAddAdminModal" @close="closeAddAdminModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          Add Admin
        </h2>

        <form @submit.prevent="addAdmin" class="mt-6">
          <div>
            <InputLabel for="name" value="Name" />
            <TextInput
              id="name"
              v-model="addAdminForm.name"
              type="text"
              class="mt-1 block w-full"
              required
            />
            <InputError :message="addAdminForm.errors.name" class="mt-2" />
          </div>

          <div class="mt-4">
            <InputLabel for="email" value="Email" />
            <TextInput
              id="email"
              v-model="addAdminForm.email"
              type="email"
              class="mt-1 block w-full"
              required
            />
            <InputError :message="addAdminForm.errors.email" class="mt-2" />
          </div>

          <div class="mt-4">
            <InputLabel for="password" value="Password" />
            <TextInput
              id="password"
              v-model="addAdminForm.password"
              type="password"
              class="mt-1 block w-full"
              required
            />
            <InputError :message="addAdminForm.errors.password" class="mt-2" />
          </div>

          <div class="mt-6 flex justify-end">
            <SecondaryButton @click="closeAddAdminModal">
              Cancel
            </SecondaryButton>

            <PrimaryButton
              class="ml-3"
              :class="{ 'opacity-25': addAdminForm.processing }"
              :disabled="addAdminForm.processing"
            >
              Add Admin
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import Modal from '@/components/Modal.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';

const props = defineProps({
  business: {
    type: Object,
    required: true
  }
});

const showAddAdminModal = ref(false);
const showAddBranchModal = ref(false);

const addAdminForm = useForm({
  name: '',
  email: '',
  password: ''
});

const addBranchForm = useForm({
  name: '',
  address: '',
  phone: ''
});

const closeAddAdminModal = () => {
  showAddAdminModal.value = false;
  addAdminForm.reset();
};

const closeAddBranchModal = () => {
  showAddBranchModal.value = false;
  addBranchForm.reset();
};

const addAdmin = () => {
  addAdminForm.post(`/businesses/${props.business.id}/admins`, {
    onSuccess: () => {
      closeAddAdminModal();
      Swal.fire({
        title: 'Success!',
        text: 'Admin has been added successfully.',
        icon: 'success',
        confirmButtonColor: '#3085d6'
      });
    },
    onError: () => {
      Swal.fire({
        title: 'Error!',
        text: 'Failed to add admin. Please try again.',
        icon: 'error',
        confirmButtonColor: '#d33'
      });
    }
  });
};

const addBranch = () => {
  // Show loading state
  Swal.fire({
    title: 'Adding Branch...',
    text: 'Please wait while we add the branch',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  addBranchForm.post(`/businesses/${props.business.id}/branches`, {
    onSuccess: () => {
      closeAddBranchModal();
      Swal.fire({
        title: 'Success!',
        text: 'Branch has been added successfully.',
        icon: 'success',
        confirmButtonColor: '#3085d6'
      }).then(() => {
        // Redirect to businesses page
        router.visit('/businesses');
      });
    },
    onError: (errors) => {
      Swal.fire({
        title: 'Error!',
        text: errors.message || 'Failed to add branch. Please try again.',
        icon: 'error',
        confirmButtonColor: '#d33'
      });
    }
  });
};

const removeAdmin = (admin) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `Do you want to remove ${admin.name} as an admin?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, remove admin',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/businesses/${props.business.id}/admins/${admin.id}`, {
        onSuccess: () => {
          Swal.fire(
            'Removed!',
            'Admin has been removed successfully.',
            'success'
          );
        },
        onError: () => {
          Swal.fire(
            'Error!',
            'Failed to remove admin. Please try again.',
            'error'
          );
        }
      });
    }
  });
};
</script> 