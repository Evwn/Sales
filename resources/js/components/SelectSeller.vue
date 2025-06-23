<template>
  <select v-model="selected" @change="emitChange" class="input input-bordered w-full" :disabled="options.length === 0">
    <option value="">{{ options.length === 0 ? 'No sellers found' : 'All Sellers' }}</option>
    <option v-for="s in options" :key="s.id" :value="s.id">{{ s.name }}</option>
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
