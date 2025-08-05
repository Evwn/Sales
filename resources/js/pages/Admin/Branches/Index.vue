<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { 
  MapPin, 
  Building, 
  Users, 
  Package, 
  Phone, 
  Mail, 
  Calendar,
  Eye,
  Search,
  Store
} from 'lucide-vue-next';

interface Branch {
  id: number;
  name: string;
  address: string;
  gps_latitude: number | null;
  gps_longitude: number | null;
  phone: string;
  email: string;
  status: string;
  barcode_path: string | null;
  business: {
    id: number;
    name: string;
    owner: {
      id: number;
      name: string;
      email: string;
    } | null;
  } | null;
  sellers_count: number;
  products_count: number;
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  branches: Branch[];
}>();

// Get flash messages
const page = usePage();
const flash = computed(() => page.props.flash);

// Reactive data
const searchQuery = ref('');
const selectedStatus = ref('');

// Computed properties
const filteredBranches = computed(() => {
  let filtered = props.branches;

  if (searchQuery.value) {
    filtered = filtered.filter(branch =>
      branch.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      branch.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      branch.business?.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      branch.business?.owner?.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(branch => branch.status === selectedStatus.value);
  }

  return filtered;
});

const statuses = computed(() => {
  return [...new Set(props.branches.map(b => b.status).filter(Boolean))];
});

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'active':
      return 'text-green-600 bg-green-100';
    case 'inactive':
      return 'text-red-600 bg-red-100';
    default:
      return 'text-gray-600 bg-gray-100';
  }
};
</script>

<template>
  <AppLayout>
    <Head title="Admin - All Branches" />

    <PageHeader title="All Branches" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <Store class="h-8 w-8 text-blue-600" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Branches</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ branches.length }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <Users class="h-8 w-8 text-green-600" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Sellers</p>
                  <p class="text-2xl font-semibold text-gray-900">
                    {{ branches.reduce((sum, b) => sum + b.sellers_count, 0) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <Package class="h-8 w-8 text-purple-600" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Products</p>
                  <p class="text-2xl font-semibold text-gray-900">
                    {{ branches.reduce((sum, b) => sum + b.products_count, 0) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <Calendar class="h-8 w-8 text-orange-600" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Active This Month</p>
                  <p class="text-2xl font-semibold text-gray-900">
                    {{ branches.filter(b => new Date(b.updated_at) > new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)).length }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Search -->
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search branches..."
                  class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <!-- Status Filter -->
              <select
                v-model="selectedStatus"
                class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">All Statuses</option>
                <option v-for="status in statuses" :key="status" :value="status">
                  {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                </option>
              </select>

              <!-- Clear Filters -->
              <button
                @click="searchQuery = ''; selectedStatus = ''"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Branches Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Branch
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Business
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Contact
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stats
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Created
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="branch in filteredBranches" :key="branch.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                          <Store class="h-5 w-5 text-gray-500" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ branch.name }}</div>
                          <div class="text-sm text-gray-500">{{ branch.address }}</div>
                        </div>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="branch.business" class="text-sm text-gray-900">
                        {{ branch.business.name }}
                      </div>
                      <div v-if="branch.business?.owner" class="text-sm text-gray-500">
                        Owner: {{ branch.business.owner.name }}
                      </div>
                      <div v-else class="text-sm text-gray-400">
                        No business assigned
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center text-sm text-gray-900">
                        <Phone class="h-4 w-4 mr-1" />
                        {{ branch.phone }}
                      </div>
                      <div class="flex items-center text-sm text-gray-500">
                        <Mail class="h-4 w-4 mr-1" />
                        {{ branch.email }}
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ branch.address }}</div>
                      <div v-if="branch.gps_latitude && branch.gps_longitude" class="text-sm text-gray-500">
                        GPS: {{ Number(branch.gps_latitude).toFixed(4) }}, {{ Number(branch.gps_longitude).toFixed(4) }}
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex space-x-4">
                        <div class="text-center">
                          <div class="text-sm font-medium text-gray-900">{{ branch.sellers_count }}</div>
                          <div class="text-xs text-gray-500">Sellers</div>
                        </div>
                        <div class="text-center">
                          <div class="text-sm font-medium text-gray-900">{{ branch.products_count }}</div>
                          <div class="text-xs text-gray-500">Products</div>
                        </div>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(branch.status)}`">
                        {{ branch.status.charAt(0).toUpperCase() + branch.status.slice(1) }}
                      </span>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(branch.created_at) }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link
                        :href="`/admin/branches/${branch.id}`"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        <Eye class="h-4 w-4 mr-1" />
                        View Details
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="filteredBranches.length === 0" class="text-center py-12">
                <Store class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No branches found</h3>
                <p class="mt-1 text-sm text-gray-500">
                  {{ searchQuery || selectedStatus ? 'Try adjusting your search or filters.' : 'No branches have been created yet.' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 