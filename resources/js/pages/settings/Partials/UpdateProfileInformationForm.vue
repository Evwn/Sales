<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1 flex justify-between">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Information</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Update your account's profile information and logo.
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="updateProfileInformation">
                <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow sm:rounded-md">
                    <div class="grid grid-cols-6 gap-6">
                        <!-- Logo Upload -->
                        <div class="col-span-6 flex flex-col items-center">
                            <div class="relative">
                                <img
                                    :src="previewUrl || form.logo_url || '/favicon.svg'"
                                    class="h-32 w-32 rounded-full object-cover cursor-pointer"
                                    alt="Logo Preview"
                                    @click="triggerFileInput"
                                />
                                <input
                                    ref="fileInput"
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleFileSelect"
                                />
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Click the image to select a new logo
                            </p>
                        </div>

                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <!-- Email -->
                        <div class="col-span-6 sm:col-span-4">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <span v-if="form.processing" class="mr-2">
                            <svg class="animate-spin h-4 w-4 text-white dark:text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        Save
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { User } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Swal from 'sweetalert2';
import { ref, onMounted, watch } from 'vue';

const props = defineProps<{
    user: User;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);

const form = useForm({
    _method: 'PATCH',
    name: props.user?.name || '',
    email: props.user?.email || '',
    logo_url: props.user?.logo_url || '',
    logo: null as File | null,
});

// Watch for changes in props to update form
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.name = newUser.name || '';
        form.email = newUser.email || '';
        form.logo_url = newUser.logo_url || '';
    }
}, { immediate: true, deep: true });

onMounted(() => {
    // Initialize file input
    if (fileInput.value) {
        fileInput.value.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }
});

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        form.logo = file;
        
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const updateProfileInformation = () => {

    // Show loading state
    const loadingSwal = Swal.fire({
        title: 'Updating Profile...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    formData.append('_method', 'PATCH');
    formData.append('name', form.name);
    formData.append('email', form.email);
    if (form.logo) formData.append('logo', form.logo);
    if (form.logo_url) formData.append('logo_url', form.logo_url);

    router.post('/profile', formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (response) => {
            loadingSwal.close();
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Profile updated successfully',
                timer: 2000,
                showConfirmButton: false,
                backdrop: 'rgba(0,0,0,0.4)'
            });
            
            // Update the form with new data from backend
            if (response.props.flash && response.props.flash.user) {
                form.logo_url = response.props.flash.user.logo_url;
                form.name = response.props.flash.user.name;
                form.email = response.props.flash.user.email;
            }
            
            // Clear preview after successful upload
            previewUrl.value = null;
            form.logo = null;
            
            // Force a complete page reload to ensure image updates
            router.visit('/settings/profile', {
                preserveScroll: true
            });
        },
        onError: (errors) => {
            loadingSwal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: Object.values(errors).join('\n'),
                confirmButtonText: 'OK',
                backdrop: 'rgba(0,0,0,0.4)'
            });
        }
    });
};

// Add logout function
const logout = () => {
    Swal.fire({
        title: 'Logout',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'No, stay',
        backdrop: 'rgba(0,0,0,0.4)'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post('/logout');
        }
    });
};
</script>

<script lang="ts">
export default {
    name: 'UpdateProfileInformationForm'
};
</script> 
