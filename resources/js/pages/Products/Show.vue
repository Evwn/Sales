<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItemType } from '@/types';

const props = defineProps<{
    branch: {
        id: number;
        name: string;
    };
    product: {
        id: number;
        name: string;
        description: string;
        price: number;
        buying_price: number;
        stock: number;
        barcode: string;
        branch: {
            id: number;
            name: string;
        } | null;
        inventoryItem: {
            name: string;
            description: string;
            barcode: string;
        };
        min_stock_level: number;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Inventory',
        href: '/products',
    },
    {
        title: props.product?.name || 'N/A',
        href: `/branches/${props.branch.id}/products/${props.product.id}`,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventory Item Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">inventory Information</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.inventoryItem.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.inventoryItem.description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Barcode</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.inventoryItem.barcode }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Information</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Price</dt>
                                        <dd class="mt-1 text-sm text-gray-900">KES {{ product.price }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Buying Price</dt>
                                        <dd class="mt-1 text-sm text-gray-900">KES {{ product.buying_price }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Stock</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.stock }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Minimum Stock Level</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.min_stock_level }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Branch</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.branch?.name || 'No branch assigned' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-4">
                            <Button
                                :href="`/branches/${branch.id}/products/${product.id}/edit`"
                                variant="outline"
                            >
                                Edit inventory
                            </Button>
                            <Button
                                :href="`/branches/${branch.id}/products`"
                                variant="outline"
                            >
                                Back to inventory
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
