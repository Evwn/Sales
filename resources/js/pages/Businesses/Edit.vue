<template>
  <AppLayout title="Edit Business">
    <PageHeader title="Edit Business">
      <template #actions>
        <Link
          href="/businesses"
          class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
          Back to Businesses
        </Link>
      </template>
    </PageHeader>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Stepper -->
            <div class="mb-8">
              <div class="flex items-center justify-between">
                <div v-for="(step, index) in steps" :key="index" class="flex items-center">
                  <div
                    :class="[
                      'flex items-center justify-center w-8 h-8 rounded-full',
                      currentStep >= index
                        ? 'bg-primary-600 text-white'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                    ]"
                  >
                    {{ index + 1 }}
                  </div>
                  <div
                    :class="[
                      'ml-2 text-sm font-medium',
                      currentStep >= index
                        ? 'text-primary-600 dark:text-primary-400'
                        : 'text-gray-500 dark:text-gray-400'
                    ]"
                  >
                    {{ step }}
                  </div>
                  <div
                    v-if="index < steps.length - 1"
                    :class="[
                      'w-full h-0.5 mx-4',
                      currentStep > index
                        ? 'bg-primary-600'
                        : 'bg-gray-200 dark:bg-gray-700'
                    ]"
                  ></div>
                </div>
              </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6" enctype="multipart/form-data">
              <!-- Step 1: Basic Information -->
              <div v-show="currentStep === 0">
                <div class="space-y-6">
                  <div>
                    <InputLabel for="name" value="Business Name" />
                    <TextInput
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="mt-1 block w-full"
                      required
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="description" value="Description" />
                    <TextArea
                      id="description"
                      v-model="form.description"
                      class="mt-1 block w-full"
                      rows="4"
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                  </div>
                </div>
              </div>

              <!-- Step 2: Contact Information -->
              <div v-show="currentStep === 1">
                <div class="space-y-6">
                  <div>
                    <InputLabel for="phone" value="Phone Number" />
                    <TextInput
                      id="phone"
                      v-model="form.phone"
                      type="tel"
                      class="mt-1 block w-full"
                      required
                    />
                    <InputError :message="form.errors.phone" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="email" value="Email Address" />
                    <TextInput
                      id="email"
                      v-model="form.email"
                      type="email"
                      class="mt-1 block w-full"
                      required
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                  </div>
                </div>
              </div>

              <!-- Step 3: Location -->
              <div v-show="currentStep === 2">
                <div class="space-y-6">
                  <!-- Address full width -->
                  <div>
                    <InputLabel for="address" value="Address" />
                    <TextInput
                      id="address"
                      v-model="form.address"
                      type="text"
                      class="mt-1 block w-full"
                      required
                    />
                    <InputError :message="form.errors.address" class="mt-2" />
                  </div>
                  <!-- Two-column grid for country, county, ward, postal code -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <InputLabel for="country" value="Country" />
                      <Multiselect
                        id="country"
                        v-model="selectedCountry"
                        :options="countryOptions"
                        placeholder="Select country"
                        :allow-empty="true"
                        :searchable="true"
                        :clear-on-select="false"
                        :close-on-select="true"
                        :show-labels="false"
                        @input="val => { form.country = val === 'Other (type manually)' ? '' : val; }"
                        label=""
                        track-by=""
                      />
                      <div v-if="selectedCountry === 'Other (type manually)'" class="mt-2">
                      <TextInput
                          id="manual-country"
                          v-model="manualCountry"
                          type="text"
                          class="mt-1 block w-full"
                          placeholder="Type country manually"
                          @input="form.country = manualCountry"
                          required
                        />
                      </div>
                      <InputError :message="form.errors.country" class="mt-2" />
                    </div>
                    <div>
                      <InputLabel for="city" value="County" />
                      <Multiselect
                        id="city"
                        v-model="selectedCounty"
                        :options="countiesWithManual"
                        placeholder="Select or search county"
                        :allow-empty="true"
                        :searchable="true"
                        :clear-on-select="false"
                        :close-on-select="true"
                        :show-labels="false"
                        @input="val => { form.city = val === MANUAL_ENTRY_OPTION ? '' : val; if (!val || val === MANUAL_ENTRY_OPTION) { selectedWard = ''; } }"
                        label=""
                        track-by=""
                      />
                      <div v-if="selectedCounty === MANUAL_ENTRY_OPTION" class="mt-2">
                        <TextInput
                          id="manual-county"
                          v-model="manualCounty"
                        type="text"
                        class="mt-1 block w-full"
                          placeholder="Type county manually"
                          @input="form.city = manualCounty"
                        required
                      />
                      </div>
                      <InputError :message="form.errors.city" class="mt-2" />
                    </div>
                    <div>
                      <InputLabel for="state" value="Village/Ward/Constituency" />
                      <Multiselect
                        id="state"
                        v-model="selectedWard"
                        :options="wardsWithManual"
                        placeholder="Select or search ward/constituency/village"
                        :allow-empty="true"
                        :searchable="true"
                        :clear-on-select="false"
                        :close-on-select="true"
                        :show-labels="false"
                        :disabled="!selectedCounty"
                        @input="val => form.state = val === MANUAL_ENTRY_OPTION ? '' : val"
                        label=""
                        track-by=""
                      />
                      <div v-if="selectedWard === MANUAL_ENTRY_OPTION" class="mt-2">
                        <TextInput
                          id="manual-ward"
                          v-model="manualWard"
                        type="text"
                        class="mt-1 block w-full"
                          placeholder="Type ward/constituency/village manually"
                          @input="form.state = manualWard"
                        required
                      />
                      </div>
                      <InputError :message="form.errors.state" class="mt-2" />
                    </div>
                    <div>
                      <InputLabel for="postal_code" value="Postal Code" />
                      <TextInput
                        id="postal_code"
                        v-model="form.postal_code"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Postal Code (optional)"
                      />
                      <InputError :message="form.errors.postal_code" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 4: Business Details -->
              <div v-show="currentStep === 3">
                <div class="space-y-6">
                  <div>
                    <InputLabel for="industry" value="Industry" />
                    <TextInput
                      id="industry"
                      v-model="form.industry"
                      type="text"
                      class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.industry" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="website" value="Website" />
                    <TextInput
                      id="website"
                      v-model="form.website"
                      type="url"
                      class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.website" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="tax_number" value="Tax ID" />
                    <TextInput
                      id="tax_number"
                      v-model="form.tax_number"
                      type="text"
                      class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.tax_number" class="mt-2" />
                  </div>
                </div>
              </div>

              <!-- Step 5: Documents -->
              <div v-show="currentStep === 4">
                <div class="space-y-6">
                  <div>
                    <InputLabel for="logo" value="Business Logo" />
                    <div class="mt-1 flex items-center">
                      <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        class="h-20 w-20 object-cover rounded-lg mr-4"
                        alt="Business logo"
                      />
                      <div class="flex-1">
                        <span v-if="business.logo_url && !selectedLogo" class="text-sm text-gray-500 dark:text-gray-400 mr-4">
                          Current: {{ business.logo_url.split('/').pop() }}
                        </span>
                        <span v-if="selectedLogo" class="text-sm text-primary-600 dark:text-primary-400">
                          Selected: {{ selectedLogo.name }} ({{ (selectedLogo.size / 1024).toFixed(1) }} KB)
                        </span>
                      </div>
                      <input
                        type="file"
                        id="logo"
                        @change="handleLogoChange"
                        accept="image/*"
                        class="hidden"
                      />
                      <label
                        for="logo"
                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                      >
                        {{ selectedLogo ? 'Change Logo' : 'Upload Logo' }}
                      </label>
                    </div>
                    <InputError :message="form.errors.logo" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="tax_document" value="Tax Document" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <span v-if="business.tax_document_url && !selectedTaxDocument" class="text-sm text-gray-500 dark:text-gray-400 mr-4">
                          Current: {{ business.tax_document_url.split('/').pop() }}
                        </span>
                        <span v-if="selectedTaxDocument" class="text-sm text-primary-600 dark:text-primary-400">
                          Selected: {{ selectedTaxDocument.name }} ({{ (selectedTaxDocument.size / 1024).toFixed(1) }} KB)
                        </span>
                      </div>
                      <input
                        type="file"
                        id="tax_document"
                        @change="handleTaxDocumentChange"
                        accept=".pdf,.doc,.docx"
                        class="hidden"
                      />
                      <label
                        for="tax_document"
                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                      >
                        {{ selectedTaxDocument ? 'Change Document' : 'Upload Document' }}
                      </label>
                    </div>
                    <InputError :message="form.errors.tax_document" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="registration_document" value="Business Registration Document" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <span v-if="business.registration_document_url && !selectedRegistrationDocument" class="text-sm text-gray-500 dark:text-gray-400 mr-4">
                          Current: {{ business.registration_document_url.split('/').pop() }}
                        </span>
                        <span v-if="selectedRegistrationDocument" class="text-sm text-primary-600 dark:text-primary-400">
                          Selected: {{ selectedRegistrationDocument.name }} ({{ (selectedRegistrationDocument.size / 1024).toFixed(1) }} KB)
                        </span>
                      </div>
                      <input
                        type="file"
                        id="registration_document"
                        @change="handleRegistrationDocumentChange"
                        accept=".pdf,.doc,.docx"
                        class="hidden"
                      />
                      <label
                        for="registration_document"
                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                      >
                        {{ selectedRegistrationDocument ? 'Change Document' : 'Upload Document' }}
                      </label>
                    </div>
                    <InputError :message="form.errors.registration_document" class="mt-2" />
                  </div>

                  <!-- Terms and Conditions Document -->
                  <div>
                    <InputLabel for="terms_and_conditions" value="Terms and Conditions Document" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <span v-if="business.terms_and_conditions && !selectedTermsDocument" class="text-sm text-gray-500 dark:text-gray-400 mr-4">
                          Current: {{ business.terms_and_conditions.split('/').pop() }}
                        </span>
                        <span v-if="selectedTermsDocument" class="text-sm text-primary-600 dark:text-primary-400">
                          Selected: {{ selectedTermsDocument.name }} ({{ (selectedTermsDocument.size / 1024).toFixed(1) }} KB)
                        </span>
                      </div>
                      <input
                        type="file"
                        id="terms_and_conditions"
                        @change="handleTermsDocumentChange"
                        accept=".pdf,.doc,.docx"
                        class="hidden"
                      />
                      <label
                        for="terms_and_conditions"
                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                      >
                        {{ selectedTermsDocument ? 'Change Document' : 'Upload Document' }}
                      </label>
                    </div>
                    <InputError :message="form.errors.terms_and_conditions" class="mt-2" />
                  </div>
                </div>
              </div>

              <!-- Navigation Buttons -->
              <div class="flex items-center justify-between pt-6">
                <button
                  v-if="currentStep > 0"
                  type="button"
                  @click="currentStep--"
                  class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                >
                  Previous
                </button>
                <div class="flex items-center space-x-4">
                  <button
                    v-if="currentStep < steps.length - 1"
                    type="button"
                    @click="currentStep++"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                  >
                    Next
                  </button>
                  <PrimaryButton
                    v-if="currentStep === steps.length - 1"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                  >
                    Update Business
                  </PrimaryButton>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import countiesData from '@/data/counties_wards.json';

