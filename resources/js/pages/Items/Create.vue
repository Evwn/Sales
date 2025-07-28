<script setup>
import { ref, onUnmounted, nextTick, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogClose from '@/components/ui/dialog/DialogClose.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import { GripVertical, Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import PageHeader from '@/components/ui/PageHeader.vue';

const skuBase = 10017;

const props = defineProps({
  businesses: Array,
  categories: Array,
  units: Array,
  taxGroups: Array,
  modifiers: Array, // [{ id, name, options: [{ id, name, price }] }]
  discounts: Array, // [{ id, type, value, starts_at, ends_at }]
  defaultSku: String,
  compositeSearchItems: Array, // [{ name: '', sku: '', optionsString: '', is_variant: false, parent_item_id: null, options: [] }]
});

const form = ref({
  business_id: '',
  category_id: '',
  name: '',
  description: '',
  brand: '',
  model: '',
  unit_id: '',
  unit_value: '',
  sku: '',
  barcode: '',
  upc: '',
  ean: '',
  isbn: '',
  mpn: '',
  sold_by: 'each',
  is_for_sale: true,
  price: '',
  cost: '',
  track_stock: true,
  is_composite: false,
  in_stock: 0,
  low_stock: '',
  is_taxable: true,
  tax_group_id: '',
  modifiers: [], // array of modifier ids
  discounts: [], // array of discount ids
  image: null,
  variants: [], // [{ name: '', values: [''] }]
  variant_matrix: [], // <-- add this
  batch_size: '', // Add batch_size
});

if (!form.value.sku && props.defaultSku) {
  form.value.sku = props.defaultSku;
}

const imagePreviewUrl = ref(null);
let objectUrl = null;

const showVariantModal = ref(false);
const variantOptions = ref([
  // { name: '', values: [''] }
]);
const variantMatrix = ref([]); // [{ key: '32/blue/fabric', options: {size: '32', color: 'blue', material: 'fabric'}, price: '', cost: '', sku: '', barcode: '' }]

const variantModalError = ref('');
const valueInputRefs = ref([]);
const shakeModal = ref(false);
const variantModalMode = ref('create'); // 'create' or 'edit'

const optionErrors = computed(() => {
  return variantOptions.value.map(opt => {
    return {
      name: (!opt.name.trim() && opt.values.length > 0) ? 'This field cannot be blank' : '',
      values: (opt.name.trim() && opt.values.length === 0) ? 'Please add at least one option value' : '',
    };
  });
});

function hasAnyOptionError() {
  return optionErrors.value.some(e => e.name || e.values);
}

function setValueInputRef(el, idx) {
  valueInputRefs.value[idx] = el;
}

const optionPlaceholders = [
  { name: 'Size', value: 'e.g., 56, 34, 23' },
  { name: 'Color', value: 'e.g., blue, black' },
  { name: 'Material', value: 'e.g., smooth, velvet' },
];

function openVariantModal(mode = 'create') {
  variantModalMode.value = mode;
  // Set placeholders for each row
  variantOptions.value.forEach((opt, i) => {
    opt.namePlaceholder = optionPlaceholders[i]?.name || 'Option name';
    opt.valuePlaceholder = optionPlaceholders[i]?.value || 'Press Enter to add the value';
  });
  showVariantModal.value = true;
}

function addOptionRow() {
  if (variantOptions.value.length < 3) {
    variantOptions.value.push({ name: '', values: [], newValue: '' });
    // Update placeholders
    variantOptions.value.forEach((opt, i) => {
      opt.namePlaceholder = optionPlaceholders[i]?.name || 'Option name';
      opt.valuePlaceholder = optionPlaceholders[i]?.value || 'Press Enter to add the value';
    });
    // Auto-focus the value input for the new option
    nextTick(() => {
      const idx = variantOptions.value.length - 1;
      const refEl = valueInputRefs.value[idx];
      if (refEl?.$el && typeof refEl.$el.focus === 'function') {
        refEl.$el.focus();
      } else if (refEl && typeof refEl.focus === 'function') {
        refEl.focus();
      }
    });
  }
}

function removeOptionRow(idx) {
  variantOptions.value.splice(idx, 1);
  // If all options are deleted, clear array reactively and clear refs
  if (variantOptions.value.length === 0) {
    variantOptions.value.splice(0, variantOptions.value.length);
    valueInputRefs.value = [];
  }
  // Update placeholders
  variantOptions.value.forEach((opt, i) => {
    opt.namePlaceholder = optionPlaceholders[i]?.name || 'Option name';
    opt.valuePlaceholder = optionPlaceholders[i]?.value || 'Press Enter to add the value';
  });
}

function closeVariantModal() {
  showVariantModal.value = false;
}

function handleOptionValueInput(opt, e) {
  const val = opt.newValue.trim();
  if ((e.key === 'Enter' || e.key === ',') && val) {
    if (!opt.values.includes(val)) {
      opt.values.push(val);
    }
    opt.newValue = '';
    e.preventDefault();
  }
}

function removeOptionValue(opt, idx) {
  opt.values.splice(idx, 1);
}

function saveVariantOptions() {
  // Validation: only require name if values are entered
  for (const opt of variantOptions.value) {
    if (opt.values.length && !opt.name.trim()) {
      variantModalError.value = 'Option name is required if you enter values.';
      return;
    }
    // Option values can be empty (optional)
    if (opt.values.some(v => !v.trim())) {
      variantModalError.value = 'All option values must be non-empty.';
      return;
    }
  }
  variantModalError.value = '';
  // Only use options with values for variant generation
  const usedOptions = variantOptions.value.filter(o => o.name && o.values.length);
  const combos = cartesianProduct(
    usedOptions.map(o => ({
      name: o.name,
      values: o.values.filter(v => v)
    }))
  );
  // Build matrix with auto-generated SKUs and default price
  const usedSkus = new Set();
  variantMatrix.value = combos.map((combo, idx) => {
    const key = Object.values(combo).join('/');
    // Try to preserve previous data
    const existing = variantMatrix.value.find(v => v.key === key);
    let sku = existing ? existing.sku : (skuBase + idx).toString();
    // Ensure SKU is unique in the matrix
    let baseSku = sku;
    let suffix = 1;
    while (usedSkus.has(sku)) {
      sku = baseSku + '-' + suffix;
      suffix++;
    }
    usedSkus.add(sku);
    return {
      key,
      options: combo,
      available: existing ? existing.available : true,
      price: existing ? existing.price : 0,
      cost: existing ? existing.cost : '',
      sku,
      barcode: existing ? existing.barcode : ''
    };
  });
  form.value.variants = variantOptions.value.map(o => ({ name: o.name, values: o.values }));
  showVariantModal.value = false;
}

function cartesianProduct(options) {
  if (!options.length) return [];
  return options.reduce((acc, opt) => {
    const res = [];
    acc.forEach(a => {
      opt.values.forEach(v => {
        res.push({ ...a, [opt.name]: v });
      });
    });
    return res;
  }, [{}]);
}

function isMatrixValid() {
  if (!variantMatrix.value.length) return true;
  return variantMatrix.value.every(v => v.price !== '' && !isNaN(Number(v.price)) && v.cost !== '' && !isNaN(Number(v.cost)));
}

const isSaving = ref(false);

const fieldErrors = ref({});

function validateForm() {
  fieldErrors.value = {};
  if (!form.value.name || !form.value.name.trim()) fieldErrors.value.name = 'Name is required';
  // Removed user_id validation as it is handled by backend
  return Object.keys(fieldErrors.value).length === 0;
}

const submitItem = () => {
  if (isSaving.value) return;
  if (!validateForm()) {
    // Show a top-right red toast for the first error
    const firstError = Object.values(fieldErrors.value)[0];
    if (firstError) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: Array.isArray(firstError) ? firstError[0] : firstError,
        showConfirmButton: false,
        timer: 3000
      });
    }
    // Optionally scroll to first error
    return;
  }
  // Set is_taxable to true if tax_group_id is set, otherwise false
  form.value.is_taxable = !!form.value.tax_group_id;
  // Ensure toggles are booleans
  form.value.track_stock = !!form.value.track_stock;
  form.value.is_composite = !!form.value.is_composite;
  // Always send compositeComponents and variant_matrix
  form.value.compositeComponents = compositeComponents.value;
  form.value.variant_matrix = variantMatrix.value;
  // Convert empty strings for price and cost to null
  if (form.value.price === '') form.value.price = null;
  if (form.value.cost === '') form.value.cost = null;
  isSaving.value = true;
  // Require at least one component if composite
  if (form.value.is_composite && compositeComponents.value.length === 0) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Please add at least one component for a composite item.',
      showConfirmButton: false,
      timer: 3000
    });
    return;
  }
  // Only set is_composite to true if there are components
  form.value.is_composite = !!(form.value.is_composite && compositeComponents.value.length > 0);
  form.value.compositeComponents = compositeComponents.value;
  // Ensure variant_options and variant_matrix are sent
  form.value.variant_options = variantOptions.value.map(opt => ({
    name: opt.name,
    values: opt.values
  }));
  form.value.variant_matrix = variantMatrix.value;
  // Prepare FormData for file upload
  const formData = new FormData();
  for (const key in form.value) {
    if (key === 'image' && form.value.image) {
      formData.append('image', form.value.image);
    } else if (key === 'compositeComponents') {
      formData.append(key, JSON.stringify(form.value[key] || []));
    } else if (
      (key === 'variant_matrix' && form.value.variant_matrix && form.value.variant_matrix.length > 0)
    ) {
      formData.append(key, JSON.stringify(form.value[key]));
    } else if (
      (key === 'variant_options' && form.value.variant_options && form.value.variant_options.length > 0)
    ) {
      formData.append(key, JSON.stringify(form.value[key]));
    } else if (
      (key === 'modifiers' && form.value.modifiers && form.value.modifiers.length > 0)
    ) {
      formData.append(key, JSON.stringify(form.value[key]));
    } else if (
      (key === 'discounts' && form.value.discounts && form.value.discounts.length > 0)
    ) {
      formData.append(key, JSON.stringify(form.value[key]));
    } else if (
      key === 'track_stock' ||
      key === 'is_composite' ||
      key === 'is_taxable' ||
      key === 'is_for_sale'
    ) {
      formData.append(key, form.value[key] ? 1 : 0);
    } else if (key === 'batch_size') {
      formData.append(key, form.value[key] ?? '');
    } else if (
      key !== 'compositeComponents' &&
      key !== 'variant_matrix' &&
      key !== 'variant_options' &&
      key !== 'modifiers' &&
      key !== 'discounts'
    ) {
      formData.append(key, form.value[key] ?? '');
    }
  }

  Swal.fire({
    title: 'Saving item...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  router.post('/items', formData, {
    forceFormData: true,
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Item created successfully!',
        timer: 2000,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: Object.values(errors).flat().join('\n'),
      });
    },
    onFinish: () => {
      Swal.hideLoading();
      isSaving.value = false;
    }
  });
};

