<template>
    <div>
        <n-card>
            <n-space justify="space-between" align="center">
                <n-button type="primary" @click="addAgreement" size="small">+ Nuevo acuerdo</n-button>
                <n-button type="success" size="small" @click="saveAllAgreements" :disabled="!agreementsStore.hasChanges">
                    💾 Guardar acuerdos
                </n-button>

                <n-button size="small" @click="resetFilters">Limpiar filtros</n-button>
            </n-space>
            <n-grid :cols="6" x-gap="12" y-gap="8" style="margin-top: 10px">
                <n-grid-item>
                    <div class="flex items-center space-x-2">
                        <label class="whitespace-nowrap">Tipo:</label>
                        <n-select
                            style="width: 150px"
                            v-model:value="filters.type"
                            :options="optionsType"
                            clearable
                            size="small"
                            placeholder="Tipo"
                        />
                    </div>
                </n-grid-item>

                <n-grid-item>
                    <div class="flex items-center space-x-2">
                        <label>Prioridad:</label>
                        <n-select
                            style="width: 100px"
                            v-model:value="filters.priority"
                            :options="optionsPriorities"
                            clearable
                            size="small"
                            placeholder="Prioridad"
                        />
                    </div>
                </n-grid-item>

                <n-grid-item>
                    <div class="flex items-center space-x-2">
                        <label>A/C:</label>
                        <n-select style="width: 150px" v-model:value="filters.ac" :options="optionsAC" clearable size="small" placeholder="A/C" />
                    </div>
                </n-grid-item>

                <n-grid-item>
                    <div class="flex items-center space-x-2">
                        <label>Responsable:</label>
                        <n-input style="width: 100px" v-model:value="filters.responsible" size="small" placeholder="Responsable" clearable />
                    </div>
                </n-grid-item>

                <n-grid-item>
                    <div class="flex items-center space-x-2">
                        <label>Estado:</label>
                        <n-select
                            style="width: 150px"
                            v-model:value="filters.resolution"
                            :options="optionsStatus"
                            clearable
                            size="small"
                            placeholder="Resolución"
                        />
                    </div>
                </n-grid-item>

                <n-grid-item>
                    <div class="flex items-center space-x-2">
                        <label>Vencidos:</label>
                        <n-switch v-model:value="filters.showOverdue" size="small" />
                    </div>
                </n-grid-item>
            </n-grid>
        </n-card>

        <n-card>
            <n-table>
                <thead>
                    <tr>
                        <th style="width: 135px">Tipo</th>
                        <th style="width: 120px">Fecha del acuerdo</th>
                        <th>Descripcion</th>
                        <th style="width: 100px">Prioridad</th>
                        <th style="width: 140px">A/C</th>
                        <th style="width: 140px">Responsable</th>
                        <th style="width: 120px">Fecha compromiso</th>
                        <th style="width: 100px">Días vencido</th>
                        <th style="width: 150px">Estado</th>
                        <th style="width: 100px">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="agree in filteredAgreements" :key="agree.id">
                        <td>
                            <n-select v-model:value="agree.agreement_type_id" :options="optionsType" clearable :placeholder="''" />
                        </td>
                        <td>
                            <n-date-picker
                                :value="parseDate(agree.date)"
                                @update:value="
                                    (val: any) => {
                                        agree.date = val ? formatDate(val) : null;
                                    }
                                "
                                type="date"
                                style="width: 120px"
                                placeholder="Selecciona una fecha"
                                size="Small"
                            />
                        </td>
                        <td @click="openDescriptionEdit(agree)">
                            <div class="ql-editor" v-html="agree.description"></div>
                        </td>

                        <n-modal v-model:show="showDescriptionModal" preset="dialog" title="Editar descripción">
                            <div style="min-height: 200px">
                                <QuillEditor v-model:content="editorDescription" content-type="html" theme="snow" style="height: 200px" />
                            </div>
                            <template #action>
                                <n-button @click="showDescriptionModal = false">Cancelar</n-button>
                                <n-button type="primary" @click="saveDescriptionChanges">Guardar</n-button>
                            </template>
                        </n-modal>
                        <td>
                            <n-select v-model:value="agree.priority_id" :options="optionsPriorities" style="width: 100px" :placeholder="''" />
                        </td>
                        <td><n-select v-model:value="agree.ac" :options="optionsAC" :placeholder="''" /></td>
                        <td>
                            <n-input v-model:value="agree.responsible" />
                        </td>
                        <td>
                            <n-date-picker
                                :value="parseDate(agree.agreed_date)"
                                @update:value="
                                    (val: any) => {
                                        agree.agreed_date = val ? formatDate(val) : null;
                                    }
                                "
                                type="date"
                                placeholder="Selecciona una fecha"
                                size="Small"
                            />
                        </td>

                        <td>
                            {{ calculateOverdueDays(agree.agreed_date, agree.status_id) || '-' }}
                        </td>

                        <td><n-select v-model:value="agree.status_id" :options="optionsStatus" :placeholder="''" /></td>
                        <td>
                            <n-button size="small" type="error" tertiary @click="deleteAgreement(agree)"> 🗑 Eliminar </n-button>
                        </td>
                    </tr>
                </tbody>
            </n-table>
        </n-card>
    </div>
</template>
<script setup lang="ts">
import { useAgreementsStore } from '@/composables/useAgreementsStore';
import { useProjectStore } from '@/composables/useProjectStore';
import { QuillEditor } from '@vueup/vue-quill';
import axios from 'axios';
import { useDialog, useNotification } from 'naive-ui';
import { computed, onMounted, ref, watch } from 'vue';
const projectStore = useProjectStore();
const agreementsStore = useAgreementsStore();
const agreements = ref<any[]>([]);
console.log(agreements)
const loading = ref(false);
const notification = useNotification();
const dialog = useDialog();
const today = new Date();
today.setHours(0, 0, 0, 0); // solo fecha, sin hora

