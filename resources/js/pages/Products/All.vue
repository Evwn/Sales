<script setup lang="ts">
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';
import type { SweetAlertOptions } from 'sweetalert2';
import html2pdf from 'html2pdf.js';
import html2canvas from 'html2canvas';
import axios from 'axios';

import AppLayout from '@/layouts';
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
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import Modal from '@/components/Modal.vue';
import SalesReceipt from '@/components/SalesReceipt.vue';

interface User {
    id: number;
    name: string;
    email: string;
    roles: {
        id: number;
        name: string;
        permissions: string[];
    }[];
    branch_id: number | null;
    business_id: number | null;
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
            buying_price: number;
            barcode: string;
            sku: string;
            stock: number;
            min_stock_level: number;
            business: {
                id: number;
                name: string;
            };
            branch: {
                id: number;
                name: string;
            } | null;
            inventory_item?: {
                image_url?: string;
            };
            image_url?: string;
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
const isAdmin = computed(() => page.props.auth.user.roles && page.props.auth.user.roles.some(role => role.name === 'admin'));
const isOwner = computed(() => page.props.auth.user.roles && page.props.auth.user.roles.some(role => role.name === 'owner'));
const isSeller = computed(() => page.props.auth.user.roles && page.props.auth.user.roles.some(role => role.name === 'seller'));

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: flash.success,
            timer: 2000,
            showConfirmButton: false
        });
    }
    if (flash?.error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: flash.error,
            confirmButtonText: 'OK'
        });
    }
}, { immediate: true });

const props = defineProps<{
    businesses: PageProps['businesses'];
    products: PageProps['products'];
}>();

const selectedBusiness = ref<string>('all');
const searchQuery = ref('');
const isProcessing = ref(false);
const barcodeInput = ref('');
const showBarcodeInput = ref(false);
const selectedSale = ref(null);
const showPreviewModal = ref(false);
const showEditModal = ref(false);
const editingProduct = ref(null);
const isUpdatingProduct = ref(false);

const barcodeBuffer = ref('');
const barcodeTimeout = ref<number | null>(null);
const isScanning = ref(false);
const lastKeyTime = ref(0);
const SCAN_TIMEOUT = 50; // Time in ms between keypresses to consider it a scan

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
        inputLabel: `Selling ${product?.name || 'N/A'}`,
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
        // Show loading Swal
        const loadingSwal = Swal.fire({
            title: 'Processing Sale...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            // Debug logging
            console.log('DEBUG: Sale processing started', {
                product: {
                    id: product.id,
                    name: product.name,
                    business_id: product.business?.id,
                    branch_id: product.branch?.id,
                    business: product.business,
                    branch: product.branch,
                    is_taxable: product.is_taxable,
                    tax_rate: product.tax_rate
                },
                user: page.props.auth.user,
                defaultCustomerId: page.props.defaultCustomerId,
                isSeller: isSeller.value,
                isOwner: isOwner.value,
                isAdmin: isAdmin.value
            });

            const saleUrl = (product.branch?.id && (isOwner.value || isAdmin.value)) 
                ? `/businesses/${product.business.id}/branches/${product.branch.id}/sales` 
                : '/sales';
            console.log('DEBUG: Using sale URL:', saleUrl);

            const saleData = {
                customer_id: page.props.defaultCustomerId, // Use default customer if available
                seller_id: page.props.auth.user.id,
                business_id: product.business.id,
                // Only include branch_id for owners/admins, sellers will have it auto-assigned
                ...(isOwner.value || isAdmin.value ? { branch_id: product.branch?.id } : {}),
                total_amount: product.price * parseInt(quantity),
                payment_method: 'cash',
                items: [{
                    product: {
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        barcode: product.barcode || '',
                        is_taxable: product.is_taxable || false,
                        tax_rate: product.tax_rate || 0
                    },
                    quantity: parseInt(quantity)
                }]
            };

            console.log('DEBUG: Sale data being sent:', saleData);
            console.log('DEBUG: About to make axios request to:', saleUrl);

            const response = await axios.post(saleUrl, saleData);
            console.log('DEBUG: Sale response received:', response);

            // Close loading Swal
            Swal.close();
            
            if (response.data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Sale Successful!',
                    text: `Sold ${quantity} ${product?.name || 'N/A'} for KES ${(product.price * parseInt(quantity)).toFixed(2)}`,
                    showConfirmButton: true,
                    confirmButtonText: 'View Receipt'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show receipt preview
                        selectedSale.value = response.data.sale;
                        showPreviewModal.value = true;
                    }
                });

                // Update the Inventory's stock locally
                product.stock -= parseInt(quantity);
                // Refresh the products list
                router.reload({ only: ['products'] });
            } else {
                throw new Error(response.data.error || 'Failed to process sale');
            }
        } catch (error) {
            // Close loading Swal
            Swal.close();
            
            // Debug error logging
            console.error('DEBUG: Sale processing error:', {
                error: error,
                errorMessage: error.message,
                errorResponse: error.response,
                errorResponseData: error.response?.data,
                errorResponseStatus: error.response?.status,
                errorResponseHeaders: error.response?.headers
            });

            let errorMessage = 'Failed to process sale. Please try again.';
            
            if (error.response?.data?.error) {
                errorMessage = error.response.data.error;
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            } else if (error.message) {
                errorMessage = error.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
        } finally {
            // Ensure loading state is cleared
            isProcessing.value = false;
            Swal.close();
        }
    }
};

