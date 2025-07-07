<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';
import Swal from 'sweetalert2';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DateRangePicker from '@/components/DateRangePicker.vue';
import SelectBusiness from '@/components/SelectBusiness.vue';
import SelectBranch from '@/components/SelectBranch.vue';
import SelectProduct from '@/components/SelectProduct.vue';
import SelectSeller from '@/components/SelectSeller.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import SalesOverTimeChart from '@/components/SalesOverTimeChart.vue';
import SalesByBusinessChart from '@/components/SalesByBusinessChart.vue';
import SalesTable from '@/components/SalesTable.vue';
import BarChart from '@/components/charts/BarChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';

const props = defineProps({
    summary: Object,
    sales: Array,
    filtersData: Object,
    perItemSalesData: Array,
    itemsReportData: Array,
    itemsValuationData: Array,
});

const filters = ref({
  date: { start: '', end: '' },
  business_id: '',
  branch_id: '',
  product_id: '',
  seller_id: '',
});

const doughnutChartRef = ref(null);

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
    filtered = filtered.filter(sale => String(sale.seller_id) === String(filters.value.seller_id));
  }

  // Filter by product
  if (filters.value.product_id) {
    filtered = filtered.filter(sale => 
      sale.items?.some(item => String(item.product?.id ?? item.product_id) === String(filters.value.product_id))
    );
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
  const params = new URLSearchParams();
  params.append('report_type', activeTab.value);
  if (filters.value.date?.start) params.append('date_from', filters.value.date.start);
  if (filters.value.date?.end) params.append('date_to', filters.value.date.end);
  if (filters.value.business_id) params.append('business_id', filters.value.business_id);
  if (filters.value.branch_id) params.append('branch_id', filters.value.branch_id);
  if (filters.value.product_id) params.append('product_id', filters.value.product_id);
  if (filters.value.seller_id) params.append('seller_id', filters.value.seller_id);
  params.append('format', 'pdf');
  // Add granularity for Profit & Loss
  if (activeTab.value === 'pl') {
    params.append('pl_granularity', plGranularity.value);
  }
  window.open(`/reports/export?${params.toString()}`);
}

function exportCSV() {
  // Build query parameters from current filters
  const params = new URLSearchParams();
  params.append('report_type', activeTab.value);
  if (filters.value.date?.start) {
    params.append('date_from', filters.value.date.start);
  }
  if (filters.value.date?.end) {
    params.append('date_to', filters.value.date.end);
  }
  if (filters.value.business_id) {
    params.append('business_id', filters.value.business_id);
  }
  if (filters.value.branch_id) {
    params.append('branch_id', filters.value.branch_id);
  }
  if (filters.value.product_id) {
    params.append('product_id', filters.value.product_id);
  }
  if (filters.value.seller_id) {
    params.append('seller_id', filters.value.seller_id);
  }
  // Add format parameter for CSV
  params.append('format', 'csv');
  // Export filtered data as CSV
  window.open(`/reports/export?${params.toString()}`);
}

function expandSale(saleId) {
  // Expand sale row for details
}

const reportTabs = [
  { key: 'sales', label: 'Sales Report' },
  { key: 'profit', label: 'Profit Report' },
  { key: 'peritem', label: 'Per Item Report' },
  { key: 'items', label: 'Items Report' },
  { key: 'stock', label: 'Stock Report' },
  { key: 'valuation', label: 'Stock & Item Valuation' },
  { key: 'pl', label: 'Profit & Loss Report' },
];
const activeTab = ref('sales');

// Add granularity refs for each time-based chart
const salesGranularity = ref('day');
const profitGranularity = ref('day');
const plGranularity = ref('month'); // P&L is usually by month, but allow selection
const stockGranularity = ref('day');

