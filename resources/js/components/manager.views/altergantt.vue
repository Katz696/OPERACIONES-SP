<template>
    <div class="gantt-container">
        <n-card size="small" class="filter-bar" :bordered="false">
            <n-space align="center" justify="space-between" wrap>
                <n-space align="center" wrap>
                    <label for="">Fases:</label>
                    <n-select
                        v-model:value="selectedPhase"
                        :options="[
                            { label: 'Todos', value: '' }, // ← opción para quitar filtro
                            ...phases.map((p) => ({ label: p.title.replace(/<[^>]*>/g, ''), value: p.id })),
                        ]"
                        placeholder="Filtrar por fase"
                        style="width: 200px"
                    />
                    <label for="">Estados:</label>
                    <n-select
                        v-model:value="selectedStatus"
                        :options="[
                            { label: 'Todos', value: '' },
                            { label: 'No iniciado', value: 1 },
                            { label: 'En progreso', value: 2 },
                            { label: 'Completado', value: 3 },
                        ]"
                        placeholder="Filtrar por estado"
                        style="width: 200px"
                    />
                    <label for="">Vencimiento:</label>
                    <n-select
                        v-model:value="selectedOverride"
                        :options="[
                            { label: 'Todos', value: '' },
                            { label: 'Vencido', value: true },
                            { label: 'No vencido', value: false },
                        ]"
                        placeholder="Filtrar por Vencimiento"
                        style="width: 200px"
                    />
                </n-space>
            </n-space>
        </n-card>

        <!-- escala (solo eje de fechas) -->
        <!-- <div class="gantt-scale-wrapper">
            <Bar ref="chartScaleRef" :data="scaledata" :options="scaleOptions" :style="{ height: scaleHeight + 'px', width: '100%' }" />
        </div> -->

        <!-- gantt principal -->
        <div class="gantt-scroll-wrapper">
            <div class="gantt-chart-container">
                <Bar ref="chartRef" :data="chartData" :options="chartOptions" style="height: 600px; width: 100%" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAltGanttMapper } from '@/composables/useAltGanttMapper';
import { BarController, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Plugin, TimeScale, Title, Tooltip } from 'chart.js';
import 'chartjs-adapter-date-fns';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { Bar } from 'vue-chartjs';
// const scaledata = ref({ datasets: [], labels: [] });
// Registrar controlador + elementos
ChartJS.register(BarController, BarElement, CategoryScale, LinearScale, TimeScale, Title, Tooltip, Legend);

// Plugins (los tuyos, sin cambios)
const todayLine: Plugin = {
    id: 'todayLine',
    afterDatasetsDraw(chart) {
        const {
            ctx,
            chartArea: { top, bottom },
            scales: { x },
        } = chart as any;
        if (!x) return;
        ctx.save();
        ctx.beginPath();
        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(255, 26, 104, 1)';
        ctx.setLineDash([6, 6]);
        // usar timestamp es más robusto
        ctx.moveTo(x.getPixelForValue(new Date().getTime()), top);
        ctx.lineTo(x.getPixelForValue(new Date().getTime()), bottom);
        ctx.stroke();
        ctx.restore();
    },
};

const statusIcons: Plugin = {
    id: 'statusIcons',
    afterDatasetsDraw(chart) {
        const xScale = (chart as any).scales.x;
        const yScale = (chart as any).scales.y;
        if (!xScale || !yScale) return;
        const ctx = chart.ctx;
        ctx.save();
        (chart.data.datasets || []).forEach((dataset: any) => {
            (dataset.data || []).forEach((point: any) => {
                const xStart = xScale.getPixelForValue(point.x[0]);
                const xEnd = xScale.getPixelForValue(point.x[1]);
                const y = yScale.getPixelForValue(point.y); // usamos índice/label según configuración más abajo
                ctx.fillStyle = 'rgba(0, 123, 255, 0.4)';
                ctx.fillRect(xStart, y - 10, xEnd - xStart, 20);
            });
        });
        ctx.restore();
    },
};

ChartJS.register(todayLine);

// refs y heights
// const chartScaleRef = ref<any>(null);
const chartRef = ref<any>(null);
// const scaleHeight = ref(25);
const mainHeight = ref(750);

