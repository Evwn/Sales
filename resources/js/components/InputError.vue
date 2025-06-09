<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-if="message" class="text-sm text-red-600">
        {{ message }}
    </div>
</template>

<style scoped>
.text-red-600 {
    color: rgb(220, 38, 38);
}
</style>

<script lang="ts">
export default {
    name: 'InputError'
};
</script>
