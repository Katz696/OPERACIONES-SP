<template>
    <div class="w-full">
        <!-- 🔍 Barra de filtros -->
        <n-card size="small" class="filter-bar" :bordered="false">
            <n-space align="center" justify="space-between" wrap>
                <n-space align="center" wrap>
                    <label for="">Tipo:</label>
                    <n-select
                        v-model:value="filters.type"
                        :options="filterOptions.type"
                        placeholder="Tipo de nodo"
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
                    <label for="">Ruta Critica:</label>
                    <n-switch v-model:value="filters.criticalPathOnly" />
                    <n-button @click="resetFilters" size="small" secondary>Limpiar</n-button>
                </n-space>
            </n-space>
        </n-card>
        <n-card size="small" class="filter-bar w-full" :bordered="false">
            <n-space align="center" justify="space-between" wrap>
                <n-space align="center" wrap>
                    <div class="w-14">Tipo</div>
                    <div class="w-16">EDT</div>
                    <div class="w-45">Titulo</div>
                    <div class="w-30">Predecesor</div>
                    <div class="w-12">Días</div>
                    <div class="w-30">Fecha inicio</div>
                    <div class="w-28">Fecha fin</div>
                    <div class="w-18">holgura</div>
                    <div class="w-28">Fecha restriccón</div>
                    <div class="w-16">Desfase</div>
                    <div class="w-32">Estado</div>
                    <div class="w-50">Sub Estado</div>
                    <div class="w-15">%AR</div>
                    <div class="w-14">%AP</div>
                    <!-- <div class="w-25">Prioridad</div>
            <div class="w-25">Sprint</div> -->
                    <div class="w-47">Comentarios</div>
                    <div class="w-30">Responsable</div>
                    <div class="w-15">Eliminar</div>
                </n-space>
            </n-space>
        </n-card>
        <!-- Contenido -->
        <div class="min-w-max">
            <n-tree :data="filteredTreeData" :render-label="renderLabel" :default-expand-all="true" show-line :indent="18" />
        </div>
    </div>
</template>

<script setup lang="ts">
import TreeNodeLabel from '@/components/TreeNodeLabel.vue';
import { filtedt } from '@/composables/useFilters';
import { useProjectStore } from '@/composables/useProjectStore';
import { NTree } from 'naive-ui';
import { computed, defineEmits, h, ref, watch } from 'vue';
const emit = defineEmits(['submit']);
const store = useProjectStore();
const sprints = computed(() => store.sprints);
const statuses = computed(() => store.statuses);
const substatuses = computed(() => store.substatuses);
const priorities = computed(() => store.priorities);
const users = computed(() => store.users);

const treeData = ref<any[]>([]);
const filters = filtedt;

// --- Opciones del selector ---
const filterOptions = {
    type: [
        { label: 'Proyectos', value: 'project' },
        { label: 'Fases', value: 'phase' },
        { label: 'Entregables', value: 'delivery' },
        { label: 'Actividades', value: 'activity' },
    ],
    status: [
        { label: 'No Iniciado', value: 1 },
        { label: 'En progreso', value: 2 },
        { label: 'Completado', value: 3 },
    ],
};

function resetFilters() {
    filters.value = { type: null, status: null, overdueOnly: false, criticalPathOnly: false };
}

// --- Función para saber si un nodo está vencido ---
function isNodeOverdue(node: any): boolean {
    if (!node.data.end_date || node.data.days === 0) return false;
    const today = new Date().toISOString().split('T')[0];
    return node.data.status_id !== 3 && (node.data.percentage ?? 0) < 100 && node.data.end_date < today;
}

