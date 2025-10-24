<template>
    <div class="flex w-full items-start gap-2 text-sm" :class="{ 'overdue-row': isOverdue }">
        <!-- boton añadir -->
        <n-button :disabled="isActivity" :type="tipoNodoBtmAdd" @click="$emit('add-child')" class="w-6" dashed round size="Small">
            {{ (!isActivity ? '+ ' : '') + tipoNodo }}
        </n-button>
        <!-- index -->
        <span class="w-12">{{ localData.index }}</span>
        <!-- Título (preview + modal con Quill) -->
        <n-input
            :value="titlePreview"
            placeholder="Título"
            class="w-48 cursor-pointer"
            style="width: 190px"
            round
            size="Small"
            readonly
            :title="localData.title"
            @click="openTitleEditor"
        />

        <!-- Modal para editar título con Quill -->
        <n-modal v-model:show="showTitleModal" preset="dialog" title="Editar título">
            <div style="min-height: 200px">
                <QuillEditor v-model:content="editorTitle" content-type="html" theme="snow" style="height: 200px" />
            </div>
            <template #action>
                <n-button @click="showTitleModal = false">Cancelar</n-button>
                <n-button type="primary" @click="saveTitleChanges">Guardar</n-button>
            </template>
        </n-modal>
        <!-- relaciones -->
        <n-tree-select
            multiple
            clearable
            size="Small"
            style="width: 120px"
            :options="siblings"
            v-model:value="localData.i_depend"
            @update:value="addDepends"
            :disabled="siblings.length === 0"
            :placeholder="''"
        />

        <!-- duracion en dias -->
        <n-input-number
            :show-button="false"
            type="number"
            v-model:value="localData.days"
            @input="emitChange"
            class="w-14"
            round
            :disabled="!isActivity ? true : undefined"
            size="Small"
        />
        <!-- Fecha inicio -->
        <n-date-picker
            :value="
                localData.start_date
                    ? new Date(
                          Number(localData.start_date.split('-')[0]),
                          Number(localData.start_date.split('-')[1]) - 1,
                          Number(localData.start_date.split('-')[2]),
                      ).getTime()
                    : null
            "
            @update:value="
                (val: any) => {
                    localData.start_date = val ? new Date(val).toISOString().split('T')[0] : null;
                    emitChange();
                }
            "
            type="date"
            :is-date-disabled="disableWeekends"
            style="width: 120px"
            placeholder="Selecciona una fecha"
            :disabled="!isProject"
            size="Small"
        />
        <!-- Fecha fin -->
        <n-date-picker
            :value="
                localData.end_date
                    ? new Date(
                          Number(localData.end_date.split('-')[0]),
                          Number(localData.end_date.split('-')[1]) - 1,
                          Number(localData.end_date.split('-')[2]),
                      ).getTime()
                    : null
            "
            @update:value="(val: any) => (localData.end_date = val ? new Date(val).toISOString().split('T')[0] : null)"
            type="date"
            :is-date-disabled="disableWeekends"
            style="width: 120px"
            placeholder="Selecciona una fecha"
            :disabled="true"
            size="Small"
        />
        <!-- holgura -->
         <n-input-number
            :show-button="false"
            type="number"
            v-model:value="localData.slack"
            class="w-16"
            round
            size="Small"
            placeholder="0"
            disabled
        />

        <!-- Fecha restirccion  -->
        <n-date-picker
            :value="
                localData.restriction_end_date
                    ? new Date(
                          Number(localData.restriction_end_date.split('-')[0]),
                          Number(localData.restriction_end_date.split('-')[1]) - 1,
                          Number(localData.restriction_end_date.split('-')[2]),
                      ).getTime()
                    : null
            "
            @update:value="
                (val: any) => {
                    localData.restriction_end_date = val ? new Date(val).toISOString().split('T')[0] : null;
                    emitChange();
                }
            "
            type="date"
            :is-date-disabled="disableWeekends"
            style="width: 120px"
            placeholder="----:--:--"
            size="Small"
            :class="
                (() => {
                    const r = localData.restriction_end_date ? new Date(localData.restriction_end_date) : null;
                    const e = localData.end_date ? new Date(localData.end_date) : null;
                    if (!r || !e) return ''; // si alguno es null, sin clase especial
                    return r < e ? 'warning-date' : 'success-date';
                })()
            "
        />
        <!-- desfase -->
        <n-input-number
            :show-button="false"
            type="number"
            v-model:value="daysDifference"
            class="w-16"
            round
            size="Small"
            placeholder=""
            style="color: red"
            :class="
                (() => {
                    const r = localData.restriction_end_date ? new Date(localData.restriction_end_date) : null;
                    const e = localData.end_date ? new Date(localData.end_date) : null;
                    if (!r || !e) return ''; // si alguno es null, sin clase especial
                    return r < e ? 'warning-date' : 'success-date';
                })()
            "
        />
        <!-- estado -->
        <n-select
            v-model:value="localData.status_id"
            @update:value="emitChange"
            :options="optionsStatuses"
            :disabled="true"
            style="width: 130px"
            :placeholder="''"
        />

        <!-- subestatus -->
        <n-select
            v-model:value="localData.substatus_id"
            @update:value="emitChange"
            :options="optionsSubstatuses"
            :disabled="!isDelivery"
            style="width: 200px"
            :placeholder="''"
        />
        <!-- porcentaje -->
        <n-input-number
            v-model:value="localData.percentage"
            :show-button="false"
            min="0"
            max="100"
            step="0.1"
            @update:value="emitChange"
            class="w-15"
            :disabled="!isActivity"
            size="Small"
            round
        >
            <template #suffix>%</template>
        </n-input-number>
        <!-- porcentaje planeado-->
        <n-input-number :show-button="false" type="number" v-model:value="localData.percentage_planned" class="w-14" round disabled size="Small"
            ><template #suffix>%</template>
        </n-input-number>
        <!-- prioridad -->
        <!-- <n-select
            v-model:value="localData.priority_id"
            @update:value="emitChange"
            :options="optionsPriorities"
            style="width: 100px"
            :placeholder="''"
        /> -->
        <!-- sprintses -->
        <!-- <n-select
            v-model:value="localData.sprint_id"
            @update:value="emitChange"
            :options="optionsSprint"
            :disabled="!isActivity"
            style="width: 100px"
            :placeholder="''"
        /> -->
        <!-- Comentarios -->
        <!-- Comentarios (abre modal con Quill) -->
        <n-input
            :value="commentsPreview"
            placeholder="Comentarios"
            class="w-48 cursor-pointer"
            style="width: 190px"
            round
            size="Small"
            readonly
            @click="openEditor"
        />

        <!-- Modal con Quill -->
        <n-modal v-model:show="showModal" preset="dialog" title="Editar comentarios" style="width: 800px; max-width: 90vw; height: 500px;">
            <div style="min-height: 200px">
                <QuillEditor v-model:content="editorContent" content-type="html" theme="snow" style="height: 300px;" />
            </div>
            <template #action>
                <n-button @click="showModal = false">Cancelar</n-button>
                <n-button type="primary" @click="saveChanges">Guardar</n-button>
            </template>
        </n-modal>
        <!-- responsable -->
        <n-select v-model:value="localData.user_id" @update:value="emitChange" :placeholder="''" :options="optionsUsers" style="width: 150px" />
        <!-- Boton eliminar -->
        <n-button v-if="!isProject" @click="$emit('remove-node')" type="error" round size="tiny"> x </n-button>
    </div>
