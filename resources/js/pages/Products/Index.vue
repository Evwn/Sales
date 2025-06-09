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
        text: `Do you want to remove ${product.name} from your products?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, remove product',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/businesses/${props.business.id}/products/${product.id}`, {
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
        <Head title="Products" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Products
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>SKU</TableHead>
                                        <TableHead>Barcode</TableHead>
                                        <TableHead>Stock</TableHead>
                                        <TableHead>Branch</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="product in products.data" :key="product.id">
                                        <TableCell>{{ product.name }}</TableCell>
                                        <TableCell>{{ product.description }}</TableCell>
                                        <TableCell class="text-right">KES {{ product.price }}</TableCell>
                                        <TableCell>{{ product.sku }}</TableCell>
                                        <TableCell>{{ product.barcode }}</TableCell>
                                        <TableCell>{{ product.stock }}</TableCell>
                                        <TableCell>{{ product.branch?.name || 'No branch assigned' }}</TableCell>
                                        <TableCell>
                                            <div class="flex space-x-3">
                                                <Link
                                                    :href="`/businesses/${business.id}/products/${product.id}`"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="`/businesses/${business.id}/products/${product.id}/edit`"
                                                    class="text-green-600 hover:text-green-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="removeProduct(product)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="products.data.length === 0">
                                        <TableCell colspan="8" class="text-center">
                                            No products found. Go to Inventory to add products to your business.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
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