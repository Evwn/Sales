<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
  MapPin, 
  Building, 
  Users, 
  Package, 
  Phone, 
  Mail, 
  Calendar,
  Eye,
  ArrowLeft,
  User,
  Store,
  QrCode,
  Download,
  Edit,
  Settings,
  RefreshCw,
  Printer,
  TrendingUp,
  CheckCircle,
  XCircle,
  AlertCircle,
  Plus
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';
import QrcodeVue from 'qrcode.vue';

interface Branch {
  id: number;
  name: string;
  address: string;
  gps_latitude: number;
  gps_longitude: number;
  phone: string;
  email: string;
  status: string;
  barcode_path?: string;
  created_at: string;
  updated_at: string;
  business?: {
    id: number;
    name: string;
    owner: {
      id: number;
      name: string;
      email: string;
    };
  };
  sellers?: Array<{
    id: number;
    name: string;
    email: string;
    status: string;
  }>;
  products?: Array<{
    id: number;
    name: string;
    price: number;
    stock: number;
  }>;
}

interface Props {
  branch: Branch;
}

const props = defineProps<Props>();

// Get flash messages
const page = usePage();
const flash = computed(() => page.props.flash);

// Reactive data
const activeTab = ref('overview');
const branch = computed(() => props.branch);

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
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

const fetchBranch = async () => {
  try {
    if (!branch.value?.id) return;
    router.reload({ only: ['branch'] });
  } catch (error) {
    console.error('Error fetching branch:', error);
  }
};

const downloadBarcode = () => {
  if (branch.value?.barcode_path) {
    const link = document.createElement('a');
    link.href = `/storage/${branch.value.barcode_path}`;
    link.download = `${branch.value.name}-barcode.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

const printBarcode = () => {
  if (branch.value?.barcode_path) {
    const printWindow = window.open(`/storage/${branch.value.barcode_path}`, '_blank');
    if (printWindow) {
      printWindow.onload = () => {
        printWindow.print();
      };
    }
  }
};

const toggleBranchStatus = async () => {
  try {
    const newStatus = branch.value?.status === 'active' ? 'inactive' : 'active';
    const result = await Swal.fire({
      title: `Change Branch Status?`,
      text: `Do you want to change the status to ${newStatus}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: `Yes, change to ${newStatus}`,
      cancelButtonText: 'Cancel',
      confirmButtonColor: newStatus === 'active' ? '#10b981' : '#ef4444',
      cancelButtonColor: '#6b7280',
    });

    if (result.isConfirmed) {
      Swal.fire({
        title: 'Updating Status...',
        text: 'Please wait while we update the branch status',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      router.patch(`/businesses/${branch.value.business?.id}/branches/${branch.value.id}/toggle-status`, {}, {
        onSuccess: () => {
          fetchBranch();
          Swal.fire({
            icon: 'success',
            title: 'Status Updated!',
            text: `Branch status changed to ${newStatus}`,
            confirmButtonText: 'Great!',
            confirmButtonColor: '#10b981',
            timer: 3000,
            timerProgressBar: true,
          });
        },
        onError: () => {
          Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to connect to server. Please check your internet connection and try again.'
          });
        }
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Network Error',
      text: 'Failed to connect to server. Please check your internet connection and try again.'
    });
  }
};

const handleBarcodeImageError = () => {
  console.error('Failed to load barcode image');
};

const handleBarcodeImageLoad = () => {
  console.log('Barcode image loaded successfully');
};