</template>

<script setup lang="ts">
import { NodeData } from '@/composables/useNodeInterfaces';
import { Priority, Sprint, Status, SubStatus, User } from '@/types/treeproject';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { computed, defineEmits, defineProps, ref, watch } from 'vue';
const props = defineProps<{
    data: NodeData;
    siblings: { label: string; key: string }[];
    isProject: Boolean;
    isActivity: Boolean;
    isDelivery: Boolean;
    isPhase: Boolean;
    statuses?: Array<Status>;
    substatuses: Array<SubStatus>;
    priorities?: Array<Priority>;
    users?: Array<User>;
    sprints?: Array<Sprint>;
}>();
const optionsStatuses = props.statuses?.map((status) => ({
    label: status.status,
    value: status.id,
    disabled: false,
}));
const optionsSubstatuses = props.substatuses.map((sub) => ({
    label: sub.substatus,
    value: sub.id,
}));
const optionsPriorities = props.priorities?.map((p) => ({
    label: p.priority,
    value: p.id,
}));
const optionsUsers = props.users?.map((u) => ({
    label: u.name,
    value: u.id,
}));
const optionsSprint = props.sprints?.map((s) => ({
    label: s.sprint,
    value: s.id,
}));
const emit = defineEmits(['update:data', 'add-child', 'remove-node', 'depends:data']);

const localData = ref<NodeData>({
    ...props.data,
    percentage: Number(props.data.percentage ?? 0), // aquí forzamos 0 si no viene definido
});

