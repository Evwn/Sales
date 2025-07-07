<template>
  <canvas ref="chartRef"></canvas>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const chartRef = ref(null);
let chart = null;

const initChart = () => {
  if (chart) {
    chart.destroy();
  }

  const ctx = chartRef.value.getContext('2d');
  chart = new Chart(ctx, {
    type: 'doughnut',
    data: props.data,
    options: props.options
  });
};

watch(() => props.data, () => {
  initChart();
}, { deep: true });

watch(() => props.options, () => {
  initChart();
}, { deep: true });

onMounted(() => {
  initChart();
});

const getChartImage = () => {
  if (chart) {
    return chart.toBase64Image();
  }
  return null;
};

defineExpose({ getChartImage });
</script> 
