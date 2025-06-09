<script setup lang="ts">
defineProps<{
    type?: 'submit' | 'button' | 'reset';
    disabled?: boolean;
}>();
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>

<style scoped>
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script lang="ts">
export default {
    name: 'PrimaryButton'
};
</script> 