// --- Función recursiva que aplica los filtros combinados ---
function filterTree(nodes: any[]): any[] {
    return nodes
        .map((node) => {
            const matchType =
                !filters.value.type ||
                (filters.value.type === 'project' && node.key.startsWith('project-')) ||
                (filters.value.type === 'phase' && node.key.startsWith('phase-')) ||
                (filters.value.type === 'delivery' && node.key.startsWith('delivery-')) ||
                (filters.value.type === 'activity' && node.key.startsWith('activity-'));

            const matchStatus = !filters.value.status || node.data.status_id === filters.value.status;

            const matchOverdue = !filters.value.overdueOnly || (filters.value.overdueOnly && isNodeOverdue(node));

            // Recursividad
            const matchCriticalPath = !filters.value.criticalPathOnly || (filters.value.criticalPathOnly && node.data.slack === 0);

            // Recursividad
            const filteredChildren = node.children ? filterTree(node.children) : [];

            // Mostrar si cumple los filtros o si un hijo los cumple
            if ((matchType && matchStatus && matchOverdue && matchCriticalPath) || filteredChildren.length > 0) {
                return { ...node, children: filteredChildren };
            }
            return null;
        })
        .filter(Boolean);
}

// --- Computed que reacciona automáticamente ---
const filteredTreeData = computed(() => filterTree(treeData.value));
watch(
    () => store.editable,
    (newVal) => {
        if (newVal) treeData.value = mapProjectToTree(newVal.project);
        applyProcessToProject();
    },
    { immediate: true },
);
function mapProjectToTree(proj: any) {
    return [
        {
            key: `project-${proj.data.id}`,
            data: {
                ...proj.data,
                percentage: Number(proj.data.percentage ?? 0),
                days: Number(proj.data.days ?? 0),
            },
            children: proj.phases.map((phase: any) => ({
                key: `phase-${phase.data.id}`,
                data: {
                    ...phase.data,
                    percentage: Number(phase.data.percentage ?? 0),
                    days: Number(phase.data.days ?? 0),
                },
                children: phase.deliveries.map((delivery: any) => ({
                    key: `delivery-${delivery.data.id}`,
                    data: {
                        ...delivery.data,
                        percentage: Number(delivery.data.percentage ?? 0),
                        days: Number(delivery.data.days ?? 0),
                    },
                    children: delivery.activities.map((activity: any) => ({
                        key: `activity-${activity.data.id}`,
                        data: {
                            ...activity.data,
                            percentage: Number(activity.data.percentage ?? 0),
                            days: Number(activity.data.days ?? 0),
                        },
                    })),
                })),
            })),
        },
    ];
}