const props = defineProps({
  business: {
    type: Object,
    required: true
  }
});

const steps = [
  'Basic Information',
  'Contact Information',
  'Location',
  'Business Details',
  'Documents'
];

const currentStep = ref(0);

// Add reactive variables to track selected files
const selectedLogo = ref(null);
const selectedTaxDocument = ref(null);
const selectedRegistrationDocument = ref(null);
const selectedTermsDocument = ref(null);

// Store the created object URL for cleanup
let currentObjectUrl = null;

// Watch for changes in logoUrl and cleanup previous object URL
const cleanupObjectUrl = () => {
  if (currentObjectUrl && typeof window !== 'undefined' && window.URL) {
    try {
      window.URL.revokeObjectURL(currentObjectUrl);
      currentObjectUrl = null;
    } catch (error) {
      console.error('Error revoking object URL:', error);
    }
  }
};

// Update the computed property to track the created URL
const logoUrl = computed(() => {
  cleanupObjectUrl(); // Cleanup previous URL
  
  if (selectedLogo.value && typeof window !== 'undefined' && window.URL) {
    try {
      currentObjectUrl = window.URL.createObjectURL(selectedLogo.value);
      return currentObjectUrl;
    } catch (error) {
      console.error('Error creating object URL:', error);
      return props.business.logo_url;
    }
  }
  return props.business.logo_url;
});

