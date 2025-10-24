<template>
    <div class="snap-container space-y-4">
        <div class="flex items-center justify-between">
            <n-button @click="openModal" size="small" type="primary">💾 Crear Snapshot</n-button>
        </div>

        <div v-if="loading" class="py-4 text-center">Cargando snapshots...</div>

        <div v-else>
            <div v-if="snaps.length === 0" class="text-gray-500">No hay snapshots guardados.</div>

            <n-table single-column :single-line="false">
                <thead>
                    <tr>
                        <th>Fecha de creación</th>
                        <th>Comentario</th>
                        <th>Creado por</th>
                        <th>Avance real</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="snap in snaps" :key="snap.id">
                        <td>{{ new Date(snap.created_at).toLocaleString() }}</td>
                        <td>{{ snap.label }}</td>
                        <td>{{ snap.user?.name || 'Sistema' }}</td>
                        <td>{{ snap.percentage }}</td>
                        <td>{{ snap.start_date }}</td>
                        <td>{{ snap.end_date }}</td>
                        <td>
                            <n-button size="tiny" @click="restoreSnap(snap)">Preview</n-button>
                            <n-button size="tiny" type="error" @click="deleteSnap(snap)">Eliminar</n-button>
                        </td>
                    </tr>
                </tbody>
            </n-table>
        </div>

        <!-- Modal para el label -->
        <n-modal v-model:show="showModal" preset="dialog" title="Guardar Snapshot">
            <div class="space-y-3">
                <p>Ingresa un comentario o etiqueta para este snapshot:</p>
                <n-input v-model:value="snapLabel" placeholder="Ejemplo: Avance semanal 3" />
                <div class="flex justify-end space-x-2 mt-4">
                    <n-button @click="showModal = false" size="small">Cancelar</n-button>
                    <n-button type="primary" size="small" @click="saveSnap">Guardar</n-button>
                </div>
            </div>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { useProjectStore } from '@/composables/useProjectStore';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { NButton, NModal, NInput, useNotification } from 'naive-ui';

const projectStore = useProjectStore();
const snaps = ref<any[]>([]);
const loading = ref(false);
const showModal = ref(false);
const snapLabel = ref('');
const notification = useNotification(); // 👈 Inicializa el sistema de notificaciones

// ----------------- Funciones -----------------
const loadSnaps = async () => {
    if (!projectStore.editable) return;
    loading.value = true;
    try {
        const res = await axios.get(route('project.snaps', projectStore.editable.project.data.id));
        snaps.value = res.data;
        console.log(snaps)
    } catch (err) {
        console.error(err);
        notification.error({
            title: 'Error',
            content: 'Error cargando snapshots',
        });
    } finally {
        loading.value = false;
    }
};

const openModal = () => {
    snapLabel.value = '';
    showModal.value = true;
};

const saveSnap = async () => {
    if (!snapLabel.value.trim()) {
        notification.warning({
            title: 'Campo vacío',
            content: 'Por favor ingresa un comentario o etiqueta.',
            duration:3000,
        });
        return;
    }

    if (!projectStore.editable) return;
    showModal.value = false;

    try {
        const res = await axios.post(route('project.snaps.store'), {
            project_id: projectStore.editable.project.data.id,
            label: snapLabel.value,
            percentage: projectStore.editable.project.data.percentage,
            percentage_planned: projectStore.editable.project.data.percentage_planned,
            percentage_progress: projectStore.editable.project.data.percentage_progress,
            start_date: projectStore.editable.project.data.start_date,
            end_date: projectStore.editable.project.data.end_date,
            data: projectStore.editable,
        });

        loadSnaps();
        notification.success({
            title: 'Snapshot guardado',
            content: 'El snapshot se ha guardado con éxito.',
            duration:3000
        });
    } catch (err) {
        console.error(err);
        notification.error({
            title: 'Error',
            content: 'Error guardando snapshot.',
        });
    }
};

const restoreSnap = (snap: any) => {
    if (!snap?.data) return;
    projectStore.editable = JSON.parse(JSON.stringify(snap.data));
    notification.info({
        title: 'Snapshot restaurado',
        content: 'El snapshot ha sido cargado correctamente.',
    });
};

const deleteSnap = async (snap: any) => {
    if (!confirm('¿Eliminar este snapshot?')) return;
    try {
        await axios.delete(route('snaps.destroy', snap.id));
        snaps.value = snaps.value.filter((s) => s.id !== snap.id);
        notification.success({
            title: 'Eliminado',
            content: 'Snapshot eliminado correctamente.',
        });
    } catch (err) {
        console.error(err);
        notification.error({
            title: 'Error',
            content: 'Error eliminando snapshot.',
        });
    }
};

// ----------------- Mounted -----------------
onMounted(loadSnaps);
</script>

<style scoped>
.snap-container {
    max-width: 100%;
}
</style>
