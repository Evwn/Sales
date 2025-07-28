<template>
  <AppLayout title="Create Location">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Location</h2>
    </template>
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <LocationsForm
              :form="form"
              :locationTypes="locationTypes"
              :businesses="businesses"
              :branches="branches"
              :isSubmitting="isSubmitting"
              :errors="form.errors"
              @submit="submit"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import LocationsForm from './LocationsForm.vue';
import Swal from 'sweetalert2';
const page = usePage();
const props = page.props;
const form = useForm({
  name: '',
  location_type_id: '',
  business_id: '',
  branch_id: '',
  address: '',
  phone: '',
  status: true,
});
const locationTypes = ref(props.locationTypes || []);
const businesses = ref(props.businesses || []);
const branches = ref(props.branches || []);
const isSubmitting = ref(false);
const submit = async () => {
  isSubmitting.value = true;
  form.post('/settings/locations', {
    onSuccess: () => {
      Swal.fire({ icon: 'success', title: 'Location Created!', timer: 2000, showConfirmButton: false });
      router.visit('/settings/locations');
    },
    onError: () => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to create location.' });
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
};
</script> 