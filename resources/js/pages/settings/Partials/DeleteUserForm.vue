<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1 flex justify-between">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Account</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Permanently delete your account and all associated data.
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow sm:rounded-md">
                <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-900 dark:bg-orange-950">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">Warning: Account Deletion</h3>
                            <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                                <p>Once you delete your account:</p>
                                <ul class="list-disc pl-5 mt-1 space-y-1">
                                    <li>All your data will be permanently deleted</li>
                                    <li>This action cannot be undone</li>
                                    <li>You will lose access to all your businesses and branches</li>
                                    <li>All associated sales and inventory data will be lost</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <Button
                        type="button"
                        @click="confirmDelete"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Delete Account
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import Swal from 'sweetalert2';

const form = useForm({});

const confirmDelete = async () => {
    try {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone. All your data will be permanently deleted.',
            icon: 'warning',
            input: 'text',
            inputLabel: 'Type "Delete" to confirm',
            inputPlaceholder: 'Type "Delete" here',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete my account',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            backdrop: 'rgba(0,0,0,0.4)',
            inputValidator: (value) => {
                if (value !== 'Delete') {
                    return 'You need to type "Delete" to confirm';
                }
            }
        });

        if (result.isConfirmed) {
            form.delete('/settings/profile', {
                preserveScroll: true,
                onSuccess: () => {
                    router.visit('/login');
                },
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred',
            confirmButtonText: 'OK',
            backdrop: 'rgba(0,0,0,0.4)'
        });
    }
};
</script>

<script lang="ts">
export default {
    name: 'DeleteUserForm'
};
</script> 