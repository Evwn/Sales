<template>
  <div :style="{ height: height + 'px' }">
    <canvas ref="chartRef"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  labels: {
    type: Array,
    required: true
  },
  height: {
    type: Number,
    default: 200
  },
  backgroundColor: {
    type: Array,
    default: () => []
  },
  borderColor: {
    type: Array,
    default: () => []
  }
});

const chartRef = ref(null);
let chart = null;

const createChart = () => {
  if (chart) {
    chart.destroy();
  }

  const ctx = chartRef.value.getContext('2d');
  
  // Use custom colors if provided, otherwise use default
  const backgroundColor = props.backgroundColor.length > 0 
    ? props.backgroundColor 
    : 'rgba(59, 130, 246, 0.5)';
  
  const borderColor = props.borderColor.length > 0 
    ? props.borderColor 
    : 'rgb(59, 130, 246)';

  chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.labels,
      datasets: [{
        label: 'Sales',
        data: props.data,
        backgroundColor: backgroundColor,
        borderColor: borderColor,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return 'KES ' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
};

onMounted(() => {
  createChart();
});

watch(() => [props.data, props.labels, props.backgroundColor, props.borderColor], () => {
  createChart();
}, { deep: true });
</script> 
