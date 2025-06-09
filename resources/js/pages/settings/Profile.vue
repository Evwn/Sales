<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import Swal from 'sweetalert2';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = async () => {
    try {
        // Show loading state
        Swal.fire({
            title: 'Updating Profile...',
            allowOutsideClick: false,
            backdrop: 'rgba(0,0,0,0.4)',
            timer: 2000,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        await form.patch('/settings/profile', {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Profile updated successfully',
                    timer: 2000,
                    showConfirmButton: false,
                    backdrop: 'rgba(0,0,0,0.4)'
                });
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
        });
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

const deleteAccount = async () => {
    try {
        // Show confirmation dialog with text input
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
            // Show loading state
            Swal.fire({
                title: 'Deleting Account...',
                allowOutsideClick: false,
                backdrop: 'rgba(0,0,0,0.4)',
                timer: 2000,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            await form.delete('/settings/profile', {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account Deleted',
                        text: 'Your account has been permanently deleted',
                        timer: 2000,
                        showConfirmButton: false,
                        backdrop: 'rgba(0,0,0,0.4)'
                    }).then(() => {
                        router.visit('/login');
                    });
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to delete account',
                        confirmButtonText: 'OK',
                        backdrop: 'rgba(0,0,0,0.4)'
                    });
                }
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

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="'/email/verification-notification'"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save</span>
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <div class="mt-6 space-y-4">
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

                <Button variant="destructive" @click="deleteAccount">Delete Account</Button>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