const removeProduct = (product: PageProps['products']['data'][0]) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to remove ${product?.name || 'N/A'} from ${product.business?.name || 'N/A'}?`,
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
                        'Inventory has been removed successfully.',
                        'success'
                    );
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Failed to remove Inventory. Please try again.',
                        'error'
                    );
                }
            });
        }
    });
};

const editProduct = (product: PageProps['products']['data'][0]) => {
    if (!product.branch) {
        Swal.fire({
            icon: 'error',
            title: 'Cannot Edit',
            text: 'This product is not assigned to a branch and cannot be edited.',
            confirmButtonText: 'OK'
        });
        return;
    }
    editingProduct.value = { ...product };
    showEditModal.value = true;
};

const updateProduct = () => {
    if (!editingProduct.value) return;
    
    isUpdatingProduct.value = true;
    
    // Show loading Swal
    const loadingSwal = Swal.fire({
        title: 'Updating Product...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    router.put(`/branches/${editingProduct.value.branch.id}/products/${editingProduct.value.id}`, {
        stock: editingProduct.value.stock,
        min_stock_level: editingProduct.value.min_stock_level,
        buying_price: editingProduct.value.buying_price,
        price: editingProduct.value.price
    }, {
        onSuccess: () => {
            // Close loading Swal
            Swal.close();
            showEditModal.value = false;
            editingProduct.value = null;
            isUpdatingProduct.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Product updated successfully',
                timer: 2000,
                showConfirmButton: false
            });
        },
        onError: (errors) => {
            // Close loading Swal
            Swal.close();
            isUpdatingProduct.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errors.message || 'Failed to update product',
                confirmButtonText: 'OK'
            });
        }
    });
};

interface CartItem {
    id: number;
    name: string;
    price: number;
    quantity: number;
    stock: number;
    sku: string;
    is_taxable: boolean;
    tax_rate: number;
    barcode: string;
    image_url?: string | null;
}

const cart = ref<CartItem[]>([]);
const showCart = ref(false);
const processingSale = ref(false);

const productQuantities = ref<Record<number, number>>({});

const updateProductQuantity = (productId: number, change: number) => {
    if (!productQuantities.value[productId]) {
        productQuantities.value[productId] = 1;
    }
    const currentQuantity = productQuantities.value[productId];
    const newQuantity = Math.max(1, currentQuantity + change);
    productQuantities.value[productId] = newQuantity;
};

const handleQuantityInput = (e: Event, productId: number) => {
    const target = e.target as HTMLInputElement;
    const value = parseInt(target.value);
    if (!isNaN(value)) {
        updateQuantity(productId, value);
    }
};

const addToCart = (product: any) => {
    const quantity = productQuantities.value[product.id] || 1;
    const existingItem = cart.value.find(item => item.id === product.id);
    if (existingItem) {
        if (existingItem.quantity + quantity <= product.stock) {
            existingItem.quantity += quantity;
            // Show success toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: `Added ${quantity} more ${product?.name || 'N/A'} to cart`
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Stock Limit',
                text: 'Cannot add more items than available in stock.'
            });
        }
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: Number(product.price),
            quantity: quantity,
            stock: product.stock,
            sku: product.sku,
            is_taxable: product.is_taxable || false,
            tax_rate: product.tax_rate || 0,
            barcode: product.barcode,
            image_url: product.inventory_item?.image_url || product.image_url || null
        });
        // Show success toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: `${product?.name || 'N/A'} added to cart`
        });
    }
    // Reset quantity after adding to cart
    productQuantities.value[product.id] = 1;
    // Remove the automatic cart popup
    // showCart.value = true;
};

const removeFromCart = (productId: number) => {
    cart.value = cart.value.filter(item => item.id !== productId);
};

const updateQuantity = (productId: number, newQuantity: number) => {
    const item = cart.value.find(item => item.id === productId);
    if (item) {
        if (newQuantity <= item.stock) {
            item.quantity = newQuantity;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Stock Limit',
                text: 'Cannot add more items than available in stock.'
            });
        }
    }
};

const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + (Number(item.price) * item.quantity), 0);
});

const showReceiptModal = ref(false);
const receiptData = ref(null);
const receiptComponent = ref(null);

const processSale = async () => {
    if (cart.value.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Empty Cart',
            text: 'Please add items to cart before processing sale.'
        });
        return;
    }

    // Show verification dialog
    const { value: confirmed } = await Swal.fire({
        title: 'Verify Sale',
        html: `
            <div class="text-left">
                <p class="mb-2">Please verify the following:</p>
                <ul class="list-disc pl-5 mb-4">
                    <li>All items are correct</li>
                    <li>Quantities are accurate</li>
                    <li>Prices are correct</li>
                </ul>
                <div class="mt-4">
                    <h4 class="font-semibold mb-2">Items in Cart:</h4>
                    <div class="max-h-60 overflow-y-auto">
                        ${cart.value.map(item => `
                            <div class="mb-2 p-2 bg-gray-50 rounded">
                                <div class="flex items-center gap-3">
                                    ${item.image_url ? `<img src="/storage/${item.image_url}" alt="${item.name}" class="h-12 w-12 object-cover rounded" />` : ''}
                                    <div>
                                        <p class="font-medium">${item.name}</p>
                                        <p class="text-sm text-gray-600">Quantity: ${item.quantity} × KES ${Number(item.price).toFixed(2)} = KES ${(Number(item.price) * item.quantity).toFixed(2)}</p>
                                        ${item.is_taxable ? `<p class="text-sm text-gray-600">Tax (${item.tax_rate}%): KES ${((Number(item.price) * item.quantity * item.tax_rate) / 100).toFixed(2)}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <div class="mt-4 pt-2 border-t">
                    <p class="font-semibold">Subtotal: KES ${(cartTotal.value - cart.value.reduce((sum, item) => sum + ((Number(item.price) * item.quantity * item.tax_rate) / 100), 0)).toFixed(2)}</p>
                    <p class="font-semibold">Tax: KES ${cart.value.reduce((sum, item) => sum + ((Number(item.price) * item.quantity * item.tax_rate) / 100), 0).toFixed(2)}</p>
                    <p class="font-semibold">Total Amount: KES ${cartTotal.value.toFixed(2)}</p>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed with sale',
        cancelButtonText: 'Review cart'
    });

    if (confirmed) {
        const { value: paymentMethod } = await Swal.fire({
            title: 'Select Payment Method',
            input: 'select',
            inputOptions: {
                cash: 'Cash',
                card: 'Card',
                mpesa: 'Mpesa'
            },
            inputPlaceholder: 'Select a payment method',
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to select a payment method';
                }
            }
        });

        if (paymentMethod) {
            processingSale.value = true;

            // Show loading Swal
            Swal.fire({
                title: 'Processing Sale',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const totalTax = cart.value.reduce((sum, item) => 
                    sum + ((Number(item.price) * item.quantity * item.tax_rate) / 100), 0);
                const form = useForm({
                    customer_id: page.props.defaultCustomerId,
                    seller_id: page.props.auth.user.id,
                    business_id: page.props.auth.user.business_id,
                    branch_id: page.props.auth.user.branch_id,
                    total_amount: cartTotal.value,
                    tax: totalTax,
                    items: cart.value.map(item => ({
                        product: {
                            id: item.id,
                            name: item.name,
                            price: item.price,
                            barcode: item.barcode,
                            is_taxable: item.is_taxable,
                            tax_rate: item.tax_rate
                        },
                        quantity: item.quantity
                    })),
                    payment_method: paymentMethod
                });
                // Use axios to get JSON response for receipt
                const axios = (await import('axios')).default;
                // Use branch-specific route if available, otherwise fallback to generic route
                const route = (page.props.auth.user.branch_id && (isOwner.value || isAdmin.value)) ? 
                    `/businesses/${page.props.auth.user.business_id}/branches/${page.props.auth.user.branch_id}/sales` : 
                    '/sales';
                const response = await axios.post(route, form.data(), {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.data && response.data.success) {
                    cart.value = [];
                    showCart.value = false;
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Sale Completed Successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    // Merge sale and receipt for SalesReceipt.vue
                    const merged = {
                        ...response.data.sale,
                        ...response.data.receipt,
                        items: response.data.receipt.items
                    };
                    receiptData.value = merged;
                    showReceiptModal.value = true;
                    // Optionally reload products
                    router.reload({ only: ['products'] });
                } else {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Processing Sale',
                        text: response.data && response.data.error ? response.data.error : 'Unknown error',
                        confirmButtonText: 'Try Again'
                    });
                }
            } catch (error) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: error.response?.data?.error || error.message,
                    confirmButtonText: 'Try Again'
                });
            } finally {
                processingSale.value = false;
            }
        }
    }
};

