<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>

<script setup>
import { computed } from "vue";
import { Bar } from "vue-chartjs";
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from "chart.js";
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
  activities: { type: Array, required: true },
  users: { type: Array, required: true }
});

const chartData = computed(() => {
  const map = {};
  for (const u of props.users) map[u.id] = 0;
  for (const a of props.activities) map[a.data.user_id] = (map[a.data.user_id] || 0) + 1;

  return {
    labels: props.users.map((u) => u.name),
    datasets: [
      {
        label: "Actividades asignadas",
        data: Object.values(map),
        backgroundColor: "#2196f3"
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true } }
};
</script>
