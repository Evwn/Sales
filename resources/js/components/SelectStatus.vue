<template>
  <select v-model="selected" @change="emitChange" class="input input-bordered w-full">
    <option value="">All Statuses</option>
    <option value="paid">Paid</option>
    <option value="pending">Pending</option>
    <option value="partial">Partial</option>
    <option value="draft">Draft</option>
  </select>
</template>
<script setup>
import { ref, watch, defineEmits } from 'vue';
const props = defineProps({
  modelValue: String
});
const emit = defineEmits(['update:modelValue']);
const selected = ref(props.modelValue);
watch(() => props.modelValue, v => { selected.value = v; });
function emitChange() { emit('update:modelValue', selected.value); }
</script> 
