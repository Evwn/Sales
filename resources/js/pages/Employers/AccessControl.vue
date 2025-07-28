<template>
  <AppLayout>
    <div class="p-6 max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-4">
        <button class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded shadow" @click="$inertia.visit('/employers/roles/create')">
          + ADD ROLE
        </button>
      </div>
      <div class="bg-white rounded shadow p-4">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="px-2 py-2"><input type="checkbox" /></th>
              <th class="px-2 py-2 text-left">Role</th>
              <th class="px-2 py-2 text-left">Access</th>
              <th class="px-2 py-2 text-left">Employees</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in paginatedRoles" :key="role.name" class="border-b hover:bg-gray-50">
              <td class="px-2 py-2"><input type="checkbox" /></td>
              <td class="px-2 py-2 flex items-center gap-2 cursor-pointer" @click="$inertia.visit(`/employers/roles/${role.name}/edit`)">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-300">
                  <svg v-if="role.name.toLowerCase() === 'owner'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a4 4 0 014-4h0a4 4 0 014 4v2"/></svg>
                  <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75"/></svg>
                </span>
                <span>{{ role.name }}</span>
              </td>
              <td class="px-2 py-2">{{ formatAccess(role.guards) }}</td>
              <td class="px-2 py-2"><span :class="{'font-bold': role.employees > 0}">{{ role.employees }}</span></td>
            </tr>
          </tbody>
        </table>
        <div class="flex items-center justify-between mt-4 text-xs text-gray-600">
          <div class="flex items-center gap-2">
            <button class="px-2 py-1" :disabled="pageNum === 1" @click="pageNum--">&lt;</button>
            <span>Page:</span>
            <input type="number" v-model.number="pageNum" min="1" :max="totalPages" class="border rounded w-10 px-1 py-0.5 text-center" />
            <span>of {{ totalPages }}</span>
            <button class="px-2 py-1" :disabled="pageNum === totalPages" @click="pageNum++">&gt;</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const page = usePage();
const roles = computed(() => page.props.roles || []);

const pageNum = ref(1);
const rowsPerPage = ref(10);
const totalPages = computed(() => Math.max(1, Math.ceil(roles.value.length / rowsPerPage.value)));
const paginatedRoles = computed(() => {
  const start = (pageNum.value - 1) * rowsPerPage.value;
  return roles.value.slice(start, start + rowsPerPage.value);
});
watch([pageNum, rowsPerPage], () => {
  if (pageNum.value > totalPages.value) pageNum.value = totalPages.value;
});

function formatAccess(guards) {
  if (!guards) return '';
  if (guards.length === 2) return 'Back office and POS';
  if (guards.includes('pos')) return 'POS';
  if (guards.includes('backoffice')) return 'Back office';
  return guards.join(', ');
}
</script>
<style scoped>
input[type="checkbox"] {
  accent-color: #4caf50;
}
</style> 