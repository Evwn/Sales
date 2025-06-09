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
            <form @submit.prevent="form.post(`/businesses/${business.id}/branches`)">
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

              <div class="flex items-center justify-end mt-4">
                <Link
                  :href="`/businesses/${business.id}`"
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

const props = defineProps({
  business: {
    type: Object,
    required: true
  }
});

const form = useForm({
  name: '',
  address: '',
  phone: ''
});
</script> 