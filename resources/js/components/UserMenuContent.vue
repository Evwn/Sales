<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { User } from '@/types';

defineProps<{
    user: User;
}>();

const handleLogout = (e: Event) => {
    e.preventDefault();
    // Handle any additional logout logic here if needed
};
</script>

<template>
    <div class="flex items-center gap-4 p-4">
        <div class="flex-1 space-y-1">
            <p class="text-sm font-medium leading-none">{{ user.name }}</p>
            <p class="text-xs leading-none text-muted-foreground">{{ user.email }}</p>
        </div>
    </div>
    <div class="border-t border-border/50">
        <div class="flex flex-col">
            <Link class="block w-full" href="/settings/profile" prefetch as="button">
                <div class="relative flex cursor-default select-none items-center rounded-sm px-4 py-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground">
                    Profile
                </div>
            </Link>
            <Link class="block w-full" method="post" href="/logout" @click="handleLogout" as="button">
                <div class="relative flex cursor-default select-none items-center rounded-sm px-4 py-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground">
                    Log out
                </div>
            </Link>
        </div>
    </div>
</template>

<script lang="ts">
export default {};
</script>
