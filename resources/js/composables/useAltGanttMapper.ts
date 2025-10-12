import { useProjectStore } from '@/composables/useProjectStore';

export interface GanttItem {
    id: number;
    phase: number;
    phase_title: string;
    title: string;
    status: number;
    days: number;
    start: string;
    end: string;
    percentage: number;
    planned: number;
    isOverride: boolean;
    x: string[];
    y: string;
}

export interface GanttMapperResult {
    data: GanttItem[];
    start_date: string;
    end_date: string;
}

export function useAltGanttMapper(): GanttMapperResult | null {
    const store = useProjectStore();
    const editable = store.editable;
    if (!editable) return null;

    const project = editable.project;
    const start_date = project.data.start_date;
    const end_date = project.data.end_date;
    const today = new Date();
    function spi(percentage: number, planned: number) {
        return planned > 0 ? Math.min((percentage / planned) * 100, 100) : 0;
    }
    const data: GanttItem[] =
        project.phases?.flatMap(
            (p) =>
                p.deliveries?.map((d) => ({
                    id: d.data.id,
                    phase: p.data.id,
                    phase_title: p.data.title,
                    title: d.data.title,
                    status: d.data.status_id,
                    days: d.data.days,
                    start: d.data.start_date,
                    end: d.data.end_date,
                    percentage: d.data.percentage,
                    planned: d.data.percentage_planned,
                    isOverride: d.data.percentage < 100 && new Date(d.data.end_date) < today ? true : false,
                    spi: spi(d.data.percentage, d.data.percentage_planned),
                    x: [
                        d.data.start_date,
                        d.data.days <= 1
                            ? (() => {
                                  const end = new Date(d.data.start_date);
                                  end.setDate(end.getDate() + 1);
                                  return end.toISOString().split('T')[0];
                              })()
                            : d.data.end_date,
                    ],
                    y: `ENT - ${d.data.index} - ${d.data.title.replace(/<[^>]*>/g, '').substring(0,10)}...`,
                })) ?? [],
        ) ?? [];

    return { data, start_date, end_date };
}
