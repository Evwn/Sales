<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItemType } from '@/types';

const props = defineProps({
    business: {
        type: Object,
        required: true
    },
    branch: {
        type: Object,
        required: true
    }
});

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Branches',
        href: `/businesses/${props.business.id}/branches`,
    },
    {
        title: props.branch.name,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ branch.name }}
                </h2>
                <Link
                    :href="`/businesses/${business.id}/branches/${branch.id}/edit`"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Edit Branch
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Details</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ branch.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ branch.address }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ branch.phone }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Sellers</h3>
                                <div v-if="branch.sellers && branch.sellers.length > 0" class="space-y-4">
                                    <div v-for="seller in branch.sellers" :key="seller.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ seller.name }}</p>
                                            <p class="text-sm text-gray-500">{{ seller.email }}</p>
                                        </div>
                                        <Link
                                            :href="`/businesses/${business.id}/branches/${branch.id}/sellers/${seller.id}/edit`"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-500">No sellers assigned to this branch.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 