<template>
    <div class="space-y-6 p-6">
        <!-- 📊 KPIs principales -->
        <n-grid :cols="4" :x-gap="16" :y-gap="16">
            <n-gi><n-card size="small">Avance Global</n-card></n-gi>
            <n-gi><n-card size="small">Duración (días)</n-card></n-gi>
            <n-gi><n-card size="small">Entregables</n-card></n-gi>
            <n-gi><n-card size="small">Actividades</n-card></n-gi>
        </n-grid>
        <n-card title="Indicador de Avance por Fase" size="small">
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
        <!-- 📊 Avance por Fase (Planeado vs Real) -->
        <n-card title="Avance por Fase (Planeado vs Real)" size="small">
            <div class="-mx-2 flex flex-wrap">
                <div v-for="(phase, i) in project.phases" :key="i" class="mb-4 flex w-full flex-col items-center px-2 sm:w-1/2 md:w-1/3 lg:w-1/4">
                    <n-progress
                        type="multiple-circle"
                        :percentage="[Math.round(Number(phase.data.percentage ?? 0)), Math.round(Number(phase.data.percentage_planned ?? 0))]"
                        :color="['#10b981', '#3b82f6']"
                        :stroke-width="12"
                        :indicator-placement="'inside'"
                        status="success"
                    />

                    <span class="mt-2 text-center text-sm">
                        {{ phase.data.title.replace(/<[^>]*>/g, '') }}
                    </span>
                </div>
            </div>
        </n-card>

        <!-- 📈 Gráficos comparativos (2 columnas) -->
        <n-grid :cols="2" :x-gap="16" :y-gap="16">
            <n-gi>
                <n-card title="Avance por Fase" size="small">
                    <PhasePerformanceChart :phases="project.phases" />
                </n-card>
            </n-gi>
            <n-gi>
                <n-card title="Distribución de Estados de Entregables" size="small">
                    <DeliveriesStatusChart :deliveries="allDeliveries" />
                </n-card>
            </n-gi>
        </n-grid>
        <n-grid :cols="2" :x-gap="16" :y-gap="16">
            <n-gi>
                <n-card v-for="(phase, i) in project.phases" :key="i" :title="phase.data.title.replace(/<[^>]*>/g, '')" size="small" class="mb-4">
                    <div class="flex flex-col gap-2">
                        <!-- Real -->
                        <n-progress
                            type="line"
                            indicator-placement="inside"
                            :color="themeVars.errorColor"
                            :rail-color="changeColor(themeVars.errorColor, { alpha: 0.2 })"
                            :percentage="Number(phase.data.percentage ?? 0)"
                            :indicator-text-color="themeVars.errorColor"
                        />

                        <!-- Planeado -->
                        <n-progress
                            type="line"
                            indicator-placement="inside"
                            :color="themeVars.warningColor"
                            :rail-color="changeColor(themeVars.warningColor, { alpha: 0.2 })"
                            :percentage="Number(phase.data.percentage_planned ?? 0)"
                            :indicator-text-color="themeVars.warningColor"
                        />
                    </div>
                </n-card>
            </n-gi>
            <n-gi>
                <n-card title="Distribución de Estados de Entregables" size="small">
                    <DeliveriesStatusChart :deliveries="allDeliveries" />
                </n-card>
            </n-gi>
        </n-grid>
        <!-- 📉 Tendencia temporal -->
        <!-- <n-card title="Tendencia de Cumplimiento" size="small" class="flex h-[300px] items-center justify-center">
            <n-grid :cols="1" :x-gap="16" :y-gap="16">
                <n-gi>
                    <TrendChart
                        :phases="
                            project.phases.map((p) => ({
                                title: p.data.title,
                                percentage: p.data.percentage,
                                percentage_planned: p.data.percentage_planned,
                            }))
                        "
                    />
                </n-gi>
            </n-grid>
        </n-card> -->

        <!-- 🧱 Gantt Chart -->
        <!-- <n-card title="Gantt de Entregables" size="small" class="flex h-[400px] items-center justify-center">
            <span class="text-gray-400">[Gantt Chart]</span>
        </n-card> -->

        <!-- ⏰ Hitos próximos -->
        <!-- <n-card title="Próximos Hitos / Tareas Críticas" size="small" class="h-[250px] overflow-y-auto">
            <ul class="text-gray-600">
                <li>[Hito 1]</li>
                <li>[Hito 2]</li>
                <li>[Hito 3]</li>
            </ul>
        </n-card> -->
    </div>
</template>
<script setup lang="ts">
import DeliveriesStatusChart from '@/components/manager.views/charts.chartjs/DeliveriesStatusChart.vue';
import GaugePhase from '@/components/manager.views/charts.chartjs/GaugePhase.vue';
import PhasePerformanceChart from '@/components/manager.views/charts.chartjs/PhasePerformanceChart.vue';
import TrendChart from '@/components/manager.views/charts.chartjs/TrendChart.vue';
import { useProjectStore } from '@/composables/useProjectStore';
import { computed } from 'vue';
import { useThemeVars } from 'naive-ui'
import { changeColor } from 'seemly'

const themeVars = useThemeVars()
const store = useProjectStore();
const project = store.editable.project;
const allDeliveries = computed(() => project.phases.flatMap((phase) => phase.deliveries ?? []));
</script>

<style scoped>
.p-6 {
    padding: 1.5rem;
}
.space-y-6 > * + * {
    margin-top: 1.5rem;
}
</style>
