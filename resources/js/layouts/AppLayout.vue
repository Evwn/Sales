<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import type { BreadcrumbItemType } from "@/types";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { ChevronDown, User as UserIcon, LogOut, Download, Menu, X } from 'lucide-vue-next';
import NavLink from "@/components/NavLink.vue";
import ApplicationLogo from "@/components/ApplicationLogo.vue";
import Dropdown from '@/components/Dropdown/Dropdown.vue';
import DropdownLink from '@/components/Dropdown/DropdownLink.vue';
import ResponsiveNavLink from '@/components/ResponsiveNavLink.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string;
    roles: {
        id: number;
        name: string;
        permissions: string[];
    }[];
    permissions: string[];
    all_permissions: string[];
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
const user = computed(() => page.props.auth.user);

const isSeller = computed(() => {
    return page.props.auth?.user?.roles?.some(role => role.name === 'seller');
});

const isOwner = computed(() => {
    return page.props.auth?.user?.roles?.some(role => role.name === 'owner');
});

const isAdmin = computed(() => 
    page.props.auth?.user?.roles?.some(role => role.name === 'admin')
);

// Update permission checks to match RoleSeeder permissions
const hasPermission = (permission: string) => {
    return page.props.auth?.user?.all_permissions?.includes(permission) ?? false;
};

// Update nav link permission checks to match RoleSeeder
const canViewDashboard = computed(() => hasPermission('view shift report') || isOwner.value || isAdmin.value);
const canViewSales = computed(() => hasPermission('view all receipts') || isOwner.value || isAdmin.value);
const canViewBusinesses = computed(() => hasPermission('manage feature settings') || isOwner.value || isAdmin.value);
const canViewBranches = computed(() => hasPermission('manage POS devices') || isOwner.value || isAdmin.value);
const canViewEmployers = computed(() => hasPermission('manage employees') || isOwner.value || isAdmin.value);
const canViewInventory = computed(() => hasPermission('manage items') || isOwner.value || isAdmin.value);
const canViewPurchases = computed(() => hasPermission('view cost of items') || isOwner.value || isAdmin.value);
const canViewStockTransfers = computed(() => hasPermission('manage items') || isOwner.value || isAdmin.value);
const canViewItems = computed(() => hasPermission('manage items') || isOwner.value || isAdmin.value);
const canViewCategories = computed(() => hasPermission('manage items') || isOwner.value || isAdmin.value);
const canViewModifiers = computed(() => hasPermission('manage items') || isOwner.value || isAdmin.value);
const canViewDiscounts = computed(() => hasPermission('apply discounts with restricted access') || isOwner.value || isAdmin.value);
const canViewReports = computed(() => hasPermission('view sales reports') || isOwner.value || isAdmin.value);
const canViewSuppliers = computed(() => hasPermission('manage suppliers') || isOwner.value || isAdmin.value);

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

const canManageDevices = computed(() => {
    return isOwner.value || isAdmin.value || hasPermission('manage devices');
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
    router.post('/logout', {}, {
        onSuccess: () => {
            // Force a page reload after logout to ensure clean state
            window.location.href = '/';
        },
        onError: (errors) => {
            // Still try to redirect even if there's an error
            window.location.href = '/';
        }
    });
};

const deferredPrompt = ref(null);
const isAppInstalled = ref(false);

// Listen for the beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt.value = e;
});

// Listen for appinstalled event
window.addEventListener('appinstalled', () => {
    isAppInstalled.value = true;
    deferredPrompt.value = null;
});

function showInstallPrompt() {
    if (deferredPrompt.value) {
        deferredPrompt.value.prompt();
        deferredPrompt.value.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                isAppInstalled.value = true;
            }
            deferredPrompt.value = null;
        });
    }
}

