<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import type { BreadcrumbItemType } from "@/types";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { ChevronDown, User as UserIcon, LogOut } from 'lucide-vue-next';
import NavLink from "@/components/NavLink.vue";
import ApplicationLogo from "@/components/ApplicationLogo.vue";
import Dropdown from '@/components/Dropdown/Dropdown.vue';
import DropdownLink from '@/components/Dropdown/DropdownLink.vue';
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

// Add csrfToken to the script
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Add handleLogout to force reload after logout
const handleLogout = (e) => {
    e.preventDefault();
    document.getElementById('logout-form').submit();
};
</script>

<template>
    <div>
        <div class="fixed bottom-4 right-4 z-50 flex items-center space-x-2 w-auto">
            <span :class="[
                'inline-block h-3 w-3 rounded-full',
                isOffline ? 'bg-red-500' : 'bg-green-500'
            ]"></span>
            <span class="text-sm text-gray-800">
                {{ isOffline ? 'Offline' : 'Online' }}
            </span>
        </div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
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
                                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                                    </template>
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex sm:items-center">
                            <!-- Seller Navigation: Only show Dashboard, Inventory, Sales -->
                            <template v-if="isSeller">
                                <NavLink href="/dashboard" :active="isCurrentRoute('/dashboard')" class="text-gray-900 hover:text-gray-700">
                                    Dashboard
                                </NavLink>
                                <NavLink href="/products" :active="isCurrentRoute('/products')" class="text-gray-900 hover:text-gray-700">
                                    Inventory
                                </NavLink>
                            </template>
                            <!-- Owner Navigation -->
                            <template v-else-if="isOwner">
                                <NavLink href="/dashboard" :active="isCurrentRoute('/dashboard')" class="text-gray-900 hover:text-gray-700">
                                    Dashboard
                                </NavLink>
                                <NavLink href="/sales" :active="isCurrentRoute('/sales')">Sales</NavLink>
                                <NavLink href="/businesses" :active="isCurrentRoute('/businesses')">Businesses</NavLink>
                                <NavLink href="/branches" :active="isCurrentRoute('/branches')">Branches</NavLink>
                                <NavLink href="/sellers" :active="isCurrentRoute('/sellers')">Sellers</NavLink>
                                <NavLink href="/products" :active="isCurrentRoute('/products')" class="text-gray-900 hover:text-gray-700">
                                    Inventory
                                </NavLink>
                                <NavLink href="/inventory-items" :active="isCurrentRoute('/inventory-items')" class="text-gray-900 hover:text-gray-700">
                                    Products
                                </NavLink>
                                <NavLink v-if="isOwner || canAccessReports" href="/reports" :active="isCurrentRoute('/reports')" class="text-gray-900 hover:text-gray-700">
                                    Reports
                                </NavLink>
                            </template>
                            <!-- Admin Navigation -->
                            <template v-else-if="isAdmin">
                                <NavLink v-if="hasPermission('manage_users')" href="/users" :active="isCurrentRoute('/users')" class="text-gray-900 hover:text-gray-700">
                                    Users
                                </NavLink>
                                <NavLink href="/products" :active="isCurrentRoute('/products')" class="text-gray-900 hover:text-gray-700">
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
                                                <form id="logout-form" method="POST" action="/logout" @submit.prevent="handleLogout">
                                                    <input type="hidden" name="_token" :value="csrfToken" />
                                                    <button type="submit" class="flex items-center gap-2 text-red-600">
                                                        <LogOut class="size-4" />
                                                        <span>Logout</span>
                                                    </button>
                                                </form>
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
                    <div class="fixed inset-y-0 left-0 w-3/4 max-w-sm bg-white shadow-lg">
                        <div class="flex flex-col h-full">
                            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                                <div class="font-medium text-base text-gray-800">
                                    {{ page.props.auth?.user?.name }}
                                </div>
                                <button @click="showingNavigationDropdown = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-4 border-t border-gray-200">
                                <div class="flex flex-col space-y-3">
                                    <form id="logout-form" method="POST" action="/logout" @submit.prevent="handleLogout">
                                        <input type="hidden" name="_token" :value="csrfToken" />
                                        <button type="submit" class="w-full block px-4 py-3 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="sm:pb-0 pb-20">
                <slot />
            </main>
        </div>
    </div>

    <!-- Bottom Scrollable Nav for Mobile -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 sm:hidden flex overflow-x-auto" style="box-shadow: 0 -2px 8px rgba(0,0,0,0.04);">
        <div class="flex flex-row w-full overflow-x-auto no-scrollbar">
            <!-- Seller Navigation -->
            <template v-if="isSeller">
                <Link href="/dashboard" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/dashboard') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0H7m6 0h6" /></svg>
                    <span class="text-xs leading-tight font-medium">Dashboard</span>
                </Link>
                <Link href="/products" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/products') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" /></svg>
                    <span class="text-xs leading-tight font-medium">Inventory</span>
                    </Link>
                <Link href="/settings/profile" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/settings/profile') ? 'text-blue-600' : 'text-gray-500'">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="text-xs leading-tight font-medium">Profile</span>
                </Link>
            </template>
            <!-- Owner Navigation -->
            <template v-else-if="isOwner">
                <Link href="/dashboard" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/dashboard') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0H7m6 0h6" /></svg>
                    <span class="text-xs leading-tight font-medium">Dashboard</span>
                </Link>
                <Link href="/businesses" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/businesses') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <span class="text-xs leading-tight font-medium">Businesses</span>
                </Link>
                <Link href="/branches" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/branches') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    <span class="text-xs leading-tight font-medium">Branches</span>
                </Link>
                <Link href="/sellers" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/sellers') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" /></svg>
                    <span class="text-xs leading-tight font-medium">Sellers</span>
                </Link>
                <Link href="/products" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/products') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" /></svg>
                    <span class="text-xs leading-tight font-medium">Inventory</span>
                </Link>
                <Link href="/inventory-items" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/inventory-items') ? 'text-blue-600' : 'text-gray-500'">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" /></svg>
                <span class="text-xs leading-tight font-medium">Products</span>
                </Link>
                <Link href="/sales" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/sales') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h2l.4 2M7 21h10l4-8H5.4M7 21l-1.35 2.7A2 2 0 007.48 27h9.04a2 2 0 001.83-1.3L21 21M7 21V14a1 1 0 011-1h5a1 1 0 011 1v7" /></svg>
                    <span class="text-xs leading-tight font-medium">Sales</span>
                </Link>
                <Link href="/reports" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/reports') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4m0 0V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" /></svg>
                    <span class="text-xs leading-tight font-medium">Reports</span>
                </Link>
                <Link href="/settings/profile" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/settings/profile') ? 'text-blue-600' : 'text-gray-500'">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="text-xs leading-tight font-medium">Profile</span>
                </Link>
            </template>
            <!-- Admin Navigation -->
            <template v-else-if="isAdmin">
                <Link href="/dashboard" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/dashboard') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0H7m6 0h6" /></svg>
                    <span class="text-xs leading-tight font-medium">Dashboard</span>
                </Link>
                <Link href="/products" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/products') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" /></svg>
                    <span class="text-xs leading-tight font-medium">Inventory</span>
                </Link>
                <Link v-if="hasPermission('manage_users')" href="/users" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/users') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" /></svg>
                    <span class="text-xs leading-tight font-medium">Users</span>
                </Link>
            </template>
        </div>
    </nav>
</template>

<script lang="ts">
export default {
    name: 'AppLayout'
};
</script>
