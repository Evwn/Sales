<template>
  <AppLayout title="Create Quotation">
    <PageHeader title="New Quotation" :button="{ text: 'Back', link: '/quotations', icon: AddIcon }" />

    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8 space-y-6">

            <!-- Requisition selector -->
            <div class="bg-white/60 p-4 rounded">
              <label class="block text-sm font-medium mb-1">Approved Requisition</label>
              <select v-model="form.requisition_id" @change="loadReqItems" class="border rounded p-2 w-full">
                <option value="" disabled>Select an approved requisition</option>
                <option v-for="r in requisitions" :key="r.id" :value="r.id">
                  {{ r.reference }} — {{ r.location?.name || 'N/A' }} — {{ r.requisition_date }}
                </option>
              </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white/60 p-4 rounded">
              <div>
                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow">
                <thead class="block text-sm font-medium mb-1">
                  <tr>
                    <th class="block text-sm font-medium mb-1">Suppliers</th>
                  </tr>
                </thead>
                <tbody class="bg-white/60 divide-y divide-gray-200">
                  <tr v-for="(row, idx) in form.suppliers" :key="row.supplier_id">
                    <td class="px-4 py-2">{{ row.name }}</td>
                    <td class="px-4 py-2 text-right"><button class="text-red-600" @click="removesRow(idx)">✕</button></td>
                  </tr>
                </tbody>
              </table>
                            <!-- Add supplier -->
              <div class="mt-4">
                <div class="relative max-w-md" :class="'supplier-add'">
                  <input v-model="supplierSearch" @focus="showDrop=true" placeholder="Add supplier..." class="border-b border-gray-400 w-full py-1 focus:outline-none" />
                  <ul v-if="showDrop" class="absolute z-10 bg-white border rounded shadow-md max-h-40 overflow-y-auto w-full">
                    <li v-for="it in filteredSuppliers" :key="it.id" class="px-3 py-2 hover:bg-gray-100 cursor-pointer" @click="selectSupplier(it)">
                      {{ it.name }}
                    </li>
                  </ul>
                </div>
              </div>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Quotation Date</label>
                <Input v-model="form.quotation_date" type="date" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <input v-model="form.notes" class="border rounded p-2 w-full bg-gray-200" placeholder="Optional notes" />
              </div>
            </div>

            <!-- Items table -->
            <div class="bg-white/60 p-4 rounded overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow">
                <thead class="bg-[#B76E79]/80 backdrop-blur-md">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Item</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Qty</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Unit Price</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Line Total</th>
                    <th class="px-4 py-2"></th>
                  </tr>
                </thead>
                <tbody class="bg-white/60 divide-y divide-gray-200">
                  <tr v-for="(row, idx) in form.items" :key="row.item_id">
                    <td class="px-4 py-2">{{ row.name }}</td>
                    <td class="px-4 py-2 w-32"><input type="number" min="0" step="0.01" v-model.number="row.quantity" class="border rounded p-1 w-full" /></td>
                    <td class="px-4 py-2 w-40"><input type="number" min="0" step="0.01" v-model.number="row.unit_price" class="border rounded p-1 w-full" /></td>
                    <td class="px-4 py-2">{{ (row.quantity * row.unit_price).toFixed(2) }}</td>
                    <td class="px-4 py-2 text-right"><button class="text-red-600" @click="removeRow(idx)">✕</button></td>
                  </tr>
                </tbody>
              </table>

              <!-- Add item -->
              <div class="mt-4">
                <div class="relative max-w-md" :class="'item-add'">
                  <input v-model="itemSearch" @focus="showDropdown=true" placeholder="Add item..." class="border-b border-gray-400 w-full py-1 focus:outline-none" />
                  <ul v-if="showDropdown" class="absolute z-10 bg-white border rounded shadow-md max-h-40 overflow-y-auto w-full">
                    <li v-for="it in filteredItems" :key="it.id" class="px-3 py-2 hover:bg-gray-100 cursor-pointer" @click="selectItem(it)">
                      {{ it.name }}
                    </li>
                  </ul>
                </div>
              </div>
              <div class="mt-4">
                <div class="relative max-w-md" :class="'supplier-add'">
                  <input v-model="supplierSearch" @focus="showDrop=true" placeholder="Add supplier..." class="border-b border-gray-400 w-full py-1 focus:outline-none" />
                  <ul v-if="showDrop" class="absolute z-10 bg-white border rounded shadow-md max-h-40 overflow-y-auto w-full">
                    <li v-for="it in filteredSuppliers" :key="it.id" class="px-3 py-2 hover:bg-gray-100 cursor-pointer" @click="selectSupplier(it)">
                      {{ it.name }}
                    </li>
                  </ul>
                </div>
              </div>
              <!-- Totals -->
              <div class="mt-6 text-right font-semibold">Grand Total: {{ grandTotal.toFixed(2) }}</div>
            </div>

            <div class="flex justify-end gap-3">
              <button @click="submit" class="px-4 py-2 rounded border bg-white hover:bg-gray-50"
              :class="{ 'opacity-25': form.uploading }"
              :disabled="form.uploading">
              <LoaderCircle v-if="form.uploading" class="h-4 w-4 animate-spin flex"> </LoaderCircle>  Save</button>
              <Link href="/quotations" class="px-4 py-2 rounded border bg-white hover:bg-gray-50">Cancel</Link>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { Input } from '@/components/ui/input'
