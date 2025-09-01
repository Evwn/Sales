<template>
  <AppLayout title="Edit Quotation">
    <PageHeader :title="`Edit Quotation ${quotation.reference}`" :button="{ text: 'Back', link: '/quotations', icon: AddIcon }" />

    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white/60 p-4 rounded">
              <div>
                <label class="block text-sm font-medium mb-1">Reference</label>
                <Input v-model="form.reference" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Quotation Date</label>
                <Input v-model="form.quotation_date" type="date" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select v-model="form.status" class="border rounded p-2 w-full">
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="md:col-span-3">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <input v-model="form.notes" class="border rounded p-2 w-full" />
              </div>
            </div>

            <!-- Items inline editing -->
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
                  <tr v-for="qi in rows" :key="qi.item_id">
                    <td class="px-4 py-2">{{ qi.name }}</td>
                    <td class="px-4 py-2 w-32">
                      <input type="number" min="0" step="0.01" v-model.number="qi.quantity" @blur="saveItem(qi)" class="border rounded p-1 w-full" />
                    </td>
                    <td class="px-4 py-2 w-40">
                      <input type="number" min="0" step="0.01" v-model.number="qi.unit_price" @blur="saveItem(qi)" class="border rounded p-1 w-full" />
                    </td>
                    <td class="px-4 py-2">{{ (qi.quantity * qi.unit_price).toFixed(2) }}</td>
                    <td class="px-4 py-2 text-right">
                      <button class="text-red-600" @click="removeItem(qi)">✕</button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Add item -->
              <div class="mt-4">
                <div class="relative max-w-md" :class="'item-add'">
                  <input v-model="itemSearch" @focus="showDropdown=true" placeholder="Add item..." class="border-b border-gray-400 w-full py-1 focus:outline-none" />
                  <ul v-if="showDropdown" class="absolute z-10 bg-white border rounded shadow-md max-h-40 overflow-y-auto w-full">
                    <li v-for="it in filteredMaster" :key="it.id" class="px-3 py-2 hover:bg-gray-100 cursor-pointer" @click="addItem(it)">
                      {{ it.name }}
                    </li>
                  </ul>
                </div>
              </div>

              <div class="mt-6 text-right font-semibold">Grand Total: {{ grandTotal.toFixed(2) }}</div>
            </div>

            <div class="flex justify-end gap-3">
              <button @click="saveHeader" class="px-4 py-2 rounded border bg-white hover:bg-gray-50">Save</button>
              <Link href="/quotations" class="px-4 py-2 rounded border bg-white hover:bg-gray-50">Back</Link>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { Input } from '@/components/ui/input'
import AddIcon from '@/components/AddIcon.vue'

const props = defineProps({
  quotation: { type: Object, required: true },
  itemsMaster: { type: Array, default: () => [] },
})

const form = reactive({
  reference: props.quotation.reference,
  quotation_date: props.quotation.quotation_date,
  status: props.quotation.status,
  notes: props.quotation.notes || '',
})

const rows = ref((props.quotation.items||[]).map(qi => ({
  item_id: qi.item_id,
  name: qi.item?.name,
  quantity: Number(qi.quantity),
  unit_price: Number(qi.unit_price),
})))

const itemSearch = ref('')
const showDropdown = ref(false)

const filteredMaster = computed(() => {
  const t = itemSearch.value.toLowerCase().trim()
  const exclude = new Set(rows.value.map(r => r.item_id))
  return props.itemsMaster.filter(i => !exclude.has(i.id) && (i.name||'').toLowerCase().includes(t))
})

function saveHeader() {
  router.put(`/quotations/${props.quotation.id}`, form)
}

function saveItem(row) {
  router.put(`/quotations/${props.quotation.id}/items`, {
    item_id: row.item_id,
    quantity: row.quantity,
    unit_price: row.unit_price,
  })
}

function addItem(it) {
  const row = { item_id: it.id, name: it.name, quantity: 1, unit_price: 0 }
  rows.value.push(row)
  saveItem(row)
  itemSearch.value = ''
  showDropdown.value = false
}

function removeItem(row) {
  router.delete(`/quotations/${props.quotation.id}/items/${row.item_id}`)
  rows.value = rows.value.filter(r => r.item_id !== row.item_id)
}

const grandTotal = computed(() => rows.value.reduce((s, r) => s + (Number(r.quantity||0) * Number(r.unit_price||0)), 0))

function handleClickOutside(e){ if(!e.target.closest('.item-add')) showDropdown.value = false }
onMounted(()=> document.addEventListener('click', handleClickOutside))
onBeforeUnmount(()=> document.removeEventListener('click', handleClickOutside))
</script>