function openAIAssistant() {
  Swal.fire({
    title: 'AI Assistant',
    input: 'text',
    inputLabel: 'Ask about your business, branches, sellers, profit, etc.',
    inputPlaceholder: 'E.g. Which products are low in stock?',
    showCancelButton: true,
    confirmButtonText: 'Ask',
    preConfirm: async (question) => {
      if (!question) return 'Please enter a question.';
      const response = await fetch('/api/ai-assistant', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ question }),
        credentials: 'same-origin'
      });
      const data = await response.json();
      // Always show the backend's message, fallback to a friendly message if truly empty
      return (typeof data.answer === 'string' && data.answer.trim()) ? data.answer : 'We will be back soon.';
    },
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),
    position: 'bottom-end',
    customClass: {
      popup: 'ai-swal-popup',
      confirmButton: 'ai-swal-confirm',
      cancelButton: 'ai-swal-cancel',
      input: 'ai-swal-input',
    },
    width: 400
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'AI Answer',
        html: `<div style=\"text-align:left;max-width:350px\">${result.value}</div>`,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 15000,
        width: 400,
        customClass: {
          popup: 'ai-swal-popup',
        }
      });
    }
  });
}

const unreadCount = ref(0);

const fetchUnreadCount = async () => {
  try {
    const response = await axios.get('/chat/unread-count');
    unreadCount.value = response.data.unread_count;
  } catch (error) {
  }
};

// Global toast logic for Inertia flash messages
function handleGlobalFlash() {
  if (page.props.flash) {
    if (page.props.flash.success) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: page.props.flash.success,
        showConfirmButton: false,
        timer: 4000,
      });
    }
    if (page.props.flash.error) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: page.props.flash.error,
        showConfirmButton: false,
        timer: 4000,
      });
    }
  }
}

onMounted(() => {
  fetchUnreadCount();
  // Poll for unread count every 30 seconds
  setInterval(fetchUnreadCount, 30000);
  handleGlobalFlash();
});

watch(() => page.props.flash, () => {
  handleGlobalFlash();
});
</script>

