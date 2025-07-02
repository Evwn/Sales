<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import LineChart from '@/components/charts/LineChart.vue';
import BarChart from '@/components/charts/BarChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import {
  CurrencyDollarIcon,
  ShoppingCartIcon,
  ChartBarIcon,
  BuildingOfficeIcon,
  UserIcon,
  CreditCardIcon,
  PlusCircleIcon,
  BuildingStorefrontIcon,
} from '@heroicons/vue/24/outline'

interface Sale {
    id: number;
    amount: number;
    created_at: string;
    seller: {
        name: string;
    };
    payment_method: string;
    branch?: {
        name: string;
        business?: {
            name: string;
        };
    };
}

interface Stats {
    total_sales: number;
    sales_count: number;
    average_sale: number;
    sales: Sale[]; // All sales data
    sales_today: Sale[]; // Today's sales
    changes: {
        sales: number;
        count: number;
        average: number;
    };
}

interface Role {
    id: number;
    name: string;
    description: string;
    permissions: string[];
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

interface User {
    name: string;
    email: string;
    roles: Role[];
}

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}

const props = defineProps({
    stats: {
        type: Object,
        required: false,
        default: () => ({
            sales_today: [],
            sales_week: 0,
            sales_month: 0,
            total_sales: 0,
            total_orders: 0,
            average_order_value: 0,
            active_branches: 0,
            total_branches: 0,
            sales_trend: {
                labels: [],
                data: []
            },
            branch_performance: {
                labels: [],
                data: []
            },
            top_products: [],
            payment_methods: {},
            recent_activity: [],
            growth: {
                sales: 0,
                orders: 0,
                aov: 0
            }
        })
    },
    name: {
        type: String,
        required: false,
        default: ''
    },
    quote: {
        type: Object,
        required: false,
        default: () => ({
            text: '',
            author: ''
        })
    },
    auth: {
        type: Object,
        required: false,
        default: () => ({
            user: {
                name: '',
                email: '',
                roles: []
            }
        })
    },
    user: {
        type: Object,
        required: false,
        default: () => ({
            name: '',
            email: '',
            roles: [],
            business: null,
            branch: null
        })
    },
    businesses: {
        type: Array,
        required: false,
        default: () => []
    },
});

defineOptions({
    name: 'Dashboard'
});

const handleViewSale = (sale: Sale) => {
    Swal.fire({
        title: 'Sale Details',
        html: `
            <div class="text-left">
                <p><strong>Date:</strong> ${new Date(sale.created_at).toLocaleString()}</p>
                <p><strong>Seller:</strong> ${sale.seller?.name || 'N/A'}</p>
                <p><strong>Amount:</strong> KES ${Number(sale.amount).toFixed(2)}</p>
                <p><strong>Payment Method:</strong> ${sale.payment_method}</p>
                <p><strong>Branch:</strong> ${sale.branch?.name || 'N/A'}</p>
                <p><strong>Business:</strong> ${sale.branch?.business?.name || 'N/A'}</p>
            </div>
        `,
        confirmButtonText: 'Close',
        confirmButtonColor: '#3085d6',
    });
};

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

// Update the isOwner computed property to check roles
const isOwner = computed(() => {
    return props.auth?.user?.roles?.some(role => role.name === 'owner');
});

// Update hasPermission to check all roles
const hasPermission = (permission: string) => {
    return props.auth?.user?.roles?.some(role => 
        role.permissions?.includes(permission)
    ) || false;
};

// Ensure sales_today is always an array
const salesToday = computed(() => {
    const sales = props.stats?.sales_today || [];
    return Array.isArray(sales) ? sales : [];
});

// Update the salesTodayTotal computed property
const salesTodayTotal = computed(() => {
    return salesToday.value.reduce((total, sale) => total + (sale.amount || 0), 0);
});

// Update the averageOrderValue computed property
const averageOrderValue = computed(() => {
    if (filteredSales.value.length === 0) return 0;
    return totalFilteredSales.value / filteredSales.value.length;
});

