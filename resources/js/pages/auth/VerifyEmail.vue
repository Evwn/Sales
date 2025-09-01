<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post('/email/verification-notification');
};

const logout = () => {
    form.post('/logout');
};
</script>
<style scoped>
.background{
    background-image: url('/images/Background.png');
    background-size: cover;
    background-position: center;
    height: 100vb;
    color: rgb(12, 2, 2);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;

}

</style>
<template>
    <Head title="Verification" />
    <div class="background min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Email Verification</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Thanks for signing up! Please verify your email address.
                </p>
            </div>

            <div v-if="status === 'verification-link-sent'" class="mb-4 font-medium text-sm text-green-600">
                A new verification link has been sent to your email address.
            </div>

            <div class="mt-4 flex items-center justify-between">
                <form method="POST" @submit.prevent="submit">
                    <PrimaryButton :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Resend Verification Email
                    </PrimaryButton>
                </form>

                <form method="POST" @submit.prevent="logout">
                    <SecondaryButton type="submit" class="ml-4">
                        Log Out
                    </SecondaryButton>
                </form>
            </div>
        </div>
    </div>
</template>
