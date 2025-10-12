<template>
  <div class="status-chart">
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Pie } from "vue-chartjs";
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  Title
} from "chart.js";
//import ChartDataLabels from 'chartjs-plugin-datalabels';

ChartJS.register(ArcElement, Tooltip, Legend, Title);

// 🧩 Props
interface Props {
  deliveries: any[];
}
const props = defineProps<Props>();

// 🎯 Etiquetas y colores por estado
const statusMap = {
  1: { label: "No iniciado", color: "#9e9e9e" },
  2: { label: "En progreso", color: "#2196f3" },
  3: { label: "Completado", color: "#4caf50" },
};

// 🧮 Agrupar entregables por estado
const grouped = computed(() => {
  const counts: Record<number, number> = { 1: 0, 2: 0, 3: 0 };
  props.deliveries.forEach((d) => {
    const id = d.data?.status_id ?? 0;
    if (counts[id] !== undefined) counts[id]++;
  });
  return counts;
});

// 📊 Datos del gráfico
const chartData = computed(() => {
  const data: number[] = [];
  const labels: string[] = [];
  const colors: string[] = [];

  Object.entries(statusMap).forEach(([id, info]) => {
    const count = grouped.value[Number(id)];
    if (count > 0) { // solo agregamos si hay al menos 1
      data.push(count);
      labels.push(info.label);
      colors.push(info.color);
    }
  });

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: colors,
        borderWidth: 10,
        borderColor: "#fff",
        hoverOffset: 8,
      },
    ],
  };
});


// ⚙️ Opciones del gráfico con datalabels
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "bottom" as const,
      labels: {
        boxWidth: 14,
        padding: 10,
        font: { size: 13 },
      },
    },
    tooltip: {
      callbacks: {
        label: (ctx: any) => {
          const label = ctx.label || "";
          const value = ctx.raw || 0;
          const total = ctx.chart._metasets[0].total;
          const percent = total ? ((value / total) * 100).toFixed(1) : 0;
          return `${label}: ${value} (${percent}%)`;
        },
      },
    },
    datalabels: {
      color: '#fff',
      font: { weight: 'bold', size: 14 },
      formatter: (value: number, context: any) => {
        const total = context.chart._metasets[0].total;
        const percent = total ? ((value / total) * 100).toFixed(1) : 0;
        return `${percent}%`;
      }
    }
  },
};
</script>

<style scoped>
.status-chart {
  height: 300px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
