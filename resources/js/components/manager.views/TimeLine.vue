<script setup lang="ts">
import { filttime } from '@/composables/useFilters';
import { mapProjectToGantt } from '@/composables/useTimeLineMapper';
import gantt from 'dhtmlx-gantt';
import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
// 📊 Referencia al contenedor del gantt
const ganttChart = ref(null);
const { data: rawData, links, start_date, end_date } = mapProjectToGantt();
function formatDate(dateString?: string) {
    if (!dateString) return 'N/D';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'N/D';
    return new Intl.DateTimeFormat('es-MX', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}
// 🎯 Filtros reactivos
const filters = filttime;

// Opciones de estado
const statusOptions = [
    { label: 'No iniciado', value: 1 },
    { label: 'En progreso', value: 2 },
    { label: 'Completado', value: 3 },
];

// 🔹 Función para detectar vencidos
function isOverdue(task: any): boolean {
    if (!task.end_date) return false;
    const today = new Date();
    const end = new Date(task.end_date);
    return task.state !== 3 && end < today;
}

// 🔹 Filtros combinados
const filteredTasks = computed(() => {
    let tasks = [...rawData];
    const { status, overdueOnly,criticalPathOnly } = filters.value;

    if (status) tasks = tasks.filter((t) => t.state === status);
    if (overdueOnly) tasks = tasks.filter((t) => isOverdue(t));
    if(criticalPathOnly)tasks = tasks.filter((t)=>t.slack === 0);
    return tasks;
});

// 🔹 Función para dibujar la línea "hoy"
function drawTodayLine() {
    gantt.deleteMarker('today-marker');
    const dateToStr = gantt.date.date_to_str('%Y-%m-%d');
    gantt.addMarker({
        id: 'today-marker',
        start_date: new Date(),
        text: dateToStr(new Date()),
        css: 'today-line',
        title: dateToStr(new Date()),
    });
}

onMounted(() => {
    gantt.plugins({ marker: true, tooltip: true, baseline: true });

    gantt.i18n.setLocale('es');

    gantt.config.readonly = true;
    gantt.config.work_time = true;
    gantt.config.skip_off_time = true;
    gantt.config.duration_unit = 'day';
    gantt.config.correct_work_time = true;
    gantt.config.xml_date = '%Y-%m-%d';
    gantt.config.fit_tasks = false;

    gantt.config.columns = [
        {
            name: 'text',
            label: '&nbsp;',
            width: 200,
            tree: true,
            template: (task) => `
        <div style="
          white-space: normal;
          height: 100%;
          overflow-wrap: break-word;
          line-height: 1.5;
          text-align: center;
          align-content: center;
        ">${task.text}</div>`,
        },
    ];

    // Escala fija año/mes
    gantt.config.scales = [
        { unit: 'year', step: 1, format: '%Y' },
        { unit: 'month', step: 1, format: '%F' },
    ];

    const extended_end = new Date(end_date);
    extended_end.setMonth(extended_end.getMonth() + 1);

    gantt.config.start_date = start_date;
    gantt.config.end_date = extended_end;

    gantt.templates.task_text = () => '';
    gantt.templates.progress_text = (start, end, task) => `${Math.round(task.progress * 100)}%`;

    const date = gantt.date.date_to_str('%Y-%m-%d');
    gantt.templates.rightside_text = (start, end, task) => `
    <span style="
      background-color: #002060;
      color: white;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 12px;
    ">${formatDate(task.end_date)}</span>`;

    gantt.templates.task_class = (start, end, task) => 'gantt_' + task.type;

    gantt.init(ganttChart.value);

    // Primer render
    renderTasks(filteredTasks.value);
});

// 🔹 Redibuja el gantt cuando cambian los filtros
watch(filteredTasks, (newTasks) => {
    renderTasks(newTasks);
});

// 🔹 Render general que ajusta altura y línea "hoy"
function renderTasks(tasks: any[]) {
    gantt.clearAll();

    const maxHeight = 590;
    const totalTasks = tasks.length || 1;
    const rowHeight = Math.max(30, Math.floor(maxHeight / totalTasks));
    gantt.config.row_height = rowHeight;
    gantt.config.bar_height = rowHeight * 0.8;

    gantt.parse({ data: tasks, links });
    drawTodayLine();
    gantt.templates.tooltip_text = (start, end, task) => `
    <b>${task.text}</b><br/>
    Inicio: ${gantt.templates.date_grid(start)}<br/>
    Fin: ${gantt.templates.date_grid(end)}<br/>
    Duración: ${task.duration} días<br/>
    Avance Real: ${Math.round(task.progress * 100) || 0}%<br/>
    Avance Planeado: ${Math.round(task.planned)}%<br/>
    SPI: ${Math.round(task.spi)}%
  `;
}

onBeforeUnmount(() => {
    gantt.clearAll();
});
</script>

<template>
    <div class="flex items-center gap-3 p-3">
        <label for="">Estados:</label>
        <n-select
            v-model:value="filters.status"
            :options="statusOptions"
            claerable
            placeholder="Estado"
            clearable
            size="small"
            style="width: 160px"
        />
        <label for="">Vencido:</label>
        <n-switch v-model:value="filters.overdueOnly">Solo vencidos</n-switch>
        <label for="">Ruta critica:</label>
        <n-switch v-model:value="filters.criticalPathOnly"/>
        <n-button size="small" secondary @click="filters = { status: null, overdueOnly: false, criticalPathOnly:false }">Limpiar</n-button>
    </div>

    <div class="gantt-wrapper" ref="ganttChart" style="width: 100%; height: 700px"></div>
</template>

<style scoped>
.gantt_good {
    --dhx-gantt-task-background: #21b621;
}
.gantt_alert {
    --dhx-gantt-task-background: #f3ff47;
}
.gantt_warnning {
    --dhx-gantt-task-background: #ff0000;
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
.gantt-wrapper :deep(.gantt_tree_icon) {
    width: 0px;
}
</style>
