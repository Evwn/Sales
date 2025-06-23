<template>
  <AppLayout title="Create Business">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Create Business
        </h2>
        <Link
          :href="route('businesses.index')"
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

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <InputLabel for="city" value="City" />
                      <TextInput
                        id="city"
                        v-model="form.city"
                        type="text"
                        class="mt-1 block w-full"
                        required
                      />
                      <InputError :message="form.errors.city" class="mt-2" />
                    </div>

                    <div>
                      <InputLabel for="state" value="State/Province" />
                      <TextInput
                        id="state"
                        v-model="form.state"
                        type="text"
                        class="mt-1 block w-full"
                        required
                      />
                      <InputError :message="form.errors.state" class="mt-2" />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <InputLabel for="country" value="Country" />
                      <TextInput
                        id="country"
                        v-model="form.country"
                        type="text"
                        class="mt-1 block w-full"
                        required
                      />
                      <InputError :message="form.errors.country" class="mt-2" />
                    </div>

                    <div>
                      <InputLabel for="postal_code" value="Postal Code" />
                      <TextInput
                        id="postal_code"
                        v-model="form.postal_code"
                        type="text"
                        class="mt-1 block w-full"
                        required
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
                    <div class="mt-1 flex items-center">
                      <img
                        v-if="form.logo && form.logo instanceof File"
                        :src="URL.createObjectURL(form.logo)"
                        class="h-20 w-20 object-cover rounded-lg mr-4"
                        alt="Business logo"
                      />
                      <img
                        v-else-if="form.logo && typeof form.logo === 'string'"
                        :src="form.logo"
                        class="h-20 w-20 object-cover rounded-lg mr-4"
                        alt="Business logo"
                      />
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
                        {{ form.logo && form.logo instanceof File ? 'Change Logo' : 'Upload Logo' }}
                      </label>
                      <span v-if="form.logo && form.logo instanceof File" class="ml-2 text-sm text-primary-600 dark:text-primary-400">
                        {{ form.logo.name }}
                      </span>
                    </div>
                    <InputError :message="form.errors.logo" class="mt-2" />
                  </div>

                  <!-- Tax Document -->
                  <div>
                    <InputLabel for="tax_document" value="Tax Document" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <span v-if="form.tax_document && typeof form.tax_document === 'string'" class="text-sm text-gray-500 dark:text-gray-400 mr-4">
                          Current: {{ form.tax_document.split('/').pop() }}
                        </span>
                        <span v-if="form.tax_document && form.tax_document instanceof File" class="text-sm text-primary-600 dark:text-primary-400">
                          Selected: {{ form.tax_document.name }}
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
                        {{ form.tax_document && form.tax_document instanceof File ? 'Change Document' : 'Upload Document' }}
                      </label>
                    </div>
                    <InputError :message="form.errors.tax_document" class="mt-2" />
                  </div>

                  <!-- Registration Document -->
                  <div>
                    <InputLabel for="registration_document" value="Business Registration Document" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <span v-if="form.registration_document && typeof form.registration_document === 'string'" class="text-sm text-gray-500 dark:text-gray-400 mr-4">
                          Current: {{ form.registration_document.split('/').pop() }}
                        </span>
                        <span v-if="form.registration_document && form.registration_document instanceof File" class="text-sm text-primary-600 dark:text-primary-400">
                          Selected: {{ form.registration_document.name }}
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
                        {{ form.registration_document && form.registration_document instanceof File ? 'Change Document' : 'Upload Document' }}
                      </label>
                    </div>
                    <InputError :message="form.errors.registration_document" class="mt-2" />
                  </div>

                  <!-- Terms and Conditions Document -->
                  <div>
                    <InputLabel for="terms_and_conditions" value="Terms and Conditions (Document)" />
                    <div class="mt-1 flex items-center">
                      <div class="flex-1">
                        <span v-if="form.terms_and_conditions && typeof form.terms_and_conditions === 'string'" class="text-sm text-gray-500 dark:text-gray-400 mr-4">
                          Current: {{ form.terms_and_conditions.split('/').pop() }}
                        </span>
                        <span v-if="form.terms_and_conditions && form.terms_and_conditions instanceof File" class="text-sm text-primary-600 dark:text-primary-400">
                          Selected: {{ form.terms_and_conditions.name }}
                        </span>
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
                        {{ form.terms_and_conditions && form.terms_and_conditions instanceof File ? 'Change Document' : 'Upload Document' }}
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
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Swal from 'sweetalert2';

const steps = [
  'Basic Information',
  'Contact Information',
  'Location',
  'Business Details',
  'Documents'
];

const currentStep = ref(0);

const form = useForm({
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

const handleLogoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.logo = target.files[0];
  }
};

const handleTaxDocumentChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.tax_document = target.files[0];
  }
};

const handleRegistrationDocumentChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.registration_document = target.files[0];
  }
};

const handleTermsChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.terms_and_conditions = target.files[0];
  }
};

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
      if (!form.postal_code) {
        form.errors.postal_code = 'The postal code field is required.';
        return false;
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
    await form.post(route('businesses.store'), {
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
</script> 