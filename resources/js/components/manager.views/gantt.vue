<script setup lang="ts">
import { filtgantt } from '@/composables/useFilters';
import { mapProjectToGantt } from '@/composables/useGanttMapper';
import gantt from 'dhtmlx-gantt';
import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';
import { cloneDeep } from 'lodash-es';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
const ganttChart = ref(null);
const { data: rawData, links, start_date, end_date } = mapProjectToGantt();
// 🔹 Filtros reactivos

const filters = filtgantt;
// 🔹 Opciones
const filterOptions = {
    type: [
        { label: 'Proyecto', value: 'project' },
        { label: 'Fases', value: 'phase' },
        { label: 'Entregables', value: 'delivery' },
        { label: 'Actividades', value: 'activity' },
    ],
    status: [
        { label: 'No iniciado', value: 1 },
        { label: 'En progreso', value: 2 },
        { label: 'Completado', value: 3 },
    ],
};

function resetFilters() {
    filters.value = { type: null, status: null, overdueOnly: false, criticalPathOnly:false };
}

// 🔹 Detección de tareas vencidas
function isOverdue(task: any): boolean {
    if (!task.start_date || !task.duration) return false;
    const today = new Date();
    return task.state !== 3 && task.end_date < today;
}

// 🔹 Función de filtrado (aplana si hay tipo seleccionado)
const filteredTasks = computed(() => {
    const { type, status, overdueOnly,criticalPathOnly } = filters.value;
    // 🔹 importante: copiar los datos para no alterar la jerarquía original
    let tasks = cloneDeep(rawData);

    if (type) {
        tasks = tasks.filter((t) => t.id.startsWith(type + '-'));
        tasks.forEach((t) => delete t.parent);
    }

    if (status) {
        tasks = tasks.filter((t) => t.state === status);
    }

    if (overdueOnly) {
        tasks = tasks.filter((t) => isOverdue(t));
    }
    if (criticalPathOnly){
        tasks = tasks.filter((t)=> t.slack == 0);
    }
    return tasks;
});

// 🔹 Inicialización de Gantt
onMounted(() => {
    gantt.plugins({ marker: true, tooltip: true, baseline: true });
    gantt.i18n.setLocale('es');

    gantt.config.grid_width = 250;
    gantt.config.row_height = 40;
    gantt.config.bar_height = 20;
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

    gantt.config.columns = [
        { name: 'text', label: '&nbsp;', tree: true, width: 250 },
        {
            name: 'estado',
            label: '',
            width: 30,
            template: (task) => {
                switch (task.state) {
                    case 1:
                        return '⏻';
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

    gantt.templates.task_text = () => '';
    gantt.templates.progress_text = (start, end, task) => `<span style='text-align:right;'>${Math.round(task.progress * 100)}%</span>`;
    gantt.templates.task_class = (start, end, task) => 'gantt_' + task.type;
    const date = gantt.date.date_to_str('%Y-%m-%d');
    gantt.templates.rightside_text = (start, end, task) => `
    <span style="
      background-color: #002060;
      color: white;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 12px;
    ">${date(task.end_date)}</span>`;
    gantt.init(ganttChart.value);
    gantt.parse({ data: filteredTasks.value, links });

    // Línea de hoy
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
});
function drawTodayLine() {
    gantt.deleteMarker('today-marker');
    const dateToStr = gantt.date.date_to_str(gantt.config.task_date);
    gantt.addMarker({
        start_date: new Date(),
        text: 'hoy',
        css: 'today-line',
        title: dateToStr(new Date()),
        id: 'today-marker',
    });
}

// 🔹 Cuando cambian filtros → recargar Gantt
watch(filteredTasks, (newTasks) => {
    gantt.clearAll();
    gantt.parse({ data: newTasks, links });
    drawTodayLine();
});

onBeforeUnmount(() => {
    gantt.clearAll();
});
</script>

<template>
    <n-card size="small" class="filter-bar" :bordered="false">
        <n-space align="center" justify="space-between" wrap>
            <n-space align="center" wrap>
                <label for="">Tipo:</label>
                <n-select
                    v-model:value="filters.type"
                    :options="filterOptions.type"
                    placeholder="Tipo de tarea"
                    clearable
                    size="small"
                    style="width: 160px"
                />
                <label for="">Estados:</label>
                <n-select
                    v-model:value="filters.status"
                    :options="filterOptions.status"
                    placeholder="Estado"
                    clearable
                    size="small"
                    style="width: 160px"
                />
                <label for="">Vencidos:</label>
                <n-switch v-model:value="filters.overdueOnly">Solo vencidos</n-switch>
                <label for="">Ruta critica:</label>
                <n-switch v-model:value="filters.criticalPathOnly"></n-switch>
                <n-button @click="resetFilters" size="small" secondary>Limpiar</n-button>
            </n-space>
        </n-space>
    </n-card>

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
