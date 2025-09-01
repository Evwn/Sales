<template>
  <AppLayout>
    <div class="flex items-center mb-4" style="margin-top: 1rem;">
      <button class="btn btn-secondary flex items-center gap-2" @click="$inertia.visit('/purchases')">
        <span class="text-lg">&#60;</span>
        <span>Back to Purchases</span>
      </button>
    </div>
    <div class="p-6 max-w-3xl mx-auto">
      <div class="bg-white rounded shadow-lg p-8">
        <!-- Header: Order number, status, actions -->
        <div class="flex justify-between items-start mb-6">
          <div>
            <div class="text-gray-500 text-xs mb-1">Purchase orders</div>
            <h1 class="text-2xl font-bold mb-1">{{ purchase.reference }}</h1>
            <div class="text-gray-500 text-sm mb-2 capitalize">{{ purchase.status }}</div>
          </div>
          <div class="flex gap-2 items-center mt-1">
            <button
              v-if="purchase.status === 'draft'"
              class="btn btn-primary"
              @click="approvePurchase"
              :disabled="approving"
            >
              {{ approving ? 'Approving...' : 'APPROVE' }}
            </button>
            <button
              v-else-if="purchase.status !== 'completed' && purchase.status !== 'cancelled' && purchase.status !== 'canceled' && purchase.status !== 'complete' && purchase.status !== 'cancled' && purchase.status !== 'complete'"
              class="btn btn-primary"
              @click="receivePurchase"
            >
              RECEIVE
            </button>
            <button class="btn btn-secondary" @click="editPurchase">EDIT</button>
            <button class="btn btn-success" @click="openEmailModal">SEND</button>
            <div class="relative" @mouseleave="showMore = false">
              <button class="btn btn-outline flex items-center gap-1" @click="showMore = !showMore">
                MORE
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
              </button>
              <div v-if="showMore" class="absolute right-0 mt-2 w-48 bg-white border rounded shadow z-10">
                <button class="dropdown-item" @click="exportAs('pdf')">Save as PDF</button>
                <button class="dropdown-item" @click="exportAs('csv')">Save as CSV</button>
                <button class="dropdown-item" @click="duplicatePurchase">Duplicate</button>
                <button class="dropdown-item" @click="printPage">Print Tables</button>
                <button class="dropdown-item text-red-600" @click="cancelRemainingItems">Cancel Remaining Items</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Dates, Ordered by, Supplier, Store -->
        <div class="flex flex-col md:flex-row md:justify-between gap-6 mb-6">
          <div class="flex-1">
            <div class="mb-1 text-gray-600 text-sm">Order date: <span class="font-semibold">{{ formatDate(purchase.order_date) }}</span></div>
            <div class="mb-1 text-gray-600 text-sm">Expected on: <span class="font-semibold">{{ formatDate(purchase.expected_date) }}</span></div>
            <div class="mb-1 text-gray-600 text-sm">Ordered by: <span class="font-semibold">{{ orderedByRole || '-' }}</span></div>
          </div>
          <div class="flex-1">
            <div class="mb-1 text-gray-600 text-sm">Supplier:</div>
            <div class="font-semibold">{{ purchase.supplier?.name }}</div>
            <div class="text-gray-600 text-sm">{{ purchase.supplier?.contact_name }}</div>
            <div class="text-gray-600 text-sm">{{ purchase.supplier?.phone }}</div>
            <div class="text-gray-600 text-sm">{{ purchase.supplier?.email }}</div>
            <div class="text-gray-600 text-sm">{{ purchase.supplier?.address }}</div>
          </div>
          <div class="flex-1">
            <div class="mb-1 text-gray-600 text-sm">Destination location:</div>
            <div class="font-semibold">
              {{ purchase.location?.name }}
              <span v-if="purchase.location?.location_type?.name"> ({{ purchase.location.location_type.name }})</span>
            </div>
          </div>
        </div>
        <!-- Received bar -->
        <div class="flex items-center gap-2 mb-6">
          <span class="text-gray-600 text-sm">Received</span>
          <span class="text-xs">{{ totalReceived }} of {{ totalOrdered }}</span>
        </div>
        <!-- Items Table -->
        <div class="mb-8">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
              <tr>
                <th class="px-3 py-2 text-left">Item</th>
                <th class="px-2 py-2 text-left">SKU</th>
                <th class="px-2 py-2 text-center w-16">Qty</th>
                <th class="px-2 py-2 text-center w-16">Rec'd</th>
                <th class="px-2 py-2 text-center w-16">Rem.</th>
                <th class="px-2 py-2 text-right w-20">Cost</th>
                <th class="px-3 py-2 text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in purchase.items" :key="item.id">
                <td class="px-3 py-2">
                  <div class="font-medium">
                    {{ item.item?.name }}
                    <span v-if="item.variant?.options" class="text-gray-600">
                      {{ formatOptions(item.variant.options) }}
                    </span>
                  </div>
                  <div class="text-xs text-gray-400">
                    SKU {{ getItemSku(item) }}
                    <span v-if="getItemUnit(item)" class="ml-2">
                      • {{ getItemUnit(item) }}
                    </span>
                  </div>
                </td>
                <td class="px-2 py-2">{{ getItemSku(item) }}</td>
                <td class="px-2 py-2 text-center">{{ item.quantity_ordered }}</td>
                <td class="px-2 py-2 text-center">
                  <span class="text-green-600 font-medium">{{ item.quantity_received || 0 }}</span>
                </td>
                <td class="px-2 py-2 text-center">
                  <span :class="getRemainingClass(item)" class="font-medium">
                    {{ getRemainingQuantity(item) }}
                  </span>
                </td>
                <td class="px-2 py-2 text-right">{{ formatCurrency(item.purchase_cost) }}</td>
                <td class="px-3 py-2 text-right">{{ formatCurrency(item.quantity_ordered * item.purchase_cost) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Totals -->
        <div class="flex flex-col md:flex-row md:justify-end gap-4">
          <div class="bg-gray-50 rounded shadow p-4 w-full md:w-80">
            <div class="flex justify-between mb-2">
              <span class="font-semibold">Total</span>
              <span>{{ formatCurrency(subtotal) }}</span>
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
          <input type="email" class="input w-full px-3 bg-gray-100 py-2 hover:bg-gray-200 cursor-pointer" v-model="emailForm.to" readonly />
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
            <span class="text-sm">{{ purchase.reference }}.pdf</span>
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
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { computed, ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
const props = defineProps({ purchase: Object, orderedByRole: String, storeBusinessName: String });
const showMore = ref(false);

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};
const formatCurrency = (amount) => {
  if (amount == null) return '-';
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount);
};

const formatOptions = (options) => {
  if (!options || typeof options !== 'object') return '';
  return Object.values(options).join(' / ');
};

const getItemSku = (item) => {
  return item.variant?.sku || item.item?.sku || '';
};

const getItemUnit = (item) => {
  // For variants, get unit from parent item
  if (item.variant) {
    // Variants inherit unit from parent item
    if (item.item?.unit?.name) {
      return item.item.unit.name;
    }
    // If no unit relationship, check unit_value
    if (item.variant.unit_value) {
      return `${item.variant.unit_value}`;
    }
  }
  
  // For regular items
  if (item.item?.unit?.name) {
    return item.item.unit.name;
  }
  // If no unit relationship, check unit_value
  if (item.item?.unit_value) {
    return `${item.item.unit_value}`;
  }
  
  return '';
};

const getRemainingQuantity = (item) => {
  const ordered = item.quantity_ordered || 0;
  const received = item.quantity_received || 0;
  return Math.max(0, ordered - received);
};

const getRemainingClass = (item) => {
  const remaining = getRemainingQuantity(item);
  if (remaining === 0) {
    return 'text-green-600'; // All received
  } else if (remaining === item.quantity_ordered) {
    return 'text-red-600'; // Nothing received
  } else {
    return 'text-orange-600'; // Partially received
  }
};

const subtotal = computed(() => {
  return props.purchase.items.reduce((sum, item) => sum + (item.quantity_ordered * item.purchase_cost), 0);
});
const exportAs = (format) => {
  showMore.value = false;
  router.get(`/purchases/${props.purchase.id}/export?format=${format}`);
};
const duplicatePurchase = () => {
  showMore.value = false;
  router.post(`/purchases/${props.purchase.id}/duplicate`);
};
const printPage = () => {
  showMore.value = false;
  window.print();
};
const cancelRemainingItems = () => {
  showMore.value = false;
  Swal.fire('Cancel Remaining Items', 'This will cancel all items not yet received.', 'info');
};
const receivePurchase = () => {
  router.visit(`/purchases/${props.purchase.id}/receive`);
};
const editPurchase = () => {
  router.visit(`/purchases/${props.purchase.id}/edit`);
};
const sendPurchase = () => {
  // Implement send logic here
  Swal.fire('Sent!', 'Purchase order has been sent.', 'success');
};
const totalOrdered = computed(() => props.purchase.items.reduce((sum, item) => sum + (item.quantity_ordered || 0), 0));
const totalReceived = computed(() => props.purchase.items.reduce((sum, item) => sum + (item.quantity_received || 0), 0));
const approving = ref(false);
const approvePurchase = () => {
  approving.value = true;
  router.post(`/purchases/${props.purchase.id}/approve`, {}, {
    preserveScroll: true,
    onSuccess: (page) => {
      props.purchase.status = 'pending';
      approving.value = false;
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Purchase order approved and is now pending.',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
      });
    },
    onError: (errors) => {
      approving.value = false;
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Error!',
        text: errors?.error || 'Could not approve purchase.',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
      });
    }
  });
};

