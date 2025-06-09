<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { User } from "@/types";
import NavLink from "@/components/NavLink.vue";
import ResponsiveNavLink from "@/components/ResponsiveNavLink.vue";
import ApplicationLogo from "@/components/ApplicationLogo.vue";
import type { BreadcrumbItemType } from "@/types";

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}

defineProps<{
    title?: string;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const showingNavigationDropdown = ref(false);
const page = usePage<PageProps>();

const isCurrentRoute = (path: string) => {
    return page.url.startsWith(path);
};

const user = page.props.auth.user;
const isAdmin = computed(() => user.role === 'admin');
const isOwner = computed(() => user.role === 'owner');
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-neutral-900">
            <nav class="bg-white dark:bg-neutral-800 border-b border-gray-100 dark:border-neutral-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link href="/dashboard">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-neutral-100" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink href="/dashboard" :active="isCurrentRoute('/dashboard')">
                                    Dashboard
                                </NavLink>
                                
                                <!-- Admin/Owner Navigation -->
                                <template v-if="isAdmin || isOwner">
                                    <NavLink href="/businesses" :active="isCurrentRoute('/businesses')">
                                        Businesses
                                    </NavLink>
                                    <NavLink href="/branches" :active="isCurrentRoute('/branches')">
                                        Branches
                                    </NavLink>
                                    <NavLink href="/sellers" :active="isCurrentRoute('/sellers')">
                                        Sellers
                                    </NavLink>
                                </template>

                                <!-- Common Navigation -->
                                <NavLink href="/products" :active="isCurrentRoute('/products')">
                                    Products
                                </NavLink>
                                <NavLink href="/inventory-items" :active="isCurrentRoute('/inventory-items')">
                                    Inventory
                                </NavLink>
                                <NavLink href="/discounts" :active="isCurrentRoute('/discounts')">
                                    Discounts
                                </NavLink>
                                <NavLink href="/sales" :active="isCurrentRoute('/sales')">
                                    Sales
                                </NavLink>

                                <!-- Admin/Owner Only -->
                                <template v-if="isAdmin || isOwner">
                                    <NavLink href="/reports" :active="isCurrentRoute('/reports')">
                                        Reports
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <div class="flex items-center">
                                    <span class="text-gray-700 dark:text-neutral-300 mr-4">{{ user.name }}</span>
                                    <Link
                                        href="/settings/profile"
                                        class="text-gray-700 dark:text-neutral-300 hover:text-gray-900 dark:hover:text-white"
                                    >
                                        Settings
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-neutral-500 hover:text-gray-500 dark:hover:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-neutral-700 focus:text-gray-500 dark:focus:text-neutral-400 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink href="/dashboard" :active="isCurrentRoute('/dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        
                        <!-- Admin/Owner Navigation -->
                        <template v-if="isAdmin || isOwner">
                            <ResponsiveNavLink href="/businesses" :active="isCurrentRoute('/businesses')">
                                Businesses
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href="/branches" :active="isCurrentRoute('/branches')">
                                Branches
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href="/sellers" :active="isCurrentRoute('/sellers')">
                                Sellers
                            </ResponsiveNavLink>
                        </template>

                        <!-- Common Navigation -->
                        <ResponsiveNavLink href="/products" :active="isCurrentRoute('/products')">
                            Products
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href="/inventory-items" :active="isCurrentRoute('/inventory-items')">
                            Inventory
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href="/discounts" :active="isCurrentRoute('/discounts')">
                            Discounts
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href="/sales" :active="isCurrentRoute('/sales')">
                            Sales
                        </ResponsiveNavLink>

                        <!-- Admin/Owner Only -->
                        <template v-if="isAdmin || isOwner">
                            <ResponsiveNavLink href="/reports" :active="isCurrentRoute('/reports')">
                                Reports
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-neutral-700">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800 dark:text-neutral-100">
                                {{ user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500 dark:text-neutral-400">
                                {{ user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink href="/settings/profile">
                                Settings
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-white dark:bg-neutral-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
