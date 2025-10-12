<script setup>
import { mapProjectToGantt } from '@/composables/useGanttMapper';
import gantt from 'dhtmlx-gantt';
import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';
import { onBeforeUnmount, onMounted, ref } from 'vue';

onBeforeUnmount(() => {
    gantt.clearAll();
});

const ganttChart = ref(null);

onMounted(() => {
    gantt.clearAll();
    const { data, links, start_date, end_date } = mapProjectToGantt();

    gantt.plugins({
        marker: true,
        tooltip: true,
        baseline: true,
    });

    gantt.i18n.setLocale('es');
    gantt.config.grid_width = 250;
    gantt.config.row_height = 40;
    gantt.config.bar_height = 20;
    gantt.config.columns = [
        { name: 'text', label: 'Tarea', tree: true, width: 250 },
        {
            name: 'estado',
            label: '',
            width: 30,
            template: function (task) {
                switch (task.state) {
                    case 1:
                        return '<span style="color:gray;">⏻</span>';
                    case 2:
                        return '▶️';
                    case 3:
                        return '✅';
                    case 4:
                        return '⛔';
                    case 5:
                        return '⚠️';
                    default:
                        return '';
                }
            },
        },
    ];

    gantt.config.readonly = true;
    gantt.config.work_time = true;
    gantt.config.skip_off_time = true;
    gantt.config.duration_unit = 'day';
    gantt.config.correct_work_time = true;
    gantt.config.xml_date = '%Y-%m-%d';
    gantt.config.min_column_width = 50;
    gantt.config.scale_height = 90;

    gantt.config.scales = [
        { unit: 'year', step: 1, format: '%Y' },
        { unit: 'month', step: 1, format: '%F' },
    ];
    //gantt.config.fit_tasks = true;
    // gantt.config.start_date = start_date;
    // gantt.config.end_date = end_date;

    gantt.templates.task_text = () => '';
    gantt.templates.progress_text = (start, end, task) => `<span style='text-align:right;'>${Math.round(task.progress * 100)}%</span>`;
    gantt.templates.task_class = (start, end, task) => 'gantt_' + task.type;
    gantt.templates.rightside_text = function (start, end, task) {
        return '';
    };
    gantt.init(ganttChart.value);

    gantt.parse({ data, links });

    const dateToStr = gantt.date.date_to_str(gantt.config.task_date);
    gantt.addMarker({
        start_date: new Date(),
        text: 'hoy',
        css: 'today-line',
        title: dateToStr(new Date()),
    });

    gantt.templates.tooltip_text = function (start, end, task) {
        return `
      <b>${task.text}</b><br/>
      Inicio: ${gantt.templates.date_grid(start)}<br/>
      Fin: ${gantt.templates.date_grid(end)}<br/>
      Duracion: ${task.duration} dias <br/>
      % Avance: ${task.progress * 100 || 0}%<br/>
      % Planeado: ${task.planned}%<br/>
      SPI: ${task.spi}
    `;
    };
});
</script>

<template>
    <div ref="ganttChart" style="width: 100%; height: 750px"></div>
</template>

<style>
.gantt_good {
    --dhx-gantt-task-background: #21b621;
}
.gantt_alert {
    --dhx-gantt-task-background: #f3ff47; /* verde */
}
.gantt_warnning {
    --dhx-gantt-task-background: #ff0000; /* amarillo */
}
.gantt_milest {
    --dhx-gantt-task-background: #000000;
}
.gantt_task_progress {
    text-align: right;
    box-sizing: border-box;
    color: white;
    font-weight: bold;
}
.gantt_task_cell.week_end,
.gantt_task_cell.no_work_hour {
    background-color: var(--dhx-gantt-base-colors-background-alt);
}

.gantt_task_row.gantt_selected .gantt_task_cell.week_end {
    background-color: var(--dhx-gantt-base-colors-select);
}
</style>