// Update the changes computed property to calculate growth based on filter period
const changes = computed(() => {
    const sales = filteredSales.value;
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

    // If filter is 'all', return no changes
    if (filter.value === 'all') {
        return {
            sales: 0,
            count: 0,
            average: 0
        };
    }

    // Get current period sales
    const currentSales = sales;
    const currentTotal = currentSales.reduce((sum, sale) => sum + (Number(sale.amount) || 0), 0);
    const currentCount = currentSales.length;
    const currentAverage = currentCount > 0 ? currentTotal / currentCount : 0;

    // Get previous period sales based on filter
    let previousStart, previousEnd;
    switch (filter.value) {
        case 'today':
            previousStart = new Date(today);
            previousStart.setDate(previousStart.getDate() - 1);
            previousEnd = new Date(today);
            break;
        case 'yesterday':
            previousStart = new Date(today);
            previousStart.setDate(previousStart.getDate() - 2);
            previousEnd = new Date(today);
            previousEnd.setDate(previousEnd.getDate() - 1);
            break;
        case 'this_week':
            previousStart = new Date(today);
            previousStart.setDate(previousStart.getDate() - 14);
            previousEnd = new Date(today);
            previousEnd.setDate(previousEnd.getDate() - 7);
            break;
        case 'last_week':
            previousStart = new Date(today);
            previousStart.setDate(previousStart.getDate() - 21);
            previousEnd = new Date(today);
            previousEnd.setDate(previousEnd.getDate() - 14);
            break;
        case 'this_month':
            previousStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            previousEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'last_month':
            previousStart = new Date(today.getFullYear(), today.getMonth() - 2, 1);
            previousEnd = new Date(today.getFullYear(), today.getMonth() - 1, 0);
            break;
        case 'this_year':
            previousStart = new Date(today.getFullYear() - 1, 0, 1);
            previousEnd = new Date(today.getFullYear() - 1, 11, 31);
            break;
        default:
            return {
                sales: 0,
                count: 0,
                average: 0
            };
    }

    // Get previous period sales from all sales data
    const previousSales = props.stats?.sales?.filter(sale => {
        const saleDate = new Date(sale.created_at);
        return saleDate >= previousStart && saleDate <= previousEnd;
    }) || [];

    const previousTotal = previousSales.reduce((sum, sale) => sum + (Number(sale.amount) || 0), 0);
    const previousCount = previousSales.length;
    const previousAverage = previousCount > 0 ? previousTotal / previousCount : 0;

    // Calculate growth percentages
    const salesGrowth = previousTotal > 0 ? ((currentTotal - previousTotal) / previousTotal) * 100 : 0;
    const ordersGrowth = previousCount > 0 ? ((currentCount - previousCount) / previousCount) * 100 : 0;
    const aovGrowth = previousAverage > 0 ? ((currentAverage - previousAverage) / previousAverage) * 100 : 0;

    return {
        sales: Math.round(salesGrowth * 100) / 100,
        count: Math.round(ordersGrowth * 100) / 100,
        average: Math.round(aovGrowth * 100) / 100
    };
});

// Chart Data and Options
const salesTrendOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: value => formatCurrency(value)
            }
        }
    }
};

const branchPerformanceOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: value => formatCurrency(value)
            }
        }
    }
};

// Add computed properties for safe access to stats
const salesWeek = computed(() => props.stats?.sales_week || 0);
const salesMonth = computed(() => props.stats?.sales_month || 0);
const totalOrders = computed(() => {
    return totalFilteredOrders.value;
});
const activeBranches = computed(() => filteredBranches.value.filter(b => b.status === 'active').length);
const totalBranches = computed(() => filteredBranches.value.length);
const salesTrend = computed(() => props.stats?.sales_trend || { labels: [], data: [] });
const branchPerformance = computed(() => props.stats?.branch_performance || { labels: [], data: [] });
const topProducts = computed(() => props.stats?.top_products || []);
const paymentMethods = computed(() => {
    const methods = props.stats?.payment_methods || {};
    const total = Object.values(methods).reduce((sum, val) => sum + (Number(val) || 0), 0);
    
    if (total === 0) return {};
    
    return Object.entries(methods).reduce((acc, [method, value]) => {
        const percentage = Math.round((Number(value) / total) * 100);
        acc[method] = isNaN(percentage) ? 0 : percentage;
        return acc;
    }, {});
});
const recentActivity = computed(() => {
    const activities = props.stats?.recent_activity || [];
    const currentUserId = props.auth?.user?.id;
    return activities.slice(0, 10).map(activity => {
        let message = '';
        let icon = '';
        let byText = '';
        // Only process activities with valid data
        if (!activity.type || !activity.data) {
            return null;
        }
        // Determine who performed the activity
        if (activity.data.user_id && currentUserId && activity.data.user_id === currentUserId) {
            byText = 'by you';
        } else if (activity.data.user_name) {
            byText = `by ${activity.data.user_name}`;
        } else {
            byText = 'by Unknown';
        }
        switch (activity.type) {
            case 'sale_created':
                message = `New sale of ${formatCurrency(activity.data.amount)} by ${activity.data.seller_name || 'Unknown'} at ${activity.data.branch_name || 'Unknown'}`;
                icon = 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                break;
            case 'product_updated':
                message = `Product "${activity.data.product_name || 'Unknown'}" updated ${byText}`;
                icon = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
                break;
            case 'inventory_adjusted':
                message = `Inventory for "${activity.data.product_name || 'Unknown'}" adjusted by ${activity.data.quantity ?? activity.data.adjustment ?? 'N/A'} units ${byText}`;
                icon = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
                break;
            case 'user_logged_in':
                message = `${activity.data.user_name || 'Unknown'} logged in`;
                icon = 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z';
                break;
            case 'payment_received':
                message = `Payment of ${formatCurrency(activity.data.amount)} received via ${activity.data.payment_method || activity.data.method || 'Unknown'} at ${activity.data.branch_name || 'Unknown'}`;
                icon = 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z';
                break;
            case 'branch_created':
                message = `New branch "${activity.data.branch_name || 'Unknown'}" created by ${activity.data.user_name || 'Unknown'}`;
                icon = 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4';
                break;
            case 'business_updated':
                message = `Business "${activity.data.business_name || 'Unknown'}" updated by ${activity.data.user_name || 'Unknown'}`;
                icon = 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4';
                break;
            default:
                return null; // Skip unknown activity types
        }
        return {
            id: activity.id,
            message,
            icon,
            time: activity.time
        };
    }).filter(activity => activity !== null);
});