function onEdit() {
    applyProcessToProject();
    store.updateEditableProject({
        ...store.editable!,
        project: reconstructProjectFromTree(treeData.value),
    });
}
function applyProcessToProject() {
    propagateDatesTopDown(treeData.value);
    calculateSlackWithTree(treeData.value);
    sortTreeByEndDate(treeData.value);
    //globalSort();
    recalculateEdt(treeData.value);
    updateStatusAndProgress(treeData.value);
    calculatePercentPlannedBottomUp(treeData.value);
    calculatePercentCompletedBottomUp(treeData.value);
    forceRefreshTree();
    console.log(treeData.value)
}
// 🔁 Reemplaza por una nueva copia para reactividad
function forceRefreshTree() {
    treeData.value = JSON.parse(JSON.stringify(treeData.value));
}
// dias de holgura
interface TreeNode {
    key: string;
    data: any;
    children?: TreeNode[];
}
function calculateSlackWithTree(treeData: TreeNode[]) {
    if (!treeData?.length) return;
    const projectNode = treeData[0];
    const projectEnd = projectNode.data.end_date;

    // 🔹 Recorrer Fases
    projectNode.children?.forEach((phase) => {
        const successors = projectNode.children?.filter(p => p.data.i_depend?.includes(phase.data.id)) ?? [];

        // Referencia de fase: sucesor más temprano o fin del proyecto
        const phaseRefEnd = successors.length > 0 ? new Date(Math.min(...successors.map((s) => parseLocalDate(s.data.start_date).getTime()))) : parseLocalDate(projectEnd);

        phase.data.slack = Math.max(getWorkingDaysSlack(phase.data.start_date,phaseRefEnd) - phase.data.days,0);
        // 🔹 Recorrer Entregables
        phase.children?.forEach((delivery) => {
            const deliverySuccessors = phase.children?.filter((e)=> e.data.i_depend?.includes(delivery.data.id)) ?? [];

            const deliveryRefEnd =
                deliverySuccessors.length > 0
                    ? new Date(Math.min(...deliverySuccessors.map((s) => parseLocalDate(s.data.start_date).getTime())))
                    : phaseRefEnd;

            delivery.data.slack = Math.max(getWorkingDaysSlack(delivery.data.start_date,deliveryRefEnd) - delivery.data.days,0);

            // 🔹 Recorrer Actividades
            delivery.children?.forEach((activity) => {
                const activitySuccessors = delivery.children?.filter(d=>d.data.i_depend?.includes(activity.data.id)) ?? [];

                const activityRefEnd =
                    activitySuccessors.length > 0
                        ? new Date(Math.min(...activitySuccessors.map((s) => parseLocalDate(s.data.start_date).getTime())))
                        : deliveryRefEnd;
                activity.data.slack = Math.max(getWorkingDaysSlack(activity.data.start_date,activityRefEnd) - activity.data.days,0);
            });
        });
    });

    return treeData;
}
function getWorkingDaysSlack(start: string, end: Date): number {
    let count = 0;
    const current = parseLocalDate(start);
    const limit = new Date(end);
    limit.setHours(0, 0, 0, 0);

    // ⛔️ Excluye el día final (no cuenta el end_date como día libre)
    while (current < limit) {
        const day = current.getDay();
        if (day !== 0 && day !== 6) count++;
        current.setDate(current.getDate() + 1);
    }
    return count;
}
//calcular estados
function updateStatusAndProgress(nodes: any[]): void {
    nodes.forEach((node) => {
        // 1. Si tiene hijos, procesarlos primero
        if (node.children?.length) {
            updateStatusAndProgress(node.children);
        }

        // 2. Si el padre está cancelado (4) o detenido (5), propaga a hijos
        if (node.children?.length && (node.data.status_id === 4 || node.data.status_id === 5)) {
            node.children.forEach((child: any) => {
                child.data.status_id = node.data.status_id;
            });
        }

        // 3. Calcular el estado del nodo actual (sea padre o hijo)
        if (node.data.status_id === 4 || node.data.status_id === 5) {
            // Si sigue en cancelado/detenido, respetar
        } else if (node.data.percentage === 100) {
            node.data.status_id = 3; // Completado
        } else if (node.data.percentage > 0) {
            node.data.status_id = 2; // En progreso
        } else {
            node.data.status_id = 1; // No iniciado
        }

        // 4. Ajusta porcentaje de progreso
        node.data.percentage_progress = node.data.percentage;
    });
}

//calcular porcentajes
function calculatePercentCompletedBottomUp(nodes: any[]): void {
    nodes.forEach((node) => {
        if (node.children?.length) {
            let totalDays = 0;
            let weightedSum = 0;

            node.children.forEach((child: any) => {
                calculatePercentCompletedBottomUp([child]); // primero hijos
                // ⚡ ignorar hijos cancelados
                if (child.data.status_id === 5) {
                    return;
                }
                const childDays = child.data.days || 0;
                const childPercent = child.data.percentage || 0;

                weightedSum += childPercent * childDays;
                totalDays += childDays;
            });

            node.data.percentage = totalDays > 0 ? +(weightedSum / totalDays).toFixed(2) : 0;
        } else {
            // nodo hoja: porcentaje viene de DB o lo que el usuario editó
            node.data.percentage = node.data.percentage || 0;
        }
    });
}
function getTodayLocalDate(): Date {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), now.getDate()); // sin hora
}
function calculatePercentPlannedBottomUp(nodes: any[], today: Date = new Date()): void {
    today = getTodayLocalDate(); // corregir zona horaria
    nodes.forEach((node) => {
        if (node.children?.length) {
            let totalDays = 0;
            let weightedSum = 0;

            node.children.forEach((child: any) => {
                calculatePercentPlannedBottomUp([child], today);

                // ⚡ ignorar hijos cancelados
                if (child.data.status_id === 5) {
                    return;
                }

                const childDays = child.data.days || 0;
                const childPlanned = child.data.percentage_planned || 0;

                weightedSum += childPlanned * childDays;
                totalDays += childDays;
            });

            node.data.percentage_planned = totalDays > 0 ? +(weightedSum / totalDays).toFixed(2) : 0;
        } else {
            // nodo hoja: calcular días transcurridos desde start_date
            const startStr = node.data.start_date;
            const totalDays = node.data.days || 0;

            if (startStr && totalDays > 0) {
                const passed = getWorkingDaysBetween(startStr, today);
                const capped = Math.min(passed, totalDays);
                node.data.percentage_planned = +((100 * capped) / totalDays).toFixed(2);
            } else {
                node.data.percentage_planned = 0;
            }
        }
    });
}

