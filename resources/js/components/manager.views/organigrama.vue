<script setup>
import OrganizationChart from 'primevue/organizationchart';
import { ref } from 'vue';
import { mapProjectToOrgChart } from '@/composables/useCronogramaMapper';

const data = ref(mapProjectToOrgChart());
// define selection para el v-model
const selection = ref(null);
</script>

<template>
  <div class="card">
    <!-- Usar orgData.value directamente si es ref -->
    <OrganizationChart v-model:selectionKeys="selection" :value="data" collapsible selectionMode="multiple">
    <template #person="slotProps">
        <div class="flex flex-col">
            <div class="flex flex-col items-center">
            </div>
        </div>
    </template>
    <template #default="slotProps">
        <span>{{ slotProps.node.label.replace(/<[^>]*>/g, '') }}</span>
    </template>
</OrganizationChart>
  </div>
</template>
