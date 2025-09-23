<template>
    <div class="flex w-full items-start gap-2 pl-4 text-sm font-semibold">
        <div class="w-14">Tipo</div>
        <div class="w-16">EDT</div>
        <div class="w-48">Titulo</div>
        <div class="w-30">Predecesor</div>
        <div class="w-14">Días</div>
        <div class="w-30">Fecha inicio</div>
        <div class="w-30">Fecha fin</div>
        <div class="w-33">Estado</div>
        <div class="w-30">Sub Estado</div>
        <div class="w-15">%</div>
        <div class="w-14">(%)</div>
        <div class="w-25">Prioridad</div>
        <div class="w-25">Sprint</div>
        <div class="w-30">Responsable</div>
        <div class="w-15">Eliminar</div>
    </div>
    <div>
        <n-tree :data="treeData" :render-label="renderLabel" :default-expand-all="true" show-line :indent="18" />
    </div>
</template>
<script setup lang="ts">
import TreeNodeLabel from '@/components/TreeNodeLabel.vue';
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
    globalSort();
    recalculateEdt(treeData.value);
    updateStatusAndProgress(treeData.value);
    calculatePercentPlannedBottomUp(treeData.value);
    calculatePercentCompletedBottomUp(treeData.value);
    forceRefreshTree();
} 
// 🔁 Reemplaza por una nueva copia para reactividad
function forceRefreshTree() {
    treeData.value = JSON.parse(JSON.stringify(treeData.value));
}
//calcular estados
function updateStatusAndProgress(nodes: any[]): void {
    nodes.forEach((node) => {
        // Primero actualizar hijos recursivamente
        if (node.children?.length) {
            updateStatusAndProgress(node.children);

            const allChildrenCompletedOrCancelled = node.children.every((child: any) => [4, 5].includes(child.data.status_id));

            // Si todos los hijos están completados o cancelados
            if (allChildrenCompletedOrCancelled) {
                node.data.status_id = 4; // Completado
                node.data.percentage = 100;
                node.data.percentage_progress = 100;
            }

            // Si el nodo está marcado como Completado pero su porcentaje es < 100, bajarlo a En Progreso
            else if (node.data.status_id === 4 && node.data.percentage < 100) {
                node.data.status_id = 2; // En progreso
            }

            // Si está en "No iniciado" y tiene avance, pasarlo a "En progreso"
            else if (node.data.status_id === 1 && node.data.percentage > 0) {
                node.data.status_id = 2;
            }
        } else {
            const actPercent = node.data.percentage;
            // Nodo hoja (actividad)
            if (node.data.percentage === 100) {
                node.data.status_id = 4;
            }
            if ([4, 5].includes(node.data.status_id)) {
                node.data.percentage = 100;
                node.data.percentage_progress = 100;
            }

            if (node.data.status_id === 1 && node.data.percentage > 0) {
                node.data.status_id = 2;
            }

            // Si está en Completado pero ya no tiene 100%
            if (node.data.status_id === 4 && node.data.percentage < 100) {
                node.data.status_id = 2;
            }
        }
    });
}
//calcular porcentajes
function calculatePercentCompletedBottomUp(nodes: any[]): void {
    nodes.forEach((node) => {
        if (node.children?.length) {
            let totalDays = 0;
            let weightedSum = 0;

            node.children.forEach((child: { data: { days: number; percentage: number } }) => {
                calculatePercentCompletedBottomUp([child]); // primero hijos
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

            node.children.forEach((child: { data: { days: number; percentage_planned: number } }) => {
                calculatePercentPlannedBottomUp([child], today);
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
                const start = new Date(startStr);
                const passed = getWorkingDaysBetween(start, today);
                const capped = Math.min(passed, totalDays);
                node.data.percentage_planned = +((100 * capped) / totalDays).toFixed(2);
            } else {
                node.data.percentage_planned = 0;
            }
        }
    });
}
function getWorkingDaysBetween(start: Date, end: Date): number {
    let count = 0;
    const current = new Date(start);

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
            // Si depende de alguien explícitamente (i_depend), tomamos ese como prioridad
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
                data.end_date = end.toISOString().split("T")[0];
            }
        }

        // Si tiene hijos, propagamos hacia abajo
        if (node.children?.length) {
            propagateDatesTopDown(node.children, data.start_date);

            // Después de propagar, ajustamos fechas del padre
            const mindate = node.children.reduce((nx: any, na: any) => {
                const min = new Date(nx.data.start_date);
                const actual = new Date(na.data.start_date);
                return min > actual ? na : nx;
            });
            const maxdate = node.children.reduce((nx: any, na: any) => {
                const max = new Date(nx.data.end_date);
                const actual = new Date(na.data.end_date);
                return max < actual ? na : nx;
            });

            data.days = calculateDurationDays(mindate.data.start_date, maxdate.data.end_date);
            data.end_date = maxdate.data.end_date;
        }
    });
}

