<template>
  <AppLayout>
    <Head title="Receipt History" />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Receipt History
        </h2>
        <div class="flex space-x-4">
          <button @click="showExportModal = true" class="btn btn-primary">
            Export Report
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Search Section -->
          <div class="mb-6">
            <div class="flex space-x-4">
              <div class="flex-1">
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search by reference or barcode..."
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
              </div>
              <div>
                <select
                  v-model="selectedBranch"
                  class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                  <option value="">All Branches</option>
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Receipts Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input
                      type="checkbox"
                      v-model="selectAll"
                      @change="toggleSelectAll"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Reference
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Branch
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Barcode
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="sale in sales.data" :key="sale.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      v-model="selectedSales"
                      :value="sale.id"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ sale.reference }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ formatDate(sale.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ sale.branch?.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ formatCurrency(sale.total_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ sale.barcode }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      @click="previewReceipt(sale)"
                      class="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Preview
                    </button>
                    <a
                      :href="`/sales/receipt/${sale.id}.pdf`"
                      target="_blank"
                      class="text-green-600 hover:text-green-900"
                    >
                      Export
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-4">
            <Pagination :links="sales.links" />
          </div>
        </div>
      </div>
    </div>

    <!-- Export Modal -->
    <Modal :show="showExportModal" @close="showExportModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
          Export Options
        </h2>

        <div class="space-y-4">
          <!-- Report Types -->
          <div class="space-y-4">
            <h3 class="font-medium text-gray-900">Report Types</h3>
            
            <!-- Daily Report -->
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Daily Report</h4>
              <div class="flex space-x-2">
                <input
                  type="date"
                  v-model="exportDate"
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <button
                  @click="exportDailyReport"
                  class="btn-primary"
                >
                  Export
                </button>
              </div>
            </div>

            <!-- Weekly Report -->
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Weekly Report</h4>
              <div class="flex space-x-2">
                <input
                  type="date"
                  v-model="exportWeekStart"
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <button
                  @click="exportWeeklyReport"
                  class="btn-primary"
                >
                  Export
                </button>
              </div>
            </div>

            <!-- Monthly Report -->
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Monthly Report</h4>
              <div class="flex space-x-2">
                <input
                  type="month"
                  v-model="exportMonth"
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <button
                  @click="exportMonthlyReport"
                  class="btn-primary"
                >
                  Export
                </button>
              </div>
            </div>

            <!-- Quarterly Report -->
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Quarterly Report</h4>
              <div class="flex space-x-2">
                <select
                  v-model="exportQuarter"
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                  <option value="1">Q1 (Jan-Mar)</option>
                  <option value="2">Q2 (Apr-Jun)</option>
                  <option value="3">Q3 (Jul-Sep)</option>
                  <option value="4">Q4 (Oct-Dec)</option>
                </select>
                <input
                  type="number"
                  v-model="exportQuarterYear"
                  min="2000"
                  max="2100"
                  class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Year"
                />
                <button
                  @click="exportQuarterlyReport"
                  class="btn-primary"
                >
                  Export
                </button>
              </div>
            </div>

            <!-- Yearly Report -->
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Yearly Report</h4>
              <div class="flex space-x-2">
                <input
                  type="number"
                  v-model="exportYear"
                  min="2000"
                  max="2100"
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Year"
                />
                <button
                  @click="exportYearlyReport"
                  class="btn-primary"
                >
                  Export
                </button>
              </div>
            </div>
          </div>

          <!-- Batch Export Options -->
          <div v-if="selectedSales.length > 0" class="mt-6 space-y-4">
            <h3 class="font-medium text-gray-900">Batch Export Options</h3>
            <div class="p-4 bg-gray-50 rounded-lg space-y-4">
              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="batchOptions.include_summary"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <label>Include Summary</label>
              </div>
              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="batchOptions.group_by_branch"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <label>Group by Branch</label>
              </div>
              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="batchOptions.include_charts"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <label>Include Charts</label>
              </div>
              <div class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="batchOptions.page_break_between"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <label>Page Break Between Receipts</label>
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Custom Header</label>
                <textarea
                  v-model="batchOptions.custom_header"
                  rows="2"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Enter custom header text..."
                ></textarea>
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Custom Footer</label>
                <textarea
                  v-model="batchOptions.custom_footer"
                  rows="2"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Enter custom footer text..."
                ></textarea>
              </div>
              <button
                @click="exportBatchReceipts"
                class="btn-primary w-full"
              >
                Export Selected Receipts
              </button>
            </div>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Preview Modal -->
    <Modal :show="showPreviewModal" @close="showPreviewModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
          Receipt Preview
        </h2>
        <div v-if="selectedSale" class="space-y-4">
          <SalesReceipt :sale="selectedSale" />
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts';
import Modal from '@/components/Modal.vue';
import Pagination from '@/components/Pagination.vue';
import SalesReceipt from '@/components/SalesReceipt.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
  sales: Object,
  branches: Array,
});