// --- Sales by Month (for Sales Report Bar Chart) ---
const salesByGranularity = computed(() => {
  const result = {};
  filteredSales.value.forEach(sale => {
    let key;
    switch (salesGranularity.value) {
      case 'year':
        key = dayjs(sale.created_at).format('YYYY');
        break;
      case 'month':
        key = dayjs(sale.created_at).format('YYYY-MM');
        break;
      case 'day':
        key = dayjs(sale.created_at).format('YYYY-MM-DD');
        break;
      case 'hour':
        key = dayjs(sale.created_at).format('YYYY-MM-DD HH:00');
        break;
      default:
        key = dayjs(sale.created_at).format('YYYY-MM-DD');
    }
    result[key] = (result[key] || 0) + Number(sale.amount);
  });
  return result;
});
const sortedSalesLabels = computed(() => [...Object.keys(salesByGranularity.value)].sort());
const salesChartLabels = sortedSalesLabels;
const salesChartData = computed(() => salesChartLabels.value.map(label => salesByGranularity.value[label]));

// --- Profit by Month (for Profit Report Line Chart) ---
const profitByGranularity = computed(() => {
  const result = {};
  filteredSales.value.forEach(sale => {
    let key;
    switch (profitGranularity.value) {
      case 'year':
        key = dayjs(sale.created_at).format('YYYY');
        break;
      case 'month':
        key = dayjs(sale.created_at).format('YYYY-MM');
        break;
      case 'day':
        key = dayjs(sale.created_at).format('YYYY-MM-DD');
        break;
      case 'hour':
        key = dayjs(sale.created_at).format('YYYY-MM-DD HH:00');
        break;
      default:
        key = dayjs(sale.created_at).format('YYYY-MM-DD');
    }
    let profit = 0;
    sale.items?.forEach(item => {
      const buy = Number(item.product.buying_price) || 0;
      const sell = Number(item.unit_price) || 0;
      profit += (sell - buy) * Number(item.quantity);
    });
    result[key] = (result[key] || 0) + profit;
  });
  return result;
});
const sortedProfitLabels = computed(() => [...Object.keys(profitByGranularity.value)].sort());
const profitChartLabels = sortedProfitLabels;
const profitChartData = computed(() => profitChartLabels.value.map(label => profitByGranularity.value[label]));

// --- Per Item Sales (for Per Item Doughnut Chart) ---
const perItemLabels = computed(() => (props.perItemSalesData || []).map(item => item.product_name));
const perItemData = computed(() => (props.perItemSalesData || []).map(item => Number(item.qty)));

// --- Items Stock (for Items Bar Chart) ---
const itemsStockLabels = computed(() => (props.itemsReportData || []).map(item => item.name));
const itemsStockData = computed(() => (props.itemsReportData || []).map(item => Number(item.stock)));

// --- Stock Movement (for Stock Line Chart) ---
const stockByGranularity = computed(() => {
  // If you have time-based stock movement, implement similar logic here
  // For now, keep as is if not time-based
  return {};
});

// --- Stock & Item Valuation (for Valuation Doughnut Chart) ---
const valuationLabels = computed(() => (props.itemsValuationData || []).map(item => item.name));
const valuationChartData = computed(() => (props.itemsValuationData || []).map(item => Number(item.stock) * Number(item.buying_price)));

// --- Profit & Loss by Month (for P&L Bar Chart) ---
const plByGranularity = computed(() => {
  const result = {};
  filteredSales.value.forEach(sale => {
    let key;
    switch (plGranularity.value) {
      case 'year':
        key = dayjs(sale.created_at).format('YYYY');
        break;
      case 'month':
        key = dayjs(sale.created_at).format('YYYY-MM');
        break;
      case 'day':
        key = dayjs(sale.created_at).format('YYYY-MM-DD');
        break;
      case 'hour':
        key = dayjs(sale.created_at).format('YYYY-MM-DD HH:00');
        break;
      default:
        key = dayjs(sale.created_at).format('YYYY-MM');
    }
    let profit = 0;
    sale.items?.forEach(item => {
      const buy = Number(item.product.buying_price) || 0;
      const sell = Number(item.unit_price) || 0;
      profit += (sell - buy) * Number(item.quantity);
    });
    result[key] = (result[key] || 0) + profit;
  });
  return result;
});
const sortedPLLabels = computed(() => [...Object.keys(plByGranularity.value)].sort());
const plChartLabels = sortedPLLabels;
const plChartData = computed(() => plChartLabels.value.map(label => plByGranularity.value[label]));

