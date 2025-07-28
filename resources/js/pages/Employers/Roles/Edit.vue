<template>
  <AppLayout>
    <div class="max-w-md mx-auto p-6">
      <form @submit.prevent="submit">
        <div class="bg-white rounded shadow p-8">
          <input v-model="form.name" type="text" placeholder="Role name" class="w-full border-b border-gray-300 focus:border-teal-600 outline-none text-lg mb-2" :class="{'border-red-500': errors.name, 'bg-gray-100 text-gray-400': isOwnerRole}" :disabled="isOwnerRole" />
          <div v-if="errors.name" class="text-red-500 text-xs mb-2">{{ errors.name }}</div>
          <div class="mb-4">
            <label :class="isOwnerRole ? 'text-gray-400' : ''"><input type="checkbox" value="pos" v-model="form.guards" :disabled="isOwnerRole" /> POS</label>
            <label class="ml-6" :class="isOwnerRole ? 'text-gray-400' : ''"><input type="checkbox" value="backoffice" v-model="form.guards" :disabled="isOwnerRole" /> Back office</label>
          </div>
          <div v-if="form.guards.includes('pos')" class="mb-4">
            <div class="font-semibold mb-2">POS Permissions</div>
            <div v-for="perm in permissions.pos" :key="perm">
              <label :class="isOwnerRole ? 'text-gray-400' : ''"><input type="checkbox" :value="perm" v-model="form.permissions.pos" :disabled="isOwnerRole" /> {{ perm }}</label>
            </div>
          </div>
          <div v-if="form.guards.includes('backoffice')" class="mb-4">
            <div class="font-semibold mb-2">Back Office Permissions</div>
            <div v-for="perm in permissions.backoffice" :key="perm">
              <label :class="isOwnerRole ? 'text-gray-400' : ''"><input type="checkbox" :value="perm" v-model="form.permissions.backoffice" :disabled="isOwnerRole" /> {{ perm }}</label>
            </div>
          </div>
          <div v-if="errors.permissions" class="text-red-500 text-xs mb-2">{{ errors.permissions }}</div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button type="button" class="px-6 py-2 rounded border border-gray-300 text-gray-700 bg-white hover:bg-gray-100" @click="$inertia.visit('/employers/access-control')">CANCEL</button>
          <button type="submit" class="px-6 py-2 rounded bg-green-500 text-white font-semibold hover:bg-green-600" :disabled="saving || isOwnerRole">{{ saving ? 'Saving...' : 'SAVE' }}</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

const page = usePage();
const props = page.props;
const permissions = props.permissions || { pos: [], backoffice: [] };
const role = props.role;

const isOwnerRole = computed(() => role.name && role.name.toLowerCase() === 'owner');

const form = ref({
  name: role.name || '',
  guards: role.guards || [],
  permissions: {
    pos: role.permissions?.pos ? [...role.permissions.pos] : [],
    backoffice: role.permissions?.backoffice ? [...role.permissions.backoffice] : [],
  },
});
const errors = ref({ name: '', permissions: '' });
const saving = ref(false);

function validate() {
  errors.value = { name: '', permissions: '' };
  if (!form.value.name.trim()) {
    errors.value.name = 'Role name is required.';
  }
  const selectedPerms = [
    ...(form.value.guards.includes('pos') ? form.value.permissions.pos : []),
    ...(form.value.guards.includes('backoffice') ? form.value.permissions.backoffice : []),
  ];
  if (selectedPerms.length === 0) {
    errors.value.permissions = 'Select at least one permission.';
  }
  return !errors.value.name && !errors.value.permissions;
}

const showLoadingToast = () => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'info',
    title: '<span class="swal-spinner"></span> Saving...',
    showConfirmButton: false,
    timer: 0,
    timerProgressBar: false,
    didOpen: toast => {
      toast.querySelector('.swal-spinner').innerHTML = `<svg class='animate-spin' style='width:1.2em;height:1.2em;vertical-align:middle;margin-right:0.5em' fill='none' viewBox='0 0 24 24'><circle class='opacity-25' cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4'></circle><path class='opacity-75' fill='currentColor' d='M4 12a8 8 0 018-8v8z'></path></svg>`;
    }
  });
};

const submit = () => {
  if (isOwnerRole.value) return;
  if (!validate()) return;
  saving.value = true;
  showLoadingToast();
  router.post(`/employers/roles/${role.name}/update`, {
    name: form.value.name,
    guards: form.value.guards,
    permissions: {
      pos: form.value.guards.includes('pos') ? form.value.permissions.pos : [],
      backoffice: form.value.guards.includes('backoffice') ? form.value.permissions.backoffice : [],
    },
  }, {
    onSuccess: () => {
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: page.props.flash?.success || 'Role updated!', showConfirmButton: false, timer: 2000 });
      saving.value = false;
    },
    onError: () => {
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: page.props.flash?.error || 'Failed to update role', showConfirmButton: false, timer: 3000 });
      saving.value = false;
    },
    onFinish: () => { saving.value = false; },
  });
};
</script>
<style scoped>
.swal2-popup .swal-spinner svg {
  display: inline-block;
  vertical-align: middle;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
.animate-spin { animation: spin 1s linear infinite; }
</style> 