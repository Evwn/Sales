<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Menu, Sun, Moon, ChevronDown, Settings, LogOut } from 'lucide-vue-next';
import type { BreadcrumbItemType, PageProps } from '@/types';
import { computed, ref } from 'vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<PageProps>();
const isDark = ref(localStorage.getItem('theme') === 'dark');

const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    document.documentElement.classList.toggle('dark', isDark.value);
};

const navItems = [
    { href: '/dashboard', label: 'Dashboard' },
    { href: '/businesses', label: 'Businesses' },
    { href: '/branches', label: 'Branches' },
    { href: '/sellers', label: 'Sellers' },
    { href: '/products', label: 'Inventory' },
    { href: '/inventory', label: 'Products' },
    { href: '/discounts', label: 'Discounts' },
    { href: '/sales', label: 'Sales' },
    { href: '/reports', label: 'Reports' },
];

const toggleSidebar = () => {
    emit('toggle-sidebar');
};

const emit = defineEmits<{
    (e: 'toggle-sidebar'): void;
}>();

defineOptions({
    name: 'AppHeader'
});
</script>

<template>
    <header class="sticky top-0 z-40 border-b bg-background">
        <div class="container flex h-16 items-center justify-between py-4">
            <div class="flex items-center gap-6">
                <Button variant="ghost" size="icon" class="-ml-4 lg:hidden" @click="toggleSidebar">
                    <Menu class="size-5" />
                    <span class="sr-only">Toggle sidebar</span>
                </Button>

                <!-- Logo -->
                <Link href="/dashboard" class="flex items-center gap-2 font-semibold">
                    Sales Management
                </Link>

                <!-- Navigation -->
                <nav class="hidden md:flex md:gap-6">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        :class="{ 'text-foreground': page.url === item.href }"
                    >
                        {{ item.label }}
                    </Link>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <Button variant="ghost" size="icon" class="h-9 w-9" @click="toggleTheme">
                        <span class="sr-only">Toggle theme</span>
                        <component :is="isDark ? Sun : Moon" class="size-5" />
                    </Button>
                </div>
                <div class="relative">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" class="flex items-center gap-2 hover:opacity-80">
                                <div class="flex items-center gap-2 text-sm">
                                    <span>{{ page.props.auth.user.name }}</span>
                                    <ChevronDown class="size-4" />
                                </div>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuItem as-child>
                                <Link href="/settings/profile" class="flex items-center gap-2">
                                    <Settings class="size-4" />
                                    <span>Settings</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/logout" method="post" as="button" class="flex items-center gap-2 text-red-600">
                                    <LogOut class="size-4" />
                                    <span>Logout</span>
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div v-if="breadcrumbs?.length" class="container border-t py-3">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li v-for="(item, index) in breadcrumbs" :key="index">
                        <div class="flex items-center">
                            <ChevronRight v-if="index > 0" class="size-5 flex-shrink-0 text-muted-foreground" aria-hidden="true" />
                            <Link
                                v-if="item.href"
                                :href="item.href"
                                :class="[
                                    index > 0 ? 'ml-4' : '',
                                    'text-sm font-medium text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                {{ item.title }}
                            </Link>
                            <span
                                v-else
                                :class="[
                                    index > 0 ? 'ml-4' : '',
                                    'text-sm font-medium text-foreground'
                                ]"
                                aria-current="page"
                            >
                                {{ item.title }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </header>
</template>

<script lang="ts">
export default {};
</script>
