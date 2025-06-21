<template>
  <select v-model="selected" @change="emitChange" class="input input-bordered w-full">
    <option value="">All Businesses</option>
    <option v-for="b in options" :key="b.id" :value="b.id">{{ b.name }}</option>
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