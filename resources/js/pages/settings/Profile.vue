<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { User, SharedData } from '@/types';

const page = usePage<SharedData>();
const user = page.props.auth.user;
</script>

<template>
    <SettingsLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="mt-10 sm:mt-0">
                    <UpdateProfileInformationForm :user="user" />
                </div>

                <div class="hidden sm:block">
                    <div class="py-8">
                        <div class="border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                </div>

                <div class="mt-10 sm:mt-0">
                    <UpdatePasswordForm />
                </div>

                <div class="hidden sm:block">
                    <div class="py-8">
                        <div class="border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                </div>

                <div class="mt-10 sm:mt-0">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>
