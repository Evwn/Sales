<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import type { BreadcrumbItemType } from "@/types";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { ChevronDown, User as UserIcon, LogOut } from 'lucide-vue-next';
import NavLink from "@/components/NavLink.vue";
import ApplicationLogo from "@/components/ApplicationLogo.vue";
import Dropdown from '@/components/Dropdown.vue';
import DropdownLink from '@/components/DropdownLink.vue';
import ResponsiveNavLink from '@/components/ResponsiveNavLink.vue';

interface User {
    id: number;
    name: string;
    email: string;
    roles: {
        id: number;
        name: string;
        permissions: string[];
    }[];
    branch_id: number | null;
    business_id: number | null;
}

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

const isSeller = computed(() => {
    return page.props.auth?.user?.roles?.some(role => role.name === 'seller');
});

const isOwner = computed(() => {
    return page.props.auth?.user?.roles?.some(role => role.name === 'owner');
});

const isAdmin = computed(() => 
    page.props.auth?.user?.roles?.some(role => role.name === 'admin')
);

const hasPermission = (permission: string) => {
    return page.props.auth?.user?.roles?.some(role => 
        role.permissions?.includes(permission)
    ) ?? false;
};

const canAccessInventory = computed(() => {
    return isOwner.value || isAdmin.value || isSeller.value || hasPermission('manage_inventory');
});

const canAccessDiscounts = computed(() => {
    // Only owner, admin, or users with 'manage_discounts' permission can access discounts
    return isOwner.value || isAdmin.value || hasPermission('manage_discounts');
});

const canAccessReports = computed(() => {
    // Only owner, admin, or users with 'view_reports' permission can access reports
    return isOwner.value || isAdmin.value || hasPermission('view_reports');
});

const isCurrentRoute = (route: string) => {
    return window.location.pathname === route;
};

// Add computed property for dropdown alignment
const dropdownAlign = computed(() => {
    return window.innerWidth < 640 ? 'start' : 'end';
});

const isOffline = ref(!navigator.onLine);

window.addEventListener('online', () => { isOffline.value = false; });
window.addEventListener('offline', () => { isOffline.value = true; });
</script>

