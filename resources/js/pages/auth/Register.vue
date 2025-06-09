<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps<{
    quote: {
        message: string;
        author: string;
    };
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen flex">
        <!-- Left side with form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900">Sales Management System</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Create your business owner account to get started.
                    </p>
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div>
                        <InputLabel for="name" value="Business Owner Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Enter your full name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email Address" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Enter your business email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Create a secure password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm your password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div>
                        <PrimaryButton
                            class="w-full flex justify-center"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Create Business Owner Account
                        </PrimaryButton>
                    </div>
                </form>

                <p class="mt-4 text-center text-sm text-gray-600">
                    Already have an account?
                    <Link
                        href="/login"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        Log in
                    </Link>
                </p>
            </div>
        </div>

        <!-- Right side with quote -->
        <div class="hidden lg:block relative w-0 flex-1 bg-indigo-600">
            <div class="absolute inset-0 flex items-center justify-center p-8">
                <div class="max-w-md w-full">
                    <blockquote class="text-white">
                        <p class="text-xl font-medium mb-4">"{{ quote.message }}"</p>
                        <footer class="text-sm">— {{ quote.author }}</footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
</template>
