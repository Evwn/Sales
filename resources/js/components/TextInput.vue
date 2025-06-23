<script setup lang="ts">
import { onMounted, ref } from 'vue';

const input = ref<HTMLInputElement | null>(null);

const props = defineProps<{
    modelValue: string;
    type?: string;
}>();

defineEmits(['update:modelValue']);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :type="type"
    />
</template>

<style scoped>
input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    outline: none;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 1px #6366f1;
}
</style>

<script lang="ts">
export default {
    name: 'TextInput'
};
</script> 
