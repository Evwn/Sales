<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';

const props = defineProps({
  items: Array,
  locations: Array
})

const form = useForm({
  location_id: '',
  reference: '',
  requisition_date: '',
  priority: '',
  notes: '',
  status: 'draft',
  items: [{ item_id: '', quantity: 1, unit: '' }]
})

const addItem = () => {
  form.items.push({ item_id: '', quantity: 1, unit: '' })
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const submit = () => {
  form.post('/requisitions')
}
</script>

<template>
  <AppLayout title="Create Supplier">
    <PageHeader title="Create Requisition"/>
  <div class="max-w-5xl mx-auto mt-8 bg-[#B76E79]/80 backdrop-blur-md shadow rounded-lg p-8">
    <form @submit.prevent="submit" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white/60 p-4 rounded">
      <div class="bg-white/40 p-4 rounded">
        <label>Location</label>
        <select v-model="form.location_id" class="border rounded p-2 w-full bg-gray-200 hover:bg-gray-300">
          <option value="">-- Select Location --</option>
          <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
        </select>
      </div>
      
      <div class="bg-white/40 p-4 rounded">
        <label>Requisition Date</label>
        <input type="date" v-model="form.requisition_date" class="border rounded p-2 w-full bg-gray-200 hover:bg-gray-300" />
      </div>

      <div class="bg-white/40 p-4 rounded">
        <label>Priority</label>
        <input v-model="form.priority" class="border rounded p-2 w-full bg-gray-200 hover:bg-gray-300" />
      </div>
      </div>

      <div class="bg-white/60 p-4 rounded">
        <label>Notes</label>
        <textarea v-model="form.notes" class="border rounded p-2 w-full bg-gray-200 hover:bg-gray-300"></textarea>
      </div>

      <div class="bg-white/60 p-4 rounded">
        <label>Status</label>
        <select v-model="form.status" class="border rounded p-2 w-full bg-gray-200 hover:bg-gray-300">
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="converted">Converted</option>
        </select>
      </div>

      <div class="bg-white/60 p-4 rounded">
        <h2 class="font-semibold">Items</h2>
        <div v-for="(ri, index) in form.items" :key="index" class="flex gap-2 mb-2 ">
          <select v-model="ri.item_id" class="border rounded p-2 flex-1 bg-gray-200 hover:bg-gray-300">
            <option value="">-- Select Item --</option>
            <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
          <input v-model="ri.quantity" type="number" min="1" class="border rounded p-2 w-24 bg-gray-200 hover:bg-gray-300" />
          <input v-model="ri.unit" placeholder="Unit" class="border rounded p-2 w-24 bg-gray-200 hover:bg-gray-300" />
          <button type="button" @click="removeItem(index)" class="text-red-600">Remove</button>
        </div>
        <button type="button" @click="addItem" class="text-gray-900">+ Add Item</button>
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
      <Link href="/requisitions" class="ml-2 text-gray-600">Cancel</Link>
    </form>
  </div>
  </AppLayout>
</template>
