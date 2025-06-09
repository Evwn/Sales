<template>
  <AppLayout>
    <Head title="Businesses" />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Businesses
        </h2>
        <Link
          href="/businesses/create"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Add Business
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
                    <TableHead>Name</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead class="w-[100px]">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="business in businesses" :key="business.id">
                    <TableCell>{{ business.name }}</TableCell>
                    <TableCell>{{ business.description }}</TableCell>
                    <TableCell>
                      <Button variant="ghost" size="sm" asChild>
                        <Link :href="`/businesses/${business.id}`">
                          View
                        </Link>
                      </Button>
                      <Button variant="ghost" size="sm" asChild>
                        <Link :href="`/businesses/${business.id}/edit`">
                          Edit
                        </Link>
                      </Button>
                    </TableCell>
                  </TableRow>
                  <TableRow v-if="businesses.length === 0">
                    <TableCell colspan="3" class="text-center text-muted-foreground">
                      No businesses found.
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

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

interface Business {
  id: number;
  name: string;
  description: string | null;
  created_at: string;
  updated_at: string;
}

defineProps<{
  businesses: Business[];
}>();

defineOptions({
  name: 'Businesses'
});
</script> 