<template>
  <div>
    <h3 class="mb-2 text-lg font-bold">Historial de Cambios</h3>

    <ul>
      <li
        v-for="log in logs"
        :key="log.id"
        class="mb-3 border-b border-gray-200 pb-2"
      >
        <p>
          <strong>{{ log.causer?.name || 'Sistema' }}</strong>
          {{ log.description }}
          <small class="text-gray-500">
            ({{ new Date(log.created_at).toLocaleString() }})
          </small>
        </p>

        <ul v-if="Object.keys(log.changes.new || {}).length" class="mt-1 ml-4 text-sm text-gray-700">
          <li
            v-for="(newValue, key) in log.changes.new"
            :key="key"
          >
            <span class="font-medium text-gray-900">
              {{ translateField(key) }}:
            </span>
            <span class="text-red-500">
              {{ log.changes.old?.[key] ?? '—' }}
            </span>
            →
            <span class="text-green-600">
              {{ newValue }}
            </span>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { useProjectStore } from '@/composables/useProjectStore';
const projectStore = useProjectStore();
import axios from 'axios';
import { onMounted, ref } from 'vue';

const logs = ref([]);

const fieldTranslations = {
  title: 'Título',
  status_id: 'Estado',
  percentage: 'Porcentaje de avance real',
  percentage_planned: 'Porcentaje planeado',
  start_date: 'Fecha de inicio',
  end_date: 'Fecha de fin',
};

function translateField(field) {
  return fieldTranslations[field] || field;
}

onMounted(async () => {
  const res = await axios.get(route('projects.history'), { params: { id: projectStore.editable.project.data.id } });
  logs.value = res.data;
});
</script>
