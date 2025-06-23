<script setup lang="ts">
defineProps<{
    value?: string;
    for?: string;
}>();
</script>

<template>
    <label class="block font-medium text-sm text-gray-700">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

<style scoped>
.text-gray-700 {
    color: rgb(55, 65, 81);
}
</style>

<script lang="ts">
export default {
    name: 'InputLabel'
};
</script> 
