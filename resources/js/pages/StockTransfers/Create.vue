<template>
  <AppLayout>
    <div class="p-6 max-w-4xl mx-auto">
      <PageHeader title="Create Stock Transfer" :button="{ text: 'Back to Stock Transfers', link: '/stock-transfers' }" />
      <form @submit.prevent="submitStockTransfer" class="space-y-8">
        <div class="bg-white rounded shadow p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block font-semibold mb-1">Source store</label>
              <select v-model="form.from_store_id" required class="input w-full">
                <option value="">Select</option>
                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}{{ l.location_type?.name ? ' (' + l.location_type.name + ')' : '' }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Destination store</label>
              <select v-model="form.to_store_id" required class="input w-full">
                <option value="">Select</option>
                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}{{ l.location_type?.name ? ' (' + l.location_type.name + ')' : '' }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Date of transfer order</label>
              <input type="date" v-model="form.date" class="input w-full" required />
            </div>
            <div>
              <label class="block font-semibold mb-1">Notes</label>
              <textarea v-model="form.notes" class="input w-full" maxlength="500" rows="2" placeholder="Notes"></textarea>
              <div class="text-xs text-right text-gray-400">{{ form.notes.length }}/500</div>
            </div>
          </div>
        </div>
        <div class="bg-white rounded shadow p-4">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-lg">Items</h3>
            <button type="button" class="text-green-600 font-semibold" @click="importItems">IMPORT</button>
          </div>
          <div class="overflow-x-auto mb-4">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="border-b">
                  <th class="py-2 px-2 text-left">Item</th>
                  <th class="py-2 px-2 text-center">Source stock</th>
                  <th class="py-2 px-2 text-center">Destination stock</th>
                  <th class="py-2 px-2 text-center">Quantity</th>
                  <th class="py-2 px-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in form.items" :key="idx" class="border-b hover:bg-gray-50">
                  <td class="py-2 px-2">
                    <div>{{ item.is_variant ? (getProductName(item) + ' ' + getProductOptionsString(item)) : getProductName(item) }}</div>
                    <div class="text-xs text-gray-500">SKU {{ getProductSku(item) }}</div>
                  </td>
                  <td class="py-2 px-2 text-center min-w-[60px]">
                    {{ getStock(item, form.from_store_id) }}
                  </td>
                  <td class="py-2 px-2 text-center min-w-[60px]">
                    {{ getStock(item, form.to_store_id) }}
                  </td>
                  <td class="py-2 px-2 text-center">
                    <input v-model.number="item.quantity" type="number" min="1" class="input w-20 text-center" required />
                  </td>
                  <td class="py-2 px-2 text-center">
                    <button type="button" class="text-gray-400 hover:text-red-600" @click="removeItem(idx)">🗑️</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="relative flex items-center gap-2 mt-2 mb-4">
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
                  <span>Source stock: {{ getStock(result, form.from_store_id) }}{{ locations.find(l => l.id === form.from_store_id)?.location_type?.name ? ' (' + locations.find(l => l.id === form.from_store_id).location_type.name + ')' : '' }}</span>
                  <span>Destination stock: {{ getStock(result, form.to_store_id) }}{{ locations.find(l => l.id === form.to_store_id)?.location_type?.name ? ' (' + locations.find(l => l.id === form.to_store_id).location_type.name + ')' : '' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="flex justify-end mt-6">
          <button type="submit" class="btn btn-primary px-8 py-2">Save</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({
  locations: Array,
  items: Array,
  stockMap: Object,
});
const form = reactive({
  from_store_id: '',
  to_store_id: '',
  date: new Date().toISOString().slice(0, 10),
  reference: '',
  notes: '',
  items: [],
});
const stockMap = ref(props.stockMap || {});
const allItems = ref(props.items || []);
const itemSearch = ref('');
const searchResults = ref([]);
const showDropdown = ref(false);
let dropdownEl = null;
function searchItems() {
  if (!itemSearch.value) {
    searchResults.value = allItems.value.slice().sort((a, b) => a.name.localeCompare(b.name));
  } else {
    const q = itemSearch.value.toLowerCase();
    searchResults.value = allItems.value
      .filter(item => item.name.toLowerCase().includes(q) || (item.sku && item.sku.toLowerCase().includes(q)))
      .sort((a, b) => a.name.localeCompare(b.name));
  }
}
function onItemSearchFocus() {
  showDropdown.value = true;
  searchResults.value = allItems.value.slice().sort((a, b) => a.name.localeCompare(b.name));
}
function onItemSearchBlur() {
  setTimeout(() => { showDropdown.value = false; }, 100);
}
function handleDocumentClick(e) {
  if (!dropdownEl) return;
  if (!dropdownEl.contains(e.target)) {
    showDropdown.value = false;
  }
}
onMounted(() => {
  searchResults.value = allItems.value;
  dropdownEl = document.getElementById('item-search-dropdown');
  document.addEventListener('mousedown', handleDocumentClick);
});
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleDocumentClick);
});
function addItem(product) {
  // Prevent duplicates for both variants and non-variants using item_id and variant_id
  if (!form.items.some(i =>
    i.item_id === product.item_id &&
    (i.variant_id === product.variant_id || (!i.variant_id && !product.variant_id))
  )) {
    form.items.push({
      item_id: product.item_id,
      variant_id: product.variant_id,
      name: product.name,
      sku: product.sku,
      is_variant: product.is_variant,
      optionsString: product.optionsString,
      quantity: 1,
    });
  }
  itemSearch.value = '';
  searchResults.value = [];
  showDropdown.value = false;
}
function removeItem(idx) {
  form.items.splice(idx, 1);
}
function getStock(item, location_id) {
  if (!location_id) return '';
  const key = `${location_id}_${item.item_id}_${item.is_variant && item.variant_id ? item.variant_id : 0}`;
  return stockMap.value[key] !== undefined ? stockMap.value[key] : '-';
}
function getProductName(item) {
  if (item.is_variant && item.variant_id) {
    const v = props.items.find(p => p.id === item.variant_id && p.is_variant);
    return v ? v.name : '';
  } else {
    const i = props.items.find(p => p.id === item.item_id && !p.is_variant);
    return i ? i.name : '';
  }
}
function getProductSku(item) {
  if (item.is_variant && item.variant_id) {
    const v = props.items.find(p => p.id === item.variant_id && p.is_variant);
    return v ? v.sku : '';
  } else {
    const i = props.items.find(p => p.id === item.item_id && !p.is_variant);
    return i ? i.sku : '';
  }
}
function getProductOptionsString(item) {
  if (item.is_variant && item.variant_id) {
    const v = props.items.find(p => p.id === item.variant_id && p.is_variant);
    return v ? v.optionsString : '';
  } else {
    return '';
  }
}
function importItems() {
  // Placeholder for import logic
  alert('Import not implemented');
}
const submitStockTransfer = () => {
  router.post('/stock-transfers', form);
};
</script>
<style scoped>
.input { border: 1px solid #d1d5db; border-radius: 4px; padding: 0.5rem; }
.btn { background: #2563eb; color: #fff; border-radius: 4px; padding: 0.5rem 1rem; font-weight: 600; }
.btn-outline-primary { background: #fff; color: #2563eb; border: 1px solid #2563eb; }
.btn-primary { background: #2563eb; color: #fff; }
.material-icons { font-size: 20px; vertical-align: middle; }
</style> 