// Add formatDate function
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};

const filter = ref('all'); // Default to 'all'
const showCustomDatePicker = ref(false);
const startDate = ref('');
const endDate = ref('');
const selectedBusiness = ref('all'); // Add business filter

const today = new Date();
today.setHours(0, 0, 0, 0);

const maxDate = computed(() => {
    const date = new Date();
    return date.toISOString().split('T')[0];
});

const filters = [
    { label: 'All', value: 'all' },
    { label: 'Today', value: 'today' },
    { label: 'Yesterday', value: 'yesterday' },
    { label: 'This Week', value: 'this_week' },
    { label: 'Last Week', value: 'last_week' },
    { label: 'This Month', value: 'this_month' },
    { label: 'Last Month', value: 'last_month' },
    { label: 'This Year', value: 'this_year' },
    { label: 'Custom Range', value: 'custom' }
];

// Use businesses prop for all business data, but do not change the interface
const businesses = props.businesses || [];

// availableBusinesses: always show all businesses, even if no sales
const availableBusinesses = computed(() => {
    return businesses.map(biz => ({ id: biz.id, name: biz.name }));
});

// filteredSales: filter from all businesses' sales, not just props.stats.sales
const filteredSales = computed(() => {
    let allSales = businesses.flatMap(biz => biz.sales || []);
    // First filter by business if selected
    let businessFilteredSales = allSales;
    if (selectedBusiness.value !== 'all') {
        businessFilteredSales = allSales.filter(sale => sale.business_id === Number(selectedBusiness.value));
    }
    // Then apply time filter as before
    if (filter.value === 'all') return businessFilteredSales;
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    return businessFilteredSales.filter(sale => {
        const saleDate = new Date(sale.created_at);
        switch (filter.value) {
            case 'today':
                return saleDate >= today;
            case 'yesterday':
                return saleDate >= yesterday && saleDate < today;
            case 'this_week':
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                return saleDate >= weekStart;
            case 'last_week':
                const lastWeekStart = new Date(today);
                lastWeekStart.setDate(today.getDate() - today.getDay() - 7);
                const lastWeekEnd = new Date(today);
                lastWeekEnd.setDate(today.getDate() - today.getDay());
                return saleDate >= lastWeekStart && saleDate < lastWeekEnd;
            case 'this_month':
                const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                return saleDate >= monthStart;
            case 'last_month':
                const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 1);
                return saleDate >= lastMonthStart && saleDate < lastMonthEnd;
            case 'this_year':
                const yearStart = new Date(today.getFullYear(), 0, 1);
                return saleDate >= yearStart;
            case 'custom':
                if (!startDate.value || !endDate.value) return true;
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                end.setHours(23, 59, 59, 999);
                return saleDate >= start && saleDate <= end;
            default:
                return true;
        }
    });
});

// Update recentSales to use filteredSales
const recentSales = computed(() => filteredSales.value);

