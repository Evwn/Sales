<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Pagination from '@/components/Pagination.vue';
import type { BreadcrumbItemType } from '@/types';
import Swal from 'sweetalert2';

interface User {
    id: number;
    name: string;
    email: string;
    role: {
        id: number;
        name: string;
        description: string;
        permissions: string[];
    };
    branch_id: number | null;
    business_id: number | null;
}

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}

const page = usePage<PageProps>();
const isSeller = computed(() => page.props.auth.user.role.name === 'seller');
const isOwner = computed(() => page.props.auth.user.role.name === 'owner');

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
            reference: string;
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

const formatCurrency = (amount) => {
    if (isNaN(amount) || amount === null || amount === undefined) {
        return 'KES 0.00';
    }
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
};

const handleView = (sale) => {
    Swal.fire({
        title: 'Sale Details',
        html: `
            <div class="text-left">
                <p><strong>Reference:</strong> ${sale.reference}</p>
                <p><strong>Date:</strong> ${formatDate(sale.created_at)}</p>
                <p><strong>Seller:</strong> ${sale.seller?.name || 'N/A'}</p>
                <p><strong>Amount:</strong> ${formatCurrency(sale.total_amount)}</p>
                <p><strong>Payment Method:</strong> ${sale.payment_method?.toUpperCase() || 'N/A'}</p>
                <p><strong>Branch:</strong> ${sale.branch?.name || 'N/A'}</p>
                    </div>
        `,
        confirmButtonText: 'Close',
        confirmButtonColor: '#3085d6',
    });
};
</script>

<template>
    <AppLayout title="Sales">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Sales
                </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Sales List -->
                    <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seller</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.reference }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(sale.created_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCurrency(sale.total_amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.seller?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.payment_method?.toUpperCase() || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <button
                                                @click="handleView(sale)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                        <div v-if="sales.links && sales.links.length > 0" class="mt-6">
                        <Pagination :links="sales.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
