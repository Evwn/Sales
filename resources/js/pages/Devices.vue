<template>
  <AppLayout title="Devices">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Devices</h2>
    </template>
    <div class="max-w-3xl mx-auto py-8">
      <div class="mb-6">
        <template v-if="businesses.length === 0">
          <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
            Please add a business before registering a device.
          </div>
        </template>
        <template v-else>
          <form @submit.prevent="registerDevice" class="flex flex-col gap-4 bg-white p-6 rounded shadow">
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700">Device UUID</label>
              <input v-model="form.device_uuid" type="text" readonly class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 pr-10" />
              <span v-if="uuidReady" class="absolute right-3 top-8 text-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd" />
                </svg>
              </span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Business</label>
              <select v-model="form.business_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="" disabled>Select business</option>
                <option v-for="business in businesses" :key="business.id" :value="business.id">{{ business.name }}</option>
              </select>
            </div>
            <div v-if="form.business_id">
              <label class="block text-sm font-medium text-gray-700">Branch</label>
              <select v-model="form.branch_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="" disabled>Select branch</option>
                <option v-for="branch in filteredBranches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
              </select>
            </div>
            <div>
              <button :disabled="!canRegister" type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="isSaving">Saving Device...</span>
                <span v-else>Register Device</span>
              </button>
            </div>
          </form>
        </template>
      </div>
      <div class="bg-white rounded shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Registered Devices</h3>
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">UUID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Registered By</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Registered At</th>
              <th class="px-4 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="device in devices" :key="device.id">
              <td class="px-4 py-2">{{ device.device_uuid }}</td>
              <td class="px-4 py-2">{{ device.branch?.name || device.branch_id }}</td>
              <td class="px-4 py-2">{{ device.registered_by ? device.registered_by.name : (device.registeredBy?.name || device.registered_by) }}</td>
              <td class="px-4 py-2">{{ formatDate(device.registered_at) }}</td>
              <td class="px-4 py-2">
                <button @click="removeDevice(device.id)" class="text-red-600 hover:underline">Remove</button>
              </td>
            </tr>
            <tr v-if="devices.length === 0">
              <td colspan="5" class="text-center text-gray-400 py-4">No devices registered.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { v4 as uuidv4 } from 'uuid';
import Swal from 'sweetalert2';

const page = usePage();
const devices = ref(page.props.devices || []);
const branches = ref(page.props.branches || []);
const businesses = ref(page.props.businesses || []); // Expect businesses to be passed from backend

const form = useForm({
  device_uuid: '',
  business_id: '',
  branch_id: '',
});

const uuidReady = ref(false);
const isSaving = ref(false);

function getDeviceUUID() {
  let uuid = localStorage.getItem('device_uuid');
  if (!uuid) {
    uuid = uuidv4();
    localStorage.setItem('device_uuid', uuid);
  }
  return uuid;
}

onMounted(() => {
  form.device_uuid = getDeviceUUID();
  uuidReady.value = !!form.device_uuid;
});

const filteredBranches = computed(() => {
  if (!form.business_id) return [];
  return branches.value.filter(b => b.business_id == form.business_id);
});

const canRegister = computed(() => {
  return (
    form.device_uuid &&
    form.business_id &&
    form.branch_id &&
    uuidReady.value &&
    !isSaving.value
  );
});

function showToast({ title, icon = 'info', timer = 2000, showLoading = false }) {
  Swal.fire({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: timer,
    title: title,
    icon: icon,
    didOpen: showLoading ? () => Swal.showLoading() : undefined,
  });
}

// Watch for Inertia flash messages and show as toast
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: flash.success,
        showConfirmButton: false,
        timer: 2000,
      });
    }
    if (flash?.error) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: flash.error,
        showConfirmButton: false,
        timer: 3000,
      });
    }
  },
  { immediate: true }
);

function registerDevice() {
  if (!canRegister.value) return;
  isSaving.value = true;
  showToast({ title: 'Saving...', icon: 'info', timer: 999999, showLoading: true });
  form.post('/devices', {
    preserveScroll: true,
    onSuccess: () => {
      Swal.close();
      form.reset();
      form.device_uuid = getDeviceUUID();
      uuidReady.value = !!form.device_uuid;
      isSaving.value = false;
      // No need to reload, redirect will update page
    },
    onError: (errors) => {
      Swal.close();
      showToast({ title: 'Failed to register device', icon: 'error', timer: 4000 });
      isSaving.value = false;
    },
    onFinish: () => {
      isSaving.value = false;
    },
  });
}

function removeDevice(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This device will be removed and cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, remove it!',
    cancelButtonText: 'Cancel',
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Removing device...',
        showConfirmButton: false,
        timer: 999999,
        didOpen: () => Swal.showLoading(),
      });
      router.delete(`/devices/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
          Swal.close();
          // No need to reload, redirect will update page
        },
        onError: () => {
          Swal.close();
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Failed to remove device',
            showConfirmButton: false,
            timer: 3000,
          });
        },
      });
    }
  });
}

function formatDate(date) {
  if (!date) return '';
  return new Date(date).toLocaleString();
}
</script> 