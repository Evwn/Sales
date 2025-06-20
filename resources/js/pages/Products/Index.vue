<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Swal from 'sweetalert2';

import AppLayout from '@/layouts';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import Pagination from '@/Components/Pagination.vue';

interface User {
    role: 'admin' | 'owner' | 'seller';
}

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}

const page = usePage<PageProps>();
const isAdmin = computed(() => page.props.auth.user.role === 'admin');
const isOwner = computed(() => page.props.auth.user.role === 'owner');

const props = defineProps<{
    business: {
        id: number;
        name: string;
    };
    products: {
        data: Array<{
            id: number;
            name: string;
            description: string;
            price: number;
            barcode: string;
            sku: string;
            stock: number;
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
}>();

const removeProduct = (product) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to remove ${product?.name || 'N/A'} from your inventory?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, remove',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/businesses/${props.business.id}/products/${product.id}`, {
                onSuccess: () => {
                    Swal.fire(
                        'Removed!',
                        'Item has been removed successfully form inventory.',
                        'success'
                    );
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Failed to remove item from inventory. Please try again.',
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
        <Head title="Inventory" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Inventory
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product?.name || 'N/A' }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.description }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">KES {{ product.price }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.barcode }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.stock }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.branch?.name || 'No branch assigned' }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
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
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="products.data.length === 0" class="hover:bg-gray-50">
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No Item found in thr inventory
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