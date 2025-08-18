<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
  requisition: Object,
  items: Array,
  locations: Array
})

const form = useForm({
  location_id: props.requisition.location_id || '',
  reference: props.requisition.reference || '',
  requisition_date: props.requisition.requisition_date || '',
  priority: props.requisition.priority || '',
  notes: props.requisition.notes || '',
  status: props.requisition.status || 'draft',
  items: props.requisition.items.map(i => ({
    item_id: i.item_id,
    quantity: i.quantity,
    unit: i.unit
  }))
})

const addItem = () => {
  form.items.push({ item_id: '', quantity: 1, unit: '' })
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const submit = () => {
  form.put(`/requisitions/${props.requisition.id}`)
}
</script>

<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Edit Requisition</h1>
    <form @submit.prevent="submit" class="space-y-6">

      <!-- Location -->
      <div>
        <label class="block font-medium">Location</label>
        <select v-model="form.location_id" class="border rounded w-full p-2">
          <option value="">Select Location</option>
          <option v-for="loc in locations" :key="loc.id" :value="loc.id">
            {{ loc.name }}
          </option>
        </select>
      </div>

      <!-- Reference (readonly) -->
      <div>
        <label class="block font-medium">Reference</label>
        <input type="text" v-model="form.reference" class="border rounded w-full p-2 bg-gray-100" readonly />
      </div>

      <!-- Date -->
      <div>
        <label class="block font-medium">Requisition Date</label>
        <input type="date" v-model="form.requisition_date" class="border rounded w-full p-2" />
      </div>

      <!-- Priority -->
      <div>
        <label class="block font-medium">Priority</label>
        <select v-model="form.priority" class="border rounded w-full p-2">
          <option value="">Select Priority</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>

      <!-- Notes -->
      <div>
        <label class="block font-medium">Notes</label>
        <textarea v-model="form.notes" rows="3" class="border rounded w-full p-2"></textarea>
      </div>

      <!-- Status -->
      <div>
        <label class="block font-medium">Status</label>
        <select v-model="form.status" class="border rounded w-full p-2">
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      <!-- Items -->
      <div>
        <label class="block font-medium">Items</label>
        <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-4 gap-2 mb-2">
          <select v-model="item.item_id" class="border rounded p-2">
            <option value="">Select Item</option>
            <option v-for="it in items" :key="it.id" :value="it.id">
              {{ it.name }}
            </option>
          </select>
          <input type="number" v-model="item.quantity" class="border rounded p-2" min="1" />
          <input type="text" v-model="item.unit" class="border rounded p-2" placeholder="Unit" />
          <button type="button" @click="removeItem(index)" class="bg-red-500 text-white px-2 rounded">X</button>
        </div>
        <button type="button" @click="addItem" class="bg-green-500 text-white px-4 py-1 rounded">
          + Add Item
        </button>
      </div>

      <!-- Submit -->
      <div class="flex items-center">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        <Link href="/requisitions" class="ml-4 text-gray-600">Cancel</Link>
      </div>
    </form>
  </div>
</template>
