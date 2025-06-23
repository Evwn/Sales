<template>
  <ListboxOption
    v-slot="{ active, selected }"
    :value="value"
    class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
    :class="[
      active ? 'bg-accent text-accent-foreground' : 'text-foreground',
    ]"
  >
    <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
      <Check v-if="selected" class="h-4 w-4" />
    </span>
    <slot />
  </ListboxOption>
</template>

<script setup lang="ts">
import { ListboxOption } from '@headlessui/vue';
import { Check } from 'lucide-vue-next';

defineProps<{
  value: any;
}>();
</script> 
