<template>
    <div class="w-full space-y-2">
        <!-- Botón agregar -->
        <div class="flex justify-end">
            <n-button type="success" round @click="addRow" title="Añadir Proyecto"> ➕ Agregar </n-button>
        </div>
        <!-- Encabezados -->
        <div
            class="grid grid-cols-[40px_200px_200px_200px_100px_80px_80px_120px_120px_80px_80px] gap-2 rounded bg-gray-100 p-2 text-xs font-semibold text-gray-700"
        >
            <div>#</div>
            <div>Título</div>
            <div>Cliente</div>
            <div>Responsable</div>
            <div>Estado</div>
            <div>%</div>
            <div>Días</div>
            <div>Inicio</div>
            <div>Fin</div>
            <div class="text-center">Gestionar</div>
            <div class="text-center">Eliminar</div>
        </div>
        <!-- Filas -->
        <div
            v-for="(p, index) in store.edit"
            :key="p.id ?? `new-${index}`"
            class="grid grid-cols-[40px_200px_200px_200px_100px_80px_80px_120px_120px_80px_80px] items-center gap-2 border-b bg-white p-2 text-sm transition hover:bg-gray-50"
        >
            <div>{{ index + 1 }}</div>

            <n-input v-model:value="p.title" round size="small" placeholder="Ingrese el titulo del proyecto" />

            <n-select v-model:value="p.customer_id" :options="customerOptions" filterable placeholder="Selecciona un cliente" />

            <n-select v-model:value="p.user_id" :options="usersOptions" filterable placeholder="Selecciona un usuario" />

            <span class="status-badge">{{ p.status.status }}</span>
            <span class="status-badge">{{ p.percentage }}</span>
            <span class="status-badge">{{ p.days }}</span>

            <n-date-picker
                :value="
                    p.start_date
                        ? new Date(
                              Number(p.start_date.split('-')[0]),
                              Number(p.start_date.split('-')[1]) - 1,
                              Number(p.start_date.split('-')[2]),
                          ).getTime()
                        : null
                "
                @update:value="(val: any) => (p.start_date = val ? new Date(val).toISOString().split('T')[0] : null)"
                type="date"
                :is-date-disabled="disableWeekends"
                placeholder="Selecciona una fecha"
            />
            <span class="status-badge">{{ p.end_date || 'Indefinido' }}</span>

            <n-button
                round
                @click="$inertia.visit(route('manager.show', { id: p.id }))"
                type="info"
                title="Abrir"
                :disabled="p.id != null ? false : true"
            >
                📂
            </n-button>

            <n-button round @click="removeRow(index)" type="Error" title="Eliminar">🗑️</n-button>
        </div>
        <!-- Overlay spinner -->
        <div v-if="loading" class="absolute inset-0 z-50 flex items-center justify-center bg-black/30">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-white border-t-transparent"></div>
        </div>
        <div v-if="store.hasChanges" class="mt-2 flex justify-end gap-2">
            <!-- Guardar cambios -->
            <n-button @click="saveChanges" round type="Primary"> 💾 Guardar Cambios </n-button>
            <!-- Descartar cambios -->
            <n-button @click="store.discardChanges" round type="Warning"> ❌ Descartar Cambios </n-button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useCustomersStore, useProjectStore, useUserStore } from '@/composables/useCatalogStore';
import type { Customer, User } from '@/types/catalogs';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const customerOptions = computed(() =>
    customers.value.map((c) => ({
        label: c.name, // lo que se muestra
        value: c.id, // lo que se guarda en p.customer_id
    })),
);
const usersOptions = computed(() =>
    users.value.map((c) => ({
        label: c.name, // lo que se muestra
        value: c.id, // lo que se guarda en p.customer_id
    })),
);

const disableWeekends = (ts: any) => {
    const day = new Date(ts).getDay();
    // 0 = domingo, 6 = sábado
    return day === 0 || day === 6;
};
// Store
const store = useProjectStore();
const storeUsers = useUserStore();
const storeCustomers = useCustomersStore();

// Datos de catálogos
const users = ref<User[]>([]);
const customers = ref<Customer[]>([]);

watch(
    () => storeUsers.origin,
    (val) => {
        users.value = val ? JSON.parse(JSON.stringify(val)) : [];
    },
    { immediate: true },
);

watch(
    () => storeCustomers.origin,
    (val) => {
        customers.value = val ? JSON.parse(JSON.stringify(val)) : [];
    },
    { immediate: true },
);

// Guardar cambios
const loading = ref(false);

async function saveChanges() {
    if (!store.edit?.length) return;

    loading.value = true;

    const payload = store.edit.map((p) => ({
        id: p.id || null,
        index: p.index,
        title: p.title,
        customer_id: p.customer_id,
        user_id: p.user_id,
        status_id: p.status.id,
        percentage: p.percentage,
        days: p.days,
        start_date: p.start_date,
        end_date: p.end_date,
        real_end_date: null,
        restriction_start_date: null,
        restriction_end_date: null,
    }));

    try {
        await axios.put(route('catalogs.projects.sync'), payload);
        const prors = await axios.get(route('catalogs.projects'));
        store.setData(prors.data);
    } catch (err) {
        console.error(err);
        alert('Error al guardar cambios');
    } finally {
        loading.value = false;
    }
}
// Eliminar fila
function removeRow(index: number) {
    if (store.edit !== null) {
        store.edit.splice(index, 1);
    }
}
// Agregar fila
function addRow() {
    if (store.edit === null) {
        store.edit = [];
    }
    store.edit.push({
        id: null,
        customer_id: null,
        user_id: null,
        index: store.edit.length + 1,
        title: 'Nuevo Proyecto',
        percentage: 0,
        days: 0,
        start_date: new Date().toISOString().split('T')[0],
        end_date: null,
        real_end_date: null,
        restriction_start_date: null,
        restriction_end_date: null,
        created_at: null,
        updated_at: null,
        status: {
            id: 1,
            status: 'No iniciado',
            color: '',
            created_at: null,
            updated_at: null,
        },
    });
}
</script>
