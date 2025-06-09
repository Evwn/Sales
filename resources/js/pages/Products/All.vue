<script setup lang="ts">
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import type { SweetAlertOptions } from 'sweetalert2';

import AppLayout from '@/layouts';
import Pagination from '@/components/Pagination.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItemType } from '@/types';

interface User {
    role: 'admin' | 'owner' | 'seller';
}

interface PageProps {
    auth: {
        user: User;
    };
    businesses: Array<{
        id: number;
        name: string;
    }>;
    products: {
        data: Array<{
            id: number;
            name: string;
            description: string;
            price: number;
            barcode: string;
            sku: string;
            stock: number;
            business: {
                id: number;
                name: string;
            };
            branch: {
                id: number;
                name: string;
            } | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    [key: string]: any; // Add index signature
}

const page = usePage<PageProps>();
const isAdmin = computed(() => page.props.auth.user.role === 'admin');
const isOwner = computed(() => page.props.auth.user.role === 'owner');

const props = defineProps<{
    businesses: PageProps['businesses'];
    products: PageProps['products'];
}>();

const selectedBusiness = ref<string>('all');
const searchQuery = ref('');
const isProcessing = ref(false);

const filteredProducts = computed(() => {
    if (!searchQuery.value && selectedBusiness.value === 'all') {
        return props.products.data;
    }
    return props.products.data.filter(product => {
        const matchesSearch = !searchQuery.value || 
            product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            product.sku.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesBusiness = selectedBusiness.value === 'all' || 
            product.business.id === Number(selectedBusiness.value);
        return matchesSearch && matchesBusiness;
    });
});

const handleSell = async (product: PageProps['products']['data'][0]) => {
    const options: SweetAlertOptions = {
        title: 'Enter Quantity',
        input: 'number',
        inputLabel: `Selling ${product.name}`,
        inputValue: 1,
        inputAttributes: {
            min: '1',
            max: product.stock.toString(),
            step: '1'
        },
        showCancelButton: true,
        inputValidator: (value: string) => {
            const numValue = parseInt(value);
            if (!value) {
                return 'Please enter a quantity';
            }
            if (numValue < 1) {
                return 'Quantity must be at least 1';
            }
            if (numValue > product.stock) {
                return 'Quantity cannot exceed available stock';
            }
            return undefined;
        }
    };

    const { value: quantity } = await Swal.fire(options);

    if (quantity) {
        isProcessing.value = true;
        const form = useForm({
            product_id: product.id,
            quantity: parseInt(quantity),
            total_amount: product.price * parseInt(quantity),
            payment_method: 'cash',
            branch_id: product.branch?.id,
            business_id: product.business.id
        });

        try {
            await form.post('/sales', {
                preserveScroll: true,
                onSuccess: () => {
                    // Update the product's stock locally
                    product.stock -= parseInt(quantity);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Sale Successful!',
                        text: `Sold ${quantity} ${product.name} for KES ${(product.price * parseInt(quantity)).toFixed(2)}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Refresh the products list
                    router.reload({ only: ['products'] });
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errors.message || 'Failed to process sale'
                    });
                },
                onFinish: () => {
                    isProcessing.value = false;
                }
            });
        } catch (error) {
            isProcessing.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred'
            });
        }
    }
};

const removeProduct = (product: PageProps['products']['data'][0]) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to remove ${product.name} from ${product.business.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, remove product',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/businesses/${product.business.id}/products/${product.id}`, {
                onSuccess: () => {
                    Swal.fire(
                        'Removed!',
                        'Product has been removed successfully.',
                        'success'
                    );
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Failed to remove product. Please try again.',
                        'error'
                    );
                }
            });
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head title="All Products" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    All Products
                </h2>
                <div class="flex items-center space-x-4">
                    <Select v-model="selectedBusiness">
                        <SelectTrigger class="w-[200px]">
                            <SelectValue :value="selectedBusiness" placeholder="Filter by business" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Businesses</SelectItem>
                            <SelectItem
                                v-for="business in businesses"
                                :key="business.id"
                                :value="business.id.toString()"
                            >
                                {{ business.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="mb-6">
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search products by name or SKU..."
                                class="w-full"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th scope="col" class="w-1/7 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="w-1/7 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="w-1/7 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                        <th scope="col" class="w-1/7 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th scope="col" class="w-1/7 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                        <th scope="col" class="w-1/7 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                        <th scope="col" class="w-1/7 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50">
                                        <td class="w-1/7 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.name }}</td>
                                        <td class="w-1/7 px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">KES {{ Number(product.price).toFixed(2) }}</td>
                                        <td class="w-1/7 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
                                        <td class="w-1/7 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.stock }}</td>
                                        <td class="w-1/7 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.business.name }}</td>
                                        <td class="w-1/7 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.branch?.name || 'No branch assigned' }}</td>
                                        <td class="w-1/7 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <template v-if="page.props.auth.user.role === 'seller'">
                                                    <button
                                                        class="text-blue-600 hover:text-blue-900"
                                                        :disabled="isProcessing || product.stock <= 0"
                                                        @click="handleSell(product)"
                                                    >
                                                        {{ isProcessing ? 'Processing...' : 'Sell' }}
                                                    </button>
                                                </template>
                                                <template v-else>
                                                    <Link
                                                        :href="`/products/${product.id}`"
                                                        class="text-blue-600 hover:text-blue-900"
                                                    >
                                                        View
                                                    </Link>
                                                    <Link
                                                        :href="`/products/${product.id}/edit`"
                                                        class="text-blue-600 hover:text-blue-900"
                                                    >
                                                        Edit
                                                    </Link>
                                                    <button
                                                        @click="removeProduct(product)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredProducts.length === 0" class="hover:bg-gray-50">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No products found. Go to Inventory to add products to your business.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            <Pagination :links="products.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 