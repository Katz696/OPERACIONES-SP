<template>
    <n-page-header subtitle="Gestión de Proyecto" class="mb-6 rounded-lg bg-white p-4 shadow">
        <template #title>
            <div class="flex items-center gap-2">📁 {{ project?.data?.title.replace(/<[^>]*>/g, '') || 'Proyecto sin nombre' }}</div>
        </template>

        <template #extra>
            <div v-if="project" class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-center text-sm">
                    <thead class="bordr border-gray-300 bg-gray-100 text-gray-700">
                        <tr>
                            <th class="center border border-gray-300 px-3 py-2 text-center">Avance Real</th>
                            <th class="border border-gray-300 px-3 py-2 text-center">Avance Planeado</th>
                            <th class="border border-gray-300 px-3 py-2 text-center">Inicio</th>
                            <th class="border border-gray-300 px-3 py-2 text-center">Fin</th>
                            <th class="border border-gray-300 px-3 py-2 text-center">Duración (días)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                {{ project.data?.percentage != null ? Math.round(project.data.percentage) + '%' : 'N/D' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                {{ project.data?.percentage_planned != null ? Math.round(project.data.percentage_planned) + '%' : 'N/D' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                {{ formatDate(project.data?.start_date) }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                {{ formatDate(project.data?.end_date) }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">{{ project.data?.days ?? 'N/D' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <span v-else class="text-gray-500 italic">Cargando proyecto…</span>
        </template>
    </n-page-header>
</template>

<script setup lang="ts">
import { useProjectStore } from '@/composables/useProjectStore';
import { ProjectData } from '@/types/treeproject';
import { NPageHeader } from 'naive-ui';
import { computed } from 'vue';

const store = useProjectStore();

// computed que devuelve el proyecto real (o null si aún no está listo)
const project = computed<ProjectData | null>(() => {
    return store.editable?.project ?? null;
});
function formatDate(dateString?: string) {
    if (!dateString) return 'N/D';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'N/D';
    return new Intl.DateTimeFormat('es-MX', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}
</script>