const search = ref('');
const selectedBranch = ref('');
const showPreviewModal = ref(false);
const showExportModal = ref(false);
const selectedSale = ref(null);
const selectedSales = ref([]);
const selectAll = ref(false);
const exportDate = ref(new Date().toISOString().split('T')[0]);
const exportWeekStart = ref(new Date().toISOString().split('T')[0]);
const exportMonth = ref(new Date().toISOString().split('T')[0].slice(0, 7));
const exportQuarter = ref(Math.ceil(new Date().getMonth() / 3));
const exportQuarterYear = ref(new Date().getFullYear());
const exportYear = ref(new Date().getFullYear());
const batchOptions = ref({
  include_summary: true,
  group_by_branch: false,
  include_charts: true,
  page_break_between: true,
  custom_header: '',
  custom_footer: ''
});

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const previewReceipt = (sale) => {
  selectedSale.value = sale;
  showPreviewModal.value = true;
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedSales.value = props.sales.data.map(sale => sale.id);
  } else {
    selectedSales.value = [];
  }
};

const exportReceipt = async (sale) => {
  try {
    const { value: deliveryMethod } = await Swal.fire({
      title: 'Choose Delivery Method',
      html: `
        <div class="space-y-4">
          <button id="download" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Download PDF
          </button>
          <button id="whatsapp" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Send via WhatsApp
          </button>
        </div>
      `,
      showConfirmButton: false,
      showCancelButton: true,
      cancelButtonText: 'Cancel',
      didOpen: () => {
        document.getElementById('download').addEventListener('click', () => {
          Swal.close();
          router.visit(`/sales/receipt/${sale.id}.pdf`);
        });
        document.getElementById('whatsapp').addEventListener('click', async () => {
          const { value: phoneNumber } = await Swal.fire({
            title: 'Enter WhatsApp Number',
            input: 'text',
            inputLabel: 'Phone number with country code (e.g., +254712345678)',
            inputPlaceholder: '+254712345678',
            showCancelButton: true,
            inputValidator: (value) => {
              if (!value) {
                return 'You need to enter a phone number!';
              }
              if (!/^\+[1-9]\d{1,14}$/.test(value)) {
                return 'Please enter a valid phone number with country code!';
              }
            }
          });
          
          if (phoneNumber) {
            try {
              const response = await axios.get(`/sales/receipt/${sale.id}.pdf`, {
                params: {
                  whatsapp: true,
                  whatsapp_number: phoneNumber
                }
              });
              Swal.fire('Success', 'Receipt sent via WhatsApp', 'success');
            } catch (error) {
              Swal.fire('Error', 'Failed to send receipt via WhatsApp', 'error');
            }
          }
        });
      }
    });
  } catch (error) {
    console.error('Export error:', error);
  }
};

const exportBatchReceipts = async () => {
  if (selectedSales.value.length === 0) {
    Swal.fire('Error', 'Please select at least one receipt', 'error');
    return;
  }

  try {
    const { value: deliveryMethod } = await Swal.fire({
      title: 'Choose Delivery Method',
      html: `
        <div class="space-y-4">
          <button id="download" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Download PDF
          </button>
          <button id="whatsapp" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Send via WhatsApp
          </button>
        </div>
      `,
      showConfirmButton: false,
      showCancelButton: true,
      cancelButtonText: 'Cancel',
      didOpen: () => {
        document.getElementById('download').addEventListener('click', async () => {
          try {
            const response = await axios.post('/sales/batch-receipts.pdf', {
              sale_ids: selectedSales.value
            }, {
              responseType: 'blob'
            });
            
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'batch-receipts.pdf');
            document.body.appendChild(link);
            link.click();
            link.remove();
            Swal.close();
          } catch (error) {
            console.error('Export error:', error);
            Swal.fire('Error', 'Failed to export receipts', 'error');
          }
        });
        
        document.getElementById('whatsapp').addEventListener('click', async () => {
          const { value: phoneNumber } = await Swal.fire({
            title: 'Enter WhatsApp Number',
            input: 'text',
            inputLabel: 'Phone number with country code (e.g., +254712345678)',
            inputPlaceholder: '+254712345678',
            showCancelButton: true,
            inputValidator: (value) => {
              if (!value) {
                return 'You need to enter a phone number!';
              }
              if (!/^\+[1-9]\d{1,14}$/.test(value)) {
                return 'Please enter a valid phone number with country code!';
              }
            }
          });
          
          if (phoneNumber) {
            try {
              const response = await axios.post('/sales/batch-receipts.pdf', {
                sale_ids: selectedSales.value,
                whatsapp: true,
                whatsapp_number: phoneNumber
              });
              Swal.fire('Success', 'Receipts sent via WhatsApp', 'success');
            } catch (error) {
              Swal.fire('Error', 'Failed to send receipts via WhatsApp', 'error');
            }
          }
        });
      }
    });
  } catch (error) {
    console.error('Export error:', error);
  }
};

