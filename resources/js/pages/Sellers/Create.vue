<template>
  <AppLayout>
    <Head title="Add Seller" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Add Seller
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            <form @submit.prevent="form.post(`/businesses/${business.id}/branches/${branch.id}/sellers`)">
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
                <InputLabel for="email" value="Email" />
                <TextInput
                  id="email"
                  type="email"
                  class="mt-1 block w-full"
                  v-model="form.email"
                  required
                />
                <InputError :message="form.errors.email" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                  id="password"
                  type="password"
                  class="mt-1 block w-full"
                  v-model="form.password"
                  required
                />
                <InputError :message="form.errors.password" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                  id="password_confirmation"
                  type="password"
                  class="mt-1 block w-full"
                  v-model="form.password_confirmation"
                  required
                />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
              </div>

              <div class="flex items-center justify-end mt-4">
                <Link
                  :href="`/businesses/${business.id}/branches/${branch.id}/sellers`"
                  class="text-sm text-gray-600 hover:text-gray-900 mr-4"
                >
                  Cancel
                </Link>
                <PrimaryButton
                  class="ml-4"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  Create Seller
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
</script> 