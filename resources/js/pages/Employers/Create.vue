<template>
  <AppLayout>
    <div class="max-w-md mx-auto p-6">
      <form @submit.prevent="submit">
        <div class="bg-white rounded shadow p-8 flex flex-col items-center">
          <div class="w-full text-left text-xs text-gray-500 mb-4">Fields marked with <span class="text-red-500">*</span> are required.</div>
          <div class="w-20 h-20 rounded-full bg-teal-600 flex items-center justify-center mb-6">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a4 4 0 014-4h0a4 4 0 014 4v2"/></svg>
          </div>
          <div class="w-full mb-4">
            <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" required placeholder="Name" class="w-full border-b border-gray-300 focus:border-teal-600 outline-none text-lg text-center" />
          </div>
          <div class="w-full flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 12a4 4 0 01-8 0V8a4 4 0 018 0v4z"/><path d="M12 16v2m0 4h.01"/></svg>
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
              <input v-model="form.email" type="email" required placeholder="Email" class="w-full border-b border-gray-300 focus:border-teal-600 outline-none" />
            </div>
          </div>
          <div class="w-full flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10a9 9 0 0118 0c0 7-9 13-9 13S3 17 3 10z"/></svg>
            <input v-model="form.phone" type="text" placeholder="Phone" class="flex-1 border-b border-gray-300 focus:border-teal-600 outline-none" />
          </div>
          <div class="w-full flex items-center mb-6">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 12a4 4 0 01-8 0V8a4 4 0 018 0v4z"/><path d="M12 16v2m0 4h.01"/></svg>
            <select v-model="form.role" class="flex-1 border-b border-gray-300 focus:border-teal-600 outline-none bg-transparent">
              <option value="">Select role</option>
              <option v-for="role in roles" :key="role.id" :value="role.name">
                {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                (
                <span v-for="(guard, idx) in role.guards" :key="guard">
                  {{ guard === 'pos' ? 'POS' : guard === 'backoffice' ? 'Back office' : guard }}<span v-if="idx < role.guards.length - 1"> & </span>
                </span>
                )
              </option>
            </select>
          </div>
          <div class="w-full flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700">Business <span class="text-red-500">*</span></label>
              <select v-model="form.business_id" required class="w-full border-b border-gray-300 focus:border-teal-600 outline-none bg-transparent">
                <option value="" disabled>Select business</option>
                <option v-for="business in businesses" :key="business.id" :value="business.id">{{ business.name }}</option>
              </select>
            </div>
          </div>
          <div v-if="form.business_id" class="w-full flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            <select v-model="form.branch_id" class="flex-1 border-b border-gray-300 focus:border-teal-600 outline-none bg-transparent">
              <option value="" disabled>Select branch</option>
              <option v-for="branch in filteredBranches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div class="w-full flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2z"/><path d="M12 13v4m0 4h.01"/></svg>
            <input
              v-model="form.password"
              type="password"
              placeholder="Password"
              :class="[
                'flex-1 border-b outline-none',
                form.password
                  ? passwordValid
                    ? 'border-green-500 focus:border-green-600'
                    : 'border-red-500 focus:border-red-600'
                  : 'border-gray-300 focus:border-teal-600'
              ]"
            />
          </div>
          <div v-if="form.password" class="w-full text-xs mt-1 mb-2 text-gray-600">
            <ul>
              <li :class="{'text-green-600': form.password.length >= 8, 'text-red-600': form.password && form.password.length < 8}">
                • At least 8 characters
              </li>
              <li :class="{'text-green-600': /[a-z]/.test(form.password), 'text-red-600': form.password && !/[a-z]/.test(form.password)}">
                • Lowercase letter
              </li>
              <li :class="{'text-green-600': /[A-Z]/.test(form.password), 'text-red-600': form.password && !/[A-Z]/.test(form.password)}">
                • Uppercase letter
              </li>
              <li :class="{'text-green-600': /[0-9]/.test(form.password), 'text-red-600': form.password && !/[0-9]/.test(form.password)}">
                • Number
              </li>
              <li :class="{'text-green-600': /[^a-zA-Z0-9]/.test(form.password), 'text-red-600': form.password && !/[^a-zA-Z0-9]/.test(form.password)}">
                • Symbol
              </li>
            </ul>
          </div>
          <div class="w-full flex flex-col items-center mb-4">
            <div class="flex gap-2">
              <input v-for="(digit, idx) in pinDigits" :key="idx" ref="pinInputs" v-model="pinDigits[idx]" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]*" class="w-12 h-12 text-center border-b border-gray-300 focus:border-teal-600 outline-none text-xl" @input="onPinInput(idx, $event)" @keydown.backspace="onPinBackspace(idx, $event)" />
            </div>
            <div class="text-xs text-gray-500 mt-1">PIN Code (4 digits will be used in pos)</div>
          </div>
          <div v-if="errorMsg" class="w-full text-center text-red-600 text-sm mb-2">{{ errorMsg }}</div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button type="button" class="px-6 py-2 rounded border border-gray-300 text-gray-700 bg-white hover:bg-gray-100" @click="$inertia.visit('/employers')">CANCEL</button>
          <button type="submit" :disabled="isSaving" class="px-6 py-2 rounded bg-green-500 text-white font-semibold hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed">
            <span v-if="isSaving">Saving...</span>
            <span v-else>SAVE</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';
