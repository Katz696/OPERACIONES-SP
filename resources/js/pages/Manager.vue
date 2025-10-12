<template>
    <Head title="Manager" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="p-4">
                <ProjectHeader v-if="projectStore.editable" />
                <PlaceholderPattern v-else text="Cargando proyecto..." />

                <div class="mb-4 flex space-x-4 border-b">
                    <n-button-group size="small">
                        <n-button round @click="activeTab = 'EDT'" :color="activeTab == 'EDT' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            Cronograma
                        </n-button>
                        <n-button round @click="activeTab = 'EDT Organigrama'" :color="activeTab == 'EDT Organigrama' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            EDT
                        </n-button>
                        <n-button round @click="activeTab = 'Tablero de control'" :color="activeTab == 'Tablero de control' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            Tablero de control
                        </n-button>
                        <n-button round @click="activeTab = 'Gantt Extendido'" :color="activeTab == 'Gantt Extendido' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            Gantt X Tarea
                        </n-button>
                        <n-button round @click="activeTab = 'Gantt Entregables'" :color="activeTab == 'Gantt Entregables' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            Gantt X Entregables
                        </n-button>
                        <n-button round @click="activeTab = 'Gantt Resumido'" :color="activeTab == 'Gantt Resumido' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            Gantt X Fase
                        </n-button>
                        
                        <n-button round @click="activeTab = 'Resumen'" :color="activeTab == 'Resumen' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            Resumen
                        </n-button>
                    </n-button-group>
                </div>
                <div>
                    <template v-if="projectStore.editable">
                        <ProjectEditor v-if="activeTab === 'EDT'" @submit="submit" />
                        <Organigrama v-if="activeTab === 'EDT Organigrama'"/>
                        <ProjectDashboard v-if="activeTab === 'Tablero de control'" />
                        <GanttView v-if="activeTab === 'Gantt Extendido'" />
                        <TimeLine v-if="activeTab === 'Gantt Resumido'" />
                        <AltGantt v-if="activeTab === 'Gantt Entregables'"/>
                        <ProjectStatus v-if="activeTab === 'Resumen'" :project="projectStore.editable" />
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

import ProjectDashboard from '@/components/manager.views/dashboard.vue';
import GanttView from '@/components/manager.views/gantt.vue';
import ProjectEditor from '@/components/manager.views/gestion.vue';
import ProjectHeader from '@/components/manager.views/header.vue';
import ProjectStatus from '@/components/manager.views/status.vue';
import TimeLine from '@/components/manager.views/TimeLine.vue';
import Organigrama from '@/components/manager.views/organigrama.vue'
import AltGantt from '@/components/manager.views/altergantt.vue'
import { useProjectStore } from '@/composables/useProjectStore';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { DocumentOutline } from '@vicons/ionicons5';
import axios from 'axios';
import { onMounted, ref } from 'vue';
const { props } = usePage();
const projectId = props.projectId as number;
const projectStore = useProjectStore();

const tabs = ['EDT', 'EDT Organigrama', 'Tablero de control', 'Gantt Extendido', 'Gantt Resumido','Gantt Entregables', 'Resumen'];
const activeTab = ref('EDT');

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