function getWorkingDaysBetween(start: string, end: Date): number {
    let count = 0;
    const current = parseLocalDate(start);

    while (current <= end) {
        const day = current.getDay();
        // 0 = domingo, 6 = sábado
        if (day !== 0 && day !== 6) count++;
        current.setDate(current.getDate() + 1);
    }

    return count;
}

function parseLocalDate(dateStr: string): Date {
    const [year, month, day] = dateStr.split('-').map(Number);
    return new Date(year, month - 1, day); // mes empieza desde 0
}
function calculateRangeLaboralDays(days: number, start: string): Date {
    let startDate = parseLocalDate(start);
    const endDate = new Date(startDate);
    let count = 0;

    while (count < days) {
        const day = endDate.getDay();
        if (day !== 0 && day !== 6) {
            count++;
        }
        if (count < days) {
            endDate.setDate(endDate.getDate() + 1);
        }
    }

    return endDate;
}

function fixInitLaboralDay(day: string, add: number) {
    const date = parseLocalDate(day);
    date.setDate(date.getDate() + add);
    while (date.getDay() === 0 || date.getDay() === 6) {
        date.setDate(date.getDate() + 1);
    }
    return date.toISOString().split('T')[0];
}
function calculateDurationDays(start: string, end: string): number {
    const startDate = parseLocalDate(start);
    const endDate = parseLocalDate(end);
    let days = 0;
    while (startDate <= endDate) {
        if (startDate.getDay() !== 0 && startDate.getDay() !== 6) {
            days++;
        }
        startDate.setDate(startDate.getDate() + 1);
    }
    return days;
}
function propagateDatesTopDown(nodes: any[], parentStartDate: string | null = null) {
    nodes.forEach((node) => {
        const data = node.data;

        // Si tiene un padre, forzamos que tome el start_date en base al padre
        if (parentStartDate) {
            let suggestInitDate = parentStartDate;

            if (data.i_depend && Array.isArray(data.i_depend) && data.i_depend.length > 0) {
                const iDepOfThese = nodes.filter((n) => data.i_depend.includes(n.data.id));
                if (iDepOfThese.length > 0) {
                    const parentNode = iDepOfThese.reduce((nx, na) => {
                        const maxFutureDate = new Date(nx.data.end_date);
                        const actualFutureDate = new Date(na.data.end_date);
                        return actualFutureDate > maxFutureDate ? na : nx;
                    });
                    suggestInitDate = fixInitLaboralDay(parentNode.data.end_date, 1);
                }
            }

            data.start_date = fixInitLaboralDay(suggestInitDate, 0);
            if (data.start_date && !isNaN(data.days)) {
                const end = calculateRangeLaboralDays(data.days, data.start_date);
                data.end_date = end.toISOString().split('T')[0];
            }
        }

        // Si tiene hijos, propagamos hacia abajo
        if (node.children?.length) {
            propagateDatesTopDown(node.children, data.start_date);

            // --- Ajustamos fechas del padre en base a los hijos ---
            const validChildren = node.children.filter((c: any) => c.data.days > 0);

            if (validChildren.length > 0) {
                const mindate = validChildren.reduce((nx: any, na: any) => {
                    const min = new Date(nx.data.start_date);
                    const actual = new Date(na.data.start_date);
                    return min > actual ? na : nx;
                });
                const maxdate = validChildren.reduce((nx: any, na: any) => {
                    const max = new Date(nx.data.end_date);
                    const actual = new Date(na.data.end_date);
                    return max < actual ? na : nx;
                });

                data.days = calculateDurationDays(mindate.data.start_date, maxdate.data.end_date);
                data.end_date = maxdate.data.end_date;
            } else {
                // 🧩 Si no hay hijos válidos o todos tienen duración 0
                data.days = 0;
                data.end_date = data.start_date;
            }
        } else {
            // 🧩 Si no tiene hijos y su propia duración no está definida, forzamos a 0
            if (!data.days || data.days <= 0) {
                data.days = 0;
                data.end_date = data.start_date;
            }
        }
    });
}

