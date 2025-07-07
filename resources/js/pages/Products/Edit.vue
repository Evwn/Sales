<template>
    <AppLayout>
        <Head title="Edit Product" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <form @submit.prevent="form.put(`/branches/${branch.id}/products/${product.id}`)">
                            <div class="space-y-6">
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">KES</span>
                                        </div>
                                        <input
                                            type="number"
                                            id="price"
                                            v-model="form.price"
                                            step="0.01"
                                            min="0"
                                            class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            required
                                        />
                                    </div>
                                    <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.price }}
                                    </div>
                                </div>

                                <div>
                                    <label for="buying_price" class="block text-sm font-medium text-gray-700">Buying Price</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">KES</span>
                                        </div>
                                        <input
                                            type="number"
                                            id="buying_price"
                                            v-model="form.buying_price"
                                            step="0.01"
                                            min="0"
                                            class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            required
                                        />
                                    </div>
                                    <div v-if="form.errors.buying_price" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.buying_price }}
                                    </div>
                                </div>

                                <div>
                                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                                    <input
                                        type="number"
                                        id="stock"
                                        v-model="form.stock"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    />
                                    <div v-if="form.errors.stock" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.stock }}
                                    </div>
                                </div>

                                <div>
                                    <label for="min_stock_level" class="block text-sm font-medium text-gray-700">Minimum Stock Level</label>
                                    <input
                                        type="number"
                                        id="min_stock_level"
                                        v-model="form.min_stock_level"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    />
                                    <div v-if="form.errors.min_stock_level" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.min_stock_level }}
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1">Tax Group</label>
                                    <select v-model="form.tax_group_id" class="border px-2 py-1 w-full">
                                        <option value="">None</option>
                                        <option v-for="tax in taxGroups" :key="tax.id" :value="tax.id">{{ tax.code }} - {{ tax.description }} ({{ tax.rate }}%)</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" v-model="form.tax_enabled" class="mr-2" />
                                        Tax Enabled
                                    </label>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <Link
                                        :href="`/branches/${branch.id}/products`"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        :disabled="form.processing"
                                    >
                                        Update Product
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    branch: {
        type: Object,
        required: true
    },
    product: {
        type: Object,
        required: true
    },
    taxGroups: {
        type: Array,
        default: []
    }
});

const form = useForm({
    price: props.product.price,
    buying_price: props.product.buying_price,
    stock: props.product.stock,
    min_stock_level: props.product.min_stock_level,
    tax_group_id: props.product.tax_group_id || '',
    tax_enabled: !!props.product.tax_enabled
});

const taxGroups = ref(props.taxGroups || []);
</script> 
