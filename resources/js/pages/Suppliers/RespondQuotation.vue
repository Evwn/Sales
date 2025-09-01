<script setup>
import { useForm,router} from '@inertiajs/vue3'
import PageHeader from '@/components/ui/PageHeader.vue'
import { ref } from 'vue'
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps({
  quotation: Object,
  supplier: Object
})

const form = useForm({
  supplier_id: props.supplier?.id ?? null,
  notes: '',
  items: props.quotation.quotation_items.map(i => ({id: i.id,quantity: i.quantity,unit_price: 0,line_total: 0,name : i.item.name})),
  uploading:false,
});

const updateLine = (item) => {
  item.line_total = item.unit_price * item.quantity
}
const submit = () => {
  form.uploading = true
    form.post('/supplier/quotation-response', {
        onFinish: () => {
          onFinish: () => form.uploading = false;
            form.reset();
        },
    });
};
</script>

<template>
    <PageHeader title="Respond to Quotation"  />
  <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h1 class="text-xl font-bold mb-4">Respond to Quotation #{{ quotation.reference }}</h1>
    <form @submit.prevent="submit" class="mt-8 space-y-6">
      <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow">
        <thead class="bg-[#B76E79]/80 backdrop-blur-md">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Item</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Qty</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Unit Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Line Total</th>
          </tr>
        </thead>
        <tbody class="bg-white/60 divide-y divide-gray-200">
          <tr v-for="item in form.items" :key="item.quotation_item_id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-500">{{ item.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ item.quantity }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 ">
              <input type="number" v-model.number="item.unit_price" @input="updateLine(item)" class="border rounded px-2 py-1 w-24 bg-gray-100 hover:bg-gray-200"/>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ item.line_total.toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="mb-4">
        <label class="block font-medium mb-1">Notes</label>
        <textarea v-model="form.notes" class="input w-full px-3 bg-gray-100 py-2 hover:bg-gray-200"></textarea>
      </div>

      <button type="submit" :disabled="form.processing"
      @click="submit"
       class="bg-blue-600 text-white px-4 py-2 rounded">
       <LoaderCircle v-if="form.uploading" class="h-4 w-4 animate-spin flex"> </LoaderCircle>Submit Response</button>
    </form>   
  </div>
</template>
