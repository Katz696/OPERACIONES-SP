<template>
  <n-card title="Tendencia de Cumplimiento" size="small">
    <Bar
      :data="chartData"
      :options="chartOptions"
      style="height: 400px; width: 100%"
    />
  </n-card>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, CategoryScale, LinearScale, BarElement);

// Props: entregables por fase
interface Phase {
  title: string;
  percentage: number;         // Real
  percentage_planned: number; // Planeado
}

const props = defineProps<{
  phases: Phase[];
}>();

// Labels (fases)
const labels = computed(() => props.phases.map(p => p.title.replace(/<[^>]*>/g, '')));

// Data para Chart.js
const chartData = computed(() => ({
  labels: labels.value,
  datasets: [
    {
      label: 'Real',
      data: props.phases.map(p => p.percentage),
      backgroundColor: '#4caf50',
    },
    {
      label: 'Planeado',
      data: props.phases.map(p => p.percentage_planned),
      backgroundColor: '#ff9800',
    },
  ],
}));

// Opciones
const chartOptions = {
  indexAxis: 'x',
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      max: 100,
      title: {
        display: true,
        text: 'Cumplimiento (%)',
      },
    },
    x: {
      title: {
        display: true,
        text: 'Fases',
      },
    },
  },
  plugins: {
    legend: {
      position: 'top',
    },
    tooltip: {
      callbacks: {
        label: (ctx: any) => `${ctx.dataset.label}: ${ctx.raw}%`,
      },
    },
  },
};
</script>
