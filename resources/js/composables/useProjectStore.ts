import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import * as types from '@/types/treeproject';

export const useProjectStore = defineStore('project', () => {

    // Versión original (como viene del backend)
    const original = ref<types.FullProjectResponse | null>(null)

    // Versión editable (puede cambiar en gestión)
    const editable = ref<types.FullProjectResponse | null>(null)

    // Catálogos (sin cambios)
    const statuses = ref<types.Status[]>([])
    const substatuses = ref<types.SubStatus[]>([])
    const customers = ref<types.Customer | null>(null)
    const users = ref<types.User[]>([])
    const sprints = ref<types.Sprint[]>([])
    const priorities = ref<types.Priority[]>([])
    const hasChanges = computed(() => {
        return JSON.stringify(original.value) !== JSON.stringify(editable.value);
    });


    function setProject(newProject: types.FullProjectResponse) {
        original.value = newProject
        editable.value = deepClone(newProject) // clonado profundo
        statuses.value = newProject.statuses ?? []
        substatuses.value = newProject.substatuses ?? []
        customers.value = newProject.customer ?? null
        users.value = newProject.users ?? []
        sprints.value = newProject.sprints ?? []
        priorities.value = newProject.priorities ?? []
    }

    function discardChanges() {
        editable.value = original.value ? JSON.parse(JSON.stringify(original.value)) : null
    }

    function updateEditableProject(newData: types.FullProjectResponse) {
        editable.value = newData
    }
    function resetStore() {
        original.value = null
        editable.value = null
        statuses.value = []
        customers.value = null
        users.value = []
        sprints.value = []
        priorities.value = []
    }
    function deepClone<T>(obj: T): T {
        return JSON.parse(JSON.stringify(obj))
    }

    return {
        original,
        editable,
        setProject,
        discardChanges,
        updateEditableProject,
        statuses,
        substatuses,
        priorities,
        customers,
        users,
        sprints,
        hasChanges,
        resetStore,
    }

});
