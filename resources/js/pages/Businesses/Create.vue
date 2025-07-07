<template>
  <AppLayout title="Create Business">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Create Business
        </h2>
        <Link
          href="/businesses"
          class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
          Back to Businesses
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Owner Selection for Admins -->
            <div v-if="isAdmin" class="mb-6">
              <InputLabel for="owner_id" value="Select Owner" />
              <Multiselect
                id="owner_id"
                v-model="selectedOwner"
                :options="props.owners"
                :searchable="true"
                :close-on-select="true"
                :clear-on-select="false"
                :show-labels="false"
                placeholder="Select an owner"
                label="name"
                track-by="id"
                :custom-label="owner => `${owner.name} (${owner.email})`"
              />
              <InputError :message="form.errors.owner_id" class="mt-2" />
            </div>
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

            <form @submit.prevent="handleSubmit" class="space-y-6">
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
                      :required="false"
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
                      :required="false"
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
                      :required="false"
                    />
                    <InputError :message="form.errors.tax_number" class="mt-2" />
                  </div>
                </div>
              </div>

              <!-- Step 5: Documents -->
              <div v-show="currentStep === 4">
                <div class="space-y-6">
                  <!-- Logo -->
                  <div>
                    <InputLabel for="logo" value="Business Logo" />
                    <div class="mt-1 flex items-center space-x-4">
                      <div v-if="form.logo" class="relative">
                        <div class="h-32 w-32 border-2 border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
                          <img
                            :src="logoPreview"
                            class="h-full w-full object-contain"
                            alt="Business logo preview"
                          />
                        </div>
                        <button
                          @click="removeLogo"
                          class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                          type="button"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                      <div class="flex-1">
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
                          {{ form.logo ? 'Change Logo' : 'Upload Logo' }}
                      </label>
                        <span v-if="form.logo" class="mt-2 block text-sm text-primary-600 dark:text-primary-400">
                          {{ form.logo.name }} ({{ (form.logo.size / 1024).toFixed(1) }} KB)
                      </span>
                      </div>
                    </div>
                    <InputError :message="form.errors.logo" class="mt-2" />
                  </div>

                  <!-- Tax Document -->
                  <div>
                    <InputLabel for="tax_document" value="Tax Document" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <div v-if="form.tax_document" class="flex items-center mb-2">
                          <svg class="w-8 h-8 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                          </svg>
                          <span class="text-sm text-primary-600">
                            {{ form.tax_document.name }} ({{ (form.tax_document.size / 1024).toFixed(1) }} KB)
                        </span>
                          <button
                            @click="removeTaxDocument"
                            class="ml-2 text-red-500 hover:text-red-700"
                            type="button"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
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
                          {{ form.tax_document ? 'Change Document' : 'Upload Document' }}
                      </label>
                      </div>
                    </div>
                    <InputError :message="form.errors.tax_document" class="mt-2" />
                  </div>

                  <!-- Registration Document -->
                  <div>
                    <InputLabel for="registration_document" value="Business Registration Document" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <div v-if="form.registration_document" class="flex items-center mb-2">
                          <svg class="w-8 h-8 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                          </svg>
                          <span class="text-sm text-primary-600">
                            {{ form.registration_document.name }} ({{ (form.registration_document.size / 1024).toFixed(1) }} KB)
                        </span>
                          <button
                            @click="removeRegistrationDocument"
                            class="ml-2 text-red-500 hover:text-red-700"
                            type="button"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
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
                          {{ form.registration_document ? 'Change Document' : 'Upload Document' }}
                      </label>
                      </div>
                    </div>
                    <InputError :message="form.errors.registration_document" class="mt-2" />
                  </div>

                  <!-- Terms and Conditions -->
                  <div>
                    <InputLabel for="terms_and_conditions" value="Terms and Conditions (Document)" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <div v-if="form.terms_and_conditions" class="flex items-center mb-2">
                          <svg class="w-8 h-8 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                          </svg>
                          <span class="text-sm text-primary-600">
                            {{ form.terms_and_conditions.name }} ({{ (form.terms_and_conditions.size / 1024).toFixed(1) }} KB)
                        </span>
                          <button
                            @click="removeTermsDocument"
                            class="ml-2 text-red-500 hover:text-red-700"
                            type="button"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
                      </div>
                      <input
                        type="file"
                        id="terms_and_conditions"
                        @change="handleTermsChange"
                        accept=".pdf,.doc,.docx"
                        class="hidden"
                      />
                      <label
                        for="terms_and_conditions"
                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                      >
                          {{ form.terms_and_conditions ? 'Change Document' : 'Upload Document' }}
                      </label>
                      </div>
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
                  @click="previousStep"
                  class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                >
                  Previous
                </button>
                <div class="flex items-center space-x-4">
                  <button
                    v-if="currentStep < steps.length - 1"
                    type="button"
                    @click="nextStep"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                  >
                    Next
                  </button>
                  <PrimaryButton
                    v-if="currentStep === steps.length - 1"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                  >
                    Create Business
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
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import countiesData from '@/data/counties_wards.json';

