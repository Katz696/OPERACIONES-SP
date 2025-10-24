<template>
    <n-card>
        <!-- 🔹 Filtros -->
        <div class="filters">
            <n-select v-model:value="filters.phase" :options="phaseOptions" placeholder="Filtrar por fase" clearable style="width: 200px" />
            <n-select v-model:value="filters.status" :options="statusOptions" placeholder="Filtrar por estado" clearable style="width: 200px" />
            <div class="vencido-filter">
                <span>Solo vencidos</span>
                <n-switch v-model:value="filters.onlyOverdue" />
            </div>
            <div class="vencido-filter">
                <span>Ruta critica</span>
                <n-switch v-model:value="filters.criticalPathOnly" />
            </div>
            <div class="counter">Mostrando {{ filteredData.length }} de {{ deliveryData.length }}</div>
        </div>

        <!-- 🔹 Tabla -->
        <n-table :bordered="true" striped size="small">
            <thead>
                <tr>
                    <th style="width: 50px">EDT</th>
                    <th style="width: 250px">Título</th>
                    <th style="width: 150px">Fase</th>
                    <th style="width: 150px">Estado</th>
                    <th style="width: 150px">Fecha inicio</th>
                    <th style="width: 100px">Fecha entrega (Cronograma)</th>
                    <th style="width: 100px">Días vencido</th>
                    <th style="width: 100px">Fecha entrega (Real)</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="d in filteredData" :key="d.id">
                    <td>{{ d.edt }}</td>
                    <td>{{ d.title }}</td>
                    <td>{{ d.phase }}</td>
                    <td>{{ d.status }}</td>
                    <td>{{ d.start_date }}</td>
                    <td>{{ d.end_date }}</td>
                    <td :style="{ color: d.isOverdue ? 'red' : '' }">
                        {{ d.isOverdue ? d.daysOverdue : 0 }}
                    </td>
                    <td>
                        <n-date-picker v-model:value="d.real_end_date" size="small" @update:value="(val: any) => updateRealEndDate(d, val)" />
                    </td>
                    <td @click="openEditor(d)">
                        <div class="ql-editor" v-html="d.comments"></div>
                    </td>
                </tr>
            </tbody>
        </n-table>
        <n-modal v-model:show="showModal" preset="dialog" title="Editar comentarios" style="width: 800px; max-width: 90vw; height: 500px">
            <div style="min-height: 200px">
                <QuillEditor v-model:content="editorContent" content-type="html" theme="snow" style="height: 300px" />
            </div>
            <template #action>
                <n-button @click="showModal = false">Cancelar</n-button>
                <n-button type="primary" @click="saveChanges">Guardar</n-button>
            </template>
        </n-modal>
    </n-card>
</template>

<script setup lang="ts">
import { useProjectStore } from '@/composables/useProjectStore';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { computed, ref } from 'vue';

const store = useProjectStore();

// --- Datos base ---
const deliveryData = computed(() => {
    if (!store.editable) return [];
    const project = store.editable.project;
    const deliveries: any[] = [];
    const today = new Date();

    project.phases.forEach((phase: any) => {
        phase.deliveries.forEach((delivery: any) => {
            const end = new Date(delivery.data.end_date);
            const isOverdue = delivery.data.status_id !== 3 && today > end;
            const diffDays = isOverdue ? Math.floor((today.getTime() - end.getTime()) / (1000 * 60 * 60 * 24)) : 0;
            console.log(delivery.data);
            deliveries.push({
                id: delivery.data.id,
                edt: delivery.data.index,
                title: delivery.data.title,
                phase: phase.data.title,
                status: store.substatuses.find((s) => s.id === delivery.data.substatus_id)?.substatus,
                status_id: delivery.data.status_id,
                start_date: delivery.data.start_date,
                end_date: delivery.data.end_date,
                real_end_date: delivery.data.real_end_date ?? null,
                comments: delivery.data.comments,
                isOverdue,
                daysOverdue: diffDays,
                _phaseRef: phase,
                _deliveryRef: delivery,
                slack:delivery.data.slack
            });
        });
    });

    return deliveries;
});

// --- Actualizar fecha real ---
function updateRealEndDate(row: any, newDate: number | null) {
    if (!newDate) {
        row.real_end_date = null;
    } else {
        const dateObj = new Date(newDate);
        const formatted = dateObj.toISOString().split('T')[0];
        row.real_end_date = formatted;
    }

    // Actualizar el delivery original
    const targetDelivery = row._phaseRef.deliveries.find((d: any) => d.data.id === row.id);
    if (targetDelivery) {
        targetDelivery.data.real_end_date = row.real_end_date;
    }
}

// --- Filtros ---
const filters = ref({
    phase: null,
    status: null,
    onlyOverdue: false,
    criticalPathOnly:false
});

const phaseOptions = computed(() =>
    [...new Set(deliveryData.value.map((d) => d.phase))].map((p) => ({
        label: p,
        value: p,
    })),
);

const statusOptions = computed(() =>
    store.substatuses?.map((s) => ({
        label: s.substatus,
        value: s.substatus,
    })),
);

// --- Aplicar filtros ---
const filteredData = computed(() => {
    return deliveryData.value.filter((d) => {
        const matchPhase = !filters.value.phase || d.phase === filters.value.phase;
        const matchStatus = !filters.value.status || d.status === filters.value.status;
        const matchOverdue = !filters.value.onlyOverdue || d.isOverdue;
        const matchCritical = !filters.value.criticalPathOnly || d.slack === 0 ;
        return matchPhase && matchStatus && matchOverdue && matchCritical;
    });
});
// modal
const showModal = ref(false);
const editorContent = ref('');
const currentRow = ref<any>(null);

// --- Abrir modal ---
function openEditor(row: any) {
    currentRow.value = row;
    editorContent.value = row.comments || '';
    showModal.value = true;
}

// --- Guardar cambios ---
function saveChanges() {
    if (!currentRow.value) return;

    // Actualizar el row de deliveryData
    currentRow.value.comments = editorContent.value;

    // Actualizar store.editable
    const phase = currentRow.value._phaseRef;
    const delivery = phase.deliveries.find((d: any) => d.data.id === currentRow.value.id);
    if (delivery) delivery.data.comments = editorContent.value;

    showModal.value = false;
}
</script>

<style scoped>
.filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    align-items: center;
}
.vencido-filter {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.n-card {
    margin-bottom: 1rem;
}
.n-data-table .n-data-table-td {
    white-space: normal !important; /* Permite saltos de línea */
    word-break: break-word; /* Rompe palabras largas */
    height: auto !important; /* Ajusta automáticamente el alto */
    line-height: 1.4; /* Mejora legibilidad */
    padding-top: 8px;
    padding-bottom: 8px;
}
</style>
