<template>
    <div class="min-h-screen space-y-6 bg-gray-50 p-6">
        <!-- 📊 KPIs principales + Gauge -->
        <n-grid :cols="3" :x-gap="16" :y-gap="16" class="h-full">
            <!-- KPI Cards -->
            <n-gi :span="2" class="h-full">
                <n-grid :cols="3" :x-gap="16" :y-gap="16" class="h-full">
                    <n-gi v-for="(kpi, i) in kpis" :key="i" class="h-full">
                        <Counter :title="kpi.label" :counter="kpi.value" />
                    </n-gi>
                </n-grid>
            </n-gi>

            <!-- Gauge -->
            <n-gi :span="1" class="flex h-full items-center justify-center">
                <n-card
                    size="small"
                    title="Índice de Desempeño del Cronograma"
                    class="flex h-full w-full items-center justify-center rounded-xl bg-white p-6 shadow-lg"
                >
                    <GaugeProject :value="valueSPI" />
                </n-card>
            </n-gi>
        </n-grid>

        <!-- Indicador de Avance por Fase -->
        <n-card
            title="Indicador de Avance por Fase"
            size="small"
            class="flex h-full w-full items-center justify-center rounded-xl bg-white shadow-lg"
        >
            <n-grid :cols="project.phases.length" :x-gap="16" :y-gap="16">
                <n-gi v-for="(phase, index) in project.phases" :key="index">
                    <GaugePhase
                        :label="phase.data.title.replace(/<[^>]*>/g, '')"
                        :percent="phase.data.percentage ?? 0"
                        :percent_planned="phase.data.percentage_planned ?? 0"
                        :percent_progress="phase.data.percentage_progress"
                        :target="100"
                    />
                </n-gi>
            </n-grid>
        </n-card>

        <!-- Gráficos comparativos -->
        <n-grid :cols="2" :x-gap="16" :y-gap="16" class="h-full">
            <n-gi class="h-full">
                <n-card title="Avance por Fase" size="small" class="flex h-full flex-col rounded-xl bg-white p-4 shadow-lg">
                    <PhasePerformanceChart :phases="project.phases" class="flex-1" />
                </n-card>
            </n-gi>

            <n-gi class="h-full">
                <n-card
                    title="Distribución de Estados de Entregables"
                    size="small"
                    class="flex h-full flex-col items-center justify-center rounded-xl bg-white p-4 shadow-lg"
                >
                    <DeliveriesStatusChart :deliveries="allDeliveries" class="flex-1" />
                </n-card>
            </n-gi>
        </n-grid>
        <n-card size="small" class="flex h-full w-full items-center justify-center rounded-xl bg-white shadow-lg">
            <BurndownChart :labels="daysLabels" :plannedData="plannedData" :actualData="actual" />
        </n-card>
    </div>
</template>

<script setup lang="ts">
import BurndownChart from '@/components/manager.views/charts.chartjs/BurnDownChart.vue';
import Counter from '@/components/manager.views/charts.chartjs/counter-card.vue';
import DeliveriesStatusChart from '@/components/manager.views/charts.chartjs/DeliveriesStatusChart.vue';
import GaugePhase from '@/components/manager.views/charts.chartjs/GaugePhase.vue';
import GaugeProject from '@/components/manager.views/charts.chartjs/GaugeProject.vue';
import PhasePerformanceChart from '@/components/manager.views/charts.chartjs/PhasePerformanceChart.vue';
import { useProjectStore } from '@/composables/useProjectStore';
import { useThemeVars } from 'naive-ui';
import { computed } from 'vue';

const actual = [100,92,95,90,91,90,56];

const themeVars = useThemeVars();
const store = useProjectStore();
const project = store.editable.project;

const allDeliveries = computed(() => project.phases.flatMap((phase) => phase.deliveries ?? []));

const valueSPI = computed(() => {
    if (project.data.percentage_planned > 0) {
        return Math.round((Math.round(project.data.percentage) / Math.round(project.data.percentage_planned)) * 100);
    }
    return 0;
});

// KPI cards dinámicos
const kpis = computed(() => {
    const deliveries = project.phases.flatMap((phase) => phase.deliveries);

    const total = deliveries.length;

    const completados = deliveries.filter((d) => d.data.status_id === 3).length;
    const enProceso = deliveries.filter((d) => d.data.status_id === 2).length;
    const noIniciados = deliveries.filter((d) => d.data.status_id === 1).length;

    const hoy = new Date();

    // Entregables en proceso y NO vencidos
    const enProcesoNoVencidos = deliveries.filter((d) => {
        if (d.data.status_id !== 2) return false;
        const endDate = new Date(d.data.end_date);
        return endDate >= hoy; // todavía no venció
    }).length;

    // Entregables vencidos (en proceso o no completados con fecha pasada)
    const vencidos = deliveries.filter((d) => {
        const endDate = new Date(d.data.end_date);
        return (
            d.data.status_id !== 3 && // no completado
            endDate < hoy // fecha vencida
        );
    }).length;

    return [
        { label: 'Total de Entregables', value: total },
        { label: 'Entregables Completados', value: completados },
        { label: 'Entregables En Proceso', value: enProceso },
        { label: 'Entregables No Vencidos', value: enProcesoNoVencidos },
        { label: 'Entregables Vencidos', value: vencidos },
        { label: 'Entregables No Iniciados', value: noIniciados },
    ];
});
const daysLabels = computed(() => {
  const start = new Date(project.data.start_date);
  const totalDays = project.data.days ?? 7; // días hábiles del proyecto
  const labels: string[] = [];
  let currentDate = new Date(start);

  while (labels.length < totalDays) {
    const day = currentDate.getDay(); // 0 = domingo, 6 = sábado
    if (day !== 0 && day !== 6) { // solo lunes-viernes
      labels.push(currentDate.toLocaleDateString());
    }
    currentDate.setDate(currentDate.getDate() + 1);
  }

  return labels;
});


const plannedData = computed(() => {
  const totalDays = daysLabels.value.length;
  const totalWork = 100; // porcentaje total de trabajo
  return Array.from({ length: totalDays }, (_, i) =>
    Math.round(totalWork - (totalWork / (totalDays - 1)) * i)
  );
});

</script>

<style scoped>
/* Si quieres que todo tenga altura mínima consistente */
.n-card {
    min-height: 120px;
    border-radius: 40px;
}
</style>