// Update the totalFilteredSales computed property
const totalFilteredSales = computed(() => {
    return filteredSales.value.reduce((total, sale) => {
        const amount = Number(sale.amount) || 0;
        return total + amount;
    }, 0);
});

// Update the totalSales computed property
const totalSales = computed(() => {
    return totalFilteredSales.value;
});

const totalFilteredOrders = computed(() => filteredSales.value.length);

const averageFilteredOrderValue = computed(() => {
    if (filteredSales.value.length === 0) return 0;
    return totalFilteredSales.value / filteredSales.value.length;
});

// Add watch for selectedPeriod changes
const getYesterdayRange = () => {
    const todayForComparison = new Date(today);
    todayForComparison.setHours(0, 0, 0, 0);
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    router.get('/dashboard', { filter: 'yesterday' }, {
        preserveState: true,
        preserveScroll: true,
        only: ['stats']
    });
};

// Update the filteredSalesTrend computed property
const filteredSalesTrend = computed(() => {
    const sales = filteredSales.value;

    // Group sales by date and branch
    const groupedSales = sales.reduce((acc, sale) => {
        const date = new Date(sale.created_at);
        const dateKey = date.toISOString().split('T')[0]; // Use YYYY-MM-DD format for consistency
        const branchName = sale.branch?.name || 'Unknown Branch';
        
        if (!acc[dateKey]) {
            acc[dateKey] = {};
        }
        if (!acc[dateKey][branchName]) {
            acc[dateKey][branchName] = 0;
        }
        acc[dateKey][branchName] += Number(sale.amount) || 0;
        return acc;
    }, {});

    // Get all unique branch names
    const allBranches = new Set();
    Object.values(groupedSales).forEach(dateData => {
        Object.keys(dateData).forEach(branch => allBranches.add(branch));
    });
    const branchNames = Array.from(allBranches);

    // Sort dates chronologically (oldest to newest)
    const sortedDates = Object.keys(groupedSales).sort((a, b) => {
        return a.localeCompare(b); // Since we're using YYYY-MM-DD format, string comparison works
    });

    // Format dates for display (convert back to readable format)
    const formattedLabels = sortedDates.map(date => {
        const dateObj = new Date(date);
        return dateObj.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric' 
        });
    });

    // Create datasets for each branch
    const branchColors = [
        'rgb(59, 130, 246)',   // Blue
        'rgb(16, 185, 129)',   // Green
        'rgb(245, 158, 11)',   // Yellow
        'rgb(239, 68, 68)',    // Red
        'rgb(139, 92, 246)',   // Purple
        'rgb(236, 72, 153)',   // Pink
        'rgb(14, 165, 233)',   // Sky Blue
        'rgb(34, 197, 94)',    // Emerald
        'rgb(251, 146, 60)',   // Orange
        'rgb(168, 85, 247)'    // Violet
    ];

    const datasets = branchNames.map((branchName, index) => {
        const data = sortedDates.map(date => {
            return groupedSales[date][branchName] || 0;
    });

    return {
            label: branchName,
            data: data,
            borderColor: branchColors[index % branchColors.length],
            backgroundColor: branchColors[index % branchColors.length] + '20', // Add transparency
            tension: 0.1,
            fill: false
        };
    });

    return {
        labels: formattedLabels,
        datasets: datasets
    };
});

// Update the filteredBranchPerformance computed property
const filteredBranchPerformance = computed(() => {
    const sales = filteredSales.value;

    // Group sales by branch
    const branchSales = sales.reduce((acc, sale) => {
        const branchName = sale.branch?.name || 'Unknown Branch';
        if (!acc[branchName]) {
            acc[branchName] = 0;
        }
        acc[branchName] += Number(sale.amount) || 0;
        return acc;
    }, {});

    // Convert to arrays for the chart
    const labels = Object.keys(branchSales);
    const data = Object.values(branchSales);

    // Use the same colors as Sales Trend chart
    const branchColors = [
        'rgb(59, 130, 246)',   // Blue
        'rgb(16, 185, 129)',   // Green
        'rgb(245, 158, 11)',   // Yellow
        'rgb(239, 68, 68)',    // Red
        'rgb(139, 92, 246)',   // Purple
        'rgb(236, 72, 153)',   // Pink
        'rgb(14, 165, 233)',   // Sky Blue
        'rgb(34, 197, 94)',    // Emerald
        'rgb(251, 146, 60)',   // Orange
        'rgb(168, 85, 247)'    // Violet
    ];

    const backgroundColor = labels.map((_, index) => 
        branchColors[index % branchColors.length]
    );

    const borderColor = labels.map((_, index) => 
        branchColors[index % branchColors.length]
    );

    return {
        labels: labels,
        data: data,
        backgroundColor: backgroundColor,
        borderColor: borderColor
    };
});