const props = defineProps({ roles: Array, businesses: Array, branches: Array });
const form = useForm({
  name: '',
  email: '',
  phone: '',
  role: '',
  business_id: '',
  branch_id: '',
  password: '',
  pin_code: '',
});
const pinDigits = ref(['', '', '', '']);
const pinInputs = ref([]);
const errorMsg = ref('');
const isSaving = ref(false);
const passwordValid = computed(() => {
  const val = form.password;
  return (
    val.length >= 8 &&
    /[a-z]/.test(val) &&
    /[A-Z]/.test(val) &&
    /[0-9]/.test(val) &&
    /[^a-zA-Z0-9]/.test(val)
  );
});
const filteredBranches = computed(() => {
  if (!form.business_id) return [];
  return props.branches.filter(b => b.business_id == form.business_id);
});
function onPinInput(idx, e) {
  const val = e.target.value.replace(/\D/g, '');
  pinDigits.value[idx] = val;
  if (val && idx < 3) {
    nextTick(() => pinInputs.value[idx + 1]?.focus());
  }
}
function onPinBackspace(idx, e) {
  if (!pinDigits.value[idx] && idx > 0) {
    nextTick(() => pinInputs.value[idx - 1]?.focus());
  }
}
const submit = () => {
  form.pin_code = pinDigits.value.join('');
  if (!form.password && !form.pin_code) {
    errorMsg.value = 'Please provide either a password or a 4-digit PIN code.';
    return;
  }
  if (form.password && !passwordValid.value) {
    errorMsg.value = 'Password does not meet requirements.';
    return;
  }
  if (form.pin_code && !/^\d{4}$/.test(form.pin_code)) {
    errorMsg.value = 'PIN code must be exactly 4 digits.';
    return;
  }
  errorMsg.value = '';
  isSaving.value = true;
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'info',
    title: 'Saving...',
    showConfirmButton: false,
    timer: 999999,
    didOpen: () => Swal.showLoading(),
  });
  form.post('/employers', {
    onSuccess: (page) => {
      Swal.close();
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: page.props.flash?.success || 'Employee created successfully!',
        showConfirmButton: false,
        timer: 2000,
      });
      isSaving.value = false;
      router.visit('/employers');
    },
    onError: (errors) => {
      Swal.close();
      let msg = errors?.error || Object.values(errors).join(', ') || 'Failed to create employee.';
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: msg,
        showConfirmButton: false,
        timer: 3000,
      });
      isSaving.value = false;
    },
    onFinish: () => {
      isSaving.value = false;
    },
  });
};
</script>
<style scoped>
input, select {
  background: transparent;
}
</style> 