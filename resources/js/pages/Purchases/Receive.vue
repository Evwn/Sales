<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
          <button @click="$inertia.visit(`/purchases/${purchase.id}`)" class="text-gray-500 hover:text-blue-600 focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <h1 class="text-2xl font-bold">Receive Items</h1>
        </div>
      </div>
      <div class="bg-white rounded shadow-lg p-8">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold">Items</h2>
          <button class="text-blue-600 font-semibold" @click="markAllReceived">MARK ALL RECEIVED</button>
        </div>
        <table class="w-full mb-6">
          <thead>
            <tr class="text-xs text-gray-500">
              <th class="text-left py-2">Item</th>
              <th class="text-center py-2">Ordered</th>
              <th class="text-center py-2">Received</th>
              <th class="text-center py-2">Price</th>
              <th class="text-center py-2">To receive</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in items" :key="item.id" class="border-t">
              <td>
                <div class="font-semibold">{{ item.item?.name }}</div>
                <div class="text-xs text-gray-400">SKU {{ item.item?.sku }}</div>
              </td>
              <td class="text-center">{{ item.quantity_ordered }}</td>
              <td class="text-center">{{ item.quantity_received }}</td>
              <td class="text-center">
                <template v-if="!hasPrice(item)">
                  <input
                    type="number"
                    min="0"
                    step="0.01"
                    v-model.number="form.items[idx].price"
                    class="w-20 border rounded px-2 py-1 text-center"
                    placeholder="Enter price"
                    required
                  />
                </template>
                <template v-else>
                  {{ getPrice(item) }}
                </template>
              </td>
              <td class="text-center">
                <input
                  type="number"
                  min="0"
                  :max="item.quantity_ordered - item.quantity_received"
                  v-model.number="form.items[idx].to_receive"
                  @input="validateToReceive(idx)"
                  class="w-16 border rounded px-2 py-1 text-center"
                />
              </td>
            </tr>
          </tbody>
        </table>

        <div class="mb-6">
          <h3 class="font-semibold mb-2">Additional cost</h3>
          <div v-for="cost in additional_costs" :key="cost.label" class="flex items-center mb-1">
            <input type="checkbox" :id="cost.label" :value="cost.label" v-model="form.additional_costs" class="mr-2" />
            <label :for="cost.label" class="flex-1">{{ cost.label }}</label>
            <span class="ml-2 font-semibold">{{ Number(cost.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button class="btn btn-secondary" @click="$inertia.visit(`/purchases/${purchase.id}`)">CANCEL</button>
          <button class="btn btn-primary" @click="handleReceive">RECEIVE</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
  purchase: Object,
  items: Array,
  additional_costs: Array
});

const items = props.items;
const additional_costs = props.additional_costs;
const purchase = props.purchase;

const page = usePage();
const handledFlash = ref(false);

function handleFlash() {
  if (handledFlash.value) return;
  if (page.props.flash) {
    if (page.props.flash.missing_prices) {
      handledFlash.value = true;
      showPriceEntryModal(page.props.flash.missing_prices, false);
      return;
    }
    if (page.props.flash.error) {
      handledFlash.value = true;
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: page.props.flash.error,
        showConfirmButton: false,
        timer: 4000,
      });
      return;
    }
    if (page.props.flash.success) {
      handledFlash.value = true;
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: page.props.flash.success,
        showConfirmButton: false,
        timer: 4000,
      });
      return;
    }
  }
}

onMounted(() => {
  handledFlash.value = false;
  handleFlash();
});

watch(
  () => page.props,
  () => {
    handledFlash.value = false;
    handleFlash();
  }
);

const form = ref({
  items: items.map(item => ({
    id: item.id,
    to_receive: 0,
    price: item.item?.price ?? item.stockItem?.price ?? '',
    variant_id: item.variant_id ?? item.stockItem?.variant_id ?? null,
  })),
  additional_costs: [],
});

function hasPrice(item) {
  return (item.item?.price != null && item.item?.price !== '') ||
         (item.stockItem?.price != null && item.stockItem?.price !== '');
}
function getPrice(item) {
  return item.item?.price ?? item.stockItem?.price ?? '';
}

const markAllReceived = () => {
  form.value.items = items.map(item => ({
    id: item.id,
    to_receive: item.quantity_ordered - item.quantity_received,
  }));
};

