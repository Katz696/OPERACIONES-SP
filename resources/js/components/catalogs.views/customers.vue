<template>
  <div class="relative w-full space-y-2">
    <!-- Botón agregar -->
    <div class="flex justify-end">
      <n-button type="success" round @click="addRow" title="Añadir Proyecto">
        ➕ Agregar
      </n-button>
    </div>
    <!-- Encabezados -->
    <div
      class="grid grid-cols-[40px_250px_250px_150px_250px_80px] gap-2 text-xs font-semibold text-gray-700 bg-gray-100 p-2 rounded"
    >
      <div>#</div>
      <div>Nombre</div>
      <div>Correo</div>
      <div>Teléfono</div>
      <div>Dirección</div>
      <div>Eliminar</div>
    </div>
    <!-- Filas -->
    <div
      v-for="(c, index) in store.edit"
      :key="c.id ?? `new-${index}`"
      class="grid grid-cols-[40px_250px_250px_150px_250px_80px] gap-2 text-sm items-center bg-white border-b p-2 hover:bg-gray-50 transition"
    >
      <div>{{ index + 1 }}</div>
      <n-input
        v-model:value="c.name"
        round
        size="small"
        placeholder="Ingrese un nombre"
      />
      <n-input
        v-model:value="c.email"
        round
        size="small"
        placeholder="Ingrese un nombre"
      />
      <n-input
        v-model:value="c.phone"
        round
        size="small"
        placeholder="Ingrese un numero de teléfono"
      />
      <n-input
        v-model:value="c.address"
        round
        size="small"
        placeholder="Ingrese una dirección"
      />
      <n-button @click="removeRow(index)" round type="Error" title="Eliminar"
        >🗑️</n-button
      >
    </div>
    <!-- Spinner -->
    <div
      v-if="loading"
      class="absolute inset-0 bg-black/30 flex justify-center items-center z-50 rounded"
    >
      <div
        class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"
      ></div>
    </div>
    <!-- Botones de acción -->
    <div v-if="store.hasChanges" class="flex justify-end gap-2 mt-2">
      <!-- Guardar cambios -->
      <n-button @click="saveChanges" round type="Primary"> 💾 Guardar Cambios </n-button>
      <!-- Descartar cambios -->
      <n-button @click="store.discardChanges" round type="Warning">
        ❌ Descartar Cambios
      </n-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useCustomersStore } from "@/composables/useCatalogStore";
import type { Customer } from "@/types/catalogs";
import axios from "axios";

const store = useCustomersStore();
const loading = ref(false);

async function saveChanges() {
  if (!store.edit?.length) return;
  loading.value = true;

  const payload = store.edit.map((c) => ({
    id: c.id || null,
    name: c.name,
    email: c.email,
    phone: c.phone,
    address: c.address,
  }));

  try {
    await axios.put(route("catalogs.customers.sync"), payload);
    const res = await axios.get(route("catalogs.customers"));
    store.setData(res.data);
  } catch (err) {
    console.error(err);
    alert("Error al guardar cambios");
  } finally {
    loading.value = false;
  }
}

function removeRow(index: number) {
  store.edit?.splice(index, 1);
}

function addRow() {
  store.edit?.push({
    id: null,
    name: "Nuevo usuario",
    email: "",
    phone: "",
    address: "",
  } as Customer);
}
</script>