// Cleanup on component unmount
onUnmounted(() => {
  cleanupObjectUrl();
});

const counties = countiesData.map(c => c.name);
const selectedCounty = ref(props.business.city || '');
const wards = computed(() => {
  const county = countiesData.find(c => c.name === selectedCounty.value);
  return county ? county.wards : [];
});
const selectedWard = ref(props.business.state || '');

const MANUAL_ENTRY_OPTION = 'Other (type manually)';
const countiesWithManual = [...counties, MANUAL_ENTRY_OPTION];
const wardsWithManual = computed(() => selectedCounty.value && selectedCounty.value !== MANUAL_ENTRY_OPTION ? [...wards.value, MANUAL_ENTRY_OPTION] : [MANUAL_ENTRY_OPTION]);
const manualCounty = ref('');
const manualWard = ref('');

const countryOptions = ['Kenya', 'Other (type manually)'];
const selectedCountry = ref(props.business.country || 'Kenya');
const manualCountry = ref('');

const form = useForm({
  name: props.business.name || '',
  description: props.business.description || '',
  phone: props.business.phone || '',
  email: props.business.email || '',
  address: props.business.address || '',
  city: props.business.city || '',
  state: props.business.state || '',
  country: props.business.country || '',
  postal_code: props.business.postal_code || '',
  industry: props.business.industry || '',
  website: props.business.website || '',
  tax_number: props.business.tax_number || '',
  logo: null,
  tax_document: null,
  registration_document: null,
  terms_and_conditions: null
});

