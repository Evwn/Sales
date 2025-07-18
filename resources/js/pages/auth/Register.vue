<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { ref, computed } from 'vue';

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

const passwordRequirements = ref('');

const isPasswordValid = computed(() => {
  // At least 1 lowercase, 1 uppercase, 1 number, 1 symbol, min 8 chars
  const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/;
  if (!regex.test(form.password)) {
    passwordRequirements.value = 'Password must be at least 8 characters and contain at least one uppercase letter, one lowercase letter, one number, and one symbol.';
    return false;
  }
  passwordRequirements.value = '';
  return true;
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
                        Create your account to get started.
                    </p>
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div>
                        <InputLabel for="name" value="Full Name" />
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
                            placeholder="Enter your email"
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

                    <div v-if="!isPasswordValid && form.password" class="text-red-600 text-sm mt-1">
                        {{ passwordRequirements }}
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

                    <div class="flex items-center justify-end mt-4">
                        <Link
                            href="/login"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Already registered?
                        </Link>

                        <PrimaryButton class="ml-4" :disabled="form.processing || !isPasswordValid">
                            Register
                        </PrimaryButton>
                    </div>
                </form>
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
