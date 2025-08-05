<template #header>
      <div class="p-6 bg-white/5 backdrop-blur-sm">
      <div class="flex justify-between items-center px-4 sm:px-6 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ title }}
    </h2>
      <slot name="actions"></slot>
      <Link
        v-if="button && button.show !== false"
        :href="button.link"
        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
      >
      <component :is="button.icon" v-if="button.icon"/>
        {{ button.text }}
      </Link>
      </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { Component } from 'lucide-vue-next';
const props = defineProps({
  title: { type: String, required: true },
  button: { type: Object, default: null }, // { text, link, show }
});
</script> 