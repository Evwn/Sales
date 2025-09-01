<template>
  <AppLayout title="Quotation">
    <PageHeader :title="`Quotation ${quotation.reference}`" :button="{ text: 'Back', link: '/quotations', icon: AddIcon }" />

    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <button class="btn btn-success" @click="openEmailModal">  SEND</button>
          <div class="p-6 lg:p-8 space-y-6">

            <div class="bg-white/60 p-4 rounded grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <div class="text-sm text-gray-600">Reference</div>
                <div class="font-semibold">{{ quotation.reference }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-600">Date</div>
                <div class="font-semibold">{{ quotation.quotation_date }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-600">Status</div>
                <div class="font-semibold">{{ quotation.status }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-600">Requisition</div>
                <div class="font-semibold">{{ quotation.requisition?.reference }} — {{ quotation.requisition?.location?.name }}</div>
              </div>
            </div>

            <div class="bg-white/60 p-4 rounded overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow">
                <thead class="bg-[#B76E79]/80 backdrop-blur-md">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Item</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Qty</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Unit Price</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Line Total</th>
                  </tr>
                </thead>
                <tbody class="bg-white/60 divide-y divide-gray-200">
                  <tr v-for="qi in quotation.items" :key="qi.id">
                    <td class="px-4 py-2">{{ qi.item?.name }}</td>
                    <td class="px-4 py-2">{{ Number(qi.quantity).toFixed(2) }}</td>
                    <td class="px-4 py-2">{{ Number(qi.unit_price).toFixed(2) }}</td>
                    <td class="px-4 py-2">{{ (Number(qi.quantity) * Number(qi.unit_price)).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
              <div class="mt-6 text-right font-semibold">Grand Total: {{ grandTotal.toFixed(2) }}</div>
            </div>

            <div class="bg-white/60 p-4 rounded">
              <div class="text-sm text-gray-600 mb-1">Notes</div>
              <div class="whitespace-pre-line">{{ quotation.notes || '—' }}</div>
            </div>

          </div>
        </div>
      </div>
    </div>
        <div v-if="showEmailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-[#B76E79]/70">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h2 class="text-lg font-semibold mb-4">Send purchase order by email</h2>
        <div class="mb-2">
          <label class="block text-xs text-gray-500 mb-1">From</label>
          <input type="email" class="input w-full px-3 bg-gray-100 py-2 hover:bg-gray-200 cursor-pointer " v-model="emailForm.from" readonly />
        </div>
        <div class="mb-2">
          <label class="block text-xs text-gray-500 mb-1">To</label>
          <div type="email" class="input w-full px-3 bg-gray-100 py-2 hover:bg-gray-200 cursor-pointer"v-for="qi in quotation.suppliers" :key="qi.id" readonly>{{ qi.email }},</div>
        </div>
        <div class="mb-2">
          <label class="block text-xs text-gray-500 mb-1">Cc</label>
          <input type="text" class="input w-full px-3 bg-gray-100 py-2 hover:bg-gray-200 " v-model="emailForm.cc" placeholder="Separate multiple emails with commas" />
        </div>
        <div class="mb-2">
          <label class="block text-xs text-gray-500 mb-1">Subject</label>
          <input type="text" class="input w-full px-3 bg-gray-100 py-2 hover:bg-gray-200 " v-model="emailForm.subject" />
        </div>
        <div class="mb-2">
          <label class="block text-xs text-gray-500 mb-1">Message</label>
          <textarea class="input w-full px-3 bg-gray-100 py-2 hover:bg-gray-200 " v-model="emailForm.message" rows="3"></textarea>
        </div>
        <div class="mb-4">
          <label class="block text-xs text-gray-500 mb-1">Attachment</label>
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l7.07-7.07a4 4 0 00-5.656-5.657l-7.07 7.07a6 6 0 108.485 8.485l6.364-6.364"/></svg>
            <span class="text-sm">purchase.reference.pdf</span>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button class="btn btn-secondary" @click="showEmailModal = false" :disabled="sendingEmail">CANCEL</button>
          <button class="btn btn-primary" @click="sendEmail" :disabled="sendingEmail">
            {{ sendingEmail ? 'Sending...' : 'SEND' }}
          </button>
        </div>
      </div>
  </div>
  </AppLayout>
</template>

<script setup>
import { computed,ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import AddIcon from '@/components/AddIcon.vue'
import { usePage, router,Link} from '@inertiajs/vue3';

const props = defineProps({ quotation: { type: Object, required: true } })
const showEmailModal = ref(false);
const page = usePage();
const sendingEmail = ref(false);
const emailForm = ref({
  from: page.props.auth?.user?.email || '',
  suppliers: props.quotation.suppliers.map(r => ({ id: r.supplier_id,email:r.supplier_email})),
  cc: '',
  subject: 'Working',
  message: '',
});

const openEmailModal = () => {
  showEmailModal.value = true;
    emailForm.value = {
    from: page.props.auth?.user?.email || '',
    suppliers: props.quotation.suppliers.map(r => ({ id: r.id,email:r.email})),
    cc: '',
    subject: "Working",
    message: '',
  };
};
const sendEmail = () => {
  sendingEmail.value = true;
  console.log('here');
    router.post(
    `/quotations/${props.quotation.id}/send-email`,
    { ...emailForm.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        sendingEmail.value = false;
        showEmailModal.value = false;
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Quotations sent by email!',
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true,
        });
      },
      onError: (errors) => {
        sendingEmail.value = false;
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'Error!',
          text: errors?.error || 'Could not send email.',
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true,
        });
      },
    }
  );
}

const grandTotal = computed(() => (props.quotation.items||[])
  .reduce((s, r) => s + (Number(r.quantity||0) * Number(r.unit_price||0)), 0))

</script>