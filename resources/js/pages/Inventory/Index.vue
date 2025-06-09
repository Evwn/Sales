<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

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
import Pagination from '@/components/Pagination.vue';
import type { BreadcrumbItemType } from '@/types';

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Inventory',
        href: '/inventory',
    },
];

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
    inventory: {
        data: Array<{
            id: number;
            quantity: number;
            updated_at: string;
            product: {
                id: number;
                name: string;
                price: number;
            };
            branch: {
                id: number;
                name: string;
                business: {
                    id: number;
                    name: string;
                };
            };
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();

const inventoryItems = ref([]);
const loading = ref(true);
const error = ref(null);

// Fetch all inventory items on mount
onMounted(() => {
    console.log('Component mounted, fetching inventory items...');
    loading.value = true;
    router.get('/api/inventory/search', { query: '' }, {
        headers: { 'Accept': 'application/json' },
        onSuccess: (response) => {
            console.log('Raw API Response:', response);
            console.log('Response data type:', typeof response.data);
            console.log('Is array?', Array.isArray(response.data));
            
            if (response.data && Array.isArray(response.data)) {
                console.log('Number of items before filtering:', response.data.length);
                // Remove duplicates by name
                const seen = new Set();
                inventoryItems.value = response.data.filter(item => {
                    console.log('Processing item:', item);
                    if (seen.has(item.name)) {
                        console.log('Duplicate found:', item.name);
                        return false;
                    }
                    seen.add(item.name);
                    return true;
                });
                console.log('Number of items after filtering:', inventoryItems.value.length);
                console.log('Final filtered items:', inventoryItems.value);
            } else {
                console.error('Invalid response format:', response);
                error.value = 'Invalid response format';
            }
            loading.value = false;
        },
        onError: (errors) => {
            console.error('API Error:', errors);
            console.error('Error details:', {
                message: errors.message,
                status: errors.status,
                response: errors.response
            });
            error.value = 'Failed to load inventory items';
            loading.value = false;
        }
    });
});
</script>

<template>
    <AppLayout>
        <Head title="Inventory" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Inventory
                </h2>
                <Link
                    v-if="isAdmin || isOwner"
                    :href="'/inventory/create'"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Add Inventory
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">All Inventory Items (Unique Names)</h3>
                        <div v-if="loading" class="text-center py-4">
                            Loading inventory items...
                        </div>
                        <div v-else-if="error" class="text-red-500 py-4">
                            {{ error }}
                        </div>
                        <div v-else-if="inventoryItems.length === 0" class="text-center py-4 text-gray-500">
                            No inventory items found
                        </div>
                        <div v-else class="overflow-x-auto mb-8">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in inventoryItems" :key="item.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.brand || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.model || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.sku || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.unit_display || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold">Inventory</h2>
                            <Link
                                v-if="isAdmin || isOwner"
                                :href="'/inventory/create'"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Add Inventory
                            </Link>
                        </div>

                        <Table>
                            <template #header>
                                <tr>
                                    <th>Product</th>
                                    <th>Branch</th>
                                    <th>Quantity</th>
                                    <th>Last Updated</th>
                                    <th v-if="isAdmin || isOwner">Actions</th>
                                </tr>
                            </template>
                            <template #default>
                                <tr v-for="item in inventory.data" :key="item.id">
                                    <td>{{ item.product.name }}</td>
                                    <td>{{ item.branch.name }}</td>
                                    <td>{{ item.quantity }}</td>
                                    <td>{{ new Date(item.updated_at).toLocaleString() }}</td>
                                    <td v-if="isAdmin || isOwner">
                                        <Link
                                            :href="`/inventory/${item.id}/edit`"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            </template>
                        </Table>

                        <Pagination :links="inventory.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 