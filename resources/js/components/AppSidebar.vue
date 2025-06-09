<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    Building2, 
    GitBranch, 
    Users, 
    Package, 
    BoxesIcon, 
    Percent, 
    ShoppingCart, 
    BarChart3,
    Sun,
    Moon
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { useTheme } from '@/composables/useTheme';

const { isDark, toggleTheme } = useTheme();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Businesses',
        href: '/businesses',
        icon: Building2,
    },
    {
        title: 'Branches',
        href: '/branches',
        icon: GitBranch,
    },
    {
        title: 'Sellers',
        href: '/sellers',
        icon: Users,
    },
    {
        title: 'Products',
        href: '/products',
        icon: Package,
    },
    {
        title: 'Inventory',
        href: '/inventory',
        icon: BoxesIcon,
    },
    {
        title: 'Discounts',
        href: '/discounts',
        icon: Percent,
    },
    {
        title: 'Sales',
        href: '/sales',
        icon: ShoppingCart,
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart3,
    },
];

defineOptions({
    name: 'AppSidebar'
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <div class="mt-auto px-2">
                <SidebarMenuButton @click="toggleTheme" class="w-full">
                    <component :is="isDark ? Sun : Moon" class="size-4" />
                    <span>{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
                </SidebarMenuButton>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

<script lang="ts">
export default {};
</script>
