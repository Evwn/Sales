<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, key) in links" :key="key">
        <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded">
          {{ cleanLabel(link.label) }}
        </div>
        <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-50': link.active }" :href="link.url">
          {{ cleanLabel(link.label) }}
        </Link>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

defineProps<{
  links: PaginationLink[];
}>();

function cleanLabel(label: string) {
  // Remove « and » and their HTML entities
  return label.replace(/&laquo;|&raquo;|«|»/g, '').trim();
}
</script>

<script lang="ts">
export default {
  name: 'Pagination'
};
</script> 
