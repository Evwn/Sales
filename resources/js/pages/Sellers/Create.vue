<template>
  <AppLayout>
    <Head title="Add Seller" />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Add Seller
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="mt-1 block w-full"
                  required
                />
                <InputError :message="form.errors.email" class="mt-2" />
              </div>

              <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                  id="password"
                  v-model="form.password"
                  type="password"
                  class="mt-1 block w-full"
                  required
                  autocomplete="new-password"
                />
                <InputError :message="form.errors.password" class="mt-2" />
                <ul v-if="passwordErrors.length" class="text-red-600 text-xs mt-1">
                  <li v-for="err in passwordErrors" :key="err">{{ err }}</li>
                </ul>
              </div>

              <div>
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  class="mt-1 block w-full"
                  required
                  autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
              </div>

              <div class="flex items-center justify-end">
                <Link
                  href="/sellers"
                  class="text-sm text-gray-600 hover:text-gray-900 mr-4"
                >
                  Cancel
                </Link>

                <PrimaryButton
                  class="ml-4"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Creating...</span>
                  <span v-else>Create Seller</span>
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
  business: {
    type: Object,
    required: true
  },
  branch: {
    type: Object,
    required: true
  }
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const passwordErrors = ref<string[]>([]);

function validatePassword(password: string): string[] {
  const errors: string[] = [];
  if (password.length < 6) errors.push('Password must be at least 6 characters.');
  if (!/[A-Z]/.test(password)) errors.push('Password must contain at least 1 uppercase letter.');
  if (!/[a-z]/.test(password)) errors.push('Password must contain at least 1 lowercase letter.');
  if (!/[0-9]/.test(password)) errors.push('Password must contain at least 1 number.');
  if (!/[^A-Za-z0-9]/.test(password)) errors.push('Password must contain at least 1 symbol.');
  return errors;
}

const handleSubmit = async () => {
  passwordErrors.value = validatePassword(form.password);
  if (passwordErrors.value.length > 0) {
    return;
  }
  try {
    // Show loading state
    Swal.fire({
      title: 'Creating Seller...',
      allowOutsideClick: false,
      backdrop: 'rgba(0,0,0,0.4)',
      timer: 2000,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Submit the form
    await form.post(`/businesses/${props.business.id}/branches/${props.branch.id}/sellers`, {
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Seller created successfully',
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
</script> 
