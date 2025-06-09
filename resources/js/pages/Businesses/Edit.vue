<template>
  <AppLayout>
    <Head title="Edit Business" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Business
      </h2>
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
                <InputLabel for="description" value="Description" />
                <TextArea
                  id="description"
                  v-model="form.description"
                  class="mt-1 block w-full"
                  rows="4"
                />
                <InputError :message="form.errors.description" class="mt-2" />
              </div>

              <div class="flex items-center justify-end">
                <Link
                  href="/businesses"
                  class="text-sm text-gray-600 hover:text-gray-900 mr-4"
                >
                  Back to Businesses
                </Link>

                <PrimaryButton
                  class="ml-4"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  Update Business
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
import TextArea from '@/components/TextArea.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  business: {
    type: Object,
    required: true
  }
});

const form = useForm({
  name: props.business.name,
  description: props.business.description
});

const handleSubmit = async () => {
  try {
    // Show loading state
    Swal.fire({
      title: 'Updating Business...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Submit the form
    await form.put(`/businesses/${props.business.id}`, {
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Business updated successfully',
          timer: 2000,
          showConfirmButton: false
        });
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: Object.values(errors).join('\n'),
          confirmButtonText: 'OK'
        });
      }
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'An unexpected error occurred',
      confirmButtonText: 'OK'
    });
  }
};
</script> 