<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import Pagination from '@/components/Pagination.vue';
import type { BreadcrumbItemType } from '@/types';
import { route } from '@/ziggy';

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Inventory Items',
        href: '/inventory-items',
    },
];

type RoleName = 'admin' | 'owner' | 'seller' | 'customer' | 'supplier';

interface Role {
    id: number;
    name: RoleName;
    description: string;
    permissions: string[];
}

interface User {
    id: number;
    name: string;
    email: string;
    role: Role;
    branch_id: number | null;
    business_id: number | null;
}

interface Branch {
    id: number;
    name: string;
    business: {
        id: number;
        name: string;
    };
}

interface PageProps {
    auth: {
        user: User;
    };
    businesses: Array<{
        id: number;
        name: string;
        description: string;
    }>;
    branches: Branch[];
    inventoryItems: {
        data: Array<{
            id: number;
            name: string;
            brand: string;
            model: string;
            sku: string;
            unit: number;
            updated_at: string;
            updated_by: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    [key: string]: any;
}

interface InventoryItem {
    id: number;
    name: string;
    brand: string | null;
    model: string | null;
    sku: string | null;
    barcode: string | null;
    upc: string | null;
    unit: string | null;
    unit_display: string | null;
    image_url: string | null;
    updated_at: string;
    last_updated_by: {
        name: string;
    };
}

interface Business {
    id: number;
    name: string;
    branches: Branch[];
}

interface Props {
    items: {
        data: InventoryItem[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    brands: string[];
    filters: {
        search?: string;
        brand?: string;
    };
    branches: Array<{
        id: number;
        name: string;
        business: {
            id: number;
            name: string;
        };
    }>;
    businesses: Business[];
}

const props = defineProps<Props>();
const page = usePage<PageProps>();

const search = ref(props.filters.search || '');
const selectedBrand = ref(props.filters.brand || '');

interface FormState {
    price: string;
    buying_price: string;
    stock: string;
    branch_id: string;
}

// Add form and modal state
const showAddToBusinessModal = ref(false);
const selectedBusiness = ref<Business | null>(null);
const form = ref<FormState>({
    price: '',
    buying_price: '',
    stock: '',
    branch_id: ''
});

watch([search, selectedBrand], ([newSearch, newBrand]) => {
    router.get(
        '/inventory-items',
        {
            search: newSearch,
            brand: newBrand,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
});

const addToBusiness = (item: InventoryItem) => {
    Swal.fire({
        title: 'Add to Branch',
        html: `
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
                    <select id="business_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select a business</option>
                        ${props.businesses.map((business: Business) => `
                            <option value="${business.id}">${business.name}</option>
                        `).join('')}
                    </select>
                </div>
                <div id="branch_container" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select a branch</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                        <input type="number" id="stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Stock Level</label>
                        <input type="number" id="min_stock_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buying Price</label>
                        <input type="number" id="buying_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                        <input type="number" id="price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required min="0" step="0.01">
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add to Branch',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        width: '32rem',
        customClass: {
            container: 'swal-wide',
            popup: 'swal-wide'
        },
        preConfirm: () => {
            const businessId = (document.getElementById('business_id') as HTMLSelectElement)?.value;
            const branchId = (document.getElementById('branch_id') as HTMLSelectElement)?.value;
            const stock = (document.getElementById('stock') as HTMLInputElement)?.value;
            const minStockLevel = (document.getElementById('min_stock_level') as HTMLInputElement)?.value;
            const buyingPrice = (document.getElementById('buying_price') as HTMLInputElement)?.value;
            const price = (document.getElementById('price') as HTMLInputElement)?.value;

            if (!businessId) {
                Swal.showValidationMessage('Please select a business');
                return false;
            }

            if (!branchId) {
                Swal.showValidationMessage('Please select a branch');
                return false;
            }

            if (!stock || !minStockLevel || !buyingPrice || !price) {
                Swal.showValidationMessage('Please fill in all fields');
                return false;
            }

            return {
                branch_id: branchId,
                stock,
                min_stock_level: minStockLevel,
                buying_price: buyingPrice,
                price
            };
        },
        didOpen: () => {
            const businessSelect = document.getElementById('business_id') as HTMLSelectElement;
            const branchContainer = document.getElementById('branch_container');
            const branchSelect = document.getElementById('branch_id') as HTMLSelectElement;

            if (businessSelect && branchContainer && branchSelect) {
                businessSelect.addEventListener('change', () => {
                    const selectedBusiness = props.businesses.find(b => b.id === parseInt(businessSelect.value));
                    branchContainer.classList.remove('hidden');
                    
                    // Clear and update branch options
                    branchSelect.innerHTML = '<option value="">Select a branch</option>';
                    
                    if (selectedBusiness && selectedBusiness.branches && selectedBusiness.branches.length > 0) {
                        selectedBusiness.branches.forEach(branch => {
                            const option = document.createElement('option');
                            option.value = branch.id.toString();
                            option.textContent = branch.name;
                            branchSelect.appendChild(option);
                        });
                        branchSelect.disabled = false;
                    } else {
                        branchSelect.innerHTML = '<option value="">No branches available</option>';
                        branchSelect.disabled = true;
                    }
                });
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Adding to Branch...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make the API call
            router.post(`/branches/${result.value.branch_id}/products`, {
                inventory_item_id: item.id,
                stock: result.value.stock,
                min_stock_level: result.value.min_stock_level,
                buying_price: result.value.buying_price,
                price: result.value.price
            }, {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Product added to branch successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errors.message || 'Failed to add product to branch',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
};

const isAdminOrOwner = computed(() => {
    const roles = page.props.auth.user.roles || [];
    return roles.some(role => role.name === 'admin' || role.name === 'owner');
});

const isSeller = computed(() => {
    const roles = page.props.auth.user.roles || [];
    return roles.some(role => role.name === 'seller');
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Central Product Items
                </h2>
                <Link
                    v-if="page.props.auth.user.role !== 'seller'"
                    href="/inventory-items/create"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Add New Item
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search and Filter Form -->
                        <div class="flex flex-wrap gap-4 mb-6">
                            <div class="flex-1">
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search items..."
                                    class="w-full"
                                />
                            </div>
                            <div class="w-48">
                                <select
                                    v-model="selectedBrand"
                                    class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                >
                                    <option value="">All Brands</option>
                                    <option v-for="brand in brands" :key="brand" :value="brand">
                                        {{ brand }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- product Items Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th scope="col" class="w-1/4 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                        <th v-if="page.props.auth.user.role !== 'seller'" scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50">
                                        <td class="w-1/4 px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.name }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.brand || '-' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.model || '-' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.sku || '-' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.unit_display || '-' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ new Date(item.updated_at).toLocaleDateString() }}</div>
                                            <div class="text-xs text-gray-500">
                                                by {{ item.last_updated_by ? item.last_updated_by.name : 'N/A' }}
                                            </div>
                                        </td>
                                        <td v-if="page.props.auth.user.role !== 'seller'" class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <Link
                                                    :href="`/inventory-items/${item.id}`"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    View
                                                </Link>
                                                <template v-if="isAdminOrOwner">
                                                    <Link
                                                        :href="`/inventory-items/${item.id}/edit`"
                                                        class="text-blue-600 hover:text-blue-900"
                                                    >
                                                        Edit
                                                    </Link>
                                                    <button
                                                        @click="addToBusiness(item)"
                                                        class="text-green-600 hover:text-green-900 cursor-pointer"
                                                        type="button"
                                                    >
                                                        Add to Business
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="items.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
