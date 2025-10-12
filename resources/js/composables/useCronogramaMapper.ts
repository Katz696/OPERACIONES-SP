import { useProjectStore } from '@/composables/useProjectStore';

function assignType(days: number, avance: number, planned: number) {
    const prdiff = planned - avance;
    switch (true) {
        case days === 0:
            return 'milest';
        case prdiff <= 5:
            return 'good';
        case prdiff <= 15:
            return 'alert';
        case prdiff > 15:
            return 'warnning';
    }
}

export function mapProjectToOrgChart() {
    const store = useProjectStore();
    const editable = store.editable;
    if (!editable) return null;

    const project = editable.project;

    const data = {
        key: `project-${project.data.id}`,
        type: 'project',
        label: `PROY - ${project.data.index} - ${project.data.title}`,
        children: project.phases.map((phase) => ({
            key: `phase-${phase.data.id}`,
            type: 'phase',
            label: `FAS - ${phase.data.index} - ${phase.data.title}`,
            children: phase.deliveries.map((delivery) => ({
                key: `delivery-${delivery.data.id}`,
                type: 'delivery',
                label: `ENT - ${delivery.data.index} - ${delivery.data.title}`,
                
            })),
        })),
    };

    return data;
}
