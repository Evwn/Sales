<template>
  <AppLayout>
    <Head title="Edit Seller" />
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit User
        </h2>
        <Link
          href="/sellers"
          class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Back to Users
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6">Edit Seller</h2>
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input v-model="form.name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input v-model="form.email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Business</label>
            <select v-model="selectedBusiness" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              <option value="">Select a business</option>
              <option v-for="biz in allBusinesses" :key="biz.id" :value="biz.id">{{ biz.name }}</option>
            </select>
          </div>
          <div class="mb-4" v-if="branchesForSelectedBusiness.length">
            <label class="block text-sm font-medium text-gray-700">Branch</label>
            <select v-model="form.branch_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              <option value="">Select a branch</option>
              <option v-for="branch in branchesForSelectedBusiness" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Password (leave blank to keep unchanged)</label>
            <input v-model="form.password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="form.processing">Update Seller</button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  seller: Object,
  businesses: {
    type: Array,
    default: () => []
  }
});

const form = useForm({
  name: props.seller.name,
  email: props.seller.email,
  password: '',
  password_confirmation: '',
  business_id: props.seller.branch?.business?.id || '',
  branch_id: props.seller.branch?.id || '',
});

const selectedBusiness = ref(form.business_id);

const allBusinesses = computed(() => {
  const list = [...(props.businesses || [])];
  const sellerBizId = props.seller.branch?.business?.id;
  if (sellerBizId && !list.some(b => b.id == sellerBizId)) {
    // Add the seller's business as a fallback
    list.push({
      id: sellerBizId,
      name: props.seller.branch.business.name,
      branches: [props.seller.branch] // fallback, at least include the current branch
    });
  }
  return list;
});

const branchesForSelectedBusiness = computed(() => {
  const biz = allBusinesses.value.find(b => b.id == selectedBusiness.value);
  return biz ? (biz.branches || []) : [];
});

watch(selectedBusiness, (val) => {
  if (val && !branchesForSelectedBusiness.value.some(b => b.id == form.branch_id)) {
    form.branch_id = '';
  }
  form.business_id = val;
});

function submit() {
  form.business_id = selectedBusiness.value || (props.seller.branch?.business?.id || '');
  form.branch_id = form.branch_id || (props.seller.branch?.id || '');
  if (!form.business_id || !form.branch_id) {
    Swal.fire({
      icon: 'error',
      title: 'Missing Data',
      text: 'No business or branch is available for this seller.',
      confirmButtonText: 'OK'
    });
    return;
  }
  Swal.fire({
    title: 'Updating Seller...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
  form.put(`/businesses/${form.business_id}/branches/${form.branch_id}/sellers/${props.seller.id}`, {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Seller updated!',
        text: 'Seller details have been updated and the seller has been notified.',
        timer: 2000,
        showConfirmButton: false
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Update failed',
        text: errors.message || 'Failed to update seller details.',
        confirmButtonText: 'OK'
      });
    }
  });
}
</script> 
