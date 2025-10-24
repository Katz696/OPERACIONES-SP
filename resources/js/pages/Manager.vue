<template>
    <Head title="Manager" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="p-4">
                <ProjectHeader v-if="projectStore.editable" />
                <PlaceholderPattern v-else text="Cargando proyecto..." />

                <div class="mb-4 flex space-x-4 border-b">
                    <n-button-group size="small">
                        <n-button v-for="tab in tabs" :key="tab" round :color="activeTab === tab ? '#ABABAB' : ''" @click="activeTab = tab">
                            <template #icon>
                                <n-icon>
                                    <component :is="tabIcons[tab]" />
                                </n-icon>
                            </template>
                            {{ tab }}
                        </n-button>
                    </n-button-group>
                </div>
                <div>
                    <template v-if="projectStore.editable">
                        <ProjectEditor v-if="activeTab === 'Cronograma'" @submit="submit" />
                        <Organigrama v-if="activeTab === 'EDT'" />
                        <ProjectDashboard v-if="activeTab === 'Tablero de control'" />
                        <GanttView v-if="activeTab === 'Gantt'" />
                        <TimeLine v-if="activeTab === 'Gantt Fases'" />
                        <AltGantt v-if="activeTab === 'Gantt Entregables'" />
                        <ProjectStatus v-if="activeTab === 'Resumen'" :project="projectStore.editable" />
                        <Log v-if="activeTab === 'Log'"/>
                        <Snap v-if="activeTab === 'Lineas base'"/>
                        <Matrixdelivery v-if="activeTab === 'Matriz Entregables'"/>
                        <Agreement v-if="activeTab === 'Acuerdos'"/>
                    </template>

                    <template v-else>
                        <PlaceholderPattern text="Cargando proyecto..." />
                    </template>
                </div>
                <div v-if="loading" class="absolute inset-0 z-50 flex items-center justify-center bg-black/30">
                    <div class="h-12 w-12 animate-spin rounded-full border-4 border-white border-t-transparent"></div>
                </div>
                <div v-if="projectStore.hasChanges" class="mb-4 flex justify-end gap-4">
                    <n-button @click="submit" round type="Primary"> 💾 Guardar Cambios </n-button>
                    <!-- Descartar cambios -->
                    <n-button @click="projectStore.discardChanges" round type="Warning"> ❌ Descartar Cambios </n-button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';

import AltGantt from '@/components/manager.views/altergantt.vue';
import ProjectDashboard from '@/components/manager.views/dashboard.vue';
import GanttView from '@/components/manager.views/gantt.vue';
import ProjectEditor from '@/components/manager.views/gestion.vue';
import ProjectHeader from '@/components/manager.views/header.vue';
import Organigrama from '@/components/manager.views/organigrama.vue';
import ProjectStatus from '@/components/manager.views/status.vue';
import Log from '@/components/manager.views/logs.vue';
import Snap from '@/components/manager.views/snaps.vue';
import TimeLine from '@/components/manager.views/TimeLine.vue';
import { useProjectStore } from '@/composables/useProjectStore';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    BarChartOutline,
    CalendarOutline,
    ClipboardOutline,
    DocumentOutline,
    GitNetworkOutline,
    GridOutline,
    ReaderOutline,
    ListOutline,
    FileTrayFullOutline,
    FolderOpenOutline,
    NewspaperOutline
} from '@vicons/ionicons5';

import axios from 'axios';
import { onMounted, ref } from 'vue';
import Matrixdelivery from '@/components/manager.views/matrixdelivery.vue';
import Agreement from '@/components/manager.views/agreement.vue';
const { props } = usePage();
const projectId = props.projectId as number;
const projectStore = useProjectStore();
const tabIcons: Record<string, any> = {
    'Cronograma': DocumentOutline,
    'EDT': GitNetworkOutline,
    'Tablero de control': GridOutline,
    'Gantt': CalendarOutline,
    'Gantt Entregables': ClipboardOutline,
    'Gantt Fases': ReaderOutline,
    'Resumen': BarChartOutline,
    'Matriz Entregables': ListOutline, // icono sugerido
    'Acuerdos': FileTrayFullOutline,   // icono sugerido
    'Lineas base': FolderOpenOutline,    // icono sugerido
    'Log': NewspaperOutline,           // icono sugerido
};


const tabs = ['Cronograma', 'EDT', 'Tablero de control', 'Gantt', 'Gantt Entregables','Gantt Fases', 'Resumen','Matriz Entregables','Acuerdos','Lineas base','Log'];
const activeTab = ref('Cronograma');

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Manager', href: '/manager' }];

// Cargar proyecto
const refreshProject = async () => {
    const res = await axios.get(route('projects.show'), {
        params: { id: projectId },
    });
    projectStore.setProject(res.data);
};

// Guardar cambios
const submit = async () => {
    if (!projectStore.editable) return;
    try {
        await axios.post(route('projects.update'), {
            project: projectStore.editable.project,
        });
        await refreshProject(); // recargar estado limpio
    } catch (err) {
        console.error(err);
        alert('Error al guardar');
    }
};

const loading = ref(false);
onMounted(refreshProject);
</script>