const handleReceive = () => {
  // Validate prices
  for (let idx = 0; idx < items.length; idx++) {
    if (!hasPrice(items[idx]) && (!form.value.items[idx].price || Number(form.value.items[idx].price) <= 0)) {
      Swal.fire({
        icon: 'error',
        title: 'Missing Price',
        text: `Please enter a valid price for ${items[idx].item?.name || 'item'} (SKU ${items[idx].item?.sku || ''})`,
      });
      return;
    }
  }
  // Check if all items will be fully received and not all costs are checked
  const allWillBeFullyReceived = items.every((item, idx) =>
    (item.quantity_received + (form.value.items[idx].to_receive || 0)) >= item.quantity_ordered
  );
  const uncheckedCosts = additional_costs
    .filter(cost => !form.value.additional_costs.includes(cost.label))
    .map(cost => cost.label);

  if (allWillBeFullyReceived && uncheckedCosts.length > 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Some additional costs are not applied',
      text: 'Some additional costs are not checked and will be discarded. Continue?',
      showCancelButton: true,
      confirmButtonText: 'Yes, continue',
      cancelButtonText: 'Cancel',
    }).then(result => {
      if (result.isConfirmed) {
        submitReceive(true);
      }
    });
  } else {
    submitReceive(false);
  }
};

const validateToReceive = (idx) => {
  const max = items[idx].quantity_ordered - items[idx].quantity_received;
  if (form.value.items[idx].to_receive > max) {
    form.value.items[idx].to_receive = max;
  }
  if (form.value.items[idx].to_receive < 0) {
    form.value.items[idx].to_receive = 0;
  }
};

const submitReceive = (forceDiscardCosts) => {
  handledFlash.value = false; // Reset before submit
  const payload = {
    ...form.value,
    force_discard_costs: forceDiscardCosts,
    prices: {},
  };
  items.forEach((item, idx) => {
    if (!hasPrice(item)) {
      payload.prices[item.item.id] = form.value.items[idx].price;
    }
  });
  router.post(`/purchases/${purchase.id}/receive`, payload, {
    onSuccess: () => {
      // No need to handle flash here; handled in onMounted/watch
    },
    onError: (err) => {
      // Handle 409 warning for additional costs
      if (err.response && err.response.status === 409 && err.response.data.warning) {
        Swal.fire({
          icon: 'warning',
          title: 'Warning',
          text: err.response.data.warning,
          showCancelButton: true,
          confirmButtonText: 'Yes, continue',
          cancelButtonText: 'Cancel',
        }).then(result => {
          if (result.isConfirmed) {
            submitReceive(true);
          }
        });
      } else if (err && err.response && err.response.status === 422 && err.response.data && err.response.data.errors) {
        // Inertia validation errors
        const errors = err.response.data.errors;
        const firstError = Object.values(errors)[0];
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: Array.isArray(firstError) ? firstError[0] : firstError,
          showConfirmButton: false,
          timer: 4000,
        });
      }
    }
  });
};

function showPriceEntryModal(missingPrices, forceDiscardCosts) {
  let priceInputs = {};
  Swal.fire({
    title: 'Enter Prices for Missing Items',
    html: `<div style='text-align:left;'>${missingPrices.map(item => `
      <div style='margin-bottom:8px;'>
        <label style='font-weight:bold;'>${item.name} <span style='color:#888;'>(SKU ${item.sku})</span></label><br/>
        <input type='number' min='0' step='0.01' id='price_${item.item_id}' class='swal2-input' placeholder='Enter price' style='width:150px;'/>
      </div>
    `).join('')}</div>`,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Submit',
    preConfirm: () => {
      let allFilled = true;
      let prices = {};
      missingPrices.forEach(item => {
        const val = document.getElementById('price_' + item.item_id).value;
        if (!val || isNaN(val) || Number(val) <= 0) {
          allFilled = false;
        } else {
          prices[item.item_id] = Number(val);
        }
      });
      if (!allFilled) {
        Swal.showValidationMessage('Please enter a valid price for all items.');
        return false;
      }
      return prices;
    }
  }).then(result => {
    if (result.isConfirmed && result.value) {
      submitReceive(forceDiscardCosts, result.value);
    }
  });
}
</script>

<style scoped>
.btn { @apply px-4 py-2 rounded font-semibold shadow; }
.btn-primary { @apply bg-green-600 text-white hover:bg-green-700; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300; }
</style> 