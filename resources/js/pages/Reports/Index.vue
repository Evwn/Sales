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
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Pagination from '@/Components/Pagination.vue';

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
                                    <p class="text-2xl font-bold">${{ reports.total_revenue.toFixed(2) }}</p>
                                </CardContent>
                            </Card>
                        </div>

                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Business</TableHead>
                                        <TableHead>Branch</TableHead>
                                        <TableHead>Seller</TableHead>
                                        <TableHead>Products</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead class="w-[100px]">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="sale in reports.recent_sales" :key="sale.id">
                                        <TableCell>{{ new Date(sale.created_at).toLocaleDateString() }}</TableCell>
                                        <TableCell>{{ sale.branch?.business?.name || 'N/A' }}</TableCell>
                                        <TableCell>{{ sale.branch?.name || 'N/A' }}</TableCell>
                                        <TableCell>{{ sale.seller?.name || 'N/A' }}</TableCell>
                                        <TableCell>{{ sale.products?.map(p => p.name).join(', ') || 'N/A' }}</TableCell>
                                        <TableCell>KES {{ Number(sale.total_amount).toFixed(2) }}</TableCell>
                                        <TableCell>
                                            <Button v-if="sale.branch?.id" variant="ghost" size="sm" asChild>
                                                <Link :href="`/branches/${sale.branch.id}/sales/${sale.id}`">
                                                    View
                                                </Link>
                                            </Button>
                                            <span v-else class="text-gray-400">N/A</span>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="reports.recent_sales.length === 0">
                                        <TableCell colspan="7" class="text-center">
                                            No sales found.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 