<template>
  <div class="flex justify-between items-center mb-6">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ title }}
    </h2>
    <div class="flex items-center gap-2">
      <slot name="actions"></slot>
      <Link
        v-if="button && button.show !== false"
        :href="button.link"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
      >
        {{ button.text }}
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
const props = defineProps({
  title: { type: String, required: true },
  button: { type: Object, default: null }, // { text, link, show }
});
</script> 