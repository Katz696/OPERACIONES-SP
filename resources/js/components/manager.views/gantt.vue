<template>
    <g-gantt-chart v-bind="chartConfig">
        <g-gantt-row v-for="row in rows" :key="row.label" :label="row.label" :bars="row.bars" />
    </g-gantt-chart>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useProjectStore } from '@/composables/useProjectStore'

const store = useProjectStore()
const rows = ref<any[]>([])

const chartConfig = {
    chartStart: ref('2025-01-01'),
    chartEnd: ref('2025-12-31'),
    precision: 'day',
    barStart: "start",
    barEnd: "end",
    dateFormat: "YYYY-MM-DD HH:mm",
    rowHeight: 30,
    grid: true,
    pushOnOverlap: true,
    colorScheme: 'default'
}
watch(
    () => store.editable,
    (newVal) => {
        if (newVal?.project) {
            const { projectRows, minDate, maxDate } = mapTreeToRows(newVal.project)
            rows.value = projectRows
            console.log(rows)
            chartConfig.chartStart.value = minDate
            chartConfig.chartEnd.value = maxDate
        }
    },
    { immediate: true }
)


function mapTreeToRows(proj: any) {
    const projectRows: any[] = []
    const startDate = new Date(proj.data.start_date)
    const endDate = new Date(proj.data.end_date)
    startDate.setDate(startDate.getDate() - 2)
    endDate.setDate(endDate.getDate() + 2)
    let minDate = startDate.toISOString().split('T')[0] + ' 00:00'
    let maxDate = endDate.toISOString().split('T')[0] + ' 23:59'

    // Proyecto
    projectRows.push(
        {
            label: "Proyecto",
            bars: [
                {
                    start: proj.data.start_date + ' 07:00',
                    end: proj.data.end_date + ' 19:00',
                    ganttBarConfig: {
                        id: proj.data.index,
                        label: proj.data.title,
                        progress: proj.data.percentage,
                        style: {
                            background: "#D46363",
                            borderRadius: '20px'
                        }
                    }
                }
            ]
        })

    // Fases
    proj.phases.forEach((phase: any) => {
        projectRows.push(
            {
                label: "Fase",
                bars: [
                    {
                        start: phase.data.start_date + ' 07:00',
                        end: phase.data.end_date + ' 19:00',
                        ganttBarConfig: {
                            id: phase.data.index,
                            label: phase.data.title,
                            progress: phase.data.percentage,
                            style: {
                                background: '#D48E63',
                                borderRadius: '20px'
                            },
                            connections: phase.data.depends_on ? [
                                {
                                    targetId: phase.data.depends_on,
                                    type: "squared",
                                    animated: true,
                                    relation: "SF", // Finish to Start
                                    label: "Prerequisite",
                                    color: "#D48E63"
                                }
                            ] : [{
                                targetId: proj.data.index,
                                type: "squared",
                                animated: true,
                                relation: "SS", // Finish to Start
                                label: "Prerequisite",
                                color: "#D46363"
                            }]
                        }
                    }
                ]
            })

        // Entregas
        phase.deliveries.forEach((delivery: any) => {
            projectRows.push(
                {
                    label: "Entrega",
                    bars: [
                        {
                            start: delivery.data.start_date + ' 07:00',
                            end: delivery.data.end_date + ' 19:00',
                            ganttBarConfig: {
                                id: delivery.data.index,
                                label: delivery.data.title,
                                progress: delivery.data.percentage,
                                style: {
                                    background: '#D4A963',
                                    borderRadius: '20px'
                                },
                                connections: delivery.data.depends_on ? [
                                    {
                                        targetId: delivery.data.depends_on,
                                        type: "squared",
                                        animated: true,
                                        relation: "SF", // Finish to Start
                                        label: "Prerequisite",
                                        color: "#D4A963"
                                    }
                                ] : [
                                    {
                                        targetId: phase.data.index,
                                        type: "squared",
                                        animated: true,
                                        relation: "SS", // Finish to Start
                                        label: "Prerequisite",
                                        color: "#D48E63"
                                    }]
                            }
                        }
                    ]
                })

            // Actividades
            delivery.activities.forEach((act: any) => {
                projectRows.push(
                    {
                        label: "Actividad",
                        bars: [
                            {
                                start: act.data.start_date + ' 07:00',
                                end: act.data.end_date + ' 19:00',
                                ganttBarConfig: {
                                    id: act.data.index,
                                    label: act.data.title,
                                    progress: act.data.percentage,
                                    style: {
                                        background: '#D4D463',
                                        borderRadius: '20px'
                                    },
                                    connections: act.data.depends_on ? [
                                        {
                                            targetId: act.data.depends_on,
                                            type: "squared",
                                            animated: true,
                                            relation: "SF", // Finish to Start
                                            label: "Prerequisite",
                                            color: "#D4D463"
                                        }
                                    ] : [
                                        {
                                            targetId: delivery.data.index,
                                            type: "squared",
                                            animated: true,
                                            relation: "SS", // Finish to Start
                                            label: "Prerequisite",
                                            color: "#D4A963"
                                        }
                                    ]
                                }
                            }
                        ]
                    })
            })
        })
    })

    return { projectRows, minDate, maxDate }
}
</script>

<style>

.g-gantt-chart {
    display: block;
}
</style>
