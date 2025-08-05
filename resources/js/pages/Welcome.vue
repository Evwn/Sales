<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { Quote } from '@/types';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

defineOptions({
    name: 'Welcome'
});

defineProps<{
    canLogin: boolean;
    canRegister: boolean;
}>();

const page = usePage();
const quote = page.props.quote as Quote;
const showPOS = ref(false);

function getDeviceUUID() {
  return localStorage.getItem('device_uuid');
}

onMounted(() => {
  const uuid = getDeviceUUID();
  if (uuid) {
    fetch(`/api/check-device?uuid=${uuid}`)
      .then(res => res.json())
      .then(data => {
        showPOS.value = data.deviceRegistered;
      });
  }
});
</script>

<template>
    <div class="background min-h-screen bg-cover bg-center bg-white/40 backdrop-blur-md overflow-hidden">
        <Head title="Welcome" />
        <div class=" min-h-screen flex items">
    <div class="relative min-h-screen bg-white/40 backdrop-blur-sm p-4 rounded text-white">
        <div v-if="canLogin" class="p-6 text-center">
            <template>
                <Link href="/login"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Log in
                </Link>

                <Link v-if="canRegister" href="/register"
                    class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Register
                </Link>
            </template>
        </div>

        <div class="flex min-h-screen items-center justify-center">
            <div class="max-w-7xl mx-auto p-6 lg:p-8">
                <div class="flex justify-center">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Sales Management System</h1>
                </div>

                <div class="mt-8 flex justify-center">
                    <blockquote class="text-center max-w-2xl">
                        <p class="text-xl font-medium text-gray-600 dark:text-gray-300">
                            "{{ quote.message }}"
                        </p>
                        <footer class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            — {{ quote.author }}
                        </footer>
                    </blockquote>
                </div>

                <div class="mt-16">
                    <div class="grid grid-cols-1 gap-6 lg:gap-8">
                        <div class="scale-100 p-6 bg-white/80 backdrop-blur-sm rounded text-white dark:bg-gray-800/50 ">
                            <div>
                                <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">Welcome to Sales Management</h2>
                                <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                    Get started by logging in or registering a new account to manage your business sales, inventory, and more.
                                </p>
                                <div class="mt-4 flex items-center">
                                    <div class="text-sm">
                                        <Link href="/login" class="font-bold text-black hover:text-primary-500">
                                            Log in to your account
                                        </Link>
                                        <span class="text-gray-500"> to get started.</span>
                                        <Link href="/register" class="font-bold text-black hover:text-primary-500">
                                            create a new account
                                        </Link>
                                        <span class="text-gray-500"> to get started</span>
                                        <span v-if="!showPOS" class="text-gray-500">. </span>
                                        <span v-if="showPOS" class="text-gray-500"> or go to</span>
                                        <Link v-if="showPOS" href="/pos" class="font-bold text-black hover:text-primary-500">
                                            POS
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Remove the floating POS button -->
    </div>
        </div>
    </div>
</template>
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
<script lang="ts">
export default {};
</script>
