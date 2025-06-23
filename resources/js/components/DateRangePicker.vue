<template>
  <div class="flex gap-2 items-center">
    <input type="date" v-model="range.start" @input="emitChange" class="input input-bordered" />
    <span>to</span>
    <input type="date" v-model="range.end" @input="emitChange" class="input input-bordered" />
  </div>
</template>
<script setup>
import { ref, watch, defineEmits } from 'vue';
const props = defineProps({
  modelValue: { type: Object, default: () => ({ start: '', end: '' }) }
});
const emit = defineEmits(['update:modelValue']);
const range = ref({ ...props.modelValue });
watch(() => props.modelValue, v => { range.value = { ...v }; });
function emitChange() {
  emit('update:modelValue', { ...range.value });
}
</script> 