// Accept owners prop from Inertia
const props = defineProps({
  owners: {
    type: Array,
    default: () => []
  },
  auth: {
    type: Object,
    default: () => {}
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

const form = useForm({
  owner_id: null as number | null,
  name: '',
  description: '',
  phone: '',
  email: '',
  address: '',
  city: '',
  state: '',
  country: '',
  postal_code: '',
  industry: '',
  website: '',
  tax_number: '',
  logo: null as File | null,
  tax_document: null as File | null,
  registration_document: null as File | null,
  terms_and_conditions: null as File | null
});

const counties = countiesData.map(c => c.name);
const selectedCounty = ref('');
const wards = computed(() => {
  const county = countiesData.find(c => c.name === selectedCounty.value);
  return county ? county.wards : [];
});
const selectedWard = ref('');

const MANUAL_ENTRY_OPTION = 'Other (type manually)';
const countiesWithManual = [...counties, MANUAL_ENTRY_OPTION];
const wardsWithManual = computed(() => selectedCounty.value && selectedCounty.value !== MANUAL_ENTRY_OPTION ? [...wards.value, MANUAL_ENTRY_OPTION] : [MANUAL_ENTRY_OPTION]);
const manualCounty = ref('');
const manualWard = ref('');

const countryOptions = ['Kenya', 'Other (type manually)'];
const selectedCountry = ref('Kenya');
const manualCountry = ref('');

const logoPreview = ref('');

onMounted(() => {
  if (form.city) selectedCounty.value = form.city;
  if (form.state) selectedWard.value = form.state;
  if (!form.country) {
    form.country = selectedCountry.value?.toString() || 'Kenya';
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
    
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
    
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
    
    form.tax_document = file;
    
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
    
    form.registration_document = file;
    
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

const handleTermsChange = (event: Event) => {
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
    
    form.terms_and_conditions = file;
    
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

const validateStep = (step: number): boolean => {
  // Clear previous errors
  form.errors = {};
  
  switch (step) {
    case 0: // Basic Information
      if (props.auth && props.auth.user && props.auth.user.roles && props.auth.user.roles.some(r => r.name === 'admin') && !form.owner_id) {
        form.errors.owner_id = 'Please select an owner.';
        return false;
      }
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
      if (!form.country || typeof form.country !== 'string') {
        form.country = form.country?.toString() || '';
        if (!form.country) {
          form.errors.country = 'The country field is required.';
          return false;
        }
      }
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

const nextStep = () => {
  if (validateStep(currentStep.value)) {
    currentStep.value++;
  }
};

const previousStep = () => {
  currentStep.value--;
};

const handleSubmit = async () => {
  // Ensure postal_code is set to 'N/A' if empty
  if (!form.postal_code || form.postal_code.trim() === '') {
    form.postal_code = 'N/A';
  }

  try {
    // Only validate required fields before submitting
    if (!validateStep(0) || !validateStep(1) || !validateStep(2)) {
      // Find the first step with errors
      for (let i = 0; i < 3; i++) {
        if (!validateStep(i)) {
          currentStep.value = i;
          return;
        }
      }
    }

    // Show loading state
    Swal.fire({
      title: 'Creating Business...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Submit the form
    await form.post('/businesses', {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Business created successfully',
          timer: 2000,
          showConfirmButton: false
        });
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: Object.values(errors).join('\n'),
          confirmButtonText: 'OK'
        });
      }
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'An unexpected error occurred',
      confirmButtonText: 'OK'
    });
  }
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
    form.country = manualCountry.value || '';
  } else {
    form.country = val || '';
  }
});

watch(manualCountry, (val) => {
  if (selectedCountry.value === 'Other (type manually)') {
    form.country = val || '';
  }
});

const removeLogo = () => {
  form.logo = null;
  logoPreview.value = '';
  const input = document.getElementById('logo') as HTMLInputElement;
  if (input) input.value = '';
  Swal.fire({
    icon: 'success',
    title: 'Logo Removed',
    timer: 1500,
    showConfirmButton: false
  });
};

const removeTaxDocument = () => {
  form.tax_document = null;
  const input = document.getElementById('tax_document') as HTMLInputElement;
  if (input) input.value = '';
  Swal.fire({
    icon: 'success',
    title: 'Tax Document Removed',
    timer: 1500,
    showConfirmButton: false
  });
};

const removeRegistrationDocument = () => {
  form.registration_document = null;
  const input = document.getElementById('registration_document') as HTMLInputElement;
  if (input) input.value = '';
  Swal.fire({
    icon: 'success',
    title: 'Registration Document Removed',
    timer: 1500,
    showConfirmButton: false
  });
};

const removeTermsDocument = () => {
  form.terms_and_conditions = null;
  const input = document.getElementById('terms_and_conditions') as HTMLInputElement;
  if (input) input.value = '';
  Swal.fire({
    icon: 'success',
    title: 'Terms Document Removed',
    timer: 1500,
    showConfirmButton: false
  });
};

// Helper to check if current user is admin
const isAdmin = computed(() => {
  return props.auth && props.auth.user && props.auth.user.roles && props.auth.user.roles.some(r => r.name === 'admin');
});

// --- Owner selection logic for admin ---
const selectedOwner = ref(null);

watch(selectedOwner, (val) => {
  form.owner_id = val ? val.id : null;
});
</script> 

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style> 