// 🔁 Reconstruye desde el árbol
function reconstructProjectFromTree(treeNodes: any[]) {
    const root = treeNodes[0];
    return {
        data: root.data,
        phases: (root.children || []).map((phaseNode: any) => ({
            data: phaseNode.data,
            deliveries: (phaseNode.children || []).map((deliveryNode: any) => ({
                data: deliveryNode.data,
                activities: (deliveryNode.children || []).map((activityNode: any) => ({
                    data: activityNode.data,
                })),
            })),
        })),
    };
}
// ➕ Añadir hijo
function addChild(node: any) {
    node.children = node.children || [];
    const suggestedStart = node.start_date;
    if (node.key.startsWith('project-')) {
        node.children.push({
            key: `phase-`,
            data: {
                id: null,
                project_id: node.data.id,
                status_id: 1,
                substatus_id: null,
                user_id: null,
                title: 'Ingrese el titulo de la fase',
                index: 0,
                days: 1,
                percentage: 0,
                percentage_planned: 0,
                percentage_progress: 0,
                start_date: suggestedStart,
                end_date: suggestedStart,
                restriction_end_date: null,
                depends_me: null,
                i_depend: null,
            },
            children: [
                {
                    key: `delivery-`,
                    data: {
                        id: null,
                        project_id: store.editable?.project.data.id,
                        phase_id: null,
                        status_id: 1,
                        substatus_id: 1,
                        priority_id: 1,
                        user_id: null,
                        sprint_id: 1,
                        index: 0,
                        title: 'Ingrese el titulo del entregable',
                        days: 1,
                        percentage: 0,
                        percentage_planned: 0,
                        percentage_progress: 0,
                        start_date: suggestedStart,
                        end_date: suggestedStart,
                        restriction_end_date: null,
                        depends_me: null,
                        i_depend: null,
                    },
                    children: [
                        {
                            key: `activity-`,
                            data: {
                                id: null,
                                delivery_id: null,
                                status_id: 1,
                                substatus_id: null,
                                priority_id: 1,
                                user_id: null,
                                index: node.children.length,
                                title: 'Ingrese el titulo de la actividad',
                                percentage: 0,
                                percentage_planned: 0,
                                percentage_progress: 0,
                                days: 1,
                                start_date: suggestedStart,
                                end_date: suggestedStart,
                                restriction_end_date: null,
                                depends_me: null,
                                i_depend: null,
                            },
                        },
                    ],
                },
            ],
        });
    } else if (node.key.startsWith('phase-')) {
        node.children.push({
            key: `delivery-`,
            data: {
                id: null,
                project_id: store.editable?.project.data.id,
                phase_id: node.data.id,
                status_id: 1,
                substatus_id: 1,
                priority_id: 1,
                user_id: null,
                sprint_id: 1,
                index: 0,
                title: 'Ingrese el titulo del entregable',
                days: 1,
                percentage: 0,
                percentage_planned: 0,
                percentage_progress: 0,
                start_date: suggestedStart,
                end_date: suggestedStart,
                restriction_end_date: null,
                depends_me: null,
                i_depend: null,
            },
            children: [
                {
                    key: `activity-`,
                    data: {
                        id: null,
                        delivery_id: null,
                        status_id: 1,
                        substatus_id: null,
                        priority_id: 1,
                        user_id: null,
                        index: node.children.length,
                        title: 'Ingrese el titulo de la actividad',
                        percentage: 0,
                        percentage_planned: 0,
                        percentage_progress: 0,
                        days: 1,
                        start_date: suggestedStart,
                        end_date: suggestedStart,
                        restriction_end_date: null,
                        depends_me: null,
                        i_depend: null,
                    },
                },
            ],
        });
    } else if (node.key.startsWith('delivery-')) {
        node.children.push({
            key: `activity-`,
            data: {
                id: null,
                delivery_id: node.data.id,
                status_id: 1,
                substatus_id: null,
                priority_id: 1,
                user_id: null,
                index: node.children.length,
                title: 'Ingrese el titulo de la actividad',
                percentage: 0,
                percentage_planned: 0,
                percentage_progress: 0,
                days: 1,
                start_date: suggestedStart,
                end_date: suggestedStart,
                restriction_end_date: null,
                depends_me: null,
                i_depend: null,
            },
        });
    }
    onEdit();
}
// ❌ Eliminar nodo
function removeNode(node: any) {
    function recursiveRemove(nodes: any[], key: string): boolean {
        const index = nodes.findIndex((n) => n.key === key);
        if (index !== -1) {
            nodes.splice(index, 1);
            return true;
        }

        for (const n of nodes) {
            if (n.children && recursiveRemove(n.children, key)) {
                // Si el hijo fue borrado, verificamos si ya no quedan hijos
                if (n.children.length === 0 && !n.key.startsWith('project-')) {
                    // Borrar el padre también si no es un proyecto
                    const parentIndex = nodes.findIndex((p) => p.key === n.key);
                    if (parentIndex !== -1) {
                        nodes.splice(parentIndex, 1);
                    }
                }
                return true;
            }
        }
        return false;
    }

    recursiveRemove(treeData.value, node.key);
    onEdit();
}