const handleImageChange = (event) => {
  const target = event.target;
  if (target.files && target.files[0]) {
    form.value.image = target.files[0];
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
    }
    objectUrl = URL.createObjectURL(target.files[0]);
    imagePreviewUrl.value = objectUrl;
  } else {
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
      objectUrl = null;
    }
    imagePreviewUrl.value = null;
  }
};

onUnmounted(() => {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl);
  }
});

function onSaveClick() {
  if (!!variantModalError.value || variantOptions.value.length === 0 || variantOptions.value.every(o => o.values.length === 0)) {
    shakeModal.value = true;
    setTimeout(() => { shakeModal.value = false; }, 500);
    return;
  }
  saveVariantOptions();
}

const compositeComponents = ref([
  // Example: { name: 'Mango', sku: '10000', qty: 2, cost: 0 }
]);
const compositeSearch = ref('');
const showCompositeDropdown = ref(false);
const filteredCompositeItems = computed(() => {
  if (!compositeSearch.value) return props.compositeSearchItems;
  const q = compositeSearch.value.toLowerCase();
  return props.compositeSearchItems.filter(item =>
    item.name.toLowerCase().includes(q) ||
    item.optionsString.toLowerCase().includes(q) ||
    item.sku.toLowerCase().includes(q)
  );
});
function addCompositeComponent(item) {
  if (compositeComponents.value.some(c => c.sku === item.sku)) return;
  const defaultQty = 0;
  const defaultCost = 0;
  if (item.is_variant) {
  compositeComponents.value.push({
    name: item.name + (item.optionsString ? ` (${item.optionsString})` : ''),
    sku: item.sku,
      qty: defaultQty,
      cost: defaultCost,
      is_variant: true,
    parent_item_id: item.parent_item_id,
    options: item.options,
      item_cost: item.cost || 0,
      manualCost: false,
      component_variant_id: item.id,
    });
  } else {
    compositeComponents.value.push({
      name: item.name + (item.optionsString ? ` (${item.optionsString})` : ''),
      sku: item.sku,
      qty: defaultQty,
      cost: defaultCost,
      is_variant: false,
      parent_item_id: item.parent_item_id,
      options: item.options,
      item_cost: item.cost || 0,
      manualCost: false,
      id: item.id,
    });
  }
  compositeSearch.value = '';
  showCompositeDropdown.value = false;
}
function removeCompositeComponent(idx) {
  compositeComponents.value.splice(idx, 1);
}
const compositeTotalCost = computed(() => {
  return compositeComponents.value.reduce((sum, c) => sum + (parseFloat(c.cost) * parseFloat(c.qty)), 0).toFixed(2);
});

