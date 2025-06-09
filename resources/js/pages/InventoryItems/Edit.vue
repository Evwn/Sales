<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItemType } from '@/types';

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
    unit_value: string | null;
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
    {
        title: 'Edit',
        href: `/inventory-items/${props.inventoryItem.id}/edit`,
    },
];

const form = useForm({
    name: props.inventoryItem.name,
    description: props.inventoryItem.description || '',
    brand: props.inventoryItem.brand || '',
    model: props.inventoryItem.model || '',
    sku: props.inventoryItem.sku || '',
    barcode: props.inventoryItem.barcode || '',
    upc: props.inventoryItem.upc || '',
    ean: props.inventoryItem.ean || '',
    isbn: props.inventoryItem.isbn || '',
    mpn: props.inventoryItem.mpn || '',
    unit: props.inventoryItem.unit || '',
    unit_value: props.inventoryItem.unit_value || '',
});

const submit = () => {
    form.put(`/inventory-items/${props.inventoryItem.id}`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Inventory Item
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                                
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    ></textarea>
                                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <div>
                                    <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                                    <Input
                                        id="brand"
                                        v-model="form.brand"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.brand" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.brand }}
                                    </div>
                                </div>

                                <div>
                                    <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                                    <Input
                                        id="model"
                                        v-model="form.model"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.model" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.model }}
                                    </div>
                                </div>
                            </div>

                            <!-- Identifiers -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Identifiers</h3>
                                
                                <div>
                                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                                    <Input
                                        id="sku"
                                        v-model="form.sku"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.sku" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.sku }}
                                    </div>
                                </div>

                                <div>
                                    <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                                    <Input
                                        id="barcode"
                                        v-model="form.barcode"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.barcode" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.barcode }}
                                    </div>
                                </div>

                                <div>
                                    <label for="upc" class="block text-sm font-medium text-gray-700">UPC</label>
                                    <Input
                                        id="upc"
                                        v-model="form.upc"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.upc" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.upc }}
                                    </div>
                                </div>

                                <div>
                                    <label for="ean" class="block text-sm font-medium text-gray-700">EAN</label>
                                    <Input
                                        id="ean"
                                        v-model="form.ean"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.ean" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.ean }}
                                    </div>
                                </div>

                                <div>
                                    <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN</label>
                                    <Input
                                        id="isbn"
                                        v-model="form.isbn"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.isbn" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.isbn }}
                                    </div>
                                </div>

                                <div>
                                    <label for="mpn" class="block text-sm font-medium text-gray-700">MPN</label>
                                    <Input
                                        id="mpn"
                                        v-model="form.mpn"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.mpn" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.mpn }}
                                    </div>
                                </div>
                            </div>

                            <!-- Units -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Units</h3>
                                
                                <div>
                                    <label for="unit" class="block text-sm font-medium text-gray-700">Unit</label>
                                    <Input
                                        id="unit"
                                        v-model="form.unit"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.unit" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.unit }}
                                    </div>
                                </div>

                                <div>
                                    <label for="unit_value" class="block text-sm font-medium text-gray-700">Unit Value</label>
                                    <Input
                                        id="unit_value"
                                        v-model="form.unit_value"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.unit_value" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.unit_value }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <Button type="submit" :disabled="form.processing">
                                Update Item
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 