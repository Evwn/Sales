<template>
  <AppLayout title="Create Supplier">
    <PageHeader title=" Create Supplier"  />
    <div class="max-w-5xl mx-auto mt-8 bg-white shadow rounded-lg p-8">
      <form @submit.prevent="submit" class="flex flex-col md:flex-row gap-8">
        <div class="flex-1 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" required placeholder="e.g. Erastus Even" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" placeholder="e.g. erastuseven@gmail.com" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="form.phone" placeholder="e.g. 01111383064" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <input v-model="form.address" placeholder="e.g. 123 Moi Avenue, Nairobi" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
        </div>
        <div class="flex-1 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Credit Limit</label>
            <input v-model.number="form.credit_limit" type="number" placeholder="e.g. 10000" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Balance</label>
            <input v-model.number="form.balance" type="number" placeholder="e.g. 0" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
              <option :value="1">Active</option>
              <option :value="0">Inactive</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
            <select v-model="form.business_id" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="" disabled>Select business</option>
              <option v-for="b in props.businesses" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select v-model="form.branch_id" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="" disabled>Select branch</option>
              <option v-for="br in props.branches.filter(br => br.business_id === form.business_id)" :key="br.id" :value="br.id">{{ br.name }}</option>
            </select>
          </div>
        </div>
        <div class="flex-1 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
            <input v-model="form.account_name" placeholder="e.g. Erastus Even" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
            <input v-model="form.account_number" placeholder="e.g. 01111383064" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
            <input v-model="form.bank_name" placeholder="e.g. Equity Bank" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Code / SWIFT <span class='text-xs text-gray-400'>(optional, for international payments)</span></label>
            <v-select
              v-model="form.bank_code"
              :options="bankOptions"
              :reduce="bank => bank.swift"
              label="label"
              placeholder="Search bank by name, country, or SWIFT/BIC code"
              class="w-full"
              :clearable="true"
            />
            <p class="text-xs text-gray-400 mt-1">Bank code is optional. For international payments, use the SWIFT/BIC code.</p>
          </div>
          <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow transition">Save</button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PageHeader from '@/components/ui/PageHeader.vue';
import vSelect from 'vue-select';
import Swal from 'sweetalert2';

const bankOptions = [
  { label: 'Equity Bank Kenya (68) - EQBLKENA', swift: 'EQBLKENA' },
  { label: 'KCB Bank Kenya (01) - KCBLKENX', swift: 'KCBLKENX' },
  { label: 'Absa Bank Kenya (03) - BARCKENX', swift: 'BARCKENX' },
  { label: 'Standard Chartered Kenya (02) - SCBLKENX', swift: 'SCBLKENX' },
  { label: 'Bank of America (USA) - BOFAUS3N', swift: 'BOFAUS3N' },
  { label: 'JPMorgan Chase (USA) - CHASUS33', swift: 'CHASUS33' },
  { label: 'Citibank (USA) - CITIUS33', swift: 'CITIUS33' },
  { label: 'Wells Fargo (USA) - WFBIUS6S', swift: 'WFBIUS6S' },
  { label: 'HSBC Bank (UK) - MIDLGB22', swift: 'MIDLGB22' },
  { label: 'Barclays Bank (UK) - BARCGB22', swift: 'BARCGB22' },
  { label: 'Standard Chartered Bank (UK) - SCBLGB2L', swift: 'SCBLGB2L' },
  { label: 'Deutsche Bank (Germany) - DEUTDEFF', swift: 'DEUTDEFF' },
  { label: 'Commerzbank (Germany) - COBADEFF', swift: 'COBADEFF' },
  { label: 'BNP Paribas (France) - BNPAFRPP', swift: 'BNPAFRPP' },
  { label: 'Société Générale (France) - SOGEFRPP', swift: 'SOGEFRPP' },
  { label: 'Credit Suisse (Switzerland) - CRESCHZZ80A', swift: 'CRESCHZZ80A' },
  { label: 'UBS AG (Switzerland) - UBSWCHZH80A', swift: 'UBSWCHZH80A' },
  { label: 'ING Bank (Netherlands) - INGBNL2A', swift: 'INGBNL2A' },
  { label: 'Rabobank (Netherlands) - RABONL2U', swift: 'RABONL2U' },
  { label: 'Banco Santander (Spain) - BSCHESMM', swift: 'BSCHESMM' },
  { label: 'BBVA (Spain) - BBVAESMM', swift: 'BBVAESMM' },
  { label: 'UniCredit (Italy) - UNCRITMM', swift: 'UNCRITMM' },
  { label: 'Intesa Sanpaolo (Italy) - BCITITMM', swift: 'BCITITMM' },
  { label: 'Bank of China (China) - BKCHCNBJ', swift: 'BKCHCNBJ' },
  { label: 'ICBC (China) - ICBKCNBJ', swift: 'ICBKCNBJ' },
  { label: 'State Bank of India (India) - SBININBB', swift: 'SBININBB' },
  { label: 'HDFC Bank (India) - HDFCINBB', swift: 'HDFCINBB' },
  { label: 'National Australia Bank (Australia) - NATAAU3303M', swift: 'NATAAU3303M' },
  { label: 'Westpac (Australia) - WPACAU2S', swift: 'WPACAU2S' },
  { label: 'RBC Royal Bank (Canada) - ROYCCAT2', swift: 'ROYCCAT2' },
  { label: 'Scotiabank (Canada) - NOSCCATT', swift: 'NOSCCATT' },
];
const props = defineProps({
  businesses: Array,
  branches: Array,
});

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  credit_limit: 0,
  balance: 0,
  status: 1,
  account_name: '',
  account_number: '',
  bank_name: '',
  bank_code: '',
  business:'',
  branch_id:'',

});

function submit() {
  Swal.fire({
    title: 'Saving...',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });
  router.post('/suppliers', form.value, {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Supplier created!',
        showConfirmButton: false,
        timer: 1800
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errors && Object.values(errors).length ? Object.values(errors).join('\n') : 'Failed to create supplier.',
        showConfirmButton: true
      });
    },
    onFinish: () => {
      Swal.hideLoading && Swal.close();
    }
  });
}
</script>

<style>
@import "vue-select/dist/vue-select.css";
</style> 