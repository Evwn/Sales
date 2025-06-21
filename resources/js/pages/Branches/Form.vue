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

    <!-- Barcode Section -->
    <div v-if="branch" class="space-y-4">
      <div class="flex items-center justify-between">
        <InputLabel value="Branch Barcode" />
        <SecondaryButton
          type="button"
          @click="generateBarcode"
          :disabled="isGeneratingBarcode"
        >
          <span v-if="isGeneratingBarcode">Generating...</span>
          <span v-else>Generate New Barcode</span>
        </SecondaryButton>
      </div>
      
      <div v-if="branch.barcode_path" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Barcode</p>
            <p class="font-mono text-lg">{{ branch.barcode_path }}</p>
          </div>
          <div class="flex space-x-2">
            <SecondaryButton
              type="button"
              @click="downloadBarcode"
              :disabled="isDownloadingBarcode"
            >
              <span v-if="isDownloadingBarcode">Downloading...</span>
              <span v-else>Download</span>
            </SecondaryButton>
            <SecondaryButton
              type="button"
              @click="printBarcode"
              :disabled="isPrintingBarcode"
            >
              <span v-if="isPrintingBarcode">Printing...</span>
              <span v-else>Print</span>
            </SecondaryButton>
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-4">
      <SecondaryButton
        type="button"
        @click="$inertia.visit(route('branches.index'))"
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

const isGeneratingBarcode = ref(false);
const isDownloadingBarcode = ref(false);
const isPrintingBarcode = ref(false);

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
  form.address = location.location;
  form.gps_latitude = location.latitude;
  form.gps_longitude = location.longitude;
};

const submit = async () => {
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

const generateBarcode = async () => {
  if (!props.branch) return;
  
  isGeneratingBarcode.value = true;
  try {
    const response = await axios.post(
      route('branches.generate-barcode', [props.business, props.branch])
    );
    props.branch.barcode_path = response.data.barcode;
  } catch (error) {
    console.error('Failed to generate barcode:', error);
  } finally {
    isGeneratingBarcode.value = false;
  }
};

const downloadBarcode = async () => {
  if (!props.branch?.barcode_path) return;
  
  isDownloadingBarcode.value = true;
  try {
    const response = await axios.get(
      route('branches.download-barcode', [props.business, props.branch]),
      { responseType: 'blob' }
    );
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `barcode-${props.branch.barcode_path}.png`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Failed to download barcode:', error);
  } finally {
    isDownloadingBarcode.value = false;
  }
};

const printBarcode = async () => {
  if (!props.branch?.barcode_path) return;
  
  isPrintingBarcode.value = true;
  try {
    const response = await axios.get(
      route('branches.print-barcode', [props.business, props.branch]),
      { responseType: 'blob' }
    );
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const printWindow = window.open(url, '_blank');
    printWindow.onload = () => {
      printWindow.print();
      printWindow.close();
    };
  } catch (error) {
    console.error('Failed to print barcode:', error);
  } finally {
    isPrintingBarcode.value = false;
  }
};
</script> 