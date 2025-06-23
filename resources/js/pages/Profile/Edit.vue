<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Profile Information</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update your account's profile information and email address.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            type="text"
                            class="max-w-xl" 
                            required 
                            autocomplete="name" 
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input 
                            id="email" 
                            v-model="form.email" 
                            type="email"
                            class="max-w-xl" 
                            required 
                            autocomplete="username"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Your email address is unverified.
                            <Link
                                href="/email/verification-notification"
                                method="post"
                                class="text-primary hover:text-primary/90 dark:text-primary-foreground"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save changes</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                        </Transition>
                    </div>
                </form>

                <Separator class="my-6" />

                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';

import * as DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import * as SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItemType, SharedData, User } from '@/types/index.d';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch('/profile', {
        preserveScroll: true,
    });
};
</script> 
