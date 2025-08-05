<template>
  <form @submit.prevent="submit" class="space-y-6 bg-[#B76E79]/10 backdrop-blur-md">
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

    <div class="mt-6">
      <label class="flex items-center space-x-2">
        <input type="checkbox" v-model="createStoreNow" class="form-checkbox h-5 w-5 text-indigo-600" />
        <span class="text-gray-700">Create a store for this branch now?</span>
      </label>
    </div>
    <div v-if="createStoreNow" class="mt-6 space-y-4 bg-gray-50 p-4 rounded-md border border-gray-200">
      <h3 class="font-semibold text-lg mb-2">Store Details</h3>
      <div>
        <InputLabel for="store_name" value="Store Name" />
        <TextInput id="store_name" type="text" class="mt-1 block w-full" v-model="storeForm.name" required />
        <InputError :message="storeForm.errors.name" class="mt-2" />
      </div>
      <div>
        <InputLabel for="store_address" value="Store Address" />
        <TextInput id="store_address" type="text" class="mt-1 block w-full" v-model="storeForm.address" />
        <InputError :message="storeForm.errors.address" class="mt-2" />
      </div>
      <div>
        <InputLabel for="store_phone" value="Store Phone" />
        <TextInput id="store_phone" type="tel" class="mt-1 block w-full" v-model="storeForm.phone" />
        <InputError :message="storeForm.errors.phone" class="mt-2" />
      </div>
      <div>
        <InputLabel for="store_status" value="Store Status" />
        <select id="store_status" v-model="storeForm.status" class="mt-1 block w-full border-gray-300 rounded-md">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
        <InputError :message="storeForm.errors.status" class="mt-2" />
      </div>
    </div>

    <div class="flex items-center justify-end gap-4">
      <SecondaryButton
        type="button"
        @click="router.visit('/branches')"
      >
        Cancel
      </SecondaryButton>
      <PrimaryButton :disabled="form.processing || isSubmitting">
        {{ isSubmitting ? (props.branch ? 'Updating...' : (createStoreNow ? 'Creating Branch and Store...' : 'Creating...')) : (branch ? 'Update Branch' : 'Create Branch') }}
      </PrimaryButton>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import GpsLocationPicker from '@/components/GpsLocationPicker.vue';
import Swal from 'sweetalert2';

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
  store: {
    name: '',
    address: '',
    phone: '',
    status: 1,
  },
});

const createStoreNow = ref(false);
const storeForm = ref({
  name: '',
  address: '',
  phone: '',
  status: 1,
  errors: {},
});

const updateLocation = (location) => {
  form.address = location.location || form.address;
  form.gps_latitude = location.latitude;
  form.gps_longitude = location.longitude;
};

const isSubmitting = ref(false);

onMounted(() => {
  if (form.errors.error) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Error!',
      text: form.errors.error,
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
  }
});

const submit = async () => {
  if (!form.address && form.location && typeof form.location === 'string') {
    form.address = form.location;
  }
  try {
    isSubmitting.value = true;
    let loadingToast;
    if (props.branch) {
      loadingToast = Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Updating Branch...',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    } else if (createStoreNow.value) {
      loadingToast = Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Creating Branch and Store...',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    } else {
      loadingToast = Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Creating Branch...',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    }
    if (props.branch) {
      await form.put(`/businesses/${props.business.id}/branches/${props.branch.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          Swal.close();
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Success!',
            text: 'Branch updated successfully',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
          });
        },
        onError: (errors) => {
          Swal.close();
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Error!',
            text: Object.values(errors).join('\n'),
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
          });
        },
        onFinish: () => {
          isSubmitting.value = false;
        }
      });
    } else {
      if (createStoreNow.value) {
        form.store.name = storeForm.value.name;
        form.store.address = storeForm.value.address;
        form.store.phone = storeForm.value.phone;
        form.store.status = storeForm.value.status;
        await form.post(`/businesses/${props.business.id}/branches-with-store`, {
          preserveScroll: true,
          onSuccess: () => {
            storeForm.value.errors = {};
            Swal.close();
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Success!',
              text: 'Branch and store created successfully',
              showConfirmButton: false,
              timer: 4000,
              timerProgressBar: true,
            });
            router.visit(`/businesses/${props.business.id}/branches`);
          },
          onError: (errors) => {
            Swal.close();
            if (errors['store']) {
              storeForm.value.errors = errors['store'];
            }
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'error',
              title: 'Error!',
              text: Object.values(errors).join('\n'),
              showConfirmButton: false,
              timer: 4000,
              timerProgressBar: true,
            });
          },
          onFinish: () => {
            isSubmitting.value = false;
          }
        });
      } else {
        await form.post(`/businesses/${props.business.id}/branches`, {
          preserveScroll: true,
          onSuccess: () => {
            Swal.close();
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Success!',
              text: 'Branch created successfully',
              showConfirmButton: false,
              timer: 4000,
              timerProgressBar: true,
            });
            router.visit(`/businesses/${props.business.id}/branches`);
          },
          onError: (errors) => {
            Swal.close();
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'error',
              title: 'Error!',
              text: Object.values(errors).join('\n'),
              showConfirmButton: false,
              timer: 4000,
              timerProgressBar: true,
            });
          },
          onFinish: () => {
            isSubmitting.value = false;
          }
        });
      }
    }
  } catch (error) {
    isSubmitting.value = false;
    Swal.close();
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Error!',
      text: error.message || 'An unexpected error occurred',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
  }
};
</script> 