// function propagateDatesTopDown(nodes: any[], parentStartDate: string | null = null) {
//     nodes.forEach((node) => {
//         const data = node.data;
//         if (node.children?.length) {
//             propagateDatesTopDown(node.children, data.start_date);
//             const mindate = node.children.reduce((nx: any, na: any) => {
//                 const min = new Date(nx.data.start_date);
//                 const actual = new Date(na.data.start_date);
//                 return min > actual ? na : nx;
//             });
//             const maxdate = node.children.reduce((nx: any, na: any) => {
//                 const max = new Date(nx.data.end_date);
//                 const actual = new Date(na.data.end_date);
//                 return max < actual ? na : nx;
//             });
//             data.days = calculateDurationDays(mindate.data.start_date,maxdate.data.end_date);
//             data.end_date = maxdate.data.end_date;
//         } else {
//             let parentNode = null;
//             if (data.i_depend !== null && Array.isArray(data.i_depend) && data.i_depend.length > 0) {
//                 const iDepOfThese = nodes.filter((n) => data.i_depend.includes(n.data.id));

//                 parentNode = iDepOfThese.reduce((nx, na) => {
//                     const maxFutureDate = new Date(nx.data.end_date);
//                     const actualFutureDate = new Date(na.data.end_date);
//                     return actualFutureDate > maxFutureDate ? na : nx;
//                 });
//             }

//             let suggestInitDate = parentStartDate;

//             if (data.start_date && !parentStartDate) {
//                 suggestInitDate = fixInitLaboralDay(data.start_date, 0);
//             }

//             if (parentNode && parentNode.data.end_date) {
//                 suggestInitDate = fixInitLaboralDay(parentNode.data.end_date, 1);
//             }

//             data.start_date = suggestInitDate;

//             if (data.start_date && !isNaN(data.days)) {
//                 const end = calculateRangeLaboralDays(data.days, data.start_date);
//                 data.end_date = end.toISOString().split('T')[0];
//             }
//         }
//     });
// }
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
                substatus_id: 1,
                user_id: null,
                title: 'Ingrese el titulo de la fase',
                index: 0,
                days: 0,
                percentage: 0,
                percentage_planned: 0,
                percentage_progress: 0,
                start_date: suggestedStart,
                end_date: suggestedStart,
                depends_me: null,
                i_depend: null,
            },
            children: [],
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
                days: 0,
                percentage: 0,
                percentage_planned: 0,
                percentage_progress: 0,
                start_date: suggestedStart,
                end_date: suggestedStart,
                depends_me: null,
                i_depend: null,
            },
            children: [],
        });
    } else if (node.key.startsWith('delivery-')) {
        node.children.push({
            key: `activity-`,
            data: {
                id: null,
                delivery_id: node.data.id,
                status_id: 1,
                substatus_id: 1,
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
            if (n.children && recursiveRemove(n.children, key)) return true;
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
function getSiblingOptions(node: any) {
    let siblings = findLevelTree(treeData.value, node);
    if (!siblings) return [];
    siblings = siblings.children;
    const meDepends: number[] = node.data.depends_me ?? [];
    return siblings.map((sib: any) => ({
        label: sib.data.index,
        key: sib.data.id,
        disabled: meDepends.includes(sib.data.id) || sib.key === node.key,
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
//ordenamiento global por dependencias
function globalSort() {
    function sort(tree: any) {
        for (const leaf of tree) {
            if (leaf.children) {
                sort(leaf.children);
            } else {
                tree.children = orderBasedInDepends(tree, leaf);
            }
        }
    }
    sort(treeData.value);
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
            Object.assign(option.data, newData);
            onEdit();
        },
        'onAdd-child': () => {
            addChild(option);
            emit('submit');
        },
        'onRemove-node': () => removeNode(option),
        'onDepends:data': (newData: any) => {
            propagatesDepends(option, newData);
            onEdit();
        },
    });
};
</script>
