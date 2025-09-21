<template>
  <n-page-header
    subtitle="Gestión de Proyecto"
    class="bg-white rounded-lg shadow mb-6 p-4"
  >
    <template #title>
      <div class="flex items-center gap-2">
        📁 {{ project?.data?.title || 'Proyecto sin nombre' }}
      </div>
    </template>

    <template #extra>
      <n-space v-if="project" align="center" size="large">
        <n-statistic label="Duración" :value="project.data?.days ?? 'N/D'">
          <template #prefix>⏳</template>
        </n-statistic>

        <n-statistic label="Avance" :value="project.data?.percentage != null ? project.data.percentage + '%' : 'N/D'">
          <template #prefix>🟢</template>
        </n-statistic>

        <n-statistic label="Planeado" :value="project.data?.percentage_planned != null ? project.data.percentage_planned + '%' : 'N/D'">
          <template #prefix>🟠</template>
        </n-statistic>

        <n-statistic
          label="Atraso"
          :value="(project.data?.percentage_planned != null && project.data?.percentage != null)
            ? (project.data.percentage_planned - project.data.percentage) + '%'
            : 'N/D'"
        >
          <template #prefix>🔴</template>
        </n-statistic>

        <n-statistic label="Inicio" :value="project.data?.start_date || 'N/D'">
          <template #prefix>📅</template>
        </n-statistic>

        <n-statistic label="Fin" :value="project.data?.end_date || 'N/D'">
          <template #prefix>📅</template>
        </n-statistic>
      </n-space>
      <span v-else class="text-gray-500 italic">Cargando proyecto…</span>
    </template>
  </n-page-header>
</template>


<script setup lang="ts">
import { NPageHeader, NSpace, NStatistic } from "naive-ui"
import { useProjectStore } from '@/composables/useProjectStore';
import { computed } from 'vue';
import { ProjectData } from "@/types/treeproject";

const store = useProjectStore();

// computed que devuelve el proyecto real (o null si aún no está listo)
const project = computed<ProjectData | null>(() => {
  return store.editable?.project ?? null
});
</script>

