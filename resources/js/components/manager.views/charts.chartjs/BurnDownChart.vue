<template>
  <div class="burndown-chart">
    <Line v-if="chartData" :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(LineElement, PointElement, CategoryScale, LinearScale, Title, Tooltip, Legend)

const props = defineProps<{
  labels: string[]
  plannedData: number[]
  actualData: number[]
}>()


const colorData = computed(() =>
  props.actualData.map((v, i) => (v > props.plannedData[i] ? '#f44336' : '#4caf50'))
)

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [
    {
      label: 'Avance Planeado',
      data: props.plannedData,
      borderColor: '#2196f3',
      borderWidth: 2,
      tension: 0.3,
      fill: false
    },
    {
      label: 'Avance Real',
      data: props.actualData,
      borderColor: '#888888',
      borderWidth: 2,
      tension: 0.3,
      fill: false,
      pointBackgroundColor: colorData.value,
      pointRadius: 5
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' },
    title: {
      display: true,
      text: 'Gráfico Burndown del Proyecto',
      font: { size: 16, weight: 'bold' }
    }
  },
  scales: {
    x: { title: { display: true, text: 'Días' } },
    y: {
      min: 0,
      max: 100,
      title: { display: true, text: '% Trabajo Restante' },
      ticks: { callback: (v: number) => `${v}%` }
    }
  }
}
</script>

<style scoped>
.burndown-chart {
  height: 600px;
  width: 1000px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
