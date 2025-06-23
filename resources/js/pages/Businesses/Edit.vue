<template>
  <AppLayout title="Edit Business">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Edit Business
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
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  business: {
    type: Object,
    required: true
  }
});

// Debug: Log the business data
console.log('Business data received:', props.business);

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
  registration_document: null
});

// Ensure form is properly initialized when component mounts
onMounted(() => {
  console.log('Component mounted, business data:', props.business);
  console.log('Form data after initialization:', form.data());
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
    console.log('Logo selected:', file.name, file.size, file.type);
    console.log('Form logo after update:', form.logo);
    
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
    console.log('Tax document selected:', file.name, file.size, file.type);
    console.log('Form tax_document after update:', form.tax_document);
    
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
    console.log('Registration document selected:', file.name, file.size, file.type);
    console.log('Form registration_document after update:', form.registration_document);
    
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
  // Log the actual form data object
  console.log('Raw form data:', form.data());
  console.log('Form logo object:', form.logo);
  console.log('Form tax_document object:', form.tax_document);
  console.log('Form registration_document object:', form.registration_document);
  
  // Log form data before submission
  console.log('Form data before submission:', {
    name: form.name,
    phone: form.phone,
    email: form.email,
    address: form.address,
    city: form.city,
    state: form.state,
    country: form.country,
    postal_code: form.postal_code,
    description: form.description,
    industry: form.industry,
    website: form.website,
    tax_number: form.tax_number,
    logo: form.logo ? `${form.logo.name} (${form.logo.size} bytes)` : 'No logo selected',
    tax_document: form.tax_document ? `${form.tax_document.name} (${form.tax_document.size} bytes)` : 'No tax document selected',
    registration_document: form.registration_document ? `${form.registration_document.name} (${form.registration_document.size} bytes)` : 'No registration document selected'
  });

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

  // Log what we're about to send
  console.log('FormData entries:');
  for (let [key, value] of formData.entries()) {
    console.log(`${key}:`, value);
  }

  // Use Inertia router to send the FormData
  router.post(route('businesses.update', props.business.id), formData, {
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
        router.visit(route('businesses.index'));
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
</script> 