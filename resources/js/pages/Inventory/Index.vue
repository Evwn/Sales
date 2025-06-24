<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

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
            threshold: number;
            product: {
                id: number;
                name: string;
                price: number;
                sku: string;
                inventoryItem: {
                    name: string;
                    sku: string;
                };
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
    loading.value = true;
    router.get('/api/inventory/search', { query: '' }, {
        headers: { 'Accept': 'application/json' },
        onSuccess: (response) => {
            if (response.data && Array.isArray(response.data)) {
                inventoryItems.value = response.data.filter(item => {
                    if (seen.has(item.name)) {
                        return false;
                    }
                    seen.add(item.name);
                    return true;
                });
            } else {
                error.value = 'Invalid response format';
            }
            loading.value = false;
        },
        onError: (errors) => {
            error.value = 'Failed to load inventory items';
            loading.value = false;
        }
    });
});

const handleDelete = async (id: number) => {
    if (await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    })) {
        router.delete(`/inventory/${id}`, {
            onSuccess: () => {
                Swal.fire(
                    'Deleted!',
                    'Inventory item has been deleted.',
                    'success'
                );
            }
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
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
                        <h3 class="text-lg font-semibold mb-4">All Inventory Items</h3>
                        
                        <div v-if="inventory.data.length === 0" class="text-center py-4 text-gray-500">
                            No inventory items found. Go to Inventory to add items to your business.
                        </div>
                        
                        <div v-else>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>SKU</TableHead>
                                        <TableHead>Stock</TableHead>
                                        <TableHead>Business</TableHead>
                                        <TableHead>Branch</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="item in inventory.data" :key="item.id">
                                        <TableCell>{{ item.product.inventoryItem.name }}</TableCell>
                                        <TableCell>KES {{ item.product.price }}</TableCell>
                                        <TableCell>{{ item.product.inventoryItem.sku }}</TableCell>
                                        <TableCell>{{ item.quantity }}</TableCell>
                                        <TableCell>{{ item.branch.business.name }}</TableCell>
                                        <TableCell>{{ item.branch.name }}</TableCell>
                                        <TableCell>
                                            <div class="flex space-x-2">
                                                <Link
                                                    :href="`/inventory/${item.id}`"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    v-if="isAdmin || isOwner"
                                                    :href="`/inventory/${item.id}/edit`"
                                                    class="text-yellow-600 hover:text-yellow-800"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    v-if="isAdmin || isOwner"
                                                    @click="handleDelete(item.id)"
                                                    class="text-red-600 hover:text-red-800"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                            
                            <div class="mt-4">
                                <Pagination :links="inventory.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
