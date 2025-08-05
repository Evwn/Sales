<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { 
  Building, 
  MapPin, 
  Users, 
  Phone, 
  Mail, 
  Globe, 
  Calendar,
  Eye,
  Search,
  Filter
} from 'lucide-vue-next';

interface Business {
  id: number;
  name: string;
  description: string | null;
  phone: string;
  email: string;
  tax_number: string | null;
  registration_number: string | null;
  industry: string | null;
  address: string;
  city: string;
  state: string;
  country: string;
  postal_code: string;
  website: string | null;
  logo_path: string | null;
  tax_document_path: string | null;
  registration_document_path: string | null;
  terms_and_conditions: string | null;
  owner: {
    id: number;
    name: string;
    email: string;
  } | null;
  branches_count: number;
  admins_count: number;
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  businesses: Business[];
}>();

// Get flash messages
const page = usePage();
const flash = computed(() => page.props.flash);

// Reactive data
const searchQuery = ref('');
const selectedIndustry = ref('');
const selectedCountry = ref('');

// Computed properties
const filteredBusinesses = computed(() => {
  let filtered = props.businesses;

  if (searchQuery.value) {
    filtered = filtered.filter(business =>
      business.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      business.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      business.owner?.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (selectedIndustry.value) {
    filtered = filtered.filter(business => business.industry === selectedIndustry.value);
  }

  if (selectedCountry.value) {
    filtered = filtered.filter(business => business.country === selectedCountry.value);
  }

  return filtered;
});

const industries = computed(() => {
  return [...new Set(props.businesses.map(b => b.industry).filter(Boolean))];
});

const countries = computed(() => {
  return [...new Set(props.businesses.map(b => b.country).filter(Boolean))];
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

const getStatusColor = (business: Business) => {
  if (business.branches_count > 0) return 'text-green-600 bg-green-100';
  return 'text-gray-600 bg-gray-100';
};
</script>

<template>
  <AppLayout>
    <Head title="Admin - All Businesses" />

    <PageHeader 
      title="All Businesses"
      :button="{
        text: 'Create Business',
        link: '/businesses/create',
        show: true
      }"
    >
      <template #actions>
        <Link
          href="/businesses/create"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <span class="mr-2">+</span>
          Create Business
        </Link>
      </template>
    </PageHeader>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <Building class="h-8 w-8 text-blue-600" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Businesses</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ businesses.length }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <MapPin class="h-8 w-8 text-green-600" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Branches</p>
                  <p class="text-2xl font-semibold text-gray-900">
                    {{ businesses.reduce((sum, b) => sum + b.branches_count, 0) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <Users class="h-8 w-8 text-purple-600" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Admins</p>
                  <p class="text-2xl font-semibold text-gray-900">
                    {{ businesses.reduce((sum, b) => sum + b.admins_count, 0) }}
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
                    {{ businesses.filter(b => new Date(b.updated_at) > new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)).length }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Search -->
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search businesses..."
                  class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <!-- Industry Filter -->
              <select
                v-model="selectedIndustry"
                class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">All Industries</option>
                <option v-for="industry in industries" :key="industry" :value="industry">
                  {{ industry }}
                </option>
              </select>

              <!-- Country Filter -->
              <select
                v-model="selectedCountry"
                class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">All Countries</option>
                <option v-for="country in countries" :key="country" :value="country">
                  {{ country }}
                </option>
              </select>

              <!-- Clear Filters -->
              <button
                @click="searchQuery = ''; selectedIndustry = ''; selectedCountry = ''"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Businesses Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Business
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Owner
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
                      Created
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="business in filteredBusinesses" :key="business.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap cursor-pointer hover:underline">
                      <Link :href="`/admin/businesses/${business.id}`" class="flex items-center group">
                        <div v-if="business.logo_path" class="flex-shrink-0 h-10 w-10">
                          <img :src="`/storage/${business.logo_path}`" :alt="business.name" class="h-10 w-10 rounded-full object-cover group-hover:opacity-80" />
                        </div>
                        <div v-else class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                          <Building class="h-5 w-5 text-gray-500 group-hover:text-indigo-600" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-600">{{ business.name }}</div>
                          <div v-if="business.industry" class="text-sm text-gray-500">{{ business.industry }}</div>
                        </div>
                      </Link>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="business.owner" class="text-sm text-gray-900">
                        {{ business.owner.name }}
                      </div>
                      <div v-if="business.owner" class="text-sm text-gray-500">
                        {{ business.owner.email }}
                      </div>
                      <div v-else class="text-sm text-gray-400">
                        No owner assigned
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center text-sm text-gray-900">
                        <Phone class="h-4 w-4 mr-1" />
                        {{ business.phone }}
                      </div>
                      <div class="flex items-center text-sm text-gray-500">
                        <Mail class="h-4 w-4 mr-1" />
                        {{ business.email }}
                      </div>
                      <div v-if="business.website" class="flex items-center text-sm text-blue-600">
                        <Globe class="h-4 w-4 mr-1" />
                        <a :href="business.website" target="_blank" class="hover:underline">
                          {{ business.website }}
                        </a>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ business.city }}, {{ business.state }}</div>
                      <div class="text-sm text-gray-500">{{ business.country }}</div>
                      <div class="text-sm text-gray-500">{{ business.postal_code }}</div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex space-x-4">
                        <div class="text-center">
                          <div class="text-sm font-medium text-gray-900">{{ business.branches_count }}</div>
                          <div class="text-xs text-gray-500">Branches</div>
                        </div>
                        <div class="text-center">
                          <div class="text-sm font-medium text-gray-900">{{ business.admins_count }}</div>
                          <div class="text-xs text-gray-500">Admins</div>
                        </div>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(business.created_at) }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link
                        :href="`/admin/businesses/${business.id}`"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        <Eye class="h-4 w-4 mr-1" />
                        View Details
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="filteredBusinesses.length === 0" class="text-center py-12">
                <Building class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No businesses found</h3>
                <p class="mt-1 text-sm text-gray-500">
                  {{ searchQuery || selectedIndustry || selectedCountry ? 'Try adjusting your search or filters.' : 'No businesses have been created yet.' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 