const page = usePage();
const flash = computed(() => page.props.flash);

const getDefaultSubject = () => {
  let businessName = props.storeBusinessName;
  let senderName = page.props.auth?.user?.name || 'User';
  let ref = props.purchase.reference;
  return `Purchase order from ${businessName || senderName} (${ref})`;
};

const showEmailModal = ref(false);
const emailForm = ref({
  from: page.props.auth?.user?.email || '',
  to: props.purchase.supplier?.email || '',
  cc: '',
  subject: getDefaultSubject(),
  message: '',
});
const sendingEmail = ref(false);

const openEmailModal = () => {
  showEmailModal.value = true;
  emailForm.value = {
    from: page.props.auth?.user?.email || '',
    to: props.purchase.supplier?.email || '',
    cc: '',
    subject: getDefaultSubject(),
    message: '',
  };
};

const sendEmail = () => {
  sendingEmail.value = true;
  router.post(
    `/purchases/${props.purchase.id}/send-email`,
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
          title: 'Purchase order sent by email!',
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
};

onMounted(() => {
  if (flash.value?.success) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: flash.value.success,
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
  }
  if (flash.value?.error) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Error!',
      text: flash.value.error,
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
  }
});

watch(
  () => flash.value,
  (newFlash) => {
    if (newFlash?.success) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: newFlash.success,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
      });
    }
    if (newFlash?.error) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Error!',
        text: newFlash.error,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
      });
    }
  }
);
</script>

<style scoped>
.btn { @apply px-4 py-2 rounded font-semibold shadow; }
.btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300; }
.btn-success { @apply bg-green-600 text-white hover:bg-green-700; }
.btn-outline { @apply border border-gray-400 text-gray-700 hover:bg-gray-100; }
.btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
.dropdown-item { @apply block w-full text-left px-4 py-2 hover:bg-gray-100 cursor-pointer; }
.input {
  @apply border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500;
}
</style> 