<template>
    <Head :title="title" />
    <div>
        <span
            v-if="isOwner"
            :class="['fixed z-50 rounded-full border-2 border-white transition-colors', isOffline ? 'bg-red-500' : 'bg-green-500']"
            style="width: 16px; height: 16px; bottom: 4.5rem; right: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
            title="{{ isOffline ? 'Offline' : 'Online' }}"
        ></span>
        <div class="min-h-screen bg-[url('images/reading.png')] background bg-cover bg-center">
            <nav class="bg-white/1 border-b border-gray-100 backdrop-blur-sm overflow-hidden">
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
                                <template v-if="isOwner || isSeller">
                                    <NavLink v-if="canViewDashboard" href="/dashboard">
                                        <Link href="/dashboard" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/dashboard') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                            <svg class="w-6 h-6 mb-1" fill="none"  stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0H7m6 0h6" /></svg>
                                            <span class="leading-tight">Dashboard</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canViewBusinesses" href="/businesses">
                                        <Link href="/businesses" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/businesses') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                            <span class="leading-tight">Businesses</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canViewBranches" href="/branches">
                                        <Link href="/branches" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/branches') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            <span class="leading-tight">Branches</span>
                                        </Link>

                                    </NavLink>
                                    <DropdownMenu v-if="canViewInventory" class="ml-2">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-1 text-white/90 hover:text-gray-800">
                                                <span class=" leading-tight font-medium">Employers</span>
                                                <ChevronDown class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-48 backdrop-blur-sm bg-white/1">
                                            <DropdownMenuItem as-child>
                                                <Link href="/employers" :class="{'font-bold text-blue-600': isCurrentRoute('/employers')}">Employers</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/employers/access-control" :class="{'font-bold text-blue-600': isCurrentRoute('/employers/access-control')}">Access Control</Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    <DropdownMenu v-if="canViewInventory" class="ml-2">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-1 text-white/90 hover:text-gray-800">
                                                <span class=" leading-tight font-medium">Inventory</span>
                                                <ChevronDown class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-48 backdrop-blur-sm bg-white/1">
                                            <DropdownMenuItem as-child>
                                                <Link href="/purchases" :class="{'font-bold text-blue-600': isCurrentRoute('/purchases')}">Purchases</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/purchase-items" :class="{'font-bold text-blue-600': isCurrentRoute('/purchase-items')}">Purchase Items</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/stock-transfers" :class="{'font-bold text-blue-600': isCurrentRoute('/stock-transfers')}">Stock Transfers</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/stock-adjustment" :class="{'font-bold text-blue-600': isCurrentRoute('/stock-adjustment')}">Stock Adjustment</Link>
                                            </DropdownMenuItem>                                            
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    <DropdownMenu v-if="canViewInventory" class="ml-2">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-1 text-white/90 hover:text-gray-700">
                                                <span class=" leading-tight font-medium">Items</span>
                                                <ChevronDown class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-48 backdrop-blur-sm bg-white/1">
                                            <DropdownMenuItem as-child>
                                                <Link href="/items" :class="{'font-bold text-blue-600': isCurrentRoute('/items')}">Items</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/categories" :class="{'font-bold text-blue-600': isCurrentRoute('/categories')}">Categories</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/modifiers" :class="{'font-bold text-blue-600': isCurrentRoute('/modifiers')}">Modifiers</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/discounts" :class="{'font-bold text-blue-600': isCurrentRoute('/discounts')}">Discounts</Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    <NavLink v-if="canViewReports" href="/devices">
                                      <Link href="/reports" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/reports') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                            <span class="leading-tight">Reports</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canViewSuppliers" href="/suppliers">
                                      <Link href="/suppliers" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/suppliers') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                            <span class=" leading-tight">Suppliers</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canManageDevices" href="/devices">
                                      <Link href="/devices" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/devices') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                            <span class="leading-tight">Devices</span>
                                        </Link>
                                    </NavLink>
                                    </template>
                                    <!-- Admin-specific links -->
                                    <template v-else-if="isAdmin">
                                        <NavLink v-if="hasPermission('view_users')" href="/users" :active="isCurrentRoute('/users')" class="text-gray-900 hover:text-gray-700">
                                            Users
                                        </NavLink>
                                        <NavLink v-if="hasPermission('manage_business')" href="/admin/businesses" :active="isCurrentRoute('/admin/businesses')" class="text-gray-900 hover:text-gray-700">
                                            All Businesses
                                        </NavLink>
                                        <NavLink  href="/admin/branches" :active="isCurrentRoute('/admin/branches')" class="text-gray-900 hover:text-gray-700">
                                            All Branches
                                        </NavLink>
                                        <NavLink v-if="hasPermission('view_reports')" href="/reports" :active="isCurrentRoute('/reports')" class="text-gray-900 hover:text-gray-700">
                                            Reports
                                        </NavLink>
                                        <NavLink href="/admin/tax-groups" :active="isCurrentRoute('/admin/tax-groups')" class="text-gray-900 hover:text-gray-700">
                                          Tax Codes
                                        </NavLink>
                                        <NavLink href="/test" :active="isCurrentRoute('/test')" class="text-gray-900 hover:text-gray-700">
                                            Flutterwave Test
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
                                            <Button variant="ghost" class="flex items-center gap-2 hover:opacity-80 text-white/90">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="hidden sm:inline">{{ page.props.auth?.user?.name }}</span>
                                                    <ChevronDown class="size-4" />
                                                </div>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-56 bg-white/1 backdrop-blur-sm">
                                            <DropdownMenuItem as-child>
                                                <Link href="/settings/profile" class="flex items-center gap-2">
                                                    <UserIcon class="size-4" />
                                                    <span>Profile</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/settings" class="flex items-center gap-2">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                    <span>Settings</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem v-if="deferredPrompt && !isAppInstalled" @click="showInstallPrompt" class="flex items-center gap-2 text-blue-600 hover:text-blue-800 cursor-pointer">
                                                <Download class="size-4" />
                                                <span>Download App</span>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/chat" class="flex items-center gap-2 hover:text-blue-600 relative">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                    <span>Chat</span>
                                                    <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                                                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                                                    </span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <button @click="handleLogout" class="flex items-center gap-2 text-red-800 w-full text-left">
                                                    <LogOut class="size-4" />
                                                    <span>Logout</span>
                                                </button>
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
                                    <Link href="/settings/profile" class="w-full block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                                        Profile
                                    </Link>
                                    <Link href="/settings" class="w-full block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                                        Settings
                                    </Link>
                                    <Link href="/chat" class="w-full block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg relative">
                                        Chat
                                        <span v-if="unreadCount > 0" class="absolute top-2 right-2 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                                        </span>
                                    </Link>
                                    <button @click="handleLogout" class="w-full block px-4 py-3 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg">
                                        Logout
                                    </button>
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

        <!-- AI Button (bottom right, above mobile nav) -->
        <div v-if="isOwner" class="fixed bottom-20 right-4 z-50">
            <button
                @click="openAIAssistant"
                class="bg-blue-600 text-white rounded-full p-3 shadow-lg hover:bg-blue-700 transition"
                title="Ask AI about your business"
                style="box-shadow: 0 4px 24px rgba(37,99,235,0.2); font-size: 1.5rem;"
            >
                🧠
            </button>
        </div>
    </div>

    <!-- Bottom Scrollable Nav for Mobile -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-[#800000]/60 backdrop-blur-sm border-t border-gray-200 sm:hidden flex overflow-x-auto" style="box-shadow: 0 -2px 8px rgba(0,0,0,0.04);">
        <div class="flex flex-row w-full overflow-x-auto no-scrollbar">
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
            <template v-else-if="isOwner">
                 <NavLink v-if="canViewDashboard" href="/dashboard">
                                        <Link href="/dashboard" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/dashboard') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                            <svg class="w-6 h-6 mb-1" fill="none"  stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0H7m6 0h6" /></svg>
                                            <span class="leading-tight">Dashboard</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canViewBusinesses" href="/businesses">
                                        <Link href="/businesses" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/businesses') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                            <span class="leading-tight">Businesses</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canViewBranches" href="/branches">
                                        <Link href="/branches" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/branches') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            <span class="leading-tight">Branches</span>
                                        </Link>

                                    </NavLink>
                                    <DropdownMenu v-if="canViewInventory" class="ml-2">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-1 text-white/90 hover:text-gray-800">
                                                <span class=" leading-tight font-medium">Employers</span>
                                                <ChevronDown class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-48 backdrop-blur-sm bg-white/1">
                                            <DropdownMenuItem as-child>
                                                <Link href="/employers" :class="{'font-bold text-blue-600': isCurrentRoute('/employers')}">Employers</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/employers/access-control" :class="{'font-bold text-blue-600': isCurrentRoute('/employers/access-control')}">Access Control</Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    <DropdownMenu v-if="canViewInventory" class="ml-2">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-1 text-white/90 hover:text-gray-800">
                                                <span class=" leading-tight font-medium">Inventory</span>
                                                <ChevronDown class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-48 backdrop-blur-sm bg-white/1">
                                            <DropdownMenuItem as-child>
                                                <Link href="/purchases" :class="{'font-bold text-blue-600': isCurrentRoute('/purchases')}">Purchases</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/purchase-items" :class="{'font-bold text-blue-600': isCurrentRoute('/purchase-items')}">Purchase Items</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/stock-transfers" :class="{'font-bold text-blue-600': isCurrentRoute('/stock-transfers')}">Stock Transfers</Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    <DropdownMenu v-if="canViewInventory" class="ml-2">
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-1 text-white/90 hover:text-gray-700">
                                                <span class=" leading-tight font-medium">Items</span>
                                                <ChevronDown class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-48 backdrop-blur-sm bg-white/1">
                                            <DropdownMenuItem as-child>
                                                <Link href="/items" :class="{'font-bold text-blue-600': isCurrentRoute('/items')}">Items</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/categories" :class="{'font-bold text-blue-600': isCurrentRoute('/categories')}">Categories</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/modifiers" :class="{'font-bold text-blue-600': isCurrentRoute('/modifiers')}">Modifiers</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/discounts" :class="{'font-bold text-blue-600': isCurrentRoute('/discounts')}">Discounts</Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    <NavLink v-if="canViewReports" href="/devices">
                                      <Link href="/reports" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/reports') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                            <span class="leading-tight">Reports</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canViewSuppliers" href="/suppliers">
                                      <Link href="/suppliers" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/suppliers') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                            <span class=" leading-tight">Suppliers</span>
                                        </Link>
                                    </NavLink>
                                    <NavLink v-if="canManageDevices" href="/devices">
                                      <Link href="/devices" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/devices') ? 'text-black font-bold' : 'text-white/90 font-medium'">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                            <span class="leading-tight">Devices</span>
                                        </Link>
                                    </NavLink>
                                     <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="flex items-center gap-2 hover:opacity-80 text-white/90">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="hidden sm:inline">{{ page.props.auth?.user?.name}}</span>
                                                    <ChevronDown class="size-4" />
                                                </div>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :align="dropdownAlign" class="w-56 bg-white/1 backdrop-blur-sm">
                                            <DropdownMenuItem as-child>
                                                <Link href="/settings/profile" class="flex items-center gap-2">
                                                    <UserIcon class="size-4" />
                                                    <span>Profile</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/settings" class="flex items-center gap-2">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                    <span>Settings</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem v-if="deferredPrompt && !isAppInstalled" @click="showInstallPrompt" class="flex items-center gap-2 text-blue-600 hover:text-blue-800 cursor-pointer">
                                                <Download class="size-4" />
                                                <span>Download App</span>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link href="/chat" class="flex items-center gap-2 hover:text-blue-600 relative">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                    <span>Chat</span>
                                                    <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                                                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                                                    </span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <button @click="handleLogout" class="flex items-center gap-2 text-red-800 w-full text-left">
                                                    <LogOut class="size-4" />
                                                    <span>Logout</span>
                                                </button>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    </template>
                                    <!-- Admin-specific links -->
                                    <template v-else-if="isAdmin">
                                        <NavLink v-if="hasPermission('view_users')" href="/users" :active="isCurrentRoute('/users')" class="text-gray-900 hover:text-gray-700">
                                            Users
                                        </NavLink>
                                        <NavLink v-if="hasPermission('manage_business')" href="/admin/businesses" :active="isCurrentRoute('/admin/businesses')" class="text-gray-900 hover:text-gray-700">
                                            All Businesses
                                        </NavLink>
                                        <NavLink  href="/admin/branches" :active="isCurrentRoute('/admin/branches')" class="text-gray-900 hover:text-gray-700">
                                            All Branches
                                        </NavLink>
                                        <NavLink v-if="hasPermission('view_reports')" href="/reports" :active="isCurrentRoute('/reports')" class="text-gray-900 hover:text-gray-700">
                                            Reports
                                        </NavLink>
                                        <NavLink href="/admin/tax-groups" :active="isCurrentRoute('/admin/tax-groups')" class="text-gray-900 hover:text-gray-700">
                                          Tax Codes
                                        </NavLink>
                                        <NavLink href="/test" :active="isCurrentRoute('/test')" class="text-gray-900 hover:text-gray-700">
                                            Flutterwave Test
                                        </NavLink>
            </template>
            <template v-else-if="isAdmin">
                <Link href="/users" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/users') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" /></svg>
                    <span class="text-xs leading-tight font-medium">Users</span>
                </Link>
                <Link href="/admin/businesses" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/admin/businesses') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <span class="text-xs leading-tight font-medium">All Businesses</span>
                </Link>
                <Link href="/admin/branches" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/admin/branches') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    <span class="text-xs leading-tight font-medium">All Branches</span>
                </Link>
                <Link href="/products" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/products') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" /></svg>
                    <span class="text-xs leading-tight font-medium">Inventory</span>
                </Link>
                <Link href="/inventory-items" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/inventory-items') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" /></svg>
                    <span class="text-xs leading-tight font-medium">Products</span>
                </Link>
                <Link href="/reports" class="flex flex-col items-center flex-shrink-0 px-4 py-2" :class="isCurrentRoute('/reports') ? 'text-blue-600' : 'text-gray-500'">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4m0 0V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" /></svg>
                    <span class="text-xs leading-tight font-medium">Reports</span>
                </Link>
            </template>
            <Link v-if="$page.props.auth.user && $page.props.auth.user.roles.includes('admin') && hasPermission('not_active')" href="/admin/tax-groups" class="nav-link">
              Tax Codes
            </Link>
            
        </div>
    </nav>
</template>

<script lang="ts">
export default {
    name: 'AppLayout'
};
</script>

<style>
.ai-swal-popup {
  border-radius: 1rem !important;
  box-shadow: 0 8px 32px rgba(37,99,235,0.15) !important;
  padding-bottom: 1.5rem !important;
}
.ai-swal-confirm {
  background: #2563eb !important;
  color: #fff !important;
  border-radius: 0.5rem !important;
  font-weight: 600 !important;
}
.ai-swal-cancel {
  border-radius: 0.5rem !important;
}
.ai-swal-input {
  border-radius: 0.5rem !important;
  border: 1px solid #2563eb !important;
}
.background{
    background-image: url('/images/reading.png');
    background-size: cover;
    background-position: center;
}

</style>