// Add computed properties for totals
const totalSalesAmount = computed(() => filteredSales.value.reduce((sum, sale) => sum + Number(sale.amount), 0));
const totalSalesProfit = computed(() => filteredSales.value.reduce((sum, sale) => sum + sale.items.reduce((s, item) => s + ((item.unit_price - (item.product?.buying_price ?? 0)) * item.quantity), 0), 0));
const totalPerItemQty = computed(() => (props.perItemSalesData || []).reduce((sum, item) => sum + Number(item.qty), 0));
const totalItemsStock = computed(() => (props.itemsReportData || []).reduce((sum, item) => sum + Number(item.stock), 0));
const totalValuationStock = computed(() => (props.itemsValuationData || []).reduce((sum, item) => sum + Number(item.stock), 0));
const totalValuationValue = computed(() => (props.itemsValuationData || []).reduce((sum, item) => sum + (Number(item.stock) * Number(item.buying_price)), 0));
const totalPLProfit = computed(() => Object.values(plByGranularity.value).reduce((sum, profit) => sum + profit, 0));

// Add computed properties for item totals
const totalSalesQuantity = computed(() => filteredSales.value.reduce((sum, sale) => sum + sale.items.reduce((s, item) => s + Number(item.quantity), 0), 0));
const totalSalesBuyingPrice = computed(() => filteredSales.value.reduce((sum, sale) => sum + sale.items.reduce((s, item) => s + Number(item.product?.buying_price ?? 0), 0), 0));
const totalSalesSellingPrice = computed(() => filteredSales.value.reduce((sum, sale) => sum + sale.items.reduce((s, item) => s + Number(item.unit_price), 0), 0));
const totalValuationSellingPrice = computed(() => (props.itemsValuationData || []).reduce((sum, item) => sum + (Number(item.stock || 0) * Number(item.price || 0)), 0));
const totalValuationExpectedProfit = computed(() => (props.itemsValuationData || []).reduce((sum, item) => sum + ((Number(item.price || 0) - Number(item.buying_price || 0)) * Number(item.stock || 0)), 0));
const totalValuationBuyingPrice = computed(() => (props.itemsValuationData || []).reduce((sum, item) => sum + (Number(item.stock || 0) * Number(item.buying_price || 0)), 0));

function truncateWords(str, maxWords) {
  if (!str) return '';
  const words = String(str).split(' ');
  if (words.length <= maxWords) return str;
  return words.slice(0, maxWords).join(' ') + '...';
}

function getColClass(idx) {
  // Alternate column background: even grey, odd white
  return idx % 2 === 0 ? 'bg-gray-100' : 'bg-white';
}

// Add computed for day boundaries for hourly charts
const getDayBoundaries = (labels) => {
  let prevDay = null;
  const boundaries = [];
  labels.forEach((label, idx) => {
    const day = label.split(' ')[0]; // 'YYYY-MM-DD'
    if (day !== prevDay) {
      boundaries.push(idx);
      prevDay = day;
    }
  });
  return boundaries;
};
const salesDayBoundaries = computed(() => salesGranularity.value === 'hour' ? getDayBoundaries(salesChartLabels.value) : []);
const profitDayBoundaries = computed(() => profitGranularity.value === 'hour' ? getDayBoundaries(profitChartLabels.value) : []);
const plDayBoundaries = computed(() => plGranularity.value === 'hour' ? getDayBoundaries(plChartLabels.value) : []);
const stockDayBoundaries = computed(() => stockGranularity.value === 'hour' ? getDayBoundaries(stockMovementLabels) : []);

