<template>
  <Listbox
    :model-value="modelValue"
    @update:model-value="(value) => $emit('update:modelValue', value)"
    v-bind="$attrs"
  >
    <slot />
  </Listbox>
</template>

<script setup lang="ts">
import { Listbox } from '@headlessui/vue';

defineProps<{
  modelValue: any;
}>();

defineEmits<{
  'update:modelValue': [value: any];
}>();
</script> 
