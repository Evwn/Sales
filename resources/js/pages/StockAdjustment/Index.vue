<template>
  <AppLayout>            
    <!-- Header -->
    <PageHeader title="Stock Adjustment" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            
            <!-- Search & Filters -->
            <div class="flex flex-wrap gap-4 mb-4 mt-4 bg-white/50 hover:bg-white/50 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 backdrop-blur sm:rounded-lg p-4">
              
              <!-- Search -->
              <div class="flex-1  hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                <Input
                  v-model="search"
                  placeholder="     Search by   business,   branches,   items,   location ..."
                  class="w-full"
                />
              </div>

              <!-- Location Filter -->
              <div >
                <select v-model="locationFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All Stores</option>
                  <option 
                    v-for="loc in uniqueLocations" 
                    :key="loc" 
                    :value="loc"
                  >
                    {{ loc }}
                  </option>
                </select>
              </div>

              <div >
                <select v-model="itemFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All Items</option>
                  <option 
                    v-for="loc in uniqueItems" 
                    :key="loc" 
                    :value="loc"
                  >
                    {{ loc }}
                  </option>
                </select>
              </div>
                            <div >
                <select v-model="businessFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All Business</option>
                  <option 
                    v-for="loc in uniqueBusiness" 
                    :key="loc" 
                    :value="loc"
                  >
                    {{ loc }}
                  </option>
                </select>
              </div>
                            <div >
                <select v-model="branchFilter" class="border border-gray-300 rounded p-2 text-sm hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/75 sm:rounded-lg">
                  <option value="">All Branches</option>
                  <option 
                    v-for="loc in uniqueBranches" 
                    :key="loc" 
                    :value="loc"
                  >
                    {{ loc }}
                  </option>
                </select>
              </div>

            </div>

            <!-- Table -->
            <div v-if="filteredItems.length" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 overflow-hidden rounded-lg shadow">
                <thead class="bg-[#B76E79]/80 backdrop-blur-md">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Item Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Business</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">In Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Selling Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Cost</th>
                  </tr>
                </thead>
                <tbody class="backdrop-blur-sm bg-white/60 divide-y divide-gray-200">
                  <tr 
                    v-for="(item, index) in filteredItems" 
                    :key="item.id" 
                    class="hover:bg-gray-50"
                    >
                    <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.location?.name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.item?.name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.location?.business?.name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.location?.branch?.name || 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500" @click="startEditing(item, 'quantity')">
                    <template v-if="editingField.id === item.id && editingField.field === 'quantity'">
                        <input
                        type="number"
                        v-model="editValue"
                        class="border border-gray-300 rounded p-1 w-20"
                        @keyup.enter="saveEdit(item)"
                        @blur="saveEdit(item)"
                        autofocus
                        />
                        <button @click="saveEdit(item)" class="ml-1 text-green-600">
                        <span v-if="savingField.id === item.id && savingField.field === editingField.field">⏳</span>
                        <span v-else>✔</span>
                        </button>

                    </template>
                    <template v-else>
                        {{ item.quantity }}
                    </template>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-500" @click="startEditing(item, 'price')">
                    <template v-if="editingField.id === item.id && editingField.field === 'price'">
                        <input
                        type="number"
                        step="0.01"
                        v-model="editValue"
                        class="border border-gray-300 rounded p-1 w-24"
                        @keyup.enter="saveEdit(item)"
                        @blur="saveEdit(item)"
                        autofocus
                        />
                        <button @click="saveEdit(item)" class="ml-1 text-green-600">
                        <span v-if="savingField.id === item.id && savingField.field === editingField.field">⏳</span>
                        <span v-else>✔</span>
                        </button>

                    </template>
                    <template v-else>
                        Ksh {{ item.price !== null ? item.price : 'not set' }}
                    </template>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-500" @click="startEditing(item, 'cost')">
                    <template v-if="editingField.id === item.id && editingField.field === 'cost'">
                        <input
                        type="number"
                        step="0.01"
                        v-model="editValue"
                        class="border border-gray-300 rounded p-1 w-24"
                        @keyup.enter="saveEdit(item)"
                        @blur="saveEdit(item)"
                        autofocus
                        />
                       <button @click="saveEdit(item)" class="ml-1 text-green-600">
                        <span v-if="savingField.id === item.id && savingField.field === editingField.field">⏳</span>
                        <span v-else>✔</span>
                        </button>

                    </template>
                    <template v-else>
                        Ksh {{ item.cost !== null ? item.cost : 'not set' }}
                    </template>
                    </td>

                  </tr>

                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-6 text-gray-500">
              No items found to Adjust.
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/PageHeader.vue';
import axios from 'axios';

const editingField = ref({ id: null, field: null })
const editValue = ref('')

const savingField = ref({ id: null, field: null });

function startEditing(item, field) {
  editingField.value = { id: item.id, field }
  editValue.value = item[field] ?? ''
}

async function saveEdit(item) {
  const { id, field } = editingField.value;
  savingField.value = { id, field };

  try {
    await axios.put(`/stock-items/${id}`, { [field]: editValue.value });

    item[field] = editValue.value;
  } catch (err) {
    console.error(err);
    alert("Failed to save changes.");
  } finally {
    savingField.value = { id: null, field: null };
    editingField.value = { id: null, field: null };
    editValue.value = '';
  }
}

const props = defineProps({
  items: { type: Array, default: () => [] }
});
const uniqueLocations = computed(() => {
  const locations = props.items.map(i => i.location?.name).filter(Boolean);
  return [...new Set(locations)];
});

const uniqueItems = computed(() => {
  const locations = props.items.map(i => i.item?.name).filter(Boolean);
  return [...new Set(locations)];
});

const uniqueBusiness = computed(() => {
  const locations = props.items.map(i => i.location.business?.name).filter(Boolean);
  return [...new Set(locations)];
});

const uniqueBranches = computed(() => {
  const locations = props.items.map(i => i.location.branch?.name).filter(Boolean);
  return [...new Set(locations)];
});
const search = ref('');
const locationFilter = ref('');
const statusFilter = ref('');
const itemFilter = ref('');
const businessFilter = ref('');
const branchFilter = ref('');

const filteredItems = computed(() => {
  const term = search.value.trim().toLowerCase();
  
  return props.items.filter(item => {
    const matchesSearch = !term || 
      (item.item?.name || '').toLowerCase().includes(term) ||
      (item.location?.name || '').toLowerCase().includes(term) ||
      (item.location.business?.name || '').toLowerCase().includes(term) ||
      (item.location.branch?.name || '').toLowerCase().includes(term);

    const matchesLocation = !locationFilter.value || item.location?.name === locationFilter.value;
    const matchesStatus = !statusFilter.value || item.status === statusFilter.value;
    const matchesItem = !itemFilter.value || item.item?.name  === itemFilter.value;
    const matchesBusiness = !businessFilter.value || item.location.business?.name === businessFilter.value;
    const matchesBranch = !branchFilter.value || item.location.branch?.name  === branchFilter.value;

    return matchesSearch && matchesLocation && matchesStatus && matchesItem && matchesBusiness && matchesBranch;
  });
});
</script>