const rawData = ref<any[]>([]);
const fit_start_date = ref('');
const fit_end_date = ref('');

// cargar datos
let chartsReady = ref(false);

onMounted(async () => {
    const { data, start_date, end_date } = useAltGanttMapper();
    rawData.value = data ?? [];

    if (start_date && end_date) {
        const s = new Date(start_date);
        const e = new Date(end_date);
        s.setDate(s.getDate() - 10);
        e.setDate(e.getDate() + 10);
        fit_start_date.value = s.toISOString().split('T')[0];
        fit_end_date.value = e.toISOString().split('T')[0];
    }

    await nextTick(); // Espera a que los <Bar> estén montados
    chartsReady.value = true; // Marca que ya se pueden actualizar
});

const selectedPhase = ref(''); // id de fase seleccionada
const selectedStatus = ref('');
const selectedOverride = ref('');
const phases = computed(() => {
    // extraer fases únicas de rawData
    const allPhases = rawData.value.map((d) => ({
        id: d.phase,
        title: d.phase_title.replace(/<[^>]*>/g, ''),
    }));

    // eliminar duplicados por id
    return Array.from(new Map(allPhases.map((p) => [p.id, p])).values());
});
// filtros (los tuyos)
const selectedFase = ref('');
const selectedTecnico = ref('');
const searchText = ref('');

// filteredData como antes
const filteredData = computed(() =>
    rawData.value.filter((d) => {
        const matchPhase = !selectedPhase.value || d.phase === selectedPhase.value;
        const matchOverride =
            selectedOverride.value === ''
                ? true // "Todos"
                : d.isOverride === selectedOverride.value;
        const matchFase = !selectedFase.value || d.phase === selectedFase.value;
        const matchTecnico = !selectedTecnico.value || d.tecnico === selectedTecnico.value;
        const matchEstado = !selectedStatus.value || selectedStatus.value === 'Todos' || d.status === selectedStatus.value;
        const matchSearch =
            !searchText.value ||
            d.title
                .replace(/<[^>]*>/g, '')
                .toLowerCase()
                .includes(searchText.value.toLowerCase());

        return matchPhase && matchOverride && matchFase && matchTecnico && matchEstado && matchSearch;
    }),
);
// 1) labels del eje Y en el orden que quieras mostrar
const yLabels = computed(() => {
    // mantén orden: filtrados en orden original
    return filteredData.value.map((d) => d.y);
});

// 2) chartData: convertimos x a Date y usamos 'y' como índice (más estable)
const chartData = computed(() => {
    const data = filteredData.value.map((d, idx) => {
        const start = new Date(d.x[0]);
        const end = new Date(d.x[1]);
        return {
            x: [start, end], // array start/end (Date)
            y: idx, // usamos índice (posiciones sincronizadas con yLabels)
        };
    });
    return {
        datasets: [
            {
                label: 'Entregables',
                data,
                backgroundColor: filteredData.value.map((d) => {
                    if (d.planned == 0) return '#4caf50'; // verde
                    if (d.spi >= 95) return '#4caf50'; // verde
                    if (d.spi >= 85) return '#f3ff47'; // naranja
                    if (d.spi < 85) return '#f44336'; // rojo
                    return '#9e9e9e'; // gris por defecto
                }),
                borderColor: filteredData.value.map((d) => {
                    if (d.spi >= 95 || d.planned == 0) return '#4caf50'; // verde
                    if (d.spi >= 85) return '#f3ff47'; // naranja
                    if (d.spi < 85) return '#f44336'; // rojo
                    return '#9e9e9e'; // gris por defecto
                }),
                borderWidth: 1,
                maxBarThickness: 20,
                borderSkipped: false,
                borderRadius: 10,
                barPercentage: 0.7,
            } as any,
        ],
    };
});