function getSectionLabel(dateStr) {
  const date = dayjs(dateStr);
  const today = dayjs();
  if (date.isSame(today, 'day')) return 'Today';
  if (date.isSame(today.subtract(1, 'day'), 'day')) return 'Yesterday';
  return '';
}
function getSectionFullLabel(dateStr) {
  const date = dayjs(dateStr);
  const today = dayjs();
  if (date.isSame(today, 'day')) return 'Today';
  if (date.isSame(today.subtract(1, 'day'), 'day')) return 'Yesterday';
  if (date.isAfter(today.subtract(7, 'day'))) return date.format('dddd'); // Day name
  return date.format('YYYY-MM-DD');
}
function getSectionLabelsAndFull(labels) {
  let prevDay = null;
  const sectionLabels = [];
  const sectionFullLabels = [];
  labels.forEach((label, idx) => {
    const day = label.split(' ')[0];
    if (day !== prevDay) {
      sectionLabels.push(getSectionLabel(day));
      sectionFullLabels.push(getSectionFullLabel(day));
      prevDay = day;
    }
  });
  return { sectionLabels, sectionFullLabels };
}
const salesSectionLabels = computed(() => {
  if (salesGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(salesChartLabels.value).sectionLabels;
});
const salesSectionFullLabels = computed(() => {
  if (salesGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(salesChartLabels.value).sectionFullLabels;
});
const profitSectionLabels = computed(() => {
  if (profitGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(profitChartLabels.value).sectionLabels;
});
const profitSectionFullLabels = computed(() => {
  if (profitGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(profitChartLabels.value).sectionFullLabels;
});
const plSectionLabels = computed(() => {
  if (plGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(plChartLabels.value).sectionLabels;
});
const plSectionFullLabels = computed(() => {
  if (plGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(plChartLabels.value).sectionFullLabels;
});
const stockSectionLabels = computed(() => {
  if (stockGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(stockMovementLabels).sectionLabels;
});
const stockSectionFullLabels = computed(() => {
  if (stockGranularity.value !== 'hour') return [];
  return getSectionLabelsAndFull(stockMovementLabels).sectionFullLabels;
});

const plGranularityLabel = computed(() => {
  switch (plGranularity.value) {
    case 'day': return 'Day';
    case 'month': return 'Month';
    case 'year': return 'Year';
    case 'hour': return 'Hour';
    default: return 'Period';
  }
});

const sendingEmail = ref(false);

async function sendReportEmail() {
  sendingEmail.value = true;
  try {
    await fetch('/reports/email', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
      },
      body: JSON.stringify({
        report_type: activeTab.value,
        filters: filters.value,
        pl_granularity: activeTab.value === 'pl' ? plGranularity.value : undefined,
      }),
    });
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Report sent to your email!'
    });
  } catch (e) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to send email.'
    });
  }
  sendingEmail.value = false;
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
                            <!-- Report Navigation Tabs -->
                            <div class="flex gap-2 mb-8">
                                <button
                                    v-for="tab in reportTabs"
                                    :key="tab.key"
                                    @click="activeTab = tab.key"
                                    :class="[
                                        'px-4 py-2 rounded font-semibold transition',
                                        activeTab === tab.key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-blue-100'
                                    ]"
                                >
                                    {{ tab.label }}
                                </button>
                            </div>

                            <!-- Filters -->
                            <div class="filters grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <DateRangePicker v-model="filters.date" />
                                <SelectBusiness v-model="filters.business_id" :options="props.filtersData.businesses" />
                                <SelectBranch v-model="filters.branch_id" :options="filteredBranches" />
                                <SelectProduct v-model="filters.product_id" :options="filteredProducts" />
                                <SelectSeller v-model="filters.seller_id" :options="filteredSellers" />
                            </div>

                            <!-- Summary Cards -->
                            <div class="summary-cards grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <SummaryCard title="Total Sales" :value="computedSummary.totalSales" icon="mdi-cash-register" />
                                <SummaryCard title="Total Revenue" :value="`KES ${computedSummary.totalRevenue.toFixed(2)}`" icon="mdi-currency-usd" />
                                <SummaryCard title="Top Products" :value="computedSummary.topProducts.slice(0, 3)" icon="mdi-star" />
                                <SummaryCard title="Top Sellers" :value="computedSummary.topSellers.slice(0, 3)" icon="mdi-account-group" />
                            </div>

                            <!-- Export Buttons (moved to top of each report) -->
                            <div class="export-buttons flex gap-4 mb-6">
                                <button class="btn btn-outline" @click="exportPDF">Export PDF</button>
                                <button class="btn btn-outline" @click="exportCSV">Export CSV</button>
                                <button class="btn btn-outline" @click="sendReportEmail" :disabled="sendingEmail">
                                    <span v-if="sendingEmail">Sending...</span>
                                    <span v-else>Send to Email</span>
                                </button>
                            </div>

                            <!-- Charts and Tables for Each Report Tab -->
                            <div v-if="activeTab === 'sales'">
                                <h3 class="font-semibold text-lg mb-2">Sales Overview</h3>
                                <select v-model="salesGranularity" class="mb-2 px-2 py-1 border rounded">
                                    <option value="day">Day</option>
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                    <option value="hour">Hour</option>
                                </select>
                                <BarChart :data="salesChartData" :labels="salesChartLabels" :day-boundaries="salesDayBoundaries" :section-labels="salesSectionLabels" :section-full-labels="salesSectionFullLabels" />
                                <table class="min-w-full table-auto border mt-6 sales-table-beautiful">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Business</th>
                                            <th>Branch</th>
                                            <th>Seller</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Buying Price</th>
                                            <th>Selling Price</th>
                                            <th>Profit</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(sale, saleIdx) in filteredSales" :key="sale.id">
                                            <template v-for="(item, itemIdx) in sale.items" :key="item.id">
                                                <tr>
                                                    <td v-if="itemIdx === 0" :rowspan="sale.items.length" :class="getColClass(0)">
                                                        <span :title="saleIdx + 1">{{ truncateWords(saleIdx + 1, 3) }}</span>
                                                    </td>
                                                    <td v-if="itemIdx === 0" :rowspan="sale.items.length" :class="getColClass(1)">
                                                        <span :title="dayjs(sale.created_at).format('DD/MM/YYYY')">{{ truncateWords(dayjs(sale.created_at).format('DD/MM/YYYY'), 3) }}</span>
                                                    </td>
                                                    <td v-if="itemIdx === 0" :rowspan="sale.items.length" :class="getColClass(2)">
                                                        <span :title="sale.business?.name">{{ truncateWords(sale.business?.name, 3) }}</span>
                                                    </td>
                                                    <td v-if="itemIdx === 0" :rowspan="sale.items.length" :class="getColClass(3)">
                                                        <span :title="sale.branch?.name">{{ truncateWords(sale.branch?.name, 3) }}</span>
                                                    </td>
                                                    <td v-if="itemIdx === 0" :rowspan="sale.items.length" :class="getColClass(4)">
                                                        <span :title="sale.seller?.name">{{ truncateWords(sale.seller?.name, 3) }}</span>
                                                    </td>
                                                    <td :class="getColClass(5)">
                                                        <span :title="item.product_name + ' (' + (sale.branch?.name || 'N/A') + ')'">
                                                            {{ truncateWords(item.product_name + ' (' + (sale.branch?.name || 'N/A') + ')', 3) }}
                                                        </span>
                                                    </td>
                                                    <td :class="getColClass(6)">
                                                        <span :title="item.quantity">{{ truncateWords(item.quantity, 3) }}</span>
                                                    </td>
                                                    <td :class="getColClass(7)">
                                                        <span :title="item.product?.buying_price ?? '-'">{{ truncateWords(item.product?.buying_price ?? '-', 3) }}</span>
                                                    </td>
                                                    <td :class="getColClass(8)">
                                                        <span :title="item.unit_price">{{ truncateWords(item.unit_price, 3) }}</span>
                                                    </td>
                                                    <td :class="getColClass(9)">
                                                        <span :title="((item.unit_price - (item.product?.buying_price ?? 0)) * item.quantity).toFixed(2)">
                                                            {{ truncateWords(((item.unit_price - (item.product?.buying_price ?? 0)) * item.quantity).toFixed(2), 3) }}
                                                        </span>
                                                    </td>
                                                    <td v-if="itemIdx === 0" :rowspan="sale.items.length" :class="getColClass(10)">
                                                        <span :title="sale.amount">{{ truncateWords(sale.amount, 3) }}</span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold">
                                            <td>Total</td>
                                            <td></td><td></td><td></td><td></td><td></td>
                                            <td>{{ totalSalesQuantity }}</td>
                                            <td>{{ totalSalesBuyingPrice.toFixed(2) }}</td>
                                            <td>{{ totalSalesSellingPrice.toFixed(2) }}</td>
                                            <td>{{ totalSalesProfit.toFixed(2) }}</td>
                                            <td>{{ totalSalesAmount.toFixed(2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div v-else-if="activeTab === 'profit'">
                                <h3 class="font-semibold text-lg mb-2">Profit Overview</h3>
                                <select v-model="profitGranularity" class="mb-2 px-2 py-1 border rounded">
                                    <option value="day">Day</option>
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                    <option value="hour">Hour</option>
                                </select>
                                <LineChart :data="profitChartData" :labels="profitChartLabels" :day-boundaries="profitDayBoundaries" :section-labels="profitSectionLabels" :section-full-labels="profitSectionFullLabels" />
                                <table class="min-w-full table-auto border mt-6 sales-table-beautiful">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Business</th>
                                            <th>Branch</th>
                                            <th>Seller</th>
                                            <th>Products</th>
                                            <th>Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(sale, idx) in filteredSales" :key="sale.id">
                                            <td :class="getColClass(0)"><span :title="idx + 1">{{ truncateWords(idx + 1, 3) }}</span></td>
                                            <td :class="getColClass(1)"><span :title="dayjs(sale.created_at).format('DD/MM/YYYY')">{{ truncateWords(dayjs(sale.created_at).format('DD/MM/YYYY'), 3) }}</span></td>
                                            <td :class="getColClass(2)"><span :title="sale.business?.name">{{ truncateWords(sale.business?.name, 3) }}</span></td>
                                            <td :class="getColClass(3)"><span :title="sale.branch?.name">{{ truncateWords(sale.branch?.name, 3) }}</span></td>
                                            <td :class="getColClass(4)"><span :title="sale.seller?.name">{{ truncateWords(sale.seller?.name, 3) }}</span></td>
                                            <td :class="getColClass(5)"><span :title="sale.items.map(item => item.product_name + ' x' + item.quantity).join(', ')">{{ truncateWords(sale.items.map(item => item.product_name + ' x' + item.quantity).join(', '), 3) }}</span></td>
                                            <td :class="getColClass(6)"><span :title="sale.items.reduce((sum, item) => sum + ((item.unit_price - item.product.buying_price) * item.quantity), 0).toFixed(2)">{{ truncateWords(sale.items.reduce((sum, item) => sum + ((item.unit_price - item.product.buying_price) * item.quantity), 0).toFixed(2), 3) }}</span></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold">
                                            <td>Total</td>
                                            <td></td><td></td><td></td><td></td><td></td>
                                            <td>{{ totalSalesProfit.toFixed(2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div v-else-if="activeTab === 'peritem'">
                                <h3 class="font-semibold text-lg mb-2">Per Item Report</h3>
                                <DoughnutChart ref="doughnutChartRef" :data="{ labels: perItemLabels, datasets: [{ data: perItemData }] }" />
                                <table class="min-w-full table-auto border mt-6 sales-table-beautiful">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity Sold</th>
                                            <th>Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in props.perItemSalesData" :key="item.product_name">
                                            <td>{{ item.product_name }}</td>
                                            <td>{{ item.qty }}</td>
                                            <td>{{ item.unit || 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold">
                                            <td>Total</td>
                                            <td>{{ totalPerItemQty }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div v-else-if="activeTab === 'items'">
                                <h3 class="font-semibold text-lg mb-2">Items Report</h3>
                                <BarChart :data="itemsStockData" :labels="itemsStockLabels" :section-labels="stockSectionLabels" :section-full-labels="stockSectionFullLabels" />
                                <table class="min-w-full table-auto border mt-6 sales-table-beautiful">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in props.itemsReportData" :key="item.name">
                                            <td>{{ item.name }}</td>
                                            <td>{{ item.stock }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold">
                                            <td>Total</td>
                                            <td>{{ totalItemsStock }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div v-else-if="activeTab === 'stock'">
                                <h3 class="font-semibold text-lg mb-2">Stock Report</h3>
                                <select v-model="stockGranularity" class="mb-2 px-2 py-1 border rounded">
                                    <option value="day">Day</option>
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                    <option value="hour">Hour</option>
                                </select>
                                <LineChart :data="stockByGranularity" :labels="stockMovementLabels" :day-boundaries="stockDayBoundaries" :section-labels="stockSectionLabels" :section-full-labels="stockSectionFullLabels" />
                                <table class="min-w-full table-auto border mt-6 sales-table-beautiful">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in props.itemsReportData" :key="item.name">
                                            <td>{{ item.name }}</td>
                                            <td>{{ item.stock }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold">
                                            <td>Total</td>
                                            <td>{{ totalItemsStock }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div v-else-if="activeTab === 'valuation'">
                                <h3 class="font-semibold text-lg mb-2">Stock & Item Valuation</h3>
                                <DoughnutChart :data="{ labels: valuationLabels, datasets: [{ data: valuationChartData }] }" />
                                <table class="min-w-full table-auto border mt-6 sales-table-beautiful">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Stock</th>
                                            <th>Buying Price</th>
                                            <th>Selling Price</th>
                                            <th>Value</th>
                                            <th>Expected Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in props.itemsValuationData" :key="item.name">
                                            <td>{{ item.name }}</td>
                                            <td>{{ item.stock }}</td>
                                            <td>{{ item.buying_price }}</td>
                                            <td>{{ item.price }}</td>
                                            <td>{{ (Number(item.stock) * Number(item.buying_price)).toFixed(2) }}</td>
                                            <td>{{ ((Number(item.price || 0) - Number(item.buying_price || 0)) * Number(item.stock || 0)).toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold">
                                            <td>Total</td>
                                            <td>{{ totalValuationStock }}</td>
                                            <td>{{ totalValuationBuyingPrice.toFixed(2) }}</td>
                                            <td>{{ totalValuationSellingPrice.toFixed(2) }}</td>
                                            <td>{{ totalValuationValue.toFixed(2) }}</td>
                                            <td>{{ totalValuationExpectedProfit.toFixed(2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div v-else-if="activeTab === 'pl'">
                                <h3 class="font-semibold text-lg mb-2">Profit & Loss Report</h3>
                                <select v-model="plGranularity" class="mb-2 px-2 py-1 border rounded">
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                    <option value="day">Day</option>
                                    <option value="hour">Hour</option>
                                </select>
                                <BarChart :data="plChartData" :labels="plChartLabels" :day-boundaries="plDayBoundaries" :section-labels="plSectionLabels" :section-full-labels="plSectionFullLabels" />
                                <table class="min-w-full table-auto border mt-6 sales-table-beautiful">
                                    <thead>
                                        <tr>
                                            <th>{{ plGranularityLabel }}</th>
                                            <th>Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(profit, month) in plByGranularity" :key="month">
                                            <td>{{ month }}</td>
                                            <td>{{ profit.toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold">
                                            <td>Total</td>
                                            <td>{{ totalPLProfit.toFixed(2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
.sales-table-beautiful th, .sales-table-beautiful td {
  border: 1px solid #d1d5db;
  padding: 6px 8px;
  max-width: 120px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.sales-table-beautiful tr {
  border-bottom: 1px solid #d1d5db;
}
.sales-table-beautiful tr:nth-child(even) td {
  background: #f3f4f6;
}
.sales-table-beautiful tr:nth-child(odd) td {
  background: #fff;
}
.sales-table-beautiful td, .sales-table-beautiful th {
  text-align: left;
}
</style> 
