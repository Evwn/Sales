<template>
  <AppLayout title="Edit Business (Admin)">
    <PageHeader title="Edit Business (Admin)">
      <template #actions>
        <Link
          :href="`/admin/businesses/${props.business.id}`"
          class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
          Back to Business
        </Link>
      </template>
    </PageHeader>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Owner Selection for Admins -->
            <div class="mb-6">
              <InputLabel for="owner_id" value="Select Owner" />
              <Multiselect
                id="owner_id"
                v-model="selectedOwner"
                :options="props.owners"
                :searchable="true"
                :close-on-select="true"
                :clear-on-select="false"
                :show-labels="false"
                placeholder="Select an owner"
                label="name"
                track-by="id"
                :custom-label="owner => `${owner.name} (${owner.email})`"
              />
              <InputError :message="form.errors.owner_id" class="mt-2" />
            </div>
            <!-- The rest of the business edit form (copy from user Edit.vue) -->
            <!-- ... existing business edit form fields ... -->
            <!-- Navigation Buttons and form submission logic should use the admin route -->
            <form @submit.prevent="handleSubmit">
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update Business</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import countiesData from '@/data/counties_wards.json';

const props = defineProps({
  business: { type: Object, required: true },
  owners: { type: Array, required: true },
  auth: { type: Object, required: true }
});

const selectedOwner = ref(props.owners.find(o => o.id === props.business.owner_id) || null);

const form = useForm({
  ...props.business,
  owner_id: props.business.owner_id || (selectedOwner.value ? selectedOwner.value.id : null),
  logo: null,
  tax_document: null,
  registration_document: null,
  terms_and_conditions: null
});

watch(selectedOwner, (val) => {
  form.owner_id = val ? val.id : null;
});

// The rest of the form logic (fields, stepper, file uploads, etc.) should be copied from the user Edit.vue, but router.post should use `/admin/businesses/${props.business.id}`
// ...

function handleSubmit() {
  form.post(`/admin/businesses/${props.business.id}`, {
    onSuccess: () => {
      router.visit('/admin/businesses');
    }
  });
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style> 