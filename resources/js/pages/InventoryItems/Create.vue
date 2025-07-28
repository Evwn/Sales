<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Collapsible } from '@/components/ui/collapsible/Collapsible.vue';
import CollapsibleTrigger from '@/components/ui/collapsible/CollapsibleTrigger.vue';
import CollapsibleContent from '@/components/ui/collapsible/CollapsibleContent.vue';
import type { BreadcrumbItemType } from '@/types';
import { ref, computed, onUnmounted } from 'vue';

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Inventory Items',
        href: '/inventory-items',
    },
    {
        title: 'Create',
        href: '/inventory-items/create',
    },
];

const form = useForm({
    name: '',
    description: '',
    brand: '',
    model: '',
    sku: '',
    barcode: '',
    upc: '',
    ean: '',
    isbn: '',
    mpn: '',
    unit: '',
    unit_value: '',
    image: null as File | null,
    // Add fields for variants, taxes, modifiers, discounts
    variants: [],
    is_taxable: true,
    tax_rate: '',
    modifiers: [],
    discounts: [],
});

const imagePreviewUrl = ref<string | null>(null);
let objectUrl: string | null = null;

const submit = () => {
    form.post('/inventory-items', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.image = target.files[0];
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
        }
        objectUrl = URL.createObjectURL(target.files[0]);
        imagePreviewUrl.value = objectUrl;
    } else {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        imagePreviewUrl.value = null;
    }
};

onUnmounted(() => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Inventory Item
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Info -->
                    <Collapsible :default-open="true">
                        <CollapsibleTrigger>
                            <div class="flex items-center gap-2 cursor-pointer">
                                <span class="font-bold text-lg">General Information</span>
                            </div>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                    <Input id="name" v-model="form.name" placeholder="e.g., iPhone 14, Nike Air Max" required />
                                </div>
                                <div>
                                    <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                                    <input type="file" id="image" @change="handleImageChange" accept="image/*" class="block w-full" />
                                    <div v-if="imagePreviewUrl" class="mt-2">
                                        <img :src="imagePreviewUrl" alt="Image Preview" class="h-24 w-auto rounded shadow border" />
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="description" v-model="form.description" rows="2" class="block w-full rounded border-gray-300" placeholder="Key features or details"></textarea>
                                </div>
                                <div>
                                    <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                                    <Input id="brand" v-model="form.brand" placeholder="e.g., Samsung, Nike" />
                                </div>
                                <div>
                                    <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                                    <Input id="model" v-model="form.model" placeholder="e.g., Galaxy S21, HP 15-ef2025nr" />
                                </div>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>

                    <!-- Product IDs & Barcodes -->
                    <Collapsible :default-open="false">
                        <CollapsibleTrigger>
                            <div class="flex items-center gap-2 cursor-pointer">
                                <span class="font-bold text-lg">Product IDs & Barcodes</span>
                            </div>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                                <div>
                                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                                    <Input id="sku" v-model="form.sku" placeholder="Unique code for inventory" />
                                </div>
                                <div>
                                    <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                                    <Input id="barcode" v-model="form.barcode" placeholder="e.g., EAN, UPC, etc." />
                                </div>
                                <div>
                                    <label for="upc" class="block text-sm font-medium text-gray-700">UPC</label>
                                    <Input id="upc" v-model="form.upc" placeholder="12-digit code (USA)" />
                                </div>
                                <div>
                                    <label for="ean" class="block text-sm font-medium text-gray-700">EAN</label>
                                    <Input id="ean" v-model="form.ean" placeholder="13-digit code (global)" />
                                </div>
                                <div>
                                    <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN</label>
                                    <Input id="isbn" v-model="form.isbn" placeholder="For books" />
                                </div>
                                <div>
                                    <label for="mpn" class="block text-sm font-medium text-gray-700">MPN</label>
                                    <Input id="mpn" v-model="form.mpn" placeholder="Manufacturer part number" />
                                </div>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>

                    <!-- Quantity & Pricing -->
                    <Collapsible :default-open="true">
                        <CollapsibleTrigger>
                            <div class="flex items-center gap-2 cursor-pointer">
                                <span class="font-bold text-lg">Quantity & Pricing</span>
                            </div>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label for="unit" class="block text-sm font-medium text-gray-700">Unit Type</label>
                                    <Input id="unit" v-model="form.unit" placeholder="e.g., piece, pack, kg, liter" />
                                </div>
                                <div>
                                    <label for="unit_value" class="block text-sm font-medium text-gray-700">Unit Price</label>
                                    <Input id="unit_value" v-model="form.unit_value" type="number" step="0.01" min="0" placeholder="KES per item or per kg" />
                                </div>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>

                    <!-- Variants -->
                    <Collapsible :default-open="false">
                        <CollapsibleTrigger>
                            <div class="flex items-center gap-2 cursor-pointer">
                                <span class="font-bold text-lg">Variants</span>
                            </div>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <div class="mt-4 text-gray-500">Add color, size, or other variants here. (Coming soon)</div>
                        </CollapsibleContent>
                    </Collapsible>

                    <!-- Taxes -->
                    <Collapsible :default-open="false">
                        <CollapsibleTrigger>
                            <div class="flex items-center gap-2 cursor-pointer">
                                <span class="font-bold text-lg">Taxes</span>
                            </div>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Is Taxable?</label>
                                    <select v-model="form.is_taxable" class="block w-full rounded border-gray-300">
                                        <option :value="true">Yes</option>
                                        <option :value="false">No</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="tax_rate" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                                    <Input id="tax_rate" v-model="form.tax_rate" type="number" step="0.01" min="0" placeholder="e.g., 16" />
                                </div>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>

                    <!-- Modifiers -->
                    <Collapsible :default-open="false">
                        <CollapsibleTrigger>
                            <div class="flex items-center gap-2 cursor-pointer">
                                <span class="font-bold text-lg">Modifiers</span>
                            </div>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <div class="mt-4 text-gray-500">Select or add modifiers (e.g., toppings, add-ons). (Coming soon)</div>
                        </CollapsibleContent>
                    </Collapsible>

                    <!-- Discounts -->
                    <Collapsible :default-open="false">
                        <CollapsibleTrigger>
                            <div class="flex items-center gap-2 cursor-pointer">
                                <span class="font-bold text-lg">Discounts</span>
                            </div>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <div class="mt-4 text-gray-500">Select or add discounts. (Coming soon)</div>
                        </CollapsibleContent>
                    </Collapsible>

                    <div class="mt-6 flex justify-end">
                        <Button type="submit" :disabled="form.processing">
                            Create Item
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template> 
