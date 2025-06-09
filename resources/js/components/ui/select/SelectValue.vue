<template>
  <ListboxOption
    v-slot="{ active, selected }"
    :value="value"
    class="relative cursor-default select-none py-2 pl-10 pr-4"
    :class="[
      active ? 'bg-accent text-accent-foreground' : 'text-foreground',
      'relative cursor-default select-none py-2 pl-10 pr-4'
    ]"
  >
    <span class="block truncate" :class="{ 'font-medium': selected, 'font-normal': !selected }">
      <slot />
    </span>
    <span
      v-if="selected"
      class="absolute inset-y-0 left-0 flex items-center pl-3 text-accent-foreground"
    >
      <Check class="h-5 w-5" aria-hidden="true" />
    </span>
  </ListboxOption>
</template>

<script setup lang="ts">
import { ListboxOption } from '@headlessui/vue';
import { Check } from 'lucide-vue-next';

defineProps<{
  value: any;
}>();
</script> 