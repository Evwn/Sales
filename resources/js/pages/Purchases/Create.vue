<template>
  <AppLayout>
    <div class="p-6 max-w-4xl mx-auto">
      <PageHeader title="Create Purchase" :button="{ text: 'Back to Purchases', link: '/purchases' }" />
      <form @submit.prevent="submitPurchase" class="space-y-8">
        <div class="bg-white rounded shadow p-4 mb-6">
          <!-- Top Row: Supplier, Store, Dates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
              <select v-model="form.supplier_id" class="w-full border rounded px-3 py-2">
                <option value="">Select supplier</option>
                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
              <select v-model="form.location_id" class="w-full border rounded px-3 py-2">
                <option value="">Select location</option>
                <option v-for="s in stores" :key="s.id" :value="s.id">
                  {{ s.name }}{{ s.location_type?.name ? ' (' + s.location_type.name + ')' : '' }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Purchase order date</label>
              <input v-model="form.order_date" type="date" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Expected on</label>
              <input v-model="form.expected_date" type="date" class="w-full border rounded px-3 py-2" />
            </div>
          </div>
          <!-- Notes -->
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="form.notes" rows="2" maxlength="500" class="w-full border rounded px-3 py-2"></textarea>
            <div class="text-xs text-gray-400 text-right">{{ form.notes.length }}/500</div>
          </div>
        </div>
        <!-- Items Table -->
        <div class="bg-white rounded shadow p-4">
          <div class="flex justify-between items-center mb-2">
            <h2 class="text-lg font-semibold">Items</h2>
            <div class="flex gap-4">
              <div class="flex items-center gap-2">
                <select v-model="selectedAutofill" class="border rounded px-3 py-2 text-sm">
                  <option value="">Select autofill option</option>
                  <option value="supplier_items">Items from Supplier</option>
                  <option value="low_stock">Low Stock Items</option>
                </select>
                <button
                  type="button"
                  @click="autofillItems"
                  :disabled="!selectedAutofill"
                  class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  AUTOFILL
                </button>
              </div>
            </div>
          </div>
          <table class="min-w-full text-sm mb-2">
            <thead>
              <tr class="border-b">
                <th class="text-left py-1">Item</th>
                <th class="text-center py-1">In stock</th>
                <th class="text-center py-1">Incoming</th>
                <th class="text-center py-1">Quantity</th>
                <th class="text-center py-1">Purchase cost</th>
                <th class="text-right py-1">Amount</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in form.items" :key="item.id || idx" class="border-b hover:bg-gray-50">
                <td>
                  <div>{{ item.is_variant ? (item.name + ' ' + item.optionsString) : item.name }}</div>
                  <div class="text-xs text-gray-500">SKU {{ item.sku }}</div>
                </td>
                <td class="text-center">{{ item.in_stock ?? 0 }}</td>
                <td class="text-center">{{ item.incoming ?? 0 }}</td>
                <td class="text-center">
                  <input v-model.number="item.quantity" type="number" min="0" class="w-16 border rounded px-2 py-1 text-center" />
                </td>
                <td class="text-center">
                  <input v-model.number="item.purchase_cost" type="number" min="0" step="0.01" class="w-20 border rounded px-2 py-1 text-center" />
                </td>
                <td class="text-right">Kes {{ (item.quantity * item.purchase_cost).toFixed(2) }}</td>
                <td class="text-center">
                  <button type="button" class="text-gray-400 hover:text-red-600" @click="removeItem(idx)">🗑️</button>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- Search Item -->
          <div class="relative flex items-center gap-2 mt-2">
            <input
              v-model="itemSearch"
              @input="searchItems"
              @focus="onItemSearchFocus"
              @blur="onItemSearchBlur"
              placeholder="Search Item"
              class="w-full border rounded px-3 py-2"
            />
            <div
              v-if="showDropdown && searchResults.length"
              id="item-search-dropdown"
              class="absolute left-0 top-full mt-1 w-full bg-white border rounded shadow z-20"
            >
              <div v-for="result in searchResults" :key="result.id" class="px-4 py-2 cursor-pointer hover:bg-gray-100" @mousedown.prevent="addItem(result)">
                <div class="flex justify-between">
                  <span>{{ result.is_variant ? (result.name + ' ' + result.optionsString) : result.name }}</span>
                  <span class="text-xs text-gray-500">SKU {{ result.sku }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-500">
                  <span>In stock: {{ result.in_stock }}</span>
                  <span v-if="result.is_composite" class="text-indigo-600 font-semibold ml-2">Composite</span>
                  <span>Cost: {{ result.cost ?? 0 }}</span>
                </div>
              </div>
            </div>
          </div>
    </div>
        <!-- Additional Cost Section -->
        <div class="bg-white rounded shadow p-4 mt-6">
          <div class="flex justify-between items-center mb-2">
            <span class="font-semibold">Additional cost</span>
            <span class="font-semibold">Amount</span>
    </div>
          <div v-for="(cost, idx) in form.additional_costs" :key="idx" class="flex items-center gap-2 mb-2">
            <input v-model="cost.label" placeholder="Description" class="flex-1 border rounded px-3 py-2" />
            <input v-model.number="cost.amount" type="number" min="0" step="0.01" class="w-32 border rounded px-3 py-2 text-right" />
            <button type="button" class="text-gray-400 hover:text-red-600" @click="removeAdditionalCost(idx)">🗑️</button>
    </div>
          <button type="button" class="flex items-center gap-2 text-green-700 font-semibold mt-2" @click="addAdditionalCost">
            <span class="text-xl">➕</span> ADD ADDITIONAL COST
          </button>
    </div>
        <!-- Save Button -->
        <div class="flex justify-end mt-6">
          <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow" :disabled="isSubmitting">
            <span v-if="isSubmitting">Saving...</span>
            <span v-else>Save Purchase</span>
          </button>
    </div>
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({ suppliers: Array, stores: Array, items: Array });
const form = reactive({
  supplier_id: '',
  location_id: '',
  order_date: '',
  expected_date: '',
  notes: '',
  items: [],
  additional_costs: [],
});
const itemSearch = ref('');
const searchResults = ref([]);
const allItems = ref(props.items || []);
const showDropdown = ref(false);
let dropdownEl = null;
const isSubmitting = ref(false);
const selectedAutofill = ref('');

// Watch for location changes and update stock data
watch(() => form.location_id, async (newLocationId) => {
  if (newLocationId) {
    try {
      const response = await fetch(`/purchases/location-stock?location_id=${newLocationId}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const locationStockData = await response.json();
      
      // Update the items with location-specific stock data, filtering out composite items
      allItems.value = locationStockData.filter(item => !item.is_composite);
      
      // Update existing items in the form with new stock data
      form.items.forEach(formItem => {
        const stockItem = locationStockData.find(item => {
          if (formItem.is_variant) {
            // For variants in the form, match by variant ID
            return item.is_variant && item.id === formItem.variant_id;
          } else {
            // For regular items in the form, match by item ID and not a variant
            return !item.is_variant && item.id === formItem.id;
          }
        });
        
        if (stockItem) {
          formItem.in_stock = stockItem.in_stock;
          formItem.incoming = stockItem.incoming;
          formItem.max_stock_level = stockItem.max_stock_level;
          formItem.purchase_cost = stockItem.recent_purchase_price ?? 0;
          
          // Recalculate quantity using max stock formula: max_stock_level - in_stock - incoming
          const maxStock = stockItem.max_stock_level || 0;
          const currentQuantity = stockItem.in_stock || 0;
          const incomingStock = stockItem.incoming || 0;
          
          let calculatedQuantity = maxStock - currentQuantity - incomingStock;
          
          // Apply rules: if max_stock is null or result is negative, use 0
          if (stockItem.max_stock_level === null || calculatedQuantity < 0) {
            calculatedQuantity = 0;
          }
          
          formItem.quantity = Math.max(0, calculatedQuantity);
        }
      });
    } catch (error) {
      console.error('Error fetching location stock:', error);
    }
  }
});

function searchItems() {
  // Filter out composite items and sort in frontend only
  const nonCompositeItems = allItems.value.filter(item => !item.is_composite);
  
  if (!itemSearch.value) {
    searchResults.value = nonCompositeItems.slice().sort((a, b) => a.name.localeCompare(b.name));
  } else {
    const q = itemSearch.value.toLowerCase();
    searchResults.value = nonCompositeItems
      .filter(item => item.name.toLowerCase().includes(q) || (item.sku && item.sku.toLowerCase().includes(q)))
      .sort((a, b) => a.name.localeCompare(b.name));
  }
}

function onItemSearchFocus(e) {
  showDropdown.value = true;
  // Always show all non-composite items immediately, sorted
  const nonCompositeItems = allItems.value.filter(item => !item.is_composite);
  searchResults.value = nonCompositeItems.slice().sort((a, b) => a.name.localeCompare(b.name));
}

function onItemSearchBlur(e) {
  // Delay to allow click on dropdown
  setTimeout(() => { showDropdown.value = false; }, 100);
}

function handleDocumentClick(e) {
  if (!dropdownEl) return;
  if (!dropdownEl.contains(e.target)) {
    showDropdown.value = false;
  }
}

onMounted(() => {
  // Use non-composite items from backend, no need to fetch
  const nonCompositeItems = allItems.value.filter(item => !item.is_composite);
  searchResults.value = nonCompositeItems;
  dropdownEl = document.getElementById('item-search-dropdown');
  document.addEventListener('mousedown', handleDocumentClick);
});
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleDocumentClick);
});

function addItem(item) {
  // Check if item already exists in the form
  // For regular items: check by item.id and variant_id is null
  // For variants: check by parent item.id and variant_id matches
  const exists = form.items.some(i => {
    if (item.is_variant) {
      // For variants, check if same parent item and variant combination exists
      return i.id === item.parent_item_id && i.variant_id === item.id;
    } else {
      // For regular items, check if same item exists (variant_id should be null)
      return i.id === item.id && i.variant_id === null;
    }
  });
  
  if (!exists) {
    // Calculate quantity using max stock formula: max_stock_level - in_stock - incoming
    const maxStock = item.max_stock_level || 0;
    const currentQuantity = item.in_stock || 0;
    const incomingStock = item.incoming || 0;
    
    let calculatedQuantity = maxStock - currentQuantity - incomingStock;
    
    // Apply rules: if max_stock is null or result is negative, use 0
    if (item.max_stock_level === null || calculatedQuantity < 0) {
      calculatedQuantity = 0;
    }
    
    const finalQuantity = Math.max(0, calculatedQuantity);
    
    form.items.push({
      id: item.is_variant ? item.parent_item_id : item.id,
      variant_id: item.is_variant ? item.id : null,
      name: item.name,
      sku: item.sku,
      in_stock: item.in_stock || 0,
      incoming: item.incoming || 0,
      max_stock_level: item.max_stock_level || null,
      quantity: finalQuantity,
      purchase_cost: item.recent_purchase_price ?? 0,
      is_composite: item.is_composite,
      is_variant: item.is_variant,
      optionsString: item.optionsString,
    });
  }
  itemSearch.value = '';
  searchResults.value = [];
  showDropdown.value = false;
}
function removeItem(idx) {
  form.items.splice(idx, 1);
}
function addAdditionalCost() {
  form.additional_costs.push({ label: '', amount: 0 });
}
function removeAdditionalCost(idx) {
  form.additional_costs.splice(idx, 1);
}
function importItems() {
  Swal.fire('Import not implemented yet.');
}
function autofillItems() {
  if (!selectedAutofill.value) return;

  // Validate supplier selection for supplier items
  if (selectedAutofill.value === 'supplier_items' && !form.supplier_id) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Please select a supplier first for "Items from Supplier" option.',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
    return;
  }

  // Validate location selection for low stock items
  if (selectedAutofill.value === 'low_stock' && !form.location_id) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Please select a location first for "Low Stock Items" option.',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
    return;
  }

  // Validate both supplier and location for supplier items
  if (selectedAutofill.value === 'supplier_items' && !form.location_id) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Please select both supplier and location for "Items from Supplier" option.',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
    return;
  }

  // Fetch and add items directly
  fetchAndAddItems();
}

async function fetchAndAddItems() {
  try {
    let itemsToAdd = [];
    if (selectedAutofill.value === 'supplier_items') {
      const response = await fetch(`/purchases/supplier-items?supplier_id=${form.supplier_id}&location_id=${form.location_id}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      itemsToAdd = await response.json();
    } else if (selectedAutofill.value === 'low_stock') {
      const response = await fetch(`/purchases/low-stock-items?location_id=${form.location_id}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      itemsToAdd = await response.json();
    }

    if (itemsToAdd.length === 0) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'No items found for autofill.',
        showConfirmButton: false,
        timer: 3000,
      });
      return;
    }

    // Merge fetched items with current location's stock data
    const mergedItems = itemsToAdd.map(fetchedItem => {
      // Find matching item in allItems (current location's stock data)
      const stockItem = allItems.value.find(item => {
        if (fetchedItem.is_variant) {
          return item.is_variant && item.id === fetchedItem.id;
        } else {
          return !item.is_variant && item.id === fetchedItem.id;
        }
      });

      if (stockItem) {
        // Merge fetched data with current location's stock data
        return {
          ...fetchedItem,
          in_stock: stockItem.in_stock,
          incoming: stockItem.incoming,
          max_stock_level: stockItem.max_stock_level,
          // Keep the recent_purchase_price from fetched data (supplier items or low stock items)
          recent_purchase_price: fetchedItem.recent_purchase_price,
        };
      }

      // If not found in current location data, use fetched data as is
      return fetchedItem;
    });

    for (const item of mergedItems) {
      addItem(item);
    }

    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: `${mergedItems.length} item(s) added for autofill.`,
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
  } catch (error) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Error autofill items',
      text: error.message,
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
    });
  }
}

function submitPurchase() {
  // Frontend validation
  if (!form.supplier_id) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Supplier is required', showConfirmButton: false, timer: 3000 });
    return;
  }
  if (!form.location_id) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Store is required', showConfirmButton: false, timer: 3000 });
    return;
  }
  if (!form.order_date) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Purchase date is required', showConfirmButton: false, timer: 3000 });
    return;
  }
  if (!form.expected_date) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Expected date is required', showConfirmButton: false, timer: 3000 });
    return;
  }
  if (!form.items.length) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'At least one item is required', showConfirmButton: false, timer: 3000 });
    return;
  }
  isSubmitting.value = true;
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'info',
    title: 'Saving purchase...',
    showConfirmButton: false,
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });
  // Prepare data for backend
  const payload = {
    supplier_id: form.supplier_id,
    location_id: form.location_id,
    order_date: form.order_date,
    expected_date: form.expected_date,
    notes: form.notes,
    additional_costs: form.additional_costs,
    items: form.items.map(item => ({
      item_id: item.id, // always parent item id
      variant_id: item.variant_id ?? null, // variant id if present
      quantity_ordered: item.quantity,
      purchase_cost: item.purchase_cost,
    })),
  };
  router.post('/purchases', payload, {
    onSuccess: () => {
      Swal.close();
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Purchase created successfully!',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
      });
      // Optionally redirect here if not handled by Inertia
      // router.visit('/purchases');
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
</script>
<style scoped>
input, select, textarea { outline: none; }
</style> 