<template>
    <div>
        <div class="fixed bottom-4 right-4 z-50 flex items-center space-x-2 w-auto">
            <span :class="[
                'inline-block h-3 w-3 rounded-full',
                isOffline ? 'bg-red-500' : 'bg-green-500'
            ]"></span>
            <span class="text-sm text-gray-800 dark:text-gray-100">
                {{ isOffline ? 'Offline' : 'Online' }}
            </span>
        </div>
        <div class="min-h-screen bg-gray-100 dark:bg-neutral-900">
            <nav class="bg-white dark:bg-neutral-800 border-b border-gray-100 dark:border-neutral-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div class="shrink-0 flex items-center">
                                <Link href="/dashboard">
                                    <template v-if="page.props.auth?.user?.logo_url">
                                        <img :src="page.props.auth.user.logo_url" alt="User Logo" class="block h-9 w-9 rounded-full object-cover" />
                                    </template>
                                    <template v-else>
                                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-neutral-100" />
                                    </template>
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex sm:items-center">
                            <!-- Seller Navigation: Only show Dashboard, Inventory, Sales -->
                            <template v-if="isSeller">
                                <NavLink href="/dashboard" :active="isCurrentRoute('/dashboard')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Dashboard
                                </NavLink>
                                <NavLink href="/products" :active="isCurrentRoute('/products')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Inventory
                                </NavLink>
                            </template>
                            <!-- Owner Navigation -->
                            <template v-else-if="isOwner">
                                <NavLink href="/dashboard" :active="isCurrentRoute('/dashboard')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Dashboard
                                </NavLink>
                                <NavLink href="/sales" :active="isCurrentRoute('/sales')">Sales</NavLink>
                                <NavLink href="/businesses" :active="isCurrentRoute('/businesses')">Businesses</NavLink>
                                <NavLink href="/branches" :active="isCurrentRoute('/branches')">Branches</NavLink>
                                <NavLink href="/sellers" :active="isCurrentRoute('/sellers')">Sellers</NavLink>
                                <NavLink href="/products" :active="isCurrentRoute('/products')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Inventory
                                </NavLink>
                                <NavLink href="/inventory-items" :active="isCurrentRoute('/inventory-items')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Products
                                </NavLink>
                                <NavLink v-if="isOwner || canAccessReports" href="/reports" :active="isCurrentRoute('/reports')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Reports
                                </NavLink>
                            </template>
                            <!-- Admin Navigation -->
                            <template v-else-if="isAdmin">
                                <NavLink v-if="hasPermission('manage_users')" href="/users" :active="isCurrentRoute('/users')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Users
                                </NavLink>
                                <NavLink href="/products" :active="isCurrentRoute('/products')" class="text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300">
                                    Inventory
                                </NavLink>
                            </template>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <!-- Settings Dropdown -->
                            <div class="hidden sm:flex sm:items-center sm:ml-6">
                                <div class="ml-3 relative">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-2 hover:opacity-80">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="hidden sm:inline">{{ page.props.auth?.user?.name }}</span>
                                                    <ChevronDown class="size-4" />
                                                </div>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-56">
                                            <DropdownMenuItem as-child>
                                                <Link href="/settings/profile" class="flex items-center gap-2">
                                                    <UserIcon class="size-4" />
                                                    <span>Profile</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem as-child>
                                                <Link href="/logout" method="post" as="button" class="flex items-center gap-2 text-red-600">
                                                    <LogOut class="size-4" />
                                                    <span>Logout</span>
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden fixed inset-0 z-50">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/50" @click="showingNavigationDropdown = false"></div>
                    
                    <!-- Menu Content -->
                    <div class="fixed inset-y-0 left-0 w-3/4 max-w-sm bg-white dark:bg-neutral-800 shadow-lg">
                        <div class="flex flex-col h-full">
                            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-neutral-700">
                                <div class="font-medium text-base text-gray-800 dark:text-neutral-100">
                                    {{ page.props.auth?.user?.name }}
                                </div>
                                <button @click="showingNavigationDropdown = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto p-4">
                                <nav class="flex flex-col space-y-3">
                                    
                                    <!-- Seller Navigation -->
                                    <template v-if="isSeller">
                                        <NavLink href="/dashboard" :active="isCurrentRoute('/dashboard')">Dashboard</NavLink>
                                        <NavLink href="/products" :active="isCurrentRoute('/products')">Inventory</NavLink>
                                    </template>

                                    <!-- Owner Navigation -->
                                    <template v-else-if="isOwner">
                                        <NavLink href="/dashboard" :active="isCurrentRoute('/dashboard')">Dashboard</NavLink>
                                        <NavLink href="/products" :active="isCurrentRoute('/products')">Inventory</NavLink>
                                        <NavLink href="/inventory-items" :active="isCurrentRoute('/inventory-items')">Products</NavLink>
                                        <NavLink href="/sales" :active="isCurrentRoute('/sales')">Sales</NavLink>
                                        <NavLink href="/businesses" :active="isCurrentRoute('/businesses')">Businesses</NavLink>
                                        <NavLink href="/branches" :active="isCurrentRoute('/branches')">Branches</NavLink>
                                        <NavLink href="/sellers" :active="isCurrentRoute('/sellers')">Sellers</NavLink>
                                        <NavLink href="/reports" :active="isCurrentRoute('/reports')">Reports</NavLink>
                                    </template>

                                    <!-- Admin Navigation -->
                                    <template v-else-if="isAdmin">
                                        <NavLink v-if="hasPermission('manage_users')" href="/users" :active="isCurrentRoute('/users')" class="w-full block px-4 py-3 text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700/50 rounded-lg">
                                            Users
                                        </NavLink>
                                    </template>
                                </nav>
                            </div>

                            <div class="p-4 border-t border-gray-200 dark:border-neutral-700">
                                <div class="flex flex-col space-y-3">
                                    <Link href="/settings/profile" class="w-full block px-4 py-3 text-gray-900 dark:text-neutral-100 hover:text-gray-700 dark:hover:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700/50 rounded-lg">
                                        Settings
                                    </Link>
                                    <Link href="/logout" method="post" as="button" class="w-full block px-4 py-3 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                        Logout
                                    </Link>
                                </div>
                            </div>
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

<script lang="ts">
export default {
    name: 'AppLayout'
};
</script>