const exportDailyReport = async () => {
  const { value: deliveryMethod } = await Swal.fire({
    title: 'Choose Delivery Method',
    text: 'How would you like to receive the daily report?',
    icon: 'question',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: 'Download',
    denyButtonText: 'WhatsApp',
    cancelButtonText: 'Cancel'
  });

  if (deliveryMethod === 'deny') {
    const { value: whatsappNumber } = await Swal.fire({
      title: 'Enter WhatsApp Number',
      input: 'text',
      inputLabel: 'WhatsApp Number (with country code)',
      inputPlaceholder: 'e.g., +254712345678',
      showCancelButton: true,
      inputValidator: (value) => {
        if (!value) {
          return 'Please enter a WhatsApp number';
        }
        if (!value.match(/^\+[1-9]\d{1,14}$/)) {
          return 'Please enter a valid WhatsApp number with country code';
        }
      }
    });

    if (whatsappNumber) {
      try {
        const response = await axios.get('/sales/daily-report.pdf', {
          params: {
            date: exportDate.value,
            branch_id: selectedBranch.value,
            whatsapp_number: whatsappNumber
          }
        });
        Swal.fire('Success', response.data.message, 'success');
      } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to send report', 'error');
      }
    }
  } else if (deliveryMethod === 'confirm') {
    router.visit('/sales/daily-report.pdf', {
      date: exportDate.value,
      branch_id: selectedBranch.value
    });
  }
};

const exportWeeklyReport = async () => {
  const { value: deliveryMethod } = await Swal.fire({
    title: 'Choose Delivery Method',
    text: 'How would you like to receive the weekly report?',
    icon: 'question',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: 'Download',
    denyButtonText: 'WhatsApp',
    cancelButtonText: 'Cancel'
  });

  if (deliveryMethod === 'deny') {
    const { value: whatsappNumber } = await Swal.fire({
      title: 'Enter WhatsApp Number',
      input: 'text',
      inputLabel: 'WhatsApp Number (with country code)',
      inputPlaceholder: 'e.g., +254712345678',
      showCancelButton: true,
      inputValidator: (value) => {
        if (!value) {
          return 'Please enter a WhatsApp number';
        }
        if (!value.match(/^\+[1-9]\d{1,14}$/)) {
          return 'Please enter a valid WhatsApp number with country code';
        }
      }
    });

    if (whatsappNumber) {
      try {
        const response = await axios.get('/sales/weekly-report.pdf', {
          params: {
            week: exportWeek.value,
            year: exportWeekYear.value,
            branch_id: selectedBranch.value,
            whatsapp_number: whatsappNumber
          }
        });
        Swal.fire('Success', response.data.message, 'success');
      } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to send report', 'error');
      }
    }
  } else if (deliveryMethod === 'confirm') {
    router.visit('/sales/weekly-report.pdf', {
      week: exportWeek.value,
      year: exportWeekYear.value,
      branch_id: selectedBranch.value
    });
  }
};

const exportMonthlyReport = async () => {
  const { value: deliveryMethod } = await Swal.fire({
    title: 'Choose Delivery Method',
    text: 'How would you like to receive the monthly report?',
    icon: 'question',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: 'Download',
    denyButtonText: 'WhatsApp',
    cancelButtonText: 'Cancel'
  });

  if (deliveryMethod === 'deny') {
    const { value: whatsappNumber } = await Swal.fire({
      title: 'Enter WhatsApp Number',
      input: 'text',
      inputLabel: 'WhatsApp Number (with country code)',
      inputPlaceholder: 'e.g., +254712345678',
      showCancelButton: true,
      inputValidator: (value) => {
        if (!value) {
          return 'Please enter a WhatsApp number';
        }
        if (!value.match(/^\+[1-9]\d{1,14}$/)) {
          return 'Please enter a valid WhatsApp number with country code';
        }
      }
    });

    if (whatsappNumber) {
      try {
        const response = await axios.get('/sales/monthly-report.pdf', {
          params: {
            month: exportMonth.value,
            year: exportMonthYear.value,
            branch_id: selectedBranch.value,
            whatsapp_number: whatsappNumber
          }
        });
        Swal.fire('Success', response.data.message, 'success');
      } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to send report', 'error');
      }
    }
  } else if (deliveryMethod === 'confirm') {
    router.visit('/sales/monthly-report.pdf', {
      month: exportMonth.value,
      year: exportMonthYear.value,
      branch_id: selectedBranch.value
    });
  }
};

const exportQuarterlyReport = () => {
  router.visit('/sales/quarterly-report.pdf', {
    year: exportQuarterYear.value,
    quarter: exportQuarter.value,
    branch_id: selectedBranch.value
  });
};

const exportYearlyReport = () => {
  router.visit('/sales/yearly-report.pdf', {
    year: exportYear.value,
    branch_id: selectedBranch.value
  });
};

watch([search, selectedBranch], () => {
  router.get(
    '/sales/receipt-history',
    {
      search: search.value,
      branch_id: selectedBranch.value
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true
    }
  );
});
</script>

<style scoped>
.receipt-preview {
  max-width: 80mm;
  margin: 0 auto;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 0.5rem;
}
</style>