const addToCartWithQuantity = (product: any, quantity: number) => {
    const existingItem = cart.value.find(item => item.id === product.id);
    if (existingItem) {
        if (existingItem.quantity + quantity <= product.stock) {
            existingItem.quantity += quantity;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Stock Limit',
                text: 'Cannot add more items than available in stock.'
            });
        }
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: quantity,
            stock: product.stock,
            sku: product.sku,
            is_taxable: product.is_taxable || false,
            tax_rate: product.tax_rate || 0,
            barcode: product.barcode,
            image_url: product.inventory_item?.image_url || product.image_url || null
        });
    }
};

const handleBarcodeInput = async () => {
    if (!barcodeInput.value) return;

    // Find the product with matching barcode
    const product = props.products.data.find(p => p.barcode === barcodeInput.value);

    if (!product) {
        Swal.fire({
            icon: 'error',
            title: 'Item Not Found',
            text: 'No product found with this barcode.',
            confirmButtonText: 'OK'
        });
        barcodeInput.value = '';
        return;
    }

    // Check if user has access to this product
    if (isSeller.value) {
        if (product.business.id !== page.props.auth.user.business_id) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have access to this product.',
                confirmButtonText: 'OK'
            });
            barcodeInput.value = '';
            return;
        }
    }

    // Check if product is in stock
    if (product.stock === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Out of Stock',
            text: `${product?.name || 'N/A'} is currently out of stock.`,
            confirmButtonText: 'OK'
        });
        return;
    }

    // Add to cart
    addToCart(product);
    barcodeInput.value = '';
    showBarcodeInput.value = false;
};

