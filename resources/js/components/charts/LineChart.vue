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
  }
});

const chartRef = ref(null);
let chart = null;

const createChart = () => {
  if (chart) {
    chart.destroy();
  }

  const ctx = chartRef.value.getContext('2d');
  
  // Check if data is an array of datasets (multi-line) or single dataset
  const datasets = Array.isArray(props.data) && props.data.length > 0 && props.data[0].data 
    ? props.data // Multi-line chart with datasets
    : [{ // Single line chart
        label: 'Sales',
        data: props.data,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgb(59, 130, 246)20',
        tension: 0.1,
        fill: false
      }];

  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.labels,
      datasets: datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: datasets.length > 1, // Show legend only for multi-line charts
          position: 'top',
          labels: {
            usePointStyle: true,
            padding: 20
          }
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
      },
      interaction: {
        intersect: false,
        mode: 'index'
      },
      elements: {
        point: {
          radius: 4,
          hoverRadius: 6
        }
      }
    }
  });
};

onMounted(() => {
  createChart();
});

watch(() => [props.data, props.labels], () => {
  createChart();
}, { deep: true });
</script> 