// Ensure form is properly initialized when component mounts
onMounted(() => {
  if (form.city) selectedCounty.value = form.city;
  if (form.state) selectedWard.value = form.state;
  if (!form.country) {
    form.country = selectedCountry.value || 'Kenya';
  }
});

const handleLogoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid File Type',
        text: 'Please select an image file (JPG, PNG, GIF, etc.)',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Validate file size (1MB limit)
    if (file.size > 1024 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'File Too Large',
        text: 'Please select an image smaller than 1MB',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Update form data reactively
    form.logo = file;
    selectedLogo.value = file;
    
    // Show success message
    Swal.fire({
      icon: 'success',
      title: 'Logo Selected',
      text: `${file.name} has been selected successfully`,
      timer: 1500,
      showConfirmButton: false
    });
  }
};

const handleTaxDocumentChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!allowedTypes.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid File Type',
        text: 'Please select a PDF or Word document',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Validate file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'File Too Large',
        text: 'Please select a document smaller than 5MB',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Update form data reactively
    form.tax_document = file;
    selectedTaxDocument.value = file;
    
    // Show success message
    Swal.fire({
      icon: 'success',
      title: 'Document Selected',
      text: `${file.name} has been selected successfully`,
      timer: 1500,
      showConfirmButton: false
    });
  }
};

const handleRegistrationDocumentChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!allowedTypes.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid File Type',
        text: 'Please select a PDF or Word document',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Validate file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'File Too Large',
        text: 'Please select a document smaller than 5MB',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Update form data reactively
    form.registration_document = file;
    selectedRegistrationDocument.value = file;
    
    // Show success message
    Swal.fire({
      icon: 'success',
      title: 'Document Selected',
      text: `${file.name} has been selected successfully`,
      timer: 1500,
      showConfirmButton: false
    });
  }
};

const handleTermsDocumentChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!allowedTypes.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid File Type',
        text: 'Please select a PDF or Word document',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Validate file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'File Too Large',
        text: 'Please select a document smaller than 5MB',
        confirmButtonText: 'OK'
      });
      target.value = '';
      return;
    }
    
    // Update form data reactively
    form.terms_and_conditions = file;
    selectedTermsDocument.value = file;
    
    // Show success message
    Swal.fire({
      icon: 'success',
      title: 'Document Selected',
      text: `${file.name} has been selected successfully`,
      timer: 1500,
      showConfirmButton: false
    });
  }
};

