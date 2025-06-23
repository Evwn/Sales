<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts';
import { Button } from '@/components/ui/button';

defineProps<{
    status: number;
    message: string;
}>();
</script>

<template>
    <AppLayout>
        <Head :title="`Error ${status}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                            Error {{ status }}
                        </h2>
                        <p class="text-gray-600 mb-6">
                            {{ message }}
                        </p>
                        <Button
                            @click="router.visit('/dashboard')"
                            variant="default"
                        >
                            Return to Dashboard
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 