// 3) options reactivos (usamos .value para fit_start/end)
const chartOptions = computed(
    () =>
        ({
            indexAxis: 'y',
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                x: {
                    type: 'time',
                    position: 'top',
                    time: { unit: 'month' },
                    min: fit_start_date.value ? new Date(fit_start_date.value) : undefined,
                    max: fit_end_date.value ? new Date(fit_end_date.value) : undefined,
                    grid: { drawTicks: false, drawBorder: false },
                    ticks: { display: true },
                },
                y: {
                    type: 'category',
                    labels: yLabels.value,
                    offset: true,
                    grid: { drawOnChartArea: false },
                    ticks: { autoSkip: false },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    usePointStyle: true,
                    callbacks: {
                        title() {
                            // Evitamos el título por defecto (la fecha del eje X)
                            return '';
                        },
                        label(context: any) {
                            const item = filteredData.value[context.dataIndex];
                            if (!item) return '';
                            const estado =
                                item.status === 1
                                    ? 'No iniciado'
                                    : item.status === 2
                                      ? 'En progreso'
                                      : item.status === 3
                                        ? 'Completado'
                                        : 'Desconocido';

                            return [
                                `📦 ${item.title.replace(/<[^>]*>/g, '')}`,
                                `🗂️ Fase: ${item.phase_title.replace(/<[^>]*>/g, '')}`,
                                `📅 Inicio: ${item.start}`,
                                `📅 Fin: ${item.end}`,
                                `📅 Dias: ${item.days}`,
                                `⚙️ Estado: ${estado}`,
                                `   %Avance: ${item.percentage}`,
                                `   %Planeado: ${item.planned}`,
                                `   SPI: ${item.spi}`,
                            ];
                        },
                    },
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    cornerRadius: 8,
                    displayColors: false,
                },
            },
        }) as any,
);

// Opciones para el chart de escala (solo eje superior)
// const scaleOptions = computed(() => ({
//     maintainAspectRatio: false,
//     responsive: true,
//     indexAxis: 'y',
//     layout: { padding: { right: 50 } },
//     scales: {
//         x: {
//             type: 'time',
//             position: 'top',
//             time: { unit: 'month', displayFormats: { month: 'MMM yyyy' } },
//             min: fit_start_date.value ? new Date(fit_start_date.value) : undefined,
//             max: fit_end_date.value ? new Date(fit_end_date.value) : undefined,
//             grid: { display: true, drawTicks: false },
//             ticks: { color: '#333', font: { size: 12 } },
//             afterFit: (axis: any) => {
//                 axis.height = 30;
//             },
//         },
//         y: { display: false },
//     },
//     plugins: { legend: { display: false }, tooltip: { enabled: false } },
// }));

// 4) cuando cambian datos o fechas, forzamos update en ambos charts para que reevalúen escalas y plugins
watch(
    [chartData, chartOptions, /*scaleOptions,*/ yLabels],
    async () => {
        if (!chartsReady.value) return; // 🚫 evita ejecutar antes de que estén montados
        await nextTick();

        // const scaleChart = chartScaleRef.value?.chart;
        const mainChart = chartRef.value?.chart;

        // if (scaleChart) {
        //     scaleChart.options.scales.x.min = scaleOptions.value.scales.x.min;
        //     scaleChart.options.scales.x.max = scaleOptions.value.scales.x.max;
        //     scaleChart.update();
        // }

        if (mainChart) {
            mainChart.options.scales.x.min = chartOptions.value.scales.x.min;
            mainChart.options.scales.x.max = chartOptions.value.scales.x.max;
            mainChart.update();
        }
    },
    { immediate: false }, // 👈 ya no immediate
);

// exportar refs y variables para template
</script>

<style scoped>
.gantt-container {
    width: 100%;
}

/* el wrapper de la escala: pequeño y sin overflow vertical */
.gantt-scale-wrapper {
    width: 100%;
    overflow: hidden; /* evita que nada "desborde" verticalmente */
}

/* wrapper del gantt principal: ocupa el resto */
.gantt-main-wrapper {
    width: 100%;
    /* opcional: overflow auto si quieres scroll vertical dentro del canvas */
}
.gantt-scroll-wrapper {
    width: 100%;
    height: 600px; /* o lo que desees como altura visible */
    overflow-y: auto; /* scroll vertical */
    overflow-x: hidden;
    border: 1px solid #ddd; /* opcional */
    border-radius: 8px;
    background: #fff;
}

.gantt-chart-container {
    min-height: 100%;
    width: 100%;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}
</style>
