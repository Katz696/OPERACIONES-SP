<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-2">Resumen del Proyecto</h2>
    <p>Estado actual: {{ project?.status ?? 'N/A' }}</p>
  </div>
</template>

<script setup>
defineProps({ project: Object })
</script>