const handleSubmit = () => {
  // Ensure postal_code is set to 'N/A' if empty
  if (!form.postal_code || form.postal_code.trim() === '') {
    form.postal_code = 'N/A';
  }
  // Log the actual form data object
  
  // Log form data before submission
  
  // Show loading state
  Swal.fire({
    title: 'Updating Business...',
  text: 'Please wait while we update your business information.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  // Create a FormData object manually to ensure all data is included
  const formData = new FormData();
  
  // Add method override for PUT request
  formData.append('_method', 'PUT');
  
  // Add CSRF token
  formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
  
  // Add all form fields
  formData.append('name', form.name);
  formData.append('description', form.description || '');
  formData.append('phone', form.phone);
  formData.append('email', form.email);
  formData.append('address', form.address);
  formData.append('city', form.city);
  formData.append('state', form.state);
  formData.append('country', form.country);
  formData.append('postal_code', form.postal_code);
  formData.append('industry', form.industry || '');
  formData.append('website', form.website || '');
  formData.append('tax_number', form.tax_number || '');
  
  // Add files if selected
  if (form.logo) {
    formData.append('logo', form.logo);
  }
  if (form.tax_document) {
    formData.append('tax_document', form.tax_document);
  }
  if (form.registration_document) {
    formData.append('registration_document', form.registration_document);
  }
  if (form.terms_and_conditions) {
    formData.append('terms_and_conditions', form.terms_and_conditions);
  }

  // Log what we're about to send
  
  // Use Inertia router to send the FormData
  router.post(`/businesses/${props.business.id}`, formData, {
    preserveScroll: true,
    onProgress: (progress) => {
      console.log('Upload progress:', progress);
    },
    onStart: () => {
      console.log('Form submission started');
    },
    onFinish: () => {
      console.log('Form submission finished');
    },
    onSuccess: (response) => {
      console.log('Success response:', response);
        Swal.fire({
          icon: 'success',
          title: 'Success!',
        text: 'Business updated successfully.',
        confirmButtonText: 'OK'
      }).then(() => {
        // Use Inertia navigation instead of page reload
        router.visit('/businesses');
        });
      },
      onError: (errors) => {
      console.error('Form validation errors:', errors);
      console.error('Full error object:', errors);
      
      // Show specific error messages
      let errorMessage = 'Please check the form and try again.';
      if (errors.logo) {
        errorMessage = `Logo error: ${errors.logo}`;
      } else if (errors.tax_document) {
        errorMessage = `Tax document error: ${errors.tax_document}`;
      } else if (errors.registration_document) {
        errorMessage = `Registration document error: ${errors.registration_document}`;
      } else if (errors.terms_and_conditions) {
        errorMessage = `Terms and conditions document error: ${errors.terms_and_conditions}`;
      } else if (Object.keys(errors).length > 0) {
        errorMessage = Object.values(errors).join('\n');
      }
      
        Swal.fire({
          icon: 'error',
        title: 'Validation Error',
        text: errorMessage,
          confirmButtonText: 'OK'
        });
      }
    });
};

watch(selectedCounty, (val) => {
  if (val === MANUAL_ENTRY_OPTION) {
    form.city = manualCounty;
  } else {
    form.city = val;
  }
});

watch(manualCounty, (val) => {
  if (selectedCounty === MANUAL_ENTRY_OPTION) {
    form.city = val;
  }
});

watch(selectedWard, (val) => {
  if (val === MANUAL_ENTRY_OPTION) {
    form.state = manualWard;
  } else {
    form.state = val;
  }
});

watch(manualWard, (val) => {
  if (selectedWard === MANUAL_ENTRY_OPTION) {
    form.state = val;
  }
});

watch(selectedCountry, (val) => {
  if (val === 'Other (type manually)') {
    form.country = manualCountry;
  } else {
    form.country = val;
  }
});

watch(manualCountry, (val) => {
  if (selectedCountry === 'Other (type manually)') {
    form.country = val;
  }
});

// In validateStep, remove postal_code required check
const validateStep = (step: number): boolean => {
  // Clear previous errors
  form.errors = {};
  switch (step) {
    case 0: // Basic Information
      if (!form.name) {
        form.errors.name = 'The name field is required.';
        return false;
      }
      break;
    case 1: // Contact Information
      if (!form.phone) {
        form.errors.phone = 'The phone field is required.';
        return false;
      }
      if (!form.email) {
        form.errors.email = 'The email field is required.';
        return false;
      }
      break;
    case 2: // Location
      if (!form.address) {
        form.errors.address = 'The address field is required.';
        return false;
      }
      if (!form.city) {
        form.errors.city = 'The city field is required.';
        return false;
      }
      if (!form.state) {
        form.errors.state = 'The state field is required.';
        return false;
      }
      if (!form.country) {
        form.errors.country = 'The country field is required.';
        return false;
      }
      // postal_code is NOT required
      break;
    case 3: // Business Details
      // All fields optional in this step
      break;
    case 4: // Documents
      // All fields optional in this step
      break;
  }
  return true;
};
</script> 

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style> 
