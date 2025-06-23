<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { Ziggy } from '@/ziggy';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Swal from 'sweetalert2';

const props = defineProps<{
  auth: {
    user: {
      name: string;
      email: string;
    };
  };
}>();

const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email
});

const submit = async () => {
  try {
    // Show loading state
    Swal.fire({
      title: 'Updating Profile...',
      allowOutsideClick: false,
      backdrop: 'rgba(0,0,0,0.4)',
      timer: 2000,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    await form.patch('/settings/profile', {
      preserveScroll: true,
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Profile updated successfully',
          timer: 2000,
          showConfirmButton: false,
          backdrop: 'rgba(0,0,0,0.4)'
        });
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: Object.values(errors).join('\n'),
          confirmButtonText: 'OK',
          backdrop: 'rgba(0,0,0,0.4)'
        });
      }
    });
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

const deleteUser = async () => {
  try {
    // Show confirmation dialog
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: 'This action cannot be undone. All your data will be permanently deleted.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete my account',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc2626',
      backdrop: 'rgba(0,0,0,0.4)'
    });

    if (result.isConfirmed) {
      // Show loading state
      Swal.fire({
        title: 'Deleting Account...',
        allowOutsideClick: false,
        backdrop: 'rgba(0,0,0,0.4)',
        timer: 2000,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      await form.delete('/settings/profile', {
        onSuccess: () => {
          Swal.fire({
            icon: 'success',
            title: 'Account Deleted',
            text: 'Your account has been permanently deleted',
            timer: 2000,
            showConfirmButton: false,
            backdrop: 'rgba(0,0,0,0.4)'
          }).then(() => {
            router.visit('/login');
          });
        },
        onError: () => {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to delete account',
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
    <Head title="Settings" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Settings
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
          <div class="max-w-xl">
            <h2 class="text-lg font-medium text-gray-900">
              Pro Informatio
            </h2>
            <p class="mt-1 text-sm text-gray-600">
              Update your name and email address.
            </p>

            <form @submit.prevent="submit" class="mt-6 space-y-6">
              <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                  id="name"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.name"
                  required
                  autofocus
                  autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                  id="email"
                  type="email"
                  class="mt-1 block w-full"
                  v-model="form.email"
                  required
                  autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />
              </div>

              <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                  <span v-if="form.processing">Saving...</span>
                  <span v-else>Save</span>
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
          <div class="max-w-xl">
            <h2 class="text-lg font-medium text-gray-900">
              Delete Account
            </h2>
            <p class="mt-1 text-sm text-gray-600">
              Delete your account and all of its resources.
            </p>

            <div class="mt-6">
              <p class="text-sm text-red-600">
                Warning: Please proceed with caution, this cannot be undone.
              </p>
              <PrimaryButton
                class="mt-4"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="deleteUser"
              >
                Delete account
              </PrimaryButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 
