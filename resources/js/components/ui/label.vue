<template>
    <label
        :for="htmlFor"
        class="block font-medium text-sm text-gray-700 dark:text-gray-300"
    >
        <slot />
    </label>
</template>

<script setup lang="ts">
defineProps<{
    htmlFor?: string;
}>();
</script> 
