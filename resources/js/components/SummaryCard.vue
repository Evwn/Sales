<template>
  <div class="summary-card bg-white rounded-lg shadow p-4 flex flex-col items-start">
    <div class="flex items-center mb-2">
      <span v-if="icon" :class="`mdi ${icon} text-2xl mr-2`"></span>
      <span class="font-semibold text-lg">{{ title }}</span>
    </div>
    <div class="text-2xl font-bold">
      <template v-if="Array.isArray(value)">
        {{ value.map(v => v.name || v).join(', ') || '—' }}
      </template>
      <template v-else>
        {{ value ?? '—' }}
      </template>
    </div>
  </div>
</template>
<script setup>
const props = defineProps({
  title: String,
  value: [String, Number, Array],
  icon: String
});
</script>
<style scoped>
.summary-card {
  min-width: 180px;
}
</style> 
