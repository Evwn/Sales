<template>
  <div :style="{ height: height + 'px', position: 'relative' }">
    <canvas ref="chartRef"></canvas>
    <div v-if="tooltip.show" :style="{ position: 'fixed', left: tooltip.x + 10 + 'px', top: tooltip.y + 10 + 'px', background: 'rgba(0,0,0,0.8)', color: '#fff', padding: '4px 10px', borderRadius: '4px', pointerEvents: 'none', zIndex: 1000, fontSize: '13px' }">
      {{ tooltip.text }}
    </div>
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
  },
  dayBoundaries: {
    type: Array,
    default: () => []
  },
  sectionLabels: {
    type: Array,
    default: () => []
  },
  sectionFullLabels: {
    type: Array,
    default: () => []
  }
});

const chartRef = ref(null);
let chart = null;
let tooltip = ref({ show: false, x: 0, y: 0, text: '' });

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
        },
        dayBackground: {
          boundaries: props.dayBoundaries,
          colorA: '#f3f4f6',
          colorB: '#fff',
          sectionLabels: props.sectionLabels,
          sectionFullLabels: props.sectionFullLabels
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
    },
    plugins: [
      {
        id: 'dayBackground',
        beforeDraw: (chart, args, options) => {
          if (!options || !options.boundaries || options.boundaries.length < 2) return;
          const { ctx, chartArea, scales } = chart;
          const { boundaries, colorA, colorB } = options;
          let lastX = chartArea.left;
          boundaries.forEach((boundary, i) => {
            const nextX = scales.x.getPixelForValue(boundary);
            ctx.save();
            ctx.fillStyle = i % 2 === 0 ? colorA : colorB;
            ctx.fillRect(lastX, chartArea.top, nextX - lastX, chartArea.bottom - chartArea.top);
            ctx.restore();
            // Watermark removed
            lastX = nextX;
          });
          // Fill the last section
          ctx.save();
          ctx.fillStyle = boundaries.length % 2 === 0 ? colorA : colorB;
          ctx.fillRect(lastX, chartArea.top, chartArea.right - lastX, chartArea.bottom - chartArea.top);
          ctx.restore();
        }
      }
    ]
  });
};

// Tooltip logic
function getSectionIndex(x) {
  if (!props.dayBoundaries || props.dayBoundaries.length === 0) return -1;
  const chartArea = chart.chartArea;
  const scales = chart.scales.x;
  for (let i = 0; i < props.dayBoundaries.length; i++) {
    const start = scales.getPixelForValue(props.dayBoundaries[i]);
    const end = i + 1 < props.dayBoundaries.length ? scales.getPixelForValue(props.dayBoundaries[i + 1]) : chartArea.right;
    if (x >= start && x < end) return i;
  }
  return -1;
}

function handleMouseMove(e) {
  if (!chart) return;
  const rect = chartRef.value.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  const idx = getSectionIndex(x);
  if (idx !== -1 && props.sectionFullLabels && props.sectionFullLabels[idx]) {
    tooltip.value = { show: true, x: e.clientX, y: e.clientY, text: props.sectionFullLabels[idx] };
  } else {
    tooltip.value.show = false;
  }
}
function handleMouseLeave() {
  tooltip.value.show = false;
}

onMounted(() => {
  createChart();
  chartRef.value.addEventListener('mousemove', handleMouseMove);
  chartRef.value.addEventListener('mouseleave', handleMouseLeave);
});

watch(() => [props.data, props.labels, props.backgroundColor, props.borderColor, props.dayBoundaries, props.sectionLabels, props.sectionFullLabels], () => {
  createChart();
}, { deep: true });
</script> 
