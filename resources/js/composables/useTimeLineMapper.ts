import { useProjectStore } from '@/composables/useProjectStore';
function spi(percentage: number, planned: number) {
    return planned > 0 ? Math.min((percentage / planned) * 100, 100) : 0;
}
function assingType(avance: number, planned: number) {
    const p = spi(avance, planned);
    if (planned == 0) {
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
    const edit = store.editable;
    if (!edit) return { data: [], links: [] };

    const tasks: any[] = [];
    const start_date = edit.project.data.start_date;
    const end_date = edit.project.data.end_date;
    // 🔹 Fases
    edit.project.phases.forEach((phase) => {
        tasks.push({
            id: `phase-${phase.data.id}`,
            text: `FAS - ${phase.data.index} - ${phase.data.title}`,
            start_date: phase.data.start_date,
            // end_date: phase.data.end_date,
            duration: phase.data.days,
            progress: phase.data.percentage / 100,
            planned: phase.data.percentage_planned ?? 0,
            type: assingType(phase.data.percentage, phase.data.percentage_planned),
            spi: spi(phase.data.percentage, phase.data.percentage_planned),
        });
    });

    return { data: tasks, start_date, end_date };
}
