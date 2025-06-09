<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

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
import Pagination from '@/Components/Pagination.vue';
import type { BreadcrumbItemType } from '@/types';

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
                <Link
                    :href="`/businesses/${sales.data[0]?.branch.business.id}/branches/${sales.data[0]?.branch.id}/sales/create`"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Add Sale
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Seller</TableHead>
                                        <TableHead>Branch</TableHead>
                                        <TableHead>Business</TableHead>
                                        <TableHead>Payment Method</TableHead>
                                        <TableHead class="text-right">Total</TableHead>
                                        <TableHead>Created At</TableHead>
                                        <TableHead class="w-[100px]">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="sale in sales.data" :key="sale.id">
                                        <TableCell>{{ new Date(sale.created_at).toLocaleString() }}</TableCell>
                                        <TableCell>KES {{ Number(sale.total_amount).toFixed(2) }}</TableCell>
                                        <TableCell>{{ sale.seller?.name || 'N/A' }}</TableCell>
                                        <TableCell>{{ sale.branch?.name || 'N/A' }}</TableCell>
                                        <TableCell>{{ sale.branch?.business?.name || 'N/A' }}</TableCell>
                                        <TableCell>{{ sale.payment_method }}</TableCell>
                                        <TableCell class="text-right">KES {{ Number(sale.total_amount).toFixed(2) }}</TableCell>
                                        <TableCell>{{ new Date(sale.created_at).toLocaleString() }}</TableCell>
                                        <TableCell class="text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link :href="`/sales/${sale.id}`">View</Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="sales.data.length === 0">
                                        <TableCell colspan="8" class="text-center">
                                            No sales found.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
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