import AddIcon from '@/components/AddIcon.vue'
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps({
  requisitions: { type: Array, default: () => [] }, // only approved passed from controller
  items: { type: Array, default: () => [] },
  suppliers: { type: Array, default: () => [] },
})

const form = reactive({
  requisition_id: '',
  reference: '',
  quotation_date: new Date().toISOString().slice(0,10),
  notes: '',
  items: [], // {item_id, name, quantity, unit_price}
  suppliers: [], 
  uploading:false,
})

const itemSearch = ref('')
const supplierSearch = ref('')
const showDropdown = ref(false)
const showDrop = ref(false)

const filteredItems = computed(() => {
  const t = itemSearch.value.toLowerCase().trim()
  return !t ? props.items : props.items.filter(i => (i.name||'').toLowerCase().includes(t))
})

const filteredSuppliers = computed(() => {
  const t = supplierSearch.value.toLowerCase().trim()
  return !t ? props.suppliers : props.suppliers.filter(i => (i.name||'').toLowerCase().includes(t))
})

function loadReqItems() {
  const r = props.requisitions.find(x => x.id === form.requisition_id)
  if (!r) return
  // prefill items from requisition items with qty
  form.items = (r.items || []).map(ri => ({
    item_id: ri.item_id,
    name: ri.item?.name,
    quantity: Number(ri.quantity || 1),
    unit_price: 0,
  }))
}

function selectItem(it) {
  const exists = form.items.find(x => x.item_id === it.id)
  if (!exists) form.items.push({ item_id: it.id, name: it.name, quantity: 1, unit_price: 0 })
  itemSearch.value = ''
  showDropdown.value = false
}
function selectSupplier(it) {
  const exists = form.suppliers.find(x => x.supplier_id === it.id)
  if (!exists) form.suppliers.push({ supplier_id: it.id, name: it.name})
  supplierSearch.value = ''
  showDrop.value = false
}

function removeRow(idx) { form.items.splice(idx,1) }
function removesRow(idx) { form.suppliers.splice(idx,1) }

const grandTotal = computed(() => form.items.reduce((s, r) => s + (Number(r.quantity||0) * Number(r.unit_price||0)), 0))

function submit() {
  form.uploading = true
  router.post('/quotations', {
    requisition_id: form.requisition_id,
    reference: form.reference,
    suppliers: form.suppliers.map(r => ({ supplier_id: r.supplier_id})),
    quotation_date: form.quotation_date,
    notes: form.notes,
    items: form.items.map(r => ({ item_id: r.item_id, quantity: r.quantity, unit_price: r.unit_price })),
  })
  onFinish: () => form.uploading = false;
}

function handleClickOutside(e){ if(!e.target.closest('.item-add')) {
  showDropdown.value = false

} 
if(!e.target.closest('.supplier-add')) {
  showDrop.value = false 

} }

onMounted(()=> document.addEventListener('click', handleClickOutside))
onBeforeUnmount(()=> document.removeEventListener('click', handleClickOutside))
</script>