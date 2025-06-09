<template>
  <AppLayout>
    <Head title="Add Branch" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Add Branch
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <form @submit.prevent="handleSubmit">
              <div class="mb-4">
                <InputLabel for="business_id" value="Business" />
                <select
                  id="business_id"
                  v-model="form.business_id"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-2"
                  required
                >
                  <option v-for="b in businesses" :key="b.id" :value="b.id">
                    {{ b.name }}
                  </option>
                </select>
                <InputError :message="form.errors.business_id" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                  id="name"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.name"
                  required
                  autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="address" value="Address" />
                <TextInput
                  id="address"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.address"
                  required
                />
                <InputError :message="form.errors.address" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="phone" value="Phone" />
                <TextInput
                  id="phone"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.phone"
                  required
                />
                <InputError :message="form.errors.phone" class="mt-2" />
              </div>

              <div class="mt-4">
                <InputLabel value="Sellers" />
                <div class="mt-2 space-y-2">
                  <div v-for="seller in sellers" :key="seller.id" class="flex items-center">
                    <input
                      type="checkbox"
                      :id="'seller-' + seller.id"
                      :value="seller.id"
                      v-model="form.seller_ids"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <label :for="'seller-' + seller.id" class="ml-2 text-sm text-gray-600">
                      {{ seller.name }} ({{ seller.email }})
                    </label>
                  </div>
                </div>
                <InputError :message="form.errors.seller_ids" class="mt-2" />
              </div>

              <div class="flex items-center justify-end mt-4">
                <Link
                  :href="`/businesses/${form.business_id}/branches`"
                  class="text-sm text-gray-600 hover:text-gray-900 mr-4"
                >
                  Cancel
                </Link>
                <PrimaryButton
                  class="ml-4"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  Create Branch
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
import { PropType } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  business: {
    type: Object,
    required: false,
    default: null
  },
  businesses: {
    type: Array as PropType<Array<{ id: number; name: string }>>,
    required: true,
    default: () => []
  },
  sellers: {
    type: Array as PropType<Array<{ id: number; name: string; email: string }>>,
    required: true,
    default: () => []
  }
});

const form = useForm({
  name: '',
  address: '',
  phone: '',
  business_id: props.business?.id || '',
  seller_ids: []
});

const handleSubmit = async () => {
  try {
    // Show loading state
    Swal.fire({
      title: 'Creating Branch...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Submit the form
    await form.post(`/businesses/${form.business_id}/branches`, {
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Branch created successfully',
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