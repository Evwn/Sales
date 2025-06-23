<template>
    <form @submit.prevent="submit" class="space-y-8">
        <!-- Stepper -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div
                    v-for="(step, index) in steps"
                    :key="index"
                    class="flex items-center"
                >
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full"
                        :class="[
                            currentStep >= index
                                ? 'bg-primary-600 text-white'
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                        ]"
                    >
                        {{ index + 1 }}
                    </div>
                    <div
                        v-if="index < steps.length - 1"
                        class="w-24 h-1 mx-2"
                        :class="[
                            currentStep > index
                                ? 'bg-primary-600'
                                : 'bg-gray-200 dark:bg-gray-700'
                        ]"
                    ></div>
                </div>
            </div>
            <div class="flex justify-between mt-2">
                <div
                    v-for="(step, index) in steps"
                    :key="index"
                    class="text-sm font-medium"
                    :class="[
                        currentStep >= index
                            ? 'text-primary-600 dark:text-primary-400'
                            : 'text-gray-500 dark:text-gray-400'
                    ]"
                >
                    {{ step.title }}
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="space-y-6">
            <!-- Step 1: Basic Information -->
            <div v-show="currentStep === 0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 gap-6">
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Contact Information
                </h3>
                <div class="grid grid-cols-1 gap-6">
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Location
                </h3>
                <div class="grid grid-cols-1 gap-6">
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
                        <InputLabel for="state" value="State" />
                        <TextInput
                            id="state"
                            v-model="form.state"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.state" class="mt-2" />
                    </div>

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

            <!-- Step 4: Business Details -->
            <div v-show="currentStep === 3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Business Details
                </h3>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="industry" value="Industry" />
                        <TextInput
                            id="industry"
                            v-model="form.industry"
                            type="text"
                            class="mt-1 block w-full"
                            required
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
                        <InputLabel for="tax_id" value="Tax ID" />
                        <TextInput
                            id="tax_id"
                            v-model="form.tax_id"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.tax_id" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Step 5: Documents -->
            <div v-show="currentStep === 4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Documents
                </h3>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="logo" value="Business Logo" />
                        <input
                            type="file"
                            id="logo"
                            @change="handleLogoChange"
                            class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-50 file:text-primary-700
                                dark:file:bg-primary-900 dark:file:text-primary-300
                                hover:file:bg-primary-100 dark:hover:file:bg-primary-800"
                            accept="image/*"
                        />
                        <InputError :message="form.errors.logo" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="business_license" value="Business License" />
                        <input
                            type="file"
                            id="business_license"
                            @change="handleLicenseChange"
                            class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-50 file:text-primary-700
                                dark:file:bg-primary-900 dark:file:text-primary-300
                                hover:file:bg-primary-100 dark:hover:file:bg-primary-800"
                            accept=".pdf,.doc,.docx"
                        />
                        <InputError :message="form.errors.business_license" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between">
            <SecondaryButton
                v-if="currentStep > 0"
                type="button"
                @click="previousStep"
            >
                Previous
            </SecondaryButton>
            <div v-else></div>

            <PrimaryButton
                v-if="currentStep < steps.length - 1"
                type="button"
                @click="nextStep"
            >
                Next
            </PrimaryButton>
            <PrimaryButton
                v-else
                type="submit"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Creating...' : 'Create Business' }}
            </PrimaryButton>
        </div>
    </form>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    business: {
        type: Object,
        default: () => ({
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
            tax_id: '',
            logo_path: null,
            business_license_path: null,
        }),
    },
});

const currentStep = ref(0);

const steps = [
    { title: 'Basic Info' },
    { title: 'Contact' },
    { title: 'Location' },
    { title: 'Details' },
    { title: 'Documents' },
];

const form = useForm({
    name: props.business.name,
    description: props.business.description,
    phone: props.business.phone,
    email: props.business.email,
    address: props.business.address,
    city: props.business.city,
    state: props.business.state,
    country: props.business.country,
    postal_code: props.business.postal_code,
    industry: props.business.industry,
    website: props.business.website,
    tax_id: props.business.tax_id,
    logo: null,
    business_license: null,
});

const nextStep = () => {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    }
};

const previousStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

const handleLogoChange = (event) => {
    form.logo = event.target.files[0];
};

const handleLicenseChange = (event) => {
    form.business_license = event.target.files[0];
};

const submit = () => {
    // Show loading state
    Swal.fire({
        title: 'Creating Business',
        text: 'Please wait while we create your business...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Log form data before submission
    console.log('Submitting form data:', form.data());

    form.post('/businesses', {
        preserveScroll: true,
        onSuccess: (response) => {
            console.log('Success response:', response);
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Business Created!',
                text: 'Your business has been successfully created.',
                confirmButtonText: 'Great!',
                confirmButtonColor: '#4F46E5'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to businesses index page
                    router.visit('/businesses');
                }
            });
            form.reset();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
            // Show error message with specific error details
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: `
                    <p>There was an error creating your business.</p>
                    <div class="text-left mt-4">
                        ${Object.entries(errors).map(([field, message]) => 
                            `<p class="text-red-500">${field}: ${message}</p>`
                        ).join('')}
                    </div>
                `,
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#4F46E5'
            });
        },
        onFinish: () => {
            console.log('Form submission finished');
        }
    });
};
</script> 