function recalculateEdt(nodes: any[], prefix = '') {
    let lastedt = '';
    nodes.forEach((node, i) => {
        const currentEdt = prefix ? `${prefix}.${i + 1}` : `${i + 1}`;
        node.data.index = currentEdt;

        if (node.children?.length) {
            recalculateEdt(node.children, currentEdt);
        }
        lastedt = currentEdt;
    });
}
//buscar hermanos disponibles:
function collectAllDepends(node: any, siblings: any[]): number[] {
    const visited = new Set<number>();
    const stack: number[] = [];

    // Paso 1: buscar quienes dependen de mí directamente
    siblings.forEach((sib) => {
        if (sib.data.i_depend?.includes(node.data.id)) {
            stack.push(sib.data.id);
        }
    });

    // Paso 2: recorrer en cascada todos los que dependan de esos
    while (stack.length) {
        const depId = stack.pop()!;
        if (!visited.has(depId)) {
            visited.add(depId);

            const depNode = siblings.find((sib) => sib.data.id === depId);
            if (depNode) {
                siblings.forEach((sib) => {
                    if (sib.data.i_depend?.includes(depNode.data.id)) {
                        stack.push(sib.data.id);
                    }
                });
            }
        }
    }

    return Array.from(visited);
}

function getSiblingOptions(node: any) {
    let siblingsContainer = findLevelTree(treeData.value, node);
    if (!siblingsContainer) return [];
    const siblings = siblingsContainer.children;

    // obtener toda la cola de dependencias
    const allDepends = collectAllDepends(node, siblings);

    return siblings.map((sib: any) => ({
        label: sib.data.index,
        key: sib.data.id,
        disabled: allDepends.includes(sib.data.id) || sib.key === node.key,
    }));
}

function findLevelTree(level: any[], node: any): any {
    for (const element of level) {
        if (element.children?.some((child: any) => child.key === node.key)) {
            return element;
        }
        if (element.children) {
            const foundLevel = findLevelTree(element.children, node);
            if (foundLevel) return foundLevel;
        }
    }
}
//ordenamiento por fecha de termino
function sortTreeByEndDate(nodes: any[]): any[] {
    if (!Array.isArray(nodes)) return nodes;

    return nodes
        .map((node) => {
            // Ordenamos recursivamente los hijos si existen
            if (node.children?.length) {
                node.children = sortTreeByEndDate(node.children);
            }
            return node;
        })
        .sort((a, b) => {
            const endA = new Date(a.data.end_date).getTime();
            const endB = new Date(b.data.end_date).getTime();
            return endA - endB;
        });
}

