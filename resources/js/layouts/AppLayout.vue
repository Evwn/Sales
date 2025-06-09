<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/components/Dropdown/Dropdown.vue';
import DropdownLink from '@/components/Dropdown/DropdownLink.vue';
import NavLink from '@/components/NavLink.vue';
import ResponsiveNavLink from '@/components/ResponsiveNavLink.vue';
import type { User } from '@/types';

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}

const showingNavigationDropdown = ref(false);
const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);

const isCurrentRoute = (route: string) => {
    return page.url.startsWith('/' + route);
};

const logout = () => {
    router.post('/logout');
};

const isAdmin = computed(() => user.value.role === 'admin');
const isOwner = computed(() => user.value.role === 'owner');
const isSeller = computed(() => user.value.role === 'seller');

defineOptions({
    name: 'AppLayout'
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link href="/dashboard">
                                    <img src="/logo.png" alt="Logo" class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink href="/dashboard" :active="isCurrentRoute('dashboard')">
                                    Dashboard
                                </NavLink>
                                
                                <!-- Admin/Owner Navigation -->
                                <template v-if="isAdmin || isOwner">
                                    <NavLink href="/businesses" :active="isCurrentRoute('businesses')">
                                        Businesses
                                    </NavLink>
                                    <NavLink href="/branches" :active="isCurrentRoute('branches')">
                                        Branches
                                    </NavLink>
                                    <NavLink href="/sellers" :active="isCurrentRoute('sellers')">
                                        Sellers
                                    </NavLink>
                                </template>

                                <!-- Common Navigation (View Only for Sellers) -->
                                <NavLink href="/products" :active="isCurrentRoute('products')">
                                    Products
                                </NavLink>
                                <!-- <NavLink href="/inventory" :active="isCurrentRoute('inventory')">
                                    Inventory
                                </NavLink> -->
                                <NavLink href="/inventory-items" :active="isCurrentRoute('inventory-items')">
                                    Inventory
                                </NavLink>
                                <NavLink href="/discounts" :active="isCurrentRoute('discounts')">
                                    Discounts
                                </NavLink>
                                <NavLink href="/sales" :active="isCurrentRoute('sales')">
                                    Sales
                                </NavLink>

                                <!-- Admin/Owner Only -->
                                <template v-if="isAdmin || isOwner">
                                    <NavLink href="/reports" :active="isCurrentRoute('reports')">
                                        Reports
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ user.name }}

                                                <svg
                                                    class="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink href="/profile"> Profile </DropdownLink>
                                        <DropdownLink as="button" @click="logout"> Log Out </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
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

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink href="/dashboard" :active="isCurrentRoute('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        
                        <!-- Admin/Owner Navigation -->
                        <template v-if="isAdmin || isOwner">
                            <ResponsiveNavLink href="/businesses" :active="isCurrentRoute('businesses')">
                                Businesses
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href="/branches" :active="isCurrentRoute('branches')">
                                Branches
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href="/sellers" :active="isCurrentRoute('sellers')">
                                Sellers
                            </ResponsiveNavLink>
                        </template>

                        <!-- Common Navigation (View Only for Sellers) -->
                        <ResponsiveNavLink href="/products" :active="isCurrentRoute('products')">
                            Products
                        </ResponsiveNavLink>
                        <!-- <ResponsiveNavLink href="/inventory" :active="isCurrentRoute('inventory')">
                            Inventory
                        </ResponsiveNavLink> -->
                        <ResponsiveNavLink href="/inventory-items" :active="isCurrentRoute('inventory-items')">
                            Inventory
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href="/discounts" :active="isCurrentRoute('discounts')">
                            Discounts
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href="/sales" :active="isCurrentRoute('sales')">
                            Sales
                        </ResponsiveNavLink>

                        <!-- Admin/Owner Only -->
                        <template v-if="isAdmin || isOwner">
                            <ResponsiveNavLink href="/reports" :active="isCurrentRoute('reports')">
                                Reports
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">{{ user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink href="/profile"> Profile </ResponsiveNavLink>
                            <ResponsiveNavLink href="#" as="button" @click="logout"> Log Out </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
