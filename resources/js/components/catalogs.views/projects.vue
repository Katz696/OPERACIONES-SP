<template>
    <div class="w-full space-y-2">
        <!-- Controles superiores -->
        <!-- Contenedor de filtros -->
        <div class="flex flex-wrap items-end gap-4">
            <!-- Filtro Cliente -->
            <div class="flex flex-col">
                <label for="filter-customer" class="mb-1 text-xs font-semibold text-gray-600"> Filtrar por cliente </label>
                <n-select
                    id="filter-customer"
                    v-model:value="filterCustomer"
                    :options="customerOptions"
                    filterable
                    clearable
                    placeholder="Selecciona un cliente"
                    size="small"
                    class="w-52"
                />
            </div>

            <!-- Filtro Estado -->
            <div class="gfap-4 flex flex-col">
                <label for="filter-status" class="mb-1 text-xs font-semibold text-gray-600"> Filtrar por estado </label>
                <n-select
                    id="filter-status"
                    v-model:value="filterStatus"
                    :options="statusOptions"
                    clearable
                    placeholder="Selecciona un estado"
                    size="small"
                    class="w-40"
                />
            </div>

            <!-- Botón agregar -->
            <div class="flex flex-grow justify-end">
                <n-button type="success" round @click="addRow" title="Añadir Proyecto"> ➕ Agregar </n-button>
            </div>
        </div>

        <!-- Encabezados -->
        <div
            class="grid grid-cols-[40px_200px_200px_200px_100px_200px_200px_120px_120px_80px_80px] gap-2 rounded bg-gray-100 p-2 text-xs font-semibold text-gray-700"
        >
            <div>#</div>
            <div>Título</div>
            <div>Cliente</div>
            <div>Responsable</div>
            <div>Estado</div>
            <div>Avance Real</div>
            <div>Avance planeado</div>
            <div>Días</div>
            <div>Inicio</div>
            <div>Fin</div>
            <div class="text-center">Gestionar</div>
            <div class="text-center">Eliminar</div>
        </div>

        <!-- Filas -->
        <div
            v-for="(p, index) in filteredProjects"
            :key="p.id ?? `new-${index}`"
            class="grid grid-cols-[40px_200px_200px_200px_100px_200px_200px_120px_120px_80px_80px] items-center gap-2 border-b bg-white p-2 text-sm transition hover:bg-gray-50"
        >
            <div>{{ index + 1 }}</div>

            <n-input v-model:value="p.title" round size="small" placeholder="Ingrese el titulo del proyecto" />

            <n-select v-model:value="p.customer_id" :options="customerOptions" filterable placeholder="Selecciona un cliente" />

            <n-select v-model:value="p.user_id" :options="usersOptions" filterable placeholder="Selecciona un usuario" />

            <span class="status-badge">{{ p.status.status }}</span>
            <n-progress
                type="line"
                indicator-placement="inside"
                :color="p.percentage < p.percentage_planned ? themeVars.errorColor : themeVars.successColor"
                :rail-color="changeColor(p.percentage < p.percentage_planned ? themeVars.errorColor : themeVars.successColor, { alpha: 0.2 })"
                :percentage="Math.round(p.percentage)"
            />
            <n-progress
                type="line"
                indicator-placement="inside"
                :color="themeVars.infoColor"
                :rail-color="changeColor(themeVars.infoColor, { alpha: 0.2 })"
                :percentage="Math.round(p.percentage_planned)"
            />
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

        <!-- Botones de guardar -->
        <div v-if="store.hasChanges" class="mt-2 flex justify-end gap-2">
            <n-button @click="saveChanges" round type="Primary"> 💾 Guardar Cambios </n-button>
            <n-button @click="store.discardChanges" round type="Warning"> ❌ Descartar Cambios </n-button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useCustomersStore, useProjectStore, useUserStore } from '@/composables/useCatalogStore';
import type { Customer, User } from '@/types/catalogs';
import axios from 'axios';
import { useThemeVars } from 'naive-ui';
import { changeColor } from 'seemly';
import { computed, ref, watch } from 'vue';

const themeVars = useThemeVars();

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

const customerOptions = computed(() =>
    customers.value.map((c) => ({
        label: c.name,
        value: c.id,
    })),
);

const usersOptions = computed(() =>
    users.value.map((c) => ({
        label: c.name,
        value: c.id,
    })),
);

// 🔹 Opciones de estado (puedes reemplazar con tus datos reales)
const statusOptions = computed(() => {
    const statuses = new Set(store.edit?.map((p) => p.status.status));
    return Array.from(statuses).map((s) => ({ label: s, value: s }));
});

// Filtros
const filterCustomer = ref<number | null>(null);
const filterStatus = ref<string | null>(null);

// Proyectos filtrados
const filteredProjects = computed(() => {
    if (!store.edit) return [];
    return store.edit.filter((p) => {
        const matchCustomer = !filterCustomer.value || p.customer_id === filterCustomer.value;
        const matchStatus = !filterStatus.value || p.status.status === filterStatus.value;
        return matchCustomer && matchStatus;
    });
});

const disableWeekends = (ts: any) => {
    const day = new Date(ts).getDay();
    return day === 0 || day === 6;
};

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

function removeRow(index: number) {
    if (store.edit !== null) {
        store.edit.splice(index, 1);
    }
}

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