watch(
    () => props.data,
    (val) => (localData.value = { ...val }),
);

function emitChange() {
    emit('update:data', localData.value);
}
function addDepends() {
    emit('depends:data', localData.value);
}
const tipoNodo = computed(() => {
    if (props.isProject) return 'PRO';
    if (props.isPhase) return 'FAS';
    if (props.isDelivery) return 'ENT';
    if (props.isActivity) return 'ACT';
    return '';
});
const tipoNodoBtmAdd = computed(() => {
    if (props.isProject) return 'Info';
    if (props.isPhase) return 'Warning';
    if (props.isDelivery) return 'Success';
    if (props.isActivity) return 'Error';
    return '';
});

const disableWeekends = (ts: any) => {
    const day = new Date(ts).getDay();
    // 0 = domingo, 6 = sábado
    return day === 0 || day === 6;
};
// --- Quill + Modal ---
const showModal = ref(false);
const editorContent = ref(localData.value.comments ?? '');

function openEditor() {
    editorContent.value = localData.value.comments ?? '';
    showModal.value = true;
}
function saveChanges() {
    localData.value.comments = editorContent.value;
    emitChange();
    showModal.value = false;
}
const commentsPreview = computed(() => {
    if (!localData.value.comments) return '';

    // 1. Quitar etiquetas HTML
    const plain = localData.value.comments.replace(/<[^>]+>/g, '');

    // 2. Reducir a 30 caracteres + "…" si es largo
    return plain.length > 30 ? plain.slice(0, 30) + '…' : plain;
});

// Modal Title
// --- Quill para Título ---
const showTitleModal = ref(false);
const editorTitle = ref(localData.value.title ?? '');

// Abrir editor de título
function openTitleEditor() {
    editorTitle.value = localData.value.title ?? '';
    showTitleModal.value = true;
}

// Guardar cambios (respetando longitud PHP)
function saveTitleChanges() {
    const maxLength = 255; // ajusta al límite de tu columna VARCHAR en PHP
    const plain = editorTitle.value.replace(/<[^>]+>/g, ''); // texto plano

    if (plain.length > maxLength) {
        //window.$message?.error?.(`El título no puede exceder ${maxLength} caracteres (actual: ${plain.length}).`);
        return;
    }

    localData.value.title = editorTitle.value;
    emitChange();
    showTitleModal.value = false;
}

// Preview corto para la tabla
const titlePreview = computed(() => {
    if (!localData.value.title) return '';
    const plain = localData.value.title.replace(/<[^>]+>/g, '');
    return plain.length > 30 ? plain.slice(0, 30) + '…' : plain;
});
const today = new Date().toISOString().split('T')[0]; // formato YYYY-MM-DD

const isOverdue = computed(() => {
    if (!localData.value.end_date || localData.value.days === 0) return false;
    return localData.value.status_id !== 3 && (localData.value.percentage ?? 0) < 100 && localData.value.end_date < today;
});
function parseLocalDate(dateStr: string): Date {
    const [year, month, day] = dateStr.split('-').map(Number);
    return new Date(year, month - 1, day); // mes empieza desde 0
}
const daysDifference = computed(() => {
    if (localData.value.restriction_end_date != null && localData.value.end_date) {
        const restric = parseLocalDate(localData.value.restriction_end_date);
        const end = parseLocalDate(localData.value.end_date);

        const min = restric >= end ? end : restric;
        const max = min === restric ? end : restric;
        let init = 0;
        while (min < max) {
            if (min.getDay() !== 0 && min.getDay() !== 6) {
                init++;
            }
            min.setDate(min.getDate() + 1);
        }
        return init;
    }

    return null;
});
</script>
<style>
.overdue-row {
    background-color: rgba(223, 226, 25, 0.3);
    /* animation: blink-red 1s infinite; */
}

@keyframes blink-red {
    0%,
    100% {
        background-color: transparent;
    }
    50% {
        background-color: rgba(223, 226, 25, 0.3);
    }
}
.warning-date .n-date-picker,
.warning-date .n-input {
    border: 1px solid #ff1500 !important;
    background-color: #f9deb2 !important;
}

.success-date .n-date-picker,
.success-date .n-input {
    border: 1px solid #10f518 !important;
    background-color: #81f78b !important;
}
.n-date-picker--disabled .n-date-picker,
.success-date .n-input {
    border: 1px solid #10f518 !important;
    background-color: #81f78b !important;
}
</style>
