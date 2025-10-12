import { useProjectStore } from '@/composables/useProjectStore';
function spi(percentage: number, planned: number) {
    return planned > 0 ? Math.min((percentage / planned) * 100, 100) : 0;
}
function assingType(days: number, avance: number, planned: number) {
    const p = spi(avance, planned);
    if (days === 0) {
        return 'milest';
    } else if (planned == 0) {
        return 'good';
    } else if (p >= 95) {
        return 'good';
    } else if (p >= 85) {
        return 'alert';
    }
    return 'warnning';
}

export function mapProjectToGantt() {
    const store = useProjectStore();
    const editable = store.editable;
    if (!editable) return { data: [], links: [] };

    const tasks: any[] = [];
    const links: any[] = [];
    const start_date = editable.project.data.start_date;
    const end_date = editable.project.data.end_date;
    const duration = editable.project.data.days;
    // 🔹 Fases
    tasks.push({
        id: `project-${editable.project.data.id}`,
        text: `PROY - ${editable.project.data.index} - ${editable.project.data.title}`,
        progress: editable.project.data.percentage / 100,
        planned: editable.project.data.percentage_planned ?? 0,
        state: editable.project.data.status_id,
        type: assingType(1, editable.project.data.percentage, editable.project.data.percentage_planned),
        spi: spi(editable.project.data.percentage, editable.project.data.percentage_planned),
        open: true,
    });
    editable.project.phases.forEach((phase) => {
        tasks.push({
            id: `phase-${phase.data.id}`,
            text: `FAS - ${phase.data.index} - ${phase.data.title}`,
            start_date: phase.data.start_date,
            // end_date: phase.data.end_date,
            duration: phase.data.days,
            progress: phase.data.percentage / 100,
            planned: phase.data.percentage_planned ?? 0,
            parent: `project-${editable.project.data.id}`,
            state: phase.data.status_id,
            type: assingType(1, phase.data.percentage, phase.data.percentage_planned),
            spi: spi(phase.data.percentage, phase.data.percentage_planned),
            open: true,
        });

        // 🔹 Entregables
        phase.deliveries.forEach((delivery) => {
            tasks.push({
                id: `delivery-${delivery.data.id}`,
                text: `ENT - ${delivery.data.index} - ${delivery.data.title}`,
                start_date: delivery.data.start_date,
                // end_date: delivery.data.end_date,
                duration: delivery.data.days,
                progress: delivery.data.percentage / 100,
                planned: delivery.data.percentage_planned ?? 0,
                parent: `phase-${phase.data.id}`,
                state: delivery.data.status_id,
                type: assingType(1, delivery.data.percentage, delivery.data.percentage_planned),
                spi: spi(delivery.data.percentage, delivery.data.percentage_planned),
                open: true,
            });

            // 🔹 Actividades
            delivery.activities.forEach((activity) => {
                tasks.push({
                    id: `activity-${activity.data.id}`,
                    text: `ACT - ${activity.data.index} - ${activity.data.title}`,
                    start_date: activity.data.start_date,
                    duration: activity.data.days > 0 ? activity.data.days : 0,
                    progress: activity.data.days === 0 ? '' : activity.data.percentage / 100,
                    planned: activity.data.days === 0 ? '' : activity.data.percentage_planned,
                    parent: `delivery-${delivery.data.id}`,
                    state: activity.data.status_id,
                    type: assingType(activity.data.days, activity.data.percentage, activity.data.percentage_planned),
                    spi: spi(activity.data.percentage, activity.data.percentage_planned),
                });
                // Dependencias (usa i_depend como arreglo)
                if (activity.data.i_depend && activity.data.i_depend.length > 0) {
                    activity.data.i_depend.forEach((depId) => {
                        links.push({
                            id: `link-${depId}->${activity.data.id}`,
                            source: `activity-${depId}`,
                            target: `activity-${activity.data.id}`,
                            type: '0', // Finish-to-Start
                        });
                    });
                }
            });

            // Dependencias de entregable
            if (delivery.data.i_depend && delivery.data.i_depend.length > 0) {
                delivery.data.i_depend.forEach((depId) => {
                    links.push({
                        id: `link-${depId}->${delivery.data.id}`,
                        source: `delivery-${depId}`,
                        target: `delivery-${delivery.data.id}`,
                        type: '0',
                    });
                });
            }
        });

        // Dependencias de fase
        if (phase.data.i_depend && phase.data.i_depend.length > 0) {
            phase.data.i_depend.forEach((depId) => {
                links.push({
                    id: `link-${depId}->${phase.data.id}`,
                    source: `phase-${depId}`,
                    target: `phase-${phase.data.id}`,
                    type: '0',
                });
            });
        }
    });
    return { data: tasks, duration, links, start_date, end_date };
}