const openAddSellerModal = async () => {
  const { value: formValues } = await Swal.fire({
    title: '<span style="font-size:1.25rem;font-weight:600;">Create New Seller</span>',
    html: `
      <div style="text-align:left;">
        <div style="margin-bottom:16px;">
          <label for="swal-input1" style="font-weight:500;display:block;margin-bottom:4px;">Name</label>
          <input id="swal-input1" class="swal2-input" placeholder="Enter full name" style="margin-bottom:0;">
        </div>
        <div style="margin-bottom:16px;">
          <label for="swal-input2" style="font-weight:500;display:block;margin-bottom:4px;">Email</label>
          <input id="swal-input2" class="swal2-input" placeholder="Enter email address" type="email" style="margin-bottom:0;">
        </div>
        <div style="margin-bottom:16px; position:relative;">
          <label for="swal-input3" style="font-weight:500;display:block;margin-bottom:4px;">Password</label>
          <input id="swal-input3" class="swal2-input" placeholder="Enter strong password" type="password" style="margin-bottom:0; padding-right:36px;">
          <span id="toggle-password" style="position:absolute; right:12px; top:38px; cursor:pointer;">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </span>
          <div style="font-size:12px;color:#2563eb;background:#eff6ff;border-radius:6px;padding:6px 10px;margin-top:6px;">
            <b>Password must:</b>
            <ul style="margin:4px 0 0 18px;padding:0;font-size:11px;">
              <li>Be at least 8 characters</li>
              <li>Include uppercase, lowercase, number, and symbol</li>
            </ul>
          </div>
        </div>
        <div style="margin-bottom:8px; position:relative;">
          <label for="swal-input4" style="font-weight:500;display:block;margin-bottom:4px;">Confirm Password</label>
          <input id="swal-input4" class="swal2-input" placeholder="Re-enter password" type="password" style="margin-bottom:0; padding-right:36px;">
          <span id="toggle-password-confirm" style="position:absolute; right:12px; top:38px; cursor:pointer;">
            <svg id="eye-icon-confirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </span>
        </div>
      </div>
    `,
    width: 420,
    showCancelButton: true,
    confirmButtonText: '<span style="font-weight:600;">Create Seller</span>',
    cancelButtonText: 'Cancel',
    focusConfirm: false,
    customClass: {
      popup: 'swal2-border-radius',
      confirmButton: 'bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md',
      cancelButton: 'bg-gray-200 text-gray-700 px-4 py-2 rounded-md'
    },
    didOpen: () => {
      // Password field toggle
      const pwd = document.getElementById('swal-input3');
      const eye = document.getElementById('toggle-password');
      eye.addEventListener('click', () => {
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
      });
      // Confirm password field toggle
      const pwdConfirm = document.getElementById('swal-input4');
      const eyeConfirm = document.getElementById('toggle-password-confirm');
      eyeConfirm.addEventListener('click', () => {
        pwdConfirm.type = pwdConfirm.type === 'password' ? 'text' : 'password';
      });
    },
    preConfirm: () => {
      const name = (document.getElementById('swal-input1') as HTMLInputElement).value;
      const email = (document.getElementById('swal-input2') as HTMLInputElement).value;
      const password = (document.getElementById('swal-input3') as HTMLInputElement).value;
      const password_confirmation = (document.getElementById('swal-input4') as HTMLInputElement).value;
      // Password strength check
      const errors = [];
      if (password.length < 8) errors.push('Password must be at least 8 characters.');
      if (!/[A-Z]/.test(password)) errors.push('Password must contain at least 1 uppercase letter.');
      if (!/[a-z]/.test(password)) errors.push('Password must contain at least 1 lowercase letter.');
      if (!/[0-9]/.test(password)) errors.push('Password must contain at least 1 number.');
      if (!/[^A-Za-z0-9]/.test(password)) errors.push('Password must contain at least 1 symbol.');
      if (password !== password_confirmation) errors.push('Passwords do not match.');
      if (!name || !email || !password || !password_confirmation) errors.push('All fields are required.');
      if (errors.length) {
        Swal.showValidationMessage(errors.join('<br>'));
        return false;
      }
      return { name, email, password, password_confirmation };
    }
  });

  if (formValues) {
    try {
      // Show loading SweetAlert
      Swal.fire({
        title: 'Creating Seller...',
        allowOutsideClick: false,
        backdrop: 'rgba(0,0,0,0.4)',
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      await router.post(
        `/businesses/${branch.value.business.id}/branches/${branch.value.id}/sellers`,
        formValues,
        {
          onSuccess: () => {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: 'Seller created successfully.',
              timer: 2000,
              showConfirmButton: false,
              backdrop: 'rgba(0,0,0,0.4)'
            });
            fetchBranch();
          },
          onError: (errors) => {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: Object.values(errors).join('\n'),
              confirmButtonText: 'OK',
              backdrop: 'rgba(0,0,0,0.4)'
            });
          }
        }
      );
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'An unexpected error occurred.',
        confirmButtonText: 'OK',
        backdrop: 'rgba(0,0,0,0.4)'
      });
    }
  }
};

