<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Pagination from '@/Components/Pagination.vue';
import type { BreadcrumbItemType } from '@/types';

const props = defineProps<{
    reports: {
        total_sales: number;
        total_revenue: number;
        recent_sales: Array<{
            id: number;
            total_amount: number;
            created_at: string;
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
    };
}>();
</script>

<template>
    <AppLayout>
        <Head title="Reports" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reports
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="grid gap-4 md:grid-cols-2 mb-8">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Total Sales</CardTitle>
                                    <CardDescription>Number of sales across all branches</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <p class="text-2xl font-bold">{{ reports.total_sales }}</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Total Revenue</CardTitle>
                                    <CardDescription>Revenue from all sales</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <p class="text-2xl font-bold">kes {{ reports.total_revenue.toFixed(2) }}</p>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Reports Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in reports.recent_sales" :key="sale.id" class="hover:bg-gray-50">
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(sale.created_at).toLocaleDateString() }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.branch?.business?.name || 'N/A' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.branch?.name || 'N/A' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.seller?.name || 'N/A' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.products?.map(p => p.name).join(', ') || 'N/A' }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES {{ Number(sale.total_amount).toFixed(2) }}</td>
                                        <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <Link
                                                :href="`/sales/${sale.id}`"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="reports.recent_sales.length === 0" class="hover:bg-gray-50">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No sales found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 