<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

interface Admin {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  admins: Admin[];
}>();

const handleDelete = async (admin: Admin) => {
  try {
    // Show confirmation dialog
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: `Do you want to remove ${admin.name} as an admin?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, remove admin',
      cancelButtonText: 'Cancel',
      backdrop: 'rgba(0,0,0,0.4)'
    });

    if (result.isConfirmed) {
      // Show loading state
      Swal.fire({
        title: 'Removing Admin...',
        allowOutsideClick: false,
        backdrop: 'rgba(0,0,0,0.4)',
        timer: 2000,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Delete the admin
      await router.delete(`/admins/${admin.id}`, {
        onSuccess: () => {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Admin removed successfully',
            timer: 2000,
            showConfirmButton: false,
            backdrop: 'rgba(0,0,0,0.4)'
          });
        },
        onError: () => {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to remove admin',
            confirmButtonText: 'OK',
            backdrop: 'rgba(0,0,0,0.4)'
          });
        }
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'An unexpected error occurred',
      confirmButtonText: 'OK',
      backdrop: 'rgba(0,0,0,0.4)'
    });
  }
};
</script>

<template>
  <AppLayout>
    <Head title="Admins" />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Admins
        </h2>
        <Link
          href="/admins/create"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
          Add Admin
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="admin in admins" :key="admin.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ admin.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ admin.email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      @click="handleDelete(admin)"
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
  </AppLayout>
</template> 