const filteredPaymentMethods = computed(() => {
    const sales = filteredSales.value;
    const methods = sales.reduce((acc, sale) => {
        const method = sale.payment_method || 'unknown';
        if (!acc[method]) {
            acc[method] = 0;
        }
        acc[method]++;
        return acc;
    }, {});

    return methods;
});

// Add this function to provide color classes for activity types
const getActivityColor = (type) => {
  switch (type) {
    case 'sale_created':
      return 'bg-green-500';
    case 'product_updated':
      return 'bg-blue-500';
    case 'inventory_adjusted':
      return 'bg-yellow-500';
    case 'user_logged_in':
      return 'bg-indigo-500';
    case 'payment_received':
      return 'bg-purple-500';
    case 'branch_created':
      return 'bg-pink-500';
    case 'business_updated':
      return 'bg-orange-500';
    default:
      return 'bg-gray-400';
  }
};

// Add this function to provide icon names for activity types
const getActivityIcon = (type) => {
  switch (type) {
    case 'sale_created':
      return 'CurrencyDollarIcon';
    case 'product_updated':
      return 'ShoppingCartIcon';
    case 'inventory_adjusted':
      return 'ChartBarIcon';
    case 'user_logged_in':
      return 'UserIcon';
    case 'payment_received':
      return 'CreditCardIcon';
    case 'branch_created':
      return 'BuildingOfficeIcon';
    case 'business_updated':
      return 'BuildingStorefrontIcon';
    default:
      return 'UserIcon';
  }
};

const isSeller = computed(() => {
    return props.auth?.user?.roles?.some(role => role.name === 'seller');
});

