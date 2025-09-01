<template>
  <AppLayout title="Quotation">
    <PageHeader title="Quotations" :button="{ text: 'New Quotation', link: '/quotations/create', icon: AddIcon }" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">

            <div v-if="filtered.length" class="overflow-x-auto">
              <div class="flex flex-wrap gap-4 mb-4 mt-2 bg-white/50 backdrop-blur sm:rounded-lg p-4">
                <Input v-model="term" placeholder="Search by reference, status, date..." class="flex-1" />
                <select v-model="statusFilter" class="border rounded p-2 text-sm">
                  <option value="">All Status</option>
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>

              <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow">
                <thead class="bg-[#B76E79]/80 backdrop-blur-md">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Requisition</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white/60 divide-y divide-gray-200">
                  <tr v-for="(q,i) in filtered" :key="q.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ i+1 }}</td>
                    <td class="px-6 py-4 text-sm font-medium">{{ q.reference }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ q.quotation_date }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ q.requisition?.reference }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ q.requisition?.location?.name || 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm">
                      <select v-model="q.status" @change="updateStatus(q)" class="border rounded px-2 py-1 text-sm">
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                      </select>
                    </td>
                    <td class="px-6 py-4 text-sm">
                      <Link :href="`/quotations/${q.id}`" class="text-blue-600">View</Link> |
                      <Link :href="`/quotations/${q.id}/edit`" class="text-green-600">Edit</Link> |
                      <Link as="button" method="delete" :href="`/quotations/${q.id}`" class="text-red-600">Delete</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="text-center py-6 text-gray-500">No quotations found.</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { Input } from '@/components/ui/input'
import AddIcon from '@/components/AddIcon.vue'

const props = defineProps({ quotations: { type: Array, default: () => [] } })
const term = ref('')
const statusFilter = ref('')

const filtered = computed(() => {
  const t = term.value.toLowerCase().trim()
  return props.quotations.filter(q => {
    const s = !statusFilter.value || q.status === statusFilter.value
    const m = !t ||
      (q.reference||'').toLowerCase().includes(t) ||
      (q.status||'').toLowerCase().includes(t) ||
      (q.quotation_date||'').toLowerCase().includes(t) ||
      (q.requisition?.reference||'').toLowerCase().includes(t)
    return s && m
  })
})

const updateStatus = (q) => {
  router.put(`/quotations/${q.id}/status`, { status: q.status })
}
</script>