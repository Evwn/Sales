<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { Quote } from '@/types';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { animate, svg, stagger,utils,text } from 'animejs';

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
    const targets = document.querySelectorAll('.line');
    animate(svg.createDrawable('.line'), {
    draw: ['0 0', '0 1', '1 1'],
    stroke: ['#ffffff', '#808080', '#000000'],
    ease: 'inOutQuad',
    duration: 2000,
    delay: stagger(100),
    loop: true
    });

const { chars } = text.split('p', {
  chars: { wrap: true },
});

animate(chars, {
  y: ['75%', '0%'],
  duration: 750,
  ease: 'out(3)',
  delay: stagger(50),
  loop: true,
  alternate: true,
});

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
    <div class="relative min-h-screen bg-white/40 backdrop-blur-sm p-4 rounded text-white overflow-hidden">

    <!-- SVG background -->
    <svg class="absolute inset-0 w-full h-full text-white/20 pointer-events-none" 
        viewBox="0 0 304 112" 
        preserveAspectRatio="xMidYMid meet">
        <g stroke="currentColor" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <path class="line" d="M59 90V56.136C58.66 46.48 51.225 39 42 39c-9.389 0-17 7.611-17 17s7.611 17 17 17h8.5v17H42C23.222 90 8 74.778 8 56s15.222-34 34-34c18.61 0 33.433 14.994 34 33.875V90H59z"/>
        <polyline class="line" points="59 22.035 59 90 76 90 76 22 59 22"/>
        <path class="line" d="M59 90V55.74C59.567 36.993 74.39 22 93 22c18.778 0 34 15.222 34 34v34h-17V56c0-9.389-7.611-17-17-17-9.225 0-16.66 7.48-17 17.136V90H59z"/>
        <polyline class="line" points="127 22.055 127 90 144 90 144 22 127 22"/>
        <path class="line" d="M127 90V55.74C127.567 36.993 142.39 22 161 22c18.778 0 34 15.222 34 34v34h-17V56c0-9.389-7.611-17-17-17-9.225 0-16.66 7.48-17 17.136V90h-17z"/>
        <path class="line" d="M118.5 22a8.5 8.5 0 1 1-8.477 9.067v-1.134c.283-4.42 3.966-7.933 8.477-7.933z"/>
        <path class="line" d="M144 73c-9.389 0-17-7.611-17-17v-8.5h-17V56c0 18.778 15.222 34 34 34V73z"/>
        <path class="line" d="M178 90V55.74C178.567 36.993 193.39 22 212 22c18.778 0 34 15.222 34 34v34h-17V56c0-9.389-7.611-17-17-17-9.225 0-16.66 7.48-17 17.136V90h-17z"/>
        <path class="line" d="M263 73c-9.389 0-17-7.611-17-17s7.611-17 17-17c9.18 0 16.58 7.4 17 17h-17v17h34V55.875C296.433 36.994 281.61 22 263 22c-18.778 0-34 15.222-34 34s15.222 34 34 34V73z"/>
        <path class="line" d="M288.477 73A8.5 8.5 0 1 1 280 82.067v-1.134c.295-4.42 3.967-7.933 8.477-7.933z"/>
        </g>
    </svg>

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
                <div class="small row"></div>
                <div class="flex justify-center">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Sales Management System</h1>
                </div>
                <div class="small row"></div>

                <div class="mt-8 flex justify-center">
                    <blockquote class="text-center max-w-2xl">
                        <p class="text-xl font-medium text-gray-600 dark:text-gray-300">
                            "{{ quote.message }}"
                        </p>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            — {{ quote.author }}
                        </p>
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
