<template>
    <Head title="Manager" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="p-4">
                <ProjectHeader v-if="projectStore.editable"/>
                <PlaceholderPattern v-else text="Cargando proyecto..." />

                <div class="mb-4 flex space-x-4 border-b">
                    <button
                        v-for="tab in tabs"
                        :key="tab"
                        @click="activeTab = tab"
                        class="border-b-2 pb-2 font-semibold"
                        :class="{
                            'border-blue-500 text-blue-600': activeTab === tab,
                            'border-transparent text-gray-500 hover:text-blue-600': activeTab !== tab,
                        }"
                    >
                        {{ tab }}
                    </button>
                </div>
                <div>
                    <template v-if="projectStore.editable">
                        <ProjectEditor v-if="activeTab === 'Gestión'" @submit="submit" />
                        <ProjectDashboard v-if="activeTab === 'Dashboard'" />
                        <GanttView v-if="activeTab === 'Gantt'" />
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

import { useProjectStore } from '@/composables/useProjectStore';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

import ProjectDashboard from '@/components/manager.views/dashboard.vue';
import GanttView from '@/components/manager.views/gantt.vue';
import ProjectEditor from '@/components/manager.views/gestion.vue';
import ProjectHeader from '@/components/manager.views/header.vue';
import ProjectStatus from '@/components/manager.views/status.vue';
import { usePage } from '@inertiajs/vue3';
const { props } = usePage();
const projectId = props.projectId as number;
const projectStore = useProjectStore();

const tabs = ['Gestión', 'Dashboard', 'Gantt', 'Resumen'];
const activeTab = ref('Gestión');

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