//ordenamiento dependencias
function orderBasedInDepends(nodes: any[], node: any): any[] {
    if (node.data.i_depend === null || (Array.isArray(node.data.i_depend) && node.data.i_depend.length === 0)) return nodes;

    const iDepOfThese = nodes.filter((n) => node.data.i_depend.includes(n.data.id));

    if (iDepOfThese.length === 0) return nodes;

    const moreFutureNode = iDepOfThese.reduce((nx, na) => {
        const maxFutureDate = new Date(nx.data.end_date);
        const actualFutureDate = new Date(na.data.end_date);
        return actualFutureDate > maxFutureDate ? na : nx;
    });

    const myIndex = nodes.indexOf(node);
    const fatherIndex = nodes.indexOf(moreFutureNode);
    if (myIndex === -1 || fatherIndex === -1) return nodes;

    nodes.splice(myIndex, 1);

    const adjustedFatherIndex = myIndex < fatherIndex ? fatherIndex - 1 : fatherIndex;

    const newIndex = myIndex < fatherIndex ? adjustedFatherIndex + 1 : adjustedFatherIndex + 1;
    nodes.splice(newIndex, 0, node);

    if (Array.isArray(node.data.depends_me)) {
        for (const childId of node.data.depends_me) {
            const childNode = nodes.find((n: any) => n.data.id === childId);
            if (childNode) {
                nodes = orderBasedInDepends(nodes, childNode);
            }
        }
    }

    return nodes;
}

//propagar dependencias hacia otros nodos
function propagatesDepends(node: any, newData: any) {
    const level = findLevelTree(treeData.value, node);

    const currentId = node.data.id;

    const prevDepends: number[] = node.data.i_depend ?? [];
    const nextDepends: number[] = newData.i_depend ?? [];

    const del = prevDepends.filter((d: number) => !nextDepends.includes(d));
    const add = nextDepends.filter((d: number) => !prevDepends.includes(d));

    for (const needDel of del) {
        const upNode = level.children.find((child: any) => child.data.id === needDel);
        if (upNode) {
            upNode.data.depends_me = upNode.data.depends_me ?? [];
            upNode.data.depends_me = upNode.data.depends_me.filter((i: number) => i !== currentId);
        }
    }

    for (const needAdd of add) {
        const upNode = level.children.find((child: any) => child.data.id === needAdd);
        if (upNode) {
            upNode.data.depends_me = upNode.data.depends_me ?? [];
            if (!upNode.data.depends_me.includes(currentId)) {
                upNode.data.depends_me.push(currentId);
            }
        }
    }
    node.data.i_depend = [...nextDepends];
    level.children = orderBasedInDepends(level.children, node);
}

// 🎨 Componente de renderizacion del arbol
const renderLabel = ({ option }: any) => {
    return h(TreeNodeLabel, {
        data: option.data,
        siblings: getSiblingOptions(option),
        isProject: option.key?.startsWith('project-'),
        isActivity: option.key?.startsWith('activity-'),
        isDelivery: option.key?.startsWith('delivery-'),
        isPhase: option.key?.startsWith('phase-'),
        statuses: statuses.value,
        substatuses: substatuses.value,
        priorities: priorities.value,
        sprints: sprints.value,
        users: users.value,
        'onUpdate:data': (newData: any) => {
            const original = findOriginalNode(option); // PASAR option, NO option.data
            if (original) {
                Object.assign(original.data, newData);
                onEdit();
            }
        },
        'onAdd-child': () => {
            const original = findOriginalNode(option);
            if (original) addChild(original);
            emit('submit');
        },
        'onRemove-node': () => {
            const original = findOriginalNode(option);
            if (original) removeNode(original);
        },
        'onDepends:data': (newData: any) => {
            const original = findOriginalNode(option);
            if (original) {
                propagatesDepends(original, newData);
                onEdit();
            }
        },
    });
};
function findOriginalNode(filternode: any, nodes: any[] = treeData.value): any | undefined {
    for (const node of nodes) {
        if (node.key === filternode.key) {
            return node;
        }
        if (node.children?.length) {
            const found = findOriginalNode(filternode, node.children);
            if (found) return found;
        }
    }
    return undefined;
}
</script>
<style scoped>
.n-space {
    width: max-content;
}
</style>