const priorities = computed(() => projectStore.priorities);
const agreement_types = computed(() => projectStore.agreement_types);
const statuses = computed(() => projectStore.statuses);
const loadAgreements = async () => {
    if (!projectStore.editable) return;
    loading.value = true;
    try {
        const res = await axios.get(route('project.agreements', projectStore.editable.project.data.id));
        agreementsStore.setAgreements(res.data);
    } catch (err) {
        console.error(err);
        notification.error({
            title: 'Error',
            content: 'Error al cargar los acuerdos',
            duration: 1500,
        });
    } finally {
        loading.value = false;
    }
};
watch(
    () => agreementsStore.editable,
    (newVal) => {
        if (newVal) agreements.value = agreementsStore.editable;
    },
    { immediate: true },
);
onMounted(() => {
    if (!agreementsStore.editable.length) {
        loadAgreements();
    }
});

// opciones select
const optionsType = agreement_types.value?.map((a) => ({
    label: a.type,
    value: a.id,
}));
const optionsPriorities = priorities.value?.map((p) => ({
    label: p.priority,
    value: p.id,
}));
const optionsStatus = statuses.value?.map((s) => ({
    value: s.id,
    label: s.status,
}));
const optionsAC = [
    { label: 'Acuerdo', value: 'Acuerdo' },
    { label: 'Compromiso', value: 'Compromiso' },
];
//modal
const showDescriptionModal = ref(false);
const editorDescription = ref('');
const editingAgreement = ref<any>(null);

function openDescriptionEdit(agree: any) {
    editingAgreement.value = agree;
    editorDescription.value = agree.description || '';
    showDescriptionModal.value = true;
}

function saveDescriptionChanges() {
    if (editingAgreement.value) {
        editingAgreement.value.description = editorDescription.value;
    }
    showDescriptionModal.value = false;
}

//add
function addAgreement() {
    agreementsStore.addAgreement({
        id: null,
        project_id: projectStore.editable?.project.data.id,
        agreement_type_id: 1,
        ac: 'Acuerdo',
        date: null,
        description: '',
        priority_id: 1,
        responsible: '',
        agreed_date: null,
        status_id: 1,
    });
}
// delete
function deleteAgreement(agree: any) {
    dialog.warning({
        title: 'Confirmar eliminación',
        content: '¿Seguro que quieres eliminar este acuerdo?',
        positiveText: 'Sí, eliminar',
        negativeText: 'Cancelar',
        onPositiveClick: () => {
            agreementsStore.removeAgreement(agree.id);
        },
    });
}

const filters = ref({
    type: null,
    priority: null,
    ac: null,
    responsible: '',
    resolution: null,
    showOverdue: false, // ✅ nuevo flag
});

const filteredAgreements = computed(() => {
    if (!agreements.value) return [];

    return agreements.value.filter((a) => {
        const matchType = !filters.value.type || a.agreement_type_id === filters.value.type;
        const matchPriority = !filters.value.priority || a.priority_id === filters.value.priority;
        const matchAC = !filters.value.ac || a.ac === filters.value.ac;
        const matchResponsible = !filters.value.responsible || a.responsible?.toLowerCase().includes(filters.value.responsible.toLowerCase());
        const matchResolution = !filters.value.resolution || a.status_id === filters.value.resolution;

        // filtro vencidos solo si showOverdue es true
        let isOverdue = true;
        if (filters.value.showOverdue) {
            const days = calculateOverdueDays(a.agreed_date, a.status_id);
            isOverdue = days !== null && days > 0;
        }

        return matchType && matchPriority && matchAC && matchResponsible && matchResolution && isOverdue;
    });
});

function resetFilters() {
    filters.value = {
        type: null,
        priority: null,
        ac: null,
        responsible: '',
        resolution: null,
        showOverdue: false,
    };
}
// guardado
const saveAllAgreements = async () => {
    try {
        const payload = {
            project_id: projectStore.editable?.project.data.id,
            agreements: agreements.value ?? [], // <--- asegurarte de que no sea undefined
        };
        await axios.post(route('project.agreements.store'), payload);
        notification.success({ title: 'Guardado', content: 'Acuerdos guardados correctamente', duration: 1500 });

        // si vas a recargar:
        await loadAgreements();
    } catch (error) {
        console.error(error);
        notification.error({ title: 'Error', content: 'No se pudieron guardar los acuerdos', duration: 1500 });
    }
};

// formatDate
function parseDate(value: string | null) {
    if (!value) return null;
    const parts = value.split('-');
    if (parts.length !== 3) return null;
    const year = Number(parts[0]);
    const month = Number(parts[1]) - 1;
    const day = Number(parts[2]);
    return new Date(year, month, day).getTime();
}

function formatDate(timestamp: number) {
    const date = new Date(timestamp);
    return date.toISOString().split('T')[0];
}
// calculo dias vencido
function calculateOverdueDays(agreed_date: string | null, status_id: number) {
    if (!agreed_date) return null;
    if (status_id === 3) return 0; // si ya está resuelto, no está vencido

    const parts = agreed_date.split('-');
    if (parts.length !== 3) return null;

    const agreed = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    agreed.setHours(0, 0, 0, 0);

    const diff = today.getTime() - agreed.getTime();
    return diff > 0 ? Math.floor(diff / (1000 * 60 * 60 * 24)) : 0;
}
</script>
<style scoped></style>