const filteredBranches = computed(() => {
  if (selectedBusiness.value === 'all') {
    return businesses.flatMap(biz => biz.branches || []);
  }
  const biz = businesses.find(biz => String(biz.id) === String(selectedBusiness.value));
  return biz ? (biz.branches || []) : [];
});
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 v-if="isSeller" class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome to {{ props.user.business?.name || 'the business' }}
            </h2>
            <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>
        <div v-if="isSeller" class="max-w-2xl mx-auto mt-12">
          <div class="bg-white shadow-lg rounded-2xl p-8 flex flex-col items-center">
            <img
              v-if="props.user.business?.logo_url"
              :src="props.user.business.logo_url"
              alt="Business Logo"
              class="h-20 w-20 rounded-full object-cover mb-4 border-2 border-blue-200"
            />
            <h1 class="text-3xl font-bold text-blue-900 mb-2 text-center">
              Welcome to {{ props.user.business?.name || 'the business' }}
            </h1>
            <p class="text-lg text-gray-600 mb-6 text-center">
              You are working at <span class="font-semibold">{{ props.user.branch?.name || 'Your Branch' }}</span>
            </p>
            <div class="bg-blue-50 p-6 rounded-lg mb-6 w-full text-center">
              <p class="text-lg text-blue-800 italic mb-2">"{{ quote?.text || '' }}"</p>
              <p class="text-sm text-blue-600">- {{ quote?.author || '' }}</p>
            </div>
            <div class="w-full">
              <h2 class="text-xl font-semibold text-gray-800 mb-2">Business Owner Contact</h2>
              <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center">
                <p class="text-gray-700"><span class="font-semibold">Name:</span> {{ props.user.business?.owner?.name || 'N/A' }}</p>
                <p class="text-gray-700"><span class="font-semibold">Email:</span> {{ props.user.business?.owner?.email || props.user.business?.email || 'N/A' }}</p>
                <p class="text-gray-700"><span class="font-semibold">Phone:</span> {{ props.user.business?.owner?.phone || props.user.business?.phone || 'N/A' }}</p>
                <p class="text-gray-700"><span class="font-semibold">Address:</span> {{ props.user.business?.address || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else>
            <div class="min-h-screen bg-gray-100">
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <!-- Filter Section -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <div class="flex flex-wrap gap-4 items-center">
                                <label class="text-sm font-medium text-gray-700">Time Filter:</label>
                                    <select
                                        v-model="filter"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                        <option value="all">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="yesterday">Yesterday</option>
                                        <option value="this_week">This Week</option>
                                        <option value="last_week">Last Week</option>
                                        <option value="this_month">This Month</option>
                                        <option value="last_month">Last Month</option>
                                        <option value="this_year">This Year</option>
                                        <option value="custom">Custom Range</option>
                                    </select>

                                    <div v-if="filter === 'custom'" class="flex gap-4">
                                        <input
                                            type="date"
                                            v-model="startDate"
                                            :max="maxDate"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <input
                                            type="date"
                                            v-model="endDate"
                                            :min="startDate"
                                            :max="maxDate"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div class="flex flex-wrap gap-4 items-center">
                                        <label class="text-sm font-medium text-gray-700">Business Filter:</label>
                                        <select
                                            v-model="selectedBusiness"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        >
                                            <option value="all">All Businesses</option>
                                            <option 
                                                v-for="business in availableBusinesses" 
                                                :key="business.id" 
                                                :value="business.id"
                                            >
                                                {{ business.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                            <!-- Total Sales -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">Total Sales</dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">
                                                        KES {{ totalSales.toFixed(2) }}
                                                    </div>
                                                    <div class="ml-2 flex items-baseline text-sm font-semibold" :class="changes.sales >= 0 ? 'text-green-600' : 'text-red-600'">
                                                        <span v-if="filter !== 'all'">
                                                            {{ changes.sales >= 0 ? '+' : '' }}{{ changes.sales.toFixed(2) }}%
                                                        </span>
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                </div>
                                </div>
                            </div>

                            <!-- Total Orders -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">{{ totalOrders }}</div>
                                                    <div class="ml-2 flex items-baseline text-sm font-semibold" :class="changes.count >= 0 ? 'text-green-600' : 'text-red-600'">
                                                        <span v-if="filter !== 'all'">
                                                            {{ changes.count >= 0 ? '+' : '' }}{{ changes.count.toFixed(2) }}%
                                    </span>
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                </div>
                            </div>
                        </div>

                            <!-- Average Order Value -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">Average Order Value</dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">
                                                        KES {{ averageOrderValue.toFixed(2) }}
                            </div>
                                                    <div class="ml-2 flex items-baseline text-sm font-semibold" :class="changes.average >= 0 ? 'text-green-600' : 'text-red-600'">
                                                        <span v-if="filter !== 'all'">
                                                            {{ changes.average >= 0 ? '+' : '' }}{{ changes.average.toFixed(2) }}%
                                    </span>
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                </div>
                            </div>
                        </div>

                            <!-- Active Branches -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">Active Branches</dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">
                                                        {{ activeBranches }} / {{ totalBranches }}
                            </div>
                                                </dd>
                                            </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <!-- Sales Trend -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Trend</h3>
                            <LineChart
                                        :data="filteredSalesTrend.datasets"
                                        :labels="filteredSalesTrend.labels"
                                        :height="300"
                            />
                        </div>
                    </div>

                            <!-- Branch Performance -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Performance</h3>
                            <BarChart
                                        :data="filteredBranchPerformance.data"
                                        :labels="filteredBranchPerformance.labels"
                                        :backgroundColor="filteredBranchPerformance.backgroundColor"
                                        :borderColor="filteredBranchPerformance.borderColor"
                                        :height="300"
                            />
                        </div>
                    </div>
                </div>

                        <!-- Payment Methods -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Methods</h3>
                                <div v-if="paymentMethods.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div v-for="method in paymentMethods" :key="method.name" class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-700">{{ method.name }}</span>
                                            <span class="text-gray-900 font-medium">{{ method.percentage }}%</span>
                                        </div>
                                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                            <div
                                                class="bg-indigo-600 h-2 rounded-full"
                                                :style="{ width: method.percentage + '%' }"
                                            ></div>
                                    </div>
                                    </div>
                                </div>
                                <p v-else class="text-gray-500 text-center">No payment methods data available</p>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                                <div v-if="recentActivity.length > 0" class="space-y-4">
                                    <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                            <div class="p-2 rounded-full" :class="getActivityColor(activity.type)">
                                                <component :is="getActivityIcon(activity.type)" class="h-5 w-5 text-white" />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900">{{ activity.message }}</p>
                                            <p class="text-sm text-gray-500">{{ activity.time }}</p>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-gray-500 text-center">No recent activities found</p>
                            </div>
                        </div>

                        <!-- Recent Sales -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Sales</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="sale in recentSales" :key="sale.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ formatDate(sale.created_at) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ sale.seller?.name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    KES {{ Number(sale.amount).toFixed(2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <Link
                                                        :href="`/sales/${sale.id}`"
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        View
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