const handleBarcodeScan = (e: KeyboardEvent) => {
    // Ignore if user is typing in search or other input fields
    if (e.target instanceof HTMLInputElement && e.target.type === 'text') {
        return;
    }

    const currentTime = Date.now();
    const timeSinceLastKey = currentTime - lastKeyTime.value;
    lastKeyTime.value = currentTime;

    // If it's a character key
    if (e.key.length === 1) {
        // If time between keypresses is very short, it's likely a scanner
        if (timeSinceLastKey < SCAN_TIMEOUT) {
            barcodeBuffer.value += e.key;
            isScanning.value = true;
            
            // Clear any existing timeout
            if (barcodeTimeout.value) {
                window.clearTimeout(barcodeTimeout.value);
            }
            
            // Set a new timeout to process the barcode
            barcodeTimeout.value = window.setTimeout(() => {
                if (barcodeBuffer.value.length > 0) {
                    processBarcode(barcodeBuffer.value);
                    barcodeBuffer.value = '';
                    isScanning.value = false;
                }
            }, 100);
        } else {
            // If time between keypresses is longer, it's likely manual typing
            // Don't process as a barcode scan
            barcodeBuffer.value = '';
            isScanning.value = false;
        }
    }
};

const processBarcode = async (barcode: string) => {
    // Find the product with matching barcode
    const product = props.products.data.find(p => p.barcode === barcode);

    if (!product) {
        Swal.fire({
            icon: 'error',
            title: 'Item Not Found',
            text: 'No product found with this barcode.',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Check if user has access to this product
    if (isSeller.value) {
        if (product.business.id !== page.props.auth.user.business_id) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have access to this product.',
                confirmButtonText: 'OK'
            });
            return;
        }
    }

    // Check if product is in stock
    if (product.stock === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Out of Stock',
            text: `${product?.name || 'N/A'} is currently out of stock.`,
            confirmButtonText: 'OK'
        });
        return;
    }

    // Add to cart
    addToCart(product);

    // Show feedback toast instead of modal
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
    });

    Toast.fire({
        icon: 'success',
        title: `${product?.name || 'N/A'} added to cart`
    });
};

// Add this to the mounted hook
onMounted(() => {
    // Add global keyboard listener for barcode scanning
    window.addEventListener('keydown', handleBarcodeScan);
});

// Add this to the unmounted hook
onUnmounted(() => {
    // Remove keyboard listener when component is unmounted
    window.removeEventListener('keydown', handleBarcodeScan);
});

// Add the watch for barcode input focus after the existing functions
watch(showBarcodeInput, (newValue) => {
    if (newValue) {
        nextTick(() => {
            const input = document.querySelector('input[placeholder="Enter or scan barcode..."]') as HTMLInputElement;
            if (input) {
                input.focus();
            }
        });
    }
});

// Update the reviewCart function to just show the cart dialog
const reviewCart = () => {
    showCart.value = true;
};

// Add these refs for scroll locking
const cartContainer = ref<HTMLElement | null>(null);
const isScrollLocked = ref(false);
const hoverTimeout = ref<number | null>(null);

// Add these functions for scroll handling
const handleCartHover = () => {
    if (hoverTimeout.value) {
        clearTimeout(hoverTimeout.value);
    }
    // Show scroll message immediately
    isScrollLocked.value = true;
    if (cartContainer.value) {
        cartContainer.value.style.overflow = 'auto';
        cartContainer.value.style.overflowY = 'hidden';
    }
    // Set timeout for scroll locking
    hoverTimeout.value = window.setTimeout(() => {
        if (cartContainer.value) {
            cartContainer.value.style.overflow = 'auto';
            cartContainer.value.style.overflowY = 'hidden';
        }
    }, 3000);
};

const handleCartLeave = () => {
    if (hoverTimeout.value) {
        clearTimeout(hoverTimeout.value);
    }
    isScrollLocked.value = false;
    if (cartContainer.value) {
        cartContainer.value.style.overflow = 'auto';
    }
};