function removeVariantRow(idx) {
  variantMatrix.value.splice(idx, 1);
}

const categories = ref(props.categories ? [...props.categories] : []);
const showAddCategory = ref(false);
const newCategoryName = ref('');
const addingCategory = ref(false);

async function addCategory() {
  if (!newCategoryName.value.trim()) return;
  addingCategory.value = true;
  try {
    const res = await axios.post('/categories', {
      name: newCategoryName.value,
    });
    if (res.data && res.data.id) {
      categories.value.push(res.data);
      form.value.category_id = res.data.id;
      showAddCategory.value = false;
      newCategoryName.value = '';
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Could not add category.' });
  } finally {
    addingCategory.value = false;
  }
}

const taxGroups = ref(props.taxGroups ? [...props.taxGroups] : []);
const showAddTax = ref(false);
const newTax = ref({ code: '', description: '', rate: '' });
const addingTax = ref(false);
const taxErrors = ref({});

function openAddTax() {
  showAddTax.value = true;
  newTax.value = { code: '', description: '', rate: '' };
  taxErrors.value = {};
}

async function addTaxGroup() {
  taxErrors.value = {};
  if (!newTax.value.code.trim()) taxErrors.value.code = ['Code is required.'];
  if (!newTax.value.description.trim()) taxErrors.value.description = ['Description is required.'];
  if (newTax.value.rate === '' || newTax.value.rate === null || isNaN(Number(newTax.value.rate))) {
    taxErrors.value.rate = ['Rate is required.'];
  }
  if (Object.keys(taxErrors.value).length) return;
  addingTax.value = true;
  try {
    const res = await axios.post('/tax-groups', {
      code: newTax.value.code,
      description: newTax.value.description,
      rate: newTax.value.rate,
    });
    if (res.data && res.data.id) {
      taxGroups.value.push(res.data);
      form.value.tax_group_id = res.data.id;
      showAddTax.value = false;
      newTax.value = { code: '', description: '', rate: '' };
      taxErrors.value = {};
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Tax group added successfully',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: 'No response from server. Please try again.' });
    }
  } catch (e) {
    if (e.response && e.response.status === 422 && e.response.data.errors) {
      taxErrors.value = e.response.data.errors;
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Could not add tax group.' });
    }
  } finally {
    addingTax.value = false;
  }
}

const imageInputRef = ref(null);
const showUploadOverlay = ref(false);

function triggerImageUpload() {
  imageInputRef.value.click();
}

function handleCancel() {
  Swal.fire({
    title: 'Are you sure you want to cancel?',
    text: 'You will lose all unsaved changes.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, cancel!',
    cancelButtonText: 'No, keep editing',
  }).then((result) => {
    if (result.isConfirmed) {
      router.visit('/items');
    }
  });
}

function handleCompositeBlur() {
  setTimeout(() => {
    showCompositeDropdown.value = false;
  }, 200);
}

const hasVariants = computed(() => variantMatrix.value.length > 0);

const mainCostValue = computed(() => {
  if (form.value.is_composite && hasVariants.value) return 0;
  if (form.value.is_composite) return compositeTotalCost.value;
  return form.value.cost;
});

// Distribute composite cost to variants if both composite and variants are enabled
watch([
  () => form.value.is_composite,
  () => variantMatrix.value.length,
  () => compositeTotalCost.value
], ([isComposite, variantCount, totalCost]) => {
  if (isComposite && variantCount > 0) {
    const costPerVariant = parseFloat(totalCost) / variantCount;
    variantMatrix.value.forEach(v => {
      v.cost = isNaN(costPerVariant) ? '' : costPerVariant.toFixed(2);
    });
  }
});

// Distribute composite cost to variants proportionally to their unit_value
watch([
  () => form.value.is_composite,
  () => variantMatrix.value.length,
  () => compositeTotalCost.value,
  () => variantMatrix.value.map(v => v.unit_value).join(','),
], ([isComposite, variantCount, totalCost]) => {
  if (isComposite && variantCount > 0) {
    const totalUnitValue = variantMatrix.value.reduce((sum, v) => sum + (parseFloat(v.unit_value) || 0), 0);
    variantMatrix.value.forEach(v => {
      const unitVal = parseFloat(v.unit_value) || 0;
      v.cost = totalUnitValue > 0 ? ((unitVal / totalUnitValue) * parseFloat(totalCost)).toFixed(2) : '';
    });
  }
});

// Watch for quantity changes and update cost if not manually overridden
watch(compositeComponents, (newVal) => {
  newVal.forEach((comp, idx) => {
    if (!comp.manualCost) {
      const qty = parseFloat(comp.qty) || 0;
      const baseCost = parseFloat(comp.item_cost) || 0;
      comp.cost = (qty * baseCost).toFixed(2);
    }
  });
}, { deep: true });

// --- Per-variant composite logic ---
// Remove per-variant composite logic
// Remove variantCompositeComponents, getVariantKey, addVariantCompositeComponent, removeVariantCompositeComponent, and related template code
// Add units section for main item and variants
// In the form, add unit_id and unit_value for main item
form.value.unit_id = '';
form.value.unit_value = '';
// In the variant matrix, add unit_id and unit_value for each variant
// In the template, add a section for selecting unit and entering unit value for the main item
// For variants, add columns for unit and unit value in the variants table
// Add newUnit modal logic
const showAddUnit = ref(false);
const newUnit = ref({ name: '', short_code: '', description: '' });
const addingUnit = ref(false);
const unitErrors = ref({});
const page = usePage();

async function addUnit() {
  unitErrors.value = {};
  if (!newUnit.value.name.trim()) unitErrors.value.name = ['Name is required.'];
  if (!newUnit.value.short_code.trim()) unitErrors.value.short_code = ['Short code is required.'];
  if (Object.keys(unitErrors.value).length) return;
  addingUnit.value = true;
  router.post('/units', newUnit.value, {
    preserveScroll: true,
    onSuccess: () => {
      showAddUnit.value = false;
      newUnit.value = { name: '', short_code: '', description: '' };
      // Show success toast
      if (page.props.flash && page.props.flash.success) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: page.props.flash.success, showConfirmButton: false, timer: 2000 });
      }
      // Refetch units or reload page to get the new unit in the dropdown
      // Optionally, you can push to props.units if you fetch units via Inertia
    },
    onError: (errors) => {
      unitErrors.value = errors;
      if (page.props.flash && page.props.flash.error) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: page.props.flash.error, showConfirmButton: false, timer: 3000 });
      }
    },
    onFinish: () => {
      addingUnit.value = false;
    }
  });
}

