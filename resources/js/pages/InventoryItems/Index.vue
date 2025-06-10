<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import Pagination from '@/components/Pagination.vue';
import type { BreadcrumbItemType } from '@/types';

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Inventory Items',
        href: '/inventory-items',
    },
];

interface User {
    role: 'admin' | 'owner' | 'seller';
    managedBranches: Array<{
        id: number;
        name: string;
        business: {
            id: number;
            name: string;
        };
    }>;
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
    updated_at: string;
    last_updated_by: {
        name: string;
    };
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

interface Business {
    id: number;
    name: string;
    description: string;
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

const addToBusiness = async (inventoryItem: InventoryItem) => {
    try {
        // Show business selection form
        const result = await Swal.fire({
            title: 'Add to Business',
            html: `
                <form id="addToBusinessForm" class="text-left">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
                        <select id="business_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a business</option>
                            ${page.props.businesses.map(business => `
                                <option value="${business.id}">${business.name}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div id="branch_container" class="mb-4 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch (Optional)</label>
                        <select id="branch_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a branch</option>
                        </select>
                    </div>
                    <div id="no_branches_message" class="mb-4 hidden text-sm text-yellow-600">
                        This business has no branches. You can still add the product to the business.
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                        <input type="number" id="stock" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="0" step="1" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buying Price</label>
                        <input type="number" id="buying_price" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="0" step="0.01" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                        <input type="number" id="price" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="0" step="0.01" required>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add to Business',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            didOpen: () => {
                const businessSelect = document.getElementById('business_id') as HTMLSelectElement;
                const branchSelect = document.getElementById('branch_id') as HTMLSelectElement;
                const branchContainer = document.getElementById('branch_container');
                const noBranchesMessage = document.getElementById('no_branches_message');
                
                if (businessSelect) {
                    businessSelect.addEventListener('change', () => {
                        const selectedBusiness = page.props.businesses.find(b => b.id === Number(businessSelect.value));
                        if (selectedBusiness) {
                            // Filter branches for the selected business
                            const branches = page.props.auth.user?.managedBranches.filter(branch => 
                                branch.business && branch.business.id === selectedBusiness.id
                            ) || [];
                            
                            if (branches.length > 0) {
                                branchContainer?.classList.remove('hidden');
                                noBranchesMessage?.classList.add('hidden');
                                branchSelect.innerHTML = branches.map(branch => `
                                    <option value="${branch.id}">${branch.name}</option>
                                `).join('');
                            } else {
                                branchContainer?.classList.add('hidden');
                                noBranchesMessage?.classList.remove('hidden');
                                branchSelect.innerHTML = '<option value="">No branches available</option>';
                            }
                        } else {
                            branchContainer?.classList.add('hidden');
                            noBranchesMessage?.classList.add('hidden');
                            branchSelect.innerHTML = '<option value="">Select a business first</option>';
                        }
                    });
                }
            },
            preConfirm: () => {
                const form = document.getElementById('addToBusinessForm') as HTMLFormElement;
                if (!form) return false;

                const businessId = (document.getElementById('business_id') as HTMLSelectElement)?.value;
                const branchId = (document.getElementById('branch_id') as HTMLSelectElement)?.value;
                const stock = (document.getElementById('stock') as HTMLInputElement)?.value;
                const buyingPrice = (document.getElementById('buying_price') as HTMLInputElement)?.value;
                const price = (document.getElementById('price') as HTMLInputElement)?.value;

                if (!businessId || !stock || !buyingPrice || !price) {
                    Swal.showValidationMessage('Please fill in all required fields');
                    return false;
                }

                return {
                    business_id: businessId,
                    branch_id: branchId || null,
                    stock: stock,
                    buying_price: buyingPrice,
                    price: price
                };
            }
        });

        if (result.isConfirmed && result.value) {
            // Show loading state
            Swal.fire({
                title: 'Adding Product...',
                text: 'Please wait while we add the product to your business.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                // Post the product
                await router.post(`/businesses/${result.value.business_id}/products`, {
                    inventory_item_id: inventoryItem.id,
                    business_id: Number(result.value.business_id),
                    price: result.value.price,
                    buying_price: result.value.buying_price,
                    stock: result.value.stock,
                    branch_id: result.value.branch_id
                }, {
                    onSuccess: () => {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Product added successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    onError: (errors) => {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errors.message || 'Failed to add product to business. Please try again.'
                        });
                    }
                });
            } catch (error) {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add product to business. Please try again.'
                });
            }
        }
    } catch (error) {
        // Show error message
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to add product to business. Please try again.'
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Central Inventory Items
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

                        <!-- Inventory Items Table -->
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
                                                by {{ item.last_updated_by.name }}
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