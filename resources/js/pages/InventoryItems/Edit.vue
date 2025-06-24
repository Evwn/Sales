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
    image_url: string | null;
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
    image: null as File | null,
});

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.image = target.files[0];
    }
};

const submit = () => {
    form.put(`/inventory-items/${props.inventoryItem.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
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
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Product Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Product Information</h3>
                                <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-4">
                                        <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                        <div class="mt-1">
                                            <input
                                                type="text"
                                                id="name"
                                                v-model="form.name"
                                                placeholder="Enter the product name (e.g., iPhone 14, Nike Air Max)"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                                        <div class="mt-1">
                                            <input
                                                type="file"
                                                id="image"
                                                @change="handleImageChange"
                                                accept="image/*"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            />
                                        </div>
                                        <div v-if="inventoryItem.image_url" class="mt-2">
                                            <img
                                                :src="`/storage/${inventoryItem.image_url}`"
                                                :alt="inventoryItem.name"
                                                class="h-32 w-32 object-cover rounded-lg"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Product Description & Brand -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900">Product Description & Brand</h3>
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">Product Description</label>
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="3"
                                            placeholder="What the product is, including key features or details."
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        ></textarea>
                                    </div>
                                    <div>
                                        <label for="brand" class="block text-sm font-medium text-gray-700">Brand Name</label>
                                        <Input
                                            id="brand"
                                            v-model="form.brand"
                                            type="text"
                                            placeholder="Manufacturer or company name (e.g., Samsung, Nike)"
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <label for="model" class="block text-sm font-medium text-gray-700">Model Number/Name</label>
                                        <Input
                                            id="model"
                                            v-model="form.model"
                                            type="text"
                                            placeholder="Product model code or name (e.g., Galaxy S21, HP 15-ef2025nr)"
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                </div>
                                <!-- Product ID & Barcodes -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900">Product ID & Barcodes</h3>
                                    <div>
                                        <label for="sku" class="block text-sm font-medium text-gray-700">Stock Keeping Unit (SKU)</label>
                                        <Input
                                            id="sku"
                                            v-model="form.sku"
                                            type="text"
                                            placeholder="Unique code you assign to track inventory."
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode Number</label>
                                        <Input
                                            id="barcode"
                                            v-model="form.barcode"
                                            type="text"
                                            placeholder="Code that can be scanned (can be EAN, UPC, etc.)"
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <label for="upc" class="block text-sm font-medium text-gray-700">UPC (Barcode Type)</label>
                                        <Input
                                            id="upc"
                                            v-model="form.upc"
                                            type="text"
                                            placeholder="Universal Product Code — 12-digit standard used mostly in the USA."
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <label for="ean" class="block text-sm font-medium text-gray-700">EAN (Barcode Type)</label>
                                        <Input
                                            id="ean"
                                            v-model="form.ean"
                                            type="text"
                                            placeholder="European Article Number — 13-digit barcode used globally."
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN (for Books)</label>
                                        <Input
                                            id="isbn"
                                            v-model="form.isbn"
                                            type="text"
                                            placeholder="Used only for books. International Standard Book Number."
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <label for="mpn" class="block text-sm font-medium text-gray-700">Manufacturer Part Number</label>
                                        <Input
                                            id="mpn"
                                            v-model="form.mpn"
                                            type="text"
                                            placeholder="Code the manufacturer gives the product."
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                </div>
                                <!-- Quantity & Pricing -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900">Quantity & Pricing</h3>
                                    <div>
                                        <label for="unit" class="block text-sm font-medium text-gray-700">Unit Type</label>
                                        <Input
                                            id="unit"
                                            v-model="form.unit"
                                            type="text"
                                            placeholder="The type of unit (e.g., piece, pack, kg, liter)."
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <label for="unit_value" class="block text-sm font-medium text-gray-700">Unit Price</label>
                                        <Input
                                            id="unit_value"
                                            v-model="form.unit_value"
                                            type="text"
                                            placeholder="Price per one unit (e.g., KES per item or KES per kg)."
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <Button type="submit" class="btn btn-primary">Save</Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
