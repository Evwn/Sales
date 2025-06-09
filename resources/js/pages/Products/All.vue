<script setup lang="ts">
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import type { SweetAlertOptions } from 'sweetalert2';

import AppLayout from '@/layouts';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import Pagination from '@/components/Pagination.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItemType } from '@/types';

interface User {
    role: 'admin' | 'owner' | 'seller';
}

interface PageProps {
    auth: {
        user: User;
    };
    businesses: Array<{
        id: number;
        name: string;
    }>;
    products: {
        data: Array<{
            id: number;
            name: string;
            description: string;
            price: number;
            barcode: string;
            sku: string;
            stock: number;
            business: {
                id: number;
                name: string;
            };
            branch: {
                id: number;
                name: string;
            } | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    [key: string]: any; // Add index signature
}

const page = usePage<PageProps>();
const isAdmin = computed(() => page.props.auth.user.role === 'admin');
const isOwner = computed(() => page.props.auth.user.role === 'owner');

const props = defineProps<{
    businesses: PageProps['businesses'];
    products: PageProps['products'];
}>();

const selectedBusiness = ref<string>('all');
const searchQuery = ref('');
const isProcessing = ref(false);

const filteredProducts = computed(() => {
    if (!searchQuery.value && selectedBusiness.value === 'all') {
        return props.products.data;
    }
    return props.products.data.filter(product => {
        const matchesSearch = !searchQuery.value || 
            product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            product.sku.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesBusiness = selectedBusiness.value === 'all' || 
            product.business.id === Number(selectedBusiness.value);
        return matchesSearch && matchesBusiness;
    });
});

const handleSell = async (product: PageProps['products']['data'][0]) => {
    const options: SweetAlertOptions = {
        title: 'Enter Quantity',
        input: 'number',
        inputLabel: `Selling ${product.name}`,
        inputValue: 1,
        inputAttributes: {
            min: '1',
            max: product.stock.toString(),
            step: '1'
        },
        showCancelButton: true,
        inputValidator: (value: string) => {
            const numValue = parseInt(value);
            if (!value) {
                return 'Please enter a quantity';
            }
            if (numValue < 1) {
                return 'Quantity must be at least 1';
            }
            if (numValue > product.stock) {
                return 'Quantity cannot exceed available stock';
            }
            return undefined;
        }
    };

    const { value: quantity } = await Swal.fire(options);

    if (quantity) {
        isProcessing.value = true;
        const form = useForm({
            product_id: product.id,
            quantity: parseInt(quantity),
            total_amount: product.price * parseInt(quantity),
            payment_method: 'cash',
            branch_id: product.branch?.id,
            business_id: product.business.id
        });

        try {
            await form.post('/sales', {
                preserveScroll: true,
                onSuccess: () => {
                    // Update the product's stock locally
                    product.stock -= parseInt(quantity);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Sale Successful!',
                        text: `Sold ${quantity} ${product.name} for KES ${(product.price * parseInt(quantity)).toFixed(2)}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Refresh the products list
                    router.reload({ only: ['products'] });
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errors.message || 'Failed to process sale'
                    });
                },
                onFinish: () => {
                    isProcessing.value = false;
                }
            });
        } catch (error) {
            isProcessing.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred'
            });
        }
    }
};

const removeProduct = (product: PageProps['products']['data'][0]) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to remove ${product.name} from ${product.business.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, remove product',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/businesses/${product.business.id}/products/${product.id}`, {
                onSuccess: () => {
                    Swal.fire(
                        'Removed!',
                        'Product has been removed successfully.',
                        'success'
                    );
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Failed to remove product. Please try again.',
                        'error'
                    );
                }
            });
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head title="All Products" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    All Products
                </h2>
                <div class="flex items-center space-x-4">
                    <Select v-model="selectedBusiness">
                        <SelectTrigger class="w-[200px]">
                            <SelectValue :value="selectedBusiness" placeholder="Filter by business" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Businesses</SelectItem>
                            <SelectItem
                                v-for="business in businesses"
                                :key="business.id"
                                :value="business.id.toString()"
                            >
                                {{ business.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="mb-6">
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search products by name or SKU..."
                                class="w-full"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>SKU</TableHead>
                                        <TableHead>Stock</TableHead>
                                        <TableHead>Business</TableHead>
                                        <TableHead>Branch</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="product in filteredProducts" :key="product.id">
                                        <TableCell>{{ product.name }}</TableCell>
                                        <TableCell class="text-right">KES {{ Number(product.price).toFixed(2) }}</TableCell>
                                        <TableCell>{{ product.sku }}</TableCell>
                                        <TableCell>{{ product.stock }}</TableCell>
                                        <TableCell>{{ product.business.name }}</TableCell>
                                        <TableCell>{{ product.branch?.name || 'No branch assigned' }}</TableCell>
                                        <TableCell class="text-right">
                                            <template v-if="page.props.auth.user.role === 'seller'">
                                                <Button 
                                                    variant="default" 
                                                    size="sm"
                                                    :disabled="isProcessing || product.stock <= 0"
                                                    @click="handleSell(product)"
                                                >
                                                    {{ isProcessing ? 'Processing...' : 'Sell' }}
                                                </Button>
                                            </template>
                                            <template v-else>
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link :href="`/products/${product.id}`">View</Link>
                                                </Button>
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link :href="`/products/${product.id}/edit`">Edit</Link>
                                                </Button>
                                                <Button variant="ghost" size="sm" @click="removeProduct(product)">Delete</Button>
                                            </template>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="filteredProducts.length === 0">
                                        <TableCell colspan="7" class="text-center">
                                            No products found. Go to Inventory to add products to your business.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div class="mt-6">
                            <Pagination :links="products.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 