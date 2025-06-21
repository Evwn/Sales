<template>
  <select v-model="selected" @change="emitChange" class="input input-bordered w-full" :disabled="options.length === 0">
    <option value="">{{ options.length === 0 ? 'No products found' : 'All Products' }}</option>
    <option v-for="p in options" :key="p.id" :value="p.id">{{ p.name }}</option>
  </select>
</template>
<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
const props = defineProps({
  modelValue: [String, Number],
  options: { type: Array, default: () => [] }
});
const emit = defineEmits(['update:modelValue']);
const selected = ref(props.modelValue);
watch(() => props.modelValue, v => { selected.value = v; });
function emitChange() { emit('update:modelValue', selected.value); }
</script> 