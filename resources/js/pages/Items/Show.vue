<template>
  <AppLayout title="Item Details">
    <div class="max-w-4xl mx-auto p-6">
      <div class="mb-4">
        <a href="/items" class="inline-flex items-center gap-2 text-blue-600 hover:underline font-semibold bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          Back to Items
        </a>
      </div>
      <div class="flex flex-col md:flex-row gap-8">
        <div class="flex-shrink-0">
          <div class="w-40 h-40 rounded-xl border bg-gray-50 flex items-center justify-center overflow-hidden">
            <img v-if="hasImage" :src="`/storage/${props.item.image_path}`" alt="Item Image" class="object-contain w-full h-full" />
            <div v-else class="text-gray-400 text-6xl">🛒</div>
          </div>
        </div>
        <div class="flex-1">
          <h1 class="text-3xl font-bold mb-2">{{ props.item.name }}</h1>
          <div class="flex flex-wrap gap-2 mb-2">
            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">SKU: {{ props.item.sku }}</span>
            <span v-if="props.item.barcode" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Barcode: {{ props.item.barcode }}</span>
            <span v-if="props.item.category_id" class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">Category: {{ props.categories?.find(c => c.id == props.item.category_id)?.name }}</span>
            <span v-if="mainUnit" class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Unit: {{ mainUnit.name }} ({{ mainUnit.short_code }})</span>
            <span v-if="props.item.is_for_sale" class="bg-green-200 text-green-900 px-2 py-1 rounded text-xs font-semibold">For Sale</span>
            <span v-if="props.item.track_stock" class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-semibold">Stock Tracked</span>
            <span v-if="isComposite" class="bg-pink-100 text-pink-800 px-2 py-1 rounded text-xs font-semibold">Composite</span>
            <span v-if="hasVariants" class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs font-semibold">Variants</span>
          </div>
          <div class="mb-2 text-gray-700">{{ props.item.description }}</div>
          <div class="grid grid-cols-2 gap-4 mb-2">
            <div><span class="font-semibold">Brand:</span> {{ props.item.brand || '-' }}</div>
            <div><span class="font-semibold">Model:</span> {{ props.item.model || '-' }}</div>
            <div><span class="font-semibold">Price:</span> <span v-if="props.item.price">KSh{{ props.item.price }}</span><span v-else>-</span></div>
            <div><span class="font-semibold">Cost:</span> <span v-if="props.item.cost">KSh{{ props.item.cost }}</span><span v-else>-</span></div>
            <div><span class="font-semibold">In Stock:</span> {{ props.item.in_stock ?? '-' }}</div>
            <div><span class="font-semibold">Low Stock Alert:</span> {{ props.item.low_stock ?? '-' }}</div>
            <div><span class="font-semibold">Batch Size:</span> {{ props.item.batch_size ?? '-' }}</div>
          </div>
          <div class="mb-2">
            <span class="font-semibold">Taxable:</span>
            <span>{{ props.item.is_taxable ? 'Yes' : 'No' }}</span>
            <span v-if="props.item.is_taxable && taxGroup" class="ml-2">({{ taxGroup.code }} - {{ taxGroup.description }} {{ taxGroup.rate }}%)</span>
          </div>
        </div>
      </div>

      <div v-if="isComposite" class="mt-8">
        <h2 class="text-xl font-bold mb-2">Composite Components</h2>
        <table class="min-w-full border text-sm bg-white rounded-xl shadow">
          <thead>
            <tr class="bg-gray-100">
              <th class="border px-2 py-1 text-left">Component</th>
              <th class="border px-2 py-1 text-center">Quantity</th>
              <th class="border px-2 py-1 text-right">Cost</th>
              <th class="border px-2 py-1 text-center">Type</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="comp in props.item.compositeComponents || []" :key="comp.sku">
              <td class="border px-2 py-1">{{ comp.name }}</td>
              <td class="border px-2 py-1 text-center">{{ comp.qty }}</td>
              <td class="border px-2 py-1 text-right">KSh{{ comp.cost }}</td>
              <td class="border px-2 py-1 text-center">
                <span v-if="comp.is_variant" class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs font-semibold">Variant</span>
                <span v-else class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-xs font-semibold">Main Item</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="hasVariants" class="mt-8">
        <h2 class="text-xl font-bold mb-2">Variants</h2>
        <table class="min-w-full border text-sm bg-white rounded-xl shadow">
          <thead>
            <tr class="bg-gray-100">
              <th class="border px-2 py-1">Variant</th>
              <th class="border px-2 py-1">Price</th>
              <th class="border px-2 py-1">Cost</th>
              <th class="border px-2 py-1">SKU</th>
              <th class="border px-2 py-1">Barcode</th>
              <th class="border px-2 py-1">Stock</th>
              <th class="border px-2 py-1">Unit Value</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="variant in props.item.variant_matrix || []" :key="variant.sku">
              <td class="border px-2 py-1">{{ Object.values(variant.options).join(' / ') }}</td>
              <td class="border px-2 py-1">KSh{{ variant.price }}</td>
              <td class="border px-2 py-1">KSh{{ variant.cost }}</td>
              <td class="border px-2 py-1">{{ variant.sku }}</td>
              <td class="border px-2 py-1">{{ variant.barcode }}</td>
              <td class="border px-2 py-1">{{ variant.stock ?? '-' }}</td>
              <td class="border px-2 py-1">{{ variant.unit_value ?? '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="props.item.modifiers && props.item.modifiers.length" class="mt-8">
        <h2 class="text-xl font-bold mb-2">Modifiers</h2>
        <ul class="flex flex-wrap gap-2">
          <li v-for="mod in props.item.modifiers" :key="mod.id" class="bg-blue-50 text-blue-800 px-3 py-1 rounded-full text-sm">{{ mod.name }}</li>
        </ul>
      </div>

      <div v-if="props.item.discounts && props.item.discounts.length" class="mt-8">
        <h2 class="text-xl font-bold mb-2">Discounts</h2>
        <ul class="flex flex-wrap gap-2">
          <li v-for="disc in props.item.discounts" :key="disc.id" class="bg-pink-50 text-pink-800 px-3 py-1 rounded-full text-sm">{{ disc.type }}: {{ disc.value }}{{ disc.type === 'percent' ? '%' : '' }}</li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
  item: Object,
  categories: Array,
  units: Array,
  taxGroups: Array,
  modifiers: Array,
  discounts: Array,
});

const hasVariants = computed(() => props.item && props.item.variant_matrix && props.item.variant_matrix.length > 0);
const isComposite = computed(() => props.item && props.item.is_composite);
const hasImage = computed(() => props.item && props.item.image_path);
const mainUnit = computed(() => props.units?.find(u => u.id == props.item.unit_id));
const taxGroup = computed(() => props.taxGroups?.find(t => t.id == props.item.tax_group_id));
</script>
<style scoped>
table { box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
th, td { font-size: 0.95rem; }
</style>
