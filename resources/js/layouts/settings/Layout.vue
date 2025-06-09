<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineOptions({
    name: 'SettingsLayout'
});

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];

const page = usePage();
const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Page Header -->
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Settings</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your profile and account settings</p>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <!-- Settings Navigation -->
                        <aside class="lg:w-64">
                            <nav class="space-y-1">
                                <Button
                                    v-for="item in sidebarNavItems"
                                    :key="item.href"
                                    variant="ghost"
                                    :class="[
                                        'w-full justify-start px-4 py-2 text-sm font-medium rounded-lg',
                                        currentPath === item.href
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-gray-800'
                                    ]"
                                    as-child
                                >
                                    <Link :href="item.href">
                                        {{ item.title }}
                                    </Link>
                                </Button>
                            </nav>
                        </aside>

                        <Separator class="my-6 lg:hidden" orientation="horizontal" />

                        <!-- Content Area -->
                        <div class="flex-1 lg:max-w-2xl">
                            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                                <slot />
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
