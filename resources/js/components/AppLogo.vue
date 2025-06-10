<script setup lang="ts">
import { User } from '@/types';

defineProps<{
    user?: User;
}>();
</script>

<script lang="ts">
export default {};
</script>

<template>
    <div class="flex items-center">
        <img
            :src="user?.logo_url || '/favicon.svg'"
            class="h-8 w-8 rounded-full object-cover"
            alt="Logo"
        />
        <span class="ml-2 text-xl font-semibold text-gray-800 dark:text-gray-200">Sales Management System</span>
    </div>
</template>
