<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import AppLayout from '@/layouts';
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
        title: 'Discounts',
        href: '/discounts',
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
    discounts: {
        data: Array<{
            id: number;
            name: string;
            percentage: number;
            start_date: string;
            end_date: string;
            business: {
                id: number;
                name: string;
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
    <AppLayout :title="'Discounts'" :user="page.props.auth.user">
        <Head title="Discounts" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Discounts
                </h2>
                <Link
                    v-if="isAdmin || isOwner"
                    href="/discounts/create"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Add Discount
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold">Discounts</h2>
                            <Link
                                v-if="isAdmin || isOwner"
                                href="/discounts/create"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Add Discount
                            </Link>
                        </div>

                        <Table>
                            <template #header>
                                <tr>
                                    <th>Name</th>
                                    <th>Percentage</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Business</th>
                                    <th v-if="isAdmin || isOwner">Actions</th>
                                </tr>
                            </template>
                            <template #default>
                                <tr v-for="discount in discounts.data" :key="discount.id">
                                    <td>{{ discount.name }}</td>
                                    <td>{{ discount.percentage }}%</td>
                                    <td>{{ new Date(discount.start_date).toLocaleDateString() }}</td>
                                    <td>{{ new Date(discount.end_date).toLocaleDateString() }}</td>
                                    <td>{{ discount.business.name }}</td>
                                    <td v-if="isAdmin || isOwner">
                                        <Link
                                            :href="`/discounts/${discount.id}/edit`"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            </template>
                        </Table>

                        <Pagination :links="discounts.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