interface SaleResponse {
    sale: {
        sale: {
            reference: string;
            id: number;
        };
        receipt: {
            reference: string;
            id: number;
        };
    };
}

const setReceiptFixedWidth = (receiptEl) => {
    // Save original styles
    const original = {
        width: receiptEl.style.width,
        maxWidth: receiptEl.style.maxWidth,
        minWidth: receiptEl.style.minWidth,
    };
    // Set fixed width for download/screenshot
    receiptEl.style.width = '80mm';
    receiptEl.style.maxWidth = '80mm';
    receiptEl.style.minWidth = '80mm';
    return original;
};

const restoreReceiptWidth = (receiptEl, original) => {
    receiptEl.style.width = original.width;
    receiptEl.style.maxWidth = original.maxWidth;
    receiptEl.style.minWidth = original.minWidth;
};

const downloadPDF = () => {
    const receiptEl = document.querySelector('.receipt');
    const reference = receiptData?.reference || receiptData?.value?.reference || 'receipt';
    if (!receiptEl) {
        Swal.fire('Error', 'Receipt element not found.', 'error');
        return;
    }
    if (!reference) {
        Swal.fire('Error', 'No receipt reference available for download.', 'error');
        return;
    }

    // Save and set fixed width
    const originalWidth = setReceiptFixedWidth(receiptEl);
    // Save original styles
    const originalMaxHeight = receiptEl.style.maxHeight;
    const originalOverflowY = receiptEl.style.overflowY;
    // Remove scrollable constraints
    receiptEl.style.maxHeight = 'none';
    receiptEl.style.overflowY = 'visible';

    const opt = {
        margin: [2, 2, 2, 2],
        filename: reference + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: [80, 150], orientation: 'portrait' }
    };

    html2pdf().set(opt).from(receiptEl).save().then(() => {
        // Restore styles
        restoreReceiptWidth(receiptEl, originalWidth);
        receiptEl.style.maxHeight = originalMaxHeight;
        receiptEl.style.overflowY = originalOverflowY;
    }).catch(() => {
        restoreReceiptWidth(receiptEl, originalWidth);
        receiptEl.style.maxHeight = originalMaxHeight;
        receiptEl.style.overflowY = originalOverflowY;
    });
};

