<template>
    <div class="flex w-full items-start gap-2 text-sm">
        <!-- boton añadir -->
        <n-button :disabled="isActivity" :type="tipoNodoBtmAdd" @click="$emit('add-child')" class="w-6" dashed round size="Small">
            {{ (!isActivity ? '+ ' : '') + tipoNodo }}
        </n-button>
        <!-- index -->
        <span class="w-12">{{ localData.index }}</span>
        <!-- Nombre o tittle-->
        <n-input
            v-model:value="localData.title"
            @input="emitChange"
            class="w-48"
            style="width: 190px"
            placeholder="Nombre"
            round
            size="Small"
            :title="localData.title"
        />
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
        <!-- estado -->
        <n-select
            v-model:value="localData.status_id"
            @update:value="emitChange"
            :options="optionsStatuses"
            :disabled="!isActivity"
            style="width: 130px"
            :placeholder="''"
        />

        <!-- subestatus -->
        <n-select
            v-model:value="localData.substatus_id"
            @update:value="emitChange"
            :options="optionsSubstatuses"
            :disabled="!isActivity"
            style="width: 120px"
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
        <n-select
            v-model:value="localData.priority_id"
            @update:value="emitChange"
            :options="optionsPriorities"
            style="width: 100px"
            :placeholder="''"
        />
        <!-- sprintses -->
        <n-select
            v-model:value="localData.sprint_id"
            @update:value="emitChange"
            :options="optionsSprint"
            :disabled="!isActivity"
            style="width: 100px"
            :placeholder="''"
        />
        <!-- responsable -->
        <n-select v-model:value="localData.user_id" @update:value="emitChange" :placeholder="''" :options="optionsUsers" style="width: 150px" />
        <!-- Boton eliminar -->
        <n-button v-if="!isProject" @click="$emit('remove-node')" type="error" round size="tiny"> x </n-button>
    </div>
</template>

<script setup lang="ts">
import { NodeData } from '@/composables/useNodeInterfaces';
import { Priority, Sprint, Status, SubStatus, User } from '@/types/treeproject';
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
</script>