const batchSizeManuallyEdited = ref(false);

watch([
  () => form.value.unit_id,
  () => form.value.unit_value
], ([newUnitId, newUnitValue]) => {
  if (!batchSizeManuallyEdited.value) {
    form.value.batch_size = newUnitValue || '';
  }
});

function onBatchSizeInput() {
  batchSizeManuallyEdited.value = true;
}

// Add a watcher to clear fieldErrors for name when form.value.name changes
watch(() => form.value.name, (newVal) => {
  if (fieldErrors.value.name && newVal && newVal.trim()) {
    delete fieldErrors.value.name;
  }
});
</script>
<template>
  <AppLayout>
    <PageHeader
      title="Create Item"
      :button="{ text: 'Back to Items', link: '/items', show: true }"
    />
    <form class="space-y-6 max-w-4xl mx-auto" @submit.prevent="submitItem">
      <!-- Main Card -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="grid grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <Input v-model="form.name" :class="{'border-red-600': fieldErrors.name}" placeholder="" />
            <div v-if="fieldErrors.name" class="text-xs text-red-600 mt-1">{{ fieldErrors.name }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <div class="relative">
              <select v-model="form.category_id" class="block w-full rounded border-gray-300">
                <option value="">No category</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                <option disabled>────────────</option>
                <option value="__add__">+ Add category</option>
              </select>
              <div v-if="form.category_id === '__add__' || showAddCategory" class="absolute left-0 right-0 bg-white border border-gray-300 rounded shadow p-4 z-10 mt-2">
                <div class="flex items-center gap-2">
                  <Input v-model="newCategoryName" placeholder="Category name" class="flex-1" @keyup.enter="addCategory" :disabled="addingCategory" />
                  <button type="button" class="bg-green-600 text-white px-3 py-1 rounded" @click="addCategory" :disabled="addingCategory || !newCategoryName.trim()">
                    <span v-if="addingCategory">Saving...</span>
                    <span v-else>Save</span>
                  </button>
                  <button type="button" class="text-gray-500 px-2" @click="showAddCategory = false; form.category_id = ''">Cancel</button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="block w-full rounded border border-gray-300" placeholder=""></textarea>
          </div>
          <div>
            <div class="flex items-center mb-4 mt-2">
              <input type="checkbox" v-model="form.is_for_sale" id="is_for_sale" class="mr-2" />
              <label for="is_for_sale" class="text-sm">The item is available for sale</label>
            </div>
            <!-- Unit selection and value -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
              <div class="flex gap-2 items-center">
                <select v-model="form.unit_id" class="block w-full rounded border-gray-300">
                  <option value="">Select unit</option>
                  <option v-for="u in props.units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.short_code }})</option>
                  <option disabled>────────────</option>
                  <option value="__add__">+ Add unit</option>
                </select>
                <div v-if="form.unit_id === '__add__' || showAddUnit" class="add-unit-modal">
                  <div class="flex flex-col gap-2">
                    <Input v-model="newUnit.name" placeholder="Unit name (e.g., Kilogram)" :class="unitErrors.name ? 'border-red-600' : ''" />
                    <Input v-model="newUnit.short_code" placeholder="Short code (e.g., kg)" :class="unitErrors.short_code ? 'border-red-600' : ''" />
                    <Input v-model="newUnit.description" placeholder="Description (optional)" />
                    <div class="flex gap-2 mt-1">
                      <button type="button" class="bg-green-600 text-white px-3 py-1 rounded flex items-center gap-2" @click="addUnit" :disabled="addingUnit">
                        <span v-if="addingUnit">Saving...</span>
                        <span v-else>Save</span>
                      </button>
                      <button type="button" class="text-gray-500 px-2" @click="showAddUnit = false; form.unit_id = ''">Cancel</button>
            </div>
                    <div v-if="unitErrors.name" class="text-xs text-red-600 mt-1">{{ unitErrors.name[0] }}</div>
                    <div v-if="unitErrors.short_code" class="text-xs text-red-600 mt-1">{{ unitErrors.short_code[0] }}</div>
                  </div>
                </div>
              </div>
              <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Unit value</label>
              <Input v-model="form.unit_value" type="number" min="0.01" step="0.01" placeholder="e.g., 1, 2.5" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
            <Input v-model="form.price" type="number" min="0" placeholder="" :disabled="hasVariants" />
            <div class="text-xs text-gray-500 mt-1">To indicate the price upon sale, leave the field blank</div>
          </div>
          <div>
            <label class="block text-xs text-gray-500">SKU</label>
            <Input v-model="form.sku" class="font-bold text-lg" />
            <div class="text-xs text-gray-500">Unique identifier assigned to an item</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cost</label>
            <Input v-if="!form.is_composite && !hasVariants" v-model="form.cost" type="number" min="0" placeholder="KSh0.00" :disabled="hasVariants" />
            <input v-else :value="mainCostValue" readonly class="bg-gray-100 font-bold text-lg px-2 py-1 w-full mb-2 border rounded" />
            <div class="text-xs text-gray-500">{{ form.is_composite ? 'Auto-calculated from components' : 'KSh0.00' }}</div>
          </div>
          <div>
            <label class="block text-xs text-gray-500">Barcode</label>
            <Input v-model="form.barcode" placeholder="" />
          </div>
        </div>
      </div>

      <!-- Inventory Card -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Inventory</h2>
        <div class="flex items-center justify-between mb-2">
          <span>Composite item <span class="text-gray-400 ml-1" title="Composite item">&#9432;</span></span>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.is_composite" class="sr-only peer" />
            <div class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-green-500 transition"></div>
            <div class="w-4 h-4 bg-white rounded-full absolute ml-1 mt-1 transition peer-checked:translate-x-4"></div>
          </label>
        </div>
        <div class="flex items-center justify-between mb-2">
          <span>Track stock</span>
          <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.track_stock" class="sr-only peer" />
            <div class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-green-500 transition"></div>
            <div class="w-4 h-4 bg-white rounded-full absolute ml-1 mt-1 transition peer-checked:translate-x-4"></div>
          </label>
        </div>
        <div v-if="form.is_composite" class="mt-4">
          <table class="min-w-full text-sm mb-2">
            <thead>
              <tr>
                <th class="text-left w-1/2">Component</th>
                <th class="text-center w-1/6">Quantity</th>
                <th class="text-right w-1/6">Cost</th>
                <th class="w-10"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(comp, idx) in compositeComponents" :key="comp.sku">
                <td class="align-middle">
                  <div>{{ comp.name }}</div>
                  <div class="text-xs text-gray-500">SKU {{ comp.sku }}</div>
                </td>
                <td class="text-center align-middle">
                  <Input v-model="comp.qty" type="number" min="0" step="0.001" class="w-20 text-center mb-2" />
                </td>
                <td class="text-right align-middle">
                  <Input v-model="comp.cost" type="number" min="0" step="0.01" class="w-24 text-right mb-2" placeholder="KSh0.00"
                    @input="comp.manualCost = true" />
                </td>
                <td class="text-center align-middle">
                  <button type="button" class="text-gray-400 hover:text-red-600" @click="removeCompositeComponent(idx)"><Trash2 /></button>
                </td>
              </tr>
            </tbody>
          </table>
          <Input v-model="compositeSearch" placeholder="Item search" class="mb-2" @focus="showCompositeDropdown = true" @input="showCompositeDropdown = true" @blur="handleCompositeBlur" />
          <div v-if="showCompositeDropdown && filteredCompositeItems.length" class="absolute bg-white border rounded shadow w-full z-20">
            <div v-for="item in filteredCompositeItems" :key="item.sku" class="px-4 py-2 cursor-pointer hover:bg-gray-100" @mousedown.prevent="addCompositeComponent(item)">
              <div>{{ item.name }}<span v-if="item.optionsString"> ({{ item.optionsString }})</span></div>
              <div class="text-xs text-gray-500">SKU {{ item.sku }}</div>
            </div>
          </div>
          <!-- You can add search logic here -->
          <div class="flex items-center gap-4 mt-2">
            <span class="mr-4">Total cost</span>
            <span>KSh{{ compositeTotalCost }}</span>
            <span class="ml-8">Batch size</span>
            <Input v-model="form.batch_size" type="number" min="0.01" step="0.01" placeholder="Batch size" class="w-24 inline-block" @input="onBatchSizeInput" />
            <span v-if="form.unit_id">{{ props.units.find(u => u.id == form.unit_id)?.short_code }}</span>
          </div>
        </div>
        <div v-else-if="form.track_stock" class="mt-4 grid grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">In stock</label>
            <Input v-model="form.in_stock" type="number" min="0" placeholder="0" :disabled="hasVariants" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Low stock</label>
            <Input v-model="form.low_stock" type="number" min="0" placeholder="" />
            <div class="text-xs text-gray-500">Item quantity at which you will be notified about low stock</div>
          </div>
        </div>
      </div>

      <!-- Variants Card -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Variants</h2>
        <div class="mb-2 font-semibold">Options: {{ variantOptions.map(o => o.name).join('/') }}</div>
        <div v-if="variantMatrix.length === 0" class="flex flex-col items-center py-8">
          <button
            type="button"
            @click="openVariantModal('create')"
            class="bg-green-600 text-white px-6 py-2 rounded font-semibold text-lg flex items-center gap-2"
          >
            <span class="text-2xl leading-none">+</span> Add Variants
          </button>
        </div>
        <div v-else>
          <button type="button" @click="openVariantModal('edit')" class="flex items-center gap-1 text-green-700 font-semibold mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14" /></svg>
            EDIT OPTIONS
          </button>
          <table class="min-w-full border text-sm">
            <thead>
              <tr>
                <th class="border px-2 py-1">Available</th>
                <th class="border px-2 py-1">Variant</th>
                <th class="border px-2 py-1">Price</th>
                <th class="border px-2 py-1">Cost</th>
                <th class="border px-2 py-1">SKU</th>
                <th class="border px-2 py-1">Barcode</th>
                <th class="border px-2 py-1">Stock</th>
                <th class="border px-2 py-1">Unit value</th>
                <th class="border px-2 py-1"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(variant, idx) in variantMatrix" :key="variant.key">
                <td class="border px-2 py-1 text-center">
                  <input type="checkbox" v-model="variant.available" />
                </td>
                <td class="border px-2 py-1">{{ Object.values(variant.options).join('/') }}</td>
                <td class="border px-2 py-1 flex items-center gap-1">
                  <span>KSh</span>
                  <Input v-model="variant.price" type="number" min="0" placeholder="0.00"
                    :class="{'border-red-500': variant.price === '' || isNaN(Number(variant.price))}" />
                </td>
                <td class="border px-2 py-1">
                  <Input v-model="variant.cost" type="number" min="0" placeholder="Cost"
                    :class="{'border-red-500': variant.cost === '' || isNaN(Number(variant.cost))}"
                    :disabled="form.is_composite && hasVariants"
                  />
                </td>
                <td class="border px-2 py-1">
                  <Input v-model="variant.sku" placeholder="SKU" readonly />
                </td>
                <td class="border px-2 py-1">
                  <Input v-model="variant.barcode" placeholder="Barcode" />
                </td>
                <td class="border px-2 py-1">
                  <Input v-model="variant.stock" type="number" min="0" placeholder="Stock" />
                </td>
                <td class="border px-2 py-1">
                  <Input v-model="variant.unit_value" type="number" min="0.01" step="0.01" placeholder="Unit value" />
                </td>
                <td class="border px-2 py-1 text-center">
                  <button type="button" class="text-gray-400 hover:text-red-600" @click="removeVariantRow(idx)"><Trash2 /></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Taxes Card -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Taxes</h2>
        <div class="flex flex-col gap-2">
          <label
            v-for="tax in taxGroups"
            :key="tax.id"
            class="flex items-center gap-2 cursor-pointer"
          >
            <input
              type="radio"
              v-model="form.tax_group_id"
              :value="tax.id"
              class="accent-green-600"
            />
            <span class="font-semibold">{{ tax.code }}</span>
            <span class="text-gray-500">({{ tax.rate }}%)</span>
            <span class="text-gray-400 text-xs">{{ tax.description }}</span>
          </label>
          <div class="border-t my-2"></div>
          <div v-if="showAddTax" class="flex flex-col gap-2 p-2 bg-gray-50 rounded">
            <div class="flex gap-2">
              <div class="flex flex-col w-16">
                <Input v-model="newTax.code" placeholder="Code" maxlength="2" :class="taxErrors.code ? 'border-red-600' : ''" />
                <div v-if="taxErrors.code" class="text-xs text-red-600 mt-1">{{ taxErrors.code[0] }}</div>
              </div>
              <div class="flex flex-col w-20">
                <Input v-model="newTax.rate" placeholder="Rate %" type="number" min="0" max="100" :class="taxErrors.rate ? 'border-red-600' : ''" />
                <div v-if="taxErrors.rate" class="text-xs text-red-600 mt-1">{{ taxErrors.rate[0] }}</div>
              </div>
              <div class="flex flex-col flex-1">
                <Input v-model="newTax.description" placeholder="Description" :class="taxErrors.description ? 'border-red-600' : ''" />
                <div v-if="taxErrors.description" class="text-xs text-red-600 mt-1">{{ taxErrors.description[0] }}</div>
              </div>
            </div>
            <div class="flex gap-2 mt-1">
              <button type="button" class="bg-green-600 text-white px-3 py-1 rounded flex items-center gap-2" @click="addTaxGroup" :disabled="addingTax">
                <span v-if="addingTax">Saving...</span>
                <span v-else>Save</span>
              </button>
              <button type="button" class="text-gray-500 px-2" @click="showAddTax = false">Cancel</button>
            </div>
          </div>
          <button v-else type="button" class="text-green-700 font-semibold text-sm mt-2 flex items-center gap-1" @click="openAddTax">
            <span class="text-lg leading-none">+</span> Add tax
          </button>
        </div>
      </div>

      <!-- POS Representation Card -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Representation on POS</h2>
        <div
          class="relative flex flex-col items-center gap-2 cursor-pointer group"
          @mouseenter="showUploadOverlay = true"
          @mouseleave="showUploadOverlay = false"
          @focusin="showUploadOverlay = true"
          @focusout="showUploadOverlay = false"
          @click="triggerImageUpload"
        >
          <input
            ref="imageInputRef"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleImageChange"
          />
          <template v-if="imagePreviewUrl">
            <img :src="imagePreviewUrl" alt="Item Image Preview" class="w-32 h-32 object-contain rounded border bg-gray-50" />
          </template>
          <template v-else>
            <div class="w-32 h-32 flex items-center justify-center bg-gray-50 border rounded">
              <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="64" height="64" rx="8" fill="#F3F4F6"/>
                <path d="M16 48L28 32L40 44L48 36L56 48" stroke="#A0AEC0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="24" cy="24" r="4" fill="#A0AEC0"/>
              </svg>
            </div>
          </template>
          <transition name="slide-up-fade">
            <div
              v-if="showUploadOverlay"
              class="absolute left-0 bottom-0 w-full h-1/3 flex items-center justify-center bg-blue-600 bg-opacity-90 text-white rounded-b cursor-pointer"
              style="transition: all 0.3s cubic-bezier(.4,0,.2,1);"
            >
              <span class="text-sm font-semibold">Upload Image</span>
            </div>
          </transition>
        </div>
        <div class="text-xs text-gray-400 mt-2 text-center">
          {{ imagePreviewUrl ? 'Click image to change' : 'Click placeholder to upload image' }}
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-2 mt-6">
        <button type="button" class="bg-white border border-gray-300 text-black px-6 py-2 rounded font-semibold" @click="handleCancel">CANCEL</button>
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-semibold" :disabled="isSaving">
          <span v-if="isSaving">Saving...</span>
          <span v-else>SAVE</span>
        </button>
      </div>

      <!-- Variant Options Modal (unchanged) -->
      <Dialog v-model:open="showVariantModal" @update:open="showVariantModal = $event">
        <DialogContent :class="{ 'animate-shake': shakeModal }">
          <DialogDescription class="sr-only">Add or edit variant options for this item.</DialogDescription>
          <DialogTitle class="mb-4">{{ variantModalMode === 'edit' ? 'Edit options' : 'Create options' }}</DialogTitle>
          <div>
            <div v-if="variantOptions.length > 0">
              <div v-for="(opt, idx) in variantOptions" :key="idx" class="grid grid-cols-12 items-start gap-4 py-4 border-b last:border-b-0">
                <div class="col-span-1 flex justify-center pt-7">
                  <GripVertical class="text-gray-400" />
                </div>
                <div class="col-span-3">
                  <label class="block text-xs font-semibold mb-1" :class="optionErrors[idx].name ? 'text-red-600' : 'text-gray-500'">Option name</label>
                  <Input v-model="opt.name" :placeholder="opt.namePlaceholder" class="mb-0" :class="optionErrors[idx].name ? 'border-red-600' : ''" />
                  <div v-if="optionErrors[idx].name" class="text-xs text-red-600 mt-1">{{ optionErrors[idx].name }}</div>
                </div>
                <div class="col-span-7">
                  <label class="block text-xs font-semibold mb-1" :class="optionErrors[idx].values ? 'text-red-600' : 'text-gray-500'">Option values</label>
                  <div class="flex flex-wrap gap-2 mb-2 min-h-[32px]">
                    <span v-for="(val, vIdx) in opt.values" :key="vIdx" :class="['inline-flex items-center rounded-full px-3 py-1 text-sm', val.trim() ? 'bg-gray-200 text-gray-800' : 'bg-red-200 text-red-800 border border-red-400']">
                      {{ val }}
                      <button type="button" class="ml-1 text-gray-500 hover:text-red-600" @click="removeOptionValue(opt, vIdx)" tabindex="-1">
                        &times;
                      </button>
                    </span>
                  </div>
                  <Input
                    v-model="opt.newValue"
                    :placeholder="opt.valuePlaceholder"
                    class="mb-0"
                    :class="optionErrors[idx].values ? 'border-red-600' : ''"
                    @keydown="handleOptionValueInput(opt, $event)"
                    @blur="() => { if (opt.newValue.trim()) { if (!opt.values.includes(opt.newValue.trim())) opt.values.push(opt.newValue.trim()); opt.newValue = ''; } }"
                    :ref="el => setValueInputRef(el, idx)"
                  />
                  <div v-if="optionErrors[idx].values" class="text-xs text-red-600 mt-1">{{ optionErrors[idx].values }}</div>
                </div>
                <div class="col-span-1 flex justify-center pt-7">
                  <button type="button" @click="removeOptionRow(idx)" :class="['transition', 'text-gray-400 hover:text-red-600']">
                    <Trash2 />
                  </button>
                </div>
              </div>
            </div>
            <div v-if="variantOptions.length < 3" class="flex items-center gap-2 mt-4 cursor-pointer select-none text-green-700 font-semibold text-sm" @click="addOptionRow">
              <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-green-700"><span class="text-lg leading-none">+</span></span>
              ADD OPTION
            </div>
            <div v-if="variantModalError" class="text-red-600 mt-2">{{ variantModalError }}</div>
            <div v-if="variantOptions.length === 0 || variantOptions.every(o => o.values.length === 0)" class="text-yellow-700 text-sm mt-4">
              Add at least one value to enable SAVE.
      </div>
    </div>
          <DialogFooter class="mt-4 flex justify-end gap-2">
            <DialogClose as-child>
              <button type="button" class="bg-gray-200 px-4 py-2 rounded" @click="closeVariantModal">CANCEL</button>
            </DialogClose>
            <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded" @click="onSaveClick"
              :disabled="!!variantModalError || hasAnyOptionError() || variantOptions.length === 0 || variantOptions.every(o => o.values.length === 0)">
              SAVE
            </button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </form>
  </AppLayout>
</template>

<style scoped>
input, select, textarea { @apply border rounded px-2 py-1 w-full mb-2; }
button { @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700; }
/* Make add unit modal smaller and centered */
.add-unit-modal {
  max-width: 350px;
  margin: 0 auto;
  left: 50%;
  transform: translateX(-50%);
  position: absolute;
  z-index: 50;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-shadow: 0 2px 16px rgba(0,0,0,0.12);
  padding: 1.5rem;
}
/* Remove modal content larger override */
/* :deep(.sm:max-w-xl) { max-width: 900px !important; } */
@keyframes shake {
  10%, 90% { transform: translateX(-2px); }
  20%, 80% { transform: translateX(4px); }
  30%, 50%, 70% { transform: translateX(-8px); }
  40%, 60% { transform: translateX(8px); }
}
.animate-shake {
  animation: shake 0.5s;
}
.slide-up-fade-enter-active, .slide-up-fade-leave-active {
  transition: all 0.3s cubic-bezier(.4,0,.2,1);
}
.slide-up-fade-enter-from, .slide-up-fade-leave-to {
  opacity: 0;
  transform: translateY(40%);
}
.slide-up-fade-enter-to, .slide-up-fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style> 