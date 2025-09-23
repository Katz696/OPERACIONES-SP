<template>
    <g-gantt-chart v-bind="chartConfig">
        <g-gantt-row
            v-for="row in rows"
            :key="row.label + '-' + row.bars[0].ganttBarConfig.id"
            :label="row.label"
            :bars="row.bars"
        />
    </g-gantt-chart>
</template>

<script setup lang="ts">
import { useProjectStore } from '@/composables/useProjectStore';
import { computed, ref, watch } from 'vue';
import { ProjectData } from "@/types/treeproject";

const store = useProjectStore();

const project = computed<ProjectData | null>(() => {
  return store.editable?.project ?? null
});

const rows = ref<any[]>([]);

// Config base del chart
const chartConfig = {
    chartStart: ref('2025-01-01 00:00'),
    chartEnd: ref('2025-12-31 23:59'),
    precision: 'day',
    barStart: 'start',
    barEnd: 'end',
    dateFormat: 'YYYY-MM-DD HH:mm',
    rowHeight: 30,
    grid: true,
    pushOnOverlap: true,
    colorScheme: 'default',
};

function prefixedId(type: 'project' | 'phase' | 'delivery' | 'activity', id: number | string) {
    return `${type}-${id}`;
}

function mapTreeToRows(proj: any) {
    const projectRows: any[] = [];
    const startDate = new Date(proj.data.start_date);
    const endDate = new Date(proj.data.end_date);
    startDate.setDate(startDate.getDate() - 2);
    endDate.setDate(endDate.getDate() + 2);
    let minDate = startDate.toISOString().split('T')[0] + ' 00:00';
    let maxDate = endDate.toISOString().split('T')[0] + ' 23:59';

    // Proyecto
    projectRows.push({
        label: 'Proyecto',
        bars: [
            {
                start: proj.data.start_date + ' 07:00',
                end: proj.data.end_date + ' 19:00',
                ganttBarConfig: {
                    id: prefixedId('project', proj.data.id),
                    label: proj.data.title,
                    progress: proj.data.percentage,
                    style: {
                        background: '#D46363',
                        borderRadius: '20px',
                    },
                },
            },
        ],
    });

    // Fases
    proj.phases.forEach((phase: any) => {
        projectRows.push({
            label: `${phase.data.index}`,
            bars: [
                {
                    start: phase.data.start_date + ' 07:00',
                    end: phase.data.end_date + ' 19:00',
                    ganttBarConfig: {
                        id: prefixedId('phase', phase.data.id),
                        label: phase.data.title,
                        progress: phase.data.percentage,
                        style: {
                            background: '#D48E63',
                            borderRadius: '20px',
                        },
                        connections: phase.data.depends_me
                            ? phase.data.depends_me.map((r: number) => ({
                                  targetId: prefixedId('phase', r),
                                  type: 'squared',
                                  animated: true,
                                  relation: 'FS',
                                  label: 'Prerequisite',
                                  color: '#D48E63',
                              }))
                            : [],
                    },
                },
            ],
        });

        // Entregas
        phase.deliveries.forEach((delivery: any) => {
            projectRows.push({
                label: `${delivery.data.index}`,
                bars: [
                    {
                        start: delivery.data.start_date + ' 07:00',
                        end: delivery.data.end_date + ' 19:00',
                        ganttBarConfig: {
                            id: prefixedId('delivery', delivery.data.id),
                            label: delivery.data.title,
                            progress: delivery.data.percentage,
                            style: {
                                background: '#D4A963',
                                borderRadius: '20px',
                            },
                            connections: delivery.data.depends_me
                                ? delivery.data.depends_me.map((r:number) => (
                                      {
                                          targetId: prefixedId('delivery', r),
                                          type: 'squared',
                                          animated: true,
                                          relation: 'FS',
                                          label: 'Prerequisite',
                                          color: '#D4A963',
                                      }))
                                : [],
                        },
                    },
                ],
            });

            // Actividades
            // delivery.activities.forEach((act: any) => {
            //     projectRows.push({
            //         label: `Actividad ${act.data.index}`,
            //         bars: [
            //             {
            //                 start: act.data.start_date + ' 07:00',
            //                 end: act.data.end_date + ' 19:00',
            //                 ganttBarConfig: {
            //                     id: prefixedId('activity', act.data.id),
            //                     label: act.data.title,
            //                     progress: act.data.percentage,
            //                     style: {
            //                         background: '#D4D463',
            //                         borderRadius: '20px',
            //                     },
            //                     connections: act.data.depends_on
            //                         ? [
            //                               {
            //                                   targetId: prefixedId('activity', act.data.depends_on),
            //                                   type: 'squared',
            //                                   animated: true,
            //                                   relation: 'FS',
            //                                   label: 'Prerequisite',
            //                                   color: '#D4D463',
            //                               },
            //                           ]
            //                         : []
            //                 },
            //             },
            //         ],
            //     });
            // });
        });
    });

    return { projectRows, minDate, maxDate };
}

// 🔄 Watch para actualizar rows y chart cuando el proyecto cambia
watch(project, (newProj) => {
    if (newProj) {
        const { projectRows, minDate, maxDate } = mapTreeToRows(newProj);
        rows.value = projectRows;
        chartConfig.chartStart.value = minDate;
        chartConfig.chartEnd.value = maxDate;
    }
}, { immediate: true });
</script>

<style>
.g-gantt-chart {
    display: block;
}
</style>