function confirmDeleteBranch() {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This will delete the branch. This cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Deleting...',
        text: 'Please wait while we delete the branch.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      window.axios.delete(`/businesses/${branch.value.business.id}/branches/${branch.value.id}`, {
        headers: { 'Accept': 'application/json' }
      })
        .then((response) => {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: response.data.message || 'Branch deleted.',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '/admin/branches';
          });
        })
        .catch((error) => {
          let msg = 'Failed to delete branch. Please try again.';
          if (error.response && error.response.data && error.response.data.message) {
            msg = error.response.data.message;
          }
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: msg,
            confirmButtonText: 'OK'
          });
        });
    }
  });
}
</script>

<template>
  <AppLayout>
    <Head :title="`Admin - ${branch.name}`" />

    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center">
          <Link
            href="/admin/branches"
            class="mr-4 p-2 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ branch.name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
              Branch Details & Management
            </p>
          </div>
        </div>
        <button
          @click="confirmDeleteBranch"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
        >
          Delete Branch
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Branch Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                  <Store class="h-8 w-8 text-gray-500" />
                </div>
                <div class="ml-6">
                  <div class="flex items-center space-x-4">
                    <div>
                      <qrcode-vue :value="branch.barcode_path" :size="64" class="mx-auto" />
                    </div>
                    <div>
                      <h2 class="text-2xl font-bold leading-tight text-gray-900">{{ branch.name }}</h2>
                      <div class="text-sm text-gray-500">{{ branch.address }}</div>
                    </div>
                  </div>
                  <span :class="`inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-2 ${getStatusColor(typeof branch.status === 'string' && branch.status ? branch.status.charAt(0).toUpperCase() + branch.status.slice(1) : 'Unknown')}`">
                    {{ typeof branch.status === 'string' && branch.status ? branch.status.charAt(0).toUpperCase() + branch.status.slice(1) : 'Unknown' }}
                  </span>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <!-- Action Buttons -->
                <div class="flex space-x-2">
                  <button
                    @click="toggleBranchStatus"
                    :class="`inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ${
                      typeof branch.status === 'string' && branch.status === 'active' 
                        ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' 
                        : 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
                    }`"
                    :title="`${typeof branch.status === 'string' && branch.status === 'active' ? 'Deactivate' : 'Activate'} Branch`"
                  >
                    <Settings class="h-4 w-4 mr-2" />
                    {{ typeof branch.status === 'string' && branch.status === 'active' ? 'Deactivate' : 'Activate' }}
                  </button>
                  
                  <Link
                    :href="`/businesses/${branch.business?.id}/branches/${branch.id}/edit`"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    title="Edit Branch"
                  >
                    <Edit class="h-4 w-4 mr-2" />
                    Edit
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6">
              <button
                @click="activeTab = 'overview'"
                :class="[
                  activeTab === 'overview'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Overview
              </button>
              <button
                @click="activeTab = 'business'"
                :class="[
                  activeTab === 'business'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Business
              </button>
              <button
                @click="activeTab = 'sellers'"
                :class="[
                  activeTab === 'sellers'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Sellers ({{ branch.sellers.length }})
              </button>
              <button
                @click="activeTab = 'products'"
                :class="[
                  activeTab === 'products'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Products ({{ branch.products.length }})
              </button>
              <button
                @click="activeTab = 'barcode'"
                :class="[
                  activeTab === 'barcode'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Barcode
              </button>
            </nav>
          </div>

          <!-- Tab Content -->
          <div class="p-6">
            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contact Information -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                  <div class="space-y-3">
                    <div class="flex items-center">
                      <Phone class="h-4 w-4 text-gray-400 mr-3" />
                      <span class="text-gray-900">{{ branch.phone }}</span>
                    </div>
                    <div class="flex items-center">
                      <Mail class="h-4 w-4 text-gray-400 mr-3" />
                      <span class="text-gray-900">{{ branch.email }}</span>
                    </div>
                  </div>
                </div>

                <!-- Location -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
                  <div class="space-y-4">
                    <div class="flex items-start">
                      <MapPin class="h-4 w-4 text-gray-400 mr-3 mt-0.5" />
                      <div>
                        <div class="text-gray-900">{{ branch.address }}</div>
                        <div v-if="branch.gps_latitude && branch.gps_longitude" class="text-gray-600 mt-1">
                          GPS: {{ Number(branch.gps_latitude).toFixed(6) }}, {{ Number(branch.gps_longitude).toFixed(6) }}
                        </div>
                      </div>
                    </div>
                    
                    <!-- Interactive Map -->
                    <div v-if="branch.gps_latitude && branch.gps_longitude" class="mt-4">
                      <div class="bg-gray-100 rounded-lg overflow-hidden" style="height: 300px;">
                        <iframe
                          :src="`https://www.openstreetmap.org/export/embed.html?bbox=${Number(branch.gps_longitude) - 0.01},${Number(branch.gps_latitude) - 0.01},${Number(branch.gps_longitude) + 0.01},${Number(branch.gps_latitude) + 0.01}&layer=mapnik&marker=${Number(branch.gps_latitude)},${Number(branch.gps_longitude)}`"
                          width="100%"
                          height="100%"
                          frameborder="0"
                          scrolling="no"
                          marginheight="0"
                          marginwidth="0"
                          title="Branch Location"
                          class="w-full h-full"
                        ></iframe>
                      </div>
                      <div class="mt-2 text-xs text-gray-500 text-center">
                        <a 
                          :href="`https://www.openstreetmap.org/?mlat=${Number(branch.gps_latitude)}&mlon=${Number(branch.gps_longitude)}&zoom=15`"
                          target="_blank"
                          class="text-blue-600 hover:text-blue-800 underline"
                        >
                          View larger map
                        </a>
                      </div>
                    </div>
                    
                    <div v-else class="mt-4 text-center py-8 bg-gray-50 rounded-lg">
                      <MapPin class="mx-auto h-8 w-8 text-gray-400 mb-2" />
                      <p class="text-sm text-gray-500">No GPS coordinates available</p>
                    </div>
                  </div>
                </div>

                <!-- Branch Details -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Details</h3>
                  <div class="space-y-3">
                    <div>
                      <span class="text-sm font-medium text-gray-500">Status:</span>
                      <span :class="`ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(typeof branch.status === 'string' && branch.status ? branch.status.charAt(0).toUpperCase() + branch.status.slice(1) : 'Unknown')}`">
                        {{ typeof branch.status === 'string' && branch.status ? branch.status.charAt(0).toUpperCase() + branch.status.slice(1) : 'Unknown' }}
                      </span>
                    </div>
                    <div>
                      <span class="text-sm font-medium text-gray-500">Created:</span>
                      <span class="ml-2 text-gray-900">{{ formatDate(branch.created_at) }}</span>
                    </div>
                    <div>
                      <span class="text-sm font-medium text-gray-500">Last Updated:</span>
                      <span class="ml-2 text-gray-900">{{ formatDate(branch.updated_at) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Statistics -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                      <Users class="h-8 w-8 text-blue-600 mx-auto mb-2" />
                      <div class="text-2xl font-bold text-gray-900">{{ branch.sellers.length }}</div>
                      <div class="text-sm text-gray-500">Sellers</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                      <Package class="h-8 w-8 text-green-600 mx-auto mb-2" />
                      <div class="text-2xl font-bold text-gray-900">{{ branch.products.length }}</div>
                      <div class="text-sm text-gray-500">Products</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Business Tab -->
            <div v-if="activeTab === 'business'">
              <div v-if="branch.business">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Business Information -->
                  <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>
                    <div class="space-y-3">
                      <div>
                        <span class="text-sm font-medium text-gray-500">Name:</span>
                        <span class="ml-2 text-gray-900">{{ branch.business.name }}</span>
                      </div>
                      <div v-if="branch.business.description">
                        <span class="text-sm font-medium text-gray-500">Description:</span>
                        <span class="ml-2 text-gray-900">{{ branch.business.description }}</span>
                      </div>
                      <div class="flex items-center">
                        <Phone class="h-4 w-4 text-gray-400 mr-3" />
                        <span class="text-gray-900">{{ branch.business.phone }}</span>
                      </div>
                      <div class="flex items-center">
                        <Mail class="h-4 w-4 text-gray-400 mr-3" />
                        <span class="text-gray-900">{{ branch.business.email }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Business Address -->
                  <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Business Address</h3>
                    <div class="space-y-1">
                      <div class="flex items-start">
                        <MapPin class="h-4 w-4 text-gray-400 mr-3 mt-0.5" />
                        <div>
                          <div class="text-gray-900">{{ branch.business.address }}</div>
                          <div class="text-gray-600">{{ branch.business.city }}, {{ branch.business.state }}</div>
                          <div class="text-gray-600">{{ branch.business.country }}</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Owner Information -->
                  <div v-if="branch.business.owner">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Owner</h3>
                    <div class="space-y-3">
                      <div class="flex items-center">
                        <User class="h-4 w-4 text-gray-400 mr-3" />
                        <div>
                          <div class="text-gray-900">{{ branch.business.owner.name }}</div>
                          <div class="text-gray-600">{{ branch.business.owner.email }}</div>
                        </div>
                      </div>
                      <div>
                        <span class="text-sm font-medium text-gray-500">Owner Since:</span>
                        <span class="ml-2 text-gray-900">{{ formatDate(branch.business.owner.created_at) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Business Admins -->
                  <div v-if="branch.business.admins.length > 0">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Business Admins</h3>
                    <div class="space-y-2">
                      <div v-for="admin in branch.business.admins" :key="admin.id" class="flex items-center">
                        <User class="h-4 w-4 text-gray-400 mr-3" />
                        <div>
                          <div class="text-gray-900">{{ admin.name }}</div>
                          <div class="text-gray-600">{{ admin.email }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Building class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No business assigned</h3>
                <p class="mt-1 text-sm text-gray-500">This branch is not assigned to any business.</p>
              </div>
            </div>

            <!-- Sellers Tab -->
            <div v-if="activeTab === 'sellers'">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Sellers</h3>
                <button
                  @click="openAddSellerModal"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none"
                  title="Add Seller"
                >
                  <Plus class="h-4 w-4 mr-1" />
                  Add Seller
                </button>
              </div>
              <div v-if="branch.sellers.length > 0">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Seller
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Joined
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="seller in branch.sellers" :key="seller.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900">{{ seller.name }}</div>
                          <div class="text-sm text-gray-500">{{ seller.email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900">{{ seller.phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(typeof seller.status === 'string' && seller.status ? seller.status.charAt(0).toUpperCase() + seller.status.slice(1) : 'Unknown')}`">
                            {{ typeof seller.status === 'string' && seller.status ? seller.status.charAt(0).toUpperCase() + seller.status.slice(1) : 'Unknown' }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ formatDate(seller.created_at) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Users class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No sellers</h3>
                <p class="mt-1 text-sm text-gray-500">This branch has no sellers assigned.</p>
              </div>
            </div>

            <!-- Products Tab -->
            <div v-if="activeTab === 'products'">
              <div v-if="branch.products.length > 0">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Added
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="product in branch.products" :key="product.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                          <div v-if="product.description" class="text-sm text-gray-500">{{ product.description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900">{{ formatCurrency(product.price) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900">{{ product.stock_quantity }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(typeof product.status === 'string' && product.status ? product.status.charAt(0).toUpperCase() + product.status.slice(1) : 'Unknown')}`">
                            {{ typeof product.status === 'string' && product.status ? product.status.charAt(0).toUpperCase() + product.status.slice(1) : 'Unknown' }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ formatDate(product.created_at) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Package class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
                <p class="mt-1 text-sm text-gray-500">This branch has no products.</p>
              </div>
            </div>

            <!-- Barcode Tab -->
            <div v-if="activeTab === 'barcode'" class="p-6">
              <div class="max-w-2xl mx-auto">
                <div v-if="branch.barcode_path" class="text-center">
                  <h3 class="text-lg font-medium text-gray-900 mb-6">Branch Barcode</h3>
                  <div class="bg-white p-8 rounded-lg border-2 border-gray-200">
                    <qrcode-vue :value="branch.barcode_path" :size="200" class="mx-auto" />
                    <div class="mt-4 text-sm text-gray-600">{{ branch.name }}</div>
                    <div class="mt-2 text-xs text-gray-500">{{ branch.address }}</div>
                    <div class="mt-2 text-xs text-gray-400">Barcode: {{ branch.barcode_path }}</div>
                  </div>
                </div>
                
                <div v-else class="text-center py-12">
                  <QrCode class="mx-auto h-12 w-12 text-gray-400" />
                  <h3 class="mt-2 text-sm font-medium text-gray-900">No barcode generated</h3>
                  <p class="mt-1 text-sm text-gray-500">This branch doesn't have a barcode yet.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>