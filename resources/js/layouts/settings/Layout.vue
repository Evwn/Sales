<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <template #header>
            <slot name="header"></slot>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <slot></slot>
            </div>
        </div>
    </AppLayout>
</template>

<script lang="ts">
export default {
    name: 'SettingsLayout'
};
</script>
