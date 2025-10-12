<template>
    <div class="phase-performance-chart">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup lang="ts">
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface Props {
    phases: any[];
}
const props = defineProps<Props>();

// 📊 Datos
const chartData = computed(() => {
    const labels = props.phases.map((p) => `FAS - ${p.data.index}`);
    const planned = props.phases.map((p) => p.data.percentage_planned ?? 0);
    const real = props.phases.map((p) => p.data.percentage ?? 0);

    return {
        labels,
        datasets: [
            {
                label: 'Planificado',
                data: planned,
                backgroundColor: 'rgba(66, 165, 245, 0.7)', // azul
                borderColor: '#42A5F5',
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 30,
            },
            {
                label: 'Real',
                data: real,
                backgroundColor: 'rgba(102, 187, 106, 0.7)', // verde
                borderColor: '#66BB6A',
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 30,
            },
        ],
    };
});

// ⚙️ Configuración del gráfico
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                boxWidth: 14,
                padding: 12,
                font: { size: 13 },
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleFont: { size: 13, weight: 'bold' },
            bodyFont: { size: 12 },
            cornerRadius: 6,
            callbacks: {
                label: (ctx: any) => `${ctx.dataset.label}: ${ctx.formattedValue}%`,
            },
        },
    },
    scales: {
        x: {
            ticks: {
                color: '#333',
                font: { size: 12 },
            },
            grid: { display: false },
        },
        y: {
            min: 0,
            max: 100,
            ticks: {
                stepSize: 20,
                callback: (v: number) => v + '%',
                color: '#333',
                font: { size: 12 },
            },
            grid: {
                color: '#e0e0e0',
                drawBorder: false,
            },
        },
    },
};
</script>

<style scoped>
.phase-performance-chart {
    height: 350px;
    width: 100%;
}
</style>
