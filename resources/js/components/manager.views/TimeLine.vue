<script setup>
import { mapProjectToGantt } from '@/composables/useTimeLineMapper';
import gantt from 'dhtmlx-gantt';
import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const ganttChart = ref(null);

onBeforeUnmount(() => {
    gantt.clearAll();
});

onMounted(() => {
    gantt.clearAll();
    const { data, links, start_date, end_date } = mapProjectToGantt();

    gantt.plugins({
        marker: true,
        tooltip: true,
        baseline: true,
    });

    // 🔹 Calcula dinámicamente la altura por fila
    const maxHeight = 600; // alto máximo del contenedor
    const totalTasks = data.length || 1; // evita división entre cero
    const rowHeight = Math.max(30, Math.floor(maxHeight / totalTasks)); // mínimo 30px

    gantt.config.row_height = rowHeight;
    gantt.config.bar_height = rowHeight * 0.8; // un poco más pequeño que la fila
    gantt.config.readonly = true;
    gantt.config.work_time = true;
    gantt.config.skip_off_time = true;
    gantt.config.duration_unit = 'day';
    gantt.config.correct_work_time = true;
    gantt.config.xml_date = '%Y-%m-%d';
    gantt.config.columns = [
        {
            name: 'text',
            label: '&nbsp;',
            width: 200,
            tree: true,
            template: (task) => {
                return `<div style="
                    white-space: normal;
                    height: 100%;
                    overflow-wrap: break-word;
                    line-height: 1.5;
                    text-align: center;
                    align-content: center;
                ">${task.text}</div>`;
            },
        },
    ];

    // Escala fija año-mes
    gantt.config.scales = [
        { unit: 'year', step: 1, format: '%Y' },
        { unit: 'month', step: 1, format: '%F' },
    ];

    const extended_end = new Date(end_date);
    extended_end.setMonth(extended_end.getMonth() + 1);

    gantt.config.start_date = start_date;
    gantt.config.end_date = extended_end;
    gantt.config.fit_tasks = false;

    // Ocultar texto dentro de la barra
    gantt.templates.task_text = () => '';
    gantt.templates.progress_text = (start, end, task) => `${Math.round(task.progress * 100)}%`;

    const date = gantt.date.date_to_str('%Y-%m-%d');
    gantt.templates.rightside_text = (start, end, task) => {
        return `<span style="
        background-color: #002060; 
        color: white; 
        padding: 2px 6px; 
        border-radius: 4px;
        font-size: 12px;
    ">${date(task.end_date)}</span>`;
    };
    gantt.templates.task_class = (start, end, task) => 'gantt_' + task.type;
    gantt.init(ganttChart.value);
    gantt.parse({ data, links });

    const dateToStr = gantt.date.date_to_str('%Y-%m-%d');
    gantt.addMarker({
        start_date: new Date(),
        text: dateToStr(new Date()),
        css: 'today-line',
        title: dateToStr(new Date()),
    });
});
</script>

<template>
    <div class="gantt-wrapper" ref="ganttChart" style="width: 100%; height: 700px"></div>
</template>

<style scoped>
.gantt_good {
    --dhx-gantt-task-background: #21b621;
}
.gantt_alert {
    --dhx-gantt-task-background: #f3ff47; /* verde */
}
.gantt_warnning {
    --dhx-gantt-task-background: #ff0000; /* amarillo */
}
.gantt-wrapper :deep(.today-line) {
    background-color: rgba(146, 186, 223);
    width: 2px;
}

.gantt-wrapper :deep(.gantt_row.odd.gantt_row_task),
.gantt-wrapper :deep(.gantt_row.gantt_row_task) {
    background: rgba(217, 217, 217);
}

.gantt-wrapper :deep(.gantt_tree_content) {
    width: 100%;
    height: 100%;
}
.gantt-wrapper :deep(.gantt_scale_cell) {
    border: none;
    background-color: rgb(33, 101, 139);
    color: white;
}
.gantt-wrapper :deep(.gantt_layout) {
    border: none;
}
.gantt-wrapper :deep(.gantt_task_cell, .gantt_task_row) {
    border: none;
}

.gantt-wrapper :deep(.gantt_task_row) {
    border: none;
}
.gantt-wrapper :deep(.gantt_tree_icon) {
    width: 0px;
}
</style>
