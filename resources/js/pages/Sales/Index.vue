<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

import AppLayout from '@/layouts';
import { Button } from '@/components/ui/button';
import Pagination from '@/Components/Pagination.vue';
import type { BreadcrumbItemType } from '@/types';

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
const isSeller = computed(() => page.props.auth.user.role === 'seller');

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Sales',
        href: '/sales',
    },
];

const props = defineProps<{
    sales: {
        data: Array<{
            id: number;
            total_amount: number;
            created_at: string;
            payment_method: string;
            seller: {
                id: number;
                name: string;
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
</script>

<template>
    <AppLayout>
        <Head title="Sales" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Sales
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <!-- Sales Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                        <th v-if="!isSeller" scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(sale.created_at).toLocaleString() }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES {{ Number(sale.total_amount).toFixed(2) }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.seller?.name || 'N/A' }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.branch?.name || 'N/A' }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.branch?.business?.name || 'N/A' }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.payment_method }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">KES {{ Number(sale.total_amount).toFixed(2) }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(sale.created_at).toLocaleString() }}</td>
                                        <td v-if="!isSeller" class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <Link
                                                    :href="`/sales/${sale.id}`"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="`/sales/${sale.id}/edit`"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    Edit
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination :links="sales.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 