<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div>
      <InputLabel for="name" value="Branch Name" />
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

    <div>
      <InputLabel for="location" value="Location" />
      <GpsLocationPicker
        v-model="form.location"
        @update:modelValue="updateLocation"
      />
      <InputError :message="form.errors.location" class="mt-2" />
      <!-- Allow manual address entry if GPS is not available -->
      <div class="mt-2">
        <InputLabel for="address" value="Or type address manually" />
        <TextInput
          id="address"
          type="text"
          class="mt-1 block w-full"
          v-model="form.address"
          placeholder="Enter address manually if GPS fails"
        />
        <InputError :message="form.errors.address" class="mt-2" />
      </div>
    </div>

    <div>
      <InputLabel for="phone" value="Phone Number" />
      <TextInput
        id="phone"
        type="tel"
        class="mt-1 block w-full"
        v-model="form.phone"
        required
      />
      <InputError :message="form.errors.phone" class="mt-2" />
    </div>

    <div>
      <InputLabel for="email" value="Email Address" />
      <TextInput
        id="email"
        type="email"
        class="mt-1 block w-full"
        v-model="form.email"
        required
      />
      <InputError :message="form.errors.email" class="mt-2" />
    </div>

    <div class="flex items-center justify-end gap-4">
      <SecondaryButton
        type="button"
        @click="$inertia.visit('/branches')"
      >
        Cancel
      </SecondaryButton>
      <PrimaryButton :disabled="form.processing">
        {{ branch ? 'Update Branch' : 'Create Branch' }}
      </PrimaryButton>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import GpsLocationPicker from '@/components/GpsLocationPicker.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
  branch: {
    type: Object,
    default: null,
  },
  business: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  name: props.branch?.name || '',
  address: props.branch?.address || '',
  gps_latitude: props.branch?.gps_latitude || null,
  gps_longitude: props.branch?.gps_longitude || null,
  phone: props.branch?.phone || '',
  email: props.branch?.email || '',
  barcode_path: props.branch?.barcode_path || null,
});

const updateLocation = (location) => {
  form.address = location.location || form.address;
  form.gps_latitude = location.latitude;
  form.gps_longitude = location.longitude;
};

const submit = async () => {
  // If address is empty but form.location has a value, use it as address
  if (!form.address && form.location && typeof form.location === 'string') {
    form.address = form.location;
  }
  try {
    // Show loading state
    Swal.fire({
      title: props.branch ? 'Updating Branch...' : 'Creating Branch...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    if (props.branch) {
      await form.put(`/businesses/${props.business.id}/branches/${props.branch.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Branch updated successfully',
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
    } else {
      await form.post(`/businesses/${props.business.id}/branches`, {
        preserveScroll: true,
        onSuccess: (response) => {
          // Update the branch prop with the response data
          if (response.props.branch) {
            props.branch = response.props.branch;
          }
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
    }
  } catch (error) {
    console.error('Form submission error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'An unexpected error occurred',
      confirmButtonText: 'OK'
    });
  }
};
</script> 
