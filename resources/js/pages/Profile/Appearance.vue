<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import * as SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItemType } from '@/types';

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Settings',
        href: '/settings/profile',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];

const form = useForm({
    theme: 'system',
});

const submit = () => {
    form.put('/settings/appearance', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Appearance Settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Appearance</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Customize the appearance of the application. Automatically switch between day and night themes.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="theme">Theme</Label>
                        <Select v-model="form.theme">
                            <SelectTrigger class="max-w-xl">
                                <SelectValue placeholder="Select a theme" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="light">Light</SelectItem>
                                <SelectItem value="dark">Dark</SelectItem>
                                <SelectItem value="system">System</SelectItem>
                            </SelectContent>
                        </Select>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Select the theme for the dashboard.
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save changes</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template> 