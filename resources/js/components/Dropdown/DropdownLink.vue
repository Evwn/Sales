<template>
  <div>
    <button v-if="as === 'button'"
      type="submit"
      class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
    >
      <slot />
    </button>

    <Link v-else
      :href="href"
      class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
    >
      <slot />
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: {
    type: String,
    required: false,
  },
  as: {
    type: String,
    default: 'link',
  },
});
</script>

<script>
export default {
  name: 'DropdownLink',
};
</script> 
