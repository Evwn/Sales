<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';

interface Sale {
    id: number;
    total_amount: number;
    created_at: string;
    seller: {
        name: string;
    };
}

interface Stats {
    total_sales: number;
    sales_count: number;
    average_sale: number;
    sales_today: Sale[];
    changes: {
        sales: number;
        count: number;
        average: number;
    };
}

const props = defineProps<{
    stats: Stats;
}>();

defineOptions({
    name: 'Dashboard'
});
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Total Sales Today</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">KES {{ stats.total_sales.toFixed(2) }}</div>
                            <div class="text-sm" :class="stats.changes.sales >= 0 ? 'text-green-600' : 'text-red-600'">
                                <span class="flex items-center gap-1">
                                    <ArrowUp v-if="stats.changes.sales >= 0" class="size-3" />
                                    <ArrowDown v-else class="size-3" />
                                    {{ Math.abs(stats.changes.sales) }}%
                                </span>
                                vs. yesterday
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Sales Count</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.sales_count }}</div>
                            <div class="text-sm" :class="stats.changes.count >= 0 ? 'text-green-600' : 'text-red-600'">
                                <span class="flex items-center gap-1">
                                    <ArrowUp v-if="stats.changes.count >= 0" class="size-3" />
                                    <ArrowDown v-else class="size-3" />
                                    {{ Math.abs(stats.changes.count) }}%
                                </span>
                                vs. yesterday
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Average Sale</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">KES {{ stats.average_sale.toFixed(2) }}</div>
                            <div class="text-sm" :class="stats.changes.average >= 0 ? 'text-green-600' : 'text-red-600'">
                                <span class="flex items-center gap-1">
                                    <ArrowUp v-if="stats.changes.average >= 0" class="size-3" />
                                    <ArrowDown v-else class="size-3" />
                                    {{ Math.abs(stats.changes.average) }}%
                                </span>
                                vs. yesterday
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Sales Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Sales</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Time</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Seller</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="sale in stats.sales_today" :key="sale.id">
                                    <TableCell>{{ new Date(sale.created_at).toLocaleTimeString() }}</TableCell>
                                    <TableCell>KES {{ Number(sale.total_amount).toFixed(2) }}</TableCell>
                                    <TableCell>{{ sale.seller.name }}</TableCell>
                                </TableRow>
                                <TableRow v-if="stats.sales_today.length === 0">
                                    <TableCell colspan="3" class="text-center text-muted-foreground">
                                        No sales today
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
