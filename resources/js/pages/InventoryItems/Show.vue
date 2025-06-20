<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { BreadcrumbItemType } from '@/types';

interface Product {
    id: number;
    price: number;
    stock: number;
    business: {
        id: number;
        name: string;
    };
}

interface InventoryItem {
    id: number;
    name: string;
    description: string | null;
    brand: string | null;
    model: string | null;
    sku: string | null;
    barcode: string | null;
    upc: string | null;
    ean: string | null;
    isbn: string | null;
    mpn: string | null;
    unit: string | null;
    unit_display: string | null;
    image_url: string | null;
    created_by: {
        name: string;
    };
    last_updated_by: {
        name: string;
    };
    products: Product[];
}

interface Props {
    inventoryItem: InventoryItem;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Inventory Items',
        href: '/inventory-items',
    },
    {
        title: props.inventoryItem.name,
        href: `/inventory-items/${props.inventoryItem.id}`,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Product Item Details
                </h2>
                <Link
                    :href="`/inventory-items/${inventoryItem.id}/edit`"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Edit Item
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Basic Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Name</div>
                                <div class="mt-1">{{ inventoryItem.name }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">Brand</div>
                                <div class="mt-1">{{ inventoryItem.brand || '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">Model</div>
                                <div class="mt-1">{{ inventoryItem.model || '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">Description</div>
                                <div class="mt-1">{{ inventoryItem.description || '-' }}</div>
                            </div>

                            <div v-if="inventoryItem.image_url" class="md:col-span-2">
                                <div class="text-sm font-medium text-gray-500">Image</div>
                                <div class="mt-1">
                                    <img
                                        :src="`/storage/${inventoryItem.image_url}`"
                                        :alt="inventoryItem.name"
                                        class="h-48 w-48 object-cover rounded-lg"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Identifiers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Identifiers</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm font-medium text-gray-500">SKU</div>
                                <div class="mt-1">{{ inventoryItem.sku || '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">Barcode</div>
                                <div class="mt-1">{{ inventoryItem.barcode || '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">UPC</div>
                                <div class="mt-1">{{ inventoryItem.upc || '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">EAN</div>
                                <div class="mt-1">{{ inventoryItem.ean || '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">ISBN</div>
                                <div class="mt-1">{{ inventoryItem.isbn || '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500">MPN</div>
                                <div class="mt-1">{{ inventoryItem.mpn || '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Units -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Units</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Unit</div>
                                <div class="mt-1">{{ inventoryItem.unit_display || '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 