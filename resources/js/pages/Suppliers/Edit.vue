<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Edit Supplier" :button="{ text: 'Back to Suppliers', link: '/suppliers' }" />
      <form @submit.prevent="submitSupplier">
        <!-- form fields -->
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import vSelect from 'vue-select';

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
const props = defineProps({ supplier: Object });
const form = ref({
  name: props.supplier.name,
  email: props.supplier.email,
  phone: props.supplier.phone,
  address: props.supplier.address,
  credit_limit: props.supplier.credit_limit,
  balance: props.supplier.balance,
  status: props.supplier.status,
  bank_details: props.supplier.bank_details,
  account_name: props.supplier.account_name,
  account_number: props.supplier.account_number,
  bank_name: props.supplier.bank_name,
  bank_code: props.supplier.bank_code
});
function submitSupplier() {
  Swal.fire({
    title: 'Saving...',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });
  router.put(`/suppliers/${props.supplier.id}`, form.value, {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Supplier updated!',
        showConfirmButton: false,
        timer: 1800
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errors && Object.values(errors).length ? Object.values(errors).join('\n') : 'Failed to update supplier.',
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