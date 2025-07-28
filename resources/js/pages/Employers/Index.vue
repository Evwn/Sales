<template>
  <AppLayout>
    <div class="p-6 max-w-5xl mx-auto">
      <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
          <button class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded shadow" @click="$inertia.visit('/employers/create')">
            + ADD EMPLOYEE
          </button>
        </div>
        <div class="relative">
          <input type="text" v-model="search" placeholder="" class="border rounded px-3 py-2 w-48" />
          <svg class="absolute right-2 top-2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
      </div>
      <div class="bg-white rounded shadow p-4">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="px-2 py-2"><input type="checkbox" /></th>
              <th class="px-2 py-2 text-left">Name</th>
              <th class="px-2 py-2 text-left">Email</th>
              <th class="px-2 py-2 text-left">Phone</th>
              <th class="px-2 py-2 text-left">Role</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="employer in paginatedEmployers" :key="employer.id" class="border-b hover:bg-gray-50">
              <td class="px-2 py-2"><input type="checkbox" /></td>
              <td class="px-2 py-2">{{ employer.name }}</td>
              <td class="px-2 py-2">{{ employer.email }}</td>
              <td class="px-2 py-2 font-semibold">{{ employer.phone }}</td>
              <td class="px-2 py-2">{{ employer.role }}</td>
            </tr>
          </tbody>
        </table>
        <div class="flex items-center justify-between mt-4 text-xs text-gray-600">
          <div class="flex items-center gap-2">
            <button class="px-2 py-1" :disabled="page === 1" @click="page--">&lt;</button>
            <span>Page:</span>
            <input type="number" v-model.number="page" min="1" :max="totalPages" class="border rounded w-10 px-1 py-0.5 text-center" />
            <span>of {{ totalPages }}</span>
            <button class="px-2 py-1" :disabled="page === totalPages" @click="page++">&gt;</button>
          </div>
          <div class="flex items-center gap-2">
            <span>Rows per page:</span>
            <select v-model.number="rowsPerPage" class="border rounded px-1 py-0.5">
              <option v-for="n in [10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
const props = defineProps({ employers: Array });
const search = ref('');
const page = ref(1);
const rowsPerPage = ref(10);
const filteredEmployers = computed(() => {
  if (!search.value) return props.employers;
  return props.employers.filter(e =>
    e.name.toLowerCase().includes(search.value.toLowerCase()) ||
    e.email.toLowerCase().includes(search.value.toLowerCase()) ||
    (e.phone && e.phone.toLowerCase().includes(search.value.toLowerCase())) ||
    (e.role && e.role.toLowerCase().includes(search.value.toLowerCase()))
  );
});
const totalPages = computed(() => Math.max(1, Math.ceil(filteredEmployers.value.length / rowsPerPage.value)));
const paginatedEmployers = computed(() => {
  const start = (page.value - 1) * rowsPerPage.value;
  return filteredEmployers.value.slice(start, start + rowsPerPage.value);
});
watch([page, rowsPerPage, filteredEmployers], () => {
  if (page.value > totalPages.value) page.value = totalPages.value;
});
</script>
<style scoped>
input[type="checkbox"] {
  accent-color: #4caf50;
}
</style> 