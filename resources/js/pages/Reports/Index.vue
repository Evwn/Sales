<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DateRangePicker from '@/components/DateRangePicker.vue';
import SelectBusiness from '@/components/SelectBusiness.vue';
import SelectBranch from '@/components/SelectBranch.vue';
import SelectProduct from '@/components/SelectProduct.vue';
import SelectSeller from '@/components/SelectSeller.vue';
import SelectStatus from '@/components/SelectStatus.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import SalesOverTimeChart from '@/components/SalesOverTimeChart.vue';
import SalesByBusinessChart from '@/components/SalesByBusinessChart.vue';
import SalesTable from '@/components/SalesTable.vue';

const props = defineProps({
    summary: Object,
    sales: Array,
    filtersData: Object,
});

const filters = ref({
  date: { start: '', end: '' },
  business_id: '',
  branch_id: '',
  product_id: '',
  seller_id: '',
  status: '',
});

// Client-side filtering
const filteredSales = computed(() => {
  let filtered = props.sales || [];

  // Filter by business
  if (filters.value.business_id) {
    filtered = filtered.filter(sale => sale.business_id == filters.value.business_id);
  }

  // Filter by branch
  if (filters.value.branch_id) {
    filtered = filtered.filter(sale => sale.branch_id == filters.value.branch_id);
  }

  // Filter by seller
  if (filters.value.seller_id) {
    filtered = filtered.filter(sale => sale.seller_id == filters.value.seller_id);
  }

  // Filter by product
  if (filters.value.product_id) {
    filtered = filtered.filter(sale => 
      sale.items?.some(item => item.product_id == filters.value.product_id)
    );
  }

  // Filter by status
  if (filters.value.status) {
    filtered = filtered.filter(sale => sale.status === filters.value.status);
  }

  // Filter by date range
  if (filters.value.date.start) {
    filtered = filtered.filter(sale => 
      new Date(sale.created_at) >= new Date(filters.value.date.start)
    );
  }
  if (filters.value.date.end) {
    filtered = filtered.filter(sale => 
      new Date(sale.created_at) <= new Date(filters.value.date.end)
    );
  }

  return filtered;
});

// Computed summary data
const computedSummary = computed(() => ({
  totalSales: filteredSales.value.length,
  totalRevenue: filteredSales.value.reduce((sum, sale) => sum + Number(sale.amount), 0),
  topProducts: props.summary?.topProducts || [],
  topSellers: props.summary?.topSellers || [],
}));

// Filter branches by selected business
const filteredBranches = computed(() => {
  if (!filters.value.business_id) return props.filtersData?.branches || [];
  return props.filtersData?.branches?.filter(b => b.business_id == filters.value.business_id) || [];
});

// Filter sellers by selected business and branch
const filteredSellers = computed(() => {
  let sellers = props.filtersData?.sellers || [];
  
  // If business is selected, filter sellers by business
  if (filters.value.business_id) {
    sellers = sellers.filter(s => s.business_id == filters.value.business_id);
  }
  
  // If branch is selected, filter sellers by branch
  if (filters.value.branch_id) {
    sellers = sellers.filter(s => s.branch_id == filters.value.branch_id);
  }
  
  return sellers;
});

// Filter products by selected business and branch
const filteredProducts = computed(() => {
  let products = props.filtersData?.products || [];
  
  // If business is selected, filter products by business
  if (filters.value.business_id) {
    products = products.filter(p => p.business_id == filters.value.business_id);
  }
  
  // If branch is selected, filter products by branch
  if (filters.value.branch_id) {
    products = products.filter(p => p.branch_id == filters.value.branch_id);
  }
  
  return products;
});

function exportPDF() {
  // Export filtered data
  window.open(route('reports.export', { 
    ...filters.value, 
    format: 'pdf',
    sales: filteredSales.value.map(s => s.id)
  }), '_blank');
}

function exportCSV() {
  // Export filtered data
  window.open(route('reports.export', { 
    ...filters.value, 
    format: 'csv',
    sales: filteredSales.value.map(s => s.id)
  }), '_blank');
}

function expandSale(saleId) {
  // Expand sale row for details
}
</script>

<template>
    <AppLayout>
        <Head title="Reports" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reports
                </h2>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="reports-page">
                            <!-- Filters -->
                            <div class="filters grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <DateRangePicker v-model="filters.date" />
                                <SelectBusiness v-model="filters.business_id" :options="props.filtersData.businesses" />
                                <SelectBranch v-model="filters.branch_id" :options="filteredBranches" />
                                <SelectProduct v-model="filters.product_id" :options="filteredProducts" />
                                <SelectSeller v-model="filters.seller_id" :options="filteredSellers" />
                                <SelectStatus v-model="filters.status" />
                            </div>

                            <!-- Summary Cards -->
                            <div class="summary-cards grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <SummaryCard title="Total Sales" :value="computedSummary.totalSales" icon="mdi-cash-register" />
                                <SummaryCard title="Total Revenue" :value="`KES ${computedSummary.totalRevenue.toFixed(2)}`" icon="mdi-currency-usd" />
                                <SummaryCard title="Top Products" :value="computedSummary.topProducts" icon="mdi-star" />
                                <SummaryCard title="Top Sellers" :value="computedSummary.topSellers" icon="mdi-account-group" />
                            </div>

                            <!-- Charts -->
                            <div class="charts grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <SalesOverTimeChart :data="filteredSales" />
                                <SalesByBusinessChart :data="props.summary.salesByBusiness" />
                            </div>

                            <!-- Export Buttons -->
                            <div class="export-buttons flex gap-4 mb-6">
                                <button class="btn btn-outline" @click="exportPDF">Export PDF</button>
                                <button class="btn btn-outline" @click="exportCSV">Export CSV</button>
                        </div>

                            <!-- Detailed Table -->
                            <SalesTable :sales="filteredSales" @expand="expandSale" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 

<style scoped>
.reports-page {
    padding: 2rem;
    background: #f9f9fb;
    min-height: 100vh;
}
.filters, .summary-cards, .charts, .export-buttons {
    margin-bottom: 2rem;
}
</style> 