const printReceiptImage = async () => {
    const receiptEl = document.querySelector('.receipt');
    if (!receiptEl) {
        Swal.fire('Error', 'Receipt element not found.', 'error');
        return;
    }

    // Save and set fixed width
    const originalWidth = setReceiptFixedWidth(receiptEl);
    // Remove scroll constraints for full image
    const originalMaxHeight = receiptEl.style.maxHeight;
    const originalOverflowY = receiptEl.style.overflowY;
    receiptEl.style.maxHeight = 'none';
    receiptEl.style.overflowY = 'visible';

    html2canvas(receiptEl, { scale: 2, useCORS: true }).then(canvas => {
        restoreReceiptWidth(receiptEl, originalWidth);
        receiptEl.style.maxHeight = originalMaxHeight;
        receiptEl.style.overflowY = originalOverflowY;

        // Open the image in a new window for printing
        const dataUrl = canvas.toDataURL('image/png');
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print Receipt</title>
                    <style>
                        body { margin: 0; text-align: center; background: #fff; }
                        img { max-width: 100%; }
                    </style>
                </head>
                <body>
                    <img src="${dataUrl}" onload="window.print();window.onafterprint=window.close;" />
                </body>
            </html>
        `);
        printWindow.document.close();
    }).catch((err) => {
        restoreReceiptWidth(receiptEl, originalWidth);
        receiptEl.style.maxHeight = originalMaxHeight;
        receiptEl.style.overflowY = originalOverflowY;
        Swal.fire('Error', 'Failed to generate print image.', 'error');
        console.error('html2canvas error:', err);
    });
};

const scanBarcode = () => {
    // Implementation for barcode scanning
    Swal.fire('Info', 'Barcode scanning feature coming soon!', 'info');
};

const showProductDetails = (product) => {
    Swal.fire({
        title: product.name,
        html: `
            <div class="text-left">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Basic Information</h3>
                    <p><span class="font-medium">Description:</span> ${product.description || 'N/A'}</p>
                    <p><span class="font-medium">SKU:</span> ${product.sku || 'N/A'}</p>
                    <p><span class="font-medium">Barcode:</span> ${product.barcode || 'N/A'}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Pricing & Stock</h3>
                    <p><span class="font-medium">Price:</span> KES ${product.price}</p>
                    <p><span class="font-medium">Buying Price:</span> KES ${product.buying_price}</p>
                    <p><span class="font-medium">Current Stock:</span> ${product.stock}</p>
                    <p><span class="font-medium">Min Stock Level:</span> ${product.min_stock_level}</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Location</h3>
                    <p><span class="font-medium">Business:</span> ${product.business?.name || 'N/A'}</p>
                    <p><span class="font-medium">Branch:</span> ${product.branch?.name || 'N/A'}</p>
                </div>
            </div>
        `,
        width: '600px',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'product-details-modal'
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head title="All Inventory" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    All Inventory
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
                        <!-- Cart Summary -->
                        <div v-if="cart.length > 0" class="mb-6 p-4 bg-gray-50 rounded-lg shadow-lg">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Cart ({{ cart.length }} items)</h3>
                                <div class="flex items-center space-x-4">
                                    <span class="text-lg font-semibold">Total: KES {{ Number(cartTotal).toFixed(2) }}</span>
                                    <Button 
                                        @click="reviewCart" 
                                        class="bg-blue-600 hover:bg-blue-700 relative"
                                    >
                                        <svg width="256px" height="256px" viewBox="0 0 1024.00 1024.00" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000" stroke="#000000" stroke-width="1.024"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M960.1 258.4H245.8l-36.1-169H63.9v44h110.2l26.7 125 100.3 469.9 530 0.4v-44l-494.4-0.3-22.6-105.9H832l128.1-320.1z m-65 44L855.6 401H276.3l-21.1-98.6h639.9zM304.8 534.5L279.7 417h569.5l-47 117.5H304.8z" fill="#39393A"></path><path d="M375.6 810.6c28.7 0 52 23.3 52 52s-23.3 52-52 52-52-23.3-52-52 23.3-52 52-52m0-20c-39.7 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.3-72-72-72zM732 810.6c28.7 0 52 23.3 52 52s-23.3 52-52 52-52-23.3-52-52 23.3-52 52-52m0-20c-39.7 0-72 32.2-72 72s32.2 72 72 72c39.7 0 72-32.2 72-72s-32.3-72-72-72zM447.5 302.4h16v232.1h-16zM652 302.4h16v232.1h-16z" fill="#1630b1"></path><path d="M276.3 401l3.4 16-3.4-16z" fill="#343535"></path></g></svg>
                                        Review Cart
                                        
                                        <span v-if="cart.length > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                            {{ cart.length }}
                                        </span>
                                    </Button>
                                    <Button 
                                        @click="processSale" 
                                        :disabled="processingSale"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        {{ processingSale ? 'Processing...' : 'Process Sale' }}
                                    </Button>
                                </div>
                            </div>
                            <div class="relative group">
                                <div 
                                    ref="cartContainer"
                                    class="overflow-x-auto scrollbar-hide hover:scrollbar-default transition-all duration-300"
                                    @mouseenter="handleCartHover"
                                    @mouseleave="handleCartLeave"
                                    @wheel.prevent="(e) => {
                                        if (isScrollLocked && cartContainer) {
                                            cartContainer.scrollLeft += e.deltaY;
                                        }
                                    }"
                                >
                                    <div class="flex space-x-4 pb-2 min-w-max">
                                        <div v-for="item in [...cart].reverse()" :key="item.id" class="flex-none w-64 p-3 bg-white rounded-lg border hover:shadow-md transition-shadow">
                                            <div class="flex justify-between items-start">
                                                <div class="flex items-center gap-3">
                                                    <img v-if="item.image_url" :src="`/storage/${item.image_url}`" :alt="item.name" class="h-12 w-12 object-cover rounded" />
                                                    <div>
                                                        <h4 class="font-medium">{{ item.name }}</h4>
                                                        <p class="text-sm text-gray-500">SKU: {{ item.sku }}</p>
                                                    </div>
                                                </div>
                                                <Button
                                                    @click="removeFromCart(item.id)"
                                                    variant="destructive"
                                                    size="sm"
                                                >
                                                    Remove
                                                </Button>
                                            </div>
                                            <div class="mt-2 flex justify-between items-center">
                                                <div class="flex items-center space-x-1">
                                                    <button
                                                        class="text-gray-600 hover:text-gray-900 px-2 py-1 border rounded"
                                                        :disabled="item.quantity <= 1"
                                                        @click="updateQuantity(item.id, item.quantity - 1)"
                                                    >
                                                        -
                                                    </button>
                                                    <input
                                                        type="number"
                                                        v-model="item.quantity"
                                                        @input="(e) => handleQuantityInput(e, item.id)"
                                                        :min="1"
                                                        :max="item.stock"
                                                        class="w-12 text-center border rounded px-1 py-0.5 text-sm"
                                                    />
                                                    <button
                                                        class="text-gray-600 hover:text-gray-900 px-2 py-1 border rounded"
                                                        :disabled="item.quantity >= item.stock"
                                                        @click="updateQuantity(item.id, item.quantity + 1)"
                                                    >
                                                        +
                                                    </button>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-600">KES {{ Number(item.price).toFixed(2) }} each</p>
                                                    <p class="font-medium">Total: KES {{ (Number(item.price) * item.quantity).toFixed(2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div 
                                    class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-gray-50 pointer-events-none transition-opacity duration-300"
                                    :class="{
                                        'opacity-0': isScrollLocked,
                                        'group-hover:opacity-0': !isScrollLocked
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Search and Table -->
                        <div class="mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1 max-w-sm">
                                    <Input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search Inventorys by name or SKU..."
                                        class="w-full"
                                    />
                                </div>
                                <div class="flex items-center space-x-4">
                                    <!-- Only show scan button for sellers -->
                                    <Button
                                        v-if="isSeller"
                                        @click="showBarcodeInput = true"
                                        variant="outline"
                                        class="flex items-center"
                                    >
                                        <svg
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v4m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                                            />
                                        </svg>
                                        Scan Barcode
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Barcode Input Section -->
                        <div v-if="showBarcodeInput" class="mt-4">
                            <div class="flex items-center space-x-2">
                                <Input
                                    v-model="barcodeInput"
                                    type="text"
                                    placeholder="Enter or scan barcode..."
                                    class="w-full"
                                    @keyup.enter="handleBarcodeInput"
                                    ref="barcodeInputRef"
                                />
                                <Button
                                    @click="handleBarcodeInput"
                                    class="bg-green-600 hover:bg-green-700"
                                >
                                    Add
                                </Button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                Press Enter or click Add to add item to cart
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                        <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <template v-if="isSeller">
                                                Sell
                                            </template>
                                            <template v-else>
                                                Actions
                                            </template>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50">
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product?.name || 'N/A' }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES {{ Number(product.price).toFixed(2) }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.barcode }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.stock }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.business.name }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.branch?.name || 'No branch assigned' }}</td>
                                        <td class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <template v-if="isSeller">
                                                <div class="flex items-center space-x-2">
                                                    <button @click="updateProductQuantity(product.id, -1)" :disabled="(productQuantities[product.id] || 1) <= 1" class="px-2 py-1 text-lg text-gray-600 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50">-</button>
                                                    <input type="number" v-model="productQuantities[product.id]" min="1" :max="product.stock" class="w-12 text-center border rounded px-1 py-0.5 text-sm" @input="(e) => handleQuantityInput(e, product.id)" />
                                                    <button @click="updateProductQuantity(product.id, 1)" :disabled="(productQuantities[product.id] || 1) >= product.stock" class="px-2 py-1 text-lg text-gray-600 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50">+</button>
                                                    <button @click="addToCart(product)" class="ml-2 bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7A2 2 0 007.48 19h9.04a2 2 0 001.83-1.3L21 13M7 13V6a1 1 0 011-1h5a1 1 0 011 1v7" /></svg>
                                                        Add to Cart
                                                    </button>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div class="flex space-x-3">
                                                    <button @click="showProductDetails(product)" class="text-blue-600 hover:text-blue-900">View</button>
                                                    <button @click="editProduct(product)" class="text-blue-600 hover:text-blue-900">Edit</button>
                                                    <button @click="removeProduct(product)" class="text-red-600 hover:text-red-900">Delete</button>
                                                </div>
                                            </template>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredProducts.length === 0" class="hover:bg-gray-50">
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No inventory items found. Go to Inventorys to add items to your business.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            <Pagination :links="products.links" />
                        </div>

                        <!-- Cart Items Modal -->
                        <Dialog v-model:open="showCart">
                            <DialogContent class="sm:max-w-[600px]">
                                <DialogHeader>
                                    <DialogTitle>Review Cart Items</DialogTitle>
                                </DialogHeader>
                                <div class="py-4">
                                    <div class="max-h-[400px] overflow-y-auto pr-2">
                                        <div v-for="item in cart" :key="item.id" class="mb-4 p-4 border rounded-lg">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="flex items-center gap-3">
                                                    <img v-if="item.image_url" :src="`/storage/${item.image_url}`" :alt="item.name" class="h-12 w-12 object-cover rounded" />
                                                    <div>
                                                        <h4 class="font-medium text-lg">{{ item.name }}</h4>
                                                        <p class="text-sm text-gray-500">SKU: {{ item.sku }}</p>
                                                    </div>
                                                </div>
                                                <Button
                                                    @click="removeFromCart(item.id)"
                                                    variant="destructive"
                                                    size="sm"
                                                >
                                                    Remove
                                                </Button>
                                            </div>
                                            <div class="flex items-center justify-between mt-2">
                                                <div class="flex items-center space-x-4">
                                                    <div class="flex items-center space-x-2">
                                                        <button
                                                            class="text-gray-600 hover:text-gray-900 px-2 py-1 border rounded"
                                                            :disabled="item.quantity <= 1"
                                                            @click="updateQuantity(item.id, item.quantity - 1)"
                                                        >
                                                            -
                                                        </button>
                                                        <input
                                                            type="number"
                                                            v-model="item.quantity"
                                                            @input="(e) => handleQuantityInput(e, item.id)"
                                                            :min="1"
                                                            :max="item.stock"
                                                            class="w-12 text-center border rounded px-1 py-0.5 text-sm"
                                                        />
                                                        <button
                                                            class="text-gray-600 hover:text-gray-900 px-2 py-1 border rounded"
                                                            :disabled="item.quantity >= item.stock"
                                                            @click="updateQuantity(item.id, item.quantity + 1)"
                                                        >
                                                            +
                                                        </button>
                                                    </div>
                                                    <span class="text-sm text-gray-600">
                                                        Available: {{ item.stock }}
                                                    </span>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-600">KES {{ Number(item.price).toFixed(2) }} each</p>
                                                    <p class="font-medium">Total: KES {{ (Number(item.price) * item.quantity).toFixed(2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-lg font-semibold">Total Items:</span>
                                            <span class="text-lg font-semibold">{{ cart.reduce((sum, item) => sum + item.quantity, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-semibold">Total Amount:</span>
                                            <span class="text-lg font-semibold">KES {{ Number(cartTotal).toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <DialogFooter class="flex space-x-2">
                                    <Button
                                        @click="showCart = false"
                                        variant="outline"
                                    >
                                        Continue Shopping
                                    </Button>
                                    <Button
                                        @click="processSale"
                                        :disabled="processingSale"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        {{ processingSale ? 'Processing...' : 'Process Sale' }}
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                        <!-- Update the scanning indicator to be more visible -->
                        <div v-if="isScanning" class="fixed top-4 right-4 bg-blue-100 text-blue-800 px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2 z-50">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-800"></div>
                            <span>Scanning barcode...</span>
                        </div>

                        <!-- Move the scroll message outside the grid -->
                        <div v-if="isScrollLocked" 
                            class="fixed top-4 right-4 bg-blue-100 text-blue-800 px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2 z-50 animate-bounce"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                            </svg>
                            <span>Scroll to view more items</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </div>

                        <Modal :show="showReceiptModal" @close="showReceiptModal = false" maxWidth="sm">
                            <SalesReceipt v-if="receiptData" :sale="receiptData" ref="receiptComponent" />
                            <div class="flex justify-end mt-4 gap-2">
                                <button
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 print-button"
                                    @click="printReceiptImage"
                                >
                                    Print Receipt
                                </button>
                                <button
                                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 print-button"
                                    @click="downloadPDF"
                                >
                                    Download PDF
                                </button>
                                <button
                                    class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 print-button"
                                    @click="showReceiptModal = false"
                                >
                                    Cancel
                                </button>
                            </div>
                        </Modal>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Preview Modal -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Sale Receipt</h3>
                    <Button @click="showPreviewModal = false" variant="outline" size="sm">
                        Close
                    </Button>
                </div>
                <div v-if="selectedSale" class="space-y-4">
                    <SalesReceipt :sale="selectedSale" />
                </div>
            </div>
        </Modal>

        <!-- Edit Product Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Edit Product</DialogTitle>
                </DialogHeader>
                <div v-if="editingProduct" class="py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
                        <input 
                            type="text" 
                            :value="editingProduct.business?.name" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-100" 
                            readonly
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                        <input 
                            type="text" 
                            :value="editingProduct.branch?.name || 'No branch assigned'" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-100" 
                            readonly
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                            <input 
                                type="number" 
                                v-model="editingProduct.stock" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                required 
                                min="0"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Stock Level</label>
                            <input 
                                type="number" 
                                v-model="editingProduct.min_stock_level" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                required 
                                min="0"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buying Price</label>
                            <input 
                                type="number" 
                                v-model="editingProduct.buying_price" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                required 
                                min="0" 
                                step="0.01"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                            <input 
                                type="number" 
                                v-model="editingProduct.price" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                required 
                                min="0" 
                                step="0.01"
                            />
                        </div>
                    </div>
                </div>
                <DialogFooter class="flex space-x-2">
                    <Button
                        @click="showEditModal = false"
                        variant="outline"
                        :disabled="isUpdatingProduct"
                    >
                        Cancel
                    </Button>
                    <Button
                        @click="updateProduct"
                        :disabled="isUpdatingProduct"
                        class="bg-blue-600 hover:bg-blue-700"
                    >
                        {{ isUpdatingProduct ? 'Updating...' : 'Update Product' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;  /* Chrome, Safari and Opera */
}
.scrollbar-default {
    -ms-overflow-style: auto;  /* IE and Edge */
    scrollbar-width: auto;  /* Firefox */
}
.scrollbar-default::-webkit-scrollbar {
    display: block;  /* Chrome, Safari and Opera */
    height: 8px;
}
.scrollbar-default::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.scrollbar-default::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
.scrollbar-default::-webkit-scrollbar-thumb:hover {
    background: #555;
}
@media print {
    body * {
        visibility: hidden;
    }
    .receipt, .receipt * {
        visibility: visible;
    }
    .receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm !important;
    }
    .print-button {
        display: none !important;
    }
}
.product-details-modal {
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
}
.product-details-modal p {
    margin-bottom: 0.5rem;
    color: #374151;
}
.product-details-modal h3 {
    color: #111827;
    border-bottom: 1px solid #E5E7EB;
    padding-bottom: 0.5rem;
}
</style> 
