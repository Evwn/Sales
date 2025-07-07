<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
  Building, 
  MapPin, 
  Users, 
  Phone, 
  Mail, 
  Globe, 
  Calendar,
  FileText,
  Download,
  Eye,
  ArrowLeft,
  ExternalLink,
  User,
  Package,
  Store,
  Edit,
  Upload,
  Save,
  X,
  Plus
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

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
    created_at: string;
  } | null;
  branches: {
    id: number;
    name: string;
    address: string;
    phone: string;
    email: string;
    sellers_count: number;
    products_count: number;
    created_at: string;
  }[];
  admins: {
    id: number;
    name: string;
    email: string;
    created_at: string;
  }[];
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  business: Business;
}>();

// Get flash messages
const page = usePage();
const flash = computed(() => page.props.flash);

// Reactive data
const activeTab = ref('overview');
const showDocumentModal = ref(false);
const editingDocument = ref<string | null>(null);
const documentTitle = ref('');
const documentDescription = ref('');

// Document upload form
const documentForm = useForm({
  tax_document: null as File | null,
  registration_document: null as File | null,
  terms_and_conditions: null as File | null,
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

const downloadDocument = (path: string, filename: string) => {
  const link = document.createElement('a');
  link.href = `/storage/${path}`;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const openDocument = (path: string) => {
  window.open(`/storage/${path}`, '_blank');
};

const openDocumentModal = (documentType: string) => {
  editingDocument.value = documentType;
  showDocumentModal.value = true;
  
  switch (documentType) {
    case 'tax_document':
      documentTitle.value = 'Tax Document';
      documentDescription.value = 'Upload or update the business tax document';
      break;
    case 'registration_document':
      documentTitle.value = 'Registration Document';
      documentDescription.value = 'Upload or update the business registration certificate';
      break;
    case 'terms_and_conditions':
      documentTitle.value = 'Terms & Conditions';
      documentDescription.value = 'Upload or update the business terms and conditions';
      break;
  }
};

const closeDocumentModal = () => {
  showDocumentModal.value = false;
  editingDocument.value = null;
  documentForm.reset();
};

const handleDocumentUpload = async () => {
  if (!editingDocument.value) return;

  try {
    const formData = new FormData();
    const file = documentForm[editingDocument.value as keyof typeof documentForm] as File;
    
    if (!file) {
      Swal.fire({
        icon: 'error',
        title: 'No File Selected',
        text: 'Please select a file to upload',
        confirmButtonText: 'OK',
        confirmButtonColor: '#ef4444',
      });
      return;
    }

    // Show loading state
    Swal.fire({
      title: 'Uploading Document...',
      text: 'Please wait while we upload your document',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    formData.append('document_type', editingDocument.value);
    formData.append('file', file);

    const response = await fetch(`/admin/businesses/${props.business.id}/documents/upload`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: formData,
    });

    const data = await response.json();

    if (response.ok) {
      Swal.fire({
        icon: 'success',
        title: 'Document Uploaded!',
        text: data.message || 'Document uploaded successfully',
        confirmButtonText: 'Great!',
        confirmButtonColor: '#10b981',
        timer: 3000,
        timerProgressBar: true,
      }).then(() => {
        // Refresh the page to show updated documents
        window.location.reload();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Upload Failed',
        text: data.message || 'Failed to upload document. Please try again.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#ef4444',
      });
    }
  } catch (error) {
    console.error('Error uploading document:', error);
    Swal.fire({
      icon: 'error',
      title: 'Network Error',
      text: 'Failed to connect to server. Please check your internet connection and try again.',
      confirmButtonText: 'OK',
      confirmButtonColor: '#ef4444',
    });
  }
  
  closeDocumentModal();
};

const deleteDocument = async (documentType: string) => {
  try {
    const result = await Swal.fire({
      title: 'Delete Document?',
      text: 'Are you sure you want to delete this document? This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
    });

    if (result.isConfirmed) {
      // Show loading state
      Swal.fire({
        title: 'Deleting Document...',
        text: 'Please wait while we delete the document',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const response = await fetch(`/admin/businesses/${props.business.id}/documents/${documentType}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      const data = await response.json();

      if (response.ok) {
        Swal.fire({
          icon: 'success',
          title: 'Document Deleted!',
          text: data.message || 'Document deleted successfully',
          confirmButtonText: 'Great!',
          confirmButtonColor: '#10b981',
          timer: 3000,
          timerProgressBar: true,
        }).then(() => {
          // Refresh the page to show updated documents
          window.location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Deletion Failed',
          text: data.message || 'Failed to delete document. Please try again.',
          confirmButtonText: 'OK',
          confirmButtonColor: '#ef4444',
        });
      }
    }
  } catch (error) {
    console.error('Error deleting document:', error);
    Swal.fire({
      icon: 'error',
      title: 'Network Error',
      text: 'Failed to connect to server. Please check your internet connection and try again.',
      confirmButtonText: 'OK',
      confirmButtonColor: '#ef4444',
    });
  }
};

const getDocumentIcon = (documentType: string) => {
  switch (documentType) {
    case 'tax_document':
      return 'text-red-500';
    case 'registration_document':
      return 'text-blue-500';
    case 'terms_and_conditions':
      return 'text-purple-500';
    default:
      return 'text-gray-500';
  }
};

const getDocumentName = (documentType: string) => {
  switch (documentType) {
    case 'tax_document':
      return 'Tax Document';
    case 'registration_document':
      return 'Registration Document';
    case 'terms_and_conditions':
      return 'Terms & Conditions';
    default:
      return 'Document';
  }
};

function confirmDeleteBusiness() {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This will delete the business and all its branches. This cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Deleting...',
        text: 'Please wait while we delete the business.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      window.axios.delete(`/businesses/${props.business.id}`, {
        headers: { 'Accept': 'application/json' }
      })
        .then((response) => {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: response.data.message || 'Business and all its branches deleted.',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '/admin/businesses';
          });
        })
        .catch((error) => {
          let msg = 'Failed to delete business. Please try again.';
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
    <Head :title="`Admin - ${business.name}`" />

    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center">
          <Link
            href="/admin/businesses"
            class="mr-4 p-2 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ business.name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
              Business Details & Management
            </p>
          </div>
        </div>
        <div class="flex gap-2">
          <Link
            :href="`/admin/businesses/${business.id}/edit`"
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition"
          >
            <Edit class="h-4 w-4 inline mr-1" /> Edit
          </Link>
          <button
            @click="confirmDeleteBusiness"
            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
          >
            Delete Business
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Business Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex items-center">
              <div v-if="business.logo_path" class="flex-shrink-0 h-16 w-16">
                <img :src="`/storage/${business.logo_path}`" :alt="business.name" class="h-16 w-16 rounded-full object-cover" />
              </div>
              <div v-else class="flex-shrink-0 h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                <Building class="h-8 w-8 text-gray-500" />
              </div>
              <div class="ml-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ business.name }}</h1>
                <p v-if="business.industry" class="text-lg text-gray-600">{{ business.industry }}</p>
                <p v-if="business.description" class="text-gray-500 mt-1">{{ business.description }}</p>
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
                @click="activeTab = 'branches'"
                :class="[
                  activeTab === 'branches'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Branches ({{ business.branches.length }})
              </button>
              <button
                @click="activeTab = 'admins'"
                :class="[
                  activeTab === 'admins'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Admins ({{ business.admins.length }})
              </button>
              <button
                @click="activeTab = 'documents'"
                :class="[
                  activeTab === 'documents'
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Documents
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
                      <span class="text-gray-900">{{ business.phone }}</span>
                    </div>
                    <div class="flex items-center">
                      <Mail class="h-4 w-4 text-gray-400 mr-3" />
                      <span class="text-gray-900">{{ business.email }}</span>
                    </div>
                    <div v-if="business.website" class="flex items-center">
                      <Globe class="h-4 w-4 text-gray-400 mr-3" />
                      <a :href="business.website" target="_blank" class="text-blue-600 hover:underline">
                        {{ business.website }}
                        <ExternalLink class="h-3 w-3 inline ml-1" />
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Address -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Address</h3>
                  <div class="space-y-1">
                    <div class="flex items-start">
                      <MapPin class="h-4 w-4 text-gray-400 mr-3 mt-0.5" />
                      <div>
                        <div class="text-gray-900">{{ business.address }}</div>
                        <div class="text-gray-600">{{ business.city }}, {{ business.state }} {{ business.postal_code }}</div>
                        <div class="text-gray-600">{{ business.country }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Business Details -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Business Details</h3>
                  <div class="space-y-3">
                    <div v-if="business.tax_number">
                      <span class="text-sm font-medium text-gray-500">Tax Number:</span>
                      <span class="ml-2 text-gray-900">{{ business.tax_number }}</span>
                    </div>
                    <div v-if="business.registration_number">
                      <span class="text-sm font-medium text-gray-500">Registration Number:</span>
                      <span class="ml-2 text-gray-900">{{ business.registration_number }}</span>
                    </div>
                    <div>
                      <span class="text-sm font-medium text-gray-500">Created:</span>
                      <span class="ml-2 text-gray-900">{{ formatDate(business.created_at) }}</span>
                    </div>
                    <div>
                      <span class="text-sm font-medium text-gray-500">Last Updated:</span>
                      <span class="ml-2 text-gray-900">{{ formatDate(business.updated_at) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Owner Information -->
                <div v-if="business.owner">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Owner</h3>
                  <div class="space-y-3">
                    <div class="flex items-center">
                      <User class="h-4 w-4 text-gray-400 mr-3" />
                      <div>
                        <div class="text-gray-900">{{ business.owner.name }}</div>
                        <div class="text-gray-600">{{ business.owner.email }}</div>
                      </div>
                    </div>
                    <div>
                      <span class="text-sm font-medium text-gray-500">Owner Since:</span>
                      <span class="ml-2 text-gray-900">{{ formatDate(business.owner.created_at) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Branches Tab -->
            <div v-if="activeTab === 'branches'">
              <div v-if="business.branches.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div
                    v-for="branch in business.branches"
                    :key="branch.id"
                    class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors"
                  >
                    <div class="flex items-center justify-between mb-4">
                      <h4 class="text-lg font-medium text-gray-900">{{ branch.name }}</h4>
                      <Link
                        :href="`/admin/branches/${branch.id}`"
                        class="text-indigo-600 hover:text-indigo-800"
                      >
                        <Eye class="h-4 w-4" />
                      </Link>
                    </div>
                    <div class="space-y-2">
                      <div class="flex items-center text-sm text-gray-600">
                        <MapPin class="h-4 w-4 mr-2" />
                        {{ branch.address }}
                      </div>
                      <div class="flex items-center text-sm text-gray-600">
                        <Phone class="h-4 w-4 mr-2" />
                        {{ branch.phone }}
                      </div>
                      <div class="flex items-center text-sm text-gray-600">
                        <Mail class="h-4 w-4 mr-2" />
                        {{ branch.email }}
                      </div>
                      <div class="flex justify-between text-sm text-gray-500 mt-4">
                        <span>{{ branch.sellers_count }} sellers</span>
                        <span>{{ branch.products_count }} products</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Store class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No branches</h3>
                <p class="mt-1 text-sm text-gray-500">This business has no branches yet.</p>
              </div>
            </div>

            <!-- Admins Tab -->
            <div v-if="activeTab === 'admins'">
              <div v-if="business.admins.length > 0">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Admin
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Added
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="admin in business.admins" :key="admin.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900">{{ admin.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-500">{{ admin.email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ formatDate(admin.created_at) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Users class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No admins</h3>
                <p class="mt-1 text-sm text-gray-500">This business has no additional admins.</p>
              </div>
            </div>

            <!-- Documents Tab -->
            <div v-if="activeTab === 'documents'" class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tax Document -->
                <div v-if="business.tax_document_path" class="bg-gray-50 rounded-lg p-6">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <FileText class="h-8 w-8 text-red-500 mr-3" />
                      <div>
                        <h4 class="text-lg font-medium text-gray-900">Tax Document</h4>
                        <p class="text-sm text-gray-500">Business tax registration</p>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="openDocument(business.tax_document_path!)"
                        class="p-2 text-blue-600 hover:text-blue-800"
                        title="View Document"
                      >
                        <Eye class="h-4 w-4" />
                      </button>
                      <button
                        @click="downloadDocument(business.tax_document_path!, 'tax-document.pdf')"
                        class="p-2 text-green-600 hover:text-green-800"
                        title="Download Document"
                      >
                        <Download class="h-4 w-4" />
                      </button>
                      <button
                        @click="openDocumentModal('tax_document')"
                        class="p-2 text-yellow-600 hover:text-yellow-800"
                        title="Edit Document"
                      >
                        <Edit class="h-4 w-4" />
                      </button>
                      <button
                        @click="deleteDocument('tax_document')"
                        class="p-2 text-red-600 hover:text-red-800"
                        title="Delete Document"
                      >
                        <X class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Registration Document -->
                <div v-if="business.registration_document_path" class="bg-gray-50 rounded-lg p-6">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <FileText class="h-8 w-8 text-blue-500 mr-3" />
                      <div>
                        <h4 class="text-lg font-medium text-gray-900">Registration Document</h4>
                        <p class="text-sm text-gray-500">Business registration certificate</p>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="openDocument(business.registration_document_path!)"
                        class="p-2 text-blue-600 hover:text-blue-800"
                        title="View Document"
                      >
                        <Eye class="h-4 w-4" />
                      </button>
                      <button
                        @click="downloadDocument(business.registration_document_path!, 'registration-document.pdf')"
                        class="p-2 text-green-600 hover:text-green-800"
                        title="Download Document"
                      >
                        <Download class="h-4 w-4" />
                      </button>
                      <button
                        @click="openDocumentModal('registration_document')"
                        class="p-2 text-yellow-600 hover:text-yellow-800"
                        title="Edit Document"
                      >
                        <Edit class="h-4 w-4" />
                      </button>
                      <button
                        @click="deleteDocument('registration_document')"
                        class="p-2 text-red-600 hover:text-red-800"
                        title="Delete Document"
                      >
                        <X class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Terms and Conditions -->
                <div v-if="business.terms_and_conditions" class="bg-gray-50 rounded-lg p-6">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <FileText class="h-8 w-8 text-purple-500 mr-3" />
                      <div>
                        <h4 class="text-lg font-medium text-gray-900">Terms & Conditions</h4>
                        <p class="text-sm text-gray-500">Business terms and conditions</p>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="openDocument(business.terms_and_conditions!)"
                        class="p-2 text-blue-600 hover:text-blue-800"
                        title="View Document"
                      >
                        <Eye class="h-4 w-4" />
                      </button>
                      <button
                        @click="downloadDocument(business.terms_and_conditions!, 'terms-conditions.pdf')"
                        class="p-2 text-green-600 hover:text-green-800"
                        title="Download Document"
                      >
                        <Download class="h-4 w-4" />
                      </button>
                      <button
                        @click="openDocumentModal('terms_and_conditions')"
                        class="p-2 text-yellow-600 hover:text-yellow-800"
                        title="Edit Document"
                      >
                        <Edit class="h-4 w-4" />
                      </button>
                      <button
                        @click="deleteDocument('terms_and_conditions')"
                        class="p-2 text-red-600 hover:text-red-800"
                        title="Delete Document"
                      >
                        <X class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Add New Document Cards -->
                <div v-if="!business.tax_document_path" class="bg-gray-50 rounded-lg p-6 border-2 border-dashed border-gray-300">
                  <div class="text-center">
                    <FileText class="mx-auto h-8 w-8 text-gray-400 mb-3" />
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tax Document</h4>
                    <p class="text-sm text-gray-500 mb-4">Upload business tax registration</p>
                    <button
                      @click="openDocumentModal('tax_document')"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                      <Plus class="h-4 w-4 mr-2" />
                      Upload Document
                    </button>
                  </div>
                </div>

                <div v-if="!business.registration_document_path" class="bg-gray-50 rounded-lg p-6 border-2 border-dashed border-gray-300">
                  <div class="text-center">
                    <FileText class="mx-auto h-8 w-8 text-gray-400 mb-3" />
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Registration Document</h4>
                    <p class="text-sm text-gray-500 mb-4">Upload business registration certificate</p>
                    <button
                      @click="openDocumentModal('registration_document')"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      <Plus class="h-4 w-4 mr-2" />
                      Upload Document
                    </button>
                  </div>
                </div>

                <div v-if="!business.terms_and_conditions" class="bg-gray-50 rounded-lg p-6 border-2 border-dashed border-gray-300">
                  <div class="text-center">
                    <FileText class="mx-auto h-8 w-8 text-gray-400 mb-3" />
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Terms & Conditions</h4>
                    <p class="text-sm text-gray-500 mb-4">Upload business terms and conditions</p>
                    <button
                      @click="openDocumentModal('terms_and_conditions')"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                    >
                      <Plus class="h-4 w-4 mr-2" />
                      Upload Document
                    </button>
                  </div>
                </div>

                <!-- No Documents -->
                <div v-if="!business.tax_document_path && !business.registration_document_path && !business.terms_and_conditions" class="col-span-2 text-center py-12">
                  <FileText class="mx-auto h-12 w-12 text-gray-400" />
                  <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                  <p class="mt-1 text-sm text-gray-500">This business has no uploaded documents.</p>
                  <div class="mt-6 flex justify-center space-x-4">
                    <button
                      @click="openDocumentModal('tax_document')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                    >
                      <Plus class="h-4 w-4 mr-2" />
                      Add Tax Document
                    </button>
                    <button
                      @click="openDocumentModal('registration_document')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                      <Plus class="h-4 w-4 mr-2" />
                      Add Registration
                    </button>
                    <button
                      @click="openDocumentModal('terms_and_conditions')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700"
                    >
                      <Plus class="h-4 w-4 mr-2" />
                      Add Terms
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Upload Modal -->
    <div v-if="showDocumentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ documentTitle }}</h3>
            <button @click="closeDocumentModal" class="text-gray-400 hover:text-gray-600">
              <X class="h-6 w-6" />
            </button>
          </div>
          
          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-600 mb-4">{{ documentDescription }}</p>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
              <input
                type="file"
                @change="(e) => {
                  const target = e.target as HTMLInputElement;
                  if (target.files && target.files[0]) {
                    documentForm[editingDocument as keyof typeof documentForm] = target.files[0];
                  }
                }"
                accept=".pdf,.doc,.docx"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              />
              <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX (Max 5MB)</p>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button
              type="button"
              @click="closeDocumentModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="handleDocumentUpload"
              :disabled="!documentForm[editingDocument as keyof typeof documentForm]"
              class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
            >
              <Upload class="h-4 w-4 mr-2 inline" />
              Upload Document
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 