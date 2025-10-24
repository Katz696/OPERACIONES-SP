// composables/useFilters.ts
import { ref } from 'vue';

// Si quieres agruparlos
export const filtedt = ref({
    type: null as string | null,
    status: null as number | null,
    overdueOnly: false,
    criticalPathOnly:false
});
export const filtgantt = ref({
    type: null as string | null,
    status: null as number | null,
    overdueOnly: false,
    criticalPathOnly:false
});
export const filtalter = ref({
    phase: null as string | null,
    status: null as number | null,
    overdueOnly: false,
    criticalPathOnly:false
});
export const filttime = ref({
    status: null as number | null,
    overdueOnly: false,
    criticalPathOnly:false
});