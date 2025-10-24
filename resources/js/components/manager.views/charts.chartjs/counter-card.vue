<template>
  <n-card :class="cardClass" class="flex h-32 w-40 flex-col items-center justify-center rounded-xl p-6 shadow-lg">
    <!-- Label -->
    <div class="text-sm">{{ title }}</div>

    <!-- Número + Icono en fila -->
    <div class="mt-2 flex items-center space-x-2">
      <div class="text-3xl font-bold">{{ counter }}</div>
      <component v-if="icon" :is="icon" class="h-6 w-6" />
    </div>
  </n-card>
</template>

<script setup>
import { CalendarIcon, CheckCircleIcon, ClockIcon, ExclamationCircleIcon, XCircleIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

const props = defineProps({
    title: String,
    counter: [String, Number],
});

const cardClass = computed(() => {
    switch (props.title) {
        case 'Total de Entregables':
            return 'card-total';
        case 'Entregables En Proceso':
            return 'card-progress';
        case 'Entregables No Vencidos':
            return 'card-noOverdue';
        case 'Entregables Completados':
            return 'card-completed';
        case 'Entregables Vencidos':
            return 'card-overdue';
        case 'Entregables No Iniciados':
            return 'card-uninicied';
        default:
            return 'card-default';
    }
});

const icon = computed(() => {
    switch (props.title) {
        case 'Total de Entregables':
            return CalendarIcon;
        case 'Entregables En Proceso':
        case 'Entregables No Vencidos':
            return ClockIcon;
        case 'Entregables Completados':
            return CheckCircleIcon;
        case 'Entregables Vencidos':
            return ExclamationCircleIcon;
        case 'Entregables No Iniciados':
            return XCircleIcon;
        default:
            return null;
    }
});
</script>

<style scoped>
/* Estilos pastel existentes */
.card-total {
    background-color: #cfe2ff;
    border: 2px solid #90cdf4;
    text-align: center;
    color: #1e3a8a;
}

.card-progress {
    background-color: #fef9c3;
    border: 2px solid #fde68a;
    text-align: center;
    color: #92400e;
}

.card-noOverdue {
    background-color: #d1fae5;
    border: 2px solid #6ee7b7;
    text-align: center;
    color: #065f46;
}

.card-completed {
    background-color: #a7f3d0;
    border: 2px solid #34d399;
    text-align: center;
    color: #065f46;
}

.card-overdue {
    background-color: #fecaca;
    border: 2px solid #f87171;
    text-align: center;
    color: #991b1b;
}

.card-uninicied {
    background-color: #e5e7eb;
    border: 2px solid #d1d5db;
    text-align: center;
    color: #374151;
}
</style>
